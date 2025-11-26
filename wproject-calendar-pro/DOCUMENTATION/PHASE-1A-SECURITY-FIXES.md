# Phase 1A: Critical Security Fixes

**Priority:** URGENT - Must be completed before deployment  
**Status:** Pending  
**Estimated Time:** 2-3 hours

## Overview

This phase addresses critical security vulnerabilities that could allow unauthorized access, data manipulation, or SQL injection attacks. These issues must be fixed before any production deployment.

---

## Security Issues Checklist

### Permission & Authorization
- [ ] Add capability checks to all AJAX handlers
- [ ] Verify calendar ownership before modifications
- [ ] Verify event ownership before modifications  
- [ ] Check user permissions for shared calendar operations
- [ ] Validate attendee permissions before allowing edits

### SQL Injection Prevention
- [ ] Fix string concatenation in `get_calendar_events()`
- [ ] Fix string concatenation in `get_user_events()`
- [ ] Fix string concatenation in `get_project_events()`
- [ ] Ensure all user input uses prepared statements

### Nonce Verification
- [ ] Verify nonce checks terminate execution properly
- [ ] Ensure nonces are generated with correct action names
- [ ] Add nonce checks to any missing AJAX endpoints

---

## 1. Missing Permission Checks in AJAX Handlers

### Problem
All AJAX handlers verify nonce but don't check if the logged-in user has permission to perform the operation. Any authenticated user can create, update, or delete ANY event or calendar without ownership validation.

### Files Affected
- `includes/ajax-handlers.php` (all functions)

### Required Fixes

#### Event Operations
Add permission checks to these functions:
- `calendar_pro_create_event()` - verify user can create events in the calendar
- `calendar_pro_update_event()` - verify user owns event or has edit permission
- `calendar_pro_delete_event()` - verify user owns event or calendar

```php
// Example for update_event
function calendar_pro_update_event() {
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'calendar_inputs' ) ) {
        calendar_ajaxStatus( 'error', __( 'Nonce check failed.', 'wproject-calendar-pro' ) );
    }

    $event_id = isset( $_POST['event_id'] ) ? (int) $_POST['event_id'] : 0;
    
    // NEW: Check event ownership or edit permission
    $event = WProject_Event_Manager::get_event( $event_id );
    if ( ! $event ) {
        calendar_ajaxStatus( 'error', __( 'Event not found.', 'wproject-calendar-pro' ) );
    }
    
    $current_user = get_current_user_id();
    $can_edit = ( $event->owner_id == $current_user ) || 
                WProject_Event_Manager::user_can_edit_event( $event_id, $current_user );
    
    if ( ! $can_edit ) {
        calendar_ajaxStatus( 'error', __( 'Permission denied.', 'wproject-calendar-pro' ) );
    }
    
    // Rest of function...
}
```

#### Calendar Operations
Add permission checks to:
- `calendar_pro_create_calendar()` - verify user has calendar creation capability
- `calendar_pro_update_calendar()` - verify user owns calendar
- `calendar_pro_delete_calendar()` - verify user owns calendar
- `calendar_pro_share_calendar()` - verify user owns calendar

```php
// Example for delete_calendar
function calendar_pro_delete_calendar() {
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'calendar_inputs' ) ) {
        calendar_ajaxStatus( 'error', __( 'Nonce check failed.', 'wproject-calendar-pro' ) );
    }

    $calendar_id = isset( $_POST['calendar_id'] ) ? (int) $_POST['calendar_id'] : 0;
    
    // NEW: Check calendar ownership
    $calendar = WProject_Calendar_Manager::get_calendar( $calendar_id );
    if ( ! $calendar ) {
        calendar_ajaxStatus( 'error', __( 'Calendar not found.', 'wproject-calendar-pro' ) );
    }
    
    if ( $calendar->owner_id != get_current_user_id() ) {
        calendar_ajaxStatus( 'error', __( 'Permission denied.', 'wproject-calendar-pro' ) );
    }
    
    // Rest of function...
}
```

### New Helper Methods Required

Add to `class-event-manager.php`:

