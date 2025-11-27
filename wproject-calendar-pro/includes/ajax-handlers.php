<?php
/**
 * AJAX Handlers - SECURITY FIXED
 *
 * Handles all AJAX requests for calendar operations
 * PHASE-1A-SECURITY-FIXES: Permission checks and proper error termination added
 *
 * @package wProject Calendar Pro
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * AJAX Response Helper Function
 */
function calendar_ajaxStatus( $status, $message, $data = array() ) {
    $response = array(
        'status'  => $status,
        'message' => $message,
        'data'    => $data
    );
    wp_send_json( $response );
    // Explicit return for clarity (wp_send_json exits, but added for safety)
    return;
}

/**
 * Get calendar events
 * Security: Checks user can access calendar
 */
add_action( 'wp_ajax_calendar_pro_get_events', 'calendar_pro_get_events' );
function calendar_pro_get_events() {
    // Verify nonce
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'calendar_inputs' ) ) {
        calendar_ajaxStatus( 'error', __( 'Nonce check failed.', 'wproject-calendar-pro' ) );
        return;
    }

    $calendar_id = isset( $_POST['calendar_id'] ) ? (int) $_POST['calendar_id'] : 0;
    $project_id = isset( $_POST['project_id'] ) ? (int) $_POST['project_id'] : 0;
    $start_date = isset( $_POST['start'] ) ? sanitize_text_field( $_POST['start'] ) : null;
    $end_date = isset( $_POST['end'] ) ? sanitize_text_field( $_POST['end'] ) : null;

    error_log( '[Calendar Pro] Get events - calendar_id: ' . $calendar_id . ', project_id: ' . $project_id );

    // Priority: project_id filter > calendar_id filter > all user events
    if ( $project_id ) {
        // Filter by project - show events assigned to this project
        error_log( '[Calendar Pro] Filtering by project_id: ' . $project_id );
        $events = WProject_Event_Manager::get_project_events( $project_id, $start_date, $end_date );
        error_log( '[Calendar Pro] Found ' . count( $events ) . ' events for project ' . $project_id );
    } elseif ( $calendar_id ) {
        // Check user can access this calendar
        if ( ! WProject_Calendar_Permissions::user_can_access_calendar( $calendar_id ) ) {
            calendar_ajaxStatus( 'error', __( 'Access denied.', 'wproject-calendar-pro' ) );
            return;
        }

        $events = WProject_Event_Manager::get_calendar_events( $calendar_id, $start_date, $end_date );
    } else {
        // Get user's own events
        $events = WProject_Event_Manager::get_user_events( get_current_user_id(), $start_date, $end_date );
    }

    // Filter events user can access
    $filtered_events = array();
    foreach ( $events as $event ) {
        if ( WProject_Calendar_Permissions::user_can_access_event( $event->id ) ) {
            $filtered_events[] = WProject_Event_Manager::to_fullcalendar_format( $event );
        }
    }

    calendar_ajaxStatus( 'success', __( 'Events retrieved', 'wproject-calendar-pro' ), $filtered_events );
    return;
}

/**
 * Create event
 * Security: Checks user can create event in calendar and owns calendar
 */
