<?php
/**
 * Calendar Manager Class
 *
 * Handles calendar CRUD operations and permissions
 *
 * @package wProject Calendar Pro
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WProject_Calendar_Manager {

    /**
     * Create a new calendar
     *
     * @param array $calendar_data Calendar data
     * @return int|false Calendar ID or false on failure
     */
    public static function create_calendar( $calendar_data ) {
        global $wpdb;

        $table_calendars = $wpdb->prefix . 'wproject_calendars';

        $defaults = array(
            'name'        => '',
            'description' => '',
            'color'       => '#00bcd4',
            'owner_id'    => get_current_user_id(),
            'is_default'  => 0,
            'is_shared'   => 0,
            'visibility'  => 'private',
            'created_at'  => current_time( 'mysql' ),
            'updated_at'  => current_time( 'mysql' )
        );

        $calendar_data = wp_parse_args( $calendar_data, $defaults );

        // Validate required fields
        if ( empty( $calendar_data['name'] ) ) {
            return false;
        }

        $result = $wpdb->insert(
            $table_calendars,
            $calendar_data,
            array( '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s' )
        );

        if ( $result ) {
            $calendar_id = $wpdb->insert_id;
            do_action( 'calendar_pro_calendar_created', $calendar_id, $calendar_data );
            return $calendar_id;
        }

        return false;
    }

    /**
     * Update a calendar
     *
     * @param int $calendar_id Calendar ID
     * @param array $calendar_data Calendar data
     * @return bool Success or failure
     */
    public static function update_calendar( $calendar_id, $calendar_data ) {
        global $wpdb;

        $table_calendars = $wpdb->prefix . 'wproject_calendars';

        // Add updated timestamp
        $calendar_data['updated_at'] = current_time( 'mysql' );

        $result = $wpdb->update(
            $table_calendars,
            $calendar_data,
            array( 'id' => $calendar_id ),
            null,
            array( '%d' )
        );

        if ( $result !== false ) {
            do_action( 'calendar_pro_calendar_updated', $calendar_id, $calendar_data );
            return true;
        }

        return false;
    }

    /**
     * Delete a calendar
     *
     * @param int $calendar_id Calendar ID
     * @return bool Success or failure
     */
    public static function delete_calendar( $calendar_id ) {
        global $wpdb;

        // Prevent deletion of default calendars
        $calendar = WProject_Calendar_Core::get_user_default_calendar();
        if ( $calendar && $calendar->id == $calendar_id && $calendar->is_default ) {
            return false;
        }

        $table_calendars = $wpdb->prefix . 'wproject_calendars';
        $table_events = $wpdb->prefix . 'wproject_events';
        $table_sharing = $wpdb->prefix . 'wproject_calendar_sharing';

        // Delete all events in this calendar
        $events = WProject_Event_Manager::get_calendar_events( $calendar_id );
        foreach ( $events as $event ) {
            WProject_Event_Manager::delete_event( $event->id );
        }

        // Delete sharing entries
        $wpdb->delete( $table_sharing, array( 'calendar_id' => $calendar_id ), array( '%d' ) );

        // Delete calendar
        $result = $wpdb->delete( $table_calendars, array( 'id' => $calendar_id ), array( '%d' ) );

        if ( $result ) {
            do_action( 'calendar_pro_calendar_deleted', $calendar_id );
            return true;
        }

        return false;
    }

    /**
     * Get calendar by ID
     *
     * @param int $calendar_id Calendar ID
     * @return object|null Calendar object or null
     */
    public static function get_calendar( $calendar_id ) {
        global $wpdb;

        $table_calendars = $wpdb->prefix . 'wproject_calendars';

        $calendar = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table_calendars WHERE id = %d",
            $calendar_id
        ) );

        return $calendar;
    }

    /**
     * Share calendar with user
     *
     * @param int $calendar_id Calendar ID
     * @param int $user_id User ID to share with
     * @param string $permission Permission level (view, edit)
     * @return int|false Sharing ID or false on failure
     */
    public static function share_calendar( $calendar_id, $user_id, $permission = 'view' ) {
        global $wpdb;

        $table_sharing = $wpdb->prefix . 'wproject_calendar_sharing';

        // Check if already shared
        $existing = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM $table_sharing WHERE calendar_id = %d AND shared_with_user_id = %d",
            $calendar_id,
            $user_id
        ) );

        if ( $existing ) {
            // Update permission
            $wpdb->update(
                $table_sharing,
                array( 'permission' => $permission ),
                array( 'id' => $existing ),
                array( '%s' ),
                array( '%d' )
            );
            return $existing;
        }

        $result = $wpdb->insert(
            $table_sharing,
            array(
                'calendar_id'          => $calendar_id,
                'shared_with_user_id'  => $user_id,
                'shared_with_team'     => 0,
                'permission'           => $permission,
                'created_at'           => current_time( 'mysql' )
            ),
            array( '%d', '%d', '%d', '%s', '%s' )
        );

        if ( $result ) {
            do_action( 'calendar_pro_calendar_shared', $wpdb->insert_id, $calendar_id, $user_id, $permission );
            return $wpdb->insert_id;
        }

        return false;
    }

    /**
     * Share calendar with team
     *
     * @param int $calendar_id Calendar ID
     * @param string $permission Permission level (view, edit)
     * @return int|false Sharing ID or false on failure
     */
    public static function share_with_team( $calendar_id, $permission = 'view' ) {
        global $wpdb;

        $table_sharing = $wpdb->prefix . 'wproject_calendar_sharing';

        // Check if already shared with team
        $existing = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM $table_sharing WHERE calendar_id = %d AND shared_with_team = 1",
            $calendar_id
        ) );

        if ( $existing ) {
            // Update permission
            $wpdb->update(
                $table_sharing,
                array( 'permission' => $permission ),
                array( 'id' => $existing ),
                array( '%s' ),
                array( '%d' )
            );
            return $existing;
        }

        $result = $wpdb->insert(
            $table_sharing,
            array(
                'calendar_id'          => $calendar_id,
                'shared_with_user_id'  => null,
                'shared_with_team'     => 1,
                'permission'           => $permission,
                'created_at'           => current_time( 'mysql' )
            ),
            array( '%d', '%d', '%d', '%s', '%s' )
        );

        if ( $result ) {
            do_action( 'calendar_pro_calendar_shared_team', $wpdb->insert_id, $calendar_id, $permission );
            return $wpdb->insert_id;
        }

        return false;
    }

    /**
     * Unshare calendar
     *
     * @param int $calendar_id Calendar ID
     * @param int $user_id User ID (optional, null for team)
     * @return bool Success or failure
     */
    public static function unshare_calendar( $calendar_id, $user_id = null ) {
        global $wpdb;

        $table_sharing = $wpdb->prefix . 'wproject_calendar_sharing';

        if ( $user_id ) {
            $result = $wpdb->delete(
                $table_sharing,
                array(
                    'calendar_id'          => $calendar_id,
                    'shared_with_user_id'  => $user_id
                ),
                array( '%d', '%d' )
            );
        } else {
            $result = $wpdb->delete(
                $table_sharing,
                array(
                    'calendar_id'       => $calendar_id,
                    'shared_with_team'  => 1
                ),
                array( '%d', '%d' )
            );
        }

        if ( $result ) {
            do_action( 'calendar_pro_calendar_unshared', $calendar_id, $user_id );
            return true;
        }

        return false;
    }

    /**
     * Get users who have access to calendar
     *
     * @param int $calendar_id Calendar ID
     * @return array Array of user objects with permission data
     */
    public static function get_calendar_shared_users( $calendar_id ) {
        global $wpdb;

        $table_sharing = $wpdb->prefix . 'wproject_calendar_sharing';

        $shared_users = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM $table_sharing WHERE calendar_id = %d AND shared_with_user_id IS NOT NULL",
            $calendar_id
        ) );

        // Enrich with user data
        foreach ( $shared_users as &$share ) {
            $user = get_userdata( $share->shared_with_user_id );
            if ( $user ) {
                $share->user_name = $user->display_name;
                $share->user_email = $user->user_email;
            }
        }

        return $shared_users;
    }

    /**
     * Check if calendar is shared with team
     *
     * @param int $calendar_id Calendar ID
     * @return bool
     */
    public static function is_shared_with_team( $calendar_id ) {
        global $wpdb;

        $table_sharing = $wpdb->prefix . 'wproject_calendar_sharing';

        $count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_sharing WHERE calendar_id = %d AND shared_with_team = 1",
            $calendar_id
        ) );

        return (bool) $count;
    }

    /**
     * Get all calendars accessible to a user
     *
     * @param int $user_id User ID
     * @return array Array of calendar objects
     */
    public static function get_accessible_calendars( $user_id = 0 ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        // Get owned calendars
        $owned = WProject_Calendar_Core::get_user_calendars( $user_id );

        // Get shared calendars
        $shared = WProject_Calendar_Core::get_shared_calendars( $user_id );

        // Merge and return
        return array_merge( $owned, $shared );
    }

    /**
     * Duplicate calendar
     *
     * @param int $calendar_id Calendar ID
     * @param bool $include_events Include events
     * @return int|false New calendar ID or false on failure
     */
    public static function duplicate_calendar( $calendar_id, $include_events = false ) {
        $calendar = self::get_calendar( $calendar_id );

        if ( ! $calendar ) {
            return false;
        }

        $new_calendar_data = array(
            'name'        => $calendar->name . ' ' . __( '(Copy)', 'wproject-calendar-pro' ),
            'description' => $calendar->description,
            'color'       => $calendar->color,
            'owner_id'    => get_current_user_id(),
            'is_default'  => 0,
            'visibility'  => $calendar->visibility
        );

        $new_calendar_id = self::create_calendar( $new_calendar_data );

        if ( $new_calendar_id && $include_events ) {
            $events = WProject_Event_Manager::get_calendar_events( $calendar_id );

            foreach ( $events as $event ) {
                $event_data = (array) $event;
                unset( $event_data['id'] );
                $event_data['calendar_id'] = $new_calendar_id;
                $event_data['owner_id'] = get_current_user_id();
                $event_data['created_by'] = get_current_user_id();

                WProject_Event_Manager::create_event( $event_data );
            }
        }

        return $new_calendar_id;
    }
}