```php
/**
 * Check if user can edit event
 *
 * @param int $event_id Event ID
 * @param int $user_id User ID
 * @return bool True if user can edit
 */
public static function user_can_edit_event( $event_id, $user_id ) {
    global $wpdb;
    
    $event = self::get_event( $event_id );
    if ( ! $event ) {
        return false;
    }
    
    // Owner can always edit
    if ( $event->owner_id == $user_id ) {
        return true;
    }
    
    // Check attendee edit permission
    $table_attendees = $wpdb->prefix . 'wproject_event_attendees';
    $can_edit = $wpdb->get_var( $wpdb->prepare(
        "SELECT can_edit FROM $table_attendees 
         WHERE event_id = %d AND user_id = %d AND can_edit = 1",
        $event_id,
        $user_id
    ) );
    
    return (bool) $can_edit;
}
```

Add to `class-calendar-manager.php`:

```php
/**
 * Check if user can access calendar
 *
 * @param int $calendar_id Calendar ID
 * @param int $user_id User ID
 * @return bool True if user can access
 */
public static function user_can_access_calendar( $calendar_id, $user_id ) {
    global $wpdb;
    
    $calendar = self::get_calendar( $calendar_id );
    if ( ! $calendar ) {
        return false;
    }
    
    // Owner can always access
    if ( $calendar->owner_id == $user_id ) {
        return true;
    }
    
    // Check shared access
    $table_shares = $wpdb->prefix . 'wproject_calendar_shares';
    $has_access = $wpdb->get_var( $wpdb->prepare(
        "SELECT id FROM $table_shares 
         WHERE calendar_id = %d AND user_id = %d",
        $calendar_id,
        $user_id
    ) );
    
    return (bool) $has_access;
}
```

---

## 2. SQL Injection Vulnerabilities

### Problem
The `get_calendar_events()`, `get_user_events()`, and `get_project_events()` functions build WHERE clauses using string concatenation instead of prepared statements. Date parameters could allow SQL injection if not properly sanitized.

### Files Affected
- `includes/class-event-manager.php`

### Required Fixes

#### Fix get_calendar_events()

**Current vulnerable code (lines ~186-202):**
```php
$where = $wpdb->prepare( "calendar_id = %d", $calendar_id );

if ( $start_date && $end_date ) {
    $where .= $wpdb->prepare(
        " AND ((start_datetime >= %s AND start_datetime <= %s) OR (end_datetime >= %s AND end_datetime <= %s))",
        $start_date,
        $end_date,
        $start_date,
        $end_date
    );
}

$events = $wpdb->get_results(
    "SELECT * FROM $table_events WHERE $where ORDER BY start_datetime ASC"
);
```

**Fixed version:**
```php
public static function get_calendar_events( $calendar_id, $start_date = null, $end_date = null ) {
    global $wpdb;

    $table_events = $wpdb->prefix . 'wproject_events';

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
            "SELECT * FROM $table_events 
             WHERE calendar_id = %d 
             ORDER BY start_datetime ASC",
            $calendar_id
        ) );
    }

    return $events;
}
```

#### Fix get_user_events()

**Current vulnerable code (lines ~218-240):**
```php
$where = "1=1";

if ( $start_date && $end_date ) {
    $where .= $wpdb->prepare(
        " AND ((e.start_datetime >= %s AND e.start_datetime <= %s) OR (e.end_datetime >= %s AND e.end_datetime <= %s))",
        $start_date,
        $end_date,
        $start_date,
        $end_date
    );
}

$events = $wpdb->get_results( $wpdb->prepare(
    "SELECT DISTINCT e.*, c.name as calendar_name, c.color as calendar_color
    FROM $table_events e
    INNER JOIN $table_calendars c ON e.calendar_id = c.id
    LEFT JOIN $table_attendees a ON e.id = a.event_id
    WHERE $where AND (c.owner_id = %d OR a.user_id = %d)
    ORDER BY e.start_datetime ASC",
    $user_id,
    $user_id
) );
```

