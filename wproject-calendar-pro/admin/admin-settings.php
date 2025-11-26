<?php
/**
 * Admin Settings Panel
 *
 * Integrates into wProject admin settings
 *
 * @package wProject Calendar Pro
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$options = get_option( 'wproject_settings' );

/* Calendar options */
$calendar_show_dashboard        = isset($options['calendar_show_dashboard']) ? $options['calendar_show_dashboard'] : '';
$calendar_show_project          = isset($options['calendar_show_project']) ? $options['calendar_show_project'] : '';
$calendar_default_view          = isset($options['calendar_default_view']) ? $options['calendar_default_view'] : 'month';
$calendar_week_start            = isset($options['calendar_week_start']) ? $options['calendar_week_start'] : '0';
$calendar_time_format           = isset($options['calendar_time_format']) ? $options['calendar_time_format'] : '24';
$calendar_enable_reminders      = isset($options['calendar_enable_reminders']) ? $options['calendar_enable_reminders'] : '';
$calendar_default_reminder      = isset($options['calendar_default_reminder']) ? $options['calendar_default_reminder'] : '15';
$calendar_enable_recurring      = isset($options['calendar_enable_recurring']) ? $options['calendar_enable_recurring'] : '';
$calendar_enable_sharing        = isset($options['calendar_enable_sharing']) ? $options['calendar_enable_sharing'] : '';
$calendar_cleanup_days          = isset($options['calendar_cleanup_days']) ? $options['calendar_cleanup_days'] : '30';
$calendar_event_color           = isset($options['calendar_event_color']) ? $options['calendar_event_color'] : '#00bcd4';
$calendar_meeting_color         = isset($options['calendar_meeting_color']) ? $options['calendar_meeting_color'] : '#9c27b0';
$calendar_deadline_color        = isset($options['calendar_deadline_color']) ? $options['calendar_deadline_color'] : '#ff5722';

// Enqueue color picker
wp_enqueue_style( 'wp-color-picker' );
wp_enqueue_script( 'calendar-settings-handle', CALENDAR_PRO_PLUGIN_URL . 'assets/js/calendar-admin.js', array( 'wp-color-picker' ), CALENDAR_PRO_VERSION, true );

$button = '<input name="submit" class="button" value="' . __( 'Save Settings', 'wproject-calendar-pro' ) . '" type="submit" />';
?>

