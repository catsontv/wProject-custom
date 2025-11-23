# wProject Custom - Complete Project Management System

## Overview

wProject Custom is a comprehensive WordPress-based project management system that provides task management, team collaboration, time tracking, and client management capabilities. The system consists of a core theme and multiple pro plugins that extend functionality.

## Project Structure

```
wProject-custom/
├── wproject/                  # Core WordPress theme
│   ├── admin-functions/       # Backend administration functions
│   ├── css/                   # Theme stylesheets
│   ├── images/                # Theme images and icons
│   ├── inc/                   # Include files and templates
│   ├── js/                    # JavaScript files
│   ├── languages/             # Translation files
│   ├── theme-updates/         # Theme update checker
│   ├── functions.php          # Main theme functions
│   └── style.css              # Main stylesheet
├── clients-pro/               # Client management plugin
├── gantt-pro/                 # Gantt chart visualization plugin
└── reports-pro/               # Reporting and analytics plugin
```

## Core Theme: wProject

### Version
- **Current Version:** 5.7.2
- **Minimum PHP Version:** 8.0
- **Author:** Rocket Apps
- **Website:** https://rocketapps.com.au/wproject-theme/

### Key Features

#### 1. User Management & Roles
- **Administrator**: Full system access
- **Project Manager**: Project and team management
- **Team Member**: Task execution and collaboration
- **Observer**: Read-only access
- **Client**: Limited client-specific access (requires clients-pro plugin)

#### 2. Project Management
- Create and manage unlimited projects
- Project status tracking (In Progress, Planning, Proposed, Complete, Archived, Cancelled)
- Project timelines with start and end dates
- Budget tracking with time allocation and hourly rates
- Project progress visualization
- Team assignment to projects
- Project-specific pages and custom content

#### 3. Task Management
- Task creation and assignment
- Task status management:
  - Not Started
  - In Progress
  - On Hold
  - Complete
  - Incomplete
- Task priority levels:
  - Low
  - Normal
  - High
  - Urgent
- Task relationships (blocked by, similar to, has issues with)
- Subtasks with descriptions
- Task dependencies and milestones
- Task filtering and sorting
- Contextual labels for categorization

#### 4. Time Tracking
- Built-in timer for tracking work on tasks
- Manual time entry for missed sessions
- Time log viewing and editing
- Project and task time summaries
- Overtime tracking and alerts
- Hourly rate calculations

#### 5. Collaboration Features
- Comment system with threading
- @mention notifications
- File attachments to tasks
- Task following/favorites
- Activity feed
- Real-time notifications
- Team member status indicators

#### 6. User Interface
- Responsive mobile-friendly design
- Dark mode support
- Customizable branding (logo, favicon)
- Avatar customization (rounded, circular, square)
- Task spacing options
- Project list styles (standard or dropdown)

#### 7. Notification System
- Email notifications for:
  - Task assignments
  - Comment replies
  - Task completion
  - Project creation
  - Task takeover requests
- Configurable notification preferences
- Custom sender name and email
- In-app notification center

## Plugin Architecture

### 1. Clients Pro Plugin
**Purpose:** Extends wProject with client management capabilities

**Key Features:**
- Client user role with restricted permissions
- Client-specific project views
- Client portal access
- Client contact management
- Client-specific notifications

**Main File:** `clients-pro/wproject-clients-pro.php`

### 2. Gantt Pro Plugin
**Purpose:** Provides visual project timeline management

**Key Features:**
- Interactive Gantt chart visualization
- Timeline view for tasks and projects
- Drag-and-drop task scheduling
- Dependencies visualization
- Multiple scale options (day, week, month)
- Jump to today functionality
- Subtask visibility in charts

**Main File:** `gantt-pro/wproject-gantt-pro.php`

### 3. Reports Pro Plugin
**Purpose:** Advanced reporting and analytics

**Key Features:**
- Time tracking reports
- Project progress analytics
- Team performance metrics
- Custom report generation
- Chart.js integration for visualizations
- Export capabilities

**Main File:** `reports-pro/wproject-reports-pro.php`

## Technical Architecture

### WordPress Integration

#### Custom Post Types
1. **Task** (`post_type: 'task'`)
   - Main content type for project tasks
   - Supports custom fields and taxonomies
   - Hierarchical structure with subtasks

2. **Message** (`post_type: 'message'`)
   - Internal notification system
   - User-specific messages
   - Auto-cleanup based on settings

#### Custom Taxonomies
1. **Project** (`taxonomy: 'project'`)
   - Organizes tasks into projects
   - Stores project metadata
   - Supports hierarchical structure

