# Phase 1C: Calendar Management UI & Auto-Creation

**Priority:** HIGH - Required for basic functionality  
**Status:** Pending  
**Estimated Time:** 3-4 hours  
**Dependencies:** PHASE-1A (Security Fixes)

## Overview

This phase implements the missing calendar management interface and automatic calendar creation functionality. Currently, users created after plugin activation don't receive default calendars, and there's no UI to create, edit, or delete calendars.

---

## Issues Identified

### Problem 1: New Users Don't Get Default Calendars
**Current Behavior:**
- `create_default_calendars()` only runs once during plugin activation
- Users created after activation have no calendars
- These users cannot create events (get "Permission denied" error)

**Impact:** 
- New team members cannot use the calendar until manually fixed
- No way for users to recover if their calendar is deleted

### Problem 2: No Calendar Management UI
**Current Behavior:**
- Calendar dropdown only displays existing calendars
- No "Create Calendar" button
- No way to edit calendar properties (name, color, description)
- No way to delete unwanted calendars

**Impact:**
- Users cannot organize events into multiple calendars
- Cannot customize calendar appearance
- Stuck with auto-generated calendar names

### Problem 3: No Default Calendar Auto-Selection
**Current Behavior:**
- Event creation form doesn't auto-select a calendar
- JavaScript sends empty `calendar_id` if nothing selected
- Backend rejects with "Permission denied"

**Impact:**
- Poor user experience
- Confusing error messages for new users

---

## Implementation Checklist

### Backend - Auto Calendar Creation
- [ ] Add `user_register` hook to create calendar for new users
- [ ] Create one-time migration function for existing users without calendars
- [ ] Add admin notice if users are missing calendars
- [ ] Add AJAX handler for manual calendar creation
- [ ] Add AJAX handler for calendar editing
- [ ] Validate calendar names (non-empty, max length)

### Frontend - Calendar Management UI
- [ ] Add "+ New Calendar" button next to calendar dropdown
- [ ] Create calendar creation/edit modal
- [ ] Add color picker for calendar colors
- [ ] Add calendar settings icon in dropdown
- [ ] Add delete calendar confirmation dialog
- [ ] Add validation for calendar form

### JavaScript - Auto-Selection Logic
- [ ] Auto-select user's default calendar in event form
- [ ] Remember last selected calendar in session
- [ ] Update dropdown when calendar is created/deleted
- [ ] Refresh calendar list after management actions

---

## 1. Auto-Create Calendars for New Users

### File: `includes/class-calendar-core.php`

Add hook in the `init_hooks()` method:

```php
private function init_hooks() {
    add_action( 'init', array( $this, 'register_post_types' ) );
    add_action( 'init', array( $this, 'register_taxonomies' ) );
    
    // NEW: Auto-create calendar for new users
    add_action( 'user_register', array( $this, 'create_user_default_calendar' ), 10, 1 );
}
```

Add new method to create calendar for a single user:

```php
/**
 * Create default calendar for a newly registered user
 *
 * @param int $user_id User ID
 * @return int|false Calendar ID or false on failure
 */
public static function create_user_default_calendar( $user_id ) {
    global $wpdb;

    // Skip for client role
    $user = get_userdata( $user_id );
    if ( ! $user || in_array( 'client', $user->roles ) ) {
        return false;
    }

    $table_calendars = $wpdb->prefix . 'wproject_calendars';

    // Check if user already has a default calendar
    $has_default = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM $table_calendars WHERE owner_id = %d AND is_default = 1",
        $user_id
    ) );

    if ( $has_default ) {
        return false;
    }

    $calendar_name = sprintf( __( '%s\'s Calendar', 'wproject-calendar-pro' ), $user->display_name );

    $result = $wpdb->insert(
        $table_calendars,
        array(
            'name'        => $calendar_name,
            'description' => __( 'Default calendar', 'wproject-calendar-pro' ),
            'color'       => '#00bcd4',
            'owner_id'    => $user_id,
            'is_default'  => 1,
            'visibility'  => 'private',
            'created_at'  => current_time( 'mysql' ),
            'updated_at'  => current_time( 'mysql' )
        ),
        array( '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s' )
    );

    if ( $result ) {
        do_action( 'calendar_pro_default_calendar_created', $wpdb->insert_id, $user_id );
        return $wpdb->insert_id;
    }

    return false;
}
```

### One-Time Migration for Existing Users

Add migration function to run via admin notice or WP-CLI:

```php
/**
 * Create missing default calendars for existing users
 * 
 * Run this once to fix users created after plugin activation
 *
 * @return array Results array with counts
 */
public static function create_missing_default_calendars() {
    global $wpdb;

    $table_calendars = $wpdb->prefix . 'wproject_calendars';
    
    $users = get_users( array(
        'fields' => array( 'ID' ),
        'role__not_in' => array( 'client' )
    ) );

    $created = 0;
    $skipped = 0;
    $errors = 0;

    foreach ( $users as $user ) {
        // Check if user has any calendar
        $has_calendar = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_calendars WHERE owner_id = %d",
            $user->ID
        ) );

        if ( ! $has_calendar ) {
            $result = self::create_user_default_calendar( $user->ID );
            if ( $result ) {
                $created++;
            } else {
                $errors++;
            }
        } else {
            $skipped++;
        }
    }

    return array(
        'created' => $created,
        'skipped' => $skipped,
        'errors'  => $errors,
        'total'   => count( $users )
    );
}
```

---

## 2. AJAX Handlers for Calendar Management

### File: `includes/ajax-handlers.php`

Add new AJAX handlers at the end of the file:

```php
/* Get user's calendars */
add_action( 'wp_ajax_calendar_pro_get_user_calendars', 'calendar_pro_get_user_calendars' );
function calendar_pro_get_user_calendars() {
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'calendar_inputs' ) ) {
        calendar_ajaxStatus( 'error', __( 'Nonce check failed.', 'wproject-calendar-pro' ) );
    }

    $user_calendars = WProject_Calendar_Core::get_user_calendars( get_current_user_id() );
    $shared_calendars = WProject_Calendar_Core::get_shared_calendars( get_current_user_id() );

    calendar_ajaxStatus( 'success', __( 'Calendars retrieved', 'wproject-calendar-pro' ), array(
        'user_calendars' => $user_calendars,
        'shared_calendars' => $shared_calendars
    ) );
}

/* Get calendar details */
add_action( 'wp_ajax_calendar_pro_get_calendar', 'calendar_pro_get_calendar' );
function calendar_pro_get_calendar() {
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'calendar_inputs' ) ) {
        calendar_ajaxStatus( 'error', __( 'Nonce check failed.', 'wproject-calendar-pro' ) );
    }

    $calendar_id = isset( $_POST['calendar_id'] ) ? (int) $_POST['calendar_id'] : 0;

    // Check if user can access this calendar
    if ( ! WProject_Calendar_Manager::user_can_access_calendar( $calendar_id, get_current_user_id() ) ) {
        calendar_ajaxStatus( 'error', __( 'Permission denied.', 'wproject-calendar-pro' ) );
    }

    $calendar = WProject_Calendar_Manager::get_calendar( $calendar_id );

    if ( $calendar ) {
        calendar_ajaxStatus( 'success', __( 'Calendar retrieved', 'wproject-calendar-pro' ), array(
            'calendar' => $calendar
        ) );
    } else {
        calendar_ajaxStatus( 'error', __( 'Calendar not found.', 'wproject-calendar-pro' ) );
    }
}
```

---

## 3. Calendar Management Modal UI

### File: `templates/calendar-management-modal.php` (NEW FILE)

Create a new template file for the calendar management modal:

```php
<?php
/**
 * Calendar Management Modal Template
 *
 * Modal for creating/editing calendars
 *
 * @package wProject Calendar Pro
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div id="calendar-management-modal" class="calendar-modal">
    <div class="calendar-modal-content">
        <div class="calendar-modal-header">
            <h3 id="calendar-modal-title"><?php _e( 'New Calendar', 'wproject-calendar-pro' ); ?></h3>
            <button class="calendar-modal-close">&times;</button>
        </div>

        <div class="calendar-modal-body">
            <form id="calendar-form">
                <input type="hidden" id="calendar-form-id" name="calendar_id" value="">

                <div class="form-row">
                    <label for="calendar-name">
                        <?php _e( 'Calendar Name', 'wproject-calendar-pro' ); ?>
                        <span class="required">*</span>
                    </label>
                    <input type="text" 
                           id="calendar-name" 
                           name="calendar_name" 
                           placeholder="<?php esc_attr_e( 'My Calendar', 'wproject-calendar-pro' ); ?>" 
                           maxlength="255" 
                           required>
                </div>

                <div class="form-row">
                    <label for="calendar-description">
                        <?php _e( 'Description', 'wproject-calendar-pro' ); ?>
                    </label>
                    <textarea id="calendar-description" 
                              name="calendar_description" 
                              rows="3" 
                              placeholder="<?php esc_attr_e( 'Optional description', 'wproject-calendar-pro' ); ?>"></textarea>
                </div>

                <div class="form-row">
                    <label><?php _e( 'Color', 'wproject-calendar-pro' ); ?></label>
                    <div class="calendar-color-picker">
                        <input type="hidden" id="calendar-color-value" name="calendar_color" value="#00bcd4">
                        
                        <div class="calendar-color-options">
                            <div class="calendar-color-option selected" data-color="#00bcd4" style="background-color: #00bcd4;" title="Cyan"></div>
                            <div class="calendar-color-option" data-color="#4caf50" style="background-color: #4caf50;" title="Green"></div>
                            <div class="calendar-color-option" data-color="#ff9800" style="background-color: #ff9800;" title="Orange"></div>
                            <div class="calendar-color-option" data-color="#f44336" style="background-color: #f44336;" title="Red"></div>
                            <div class="calendar-color-option" data-color="#9c27b0" style="background-color: #9c27b0;" title="Purple"></div>
                            <div class="calendar-color-option" data-color="#2196f3" style="background-color: #2196f3;" title="Blue"></div>
                            <div class="calendar-color-option" data-color="#ffeb3b" style="background-color: #ffeb3b;" title="Yellow"></div>
                            <div class="calendar-color-option" data-color="#607d8b" style="background-color: #607d8b;" title="Gray"></div>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <label for="calendar-visibility">
                        <?php _e( 'Visibility', 'wproject-calendar-pro' ); ?>
                    </label>
                    <select id="calendar-visibility" name="calendar_visibility">
                        <option value="private"><?php _e( 'Private (Only Me)', 'wproject-calendar-pro' ); ?></option>
                        <option value="team"><?php _e( 'Team', 'wproject-calendar-pro' ); ?></option>
                        <option value="public"><?php _e( 'Public', 'wproject-calendar-pro' ); ?></option>
                    </select>
                </div>
            </form>
        </div>

        <div class="calendar-modal-footer">
            <button class="btn btn-secondary btn-cancel">
                <?php _e( 'Cancel', 'wproject-calendar-pro' ); ?>
            </button>
            <button class="btn btn-primary btn-save-calendar">
                <?php _e( 'Save Calendar', 'wproject-calendar-pro' ); ?>
            </button>
        </div>
    </div>
</div>
```