add_action( 'wp_ajax_calendar_pro_create_event', 'calendar_pro_create_event' );
function calendar_pro_create_event() {
    // Verify nonce
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'calendar_inputs' ) ) {
        calendar_ajaxStatus( 'error', __( 'Nonce check failed.', 'wproject-calendar-pro' ) );
        return;
    }

    $calendar_id = isset( $_POST['calendar_id'] ) ? (int) $_POST['calendar_id'] : 0;

    error_log( '[CREATE EVENT] Calendar ID received: ' . var_export( $calendar_id, true ) );
    error_log( '[CREATE EVENT] POST calendar_id raw: ' . var_export( $_POST['calendar_id'] ?? 'NOT SET', true ) );
    error_log( '[CREATE EVENT] POST data: ' . json_encode( $_POST ) );

    // Check user can create event in this calendar
    if ( ! WProject_Calendar_Permissions::user_can_create_event( $calendar_id ) ) {
        calendar_ajaxStatus( 'error', __( 'Permission denied. You cannot create events in this calendar.', 'wproject-calendar-pro' ) );
        return;
    }

    $event_data = array(
        'calendar_id'      => $calendar_id,
        'title'            => isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : '',
        'description'      => isset( $_POST['description'] ) ? wp_kses_post( $_POST['description'] ) : '',
        'location'         => isset( $_POST['location'] ) ? sanitize_text_field( $_POST['location'] ) : '',
        'start_datetime'   => isset( $_POST['start'] ) ? sanitize_text_field( $_POST['start'] ) : '',
        'end_datetime'     => isset( $_POST['end'] ) ? sanitize_text_field( $_POST['end'] ) : '',
        'all_day'          => isset( $_POST['all_day'] ) ? 1 : 0,
        'event_type'       => isset( $_POST['event_type'] ) ? sanitize_text_field( $_POST['event_type'] ) : 'event',
        'color'            => isset( $_POST['color'] ) ? sanitize_hex_color( $_POST['color'] ) : null,
        'project_id'       => isset( $_POST['project_id'] ) ? (int) $_POST['project_id'] : null,
        'task_id'          => isset( $_POST['task_id'] ) ? (int) $_POST['task_id'] : null,
        'visibility'       => isset( $_POST['visibility'] ) ? sanitize_text_field( $_POST['visibility'] ) : 'private',
        'reminder_enabled' => isset( $_POST['reminder_enabled'] ) ? 1 : 0,
        'reminder_minutes' => isset( $_POST['reminder_minutes'] ) ? (int) $_POST['reminder_minutes'] : 15,
        'categories'       => isset( $_POST['categories'] ) ? sanitize_text_field( $_POST['categories'] ) : '',
        'timezone'         => isset( $_POST['timezone'] ) ? sanitize_text_field( $_POST['timezone'] ) : 'UTC'
    );

    $event_id = WProject_Event_Manager::create_event( $event_data );

    if ( $event_id ) {
        error_log( '[Calendar Pro AJAX] Event created successfully: Event ID=' . $event_id . ', Title=' . $event_data['title'] );

        // Add attendees if provided
        $attendees = isset( $_POST['attendees'] ) ? $_POST['attendees'] : array();
        if ( is_string( $attendees ) ) {
            $attendees = explode( ',', $attendees );
        }
        if ( is_array( $attendees ) && ! empty( $attendees ) ) {
            foreach ( $attendees as $user_id ) {
                $user_id = (int) $user_id;
                if ( $user_id > 0 ) {
                    WProject_Event_Manager::add_attendee( $event_id, $user_id, 'pending', false );
                }
            }
        }

        $event = WProject_Event_Manager::get_event( $event_id );
        calendar_ajaxStatus( 'success', __( 'Event created successfully', 'wproject-calendar-pro' ), array(
            'event' => WProject_Event_Manager::to_fullcalendar_format( $event )
        ) );
    } else {
        error_log( '[Calendar Pro AJAX] Event creation failed - check class-event-manager.php debug.log for details. Data: ' . json_encode( $event_data ) );
        calendar_ajaxStatus( 'error', __( 'Failed to create event', 'wproject-calendar-pro' ) );
    }
    return;
}

/**
 * Update event
 * Security: Checks user can edit this specific event
 */