#### Custom Database Tables
1. **Time Table** (`wp_time`)
   - Stores time tracking entries
   - Links to tasks and users
   - Tracks start/stop times

### Theme Functions

#### Core Functions (`functions.php`)

**User Management:**
- `user_details()` - Retrieves current user preferences and settings
- `user_avatar()` - Displays user avatars with fallback to initials
- `extra_profile_details()` - Extends user profile with custom fields

**Project Functions:**
- `all_projects_list()` - Displays navigation of active projects
- `limited_projects_list()` - Shows projects user has tasks in
- `all_projects_count()` - Counts total active projects
- `project_progress()` - Calculates and displays project completion
- `project_status()` - Displays project status information
- `project_team()` - Shows team members assigned to project

**Task Functions:**
- `my_total_task_count()` - Counts user's incomplete tasks
- `all_tasks_count()` - Counts all active tasks in system
- `task_status()` - Returns task status information
- `task_priority()` - Returns task priority information
- `task_filter()` - Provides task filtering interface

**Time Tracking:**
- `timer_ui()` - Displays task timer interface
- `project_time()` - Calculates total time logged on project
- `task_in_progress()` - Shows currently active timer

**Notification Functions:**
- `messages()` - Displays user notifications
- `message_count()` - Counts unread messages
- Various `notify_when_*` functions for different events

### AJAX Handlers

**File:** `admin-functions/functions-ajax.php`

Handles real-time updates for:
- Task status changes
- Time tracking start/stop
- Comment submission
- File uploads
- Task assignments
- Project updates

### Security Features

1. **Role-Based Access Control:**
   - `wproject_admin_control()` - Restricts admin access
   - `user_project_tasks_count()` - Filters visible tasks
   - Permission checks throughout

2. **Data Sanitization:**
   - Input validation on all forms
   - XSS prevention in search queries
   - Nonce verification for actions

3. **Session Management:**
   - Configurable session duration
   - Auto-redirect for unauthorized access

## Configuration

### Theme Settings (`wProject()` function)

**Presentation Options:**
```php
- branding_logo: Custom logo URL
- favicon: Custom favicon URL
- avatar_style: rounded|circular|square
- task_spacing: Enable spacing between tasks
- project_list_style: standard|dropdown
- dark_mode: Enable dark theme
```

**Team Privileges:**
```php
- users_can_create_tasks: Allow task creation
- users_can_assign_tasks: Allow task assignment
- users_can_task_takeover: Allow task takeover
- allow_pm_admin_access: Grant PMs admin access
- project_access: all|limited
```

**Time & Cost:**
```php
- enable_time: Enable time tracking
- overtime: Maximum hours per entry
- currency_symbol: Currency symbol
- currency_symbol_position: l|r (left/right)
- default_project_rate: Default hourly rate
- logged_time_increments: Time entry increments
```

**Notifications:**
```php
- notify_when_task_created: Email on task creation
- notify_when_comment_reply: Email on comment reply
- notify_pm_when_task_complete: Email PM on completion
- response_message_duration: Notification display time
- sender_name: Email sender name
- sender_email: Email sender address
```

**Gantt Options:**
```php
- gantt_show_dashboard: Show on dashboard
- gantt_show_project: Show on project page
- gantt_scale_tasks: day|week|month
- gantt_hide_completed: Hide complete tasks
- gantt_jump_to_today: Auto-scroll to current date
```

**Kanban Options:**
```php
- enable_kanban: Enable Kanban board
- kanban_density: compact|normal|comfortable
- kanban_card_colours: Enable color coding
- kanban_card_descriptions: Show descriptions
```

## Database Schema

### Custom Tables

#### Time Tracking Table
```sql
CREATE TABLE wp_time (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    user_id INT NOT NULL,
    project_id INT NOT NULL,
    time_log INT NOT NULL,
    date_created DATETIME NOT NULL,
    INDEX task_idx (task_id),
    INDEX user_idx (user_id),
    INDEX project_idx (project_id)
);
```

### Post Meta Keys (Tasks)

- `task_status`: complete|incomplete|in-progress|on-hold|not-started
- `task_priority`: low|normal|high|urgent
- `task_start_date`: YYYY-MM-DD format
- `task_end_date`: YYYY-MM-DD format
- `task_description`: Rich text content
- `task_private`: yes|no
- `task_job_number`: Custom job reference
- `task_timer`: on|off (timer running status)
- `task_start_time`: Unix timestamp
- `task_stop_time`: Unix timestamp
- `task_is_blocked_by`: Task ID
- `task_has_issues_with`: Task ID
- `task_is_similar_to`: Task ID
- `task_milestone`: yes|no
- `task_context_label`: Custom label
- `task_pc_complete`: Percentage complete

