<?php
/**
 * Event Form Template
 *
 * Modal form for creating/editing events
 *
 * @package wProject Calendar Pro
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$options = get_option( 'wproject_settings' );
$event_color = isset($options['calendar_event_color']) ? $options['calendar_event_color'] : '#00bcd4';
$meeting_color = isset($options['calendar_meeting_color']) ? $options['calendar_meeting_color'] : '#9c27b0';
$deadline_color = isset($options['calendar_deadline_color']) ? $options['calendar_deadline_color'] : '#ff5722';
$default_reminder = isset($options['calendar_default_reminder']) ? $options['calendar_default_reminder'] : '15';
$enable_reminders = isset($options['calendar_enable_reminders']) ? $options['calendar_enable_reminders'] : false;

// Get user's calendars for dropdown
$user_calendars = WProject_Calendar_Core::get_user_calendars();
$shared_calendars = WProject_Calendar_Core::get_shared_calendars();
?>

<div id="event-modal" class="calendar-modal">
    <div class="calendar-modal-content">
        <div class="calendar-modal-header">
            <h3><?php _e( 'Event Details', 'wproject-calendar-pro' ); ?></h3>
            <button class="calendar-modal-close">&times;</button>
        </div>

        <div class="calendar-modal-body">
            <form id="event-form">
                <input type="hidden" id="event-id" name="event_id" value="">

                <div class="calendar-form-group">
                    <label for="event-title"><?php _e( 'Title', 'wproject-calendar-pro' ); ?> *</label>
                    <input type="text" id="event-title" name="title" required>
                </div>

                <div class="calendar-form-group">
                    <label for="event-calendar"><?php _e( 'Calendar', 'wproject-calendar-pro' ); ?> *</label>
                    <select id="event-calendar" name="calendar_id" required>
                        <?php if ( ! empty( $user_calendars ) ) : ?>
                            <optgroup label="<?php esc_attr_e( 'My Calendars', 'wproject-calendar-pro' ); ?>">
                                <?php foreach ( $user_calendars as $calendar ) : ?>
                                    <option value="<?php echo esc_attr( $calendar->id ); ?>"
                                            <?php echo isset($calendar->is_default) && $calendar->is_default ? 'data-default="1"' : ''; ?>>
                                        <?php echo esc_html( $calendar->name ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endif; ?>
                        
                        <?php if ( ! empty( $shared_calendars ) ) : ?>
                            <optgroup label="<?php esc_attr_e( 'Shared Calendars', 'wproject-calendar-pro' ); ?>">
                                <?php foreach ( $shared_calendars as $calendar ) : ?>
                                    <option value="<?php echo esc_attr( $calendar->id ); ?>">
                                        <?php echo esc_html( $calendar->name ); ?> (<?php _e( 'Shared', 'wproject-calendar-pro' ); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="calendar-form-group">
                    <label for="event-description"><?php _e( 'Description', 'wproject-calendar-pro' ); ?></label>
                    <textarea id="event-description" name="description"></textarea>
                </div>

                <div class="calendar-form-group">
                    <label for="event-location"><?php _e( 'Location', 'wproject-calendar-pro' ); ?></label>
                    <input type="text" id="event-location" name="location">
                </div>

                <div class="calendar-form-group">
                    <label for="event-start"><?php _e( 'Start Date & Time', 'wproject-calendar-pro' ); ?> *</label>
                    <input type="datetime-local" id="event-start" name="start" required>
                </div>

                <div class="calendar-form-group">
                    <label for="event-end"><?php _e( 'End Date & Time', 'wproject-calendar-pro' ); ?> *</label>
                    <input type="datetime-local" id="event-end" name="end" required>
                </div>

                <div class="calendar-form-group">
                    <label>
                        <input type="checkbox" id="event-all-day" name="all_day">
                        <?php _e( 'All day event', 'wproject-calendar-pro' ); ?>
                    </label>
                </div>

                <div class="calendar-form-group">
                    <label><?php _e( 'Event Type', 'wproject-calendar-pro' ); ?></label>
                    <div class="event-type-selector">
                        <div class="event-type-option selected" data-type="event">
                            <?php _e( 'Event', 'wproject-calendar-pro' ); ?>
                        </div>
                        <div class="event-type-option" data-type="meeting">
                            <?php _e( 'Meeting', 'wproject-calendar-pro' ); ?>
                        </div>
                        <div class="event-type-option" data-type="deadline">
                            <?php _e( 'Deadline', 'wproject-calendar-pro' ); ?>
                        </div>
                        <div class="event-type-option" data-type="reminder">
                            <?php _e( 'Reminder', 'wproject-calendar-pro' ); ?>
                        </div>
                    </div>
                    <input type="hidden" id="event-type" name="event_type" value="event">
                </div>

                <div class="calendar-form-group">
                    <label><?php _e( 'Color', 'wproject-calendar-pro' ); ?></label>
                    <div class="calendar-color-picker">
                        <div class="calendar-color-option selected" data-color="<?php echo esc_attr($event_color); ?>" style="background-color: <?php echo esc_attr($event_color); ?>;"></div>
                        <div class="calendar-color-option" data-color="<?php echo esc_attr($meeting_color); ?>" style="background-color: <?php echo esc_attr($meeting_color); ?>;"></div>
                        <div class="calendar-color-option" data-color="<?php echo esc_attr($deadline_color); ?>" style="background-color: <?php echo esc_attr($deadline_color); ?>;"></div>
                        <div class="calendar-color-option" data-color="#8bc34a" style="background-color: #8bc34a;"></div>
                        <div class="calendar-color-option" data-color="#ff9800" style="background-color: #ff9800;"></div>
                        <div class="calendar-color-option" data-color="#607ae3" style="background-color: #607ae3;"></div>
                    </div>
                    <input type="hidden" id="event-color" name="color" value="<?php echo esc_attr($event_color); ?>">
                </div>

                <?php if ( $enable_reminders ) : ?>
                <div class="calendar-form-group">
                    <label>
                        <input type="checkbox" id="reminder-enabled" name="reminder_enabled" checked>
                        <?php _e( 'Send reminder', 'wproject-calendar-pro' ); ?>
                    </label>
                    <div class="sublabel">
                        <select id="reminder-minutes" name="reminder_minutes">
                            <option value="5" <?php selected( $default_reminder, '5' ); ?>>5 <?php _e( 'minutes before', 'wproject-calendar-pro' ); ?></option>
                            <option value="15" <?php selected( $default_reminder, '15' ); ?>>15 <?php _e( 'minutes before', 'wproject-calendar-pro' ); ?></option>
                            <option value="30" <?php selected( $default_reminder, '30' ); ?>>30 <?php _e( 'minutes before', 'wproject-calendar-pro' ); ?></option>
                            <option value="60" <?php selected( $default_reminder, '60' ); ?>>1 <?php _e( 'hour before', 'wproject-calendar-pro' ); ?></option>
                            <option value="120" <?php selected( $default_reminder, '120' ); ?>>2 <?php _e( 'hours before', 'wproject-calendar-pro' ); ?></option>
                            <option value="1440" <?php selected( $default_reminder, '1440' ); ?>>1 <?php _e( 'day before', 'wproject-calendar-pro' ); ?></option>
                        </select>
                    </div>
                </div>
                <?php endif; ?>

                <div class="calendar-form-group">
                    <label for="event-visibility"><?php _e( 'Visibility', 'wproject-calendar-pro' ); ?></label>
                    <select id="event-visibility" name="visibility">
                        <option value="private"><?php _e( 'Private', 'wproject-calendar-pro' ); ?></option>
                        <option value="team"><?php _e( 'Team', 'wproject-calendar-pro' ); ?></option>
                        <option value="public"><?php _e( 'Public', 'wproject-calendar-pro' ); ?></option>
                    </select>
                </div>

            </form>
        </div>

        <div class="calendar-modal-footer">
            <button class="btn btn-secondary btn-cancel"><?php _e( 'Cancel', 'wproject-calendar-pro' ); ?></button>
            <button class="btn btn-primary btn-save-event"><?php _e( 'Save Event', 'wproject-calendar-pro' ); ?></button>
        </div>
    </div>
</div>