**Fixed version:**
```php
public static function get_user_events( $user_id = 0, $start_date = null, $end_date = null ) {
    global $wpdb;

    if ( ! $user_id ) {
        $user_id = get_current_user_id();
    }

    $table_events = $wpdb->prefix . 'wproject_events';
    $table_calendars = $wpdb->prefix . 'wproject_calendars';
    $table_attendees = $wpdb->prefix . 'wproject_event_attendees';

    if ( $start_date && $end_date ) {
        $events = $wpdb->get_results( $wpdb->prepare(
            "SELECT DISTINCT e.*, c.name as calendar_name, c.color as calendar_color
            FROM $table_events e
            INNER JOIN $table_calendars c ON e.calendar_id = c.id
            LEFT JOIN $table_attendees a ON e.id = a.event_id
            WHERE (c.owner_id = %d OR a.user_id = %d)
            AND ((e.start_datetime >= %s AND e.start_datetime <= %s) 
                 OR (e.end_datetime >= %s AND e.end_datetime <= %s))
            ORDER BY e.start_datetime ASC",
            $user_id,
            $user_id,
            $start_date,
            $end_date,
            $start_date,
            $end_date
        ) );
    } else {
        $events = $wpdb->get_results( $wpdb->prepare(
            "SELECT DISTINCT e.*, c.name as calendar_name, c.color as calendar_color
            FROM $table_events e
            INNER JOIN $table_calendars c ON e.calendar_id = c.id
            LEFT JOIN $table_attendees a ON e.id = a.event_id
            WHERE (c.owner_id = %d OR a.user_id = %d)
            ORDER BY e.start_datetime ASC",
            $user_id,
            $user_id
        ) );
    }

    return $events;
}
```

#### Fix get_project_events()

Apply the same pattern as `get_calendar_events()` - use conditional logic instead of string concatenation.

---

## 3. Proper Error Handling & Exit

### Problem
After nonce verification fails, the code calls `calendar_ajaxStatus()` but doesn't explicitly exit, which could allow execution to continue.

### Files Affected
- `includes/ajax-handlers.php` (all AJAX functions)

### Required Fix

The `calendar_ajaxStatus()` function already calls `wp_send_json()` which exits, but it's not explicit. Add a return statement for clarity:

```php
function calendar_ajaxStatus($status, $message, $data = array()) {
    $response = array(
        'status'  => $status,
        'message' => $message,
        'data'    => $data
    );
    wp_send_json($response);
    exit; // Add explicit exit for clarity
}
```

Or update each AJAX handler to explicitly return after failed checks:

```php
if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'calendar_inputs' ) ) {
    calendar_ajaxStatus( 'error', __( 'Nonce check failed.', 'wproject-calendar-pro' ) );
    return; // Add explicit return
}
```

---

## Testing Criteria

### Permission Tests
1. Create calendar as User A
2. Try to modify User A's calendar as User B → should fail with "Permission denied"
3. Share calendar from A to B with view-only permission
4. Try to delete calendar as User B → should fail
5. Try to create event in User A's calendar as User B → should fail unless calendar is shared with edit permission

### SQL Injection Tests
1. Use sqlmap or manual testing to verify date parameters can't inject SQL
2. Test with malicious input: `start_date = "2024-01-01' OR '1'='1"`
3. Verify all database queries use proper escaping

### Nonce Tests
1. Submit AJAX request without nonce → should fail
2. Submit AJAX request with invalid nonce → should fail
3. Submit AJAX request with expired nonce → should fail
4. Submit valid request → should succeed

---

## Implementation Priority

1. **First:** Fix SQL injection issues (30 minutes)
2. **Second:** Add permission helper methods (30 minutes)
3. **Third:** Update all AJAX handlers with permission checks (60 minutes)
4. **Fourth:** Test all security fixes (30 minutes)

---

## Related Files
- `includes/ajax-handlers.php`
- `includes/class-event-manager.php`
- `includes/class-calendar-manager.php`

## Next Phase
After completing these security fixes, proceed to [PHASE-1B-CORE-FIXES.md](./PHASE-1B-CORE-FIXES.md)