### Term Meta Keys (Projects)

- `project_status`: in-progress|planning|proposed|complete|archived|cancelled
- `project_manager`: User ID
- `project_start_date`: YYYY-MM-DD format
- `project_end_date`: YYYY-MM-DD format
- `project_time_allocated`: Hours
- `project_hourly_rate`: Decimal
- `project_created_date`: YYYY-MM-DD format
- `project_pep_talk_percentage`: Integer
- `project_pep_talk_message`: Text
- `project_job_number`: Custom reference
- `project_web_page_url`: URL

### User Meta Keys

- `phone`: Contact number
- `skype`: Skype username
- `slack`: Slack username
- `teams`: Microsoft Teams username
- `hangouts`: Google Meet username
- `title`: Job title
- `the_status`: available|away|busy|etc.
- `user_photo`: Avatar URL
- `default_task_order`: Preference
- `default_task_ownership`: yes|no
- `hide_gantt`: yes|no
- `minimise_complete_tasks`: yes|no
- `dark_mode`: yes|no
- `task_wip`: Currently timing task ID
- `fav_tasks`: Comma-separated task IDs
- `notifications_count`: Integer

## JavaScript Libraries

### Core Libraries
1. **jQuery 1.9.1** - DOM manipulation and AJAX
2. **Feather Icons** - SVG icon set
3. **js-cookie** - Cookie management
4. **EasyTimer.js** - Time tracking
5. **Moment.js** - Date manipulation
6. **Chart.js 2.7.0** - Data visualization (Reports Pro)

### Custom Scripts

Located in `wproject/js/`:
- Task management functions
- Timer functionality
- Real-time updates
- Form validation
- UI interactions

## Styling System

### CSS Architecture

**Main Stylesheet:** `wproject/style.css`
- Normalized base styles
- Component-specific styles
- Responsive breakpoints
- Print styles

**Dark Mode:** `wproject/css/dark.css`
- Inverted color scheme
- User preference-based loading

**Key Breakpoints:**
- Mobile: < 960px
- Tablet: 960px - 1360px
- Desktop: > 1360px
- Large Desktop: > 1600px

### Color Scheme

**Primary Colors:**
- Primary: #00bcd4 (Cyan)
- Secondary: #ff9800 (Orange)
- Success: #8bc34a (Green)
- Warning: #ff5722 (Red)
- Info: #9c27b0 (Purple)
- Neutral: #5b606c (Gray)

**Status Colors:**
- Complete: #8bc34a
- In Progress: #9c27b0
- On Hold: #5b606c
- Incomplete: #00bcd4
- Not Started: #5b606c

## API & Hooks

### Actions

```php
// Before template rendering
do_action('before_wp_head');
do_action('after_wp_head');
do_action('before_body_start');
do_action('after_body_start');
do_action('before_body_end');
do_action('after_body_end');

// Template sections
do_action('avatar');
do_action('before_side_nav');
do_action('side_nav');
do_action('projects_selection');
do_action('task_assignment');
do_action('before_tips');
```

### Filters

```php
// Modify user contact methods
apply_filters('user_contactmethods', $methods);

// Modify email settings
apply_filters('wp_mail_from', $email);
apply_filters('wp_mail_from_name', $name);

// Modify authentication
apply_filters('auth_cookie_expiration', $expire);

// Modify login
apply_filters('login_redirect', $redirect_to);
```

### Custom Functions for Plugin Integration

```php
// Check if function exists before calling
if(function_exists('add_client_settings')) {
    $client_settings = wProject_client();
}

// Plugin-specific settings
function wProject() {
    // Returns array of all theme settings
}
```

## Installation

### Requirements
- WordPress 5.0+
- PHP 8.0+
- MySQL 5.6+
- Modern browser with JavaScript enabled

### Setup Steps

1. **Install Theme:**
   ```bash
   # Upload wproject folder to wp-content/themes/
   # Or install via WordPress admin
   ```

2. **Activate Theme:**
   - Navigate to Appearance > Themes
   - Click "Activate" on wProject

3. **Install Plugins:**
   ```bash
   # Upload plugin folders to wp-content/plugins/
   clients-pro/
   gantt-pro/
   reports-pro/
   ```

4. **Activate Plugins:**
   - Navigate to Plugins page
   - Activate desired pro plugins

5. **Configure Settings:**
   - Go to wProject > Settings
   - Configure team privileges
   - Set up notifications
   - Customize appearance

