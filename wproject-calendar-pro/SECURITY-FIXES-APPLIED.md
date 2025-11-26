# Security Fixes Applied - Calendar Pro v1.0.0

This document outlines the security vulnerabilities from PHASE-1A-SECURITY-FIXES that have been addressed in this plugin version.

## Overview

All three critical security vulnerabilities identified in the PHASE-1A security audit have been remediated:

1. **SQL Injection Vulnerabilities** - FIXED
2. **Missing Permission Checks** - FIXED
3. **New User Calendar Initialization** - FIXED

---

## 1. SQL Injection Vulnerabilities - FIXED

### Problem
Three functions in the Event Manager were building database queries using string concatenation instead of fully parameterized prepared statements:
- `get_calendar_events()`
- `get_user_events()`
- `get_project_events()`

### Solution
Refactored all three functions to use conditional logic **within** prepared statements instead of building WHERE clauses with string concatenation.

### File: `includes/class-event-manager.php`

**Before (Vulnerable):**
```php
$where = $wpdb->prepare( "calendar_id = %d", $calendar_id );
if ( $start_date && $end_date ) {
    $where .= $wpdb->prepare( "..." );  // String concatenation!
}
$events = $wpdb->get_results(
    "SELECT * FROM $table_events WHERE $where ..."  // Unescaped $where
);
```

**After (Fixed):**
```php
if ( $start_date && $end_date ) {
    $events = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM $table_events
         WHERE calendar_id = %d
         AND ((start_datetime >= %s AND start_datetime <= %s)
              OR (end_datetime >= %s AND end_datetime <= %s))
         ORDER BY start_datetime ASC",
        $calendar_id,
        $start_date,
        $end_date,
        $start_date,
        $end_date
    ) );
} else {
    $events = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM $table_events WHERE calendar_id = %d ORDER BY start_datetime ASC",
        $calendar_id
    ) );
}
```

**Impact:** All user inputs are now properly parameterized, preventing SQL injection attacks through date filtering parameters.

---

## 2. Missing Permission Checks - FIXED

### Problem
AJAX handlers only verified nonce tokens but did **not** check if the current user owned or had permission to access the resource being modified:
- Any authenticated user could create/update/delete ANY event
- Any authenticated user could modify ANY calendar
- No ownership validation before operations

### Solution

#### Created New Permission Class: `includes/class-permissions.php`

Added comprehensive authorization checking with these helper methods:

- `user_can_edit_event( $event_id, $user_id )` - Only owner or calendar owner or attendee with edit permission
- `user_can_access_event( $event_id, $user_id )` - Owner, attendee, calendar owner, or team visibility
- `user_can_delete_event( $event_id, $user_id )` - Only owner or calendar owner
- `user_can_edit_calendar( $calendar_id, $user_id )` - Only owner or shared with edit permission
- `user_can_access_calendar( $calendar_id, $user_id )` - Owner, public, or shared with view/edit permission
- `user_can_delete_calendar( $calendar_id, $user_id )` - Only owner
- `user_can_create_calendar( $user_id )` - Any authenticated user
- `user_can_create_event( $calendar_id, $user_id )` - Owner or has edit permission to calendar

#### Updated AJAX Handlers: `includes/ajax-handlers.php`

All AJAX endpoints now include permission checks AFTER nonce verification:

**Event AJAX Handlers:**
- `calendar_pro_get_events()` - Checks `user_can_access_calendar()` and filters by `user_can_access_event()`
- `calendar_pro_create_event()` - Checks `user_can_create_event()` before allowing creation
- `calendar_pro_update_event()` - Checks `user_can_edit_event()` before allowing modification
- `calendar_pro_delete_event()` - Checks `user_can_delete_event()` before allowing deletion

**Calendar AJAX Handlers:**
- `calendar_pro_create_calendar()` - Checks user is logged in
- `calendar_pro_update_calendar()` - Checks `user_can_edit_calendar()`
- `calendar_pro_delete_calendar()` - Checks `user_can_delete_calendar()`
- `calendar_pro_share_calendar()` - Checks `user_can_delete_calendar()` (owner only can share)