add_action( 'wp_ajax_calendar_pro_update_event', 'calendar_pro_update_event' );
function calendar_pro_update_event() {
    // Verify nonce
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'calendar_inputs' ) ) {
        calendar_ajaxStatus( 'error', __( 'Nonce check failed.', 'wproject-calendar-pro' ) );
        return;
    }

    $event_id = isset( $_POST['event_id'] ) ? (int) $_POST['event_id'] : 0;

    // Check user can edit this event
    if ( ! WProject_Calendar_Permissions::user_can_edit_event( $event_id ) ) {
        calendar_ajaxStatus( 'error', __( 'Permission denied. You cannot edit this event.', 'wproject-calendar-pro' ) );
        return;
    }

    $event_data = array();

    if ( isset( $_POST['title'] ) ) {
        $event_data['title'] = sanitize_text_field( $_POST['title'] );
    }
    if ( isset( $_POST['description'] ) ) {
        $event_data['description'] = wp_kses_post( $_POST['description'] );
    }
    if ( isset( $_POST['location'] ) ) {
        $event_data['location'] = sanitize_text_field( $_POST['location'] );
    }
    if ( isset( $_POST['start'] ) ) {
        $event_data['start_datetime'] = sanitize_text_field( $_POST['start'] );
    }
    if ( isset( $_POST['end'] ) ) {
        $event_data['end_datetime'] = sanitize_text_field( $_POST['end'] );
    }
    if ( isset( $_POST['all_day'] ) ) {
        $event_data['all_day'] = 1;
    } else {
        $event_data['all_day'] = 0;
    }
    if ( isset( $_POST['event_type'] ) ) {
        $event_data['event_type'] = sanitize_text_field( $_POST['event_type'] );
    }
    if ( isset( $_POST['color'] ) ) {
        $event_data['color'] = sanitize_hex_color( $_POST['color'] );
    }
    if ( isset( $_POST['status'] ) ) {
        $event_data['status'] = sanitize_text_field( $_POST['status'] );
    }
    if ( isset( $_POST['visibility'] ) ) {
        $event_data['visibility'] = sanitize_text_field( $_POST['visibility'] );
    }
    if ( isset( $_POST['reminder_enabled'] ) ) {
        $event_data['reminder_enabled'] = 1;
    } else {
        $event_data['reminder_enabled'] = 0;
    }
    if ( isset( $_POST['reminder_minutes'] ) ) {
        $event_data['reminder_minutes'] = (int) $_POST['reminder_minutes'];
    }
    if ( isset( $_POST['categories'] ) ) {
        $event_data['categories'] = sanitize_text_field( $_POST['categories'] );
    }
    if ( isset( $_POST['timezone'] ) ) {
        $event_data['timezone'] = sanitize_text_field( $_POST['timezone'] );
    }

    if ( WProject_Event_Manager::update_event( $event_id, $event_data ) ) {
        error_log( '[Calendar Pro AJAX] Event updated successfully: Event ID=' . $event_id );

        // Update attendees if provided
        $attendees = isset( $_POST['attendees'] ) ? $_POST['attendees'] : array();
        if ( is_string( $attendees ) ) {
            $attendees = explode( ',', $attendees );
        }
        if ( is_array( $attendees ) && ! empty( $attendees ) ) {
            // Get current attendees
            $current_attendees = WProject_Event_Manager::get_event_attendees( $event_id );
            $current_ids = wp_list_pluck( $current_attendees, 'user_id' );

            // Add new attendees
            foreach ( $attendees as $user_id ) {
                $user_id = (int) $user_id;
                if ( $user_id > 0 && ! in_array( $user_id, $current_ids ) ) {
                    WProject_Event_Manager::add_attendee( $event_id, $user_id, 'pending', false );
                }
            }

            // Remove attendees not in the list (except the event owner)
            $event = WProject_Event_Manager::get_event( $event_id );
            foreach ( $current_ids as $user_id ) {
                if ( $user_id !== (int) $event->owner_id && ! in_array( $user_id, array_map( 'intval', $attendees ) ) ) {
                    WProject_Event_Manager::remove_attendee( $event_id, $user_id );
                }
            }
        }
        $event = WProject_Event_Manager::get_event( $event_id );
        calendar_ajaxStatus( 'success', __( 'Event updated successfully', 'wproject-calendar-pro' ), array(
            'event' => WProject_Event_Manager::to_fullcalendar_format( $event )
        ) );
    } else {
        error_log( '[Calendar Pro AJAX] Event update failed for Event ID=' . $event_id . ' - check class-event-manager.php debug.log for details' );
        calendar_ajaxStatus( 'error', __( 'Failed to update event', 'wproject-calendar-pro' ) );
    }
    return;
}

/**
 * Delete event
 * Security: Checks user can delete this specific event
 */
add_action( 'wp_ajax_calendar_pro_delete_event', 'calendar_pro_delete_event' );
function calendar_pro_delete_event() {
    // Verify nonce
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'calendar_inputs' ) ) {
        calendar_ajaxStatus( 'error', __( 'Nonce check failed.', 'wproject-calendar-pro' ) );
        return;
    }

    $event_id = isset( $_POST['event_id'] ) ? (int) $_POST['event_id'] : 0;

    // Check user can delete this event
    if ( ! WProject_Calendar_Permissions::user_can_delete_event( $event_id ) ) {
        calendar_ajaxStatus( 'error', __( 'Permission denied. You cannot delete this event.', 'wproject-calendar-pro' ) );
        return;
    }

    if ( WProject_Event_Manager::delete_event( $event_id ) ) {
        error_log( '[Calendar Pro AJAX] Event deleted successfully: Event ID=' . $event_id );
        calendar_ajaxStatus( 'success', __( 'Event deleted successfully', 'wproject-calendar-pro' ) );
    } else {
        error_log( '[Calendar Pro AJAX] Event delete failed for Event ID=' . $event_id );
        calendar_ajaxStatus( 'error', __( 'Failed to delete event', 'wproject-calendar-pro' ) );
    }
    return;
}

