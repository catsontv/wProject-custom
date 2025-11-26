<?php
/**
 * Calendar View Template
 *
 * Main calendar display template
 *
 * @package wProject Calendar Pro
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$options = get_option( 'wproject_settings' );
$default_view = isset($options['calendar_default_view']) ? $options['calendar_default_view'] : 'month';
$week_start = isset($options['calendar_week_start']) ? $options['calendar_week_start'] : '0';
$time_format = isset($options['calendar_time_format']) ? $options['calendar_time_format'] : '24';

// Map view names to FullCalendar view names
$view_map = array(
    'month' => 'dayGridMonth',
    'week'  => 'timeGridWeek',
    'day'   => 'timeGridDay',
    'list'  => 'listWeek'
);
$fullcalendar_view = isset($view_map[$default_view]) ? $view_map[$default_view] : 'dayGridMonth';

// Get user's calendars
$user_calendars = WProject_Calendar_Core::get_user_calendars();
$shared_calendars = WProject_Calendar_Core::get_shared_calendars();
$all_calendars = array_merge($user_calendars, $shared_calendars);
?>

<div class="calendar-pro-wrapper">

    <div class="calendar-header">
        <div class="calendar-header-left">
            <h2><?php _e( 'Calendar', 'wproject-calendar-pro' ); ?></h2>

            <?php if ( ! empty( $all_calendars ) ) : ?>
            <div class="calendar-selector">
                <select id="calendar-selector">
                    <option value=""><?php _e( 'All Calendars', 'wproject-calendar-pro' ); ?></option>
                    <?php foreach ( $all_calendars as $calendar ) : ?>
                        <option value="<?php echo esc_attr( $calendar->id ); ?>">
                            <?php echo esc_html( $calendar->name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
        </div>

        <div class="calendar-header-right">
            <button class="btn btn-primary btn-new-event">
                <i data-feather="plus"></i>
                <?php _e( 'New Event', 'wproject-calendar-pro' ); ?>
            </button>
        </div>
    </div>

    <div id="calendar-pro"
         data-default-view="<?php echo esc_attr( $fullcalendar_view ); ?>"
         data-week-start="<?php echo esc_attr( $week_start ); ?>"
         data-time-format="<?php echo esc_attr( $time_format ); ?>">
    </div>

</div>

<?php
// Include event modal
include CALENDAR_PRO_PLUGIN_PATH . 'templates/event-form.php';

// Include event detail modal
include CALENDAR_PRO_PLUGIN_PATH . 'templates/event-detail.php';
?>

<script>
// Initialize Feather icons
if (typeof feather !== 'undefined') {
    feather.replace();
}
</script>
