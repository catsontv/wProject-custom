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
        currentEditingCalendarId: null,

        /**
         * Initialize calendar
         */
        init: function() {
            this.bindEvents();
            this.initializeCalendar();
            this.autoSelectDefaultCalendar();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            var self = this;

            // Calendar selector change
            $(document).on('change', '#calendar-selector', function() {
                self.currentCalendarId = $(this).val();
                if (self.currentCalendarId) {
                    $('.btn-manage-calendar').show();
                } else {
                    $('.btn-manage-calendar').hide();
                }
                self.refreshEvents();
            });

            // New event button
            $(document).on('click', '.btn-new-event', function(e) {
                e.preventDefault();
                self.showEventModal();
            });

            // Calendar management buttons
            $(document).on('click', '.btn-new-calendar', function(e) {
                e.preventDefault();
                self.showCalendarModal();
            });

            $(document).on('click', '.btn-manage-calendar', function(e) {
                e.preventDefault();
                self.showCalendarModal(self.currentCalendarId);
            });

            $(document).on('click', '.btn-save-calendar', function(e) {
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

            // Close modal
            $(document).on('click', '.calendar-modal-close, .btn-cancel', function(e) {
                e.preventDefault();
                self.closeModal();
            });

            // Click outside modal to close
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
                var colorField = $(this).closest('.calendar-color-picker').find('input[type="hidden"]');
                if (colorField.length) {
                    colorField.val($(this).data('color'));
                }
            });
        },

        /**
         * Auto-select default calendar
         */
        autoSelectDefaultCalendar: function() {
            var $selector = $('#calendar-selector');
            var $defaultOption = $selector.find('option[data-default="1"]').first();
            
            if ($defaultOption.length) {
                $selector.val($defaultOption.val());
                this.currentCalendarId = $defaultOption.val();
                $('.btn-manage-calendar').show();
            }
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
         * Show calendar management modal
         */
        showCalendarModal: function(calendarId) {
            var self = this;
            var modal = $('#calendar-management-modal');

            // Reset form
            $('#calendar-form')[0].reset();
            $('#calendar-form-id').val('');
            $('.calendar-color-option').first().click();
            this.currentEditingCalendarId = null;

            if (calendarId) {
                // Edit mode - load calendar data
                this.currentEditingCalendarId = calendarId;
                $('#calendar-modal-title').text('Edit Calendar');

                $.ajax({
                    url: calendar_inputs.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'calendar_pro_get_calendar',
                        nonce: calendar_inputs.nonce,
                        calendar_id: calendarId
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            var cal = response.data.calendar;
                            $('#calendar-form-id').val(cal.id);
                            $('#calendar-name').val(cal.name);
                            $('#calendar-description').val(cal.description);
                            $('#calendar-visibility').val(cal.visibility);
                            
                            // Select color
                            $('.calendar-color-option[data-color="' + cal.color + '"]').click();
                        }
                    }
                });
            } else {
                // Create mode
                $('#calendar-modal-title').text('New Calendar');
            }

            modal.addClass('active');
            $('#calendar-name').focus();

            // Refresh feather icons if available
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        },

        /**
         * Save calendar
         */
        saveCalendar: function() {
            var self = this;
            var calendarId = $('#calendar-form-id').val();
            var isEdit = calendarId !== '';

            var calendarData = {
                action: isEdit ? 'calendar_pro_update_calendar' : 'calendar_pro_create_calendar',
                nonce: calendar_inputs.nonce,
                name: $('#calendar-name').val().trim(),
                description: $('#calendar-description').val().trim(),
                color: $('#calendar-color-value').val(),
                visibility: $('#calendar-visibility').val()
            };

            if (isEdit) {
                calendarData.calendar_id = calendarId;
            }

            // Validation
            if (!calendarData.name) {
                alert('Please enter a calendar name');
                $('#calendar-name').focus();
                return;
            }

            // Show loading
            $('.btn-save-calendar').prop('disabled', true).text('Saving...');

            $.ajax({
                url: calendar_inputs.ajaxurl,
                type: 'POST',
                data: calendarData,
                success: function(response) {
                    if (response.status === 'success') {
                        self.closeModal();
                        self.refreshCalendarList();
                        self.showNotification(
                            isEdit ? 'Calendar updated successfully' : 'Calendar created successfully',
                            'success'
                        );
                    } else {
                        alert(response.message);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                },
                complete: function() {
                    $('.btn-save-calendar').prop('disabled', false).text('Save Calendar');
                }
            });
        },

        /**
         * Refresh calendar list in dropdown
         */
        refreshCalendarList: function() {
            var self = this;

            $.ajax({
                url: calendar_inputs.ajaxurl,
                type: 'POST',
                data: {
                    action: 'calendar_pro_get_user_calendars',
                    nonce: calendar_inputs.nonce
                },
                success: function(response) {
                    if (response.status === 'success') {
                        var $selector = $('#calendar-selector');
                        var currentValue = $selector.val();

                        // Rebuild dropdown
                        $selector.empty();
                        $selector.append('<option value="">All Calendars</option>');

                        // Add user's calendars
                        $.each(response.data.user_calendars, function(i, calendar) {
                            var isDefault = calendar.is_default == 1 ? ' data-default="1"' : '';
                            $selector.append(
                                '<option value="' + calendar.id + '"' + isDefault + '>' + 
                                calendar.name + 
                                '</option>'
                            );
                        });

                        // Add shared calendars
                        $.each(response.data.shared_calendars, function(i, calendar) {
                            $selector.append(
                                '<option value="' + calendar.id + '">' + 
                                calendar.name + ' (Shared)' +
                                '</option>'
                            );
                        });

                        // Restore selection or auto-select default
                        if (currentValue) {
                            $selector.val(currentValue);
                        } else {
                            self.autoSelectDefaultCalendar();
                        }

                        self.refreshEvents();
                    }
                }
            });
        },

        /**
         * Show event modal
         */
        showEventModal: function(selectInfo) {
            var modal = $('#event-modal');

            // Reset form
            $('#event-form')[0].reset();
            $('#event-id').val('');
            $('.event-type-option').first().click();
            $('.calendar-color-option').first().click();

            // Auto-set calendar from dropdown or default
            var selectedCalendar = this.currentCalendarId || $('#calendar-selector').val();
            if (selectedCalendar) {
                $('#event-calendar').val(selectedCalendar);
            }

            // Set dates if creating from selection
            if (selectInfo) {
                $('#event-start').val(this.formatDateTimeLocal(selectInfo.start));
                $('#event-end').val(this.formatDateTimeLocal(selectInfo.end));
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
                calendar_id: $('#event-calendar').val() || self.currentCalendarId || $('#calendar-selector').val(),
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
                visibility: $('#event-visibility').val()
            };

            if (isEdit) {
                eventData.event_id = eventId;
            }

            // Validation
            if (!eventData.title) {
                alert('Please enter an event title');
                return;
            }

            if (!eventData.calendar_id) {
                alert('Please select a calendar');
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
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        CalendarPro.init();
    });

})(jQuery);
