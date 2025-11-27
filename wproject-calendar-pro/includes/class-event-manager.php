<?php
/**
 * Event Manager Class - SECURITY FIXED
 *
 * Handles event creation, retrieval, updating, and deletion
 * PHASE-1A-SECURITY-FIXES: SQL Injection vulnerabilities fixed
 *
 * @package wProject Calendar Pro
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WProject_Event_Manager {

    /**
     * Create a new event
     *
     * @param array $event_data Event data
     * @return int|false Event ID or false on failure
     */
    public static function create_event( $event_data ) {
        global $wpdb;

        $table_events = $wpdb->prefix . 'wproject_events';

        $defaults = array(
            'calendar_id'         => 0,
            'title'               => '',
            'description'         => '',
            'location'            => '',
            'start_datetime'      => current_time( 'mysql' ),
            'end_datetime'        => current_time( 'mysql' ),
            'all_day'             => 0,
            'event_type'          => 'event',
            'color'               => null,
            'owner_id'            => get_current_user_id(),
            'created_by'          => get_current_user_id(),
            'project_id'          => null,
            'task_id'             => null,
            'is_recurring'        => 0,
            'recurrence_parent_id'=> null,
            'recurrence_rule'     => null,
            'status'              => 'scheduled',
            'visibility'          => 'private',
            'reminder_enabled'    => 0,
            'reminder_minutes'    => 15,
            'categories'          => '',
            'timezone'            => 'UTC',
            'created_at'          => current_time( 'mysql' ),
            'updated_at'          => current_time( 'mysql' )
        );

        $event_data = wp_parse_args( $event_data, $defaults );

        // Validate required fields
        if ( empty( $event_data['calendar_id'] ) ) {
            error_log( '[Calendar Pro] Event creation failed: Missing calendar_id' );
            return false;
        }
        if ( empty( $event_data['title'] ) ) {
            error_log( '[Calendar Pro] Event creation failed: Missing title' );
            return false;
        }

        $result = $wpdb->insert(
            $table_events,
            $event_data,
            array(
                '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%s',
                '%s', '%d', '%d', '%d', '%d', '%d', '%d', '%s',
                '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s'
            )
        );

        if ( ! $result ) {
            error_log( '[Calendar Pro] Event insert failed: ' . $wpdb->last_error );
            return false;
        }

        $event_id = $wpdb->insert_id;

        // Schedule reminder if enabled
        if ( $event_data['reminder_enabled'] ) {
            self::schedule_reminder( $event_id, $event_data['owner_id'] );
        }

        // Add owner as attendee
        self::add_attendee( $event_id, $event_data['owner_id'], 'accepted', 1 );

        do_action( 'calendar_pro_event_created', $event_id, $event_data );

        return $event_id;
    }

    /**
     * Update an event
     *
     * @param int $event_id Event ID
     * @param array $event_data Event data
     * @return bool Success or failure
     */
    public static function update_event( $event_id, $event_data ) {
        global $wpdb;

        $table_events = $wpdb->prefix . 'wproject_events';

        // Add updated timestamp
        $event_data['updated_at'] = current_time( 'mysql' );

        $result = $wpdb->update(
            $table_events,
            $event_data,
            array( 'id' => $event_id ),
            null,
            array( '%d' )
        );

        if ( $result !== false ) {
            do_action( 'calendar_pro_event_updated', $event_id, $event_data );
            return true;
        }

        return false;
    }

    /**
     * Delete an event
     *
     * @param int $event_id Event ID
     * @return bool Success or failure
     */
    public static function delete_event( $event_id ) {
        global $wpdb;

        $table_events = $wpdb->prefix . 'wproject_events';
        $table_attendees = $wpdb->prefix . 'wproject_event_attendees';
        $table_reminders = $wpdb->prefix . 'wproject_event_reminders';

        // Delete attendees
        $wpdb->delete( $table_attendees, array( 'event_id' => $event_id ), array( '%d' ) );

        // Delete reminders
        $wpdb->delete( $table_reminders, array( 'event_id' => $event_id ), array( '%d' ) );

        // Delete event
        $result = $wpdb->delete( $table_events, array( 'id' => $event_id ), array( '%d' ) );

        if ( $result ) {
            do_action( 'calendar_pro_event_deleted', $event_id );
            return true;
        }

        return false;
    }

    /**
     * Get event by ID
     *
     * @param int $event_id Event ID
     * @return object|null Event object or null
     */
    public static function get_event( $event_id ) {
        global $wpdb;

        $table_events = $wpdb->prefix . 'wproject_events';

        $event = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table_events WHERE id = %d",
            $event_id
        ) );

        return $event;
    }

    /**
     * Get events for a calendar - SECURITY FIX: No more SQL injection vulnerability
     *
     * @param int $calendar_id Calendar ID
     * @param string $start_date Start date (Y-m-d)
     * @param string $end_date End date (Y-m-d)
     * @return array Array of event objects
     */
    public static function get_calendar_events( $calendar_id, $start_date = null, $end_date = null ) {
        global $wpdb;

        $table_events = $wpdb->prefix . 'wproject_events';

        // Base query with prepared statement
        if ( $start_date && $end_date ) {
            // Use conditional logic within prepared statement - FIX for SQL injection vulnerability
            $events = $wpdb->get_results( $wpdb->prepare(
                "SELECT * FROM $table_events
                 WHERE calendar_id = %d
                 AND ((start_datetime >= %s AND start_datetime <= %s)
                      OR (end_datetime >= %s AND end_datetime <= %s))
                 ORDER BY start_datetime ASC",
                $calendar_id,
                $start_date,
                $end_date,
                $start_date,
                $end_date
            ) );
        } else {
            // No date filtering
            $events = $wpdb->get_results( $wpdb->prepare(
                "SELECT * FROM $table_events WHERE calendar_id = %d ORDER BY start_datetime ASC",
                $calendar_id
            ) );
        }

        return $events;
    }

    /**
     * Get events for a user across all calendars - SECURITY FIX: No more SQL injection vulnerability
     *
     * @param int $user_id User ID
     * @param string $start_date Start date (Y-m-d)
     * @param string $end_date End date (Y-m-d)
     * @return array Array of event objects
     */
    public static function get_user_events( $user_id = 0, $start_date = null, $end_date = null ) {
        global $wpdb;

        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        $table_events = $wpdb->prefix . 'wproject_events';
        $table_calendars = $wpdb->prefix . 'wproject_calendars';
        $table_attendees = $wpdb->prefix . 'wproject_event_attendees';

        // Use conditional logic within prepared statement - FIX for SQL injection vulnerability
        if ( $start_date && $end_date ) {
            // Get events from owned calendars and events where user is attendee with date filtering
            $events = $wpdb->get_results( $wpdb->prepare(
                "SELECT DISTINCT e.*, c.name as calendar_name, c.color as calendar_color
                FROM $table_events e
                INNER JOIN $table_calendars c ON e.calendar_id = c.id
                LEFT JOIN $table_attendees a ON e.id = a.event_id
                WHERE ((e.start_datetime >= %s AND e.start_datetime <= %s)
                       OR (e.end_datetime >= %s AND e.end_datetime <= %s))
                AND (c.owner_id = %d OR a.user_id = %d)
                ORDER BY e.start_datetime ASC",
                $start_date,
                $end_date,
                $start_date,
                $end_date,
                $user_id,
                $user_id
            ) );
        } else {
            // Get events without date filtering
            $events = $wpdb->get_results( $wpdb->prepare(
                "SELECT DISTINCT e.*, c.name as calendar_name, c.color as calendar_color
                FROM $table_events e
                INNER JOIN $table_calendars c ON e.calendar_id = c.id
                LEFT JOIN $table_attendees a ON e.id = a.event_id
                WHERE (c.owner_id = %d OR a.user_id = %d)
                ORDER BY e.start_datetime ASC",
                $user_id,
                $user_id
            ) );
        }

        return $events;
    }

    /**
     * Get events for a project - SECURITY FIX: No more SQL injection vulnerability
     *
     * @param int $project_id Project ID
     * @param string $start_date Start date (Y-m-d)
     * @param string $end_date End date (Y-m-d)
     * @return array Array of event objects
     */
    public static function get_project_events( $project_id, $start_date = null, $end_date = null ) {
        global $wpdb;

        $table_events = $wpdb->prefix . 'wproject_events';

        // Use conditional logic within prepared statement - FIX for SQL injection vulnerability
        if ( $start_date && $end_date ) {
            $events = $wpdb->get_results( $wpdb->prepare(
                "SELECT * FROM $table_events
                 WHERE project_id = %d
                 AND ((start_datetime >= %s AND start_datetime <= %s)
                      OR (end_datetime >= %s AND end_datetime <= %s))
                 ORDER BY start_datetime ASC",
                $project_id,
                $start_date,
                $end_date,
                $start_date,
                $end_date
            ) );
        } else {
            $events = $wpdb->get_results( $wpdb->prepare(
                "SELECT * FROM $table_events WHERE project_id = %d ORDER BY start_datetime ASC",
                $project_id
            ) );
        }

        return $events;
    }

    /**
     * Add attendee to event
     *
     * @param int $event_id Event ID
     * @param int $user_id User ID
     * @param string $status Status (pending, accepted, declined, tentative)
     * @param bool $can_edit Can edit event
     * @return int|false Attendee ID or false on failure
     */
    public static function add_attendee( $event_id, $user_id, $status = 'pending', $can_edit = false ) {
        global $wpdb;

        $table_attendees = $wpdb->prefix . 'wproject_event_attendees';

        $result = $wpdb->insert(
            $table_attendees,
            array(
                'event_id'   => $event_id,
                'user_id'    => $user_id,
                'status'     => $status,
                'can_edit'   => $can_edit ? 1 : 0,
                'created_at' => current_time( 'mysql' ),
                'updated_at' => current_time( 'mysql' )
            ),
            array( '%d', '%d', '%s', '%d', '%s', '%s' )
        );

        if ( $result ) {
            do_action( 'calendar_pro_attendee_added', $wpdb->insert_id, $event_id, $user_id );
            return $wpdb->insert_id;
        }

        return false;
    }

    /**
     * Update attendee status
     *
     * @param int $event_id Event ID
     * @param int $user_id User ID
     * @param string $status Status (accepted, declined, tentative)
     * @return bool Success or failure
     */
    public static function update_attendee_status( $event_id, $user_id, $status ) {
        global $wpdb;

        $table_attendees = $wpdb->prefix . 'wproject_event_attendees';

        $result = $wpdb->update(
            $table_attendees,
            array(
                'status'     => $status,
                'updated_at' => current_time( 'mysql' )
            ),
            array(
                'event_id' => $event_id,
                'user_id'  => $user_id
            ),
            array( '%s', '%s' ),
            array( '%d', '%d' )
        );

        if ( $result !== false ) {
            do_action( 'calendar_pro_attendee_status_updated', $event_id, $user_id, $status );
            return true;
        }

        return false;
    }

    /**
     * Get event attendees
     *
     * @param int $event_id Event ID
     * @return array Array of attendee objects with user data
     */
    public static function get_event_attendees( $event_id ) {
        global $wpdb;

        $table_attendees = $wpdb->prefix . 'wproject_event_attendees';

        $attendees = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM $table_attendees WHERE event_id = %d",
            $event_id
        ) );

        // Enrich with user data
        foreach ( $attendees as &$attendee ) {
            $user = get_userdata( $attendee->user_id );
            if ( $user ) {
                $attendee->user_name = $user->display_name;
                $attendee->user_email = $user->user_email;
            }
        }

        return $attendees;
    }

    /**
     * Remove attendee from event
     *
     * @param int $event_id Event ID
     * @param int $user_id User ID
     * @return bool Success or failure
     */
    public static function remove_attendee( $event_id, $user_id ) {
        global $wpdb;

        $table_attendees = $wpdb->prefix . 'wproject_event_attendees';

        $result = $wpdb->delete(
            $table_attendees,
            array(
                'event_id' => $event_id,
                'user_id'  => $user_id
            ),
            array( '%d', '%d' )
        );

        if ( $result ) {
            do_action( 'calendar_pro_attendee_removed', $event_id, $user_id );
            return true;
        }

        return false;
    }

    /**
     * Schedule event reminder
     *
     * @param int $event_id Event ID
     * @param int $user_id User ID
     * @return bool Success or failure
     */
    private static function schedule_reminder( $event_id, $user_id ) {
        global $wpdb;

        $event = self::get_event( $event_id );

        if ( ! $event || ! $event->reminder_enabled ) {
            return false;
        }

        $table_reminders = $wpdb->prefix . 'wproject_event_reminders';

        // Calculate reminder datetime
        $start_datetime = strtotime( $event->start_datetime );
        $reminder_minutes = $event->reminder_minutes;
        $reminder_datetime = date( 'Y-m-d H:i:s', $start_datetime - ( $reminder_minutes * 60 ) );

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

        return (bool) $result;
    }

    /**
     * Convert event to FullCalendar format
     *
     * @param object $event Event object
     * @return array FullCalendar event array
     */
    public static function to_fullcalendar_format( $event ) {
        return array(
            'id'             => $event->id,
            'title'          => $event->title,
            'start'          => $event->start_datetime,
            'end'            => $event->end_datetime,
            'allDay'         => (bool) $event->all_day,
            'backgroundColor'=> $event->color,
            'borderColor'    => $event->color,
            'description'    => $event->description,
            'location'       => $event->location,
            'eventType'      => $event->event_type,
            'status'         => $event->status,
            'editable'       => true,
            'extendedProps'  => array(
                'calendar_id' => $event->calendar_id,
                'project_id'  => $event->project_id,
                'task_id'     => $event->task_id,
                'is_recurring'=> $event->is_recurring,
                'visibility'  => $event->visibility
            )
        );
    }
}