**Example Implementation:**
```php
add_action( 'wp_ajax_calendar_pro_update_event', 'calendar_pro_update_event' );
function calendar_pro_update_event() {
    // 1. Verify nonce
    if ( ! wp_verify_nonce( $_POST['nonce'], 'calendar_inputs' ) ) {
        calendar_ajaxStatus( 'error', 'Nonce check failed.' );
        return;  // Explicit return for clarity
    }

    $event_id = (int) $_POST['event_id'];

    // 2. Check user can edit this specific event
    if ( ! WProject_Calendar_Permissions::user_can_edit_event( $event_id ) ) {
        calendar_ajaxStatus( 'error', 'Permission denied.' );
        return;  // Explicit return for clarity
    }

    // 3. Proceed with update
    WProject_Event_Manager::update_event( $event_id, $event_data );
}
```

**Impact:** Only resource owners and users with explicit permissions can modify resources. Unauthorized access is immediately denied.

---

## 3. Proper Error Termination - FIXED

### Problem
AJAX handlers should explicitly return after failed nonce verification to ensure no code continues execution.

### Solution
Added explicit `return;` statements after all `calendar_ajaxStatus()` calls that represent error conditions.

**Example:**
```php
if ( ! wp_verify_nonce( $_POST['nonce'], 'calendar_inputs' ) ) {
    calendar_ajaxStatus( 'error', __( 'Nonce check failed.', 'wproject-calendar-pro' ) );
    return;  // Explicit return - code will not continue
}
```

**Impact:** Clearer code flow and guaranteed execution termination after errors, preventing any accidental code execution.

---

## 4. NEW USER DEFAULT CALENDAR CREATION - FIXED

### Problem
New users registering on the system did not automatically get a default calendar. When they tried to create an event:
- They received "Permission Denied" errors
- They had no calendar to assign the event to
- The system expected a default calendar that didn't exist

### Solution

#### Updated Calendar Core Class: `includes/class-calendar-core.php`

Added hook to `user_register` action to automatically create a default calendar for new users:

```php
// In __construct's init_hooks():
add_action( 'user_register', array( $this, 'create_new_user_calendar' ), 10, 1 );

/**
 * Create default calendar for new registered user
 */
public function create_new_user_calendar( $user_id ) {
    // Check if user is not a client role
    $user = get_userdata( $user_id );
    if ( ! $user || in_array( 'client', $user->roles ) ) {
        return;
    }

    // Create default calendar for this new user
    $calendar_name = sprintf( __( '%s\'s Calendar', 'wproject-calendar-pro' ), $user->display_name );

    $wpdb->insert(
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
}
```

**Impact:** New users can immediately create events without encountering permission errors. A personal "My Calendar" is ready upon registration.

---

## Testing the Fixes

### 1. Test SQL Injection Protection
```php
// This would previously allow SQL injection, now it's safe
$_POST['start'] = "2024-01-01' OR '1'='1";
$_POST['end'] = "2024-12-31' OR '1'='1";
// Result: Properly escaped via wpdb->prepare()
```

### 2. Test Permission Checks
```php
// User A creates an event
$event_id = WProject_Event_Manager::create_event( [...] );

// User B tries to update User A's event
$_POST['event_id'] = $event_id;
$_POST['nonce'] = wp_create_nonce('calendar_inputs');
// Result: Permission denied - User B is not the owner
```

### 3. Test New User Calendar
```php
// Create new user via registration form
$user_id = wp_create_user( 'newuser', 'password', 'user@example.com' );
// Or via wp_insert_user()
// Result: Default calendar automatically created for the user
$default_cal = WProject_Calendar_Core::get_user_default_calendar( $user_id );
// Result: $default_cal is not null - calendar exists!
```

---

## Files Modified

1. **includes/class-event-manager.php** - Fixed SQL injection vulnerabilities
2. **includes/class-permissions.php** - NEW: Comprehensive permission checking
3. **includes/ajax-handlers.php** - Added permission checks to all AJAX endpoints
4. **includes/class-calendar-core.php** - Added new user default calendar creation hook

---

## Backwards Compatibility

All changes are backwards compatible:
- Existing function signatures remain the same
- New permission functions are additions, not replacements
- AJAX endpoints maintain the same request/response format
- Database schema is unchanged

---

## Compliance

These fixes address:
- **OWASP Top 10** - A03:2021 Injection (SQL Injection)
- **OWASP Top 10** - A01:2021 Broken Access Control
- **WordPress Security Best Practices** - Permission and Authorization checks
- **WordPress Coding Standards** - Proper use of `wpdb->prepare()`

---

## Version History

- **v1.0.1** - PHASE-1A Security fixes implemented
- **v1.0.0** - Initial release

---

**Last Updated:** 2024
**Security Audit:** PHASE-1A-SECURITY-FIXES.md
