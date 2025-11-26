<?php
/**
 * AJAX Handlers
 *
 * Handles all AJAX requests for calendar operations
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
function calendar_ajaxStatus($status, $message, $data = array()) {
    $response = array(
        'status'  => $status,
        'message' => $message,
        'data'    => $data
    );
    wp_send_json($response);
    exit; // Explicit exit for clarity
}

/* Get calendar events */
add_action( 'wp_ajax_calendar_pro_get_events', 'calendar_pro_get_events' );
function calendar_pro_get_events() {
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'calendar_inputs' ) ) {
        calendar_ajaxStatus( 'error', __( 'Nonce check failed.', 'wproject-calendar-pro' ) );
    }

    $calendar_id = isset( $_POST['calendar_id'] ) ? (int) $_POST['calendar_id'] : 0;
    $start_date = isset( $_POST['start'] ) ? sanitize_text_field( $_POST['start'] ) : null;
    $end_date = isset( $_POST['end'] ) ? sanitize_text_field( $_POST['end'] ) : null;

    if ( $calendar_id ) {
        // Check if user has access to this calendar
        if ( ! WProject_Calendar_Manager::user_can_access_calendar( $calendar_id, get_current_user_id() ) ) {
            calendar_ajaxStatus( 'error', __( 'Permission denied.', 'wproject-calendar-pro' ) );
        }
        $events = WProject_Event_Manager::get_calendar_events( $calendar_id, $start_date, $end_date );
    } else {
        $events = WProject_Event_Manager::get_user_events( get_current_user_id(), $start_date, $end_date );
    }

    // Convert to FullCalendar format
    $formatted_events = array();
    foreach ( $events as $event ) {
        $formatted_events[] = WProject_Event_Manager::to_fullcalendar_format( $event );
    }

    calendar_ajaxStatus( 'success', __( 'Events retrieved', 'wproject-calendar-pro' ), $formatted_events );
}

/* Create event */
add_action( 'wp_ajax_calendar_pro_create_event', 'calendar_pro_create_event' );
function calendar_pro_create_event() {
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'calendar_inputs' ) ) {
        calendar_ajaxStatus( 'error', __( 'Nonce check failed.', 'wproject-calendar-pro' ) );
    }

    $calendar_id = isset( $_POST['calendar_id'] ) ? (int) $_POST['calendar_id'] : 0;
    
    // Check if user can create events in this calendar
    if ( ! WProject_Calendar_Manager::user_can_access_calendar( $calendar_id, get_current_user_id() ) ) {
        calendar_ajaxStatus( 'error', __( 'Permission denied.', 'wproject-calendar-pro' ) );
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
        'reminder_minutes' => isset( $_POST['reminder_minutes'] ) ? (int) $_POST['reminder_minutes'] : 15
    );

    $event_id = WProject_Event_Manager::create_event( $event_data );

    if ( $event_id ) {
        // Add attendees if provided
        if ( isset( $_POST['attendees'] ) && is_array( $_POST['attendees'] ) ) {
            foreach ( $_POST['attendees'] as $user_id ) {
                WProject_Event_Manager::add_attendee( $event_id, (int) $user_id, 'pending', false );
            }
        }

        $event = WProject_Event_Manager::get_event( $event_id );
        calendar_ajaxStatus( 'success', __( 'Event created successfully', 'wproject-calendar-pro' ), array(
            'event' => WProject_Event_Manager::to_fullcalendar_format( $event )
        ) );
    } else {
        calendar_ajaxStatus( 'error', __( 'Failed to create event', 'wproject-calendar-pro' ) );
    }
}

