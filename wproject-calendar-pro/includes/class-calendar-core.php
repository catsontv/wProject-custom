<?php
/**
 * Calendar Pro Core Class
 *
 * Handles database creation, plugin initialization, and core functionality
 *
 * @package wProject Calendar Pro
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WProject_Calendar_Core {

    /**
     * Single instance of the class
     *
     * @var WProject_Calendar_Core
     */
    private static $instance = null;

    /**
     * Get single instance
     *
     * @return WProject_Calendar_Core
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action( 'init', array( $this, 'register_post_types' ) );
        add_action( 'init', array( $this, 'register_taxonomies' ) );
    }

    /**
     * Activation - Create database tables
     */
    public static function activate() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        /* Calendar table */
        $table_calendars = $wpdb->prefix . 'wproject_calendars';
        $sql_calendars = "CREATE TABLE IF NOT EXISTS $table_calendars (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            color varchar(7) DEFAULT '#00bcd4',
            owner_id bigint(20) NOT NULL,
            is_default tinyint(1) DEFAULT 0,
            is_shared tinyint(1) DEFAULT 0,
            visibility enum('private','team','public') DEFAULT 'private',
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY owner_id (owner_id),
            KEY is_default (is_default)
        ) $charset_collate;";

        /* Events table */
        $table_events = $wpdb->prefix . 'wproject_events';
        $sql_events = "CREATE TABLE IF NOT EXISTS $table_events (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            calendar_id bigint(20) NOT NULL,
            title varchar(255) NOT NULL,
            description text,
            location varchar(255),
            start_datetime datetime NOT NULL,
            end_datetime datetime NOT NULL,
            all_day tinyint(1) DEFAULT 0,
            event_type enum('event','meeting','task','deadline','reminder') DEFAULT 'event',
            color varchar(7),
            owner_id bigint(20) NOT NULL,
            created_by bigint(20) NOT NULL,
            project_id bigint(20),
            task_id bigint(20),
            is_recurring tinyint(1) DEFAULT 0,
            recurrence_parent_id bigint(20),
            recurrence_rule text,
            status enum('scheduled','completed','cancelled') DEFAULT 'scheduled',
            visibility enum('private','team','public') DEFAULT 'private',
            reminder_enabled tinyint(1) DEFAULT 0,
            reminder_minutes int(11) DEFAULT 15,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY calendar_id (calendar_id),
            KEY owner_id (owner_id),
            KEY project_id (project_id),
            KEY task_id (task_id),
            KEY start_datetime (start_datetime),
            KEY end_datetime (end_datetime),
            KEY is_recurring (is_recurring),
            KEY recurrence_parent_id (recurrence_parent_id)
        ) $charset_collate;";

        /* Event attendees table */
        $table_attendees = $wpdb->prefix . 'wproject_event_attendees';
        $sql_attendees = "CREATE TABLE IF NOT EXISTS $table_attendees (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            event_id bigint(20) NOT NULL,
            user_id bigint(20) NOT NULL,
            status enum('pending','accepted','declined','tentative') DEFAULT 'pending',
            can_edit tinyint(1) DEFAULT 0,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY event_id (event_id),
            KEY user_id (user_id),
            UNIQUE KEY event_user (event_id, user_id)
        ) $charset_collate;";

        /* Calendar sharing table */
        $table_sharing = $wpdb->prefix . 'wproject_calendar_sharing';
        $sql_sharing = "CREATE TABLE IF NOT EXISTS $table_sharing (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            calendar_id bigint(20) NOT NULL,
            shared_with_user_id bigint(20),
            shared_with_team tinyint(1) DEFAULT 0,
            permission enum('view','edit') DEFAULT 'view',
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY calendar_id (calendar_id),
            KEY shared_with_user_id (shared_with_user_id)
        ) $charset_collate;";

        /* Reminders table */
        $table_reminders = $wpdb->prefix . 'wproject_event_reminders';
        $sql_reminders = "CREATE TABLE IF NOT EXISTS $table_reminders (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            event_id bigint(20) NOT NULL,
            user_id bigint(20) NOT NULL,
            reminder_datetime datetime NOT NULL,
            sent tinyint(1) DEFAULT 0,
            sent_at datetime,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY event_id (event_id),
            KEY user_id (user_id),
            KEY reminder_datetime (reminder_datetime),
            KEY sent (sent)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql_calendars );
        dbDelta( $sql_events );
        dbDelta( $sql_attendees );
        dbDelta( $sql_sharing );
        dbDelta( $sql_reminders );

        /* Set version option */
        update_option( 'calendar_pro_db_version', '1.0.0' );

        /* Create default calendar for existing users */
        self::create_default_calendars();
    }

    /**
     * Deactivation
     */
    public static function deactivate() {
        // Clean up scheduled events
        wp_clear_scheduled_hook( 'calendar_pro_send_reminders' );
        wp_clear_scheduled_hook( 'calendar_pro_cleanup_old_events' );
    }

    /**
     * Create default calendars for existing users
     */
    private static function create_default_calendars() {
        global $wpdb;

        $users = get_users( array(
            'fields' => array( 'ID' ),
            'role__not_in' => array( 'client' )
        ) );

        $table_calendars = $wpdb->prefix . 'wproject_calendars';

        foreach ( $users as $user ) {
            // Check if user already has a default calendar
            $has_default = $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM $table_calendars WHERE owner_id = %d AND is_default = 1",
                $user->ID
            ) );

            if ( ! $has_default ) {
                $user_info = get_userdata( $user->ID );
                $calendar_name = sprintf( __( '%s\'s Calendar', 'wproject-calendar-pro' ), $user_info->display_name );

                $wpdb->insert(
                    $table_calendars,
                    array(
                        'name'        => $calendar_name,
                        'description' => __( 'Default calendar', 'wproject-calendar-pro' ),
                        'color'       => '#00bcd4',
                        'owner_id'    => $user->ID,
                        'is_default'  => 1,
                        'visibility'  => 'private',
                        'created_at'  => current_time( 'mysql' ),
                        'updated_at'  => current_time( 'mysql' )
                    ),
                    array( '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s' )
                );
            }
        }
    }

    /**
     * Register custom post types
     */
    public function register_post_types() {
        // Reserved for future expansion if needed
        // Events are stored in custom tables for better performance
    }

    /**
     * Register custom taxonomies
     */
    public function register_taxonomies() {
        // Reserved for future expansion if needed
    }

    /**
     * Get user's default calendar
     *
     * @param int $user_id User ID
     * @return object|null Calendar object or null
     */
    public static function get_user_default_calendar( $user_id = 0 ) {
        global $wpdb;

        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        $table_calendars = $wpdb->prefix . 'wproject_calendars';

        $calendar = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table_calendars WHERE owner_id = %d AND is_default = 1 LIMIT 1",
            $user_id
        ) );

        return $calendar;
    }

    /**
     * Get all calendars for a user
     *
     * @param int $user_id User ID
     * @return array Array of calendar objects
     */
    public static function get_user_calendars( $user_id = 0 ) {
        global $wpdb;

        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        $table_calendars = $wpdb->prefix . 'wproject_calendars';

        $calendars = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM $table_calendars WHERE owner_id = %d ORDER BY is_default DESC, name ASC",
            $user_id
        ) );

        return $calendars;
    }

    /**
     * Get shared calendars for a user
     *
     * @param int $user_id User ID
     * @return array Array of calendar objects
     */
    public static function get_shared_calendars( $user_id = 0 ) {
        global $wpdb;

        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        $table_calendars = $wpdb->prefix . 'wproject_calendars';
        $table_sharing = $wpdb->prefix . 'wproject_calendar_sharing';

        $calendars = $wpdb->get_results( $wpdb->prepare(
            "SELECT c.*, s.permission
            FROM $table_calendars c
            INNER JOIN $table_sharing s ON c.id = s.calendar_id
            WHERE s.shared_with_user_id = %d OR s.shared_with_team = 1
            ORDER BY c.name ASC",
            $user_id
        ) );

        return $calendars;
    }

    /**
     * Check if user can view calendar
     *
     * @param int $calendar_id Calendar ID
     * @param int $user_id User ID
     * @return bool
     */
    public static function can_view_calendar( $calendar_id, $user_id = 0 ) {
        global $wpdb;

        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        $table_calendars = $wpdb->prefix . 'wproject_calendars';
        $table_sharing = $wpdb->prefix . 'wproject_calendar_sharing';

        // Check if owner
        $is_owner = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_calendars WHERE id = %d AND owner_id = %d",
            $calendar_id,
            $user_id
        ) );

        if ( $is_owner ) {
            return true;
        }

        // Check if shared
        $is_shared = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_sharing WHERE calendar_id = %d AND shared_with_user_id = %d",
            $calendar_id,
            $user_id
        ) );

        return (bool) $is_shared;
    }

    /**
     * Check if user can edit calendar
     *
     * @param int $calendar_id Calendar ID
     * @param int $user_id User ID
     * @return bool
     */
    public static function can_edit_calendar( $calendar_id, $user_id = 0 ) {
        global $wpdb;

        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        $table_calendars = $wpdb->prefix . 'wproject_calendars';
        $table_sharing = $wpdb->prefix . 'wproject_calendar_sharing';

        // Check if owner
        $is_owner = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_calendars WHERE id = %d AND owner_id = %d",
            $calendar_id,
            $user_id
        ) );

        if ( $is_owner ) {
            return true;
        }

        // Check if shared with edit permission
        $has_edit_permission = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_sharing WHERE calendar_id = %d AND shared_with_user_id = %d AND permission = 'edit'",
            $calendar_id,
            $user_id
        ) );

        return (bool) $has_edit_permission;
    }
}
