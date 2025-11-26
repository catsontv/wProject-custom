# Calendar Pro Installation & Integration Guide

## Quick Start

### 1. Install the Plugin

1. Upload the `wproject-calendar-pro` folder to `wp-content/plugins/`
2. Activate the plugin in WordPress admin under **Plugins**
3. Navigate to **Calendar Pro > License** and activate your license key
4. Go to **wProject > Settings** and click the **Calendar Pro** tab
5. Configure your calendar settings

### 2. Verify Installation

After activation, check:
- ✅ Calendar Pro menu appears in WordPress admin
- ✅ Calendar Pro tab appears in wProject > Settings
- ✅ Database tables are created (check phpMyAdmin for `wp_wproject_*` tables)

## Integration with wProject Theme

### Adding Calendar to Dashboard

The calendar can automatically display on the dashboard. Enable this in:

**wProject > Settings > Calendar Pro > Show calendar > Dashboard**

Alternatively, manually add to your theme template:

**File**: `wproject/inc/home.php` or `wproject/front-page.php`

```php
<?php
// Add after existing dashboard widgets
if ( function_exists('wProject_Calendar_Core') ) {
    $options = get_option( 'wproject_settings' );
    $calendar_show_dashboard = isset($options['calendar_show_dashboard']) ? $options['calendar_show_dashboard'] : '';

    if ( $calendar_show_dashboard ) {
        include CALENDAR_PRO_PLUGIN_PATH . 'templates/calendar-view.php';
    }
}
?>
```

### Adding Calendar to Project Pages

Enable in: **wProject > Settings > Calendar Pro > Show calendar > Individual projects**

Or manually add to project template:

**File**: `wproject/taxonomy-project.php`

```php
<?php
// Add after project details section
if ( function_exists('wProject_Calendar_Core') ) {
    $options = get_option( 'wproject_settings' );
    $calendar_show_project = isset($options['calendar_show_project']) ? $options['calendar_show_project'] : '';

    if ( $calendar_show_project ) {
        // Get current project ID
        $term = get_queried_object();
        $project_id = $term->term_id;

        // Display project-specific calendar
        ?>
        <div class="calendar-pro-wrapper">
            <h2><?php _e('Project Calendar', 'wproject-calendar-pro'); ?></h2>
            <div id="calendar-pro"
                 data-project-id="<?php echo esc_attr($project_id); ?>"
                 data-default-view="dayGridMonth"
                 data-week-start="0"
                 data-time-format="24">
            </div>
        </div>
        <?php
    }
}
?>
```

### Creating Action Hooks in Theme

To allow Calendar Pro to inject itself into theme templates, add these hooks:

**File**: `wproject/inc/home.php` (Dashboard)

```php
<?php
// After the main dashboard content
do_action('calendar_pro_dashboard_page');
?>
```

**File**: `wproject/taxonomy-project.php` (Project page)

```php
<?php
// After the project details
do_action('calendar_pro_project_page');
?>
```

Then in your Calendar Pro plugin, create display functions:

**File**: `wproject-calendar-pro/wproject-calendar-pro.php`

```php
/* Display calendar on dashboard */
function calendar_pro_dashboard_display() {
    $options = get_option( 'wproject_settings' );
    $calendar_show_dashboard = isset($options['calendar_show_dashboard']) ? $options['calendar_show_dashboard'] : '';

    if ( $calendar_show_dashboard && is_front_page() ) {
        include CALENDAR_PRO_PLUGIN_PATH . 'templates/calendar-view.php';
    }
}
add_action( 'calendar_pro_dashboard_page', 'calendar_pro_dashboard_display' );

/* Display calendar on project page */
function calendar_pro_project_display() {
    $options = get_option( 'wproject_settings' );
    $calendar_show_project = isset($options['calendar_show_project']) ? $options['calendar_show_project'] : '';

    if ( $calendar_show_project && is_tax('project') ) {
        include CALENDAR_PRO_PLUGIN_PATH . 'templates/calendar-view.php';
    }
}
add_action( 'calendar_pro_project_page', 'calendar_pro_project_display' );
```

## Dependencies

### Required External Libraries

Calendar Pro requires FullCalendar library for the calendar interface.

**Add to theme or plugin (recommended method):**

**File**: `wproject-calendar-pro/wproject-calendar-pro.php`

Add to the `calendar_pro_scripts()` function:

```php
function calendar_pro_scripts() {
    if(function_exists('wProject')) {
        // FullCalendar CSS
        wp_enqueue_style(
            'fullcalendar',
            'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.0/main.min.css',
            array(),
            '6.1.0'
        );

        // FullCalendar JS
        wp_enqueue_script(
            'fullcalendar',
            'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.0/main.min.js',
            array(),
            '6.1.0',
            true
        );

        // Calendar Pro CSS
        wp_enqueue_style('calendar_pro_css', CALENDAR_PRO_PLUGIN_URL . 'assets/css/calendar.css', array('fullcalendar'), CALENDAR_PRO_VERSION);

        // ... rest of the function
    }
}
```

## Default Calendars

Calendar Pro automatically creates a default calendar for each user upon activation. To manually trigger this for new users:

**File**: Create in `wproject-calendar-pro/includes/user-calendar-setup.php`

```php
<?php
/* Create default calendar for new users */
add_action( 'user_register', 'calendar_pro_create_user_calendar' );
function calendar_pro_create_user_calendar( $user_id ) {
    global $wpdb;

    $table_calendars = $wpdb->prefix . 'wproject_calendars';
    $user_info = get_userdata( $user_id );

    // Check if user already has a default calendar
    $has_default = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM $table_calendars WHERE owner_id = %d AND is_default = 1",
        $user_id
    ) );

    if ( ! $has_default ) {
        $calendar_name = sprintf( __( '%s\'s Calendar', 'wproject-calendar-pro' ), $user_info->display_name );

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
}
```