/* Update event */
add_action( 'wp_ajax_calendar_pro_update_event', 'calendar_pro_update_event' );
function calendar_pro_update_event() {
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'calendar_inputs' ) ) {
        calendar_ajaxStatus( 'error', __( 'Nonce check failed.', 'wproject-calendar-pro' ) );
    }

    $event_id = isset( $_POST['event_id'] ) ? (int) $_POST['event_id'] : 0;
    
    // Check event ownership or edit permission
    $event = WProject_Event_Manager::get_event( $event_id );
    if ( ! $event ) {
        calendar_ajaxStatus( 'error', __( 'Event not found.', 'wproject-calendar-pro' ) );
    }
    
    $current_user = get_current_user_id();
    $can_edit = ( $event->owner_id == $current_user ) || 
                WProject_Event_Manager::user_can_edit_event( $event_id, $current_user );
    
    if ( ! $can_edit ) {
        calendar_ajaxStatus( 'error', __( 'Permission denied.', 'wproject-calendar-pro' ) );
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

    if ( WProject_Event_Manager::update_event( $event_id, $event_data ) ) {
        $event = WProject_Event_Manager::get_event( $event_id );
        calendar_ajaxStatus( 'success', __( 'Event updated successfully', 'wproject-calendar-pro' ), array(
            'event' => WProject_Event_Manager::to_fullcalendar_format( $event )
        ) );
    } else {
        calendar_ajaxStatus( 'error', __( 'Failed to update event', 'wproject-calendar-pro' ) );
    }
}

/* Delete event */
add_action( 'wp_ajax_calendar_pro_delete_event', 'calendar_pro_delete_event' );
function calendar_pro_delete_event() {
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'calendar_inputs' ) ) {
        calendar_ajaxStatus( 'error', __( 'Nonce check failed.', 'wproject-calendar-pro' ) );
    }

    $event_id = isset( $_POST['event_id'] ) ? (int) $_POST['event_id'] : 0;
    
    // Check event ownership
    $event = WProject_Event_Manager::get_event( $event_id );
    if ( ! $event ) {
        calendar_ajaxStatus( 'error', __( 'Event not found.', 'wproject-calendar-pro' ) );
    }
    
    $current_user = get_current_user_id();
    // Only owner or calendar owner can delete
    $calendar = WProject_Calendar_Manager::get_calendar( $event->calendar_id );
    $can_delete = ( $event->owner_id == $current_user ) || ( $calendar && $calendar->owner_id == $current_user );
    
    if ( ! $can_delete ) {
        calendar_ajaxStatus( 'error', __( 'Permission denied.', 'wproject-calendar-pro' ) );
    }

    if ( WProject_Event_Manager::delete_event( $event_id ) ) {
        calendar_ajaxStatus( 'success', __( 'Event deleted successfully', 'wproject-calendar-pro' ) );
    } else {
        calendar_ajaxStatus( 'error', __( 'Failed to delete event', 'wproject-calendar-pro' ) );
    }
}

/* Create calendar */
add_action( 'wp_ajax_calendar_pro_create_calendar', 'calendar_pro_create_calendar' );
function calendar_pro_create_calendar() {
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'calendar_inputs' ) ) {
        calendar_ajaxStatus( 'error', __( 'Nonce check failed.', 'wproject-calendar-pro' ) );
    }

    // Verify user has calendar creation capability (logged in users can create calendars)
    if ( ! is_user_logged_in() ) {
        calendar_ajaxStatus( 'error', __( 'Permission denied.', 'wproject-calendar-pro' ) );
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
}

/* Update calendar */
add_action( 'wp_ajax_calendar_pro_update_calendar', 'calendar_pro_update_calendar' );
function calendar_pro_update_calendar() {
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'calendar_inputs' ) ) {
        calendar_ajaxStatus( 'error', __( 'Nonce check failed.', 'wproject-calendar-pro' ) );
    }

    $calendar_id = isset( $_POST['calendar_id'] ) ? (int) $_POST['calendar_id'] : 0;
    
    // Check calendar ownership
    $calendar = WProject_Calendar_Manager::get_calendar( $calendar_id );
    if ( ! $calendar ) {
        calendar_ajaxStatus( 'error', __( 'Calendar not found.', 'wproject-calendar-pro' ) );
    }
    
    if ( $calendar->owner_id != get_current_user_id() ) {
        calendar_ajaxStatus( 'error', __( 'Permission denied.', 'wproject-calendar-pro' ) );
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
}

