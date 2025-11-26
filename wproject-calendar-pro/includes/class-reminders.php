<?php
/**
 * Reminders Class
 *
 * Handles event reminder scheduling and notifications
 *
 * @package wProject Calendar Pro
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WProject_Reminders {

    /**
     * Initialize reminders cron
     */
    public static function init() {
        if ( ! wp_next_scheduled( 'calendar_pro_send_reminders' ) ) {
            wp_schedule_event( time(), 'hourly', 'calendar_pro_send_reminders' );
        }

        add_action( 'calendar_pro_send_reminders', array( __CLASS__, 'send_pending_reminders' ) );
    }

    /**
     * Send pending reminders
     */
    public static function send_pending_reminders() {
        global $wpdb;

        $table_reminders = $wpdb->prefix . 'wproject_event_reminders';
        $table_events = $wpdb->prefix . 'wproject_events';

        // Get reminders that are due and not sent
        $reminders = $wpdb->get_results(
            "SELECT r.*, e.title, e.start_datetime, e.location, e.description
            FROM $table_reminders r
            INNER JOIN $table_events e ON r.event_id = e.id
            WHERE r.sent = 0 AND r.reminder_datetime <= NOW()
            LIMIT 50"
        );

        foreach ( $reminders as $reminder ) {
            self::send_reminder_notification( $reminder );

            // Mark as sent
            $wpdb->update(
                $table_reminders,
                array(
                    'sent'    => 1,
                    'sent_at' => current_time( 'mysql' )
                ),
                array( 'id' => $reminder->id ),
                array( '%d', '%s' ),
                array( '%d' )
            );
        }
    }

    /**
     * Send reminder notification
     *
     * @param object $reminder Reminder object with event data
     */
    private static function send_reminder_notification( $reminder ) {
        $user = get_userdata( $reminder->user_id );

        if ( ! $user ) {
            return;
        }

        $options = get_option( 'wproject_settings' );
        $sender_name = isset( $options['sender_name'] ) ? $options['sender_name'] : get_bloginfo( 'name' );
        $sender_email = isset( $options['sender_email'] ) ? $options['sender_email'] : get_bloginfo( 'admin_email' );

        $event_time = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $reminder->start_datetime ) );

        $subject = sprintf(
            __( 'Reminder: %s', 'wproject-calendar-pro' ),
            $reminder->title
        );

        $message = sprintf(
            __( 'Hi %s,', 'wproject-calendar-pro' ) . "\n\n",
            $user->display_name
        );

        $message .= sprintf(
            __( 'This is a reminder for your upcoming event:', 'wproject-calendar-pro' ) . "\n\n"
        );

        $message .= sprintf( __( 'Event: %s', 'wproject-calendar-pro' ) . "\n", $reminder->title );
        $message .= sprintf( __( 'When: %s', 'wproject-calendar-pro' ) . "\n", $event_time );

        if ( $reminder->location ) {
            $message .= sprintf( __( 'Location: %s', 'wproject-calendar-pro' ) . "\n", $reminder->location );
        }

        if ( $reminder->description ) {
            $message .= "\n" . sprintf( __( 'Description:', 'wproject-calendar-pro' ) . "\n%s\n", wp_strip_all_tags( $reminder->description ) );
        }

        $headers = array(
            'From: ' . $sender_name . ' <' . $sender_email . '>',
            'Content-Type: text/plain; charset=UTF-8'
        );

        wp_mail( $user->user_email, $subject, $message, $headers );

        do_action( 'calendar_pro_reminder_sent', $reminder->id, $reminder->event_id, $reminder->user_id );
    }

    /**
     * Create reminder for event
     *
     * @param int $event_id Event ID
     * @param int $user_id User ID
     * @param int $minutes_before Minutes before event
     * @return int|false Reminder ID or false on failure
     */
    public static function create_reminder( $event_id, $user_id, $minutes_before = 15 ) {
        global $wpdb;

        $event = WProject_Event_Manager::get_event( $event_id );

        if ( ! $event ) {
            return false;
        }

        $table_reminders = $wpdb->prefix . 'wproject_event_reminders';

        // Calculate reminder datetime
        $start_datetime = strtotime( $event->start_datetime );
        $reminder_datetime = date( 'Y-m-d H:i:s', $start_datetime - ( $minutes_before * 60 ) );

        $result = $wpdb->insert(
            $table_reminders,
            array(
                'event_id'          => $event_id,
                'user_id'           => $user_id,
                'reminder_datetime' => $reminder_datetime,
                'sent'              => 0,
                'created_at'        => current_time( 'mysql' )
            ),
            array( '%d', '%d', '%s', '%d', '%s' )
        );

        if ( $result ) {
            return $wpdb->insert_id;
        }

        return false;
    }

    /**
     * Delete reminder
     *
     * @param int $reminder_id Reminder ID
     * @return bool Success or failure
     */
    public static function delete_reminder( $reminder_id ) {
        global $wpdb;

        $table_reminders = $wpdb->prefix . 'wproject_event_reminders';

        $result = $wpdb->delete(
            $table_reminders,
            array( 'id' => $reminder_id ),
            array( '%d' )
        );

        return (bool) $result;
    }

    /**
     * Get user's upcoming reminders
     *
     * @param int $user_id User ID
     * @param int $limit Limit
     * @return array Array of reminder objects
     */
    public static function get_user_reminders( $user_id = 0, $limit = 10 ) {
        global $wpdb;

        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        $table_reminders = $wpdb->prefix . 'wproject_event_reminders';
        $table_events = $wpdb->prefix . 'wproject_events';

        $reminders = $wpdb->get_results( $wpdb->prepare(
            "SELECT r.*, e.title, e.start_datetime, e.location
            FROM $table_reminders r
            INNER JOIN $table_events e ON r.event_id = e.id
            WHERE r.user_id = %d AND r.sent = 0 AND r.reminder_datetime >= NOW()
            ORDER BY r.reminder_datetime ASC
            LIMIT %d",
            $user_id,
            $limit
        ) );

        return $reminders;
    }
}

// Initialize reminders
WProject_Reminders::init();