---

## 4. Update Calendar View Template

### File: `templates/calendar-view.php`

Modify the calendar header to include calendar management button:

```php
<div class="calendar-header-left">
    <h2><?php _e( 'Calendar', 'wproject-calendar-pro' ); ?></h2>

    <?php if ( ! empty( $all_calendars ) ) : ?>
    <div class="calendar-selector-wrapper">
        <select id="calendar-selector">
            <option value=""><?php _e( 'All Calendars', 'wproject-calendar-pro' ); ?></option>
            <?php foreach ( $all_calendars as $calendar ) : ?>
                <option value="<?php echo esc_attr( $calendar->id ); ?>"
                        <?php echo isset($calendar->is_default) && $calendar->is_default ? 'data-default="1"' : ''; ?>>
                    <?php echo esc_html( $calendar->name ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <!-- NEW: Calendar management buttons -->
        <button class="btn btn-icon btn-new-calendar" title="<?php esc_attr_e( 'New Calendar', 'wproject-calendar-pro' ); ?>">
            <i data-feather="plus-circle"></i>
        </button>
        
        <button class="btn btn-icon btn-manage-calendar" 
                title="<?php esc_attr_e( 'Manage Calendar', 'wproject-calendar-pro' ); ?>"
                style="display: none;">
            <i data-feather="settings"></i>
        </button>
    </div>
    <?php else : ?>
    <div class="no-calendars-notice">
        <p><?php _e( 'You don\'t have any calendars yet.', 'wproject-calendar-pro' ); ?></p>
        <button class="btn btn-primary btn-new-calendar">
            <i data-feather="plus"></i>
            <?php _e( 'Create Your First Calendar', 'wproject-calendar-pro' ); ?>
        </button>
    </div>
    <?php endif; ?>
</div>
```

Add modal include at the end:

```php
<?php
// Include event modal
include CALENDAR_PRO_PLUGIN_PATH . 'templates/event-form.php';

// Include event detail modal
include CALENDAR_PRO_PLUGIN_PATH . 'templates/event-detail.php';

// NEW: Include calendar management modal
include CALENDAR_PRO_PLUGIN_PATH . 'templates/calendar-management-modal.php';
?>
```

---

## 5. JavaScript Updates

### File: `assets/js/calendar.js`

Add calendar management functionality:

