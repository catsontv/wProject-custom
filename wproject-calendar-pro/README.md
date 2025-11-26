# Calendar Pro for wProject

Advanced calendar and event management plugin for wProject theme.

## Version
1.0.0

## Description

Calendar Pro extends wProject with comprehensive calendar and event management capabilities. Create events, meetings, deadlines, and reminders with a beautiful, fully-integrated calendar interface that matches the wProject design system.

## Features

### Core Functionality
- **Multiple Calendars** - Create and manage multiple calendars with custom colors
- **Event Types** - Events, meetings, deadlines, and reminders
- **Recurring Events** - Daily, weekly, monthly, and yearly recurrence patterns
- **Event Reminders** - Email reminders at customizable intervals
- **Calendar Sharing** - Share calendars with team members or specific users
- **Meeting Management** - Track attendees and their response status
- **Project Integration** - Link events to wProject projects and tasks

### Views
- Month view
- Week view
- Day view
- List view
- Responsive mobile design

### User Experience
- Drag and drop event editing
- Click to create events
- Inline event editing
- Event categorization by type
- Color-coded calendars
- Dark mode support
- FullCalendar integration

### Permissions & Privacy
- Private, team, and public event visibility
- User-specific calendars
- Calendar sharing with view/edit permissions
- Role-based access control

### Data Management
- iCal export
- Event soft deletion (trash)
- Auto-cleanup of old cancelled events
- Event duplication
- Bulk operations

## Installation

1. Upload the `wproject-calendar-pro` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to Calendar Pro > License and enter your license key
4. Configure settings in wProject > Settings > Calendar Pro
5. Add calendar to your theme templates or use shortcode

## Requirements

- **WordPress**: 6.0 or higher
- **PHP**: 8.0 or higher
- **wProject Theme**: 5.7.0 or higher

## Usage

### Display Calendar

The calendar automatically integrates with wProject's dashboard and project pages based on settings.

**Manually add to templates:**
```php
<?php
// In your theme template file
if ( function_exists('wProject_Calendar_Core') ) {
    include CALENDAR_PRO_PLUGIN_PATH . 'templates/calendar-view.php';
}
?>
```

### Create Events Programmatically

```php
// Create a simple event
$event_data = array(
    'calendar_id'    => 1,
    'title'          => 'Team Meeting',
    'description'    => 'Weekly standup meeting',
    'location'       => 'Conference Room A',
    'start_datetime' => '2025-01-15 10:00:00',
    'end_datetime'   => '2025-01-15 11:00:00',
    'event_type'     => 'meeting',
    'color'          => '#9c27b0',
    'reminder_enabled' => 1,
    'reminder_minutes' => 15
);

$event_id = WProject_Event_Manager::create_event( $event_data );
```

### Create Recurring Events

```php
// Create a recurring event
$parent_event_id = WProject_Event_Manager::create_event( $event_data );

$recurrence_rule = array(
    'frequency' => 'weekly',  // daily, weekly, monthly, yearly
    'interval'  => 1,
    'by_day'    => array('mon', 'wed', 'fri'),
    'count'     => 20  // or use 'until' => 'YYYY-MM-DD'
);

WProject_Recurring_Events::create_instances(
    $parent_event_id,
    $recurrence_rule,
    '2025-12-31'
);
```

### Share Calendar

```php
// Share with specific user
WProject_Calendar_Manager::share_calendar( $calendar_id, $user_id, 'view' );

// Share with entire team
WProject_Calendar_Manager::share_with_team( $calendar_id, 'edit' );
```

### Export Calendar

```php
// Generate iCal file
$ical_data = WProject_Sharing::export_to_ical( $calendar_id );
header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="calendar.ics"');
echo $ical_data;
```

## Settings

Configure Calendar Pro in **wProject > Settings > Calendar Pro**:

### Display Options
- Show calendar on dashboard
- Show calendar on individual projects
- Default view (month, week, day, list)
- Week start day
- Time format (12/24 hour)

### Features
- Enable/disable event reminders
- Default reminder time
- Enable/disable recurring events
- Enable/disable calendar sharing
- Auto-cleanup interval for cancelled events

### Colors
- Default event color
- Default meeting color
- Default deadline color

## Database Schema

Calendar Pro creates the following custom tables:

- `wp_wproject_calendars` - Calendar definitions
- `wp_wproject_events` - Event data
- `wp_wproject_event_attendees` - Event attendees and their status
- `wp_wproject_calendar_sharing` - Calendar sharing permissions
- `wp_wproject_event_reminders` - Scheduled reminders

## Hooks & Filters

### Actions

```php
// After calendar created
do_action( 'calendar_pro_calendar_created', $calendar_id, $calendar_data );

// After event created
do_action( 'calendar_pro_event_created', $event_id, $event_data );

// After event updated
do_action( 'calendar_pro_event_updated', $event_id, $event_data );

// After event deleted
do_action( 'calendar_pro_event_deleted', $event_id );

// After calendar shared
do_action( 'calendar_pro_calendar_shared', $share_id, $calendar_id, $user_id, $permission );

// After reminder sent
do_action( 'calendar_pro_reminder_sent', $reminder_id, $event_id, $user_id );
```

### Filters

```php
// Modify calendar data before creation
apply_filters( 'calendar_pro_calendar_data', $calendar_data );

// Modify event data before creation
apply_filters( 'calendar_pro_event_data', $event_data );
```

## Development

### File Structure

```
wproject-calendar-pro/
├── assets/
│   ├── css/           # Stylesheets
│   ├── js/            # JavaScript files
│   └── images/        # Icons and images
├── includes/          # PHP classes
├── admin/             # Admin interface files
├── templates/         # Frontend templates
├── languages/         # Translation files
├── wproject-calendar-pro.php  # Main plugin file
├── uninstall.php      # Uninstall script
└── README.md
```

### Classes

- `WProject_Calendar_Core` - Core functionality and database
- `WProject_Event_Manager` - Event CRUD operations
- `WProject_Calendar_Manager` - Calendar CRUD operations
- `WProject_Recurring_Events` - Recurring event handling
- `WProject_Reminders` - Reminder system
- `WProject_Sharing` - Sharing and export functionality
- `WProject_Meetings` - Meeting-specific features
- `WProject_Trash_Manager` - Soft deletion and cleanup

## Support

For support, documentation, and updates:

- **Website**: https://rocketapps.com.au/product/calendar-pro/
- **Support**: https://rocketapps.com.au/log-ticket
- **Documentation**: https://rocketapps.com.au/product/calendar-pro/#docs

## License

This is a commercial plugin. A valid license key is required for updates and support.

## Changelog

### 1.0.0 - 2025-01-26
- Initial release
- Multiple calendars support
- Event types (event, meeting, deadline, reminder)
- Recurring events (daily, weekly, monthly, yearly)
- Event reminders via email
- Calendar sharing with permissions
- Meeting attendee tracking
- Project and task integration
- iCal export
- Dark mode support
- Responsive design
- FullCalendar integration
- Soft deletion and auto-cleanup

## Credits

**Developed by**: Rocket Apps
**JavaScript Libraries**: FullCalendar, Feather Icons
**Compatible with**: wProject Theme v5.7.0+

---

**Last Updated**: January 26, 2025
**Version**: 1.0.0
**Requires PHP**: 8.0+
**Requires WordPress**: 6.0+
**Requires wProject**: 5.7.0+