/**
 * Create calendar
 * Security: User must be authenticated
 */
add_action( 'wp_ajax_calendar_pro_create_calendar', 'calendar_pro_create_calendar' );
function calendar_pro_create_calendar() {
    error_log( '=== CALENDAR CREATION AJAX CALLED ===' );
    error_log( 'POST data: ' . print_r( $_POST, true ) );
    error_log( 'User ID: ' . get_current_user_id() );
    error_log( 'Is user logged in: ' . ( is_user_logged_in() ? 'YES' : 'NO' ) );

    // Verify nonce
    if ( ! isset( $_POST['nonce'] ) ) {
        error_log( '[Calendar Pro] NONCE NOT SET in POST data' );
        calendar_ajaxStatus( 'error', __( 'Nonce not provided.', 'wproject-calendar-pro' ) );
        return;
    }

    error_log( '[Calendar Pro] Nonce received: ' . $_POST['nonce'] );

    $nonce_check = wp_verify_nonce( $_POST['nonce'], 'calendar_inputs' );
    error_log( '[Calendar Pro] Nonce verification result: ' . ( $nonce_check ? 'PASS' : 'FAIL' ) );

    if ( ! $nonce_check ) {
        calendar_ajaxStatus( 'error', __( 'Nonce check failed. Please refresh the page and try again.', 'wproject-calendar-pro' ) );
        return;
    }

    // Check user is logged in
    if ( ! is_user_logged_in() ) {
        error_log( '[Calendar Pro] USER NOT LOGGED IN' );
        calendar_ajaxStatus( 'error', __( 'You must be logged in to create a calendar.', 'wproject-calendar-pro' ) );
        return;
    }

    $calendar_data = array(
        'name'        => isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '',
        'description' => isset( $_POST['description'] ) ? sanitize_textarea_field( $_POST['description'] ) : '',
        'color'       => isset( $_POST['color'] ) ? sanitize_hex_color( $_POST['color'] ) : '#00bcd4',
        'visibility'  => isset( $_POST['visibility'] ) ? sanitize_text_field( $_POST['visibility'] ) : 'private'
    );

    error_log( '[Calendar Pro] Sanitized calendar data: ' . json_encode( $calendar_data ) );

    $calendar_id = WProject_Calendar_Manager::create_calendar( $calendar_data );

    if ( $calendar_id ) {
        error_log( '[Calendar Pro AJAX] Calendar created successfully: Calendar ID=' . $calendar_id . ', Name=' . $calendar_data['name'] );
        calendar_ajaxStatus( 'success', __( 'Calendar created successfully', 'wproject-calendar-pro' ), array(
            'calendar_id' => $calendar_id
        ) );
    } else {
        error_log( '[Calendar Pro AJAX] Calendar creation failed - check class-calendar-manager.php debug.log for details. Data: ' . json_encode( $calendar_data ) );
        calendar_ajaxStatus( 'error', __( 'Failed to create calendar', 'wproject-calendar-pro' ) );
    }

    error_log( '=== CALENDAR CREATION AJAX END ===' );
    return;
}

/**
 * Update calendar
 * Security: Checks user owns or has edit permission for calendar
 */
