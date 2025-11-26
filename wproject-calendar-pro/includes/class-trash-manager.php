<?php
/**
 * Trash Manager Class
 *
 * Handles soft deletion and recovery of events and calendars
 *
 * @package wProject Calendar Pro
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WProject_Trash_Manager {

    /**
     * Move event to trash
     *
     * @param int $event_id Event ID
     * @return bool Success or failure
     */
    public static function trash_event( $event_id ) {
        return WProject_Event_Manager::update_event( $event_id, array(
            'status' => 'cancelled'
        ) );
    }

    /**
     * Restore event from trash
     *
     * @param int $event_id Event ID
     * @return bool Success or failure
     */
    public static function restore_event( $event_id ) {
        return WProject_Event_Manager::update_event( $event_id, array(
            'status' => 'scheduled'
        ) );
    }

    /**
     * Permanently delete event
     *
     * @param int $event_id Event ID
     * @return bool Success or failure
     */
    public static function delete_event_permanently( $event_id ) {
        return WProject_Event_Manager::delete_event( $event_id );
    }

    /**
     * Get trashed events for user
     *
     * @param int $user_id User ID
     * @return array Array of trashed events
     */
    public static function get_trashed_events( $user_id = 0 ) {
        global $wpdb;

        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        $table_events = $wpdb->prefix . 'wproject_events';
        $table_calendars = $wpdb->prefix . 'wproject_calendars';

        $events = $wpdb->get_results( $wpdb->prepare(
            "SELECT e.*, c.name as calendar_name
            FROM $table_events e
            INNER JOIN $table_calendars c ON e.calendar_id = c.id
            WHERE e.status = 'cancelled' AND c.owner_id = %d
            ORDER BY e.updated_at DESC",
            $user_id
        ) );

        return $events;
    }

    /**
     * Auto-cleanup old cancelled events
     *
     * @param int $days_old Number of days
     * @return int Number of events deleted
     */
    public static function cleanup_old_cancelled_events( $days_old = 30 ) {
        global $wpdb;

        $table_events = $wpdb->prefix . 'wproject_events';

        $cutoff_date = date( 'Y-m-d H:i:s', strtotime( "-{$days_old} days" ) );

        // Get old cancelled events
        $events = $wpdb->get_results( $wpdb->prepare(
            "SELECT id FROM $table_events WHERE status = 'cancelled' AND updated_at < %s",
            $cutoff_date
        ) );

        $count = 0;
        foreach ( $events as $event ) {
            if ( WProject_Event_Manager::delete_event( $event->id ) ) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Initialize cleanup cron
     */
    public static function init() {
        if ( ! wp_next_scheduled( 'calendar_pro_cleanup_old_events' ) ) {
            wp_schedule_event( time(), 'daily', 'calendar_pro_cleanup_old_events' );
        }

        add_action( 'calendar_pro_cleanup_old_events', array( __CLASS__, 'run_cleanup' ) );
    }

    /**
     * Run scheduled cleanup
     */
    public static function run_cleanup() {
        $options = get_option( 'wproject_settings' );
        $cleanup_days = isset( $options['calendar_cleanup_days'] ) ? (int) $options['calendar_cleanup_days'] : 30;

        if ( $cleanup_days > 0 ) {
            self::cleanup_old_cancelled_events( $cleanup_days );
        }
    }
}

// Initialize trash manager
WProject_Trash_Manager::init();
