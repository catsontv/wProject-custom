<?php
/**
 * Sharing Class
 *
 * Handles calendar and event sharing functionality
 *
 * @package wProject Calendar Pro
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WProject_Sharing {

    /**
     * Share event with users
     *
     * @param int $event_id Event ID
     * @param array $user_ids Array of user IDs
     * @param bool $can_edit Can edit event
     * @return int Number of users added
     */
    public static function share_event( $event_id, $user_ids, $can_edit = false ) {
        $count = 0;

        foreach ( $user_ids as $user_id ) {
            if ( WProject_Event_Manager::add_attendee( $event_id, $user_id, 'pending', $can_edit ) ) {
                $count++;

                // Send notification
                self::notify_event_shared( $event_id, $user_id );
            }
        }

        return $count;
    }

    /**
     * Send event shared notification
     *
     * @param int $event_id Event ID
     * @param int $user_id User ID
     */
    private static function notify_event_shared( $event_id, $user_id ) {
        $event = WProject_Event_Manager::get_event( $event_id );
        $user = get_userdata( $user_id );

        if ( ! $event || ! $user ) {
            return;
        }

        $options = get_option( 'wproject_settings' );
        $sender_name = isset( $options['sender_name'] ) ? $options['sender_name'] : get_bloginfo( 'name' );
        $sender_email = isset( $options['sender_email'] ) ? $options['sender_email'] : get_bloginfo( 'admin_email' );

        $event_time = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $event->start_datetime ) );

        $subject = sprintf(
            __( 'You have been invited to: %s', 'wproject-calendar-pro' ),
            $event->title
        );

        $message = sprintf(
            __( 'Hi %s,', 'wproject-calendar-pro' ) . "\n\n",
            $user->display_name
        );

        $message .= sprintf(
            __( 'You have been invited to the following event:', 'wproject-calendar-pro' ) . "\n\n"
        );

        $message .= sprintf( __( 'Event: %s', 'wproject-calendar-pro' ) . "\n", $event->title );
        $message .= sprintf( __( 'When: %s', 'wproject-calendar-pro' ) . "\n", $event_time );

        if ( $event->location ) {
            $message .= sprintf( __( 'Location: %s', 'wproject-calendar-pro' ) . "\n", $event->location );
        }

        if ( $event->description ) {
            $message .= "\n" . sprintf( __( 'Description:', 'wproject-calendar-pro' ) . "\n%s\n", wp_strip_all_tags( $event->description ) );
        }

        $headers = array(
            'From: ' . $sender_name . ' <' . $sender_email . '>',
            'Content-Type: text/plain; charset=UTF-8'
        );

        wp_mail( $user->user_email, $subject, $message, $headers );
    }

    /**
     * Get shareable users (team members)
     *
     * @return array Array of user objects
     */
    public static function get_shareable_users() {
        $users = get_users( array(
            'role__not_in' => array( 'client' ),
            'orderby'      => 'display_name',
            'order'        => 'ASC'
        ) );

        return $users;
    }

    /**
     * Generate shareable link for calendar
     *
     * @param int $calendar_id Calendar ID
     * @return string|false Shareable link or false on failure
     */
    public static function generate_share_link( $calendar_id ) {
        $calendar = WProject_Calendar_Manager::get_calendar( $calendar_id );

        if ( ! $calendar ) {
            return false;
        }

        // Generate unique token
        $token = wp_generate_password( 32, false );

        // Store token in calendar meta (would need meta table or use options)
        update_option( 'calendar_share_token_' . $calendar_id, $token );

        $link = add_query_arg(
            array(
                'calendar_share' => $calendar_id,
                'token'          => $token
            ),
            home_url()
        );

        return $link;
    }

    /**
     * Verify share link token
     *
     * @param int $calendar_id Calendar ID
     * @param string $token Token
     * @return bool Valid or not
     */
    public static function verify_share_token( $calendar_id, $token ) {
        $stored_token = get_option( 'calendar_share_token_' . $calendar_id );

        return $stored_token && $stored_token === $token;
    }

    /**
     * Export calendar to iCal format
     *
     * @param int $calendar_id Calendar ID
     * @return string iCal format string
     */
    public static function export_to_ical( $calendar_id ) {
        $calendar = WProject_Calendar_Manager::get_calendar( $calendar_id );
        $events = WProject_Event_Manager::get_calendar_events( $calendar_id );

        $ical = "BEGIN:VCALENDAR\r\n";
        $ical .= "VERSION:2.0\r\n";
        $ical .= "PRODID:-//wProject Calendar Pro//EN\r\n";
        $ical .= "CALSCALE:GREGORIAN\r\n";
        $ical .= "X-WR-CALNAME:" . $calendar->name . "\r\n";
        $ical .= "X-WR-TIMEZONE:UTC\r\n";

        foreach ( $events as $event ) {
            $ical .= "BEGIN:VEVENT\r\n";
            $ical .= "UID:" . $event->id . "@" . get_bloginfo( 'url' ) . "\r\n";
            $ical .= "DTSTAMP:" . self::format_ical_date( current_time( 'timestamp' ) ) . "\r\n";
            $ical .= "DTSTART:" . self::format_ical_date( strtotime( $event->start_datetime ) ) . "\r\n";
            $ical .= "DTEND:" . self::format_ical_date( strtotime( $event->end_datetime ) ) . "\r\n";
            $ical .= "SUMMARY:" . self::escape_ical_text( $event->title ) . "\r\n";

            if ( $event->description ) {
                $ical .= "DESCRIPTION:" . self::escape_ical_text( wp_strip_all_tags( $event->description ) ) . "\r\n";
            }

            if ( $event->location ) {
                $ical .= "LOCATION:" . self::escape_ical_text( $event->location ) . "\r\n";
            }

            $ical .= "STATUS:" . strtoupper( $event->status ) . "\r\n";
            $ical .= "END:VEVENT\r\n";
        }

        $ical .= "END:VCALENDAR\r\n";

        return $ical;
    }

    /**
     * Format date for iCal
     *
     * @param int $timestamp Timestamp
     * @return string Formatted date
     */
    private static function format_ical_date( $timestamp ) {
        return gmdate( 'Ymd\THis\Z', $timestamp );
    }

    /**
     * Escape text for iCal
     *
     * @param string $text Text to escape
     * @return string Escaped text
     */
    private static function escape_ical_text( $text ) {
        $text = str_replace( array( "\\", ",", ";", "\n" ), array( "\\\\", "\\,", "\\;", "\\n" ), $text );
        return $text;
    }
}
