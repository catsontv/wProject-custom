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
    $start_date = isset( $_POST['start'] ) ? sanitize_text_field( $_POST['start'] ) : null;
    $end_date = isset( $_POST['end'] ) ? sanitize_text_field( $_POST['end'] ) : null;

    if ( $calendar_id ) {
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
        calendar_ajaxStatus( 'success', __( 'Event deleted successfully', 'wproject-calendar-pro' ) );
    } else {
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
    // Verify nonce
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'calendar_inputs' ) ) {
        calendar_ajaxStatus( 'error', __( 'Nonce check failed.', 'wproject-calendar-pro' ) );
        return;
    }

    // Check user is logged in
    if ( ! is_user_logged_in() ) {
        calendar_ajaxStatus( 'error', __( 'You must be logged in to create a calendar.', 'wproject-calendar-pro' ) );
        return;
    }

    $calendar_data = array(
        'name'        => isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '',
        'description' => isset( $_POST['description'] ) ? sanitize_textarea_field( $_POST['description'] ) : '',
        'color'       => isset( $_POST['color'] ) ? sanitize_hex_color( $_POST['color'] ) : '#00bcd4',
        'visibility'  => isset( $_POST['visibility'] ) ? sanitize_text_field( $_POST['visibility'] ) : 'private'
    );

    $calendar_id = WProject_Calendar_Manager::create_calendar( $calendar_data );

    if ( $calendar_id ) {
        calendar_ajaxStatus( 'success', __( 'Calendar created successfully', 'wproject-calendar-pro' ), array(
            'calendar_id' => $calendar_id
        ) );
    } else {
        calendar_ajaxStatus( 'error', __( 'Failed to create calendar', 'wproject-calendar-pro' ) );
    }
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
        calendar_ajaxStatus( 'success', __( 'Calendar updated successfully', 'wproject-calendar-pro' ) );
    } else {
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
        calendar_ajaxStatus( 'success', __( 'Calendar deleted successfully', 'wproject-calendar-pro' ) );
    } else {
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
            calendar_ajaxStatus( 'success', __( 'Calendar shared successfully', 'wproject-calendar-pro' ) );
        } else {
            calendar_ajaxStatus( 'error', __( 'Failed to share calendar', 'wproject-calendar-pro' ) );
        }
    } else {
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
