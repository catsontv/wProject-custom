# Calendar Pro - Quick Start Guide

## Installation Steps

1. **Upload Plugin**
   - Copy `wproject-calendar-pro` folder to `wp-content/plugins/`

2. **Activate Plugin**
   - Go to WordPress Admin → Plugins
   - Find "Calendar Pro for wProject"
   - Click "Activate"

3. **Enable Calendar Display**
   - Go to WordPress Admin → wProject → Settings
   - Click the **"Calendar Pro"** tab (should be visible after activation)
   - Check the boxes:
     - ✅ **Show calendar → Dashboard**
     - ✅ **Show calendar → Individual projects**
   - Configure other settings as desired
   - Click **"Save Settings"** at the bottom

4. **View Calendar**
   - Go to your wProject Dashboard (home page)
   - The calendar should now appear below the Gantt chart (if you have Gantt Pro) or in the main content area
   - On individual project pages, the calendar will show project-specific events

## First Event

To create your first event:

1. Click the **"+ New Event"** button on the calendar
2. Fill in the event details:
   - Title (required)
   - Start and end date/time
   - Description, location (optional)
   - Event type (Event, Meeting, Deadline, Reminder)
   - Color
3. Click **"Save Event"**

You can also click any date on the calendar to quick-create an event!

## Troubleshooting

### Calendar Not Showing?

1. **Check Settings Are Enabled**
   - wProject → Settings → Calendar Pro
   - Make sure checkboxes are checked
   - Click Save Settings

2. **Check Console for Errors**
   - Right-click page → Inspect → Console tab
   - Look for JavaScript errors

3. **Verify Plugin is Active**
   - WordPress Admin → Plugins
   - "Calendar Pro for wProject" should say "Active"

4. **Check Database Tables**
   - Go to phpMyAdmin
   - Look for tables starting with `wp_wproject_calendars`, `wp_wproject_events`, etc.
   - If missing, deactivate and reactivate the plugin

### Events Not Saving?

1. **Check Browser Console**
   - Look for AJAX errors
   - Verify the request to `admin-ajax.php` is successful

2. **Check PHP Error Log**
   - Enable WordPress debug mode
   - Check `wp-content/debug.log`

## Features Overview

### Calendar Views
- **Month** - Monthly grid view
- **Week** - Weekly schedule
- **Day** - Single day timeline
- **List** - List of upcoming events

### Event Types
- **Event** - General events (cyan color by default)
- **Meeting** - Team meetings (purple by default)
- **Deadline** - Project deadlines (red by default)
- **Reminder** - Personal reminders

### Calendar Features
- Drag and drop to reschedule events
- Click to create events
- Multiple calendars per user
- Share calendars with team members
- Email reminders
- Recurring events (daily, weekly, monthly, yearly)
- Dark mode support

## Settings Explained

### Display Options
- **Show calendar → Dashboard** - Display on home/dashboard page
- **Show calendar → Individual projects** - Display on project pages

### Default View
- Choose which view loads first (Month, Week, Day, List)

### Week Starts On
- Sunday, Monday, or Saturday

### Time Format
- 12-hour (2:00 PM) or 24-hour (14:00)

### Event Reminders
- Enable email reminders
- Set default reminder time (5 min to 1 day before event)

### Features
- **Recurring events** - Enable repeating events
- **Calendar sharing** - Share calendars with team

### Default Event Colors
- Set colors for different event types
- Uses color picker for custom colors

### Auto Cleanup
- Automatically delete old cancelled events
- Set days (0 = disabled)

## Next Steps

- Create your first calendar
- Add team events
- Set up recurring meetings
- Configure email reminders
- Share calendars with team members

## Support

For issues or questions, check:
- WordPress debug log
- Browser console for JavaScript errors
- Database tables for data
- Plugin is activated and settings are saved
