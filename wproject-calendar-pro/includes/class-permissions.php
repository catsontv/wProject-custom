<?php
/**
 * Permissions & Authorization Class - SECURITY FIXED
 *
 * Handles permission checking for calendar and event operations
 * PHASE-1A-SECURITY-FIXES: Missing permission checks added
 *
 * @package wProject Calendar Pro
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WProject_Calendar_Permissions {

    /**
     * Check if user can edit an event
     *
     * @param int $event_id Event ID
     * @param int $user_id User ID (optional, defaults to current user)
     * @return bool True if user can edit, false otherwise
     */
    public static function user_can_edit_event( $event_id, $user_id = 0 ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        // Not logged in
        if ( ! $user_id ) {
            return false;
        }

        $event = WProject_Event_Manager::get_event( $event_id );

        if ( ! $event ) {
            return false;
        }

        // Owner can edit
        if ( (int) $event->owner_id === (int) $user_id ) {
            return true;
        }

        // Attendee with edit permission can edit
        $attendee = self::get_attendee_permission( $event_id, $user_id );
        if ( $attendee && (int) $attendee->can_edit === 1 ) {
            return true;
        }

        // Calendar owner can edit events in their calendar
        $calendar = WProject_Calendar_Manager::get_calendar( $event->calendar_id );
        if ( $calendar && (int) $calendar->owner_id === (int) $user_id ) {
            return true;
        }

        // Shared calendar with edit permission
        if ( self::user_can_edit_calendar( $event->calendar_id, $user_id ) ) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can view/access an event
     *
     * @param int $event_id Event ID
     * @param int $user_id User ID (optional, defaults to current user)
     * @return bool True if user can access, false otherwise
     */
    public static function user_can_access_event( $event_id, $user_id = 0 ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        // Not logged in
        if ( ! $user_id ) {
            return false;
        }

        $event = WProject_Event_Manager::get_event( $event_id );

        if ( ! $event ) {
            return false;
        }

        // Public events - everyone can access
        if ( $event->visibility === 'public' ) {
            return true;
        }

        // Owner can access
        if ( (int) $event->owner_id === (int) $user_id ) {
            return true;
        }

        // User is attendee
        $attendee = self::get_attendee_permission( $event_id, $user_id );
        if ( $attendee ) {
            return true;
        }

        // Calendar owner can access events in their calendar
        $calendar = WProject_Calendar_Manager::get_calendar( $event->calendar_id );
        if ( $calendar && (int) $calendar->owner_id === (int) $user_id ) {
            return true;
        }

        // User has access to the calendar
        if ( self::user_can_access_calendar( $event->calendar_id, $user_id ) ) {
            return true;
        }

        // Team visibility check
        if ( $event->visibility === 'team' ) {
            if ( self::user_is_team_member( $user_id, $event->project_id ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user can delete an event
     *
     * @param int $event_id Event ID
     * @param int $user_id User ID (optional, defaults to current user)
     * @return bool True if user can delete, false otherwise
     */
    public static function user_can_delete_event( $event_id, $user_id = 0 ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        $event = WProject_Event_Manager::get_event( $event_id );

        if ( ! $event ) {
            return false;
        }

        // Only owner can delete
        if ( (int) $event->owner_id === (int) $user_id ) {
            return true;
        }

        // Calendar owner can delete events in their calendar
        $calendar = WProject_Calendar_Manager::get_calendar( $event->calendar_id );
        if ( $calendar && (int) $calendar->owner_id === (int) $user_id ) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can edit a calendar
     *
     * @param int $calendar_id Calendar ID
     * @param int $user_id User ID (optional, defaults to current user)
     * @return bool True if user can edit, false otherwise
     */
    public static function user_can_edit_calendar( $calendar_id, $user_id = 0 ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        if ( ! $user_id ) {
            return false;
        }

        $calendar = WProject_Calendar_Manager::get_calendar( $calendar_id );

        if ( ! $calendar ) {
            return false;
        }

        // Owner can edit
        if ( (int) $calendar->owner_id === (int) $user_id ) {
            return true;
        }

        // Shared with edit permission
        return self::has_calendar_share_permission( $calendar_id, $user_id, 'edit' );
    }

    /**
     * Check if user can access a calendar
     *
     * @param int $calendar_id Calendar ID
     * @param int $user_id User ID (optional, defaults to current user)
     * @return bool True if user can access, false otherwise
     */
    public static function user_can_access_calendar( $calendar_id, $user_id = 0 ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        if ( ! $user_id ) {
            return false;
        }

        $calendar = WProject_Calendar_Manager::get_calendar( $calendar_id );

        if ( ! $calendar ) {
            return false;
        }

        // Owner can access
        if ( (int) $calendar->owner_id === (int) $user_id ) {
            return true;
        }

        // Public calendars - everyone can access
        if ( $calendar->visibility === 'public' ) {
            return true;
        }

        // Check if shared with user
        return self::has_calendar_share_permission( $calendar_id, $user_id, 'view' );
    }

    /**
     * Check if user can delete a calendar
     *
     * @param int $calendar_id Calendar ID
     * @param int $user_id User ID (optional, defaults to current user)
     * @return bool True if user can delete, false otherwise
     */
    public static function user_can_delete_calendar( $calendar_id, $user_id = 0 ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        $calendar = WProject_Calendar_Manager::get_calendar( $calendar_id );

        if ( ! $calendar ) {
            return false;
        }

        // Only owner can delete
        if ( (int) $calendar->owner_id === (int) $user_id ) {
            return true;
        }

        return false;
    }

    /**
     * Get attendee permission details
     *
     * @param int $event_id Event ID
     * @param int $user_id User ID
     * @return object|null Attendee object or null
     */
    private static function get_attendee_permission( $event_id, $user_id ) {
        global $wpdb;

        $table_attendees = $wpdb->prefix . 'wproject_event_attendees';

        $attendee = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table_attendees WHERE event_id = %d AND user_id = %d",
            $event_id,
            $user_id
        ) );

        return $attendee;
    }

    /**
     * Check calendar sharing permission
     *
     * @param int $calendar_id Calendar ID
     * @param int $user_id User ID
     * @param string $permission Permission level (view, edit)
     * @return bool True if user has permission
     */
    private static function has_calendar_share_permission( $calendar_id, $user_id, $permission = 'view' ) {
        global $wpdb;

        $table_sharing = $wpdb->prefix . 'wproject_calendar_sharing';

        // Check direct user share
        $share = $wpdb->get_var( $wpdb->prepare(
            "SELECT permission FROM $table_sharing
             WHERE calendar_id = %d AND shared_with_user_id = %d",
            $calendar_id,
            $user_id
        ) );

        if ( $share ) {
            return ( $share === 'edit' || $share === $permission );
        }

        // Check team share
        $team_share = $wpdb->get_var( $wpdb->prepare(
            "SELECT permission FROM $table_sharing
             WHERE calendar_id = %d AND shared_with_team = 1",
            $calendar_id
        ) );

        if ( $team_share && self::user_is_team_member( $user_id ) ) {
            return ( $team_share === 'edit' || $team_share === $permission );
        }

        return false;
    }

    /**
     * Check if user is a team member
     *
     * @param int $user_id User ID
     * @param int $project_id Optional project ID
     * @return bool True if user is team member
     */
    private static function user_is_team_member( $user_id, $project_id = 0 ) {
        // Check if user has any role that indicates team membership
        $user = get_userdata( $user_id );

        if ( ! $user ) {
            return false;
        }

        $team_roles = array(
            'administrator',
            'project_manager',
            'team_member',
            'operator'
        );

        foreach ( $team_roles as $role ) {
            if ( $user->has_cap( $role ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user can create calendar
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @return bool True if user can create calendar
     */
    public static function user_can_create_calendar( $user_id = 0 ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        if ( ! $user_id ) {
            return false;
        }

        // All authenticated users can create calendars
        return true;
    }

    /**
     * Check if user can create event
     *
     * @param int $calendar_id Calendar ID
     * @param int $user_id User ID (optional, defaults to current user)
     * @return bool True if user can create event in calendar
     */
    public static function user_can_create_event( $calendar_id, $user_id = 0 ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        if ( ! $user_id ) {
            return false;
        }

        // Can create if owns the calendar or has edit permission
        return self::user_can_edit_calendar( $calendar_id, $user_id );
    }
}
