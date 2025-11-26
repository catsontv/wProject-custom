/**
 * Calendar Pro Frontend JavaScript
 *
 * Handles calendar interactions, event management, and AJAX operations
 *
 * @package wProject Calendar Pro
 * @since 1.0.0
 */

(function($) {
    'use strict';

    var CalendarPro = {

        calendar: null,
        currentCalendarId: null,

        /**
         * Initialize calendar
         */
        init: function() {
            this.bindEvents();
            this.initializeCalendar();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            var self = this;

            // Calendar selector change
            $(document).on('change', '#calendar-selector', function() {
                self.currentCalendarId = $(this).val();
                self.refreshEvents();
            });

            // New calendar button
            $(document).on('click', '.btn-new-calendar', function(e) {
                e.preventDefault();
                console.log('New calendar button clicked');
                self.showCalendarModal();
            });

            // New event button
            $(document).on('click', '.btn-new-event', function(e) {
                e.preventDefault();
                self.showEventModal();
            });

            // Save calendar (button click)
            $(document).on('click', '#calendar-form .btn-primary', function(e) {
                e.preventDefault();
                self.saveCalendar();
            });

            // Prevent calendar form submission
            $(document).on('submit', '#calendar-form', function(e) {
                e.preventDefault();
                self.saveCalendar();
            });

            // Save event
            $(document).on('click', '.btn-save-event', function(e) {
                e.preventDefault();
                self.saveEvent();
            });

            // Delete event
            $(document).on('click', '.btn-delete-event', function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to delete this event?')) {
                    self.deleteEvent();
                }
            });

            // Edit event
            $(document).on('click', '.btn-edit-event', function(e) {
                e.preventDefault();
                var eventId = $('#detail-event-id').val();
                console.log('Edit button clicked, Event ID:', eventId);
                if (eventId) {
                    self.closeModal();
                    self.editEvent(eventId);
                } else {
                    console.log('ERROR: No event ID found');
                }
            });

            // Close calendar modal
            $(document).on('click', '#calendar-modal .calendar-modal-close, #calendar-form-cancel', function(e) {
                e.preventDefault();
                self.hideCalendarModal();
            });

            // Close calendar modal by clicking outside
            $(document).on('click', '.calendar-modal', function(e) {
                if ($(e.target).hasClass('calendar-modal') && $(e.target).attr('id') === 'calendar-modal') {
                    self.hideCalendarModal();
                }
            });

            // Close event modal
            $(document).on('click', '.calendar-modal-close, .btn-cancel:not(#calendar-form-cancel)', function(e) {
                e.preventDefault();
                self.closeModal();
            });

            // Click outside event modal to close
            $(document).on('click', '.calendar-modal', function(e) {
                if ($(e.target).hasClass('calendar-modal')) {
                    self.closeModal();
                }
            });

            // Event type selection
            $(document).on('click', '.event-type-option', function() {
                $('.event-type-option').removeClass('selected');
                $(this).addClass('selected');
                $('#event-type').val($(this).data('type'));
            });

            // Color selection
            $(document).on('click', '.calendar-color-option', function() {
                $('.calendar-color-option').removeClass('selected');
                $(this).addClass('selected');
                $('#event-color').val($(this).data('color'));
            });
        },

        /**
         * Initialize FullCalendar
         */
        initializeCalendar: function() {
            var self = this;
            var calendarEl = document.getElementById('calendar-pro');

            if (!calendarEl) {
                return;
            }

            // Get settings from WordPress
            var defaultView = calendarEl.dataset.defaultView || 'dayGridMonth';
            var weekStart = parseInt(calendarEl.dataset.weekStart) || 0;
            var timeFormat = calendarEl.dataset.timeFormat || '24';

            var timeFormatString = timeFormat === '12' ? 'h:mm a' : 'HH:mm';

            this.calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: defaultView,
                firstDay: weekStart,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                },
                buttonText: {
                    today: 'Today',
                    month: 'Month',
                    week: 'Week',
                    day: 'Day',
                    list: 'List'
                },
                eventTimeFormat: {
                    hour: timeFormat === '12' ? 'numeric' : '2-digit',
                    minute: '2-digit',
                    meridiem: timeFormat === '12' ? 'short' : false
                },
                slotLabelFormat: {
                    hour: timeFormat === '12' ? 'numeric' : '2-digit',
                    minute: '2-digit',
                    meridiem: timeFormat === '12' ? 'short' : false
                },
                editable: true,
                selectable: true,
                selectMirror: true,
                dayMaxEvents: true,
                weekends: true,
                select: function(info) {
                    self.showEventModal(info);
                },
                eventClick: function(info) {
                    self.showEventDetail(info.event);
                },
                eventDrop: function(info) {
                    self.updateEventDates(info.event);
                },
                eventResize: function(info) {
                    self.updateEventDates(info.event);
                },
                events: function(info, successCallback, failureCallback) {
                    self.loadEvents(info, successCallback, failureCallback);
                }
            });

            this.calendar.render();
        },

        /**
         * Load events from server
         */
        loadEvents: function(info, successCallback, failureCallback) {
            var self = this;

            $.ajax({
                url: calendar_inputs.ajaxurl,
                type: 'POST',
                data: {
                    action: 'calendar_pro_get_events',
                    nonce: calendar_inputs.nonce,
                    calendar_id: self.currentCalendarId,
                    start: info.startStr,
                    end: info.endStr
                },
                success: function(response) {
                    if (response.status === 'success') {
                        successCallback(response.data);
                    } else {
                        failureCallback();
                    }
                },
                error: function() {
                    failureCallback();
                }
            });
        },

        /**
         * Refresh events
         */
        refreshEvents: function() {
            if (this.calendar) {
                this.calendar.refetchEvents();
            }
        },

        /**
         * Show event modal
         */
        showEventModal: function(selectInfo) {
            var modal = $('#event-modal');
            var isEditing = $('#event-id').val() !== '';

            // Only reset form if creating new event (not editing)
            if (!isEditing) {
                $('#event-form')[0].reset();
                $('#event-id').val('');
                $('.event-type-option').first().click();
                $('.calendar-color-option').first().click();
                $('.btn-save-event').text('Save Event'); // Reset button text

                // Set dates if creating from selection
                if (selectInfo) {
                    $('#event-start').val(this.formatDateTimeLocal(selectInfo.start));
                    $('#event-end').val(this.formatDateTimeLocal(selectInfo.end));
                }
            }

            // Show modal
            modal.addClass('active');
            $('#event-title').focus();
        },

        /**
         * Show event detail
         */
        showEventDetail: function(event) {
            var modal = $('#event-detail-modal');

            // Populate event details
            $('#detail-title').text(event.title);
            $('#detail-start').text(this.formatDateTime(event.start));
            $('#detail-end').text(event.end ? this.formatDateTime(event.end) : '');
            $('#detail-description').text(event.extendedProps.description || 'No description');
            $('#detail-location').text(event.extendedProps.location || 'No location');
            $('#detail-type').text(event.extendedProps.eventType || 'event');

            // Store event ID for actions
            modal.data('event-id', event.id);
            $('#detail-event-id').val(event.id);

            // Show modal
            modal.addClass('active');
        },

        /**
         * Close modal
         */
        closeModal: function() {
            $('.calendar-modal').removeClass('active');
        },

        /**
         * Save event
         */
        saveEvent: function() {
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
                categories: $('#event-categories').val() || '',
                timezone: $('#event-timezone').val() || 'UTC',
                attendees: $('#event-attendees').val() || []
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
        },

        /**
         * Delete event
         */
        deleteEvent: function() {
            var self = this;
            var eventId = $('#event-detail-modal').data('event-id');

            if (!eventId) {
                return;
            }

            $.ajax({
                url: calendar_inputs.ajaxurl,
                type: 'POST',
                data: {
                    action: 'calendar_pro_delete_event',
                    nonce: calendar_inputs.nonce,
                    event_id: eventId
                },
                success: function(response) {
                    if (response.status === 'success') {
                        self.closeModal();
                        self.refreshEvents();
                        self.showNotification('Event deleted successfully', 'success');
                    } else {
                        alert(response.message);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                }
            });
        },

        /**
         * Edit event - Fetch and populate form with event data
         */
        editEvent: function(eventId) {
            var self = this;

            console.log('editEvent called with ID:', eventId);

            $.ajax({
                url: calendar_inputs.ajaxurl,
                type: 'POST',
                data: {
                    action: 'calendar_pro_get_event',
                    nonce: calendar_inputs.nonce,
                    event_id: eventId
                },
                success: function(response) {
                    console.log('editEvent AJAX success:', response);
                    if (response.status === 'success' && response.data.event) {
                        var event = response.data.event;
                        console.log('Event data loaded:', event);

                        // Populate form fields
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

                        // Populate advanced fields
                        $('#event-categories').val(event.categories || '');
                        $('#event-timezone').val(event.timezone || 'UTC');

                        // Set attendees if available
                        if (response.data.attendees && response.data.attendees.length > 0) {
                            var attendeeIds = response.data.attendees.map(function(a) { return a.user_id; });
                            $('#event-attendees').val(attendeeIds);
                        } else {
                            $('#event-attendees').val([]);
                        }

                        // Update button text
                        $('.btn-save-event').text('Update Event');

                        console.log('Showing event modal for editing');
                        // Show event form modal for editing
                        self.showEventModal();
                    } else {
                        console.error('Response error:', response.message);
                        alert(response.message || 'Failed to load event');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('editEvent AJAX error:', xhr, status, error);
                    alert('An error occurred. Please try again.');
                }
            });
        },

        /**
         * Update event dates after drag/resize
         */
        updateEventDates: function(event) {
            var self = this;

            $.ajax({
                url: calendar_inputs.ajaxurl,
                type: 'POST',
                data: {
                    action: 'calendar_pro_update_event',
                    nonce: calendar_inputs.nonce,
                    event_id: event.id,
                    start: event.start.toISOString(),
                    end: event.end ? event.end.toISOString() : null
                },
                success: function(response) {
                    if (response.status === 'success') {
                        self.showNotification('Event updated', 'success');
                    } else {
                        alert(response.message);
                        self.refreshEvents(); // Revert on failure
                    }
                },
                error: function() {
                    alert('An error occurred');
                    self.refreshEvents(); // Revert on error
                }
            });
        },

        /**
         * Show notification
         */
        showNotification: function(message, type) {
            // Use wProject notification system if available
            if (typeof showNotification === 'function') {
                showNotification(message);
            } else {
                console.log(message);
            }
        },

        /**
         * Format datetime for display
         */
        formatDateTime: function(date) {
            if (!date) return '';
            return new Date(date).toLocaleString();
        },

        /**
         * Format datetime for input field
         */
        formatDateTimeLocal: function(date) {
            if (!date) return '';
            var d = new Date(date);
            d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
            return d.toISOString().slice(0, 16);
        },

        /**
         * Show calendar creation modal
         */
        showCalendarModal: function() {
            console.log('showCalendarModal called');
            $('#calendar-id').val('');
            $('#calendar-form').trigger('reset');
            console.log('Adding active class to #calendar-modal');
            $('#calendar-modal').addClass('active');
            console.log('Modal classes:', $('#calendar-modal').attr('class'));
            $('#calendar-name').focus();
        },

        /**
         * Hide calendar modal
         */
        hideCalendarModal: function() {
            $('#calendar-modal').removeClass('active');
            $('#calendar-form').trigger('reset');
        },

        /**
         * Save calendar
         */
        saveCalendar: function() {
            var self = this;
            var name = $('#calendar-name').val().trim();
            var description = $('#calendar-description').val().trim();
            var color = $('#calendar-color').val();
            var visibility = $('#calendar-visibility').val();

            console.log('Saving calendar:', {name: name, description: description, color: color, visibility: visibility});

            if (!name) {
                alert('Please enter a calendar name');
                return;
            }

            var data = {
                action: 'calendar_pro_create_calendar',
                nonce: calendar_inputs.nonce,
                name: name,
                description: description,
                color: color,
                visibility: visibility
            };

            console.log('Sending AJAX data:', data);
            console.log('AJAX URL:', calendar_inputs.ajaxurl);

            $.ajax({
                url: calendar_inputs.ajaxurl,
                type: 'POST',
                data: data,
                success: function(response) {
                    console.log('Calendar save AJAX success:', response);
                    if (response.status === 'success') {
                        self.showNotification('Calendar created successfully!', 'success');
                        self.hideCalendarModal();
                        // Reload page to show new calendar
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        console.error('Response status not success:', response.message);
                        self.showNotification(response.message || 'Failed to create calendar', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Calendar save AJAX error:', xhr.status, error);
                    console.error('Response text:', xhr.responseText);
                    self.showNotification('An error occurred. Please check the console for details.', 'error');
                }
            });
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        CalendarPro.init();
    });

})(jQuery);
