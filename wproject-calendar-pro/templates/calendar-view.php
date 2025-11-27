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

// Check if we're in project context (variables set by calendar_pro_display_project())
$is_project_context = isset($project_id) && $project_id !== null;
?>

<div class="calendar-pro-wrapper">

    <div class="calendar-header">
        <div class="calendar-header-left">
            <h2>
                <?php _e( 'Calendar', 'wproject-calendar-pro' ); ?>
                <?php if ( $is_project_context ) : ?>
                    <span class="project-context-indicator" style="font-size: 0.85em; color: #666; font-weight: normal;">
                        &mdash; <?php echo esc_html( $project_name ); ?>
                    </span>
                <?php endif; ?>
            </h2>

            <?php if ( ! $is_project_context && ! empty( $all_calendars ) ) : ?>
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
            <?php elseif ( $is_project_context ) : ?>
            <div class="project-calendar-info" style="padding: 8px 12px; background: #e3f2fd; border-radius: 4px; font-size: 0.9em;">
                <i data-feather="filter" style="width: 14px; height: 14px;"></i>
                <?php _e( 'Showing events for this project only', 'wproject-calendar-pro' ); ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="calendar-header-right">
            <button class="btn btn-secondary btn-manage-calendars" style="display: inline-block !important;">
                <i data-feather="settings"></i>
                <span><?php _e( 'Manage Calendars', 'wproject-calendar-pro' ); ?></span>
            </button>
            <button class="btn btn-secondary btn-new-calendar">
                <i data-feather="plus"></i>
                <?php _e( 'New Calendar', 'wproject-calendar-pro' ); ?>
            </button>
            <button class="btn btn-primary btn-new-event">
                <i data-feather="plus"></i>
                <?php _e( 'New Event', 'wproject-calendar-pro' ); ?>
            </button>
        </div>
    </div>

    <!-- Calendar Management Panel -->
    <div id="calendar-management-panel" class="calendar-management-panel" style="display: none;">
        <div class="management-panel-header">
            <h3><?php _e( 'My Calendars', 'wproject-calendar-pro' ); ?></h3>
            <button class="btn-close-panel" aria-label="Close">&times;</button>
        </div>
        <div class="management-panel-body">
            <?php if ( ! empty( $user_calendars ) ) : ?>
                <div class="calendars-list">
                    <?php foreach ( $user_calendars as $calendar ) :
                        $is_default = ( $calendar->name === 'Personal' || $calendar->is_default == 1 );
                    ?>
                        <div class="calendar-item" data-calendar-id="<?php echo esc_attr( $calendar->id ); ?>">
                            <div class="calendar-info">
                                <span class="calendar-color" style="background-color: <?php echo esc_attr( $calendar->color ); ?>"></span>
                                <div class="calendar-details">
                                    <strong><?php echo esc_html( $calendar->name ); ?></strong>
                                    <?php if ( $is_default ) : ?>
                                        <span class="calendar-badge"><?php _e( 'Default', 'wproject-calendar-pro' ); ?></span>
                                    <?php endif; ?>
                                    <?php if ( $calendar->description ) : ?>
                                        <p class="calendar-description"><?php echo esc_html( $calendar->description ); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="calendar-actions">
                                <?php if ( ! $is_default ) : ?>
                                    <button class="btn btn-sm btn-delete-calendar"
                                            data-calendar-id="<?php echo esc_attr( $calendar->id ); ?>"
                                            data-calendar-name="<?php echo esc_attr( $calendar->name ); ?>">
                                        <i data-feather="trash-2"></i>
                                        <?php _e( 'Delete', 'wproject-calendar-pro' ); ?>
                                    </button>
                                <?php else : ?>
                                    <span class="calendar-note"><?php _e( 'Cannot delete default calendar', 'wproject-calendar-pro' ); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <p class="no-calendars"><?php _e( 'You don\'t have any calendars yet.', 'wproject-calendar-pro' ); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div id="calendar-pro"
         data-default-view="<?php echo esc_attr( $fullcalendar_view ); ?>"
         data-week-start="<?php echo esc_attr( $week_start ); ?>"
         data-time-format="<?php echo esc_attr( $time_format ); ?>"
         <?php if ( $is_project_context ) : ?>
         data-project-id="<?php echo esc_attr( $project_id ); ?>"
         data-project-name="<?php echo esc_attr( $project_name ); ?>"
         <?php endif; ?>>
    </div>

</div>

<?php
// Include calendar creation modal
include CALENDAR_PRO_PLUGIN_PATH . 'templates/calendar-form.php';

// Include calendar delete modal
include CALENDAR_PRO_PLUGIN_PATH . 'templates/calendar-delete-modal.php';

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