Then require this file in main plugin file:

```php
require_once( CALENDAR_PRO_PLUGIN_PATH . 'includes/user-calendar-setup.php' );
```

## Troubleshooting

### Calendar Not Displaying

1. **Check if wProject theme is active**
   - Calendar Pro requires wProject theme v5.7.0+

2. **Check JavaScript console for errors**
   - FullCalendar library may not be loading
   - Check network tab for failed requests

3. **Verify settings are enabled**
   - Go to wProject > Settings > Calendar Pro
   - Ensure "Show calendar" options are checked

### Events Not Loading

1. **Check AJAX endpoint**
   - Open browser console
   - Look for failed AJAX requests to `admin-ajax.php`

2. **Verify nonce**
   - Check that `calendar_inputs.nonce` is being passed correctly

3. **Check database tables**
   - Verify tables were created: `wp_wproject_events`, `wp_wproject_calendars`

### Styling Issues

1. **CSS not loading**
   - Check if calendar.css is enqueued
   - Verify file path is correct

2. **Dark mode not applying**
   - User must have dark mode enabled in wProject settings
   - calendar-dark.css should load automatically

### License Activation Fails

1. **Check server connection**
   - Ensure server can connect to rocketapps.com.au
   - Check firewall settings

2. **Verify license key**
   - Copy/paste carefully (no spaces)
   - Check if license is already activated on another site

## Advanced Configuration

### Custom Event Colors by Project

Add to your theme's `functions.php`:

```php
add_filter( 'calendar_pro_event_data', 'customize_event_colors', 10, 1 );
function customize_event_colors( $event_data ) {
    if ( isset( $event_data['project_id'] ) ) {
        // Get project term meta color
        $project_color = get_term_meta( $event_data['project_id'], 'project_color', true );
        if ( $project_color ) {
            $event_data['color'] = $project_color;
        }
    }
    return $event_data;
}
```

### Auto-create Events from Tasks

```php
add_action( 'save_post_task', 'create_calendar_event_from_task', 10, 2 );
function create_calendar_event_from_task( $post_id, $post ) {
    // Only for new tasks
    if ( $post->post_status !== 'publish' ) {
        return;
    }

    $task_end_date = get_post_meta( $post_id, 'task_end_date', true );
    if ( ! $task_end_date ) {
        return;
    }

    // Get default calendar for task owner
    $task_owner = $post->post_author;
    $calendar = WProject_Calendar_Core::get_user_default_calendar( $task_owner );

    if ( ! $calendar ) {
        return;
    }

    // Create deadline event
    $event_data = array(
        'calendar_id'    => $calendar->id,
        'title'          => 'Task Due: ' . $post->post_title,
        'description'    => $post->post_content,
        'start_datetime' => $task_end_date . ' 09:00:00',
        'end_datetime'   => $task_end_date . ' 17:00:00',
        'event_type'     => 'deadline',
        'task_id'        => $post_id,
        'reminder_enabled' => 1,
        'reminder_minutes' => 1440 // 1 day before
    );

    WProject_Event_Manager::create_event( $event_data );
}
```

## Security Considerations

### Capability Checks

Calendar Pro respects wProject's permission system. To customize:

```php
add_filter( 'wproject_required_capabilities', 'calendar_custom_capabilities' );
function calendar_custom_capabilities( $capability ) {
    // Only admins and project managers can manage calendars
    if ( current_user_can( 'administrator' ) || current_user_can( 'project_manager' ) ) {
        return 'manage_options';
    }
    return 'read'; // View only for others
}
```

### Data Validation

All user inputs are sanitized in AJAX handlers. To add custom validation:

```php
add_filter( 'calendar_pro_event_data', 'validate_event_data', 5, 1 );
function validate_event_data( $event_data ) {
    // Ensure event duration is at least 15 minutes
    $start = strtotime( $event_data['start_datetime'] );
    $end = strtotime( $event_data['end_datetime'] );

    if ( ($end - $start) < 900 ) { // 15 minutes
        $event_data['end_datetime'] = date( 'Y-m-d H:i:s', $start + 900 );
    }

    return $event_data;
}
```

## Performance Optimization

### Caching

Implement transient caching for frequently accessed data:

```php
function get_user_events_cached( $user_id, $start_date, $end_date ) {
    $cache_key = 'calendar_events_' . $user_id . '_' . $start_date . '_' . $end_date;
    $events = get_transient( $cache_key );

    if ( false === $events ) {
        $events = WProject_Event_Manager::get_user_events( $user_id, $start_date, $end_date );
        set_transient( $cache_key, $events, HOUR_IN_SECONDS );
    }

    return $events;
}

// Clear cache when events change
add_action( 'calendar_pro_event_created', 'clear_calendar_cache' );
add_action( 'calendar_pro_event_updated', 'clear_calendar_cache' );
add_action( 'calendar_pro_event_deleted', 'clear_calendar_cache' );
function clear_calendar_cache( $event_id ) {
    // Clear all calendar caches (crude but effective)
    global $wpdb;
    $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_calendar_events_%'" );
}
```

## Support

For issues or questions:
- **Email**: support@rocketapps.com.au
- **Documentation**: https://rocketapps.com.au/product/calendar-pro/
- **Support Portal**: https://rocketapps.com.au/log-ticket

---

**Last Updated**: January 26, 2025
**Version**: 1.0.0