/* Delete calendar */
add_action( 'wp_ajax_calendar_pro_delete_calendar', 'calendar_pro_delete_calendar' );
function calendar_pro_delete_calendar() {
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'calendar_inputs' ) ) {
        calendar_ajaxStatus( 'error', __( 'Nonce check failed.', 'wproject-calendar-pro' ) );
    }

    $calendar_id = isset( $_POST['calendar_id'] ) ? (int) $_POST['calendar_id'] : 0;
    
    // Check calendar ownership
    $calendar = WProject_Calendar_Manager::get_calendar( $calendar_id );
    if ( ! $calendar ) {
        calendar_ajaxStatus( 'error', __( 'Calendar not found.', 'wproject-calendar-pro' ) );
    }
    
    if ( $calendar->owner_id != get_current_user_id() ) {
        calendar_ajaxStatus( 'error', __( 'Permission denied.', 'wproject-calendar-pro' ) );
    }

    if ( WProject_Calendar_Manager::delete_calendar( $calendar_id ) ) {
        calendar_ajaxStatus( 'success', __( 'Calendar deleted successfully', 'wproject-calendar-pro' ) );
    } else {
        calendar_ajaxStatus( 'error', __( 'Cannot delete default calendar', 'wproject-calendar-pro' ) );
    }
}

/* Update attendee status */
add_action( 'wp_ajax_calendar_pro_update_attendee_status', 'calendar_pro_update_attendee_status' );
function calendar_pro_update_attendee_status() {
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'calendar_inputs' ) ) {
        calendar_ajaxStatus( 'error', __( 'Nonce check failed.', 'wproject-calendar-pro' ) );
    }

    $event_id = isset( $_POST['event_id'] ) ? (int) $_POST['event_id'] : 0;
    $status = isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : 'pending';

    if ( WProject_Event_Manager::update_attendee_status( $event_id, get_current_user_id(), $status ) ) {
        calendar_ajaxStatus( 'success', __( 'Status updated successfully', 'wproject-calendar-pro' ) );
    } else {
        calendar_ajaxStatus( 'error', __( 'Failed to update status', 'wproject-calendar-pro' ) );
    }
}

/* Share calendar */
add_action( 'wp_ajax_calendar_pro_share_calendar', 'calendar_pro_share_calendar' );
function calendar_pro_share_calendar() {
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'calendar_inputs' ) ) {
        calendar_ajaxStatus( 'error', __( 'Nonce check failed.', 'wproject-calendar-pro' ) );
    }

    $calendar_id = isset( $_POST['calendar_id'] ) ? (int) $_POST['calendar_id'] : 0;
    
    // Check calendar ownership
    $calendar = WProject_Calendar_Manager::get_calendar( $calendar_id );
    if ( ! $calendar ) {
        calendar_ajaxStatus( 'error', __( 'Calendar not found.', 'wproject-calendar-pro' ) );
    }
    
    if ( $calendar->owner_id != get_current_user_id() ) {
        calendar_ajaxStatus( 'error', __( 'Permission denied.', 'wproject-calendar-pro' ) );
    }
    
    $user_ids = isset( $_POST['user_ids'] ) && is_array( $_POST['user_ids'] ) ? array_map( 'intval', $_POST['user_ids'] ) : array();
    $permission = isset( $_POST['permission'] ) ? sanitize_text_field( $_POST['permission'] ) : 'view';

    $count = 0;
    foreach ( $user_ids as $user_id ) {
        if ( WProject_Calendar_Manager::share_calendar( $calendar_id, $user_id, $permission ) ) {
            $count++;
        }
    }

    if ( $count > 0 ) {
        calendar_ajaxStatus( 'success', sprintf( __( 'Calendar shared with %d users', 'wproject-calendar-pro' ), $count ) );
    } else {
        calendar_ajaxStatus( 'error', __( 'Failed to share calendar', 'wproject-calendar-pro' ) );
    }
}
