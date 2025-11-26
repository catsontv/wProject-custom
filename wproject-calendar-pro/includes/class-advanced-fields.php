<?php
/**
 * Enhanced Event Form Fields
 *
 * Adds missing form fields for advanced event management:
 * - Categories
 * - Timezone
 * - Attendees/Guests
 *
 * @package wProject Calendar Pro
 * @since 1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Render additional event form fields
 * Hook this into the event form after line 120
 */
function calendar_pro_render_advanced_fields() {
    global $wpdb;

    // Get list of users for attendee selection
    $users = get_users( array(
        'fields' => array( 'ID', 'display_name', 'user_email' ),
        'role__not_in' => array( 'client' )
    ) );
    ?>

    <!-- Categories Field -->
    <div class="calendar-form-group">
        <label for="event-categories">
            <?php _e( 'Categories', 'wproject-calendar-pro' ); ?>
        </label>
        <input
            type="text"
            id="event-categories"
            name="categories"
            placeholder="<?php esc_attr_e( 'e.g., Work, Personal, Important (comma separated)', 'wproject-calendar-pro' ); ?>"
            maxlength="255"
        >
        <small><?php _e( 'Add multiple categories separated by commas for easy filtering', 'wproject-calendar-pro' ); ?></small>
    </div>

    <!-- Timezone Field -->
    <div class="calendar-form-group">
        <label for="event-timezone">
            <?php _e( 'Timezone', 'wproject-calendar-pro' ); ?>
        </label>
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

    <!-- Attendees/Guests Field -->
    <div class="calendar-form-group">
        <label for="event-attendees">
            <?php _e( 'Add Guests/Attendees', 'wproject-calendar-pro' ); ?>
        </label>
        <select
            id="event-attendees"
            name="attendees[]"
            multiple
            class="calendar-attendees-select"
            data-placeholder="<?php esc_attr_e( 'Select team members to invite...', 'wproject-calendar-pro' ); ?>"
        >
            <?php if ( ! empty( $users ) ) : ?>
                <?php foreach ( $users as $user ) : ?>
                    <option value="<?php echo esc_attr( $user->ID ); ?>">
                        <?php echo esc_html( $user->display_name ); ?> (<?php echo esc_html( $user->user_email ); ?>)
                    </option>
                <?php endforeach; ?>
            <?php else : ?>
                <option disabled><?php _e( 'No users available', 'wproject-calendar-pro' ); ?></option>
            <?php endif; ?>
        </select>
        <small><?php _e( 'Hold Ctrl/Cmd to select multiple attendees', 'wproject-calendar-pro' ); ?></small>
    </div>

    <style>
    .calendar-attendees-select {
        width: 100%;
        min-height: 100px;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 3px;
        font-family: inherit;
        font-size: 1em;
    }

    .calendar-attendees-select:focus {
        border-color: #00bcd4;
        outline: none;
        box-shadow: 0 0 0 3px rgba(0, 188, 212, 0.1);
    }

    .calendar-form-group small {
        display: block;
        margin-top: 5px;
        font-size: 0.85em;
        color: #999;
    }
    </style>

    <?php
}

/**
 * Output the advanced fields in event form
 * Add this where the form fields are rendered
 */
function calendar_pro_enqueue_advanced_scripts() {
    wp_add_inline_script( 'calendar_pro_js', "
        // Update the saveEvent function to include new fields
        var originalSaveEvent = CalendarPro.saveEvent;
        CalendarPro.saveEvent = function() {
            var self = this;
            var eventId = $('#event-id').val();
            var isEdit = eventId !== '';

            var eventData = {
                action: isEdit ? 'calendar_pro_update_event' : 'calendar_pro_create_event',
                nonce: calendar_inputs.nonce,
                calendar_id: self.currentCalendarId || $('#calendar-selector').val(),
                title: $('#event-title').val(),
                description: $('#event-description').val(),
                location: $('#event-location').val(),
                start: $('#event-start').val(),
                end: $('#event-end').val(),
                all_day: $('#event-all-day').is(':checked') ? 1 : 0,
                event_type: $('#event-type').val(),
                color: $('#event-color').val(),
                reminder_enabled: $('#reminder-enabled').is(':checked') ? 1 : 0,
                reminder_minutes: $('#reminder-minutes').val(),
                visibility: $('#event-visibility').val(),
                // New fields
                categories: $('#event-categories').val(),
                timezone: $('#event-timezone').val(),
                attendees: $('#event-attendees').val()
            };

            if (isEdit) {
                eventData.event_id = eventId;
            }

            // Validation
            if (!eventData.title) {
                alert('Please enter an event title');
                return;
            }

            // Show loading
            $('.btn-save-event').prop('disabled', true).text('Saving...');

            $.ajax({
                url: calendar_inputs.ajaxurl,
                type: 'POST',
                data: eventData,
                success: function(response) {
                    if (response.status === 'success') {
                        self.closeModal();
                        self.refreshEvents();
                        self.showNotification('Event saved successfully', 'success');
                    } else {
                        alert(response.message);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                },
                complete: function() {
                    $('.btn-save-event').prop('disabled', false).text('Save Event');
                }
            });
        };

        // Update editEvent to populate new fields
        var originalEditEvent = CalendarPro.editEvent;
        CalendarPro.editEvent = function(eventId) {
            var self = this;

            $.ajax({
                url: calendar_inputs.ajaxurl,
                type: 'POST',
                data: {
                    action: 'calendar_pro_get_event',
                    nonce: calendar_inputs.nonce,
                    event_id: eventId
                },
                success: function(response) {
                    if (response.status === 'success' && response.data.event) {
                        var event = response.data.event;

                        // Populate all fields
                        $('#event-id').val(event.id);
                        $('#event-title').val(event.title);
                        $('#event-description').val(event.description);
                        $('#event-location').val(event.location);
                        $('#event-start').val(self.formatDateTimeLocal(event.start_datetime));
                        $('#event-end').val(self.formatDateTimeLocal(event.end_datetime));
                        $('#event-all-day').prop('checked', event.all_day == 1);
                        $('#event-type').val(event.event_type);
                        $('#event-color').val(event.color);
                        $('#event-visibility').val(event.visibility);
                        $('#reminder-enabled').prop('checked', event.reminder_enabled == 1);
                        $('#reminder-minutes').val(event.reminder_minutes);

                        // New fields
                        $('#event-categories').val(event.categories || '');
                        $('#event-timezone').val(event.timezone || 'UTC');

                        // Set attendees if available
                        if (response.data.attendees && response.data.attendees.length > 0) {
                            var attendeeIds = response.data.attendees.map(function(a) { return a.user_id; });
                            $('#event-attendees').val(attendeeIds);
                        }

                        // Update button text
                        $('.btn-save-event').text('Update Event');

                        // Show event form modal for editing
                        self.showEventModal();
                    } else {
                        alert(response.message || 'Failed to load event');
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                }
            });
        };
    " );
}
add_action( 'wp_enqueue_scripts', 'calendar_pro_enqueue_advanced_scripts' );