```javascript
var CalendarPro = {

    calendar: null,
    currentCalendarId: null,
    currentEditingCalendarId: null,

    init: function() {
        this.bindEvents();
        this.initializeCalendar();
        this.autoSelectDefaultCalendar(); // NEW
    },

    bindEvents: function() {
        var self = this;

        // Existing bindings...

        // NEW: Calendar management bindings
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

        $(document).on('click', '.calendar-color-option', function() {
            $('.calendar-color-option').removeClass('selected');
            $(this).addClass('selected');
            $('#calendar-color-value').val($(this).data('color'));
        });

        // Show/hide manage button based on calendar selection
        $(document).on('change', '#calendar-selector', function() {
            var calendarId = $(this).val();
            if (calendarId) {
                $('.btn-manage-calendar').show();
            } else {
                $('.btn-manage-calendar').hide();
            }
        });
    },

    /**
     * NEW: Auto-select default calendar
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
     * NEW: Show calendar management modal
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
    },

    /**
     * NEW: Save calendar
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
     * NEW: Refresh calendar list in dropdown
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

    // Update showEventModal to use selected calendar
    showEventModal: function(selectInfo) {
        var modal = $('#event-modal');

        // Reset form
        $('#event-form')[0].reset();
        $('#event-id').val('');
        $('.event-type-option').first().click();
        $('.calendar-color-option').first().click();

        // NEW: Auto-set calendar from dropdown or default
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
    }
};
```

---

## 6. CSS Styles

### File: `assets/css/calendar.css`

Add styles for calendar management UI:

```css
/* Calendar selector wrapper */
.calendar-selector-wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
}

#calendar-selector {
    min-width: 200px;
}

.btn-icon {
    padding: 8px;
    min-width: auto;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.btn-icon svg {
    width: 18px;
    height: 18px;
}

/* No calendars notice */
.no-calendars-notice {
    padding: 20px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 8px;
    text-align: center;
}

.no-calendars-notice p {
    margin-bottom: 15px;
    color: #999;
}

/* Calendar color picker */
.calendar-color-picker {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.calendar-color-options {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.calendar-color-option {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    cursor: pointer;
    border: 3px solid transparent;
    transition: all 0.2s ease;
}

.calendar-color-option:hover {
    transform: scale(1.1);
}

.calendar-color-option.selected {
    border-color: #fff;
    box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.2);
}

/* Calendar form */
#calendar-form .form-row {
    margin-bottom: 20px;
}

#calendar-form label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
}

#calendar-form .required {
    color: #f44336;
}

#calendar-form input[type="text"],
#calendar-form textarea,
#calendar-form select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

#calendar-form textarea {
    resize: vertical;
}
```

---

## Testing Criteria

### Auto-Calendar Creation Tests
1. Create a new WordPress user → Should automatically get a default calendar
2. Check existing users without calendars → Run migration to create calendars
3. Verify calendar is marked as `is_default = 1`
4. Verify calendar name format: "Username's Calendar"

### Calendar Management UI Tests
1. Click "+ New Calendar" → Modal should open
2. Fill out form and save → Calendar should be created
3. Select calendar from dropdown and click settings → Should load calendar for editing
4. Update calendar properties → Changes should save
5. Delete calendar → Should confirm and remove (except default calendar)

### Auto-Selection Tests
1. User with one calendar → Should auto-select in event form
2. User with multiple calendars → Should select default calendar
3. Change calendar selection → New events should use selected calendar
4. No calendar selected → Event creation should fail gracefully

### Permission Tests
1. Try to edit another user's calendar → Should fail
2. Try to delete shared calendar you don't own → Should fail
3. Verify calendar owner can manage their calendars

---

## Migration Instructions

### For Existing Installations

Run this once after deploying PHASE-1C:

**Option A - Via WordPress Admin:**
1. Add admin notice with "Fix Missing Calendars" button
2. Button calls `WProject_Calendar_Core::create_missing_default_calendars()`
3. Display results to admin

**Option B - Via WP-CLI:**
```bash
wp eval 'var_dump(WProject_Calendar_Core::create_missing_default_calendars());'
```

**Option C - Via Code (one-time):**
Add temporary code to admin page:
```php
if ( current_user_can( 'manage_options' ) && isset( $_GET['fix_calendars'] ) ) {
    $results = WProject_Calendar_Core::create_missing_default_calendars();
    echo '<div class="notice notice-success"><p>Created ' . $results['created'] . ' calendars</p></div>';
}
```

---

## Implementation Priority

1. **First:** Auto-calendar creation hook (15 min)
2. **Second:** Migration function for existing users (15 min)
3. **Third:** AJAX handlers for calendar CRUD (45 min)
4. **Fourth:** Calendar management modal UI (60 min)
5. **Fifth:** JavaScript calendar management (60 min)
6. **Sixth:** CSS styling (30 min)
7. **Seventh:** Testing and refinement (30 min)

---

## Related Files
- `includes/class-calendar-core.php` - Auto-creation logic
- `includes/ajax-handlers.php` - New AJAX handlers
- `templates/calendar-view.php` - UI updates
- `templates/calendar-management-modal.php` - New modal
- `assets/js/calendar.js` - Calendar management JS
- `assets/css/calendar.css` - Styling

## Next Phase
After completing these calendar management features, proceed to [PHASE-1B-CORE-FIXES.md](./PHASE-1B-CORE-FIXES.md) for remaining core functionality improvements.