<!--/ Start Calendar Pro / -->
<div class="settings-div calendar-pro">

    <h3><?php _e( 'Calendar Pro', 'wproject-calendar-pro' ); ?> <span>v<?php echo calendarPluginVersion(); ?></span></h3>

    <!--/ Start Version Check Notice / -->
    <?php
        $required_theme_version = '5.7.0';
        $update_link = admin_url() . 'themes.php?theme=wproject';
        if (version_compare(wproject_theme_version_check_calendar(), $required_theme_version) < 0) { ?>
            <div class="wproject-notice">
                <strong><?php printf( __('wProject Calendar Pro requires at least wProject %1$s. <a href="%2$s" rel="noopener">Update your theme now</a>.', 'wproject-calendar-pro'), $required_theme_version, $update_link); ?></strong>
            </div>
    <?php } ?>
    <!--/ End Version Check Notice / -->

    <hr />

    <!--/ Display Options / -->
    <div class="fleft">
        <p>
            <?php _e( 'Show calendar', 'wproject-calendar-pro' ); ?>
            <span><?php _e( 'Where to display the calendar interface.', 'wproject-calendar-pro' ); ?></span>
        </p>
    </div>

    <div class="fright">
        <ul>
            <li>
                <input type="checkbox" name="wproject_settings[calendar_show_dashboard]" <?php if ( $calendar_show_dashboard ) { ?>checked<?php } ?> />
                <?php _e( 'Dashboard', 'wproject-calendar-pro' ); ?>
            </li>
            <li>
                <input type="checkbox" name="wproject_settings[calendar_show_project]" <?php if ( $calendar_show_project ) { ?>checked<?php } ?> />
                <?php _e( 'Individual projects', 'wproject-calendar-pro' ); ?>
            </li>
        </ul>
    </div>

    <hr />

    <!--/ Default View / -->
    <div class="fleft">
        <p>
            <?php _e( 'Default view', 'wproject-calendar-pro' ); ?>
            <span><?php _e( 'The initial calendar view when loading the page.', 'wproject-calendar-pro' ); ?></span>
        </p>
    </div>

    <div class="fright">
        <ul>
            <li>
                <input type="radio" name="wproject_settings[calendar_default_view]" value="month" <?php if ( $calendar_default_view == 'month' ) { ?>checked<?php } ?> />
                <?php _e( 'Month', 'wproject-calendar-pro' ); ?>
            </li>
            <li>
                <input type="radio" name="wproject_settings[calendar_default_view]" value="week" <?php if ( $calendar_default_view == 'week' ) { ?>checked<?php } ?> />
                <?php _e( 'Week', 'wproject-calendar-pro' ); ?>
            </li>
            <li>
                <input type="radio" name="wproject_settings[calendar_default_view]" value="day" <?php if ( $calendar_default_view == 'day' ) { ?>checked<?php } ?> />
                <?php _e( 'Day', 'wproject-calendar-pro' ); ?>
            </li>
            <li>
                <input type="radio" name="wproject_settings[calendar_default_view]" value="list" <?php if ( $calendar_default_view == 'list' ) { ?>checked<?php } ?> />
                <?php _e( 'List', 'wproject-calendar-pro' ); ?>
            </li>
        </ul>
    </div>

    <hr />

    <!--/ Week Start Day / -->
    <div class="fleft">
        <p>
            <?php _e( 'Week starts on', 'wproject-calendar-pro' ); ?>
            <span><?php _e( 'The first day of the week in calendar view.', 'wproject-calendar-pro' ); ?></span>
        </p>
    </div>

    <div class="fright">
        <select name="wproject_settings[calendar_week_start]">
            <option value="0" <?php selected( $calendar_week_start, '0' ); ?>><?php _e( 'Sunday', 'wproject-calendar-pro' ); ?></option>
            <option value="1" <?php selected( $calendar_week_start, '1' ); ?>><?php _e( 'Monday', 'wproject-calendar-pro' ); ?></option>
            <option value="6" <?php selected( $calendar_week_start, '6' ); ?>><?php _e( 'Saturday', 'wproject-calendar-pro' ); ?></option>
        </select>
    </div>

    <hr />

    <!--/ Time Format / -->
    <div class="fleft">
        <p>
            <?php _e( 'Time format', 'wproject-calendar-pro' ); ?>
            <span><?php _e( '12 hour or 24 hour time format.', 'wproject-calendar-pro' ); ?></span>
        </p>
    </div>

    <div class="fright">
        <ul>
            <li>
                <input type="radio" name="wproject_settings[calendar_time_format]" value="12" <?php if ( $calendar_time_format == '12' ) { ?>checked<?php } ?> />
                <?php _e( '12 hour (2:00 PM)', 'wproject-calendar-pro' ); ?>
            </li>
            <li>
                <input type="radio" name="wproject_settings[calendar_time_format]" value="24" <?php if ( $calendar_time_format == '24' ) { ?>checked<?php } ?> />
                <?php _e( '24 hour (14:00)', 'wproject-calendar-pro' ); ?>
            </li>
        </ul>
    </div>

    <hr />

    <!--/ Reminders / -->
    <div class="fleft">
        <p>
            <?php _e( 'Event reminders', 'wproject-calendar-pro' ); ?>
            <span><?php _e( 'Enable email reminders for events.', 'wproject-calendar-pro' ); ?></span>
        </p>
    </div>

    <div class="fright">
        <ul>
            <li>
                <input type="checkbox" name="wproject_settings[calendar_enable_reminders]" <?php if ( $calendar_enable_reminders ) { ?>checked<?php } ?> />
                <?php _e( 'Enable reminders', 'wproject-calendar-pro' ); ?>
            </li>
        </ul>
        <p class="sublabel">
            <?php _e( 'Default reminder time:', 'wproject-calendar-pro' ); ?>
            <select name="wproject_settings[calendar_default_reminder]">
                <option value="5" <?php selected( $calendar_default_reminder, '5' ); ?>>5 <?php _e( 'minutes', 'wproject-calendar-pro' ); ?></option>
                <option value="15" <?php selected( $calendar_default_reminder, '15' ); ?>>15 <?php _e( 'minutes', 'wproject-calendar-pro' ); ?></option>
                <option value="30" <?php selected( $calendar_default_reminder, '30' ); ?>>30 <?php _e( 'minutes', 'wproject-calendar-pro' ); ?></option>
                <option value="60" <?php selected( $calendar_default_reminder, '60' ); ?>>1 <?php _e( 'hour', 'wproject-calendar-pro' ); ?></option>
                <option value="120" <?php selected( $calendar_default_reminder, '120' ); ?>>2 <?php _e( 'hours', 'wproject-calendar-pro' ); ?></option>
                <option value="1440" <?php selected( $calendar_default_reminder, '1440' ); ?>>1 <?php _e( 'day', 'wproject-calendar-pro' ); ?></option>
            </select>
        </p>
    </div>

    <hr />

    <!--/ Features / -->
    <div class="fleft">
        <p>
            <?php _e( 'Features', 'wproject-calendar-pro' ); ?>
            <span><?php _e( 'Enable or disable calendar features.', 'wproject-calendar-pro' ); ?></span>
        </p>
    </div>

    <div class="fright">
        <ul>
            <li>
                <input type="checkbox" name="wproject_settings[calendar_enable_recurring]" <?php if ( $calendar_enable_recurring ) { ?>checked<?php } ?> />
                <?php _e( 'Recurring events', 'wproject-calendar-pro' ); ?>
            </li>
            <li>
                <input type="checkbox" name="wproject_settings[calendar_enable_sharing]" <?php if ( $calendar_enable_sharing ) { ?>checked<?php } ?> />
                <?php _e( 'Calendar sharing', 'wproject-calendar-pro' ); ?>
            </li>
        </ul>
    </div>

    <hr />

    <!--/ Event Colors / -->
    <div class="fleft">
        <p>
            <?php _e( 'Default event colors', 'wproject-calendar-pro' ); ?>
            <span><?php _e( 'Default colors for different event types.', 'wproject-calendar-pro' ); ?></span>
        </p>
    </div>

    <div class="fright">
        <p class="sublabel">
            <?php _e( 'Event:', 'wproject-calendar-pro' ); ?>
            <input type="text" name="wproject_settings[calendar_event_color]" value="<?php echo esc_attr( $calendar_event_color ); ?>" class="colour-picker" />
        </p>
        <p class="sublabel">
            <?php _e( 'Meeting:', 'wproject-calendar-pro' ); ?>
            <input type="text" name="wproject_settings[calendar_meeting_color]" value="<?php echo esc_attr( $calendar_meeting_color ); ?>" class="colour-picker" />
        </p>
        <p class="sublabel">
            <?php _e( 'Deadline:', 'wproject-calendar-pro' ); ?>
            <input type="text" name="wproject_settings[calendar_deadline_color]" value="<?php echo esc_attr( $calendar_deadline_color ); ?>" class="colour-picker" />
        </p>
    </div>

    <hr />

    <!--/ Cleanup / -->
    <div class="fleft">
        <p>
            <?php _e( 'Auto cleanup', 'wproject-calendar-pro' ); ?>
            <span><?php _e( 'Automatically delete cancelled events older than specified days.', 'wproject-calendar-pro' ); ?></span>
        </p>
    </div>

    <div class="fright">
        <input type="number" name="wproject_settings[calendar_cleanup_days]" value="<?php echo esc_attr( $calendar_cleanup_days ); ?>" min="0" max="365" />
        <?php _e( 'days', 'wproject-calendar-pro' ); ?>
        <p class="sublabel"><?php _e( 'Set to 0 to disable auto cleanup.', 'wproject-calendar-pro' ); ?></p>
    </div>

    <hr />

    <?php echo $button; ?>

</div>
<!--/ End Calendar Pro / -->