add_action( 'wp_ajax_calendar_pro_update_calendar', 'calendar_pro_update_calendar' );
function calendar_pro_update_calendar() {
    // Verify nonce
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'calendar_inputs' ) ) {
        calendar_ajaxStatus( 'error', __( 'Nonce check failed.', 'wproject-calendar-pro' ) );
        return;
    }

    $calendar_id = isset( $_POST['calendar_id'] ) ? (int) $_POST['calendar_id'] : 0;

    // Check user can edit this calendar
    if ( ! WProject_Calendar_Permissions::user_can_edit_calendar( $calendar_id ) ) {
        calendar_ajaxStatus( 'error', __( 'Permission denied. You cannot edit this calendar.', 'wproject-calendar-pro' ) );
        return;
    }

    $calendar_data = array();

    if ( isset( $_POST['name'] ) ) {
        $calendar_data['name'] = sanitize_text_field( $_POST['name'] );
    }
    if ( isset( $_POST['description'] ) ) {
        $calendar_data['description'] = sanitize_textarea_field( $_POST['description'] );
    }
    if ( isset( $_POST['color'] ) ) {
        $calendar_data['color'] = sanitize_hex_color( $_POST['color'] );
    }
    if ( isset( $_POST['visibility'] ) ) {
        $calendar_data['visibility'] = sanitize_text_field( $_POST['visibility'] );
    }

    if ( WProject_Calendar_Manager::update_calendar( $calendar_id, $calendar_data ) ) {
        error_log( '[Calendar Pro AJAX] Calendar updated successfully: Calendar ID=' . $calendar_id );
        calendar_ajaxStatus( 'success', __( 'Calendar updated successfully', 'wproject-calendar-pro' ) );
    } else {
        error_log( '[Calendar Pro AJAX] Calendar update failed for Calendar ID=' . $calendar_id );
        calendar_ajaxStatus( 'error', __( 'Failed to update calendar', 'wproject-calendar-pro' ) );
    }
    return;
}

/**
 * Delete calendar
 * Security: Only calendar owner can delete
 */
add_action( 'wp_ajax_calendar_pro_delete_calendar', 'calendar_pro_delete_calendar' );
function calendar_pro_delete_calendar() {
    // Verify nonce
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'calendar_inputs' ) ) {
        calendar_ajaxStatus( 'error', __( 'Nonce check failed.', 'wproject-calendar-pro' ) );
        return;
    }

    $calendar_id = isset( $_POST['calendar_id'] ) ? (int) $_POST['calendar_id'] : 0;

    // Check user can delete this calendar
    if ( ! WProject_Calendar_Permissions::user_can_delete_calendar( $calendar_id ) ) {
        calendar_ajaxStatus( 'error', __( 'Permission denied. You cannot delete this calendar.', 'wproject-calendar-pro' ) );
        return;
    }

    if ( WProject_Calendar_Manager::delete_calendar( $calendar_id ) ) {
        error_log( '[Calendar Pro AJAX] Calendar deleted successfully: Calendar ID=' . $calendar_id );
        calendar_ajaxStatus( 'success', __( 'Calendar deleted successfully', 'wproject-calendar-pro' ) );
    } else {
        error_log( '[Calendar Pro AJAX] Calendar delete failed for Calendar ID=' . $calendar_id );
        calendar_ajaxStatus( 'error', __( 'Failed to delete calendar', 'wproject-calendar-pro' ) );
    }
    return;
}

/**
 * Share calendar
 * Security: Only calendar owner can share
 */
add_action( 'wp_ajax_calendar_pro_share_calendar', 'calendar_pro_share_calendar' );
function calendar_pro_share_calendar() {
    // Verify nonce
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'calendar_inputs' ) ) {
        calendar_ajaxStatus( 'error', __( 'Nonce check failed.', 'wproject-calendar-pro' ) );
        return;
    }

    $calendar_id = isset( $_POST['calendar_id'] ) ? (int) $_POST['calendar_id'] : 0;
    $user_id = isset( $_POST['user_id'] ) ? (int) $_POST['user_id'] : 0;
    $permission = isset( $_POST['permission'] ) ? sanitize_text_field( $_POST['permission'] ) : 'view';

    // Check user owns calendar
    if ( ! WProject_Calendar_Permissions::user_can_delete_calendar( $calendar_id ) ) {
        calendar_ajaxStatus( 'error', __( 'Permission denied. Only calendar owner can share.', 'wproject-calendar-pro' ) );
        return;
    }

    if ( $user_id ) {
        $share_id = WProject_Calendar_Manager::share_calendar( $calendar_id, $user_id, $permission );
        if ( $share_id ) {
            error_log( '[Calendar Pro AJAX] Calendar shared successfully: Calendar ID=' . $calendar_id . ', User ID=' . $user_id . ', Permission=' . $permission );
            calendar_ajaxStatus( 'success', __( 'Calendar shared successfully', 'wproject-calendar-pro' ) );
        } else {
            error_log( '[Calendar Pro AJAX] Calendar share failed: Calendar ID=' . $calendar_id . ', User ID=' . $user_id );
            calendar_ajaxStatus( 'error', __( 'Failed to share calendar', 'wproject-calendar-pro' ) );
        }
    } else {
        error_log( '[Calendar Pro AJAX] Share calendar called with invalid User ID' );
        calendar_ajaxStatus( 'error', __( 'Invalid user ID', 'wproject-calendar-pro' ) );
    }
    return;
}

