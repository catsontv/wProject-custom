<?php
/**
 * Meetings Class
 *
 * Handles meeting-specific functionality
 *
 * @package wProject Calendar Pro
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WProject_Meetings {

    /**
     * Create meeting event
     *
     * @param array $meeting_data Meeting data
     * @return int|false Event ID or false on failure
     */
    public static function create_meeting( $meeting_data ) {
        $meeting_data['event_type'] = 'meeting';

        $event_id = WProject_Event_Manager::create_event( $meeting_data );

        if ( $event_id && isset( $meeting_data['attendees'] ) ) {
            foreach ( $meeting_data['attendees'] as $user_id ) {
                WProject_Event_Manager::add_attendee( $event_id, $user_id, 'pending', false );
            }
        }

        return $event_id;
    }

    /**
     * Get user's upcoming meetings
     *
     * @param int $user_id User ID
     * @param int $limit Limit
     * @return array Array of meeting events
     */
    public static function get_upcoming_meetings( $user_id = 0, $limit = 10 ) {
        global $wpdb;

        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        $table_events = $wpdb->prefix . 'wproject_events';
        $table_attendees = $wpdb->prefix . 'wproject_event_attendees';

        $meetings = $wpdb->get_results( $wpdb->prepare(
            "SELECT DISTINCT e.*
            FROM $table_events e
            INNER JOIN $table_attendees a ON e.id = a.event_id
            WHERE e.event_type = 'meeting'
            AND a.user_id = %d
            AND e.start_datetime >= NOW()
            AND e.status = 'scheduled'
            ORDER BY e.start_datetime ASC
            LIMIT %d",
            $user_id,
            $limit
        ) );

        return $meetings;
    }

    /**
     * Get meeting attendance summary
     *
     * @param int $event_id Event ID
     * @return array Attendance summary
     */
    public static function get_attendance_summary( $event_id ) {
        $attendees = WProject_Event_Manager::get_event_attendees( $event_id );

        $summary = array(
            'total'     => count( $attendees ),
            'accepted'  => 0,
            'declined'  => 0,
            'tentative' => 0,
            'pending'   => 0
        );

        foreach ( $attendees as $attendee ) {
            $summary[ $attendee->status ]++;
        }

        return $summary;
    }

    /**
     * Generate meeting notes template
     *
     * @param int $event_id Event ID
     * @return string Meeting notes template
     */
    public static function generate_notes_template( $event_id ) {
        $event = WProject_Event_Manager::get_event( $event_id );
        $attendees = WProject_Event_Manager::get_event_attendees( $event_id );

        $notes = "# " . $event->title . "\n\n";
        $notes .= "**Date:** " . date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $event->start_datetime ) ) . "\n";
        $notes .= "**Location:** " . ( $event->location ? $event->location : 'N/A' ) . "\n\n";

        $notes .= "## Attendees\n\n";
        foreach ( $attendees as $attendee ) {
            if ( $attendee->status == 'accepted' ) {
                $notes .= "- " . $attendee->user_name . "\n";
            }
        }

        $notes .= "\n## Agenda\n\n";
        if ( $event->description ) {
            $notes .= wp_strip_all_tags( $event->description ) . "\n\n";
        }

        $notes .= "## Discussion\n\n";
        $notes .= "## Action Items\n\n";
        $notes .= "## Next Steps\n\n";

        return $notes;
    }
}
