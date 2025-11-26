<?php
/**
 * Uninstall Script
 *
 * Fired when the plugin is uninstalled
 *
 * @package wProject Calendar Pro
 * @since 1.0.0
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

// Delete tables
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wproject_calendars" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wproject_events" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wproject_event_attendees" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wproject_calendar_sharing" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wproject_event_reminders" );

// Delete options
delete_option( 'calendar_pro_db_version' );

// Delete all calendar share tokens
$wpdb->query( "DELETE FROM {$wpdb->prefix}options WHERE option_name LIKE 'calendar_share_token_%'" );

// Delete wProject settings related to calendar
$options = get_option( 'wproject_settings' );
if ( $options ) {
    unset( $options['calendar_show_dashboard'] );
    unset( $options['calendar_show_project'] );
    unset( $options['calendar_default_view'] );
    unset( $options['calendar_week_start'] );
    unset( $options['calendar_time_format'] );
    unset( $options['calendar_enable_reminders'] );
    unset( $options['calendar_default_reminder'] );
    unset( $options['calendar_enable_recurring'] );
    unset( $options['calendar_enable_sharing'] );
    unset( $options['calendar_cleanup_days'] );
    unset( $options['calendar_event_color'] );
    unset( $options['calendar_meeting_color'] );
    unset( $options['calendar_deadline_color'] );
    update_option( 'wproject_settings', $options );
}

// Clear scheduled events
wp_clear_scheduled_hook( 'calendar_pro_send_reminders' );
wp_clear_scheduled_hook( 'calendar_pro_cleanup_old_events' );