/**
 * Get user's calendars
 * Security: Only returns calendars user owns or has access to
 */
add_action( 'wp_ajax_calendar_pro_get_user_calendars', 'calendar_pro_get_user_calendars' );
function calendar_pro_get_user_calendars() {
    // Verify nonce
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'calendar_inputs' ) ) {
        calendar_ajaxStatus( 'error', __( 'Nonce check failed.', 'wproject-calendar-pro' ) );
        return;
    }

    $user_id = get_current_user_id();

    if ( ! $user_id ) {
        calendar_ajaxStatus( 'error', __( 'You must be logged in.', 'wproject-calendar-pro' ) );
        return;
    }

    // Get all accessible calendars for user
    $calendars = WProject_Calendar_Manager::get_accessible_calendars( $user_id );

    calendar_ajaxStatus( 'success', __( 'Calendars retrieved', 'wproject-calendar-pro' ), $calendars );
    return;
}

/**
 * Get single event for editing
 * Security: Only returns event if user can access it
 */
add_action( 'wp_ajax_calendar_pro_get_event', 'calendar_pro_get_event' );
function calendar_pro_get_event() {
    // Verify nonce
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'calendar_inputs' ) ) {
        calendar_ajaxStatus( 'error', __( 'Nonce check failed.', 'wproject-calendar-pro' ) );
        return;
    }

    $event_id = isset( $_POST['event_id'] ) ? (int) $_POST['event_id'] : 0;

    if ( ! $event_id ) {
        calendar_ajaxStatus( 'error', __( 'Invalid event ID.', 'wproject-calendar-pro' ) );
        return;
    }

    // Check user can access this event
    if ( ! WProject_Calendar_Permissions::user_can_access_event( $event_id ) ) {
        calendar_ajaxStatus( 'error', __( 'Access denied.', 'wproject-calendar-pro' ) );
        return;
    }

    // Get event
    $event = WProject_Event_Manager::get_event( $event_id );

    if ( ! $event ) {
        calendar_ajaxStatus( 'error', __( 'Event not found.', 'wproject-calendar-pro' ) );
        return;
    }

    // Get attendees
    $attendees = WProject_Event_Manager::get_event_attendees( $event_id );

    calendar_ajaxStatus( 'success', __( 'Event retrieved', 'wproject-calendar-pro' ), array(
        'event' => $event,
        'attendees' => $attendees
    ) );
    return;
}

/**
 * Get calendar event count
 * Security: Only returns count if user can access calendar
 */
add_action( 'wp_ajax_calendar_pro_get_calendar_event_count', 'calendar_pro_get_calendar_event_count' );
function calendar_pro_get_calendar_event_count() {
    // Verify nonce
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'calendar_inputs' ) ) {
        calendar_ajaxStatus( 'error', __( 'Nonce check failed.', 'wproject-calendar-pro' ) );
        return;
    }

    $calendar_id = isset( $_POST['calendar_id'] ) ? (int) $_POST['calendar_id'] : 0;

    // Check user can access this calendar
    if ( ! WProject_Calendar_Permissions::user_can_access_calendar( $calendar_id ) ) {
        calendar_ajaxStatus( 'error', __( 'Access denied.', 'wproject-calendar-pro' ) );
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'wproject_events';

    $count = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE calendar_id = %d",
        $calendar_id
    ) );

    error_log( "[Calendar Pro] Event count for calendar {$calendar_id}: {$count}" );

    calendar_ajaxStatus( 'success', __( 'Event count retrieved', 'wproject-calendar-pro' ), array(
        'count' => (int) $count
    ) );
    return;
}

/**
 * Delete calendar with options (transfer or delete events)
 * Security: Only calendar owner can delete
 */