6. **Create Users:**
   - Add users with appropriate roles
   - Configure user profiles

7. **Create First Project:**
   - Navigate to Projects
   - Click "New Project"
   - Assign project manager
   - Set timeline and budget

## Development

### File Organization

**Theme Development:**
```
wproject/
├── functions.php           # Main functions file
├── admin-functions/
│   ├── functions-admin.php # Admin panel functions
│   ├── functions-ajax.php  # AJAX handlers
│   └── functions-task.php  # Task-specific functions
├── inc/                    # Template includes
└── js/                     # Custom JavaScript
```

### Adding Custom Functionality

**1. Custom Task Status:**
```php
// In child theme or plugin
add_filter('wproject_task_statuses', 'add_custom_status');
function add_custom_status($statuses) {
    $statuses['custom-status'] = __('Custom Status', 'textdomain');
    return $statuses;
}
```

**2. Custom Notification:**
```php
add_action('task_status_changed', 'custom_notification', 10, 2);
function custom_notification($task_id, $new_status) {
    // Send custom notification
}
```

**3. Custom Meta Box:**
```php
add_action('add_meta_boxes', 'add_custom_metabox');
function add_custom_metabox() {
    add_meta_box(
        'custom_field',
        'Custom Field',
        'render_custom_field',
        'task'
    );
}
```

### Debugging

**Enable WordPress Debug Mode:**
```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

**Theme Debug Functions:**
```php
// Check user permissions
if(current_user_can('administrator')) {
    var_dump($data);
}
```

## Maintenance

### Updates

**Theme Updates:**
- Built-in update checker pulls from rocketapps.com.au
- Notifications appear in WordPress admin
- Update via Appearance > Themes

**Plugin Updates:**
- Each plugin includes update checker
- Check for updates in Plugins page

### Backups

**Recommended:**
- Install backup plugin (UpdraftPlus, BackWPup)
- Schedule daily database backups
- Weekly file backups
- Store backups offsite

### Performance

**Optimization Tips:**
1. Use caching plugin (WP Super Cache, W3 Total Cache)
2. Optimize images before upload
3. Limit number of projects shown in navigation
4. Regular database cleanup
5. Remove unused plugins

### Security

**Best Practices:**
1. Keep WordPress, theme, and plugins updated
2. Use strong passwords
3. Install security plugin (Wordfence, Sucuri)
4. Regular security audits
5. Limit login attempts
6. Enable two-factor authentication

## Troubleshooting

### Common Issues

**1. White Screen of Death**
- Enable WP_DEBUG to see errors
- Check PHP error logs
- Increase PHP memory limit
- Deactivate plugins one by one

**2. Timer Not Working**
- Verify user is task owner
- Check JavaScript console for errors
- Ensure time tracking is enabled in settings
- Clear browser cache

**3. Notifications Not Sending**
- Verify SMTP settings
- Check sender email configuration
- Test with WP Mail SMTP plugin
- Check spam folder

**4. Projects Not Showing**
- Verify project status (not archived/cancelled)
- Check user permissions
- Clear navigation cache
- Verify taxonomy relationships

## Support & Resources

### Documentation
- Official Website: https://rocketapps.com.au/wproject-theme/
- Theme Documentation: Available in theme package
- Plugin Documentation: Included with each plugin

### Community
- Support Forum: Available for license holders
- Email Support: Via Rocket Apps website

### License
- Theme License: Commercial license required
- Plugin Licenses: Separate licenses for each pro plugin
- License Key: Entered in wProject > Settings > License

## Changelog

### Version 5.7.2 (Current)
- Bug fixes and performance improvements
- Enhanced security measures
- Improved mobile responsiveness
- Updated dependencies

### Version 5.7.x
- Kanban board functionality
- Context labels
- Enhanced time tracking
- Improved notification system

### Version 5.6.x
- Dark mode support
- User status indicators
- Enhanced filtering
- Performance optimizations

## Contributing

This is a custom implementation. For feature requests or bug reports:
1. Document the issue clearly
2. Include WordPress and PHP versions
3. Provide steps to reproduce
4. Include relevant error messages

## Credits

**Developed by:** Rocket Apps  
**Theme Author:** Rocket Apps  
**Website:** https://rocketapps.com.au  
**PHP Libraries:** Plugin Update Checker  
**JavaScript Libraries:** jQuery, Feather Icons, EasyTimer.js, Moment.js, Chart.js  
**Icons:** Feather Icons  

---

**Last Updated:** November 23, 2025  
**Documentation Version:** 1.0  
**Theme Version:** 5.7.2