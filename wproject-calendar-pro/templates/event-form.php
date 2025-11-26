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

                <!-- Advanced Fields: Categories -->
                <div class="calendar-form-group">
                    <label for="event-categories"><?php _e( 'Categories', 'wproject-calendar-pro' ); ?></label>
                    <input
                        type="text"
                        id="event-categories"
                        name="categories"
                        placeholder="<?php esc_attr_e( 'e.g., Work, Personal, Important (comma separated)', 'wproject-calendar-pro' ); ?>"
                        maxlength="255"
                    >
                    <small><?php _e( 'Add multiple categories separated by commas for easy filtering', 'wproject-calendar-pro' ); ?></small>
                </div>

                <!-- Advanced Fields: Timezone -->
                <div class="calendar-form-group">
                    <label for="event-timezone"><?php _e( 'Timezone', 'wproject-calendar-pro' ); ?></label>
                    <select id="event-timezone" name="timezone">
                        <option value="UTC">UTC (Coordinated Universal Time)</option>
                        <optgroup label="<?php esc_attr_e( 'Americas', 'wproject-calendar-pro' ); ?>">
                            <option value="America/New_York">Eastern Time (ET)</option>
                            <option value="America/Chicago">Central Time (CT)</option>
                            <option value="America/Denver">Mountain Time (MT)</option>
                            <option value="America/Los_Angeles">Pacific Time (PT)</option>
                        </optgroup>
                        <optgroup label="<?php esc_attr_e( 'Europe', 'wproject-calendar-pro' ); ?>">
                            <option value="Europe/London">London (GMT)</option>
                            <option value="Europe/Paris">Central European Time (CET)</option>
                            <option value="Europe/Berlin">Berlin (CEST)</option>
                            <option value="Europe/Istanbul">Istanbul (EET)</option>
                        </optgroup>
                        <optgroup label="<?php esc_attr_e( 'Asia', 'wproject-calendar-pro' ); ?>">
                            <option value="Asia/Dubai">Dubai (GST)</option>
                            <option value="Asia/Kolkata">India (IST)</option>
                            <option value="Asia/Bangkok">Bangkok (ICT)</option>
                            <option value="Asia/Singapore">Singapore (SGT)</option>
                            <option value="Asia/Hong_Kong">Hong Kong (HKT)</option>
                            <option value="Asia/Tokyo">Tokyo (JST)</option>
                            <option value="Australia/Sydney">Sydney (AEDT)</option>
                        </optgroup>
                    </select>
                </div>

                <!-- Advanced Fields: Attendees/Guests -->
                <div class="calendar-form-group">
                    <label for="event-attendees"><?php _e( 'Add Guests/Attendees', 'wproject-calendar-pro' ); ?></label>
                    <select id="event-attendees" name="attendees[]" multiple class="calendar-attendees-select">
                        <?php
                        $users = get_users( array(
                            'fields' => array( 'ID', 'display_name', 'user_email' ),
                            'role__not_in' => array( 'client' )
                        ) );
                        if ( ! empty( $users ) ) {
                            foreach ( $users as $user ) {
                                echo '<option value="' . esc_attr( $user->ID ) . '">' . esc_html( $user->display_name ) . ' (' . esc_html( $user->user_email ) . ')</option>';
                            }
                        }
                        ?>
                    </select>
                    <small><?php _e( 'Hold Ctrl/Cmd to select multiple attendees. They will receive email invitations.', 'wproject-calendar-pro' ); ?></small>
                </div>

            </form>
        </div>

        <div class="calendar-modal-footer">
            <button class="btn btn-secondary btn-cancel"><?php _e( 'Cancel', 'wproject-calendar-pro' ); ?></button>
            <button class="btn btn-primary btn-save-event"><?php _e( 'Save Event', 'wproject-calendar-pro' ); ?></button>
        </div>
    </div>
</div>