add_action( 'wp_ajax_calendar_pro_delete_calendar_with_options', 'calendar_pro_delete_calendar_with_options' );
function calendar_pro_delete_calendar_with_options() {
    // Verify nonce
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'calendar_inputs' ) ) {
        calendar_ajaxStatus( 'error', __( 'Nonce check failed.', 'wproject-calendar-pro' ) );
        return;
    }

    $calendar_id = isset( $_POST['calendar_id'] ) ? (int) $_POST['calendar_id'] : 0;
    $delete_option = isset( $_POST['delete_option'] ) ? sanitize_text_field( $_POST['delete_option'] ) : 'transfer';

    // Check user can delete this calendar
    if ( ! WProject_Calendar_Permissions::user_can_delete_calendar( $calendar_id ) ) {
        calendar_ajaxStatus( 'error', __( 'Permission denied. Only calendar owner can delete.', 'wproject-calendar-pro' ) );
        return;
    }

    global $wpdb;

    // Get calendar info
    $calendar = WProject_Calendar_Manager::get_calendar( $calendar_id );
    if ( ! $calendar ) {
        calendar_ajaxStatus( 'error', __( 'Calendar not found.', 'wproject-calendar-pro' ) );
        return;
    }

    // Prevent deletion of default calendar
    if ( $calendar->name === 'Personal' || $calendar->is_default == 1 ) {
        calendar_ajaxStatus( 'error', __( 'Cannot delete default calendar.', 'wproject-calendar-pro' ) );
        return;
    }

    $user_id = get_current_user_id();
    $events_table = $wpdb->prefix . 'wproject_events';
    $attendees_table = $wpdb->prefix . 'wproject_event_attendees';
    $calendars_table = $wpdb->prefix . 'wproject_calendars';

    if ( $delete_option === 'transfer' ) {
        // Get user's default calendar
        $default_calendar = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $calendars_table WHERE owner_id = %d AND (name = 'Personal' OR is_default = 1) LIMIT 1",
            $user_id
        ) );

        // If no default calendar exists, create one
        if ( ! $default_calendar ) {
            $wpdb->insert(
                $calendars_table,
                array(
                    'name' => 'Personal',
                    'description' => 'Personal calendar',
                    'color' => '#00bcd4',
                    'owner_id' => $user_id,
                    'visibility' => 'private',
                    'is_default' => 1,
                    'created_at' => current_time( 'mysql' )
                ),
                array( '%s', '%s', '%s', '%d', '%s', '%d', '%s' )
            );
            $default_calendar_id = $wpdb->insert_id;
        } else {
            $default_calendar_id = $default_calendar->id;
        }

        // Transfer all events to default calendar
        $updated = $wpdb->update(
            $events_table,
            array( 'calendar_id' => $default_calendar_id ),
            array( 'calendar_id' => $calendar_id ),
            array( '%d' ),
            array( '%d' )
        );

        error_log( '[Calendar Pro] Transferred ' . $updated . ' events from calendar ' . $calendar_id . ' to default calendar ' . $default_calendar_id );
    } else {
        // Delete all events and related data
        $event_ids = $wpdb->get_col( $wpdb->prepare(
            "SELECT id FROM $events_table WHERE calendar_id = %d",
            $calendar_id
        ) );

        if ( ! empty( $event_ids ) ) {
            $placeholders = implode( ',', array_fill( 0, count( $event_ids ), '%d' ) );

            // Delete attendees
            $wpdb->query( $wpdb->prepare(
                "DELETE FROM $attendees_table WHERE event_id IN ($placeholders)",
                $event_ids
            ) );

            // Delete events
            $wpdb->query( $wpdb->prepare(
                "DELETE FROM $events_table WHERE calendar_id = %d",
                $calendar_id
            ) );

            error_log( '[Calendar Pro] Deleted ' . count( $event_ids ) . ' events and attendees from calendar ' . $calendar_id );
        }
    }

    // Delete calendar shares
    $shares_table = $wpdb->prefix . 'wproject_calendar_sharing';
    $wpdb->delete( $shares_table, array( 'calendar_id' => $calendar_id ), array( '%d' ) );

    // Delete the calendar
    $deleted = $wpdb->delete( $calendars_table, array( 'id' => $calendar_id ), array( '%d' ) );

    if ( $deleted ) {
        error_log( '[Calendar Pro] Calendar deleted successfully: Calendar ID=' . $calendar_id . ', Option=' . $delete_option );
        calendar_ajaxStatus( 'success', __( 'Calendar deleted successfully', 'wproject-calendar-pro' ) );
    } else {
        error_log( '[Calendar Pro] Calendar deletion failed: Calendar ID=' . $calendar_id );
        calendar_ajaxStatus( 'error', __( 'Failed to delete calendar', 'wproject-calendar-pro' ) );
    }
    return;
}
