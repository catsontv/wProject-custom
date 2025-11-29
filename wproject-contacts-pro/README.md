# wProject Contacts Pro

**Version:** 1.0.0  
**Author:** Custom Development for wProject  
**Requires:** wProject Theme 5.7.2+, WordPress 5.0+, PHP 8.0+  
**License:** Commercial  

## Overview

Contacts Pro is a comprehensive contact and company management plugin for wProject that extends the project management system with client, vendor, and team directory capabilities. The plugin provides a centralized database for managing business relationships, linking contacts to projects, tasks, and calendar events.

## Key Features

### Company Management
- **Company-Centric Structure**: Organize contacts by company
- **Company Profiles**: Store company information including name, website, main phone, main email, and type
- **Company Types**: Categorize companies as Client, Vendor, Partner, or custom types
- **Multiple Contacts per Company**: Link unlimited contacts to each company
- **Company Search & Filtering**: Quickly find companies across your database

### Contact Management
- **Comprehensive Contact Profiles**: Store detailed information for each person
- **Multiple Communication Channels**:
  - Multiple emails with labels (Work, Personal, Assistant, Other)
  - Multiple cell phones with labels (Mobile, Assistant Mobile, Other)
  - Multiple local phones with labels (Office, Home, Fax, Other)
  - Preferred contact method flagging
- **Social Profile Integration**: LinkedIn, Twitter/X, Facebook links
- **Role & Department Tracking**: Predefined roles (CEO, CTO, PM, Developer, etc.) with custom role support
- **Contact Photos**: Gravatar auto-fetch with manual upload override
- **Identification Fields**: ID and Passport number storage with role-based visibility
- **Contact Tags**: Custom taxonomy for categorizing and filtering contacts

### Project Integration
- **Link Contacts to Multiple Projects**: Associate contacts with unlimited projects
- **Contact Panel on Project Pages**: Dedicated section showing linked contacts
- **Add/Remove Contacts Dynamically**: Manage project contacts without leaving project page
- **Project Creation from Contacts**: Quick project creation with pre-linked contacts

### Task Integration
- **Link Contacts to Tasks**: Associate external contacts with specific tasks
- **Contact Selector in Task Form**: Dropdown selection when creating/editing tasks
- **Task Assignment Notifications**: Email contacts when linked to tasks (optional)
- **Contact Task History**: View all tasks associated with a contact

### Calendar Integration
- **External Contacts in Events**: Separate field for non-team member attendees
- **Event Invitations**: Automatically send event details to linked contacts
- **Contact Calendar View**: See all events a contact is attending
- **RSVP Tracking**: Track contact attendance status (if they respond)

### Activity Timeline
- **Chronological Activity Feed**: View all interactions with a contact
- **Tracked Activities**:
  - Projects linked to contact (with dates)
  - Tasks assigned to contact (with status)
  - Calendar events attended (with dates)
- **Filtered Views**: Show all activities or filter by Projects/Tasks/Events
- **Date-Sorted Display**: Most recent activity first

### Quick Actions
- **Email Contact**: Click to open default email client with pre-filled address
- **Call Contact**: Click-to-call with tel: link (mobile-friendly)
- **Create Task for Contact**: Opens new task form with contact pre-selected
- **Create Project for Contact**: Opens new project form with contact pre-linked
- **View Full Profile**: Opens contact detail slide-in panel
- **Edit Contact**: Quick access to edit form

### Import/Export (V1)
- **CSV Import**: Bulk import contacts from CSV files
- **CSV Export**: Export all or filtered contacts to CSV
- **vCard Export**: Generate .vcf files for individual contacts
- **Field Mapping**: Map CSV columns to contact fields during import
- **Duplicate Detection**: Prevent duplicate contacts during import

### User Interface
- **wProject Design Language**: Matches theme CSS, colors, and typography
- **Responsive Design**: Mobile, tablet, and desktop optimized
- **Dark Mode Support**: Automatically inherits theme dark mode
- **Slide-in Contact Details**: Quick view panel from right side (like project info)
- **Feather Icons**: Consistent icon library with wProject theme
- **Customizable Columns**: Configure which columns display in contact list

### Security & Permissions
- **Role-Based Access Control**: Respects wProject user roles (Admin, PM, Team Member, Observer)
- **Sensitive Data Protection**: ID/Passport fields visible only to Admins and Project Managers
- **Nonce Verification**: CSRF protection on all forms and AJAX calls
- **Sanitized Input**: XSS prevention on all user-submitted data
- **SQL Injection Protection**: Prepared statements throughout

## System Requirements

### Required
- **WordPress:** 5.0 or higher
- **PHP:** 8.0 or higher
- **MySQL:** 5.6 or higher
- **wProject Theme:** 5.7.2 or higher
- **Browser:** Modern browser with JavaScript enabled

### Recommended
- **PHP Memory Limit:** 256MB or higher
- **Max Upload Size:** 64MB or higher (for CSV imports)
- **wProject Calendar Pro:** For calendar integration features

## Installation

### Step 1: Upload Plugin Files

```bash
# Via FTP/SFTP
wp-content/plugins/wproject-contacts-pro/

# Or via WordPress Admin
Plugins → Add New → Upload Plugin → Choose wproject-contacts-pro.zip
```

### Step 2: Activate Plugin

1. Navigate to **Plugins** in WordPress admin
2. Find **wProject Contacts Pro**
3. Click **Activate**

### Step 3: Configure Settings

1. Go to **wProject → Pro Addons → Contacts Pro**
2. Configure column visibility preferences
3. Set notification preferences
4. Configure import/export settings

### Step 4: Create Your First Company

1. Click **Contacts** in the main navigation menu
2. Click **Add New Company** button
3. Fill in company details:
   - Company Name (required)
   - Website
   - Main Phone
   - Main Email
   - Company Type (Client/Vendor/Partner)
4. Click **Create Company**

### Step 5: Add Contacts to Company

1. Click on the company name to open details panel
2. Click **Add Contact** button
3. Fill in contact information:
   - First Name (required)
   - Last Name (required)
   - Role/Title
   - Department
   - Emails (add multiple with labels)
   - Phones (add multiple with labels)
   - Social profiles
   - Photo (upload or use Gravatar)
   - ID/Passport (if applicable)
4. Add tags for categorization
5. Click **Save Contact**

## Plugin Architecture

### File Structure

```
wproject-contacts-pro/
├── wproject-contacts-pro.php       # Main plugin file
├── README.md                       # This file
├── DEVELOPMENT-PLAN.md             # Development roadmap
├── uninstall.php                   # Cleanup on uninstall
├── admin/
│   ├── settings.php               # Plugin settings page
│   ├── company-admin.php          # Company CRUD interface
│   └── contact-admin.php          # Contact CRUD interface
├── includes/
│   ├── class-company.php          # Company entity class
│   ├── class-contact.php          # Contact entity class
│   ├── class-database.php         # Database operations
│   ├── class-ajax-handlers.php    # AJAX endpoint handlers
│   ├── class-import-export.php    # CSV/vCard import/export
│   ├── class-integrations.php     # Project/Task/Calendar integration
│   └── class-notifications.php    # Email notification system
├── templates/
│   ├── contact-list.php           # Main contact list view
│   ├── contact-detail.php         # Slide-in contact detail panel
│   ├── company-card.php           # Company list item
│   ├── contact-card.php           # Contact list item
│   ├── add-company-form.php       # Add company modal
│   ├── edit-company-form.php      # Edit company modal
│   ├── add-contact-form.php       # Add contact modal
│   └── edit-contact-form.php      # Edit contact modal
└── assets/
    ├── css/
    │   ├── contacts-pro.css       # Main stylesheet
    │   └── contacts-dark.css      # Dark mode overrides
    └── js/
        ├── contacts-list.js       # List view functionality
        ├── contact-detail.js      # Detail panel interactions
        ├── contact-form.js        # Form validation & AJAX
        ├── quick-actions.js       # Quick action buttons
        └── import-export.js       # Import/export UI
```

### Database Schema

#### Companies Table: `wp_wproject_companies`

```sql
CREATE TABLE wp_wproject_companies (
    id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(255) NOT NULL,
    company_website VARCHAR(255),
    company_phone VARCHAR(50),
    company_email VARCHAR(100),
    company_type VARCHAR(50) DEFAULT 'client',
    company_logo_url TEXT,
    company_notes TEXT,
    created_by BIGINT(20) UNSIGNED,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX company_name_idx (company_name),
    INDEX company_type_idx (company_type),
    INDEX created_by_idx (created_by)
);
```

#### Contacts Table: `wp_wproject_contacts`

```sql
CREATE TABLE wp_wproject_contacts (
    id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT(20) UNSIGNED NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    role VARCHAR(100),
    department VARCHAR(100),
    photo_url TEXT,
    gravatar_email VARCHAR(100),
    contact_id_number VARCHAR(100),
    passport_number VARCHAR(100),
    notes TEXT,
    created_by BIGINT(20) UNSIGNED,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES wp_wproject_companies(id) ON DELETE CASCADE,
    INDEX company_id_idx (company_id),
    INDEX name_idx (first_name, last_name),
    INDEX role_idx (role),
    INDEX created_by_idx (created_by)
);
```

#### Contact Emails Table: `wp_wproject_contact_emails`

```sql
CREATE TABLE wp_wproject_contact_emails (
    id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contact_id BIGINT(20) UNSIGNED NOT NULL,
    email VARCHAR(100) NOT NULL,
    email_label VARCHAR(50) DEFAULT 'work',
    is_preferred TINYINT(1) DEFAULT 0,
    FOREIGN KEY (contact_id) REFERENCES wp_wproject_contacts(id) ON DELETE CASCADE,
    INDEX contact_id_idx (contact_id),
    INDEX email_idx (email)
);
```

#### Contact Phones Table: `wp_wproject_contact_phones`

```sql
CREATE TABLE wp_wproject_contact_phones (
    id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contact_id BIGINT(20) UNSIGNED NOT NULL,
    phone_number VARCHAR(50) NOT NULL,
    phone_type VARCHAR(20) DEFAULT 'cell',
    phone_label VARCHAR(50) DEFAULT 'mobile',
    is_preferred TINYINT(1) DEFAULT 0,
    FOREIGN KEY (contact_id) REFERENCES wp_wproject_contacts(id) ON DELETE CASCADE,
    INDEX contact_id_idx (contact_id)
);
```

#### Contact Social Profiles Table: `wp_wproject_contact_socials`

```sql
CREATE TABLE wp_wproject_contact_socials (
    id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contact_id BIGINT(20) UNSIGNED NOT NULL,
    platform VARCHAR(50) NOT NULL,
    profile_url TEXT NOT NULL,
    FOREIGN KEY (contact_id) REFERENCES wp_wproject_contacts(id) ON DELETE CASCADE,
    INDEX contact_id_idx (contact_id),
    INDEX platform_idx (platform)
);
```

#### Contact-Project Relations: `wp_wproject_contact_projects`

```sql
CREATE TABLE wp_wproject_contact_projects (
    id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contact_id BIGINT(20) UNSIGNED NOT NULL,
    project_term_id BIGINT(20) UNSIGNED NOT NULL,
    linked_by BIGINT(20) UNSIGNED,
    linked_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (contact_id) REFERENCES wp_wproject_contacts(id) ON DELETE CASCADE,
    UNIQUE KEY unique_contact_project (contact_id, project_term_id),
    INDEX contact_id_idx (contact_id),
    INDEX project_term_id_idx (project_term_id)
);
```

#### Contact-Task Relations: `wp_wproject_contact_tasks`

```sql
CREATE TABLE wp_wproject_contact_tasks (
    id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contact_id BIGINT(20) UNSIGNED NOT NULL,
    task_post_id BIGINT(20) UNSIGNED NOT NULL,
    linked_by BIGINT(20) UNSIGNED,
    linked_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (contact_id) REFERENCES wp_wproject_contacts(id) ON DELETE CASCADE,
    UNIQUE KEY unique_contact_task (contact_id, task_post_id),
    INDEX contact_id_idx (contact_id),
    INDEX task_post_id_idx (task_post_id)
);
```

#### Contact-Event Relations: `wp_wproject_contact_events`

```sql
CREATE TABLE wp_wproject_contact_events (
    id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contact_id BIGINT(20) UNSIGNED NOT NULL,
    event_id BIGINT(20) UNSIGNED NOT NULL,
    rsvp_status VARCHAR(20) DEFAULT 'pending',
    linked_by BIGINT(20) UNSIGNED,
    linked_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (contact_id) REFERENCES wp_wproject_contacts(id) ON DELETE CASCADE,
    UNIQUE KEY unique_contact_event (contact_id, event_id),
    INDEX contact_id_idx (contact_id),
    INDEX event_id_idx (event_id)
);
```

### Custom Taxonomy: `contact_tag`

```php
register_taxonomy('contact_tag', null, [
    'hierarchical' => false,
    'labels' => [
        'name' => 'Contact Tags',
        'singular_name' => 'Contact Tag',
    ],
    'public' => false,
    'show_ui' => true,
    'show_in_rest' => true,
]);
```

## AJAX Endpoints

### Company Operations

- `contacts_pro_create_company` - Create new company
- `contacts_pro_update_company` - Update company details
- `contacts_pro_delete_company` - Delete company (with cascading contact deletion)
- `contacts_pro_get_company` - Fetch company details
- `contacts_pro_list_companies` - List all companies with filtering

### Contact Operations

- `contacts_pro_create_contact` - Create new contact
- `contacts_pro_update_contact` - Update contact details
- `contacts_pro_delete_contact` - Delete contact
- `contacts_pro_get_contact` - Fetch contact details with all relations
- `contacts_pro_list_contacts` - List contacts with filtering and pagination
- `contacts_pro_search_contacts` - Search contacts by name/email/phone

### Relationship Operations

- `contacts_pro_link_to_project` - Link contact to project
- `contacts_pro_unlink_from_project` - Remove contact from project
- `contacts_pro_link_to_task` - Link contact to task
- `contacts_pro_unlink_from_task` - Remove contact from task
- `contacts_pro_link_to_event` - Link contact to calendar event
- `contacts_pro_unlink_from_event` - Remove contact from event

### Import/Export Operations

- `contacts_pro_import_csv` - Import contacts from CSV
- `contacts_pro_export_csv` - Export contacts to CSV
- `contacts_pro_export_vcard` - Export contact as vCard
- `contacts_pro_validate_import` - Preview import before execution

## UI Components

### Contact List Page

**Location:** `/contacts` (accessible via main navigation menu)

**Features:**
- Tabbed view: All Contacts | By Company | By Tag
- Search bar with real-time filtering
- Column headers with sorting (click to sort)
- Configurable column visibility
- Bulk actions (export, tag, delete)
- Add New Company/Contact buttons
- Pagination (25, 50, 100 per page)

**Default Columns:**
1. Contact Photo (avatar)
2. Contact Name (First Last)
3. Company Name (clickable)
4. Role/Title
5. Primary Email (clickable mailto:)
6. Primary Phone (clickable tel:)
7. Tags (colored chips)
8. Linked Projects (count)
9. Quick Actions (icon buttons)

### Contact Detail Slide-in Panel

**Trigger:** Click contact name anywhere in wProject

**Sections:**
1. **Header**
   - Contact photo
   - Full name
   - Company name (clickable)
   - Role & Department
   - Close button (X)

2. **Contact Information**
   - All emails with labels and preferred flag
   - All phones with labels and preferred flag
   - Social profile icons (clickable)
   - ID/Passport (if user has permission)

3. **Quick Actions Bar**
   - Email, Call, Create Task, Create Project, Edit, Delete

4. **Activity Timeline**
   - Tabbed view: All | Projects | Tasks | Events
   - Chronological list with icons
   - Links to related items
   - Empty state if no activities

5. **Tags & Notes**
   - Tag chips (removable)
   - Add tag dropdown
   - Notes text area (editable inline)

### Add/Edit Company Modal

**Form Fields:**
- Company Name* (required, text)
- Website (URL with validation)
- Main Phone (tel with formatting)
- Main Email (email with validation)
- Company Type (dropdown: Client, Vendor, Partner, Other)
- Company Logo (image upload, 200x200px recommended)
- Notes (textarea)

**Validation:**
- Company name uniqueness check
- URL format validation
- Email format validation
- Phone format validation (international support)

**Actions:**
- Save Company (primary button)
- Cancel (secondary button)

### Add/Edit Contact Modal

**Form Fields:**
- First Name* (required, text)
- Last Name* (required, text)
- Company* (required, dropdown with search)
- Role (dropdown with custom option)
- Department (text)
- Photo (image upload or Gravatar email)

**Repeatable Fields:**
- Emails (label dropdown + email input + preferred checkbox + remove button)
- Cell Phones (label dropdown + phone input + preferred checkbox + remove button)
- Local Phones (label dropdown + phone input + preferred checkbox + remove button)
- [+ Add Email / + Add Cell Phone / + Add Local Phone buttons]

**Social Profiles:**
- LinkedIn URL
- Twitter/X URL
- Facebook URL

**Additional Fields:**
- ID Number (text, admin/PM only)
- Passport Number (text, admin/PM only)
- Tags (multi-select with autocomplete)
- Notes (textarea)

**Validation:**
- Required fields check
- Email format validation for all emails
- Phone format validation for all phones
- URL format validation for social profiles
- At least one email required
- At least one phone recommended (warning, not error)

**Actions:**
- Save Contact (primary button)
- Save & Add Another (secondary button)
- Cancel (tertiary button)

## Integration with wProject Features

### Projects Integration

**Project Detail Page:**
- New "Contacts" section/panel
- Shows all linked contacts with photos and roles
- "Add Contact" button opens contact selector modal
- Click contact name opens detail slide-in panel
- Remove icon (X) to unlink contact from project

**Project Creation:**
- Optional "Link Contacts" field in new project form
- Multi-select dropdown to choose contacts
- Contacts can be added/edited after project creation

**Project List:**
- New column option: "Contacts" (shows contact count)
- Tooltip on hover shows contact names

### Tasks Integration

**Task Form (Create/Edit):**
- New "External Contact" field
- Single-select dropdown (one contact per task in V1)
- Shows contact photo + name + company
- Optional: Send notification to contact checkbox

**Task Detail View:**
- Contact info displayed below task description
- Contact photo + name (clickable to detail panel)
- Company name
- Quick actions: Email, Call

**Task List:**
- New column option: "Contact" (shows contact name)
- Icon indicates if task has linked contact

### Calendar Integration

**Event Form (Create/Edit):**
- Existing "Attendees" field (team members)
- New "External Contacts" field below attendees
- Multi-select dropdown for contacts
- Shows contact photo + name + company
- "Send invitation email" checkbox (per contact)

**Event Detail View:**
- Separate sections:
  - Team Attendees (existing)
  - External Contacts (new)
- Contact RSVP status (if they responded)
- Contact photos in row
- Click contact opens detail panel

**Calendar View:**
- Event tooltip shows team attendees + external contacts
- Contact icon indicates external attendees present

## Settings & Configuration

**Location:** wProject → Pro Addons → Contacts Pro

### General Settings

- **Default Company Type:** Client | Vendor | Partner | Other
- **Require Photo Upload:** Yes | No (default: No)
- **Gravatar Fallback:** Yes | No (default: Yes)
- **ID/Passport Visibility:** Admins Only | Admins & PMs | All Users

### Column Visibility

- **Contact List Columns:** (checkboxes)
  - ✓ Contact Photo
  - ✓ Contact Name
  - ✓ Company Name
  - ✓ Role/Title
  - ✓ Primary Email
  - ✓ Primary Phone
  - ✓ Tags
  - ✓ Linked Projects
  - ✓ Quick Actions
  - ☐ Department
  - ☐ All Emails
  - ☐ All Phones
  - ☐ Social Profiles
  - ☐ Date Added
  - ☐ Last Modified

### Role Configuration

- **Predefined Roles:** (editable list)
  - CEO, CTO, CFO, COO
  - President, Vice President
  - Director, Manager, Supervisor
  - Project Manager, Team Lead
  - Developer, Designer, Analyst
  - Consultant, Contractor
  - Client Contact, Vendor Contact
  - (+ Add Custom Role)

### Notification Settings

- **Notify when contact linked to project:** Yes | No
- **Notify when contact linked to task:** Yes | No
- **Notify when contact added to event:** Yes | No
- **Notification Sender Name:** (text field)
- **Notification Sender Email:** (email field)

### Import/Export Settings

- **CSV Field Delimiter:** , | ; | | (tab)
- **CSV Text Qualifier:** " | '
- **Prevent Duplicate Imports:** Yes | No
- **Duplicate Detection Field:** Email | Phone | ID Number | Passport
- **Import Batch Size:** 50 | 100 | 250 | 500

## User Permissions

### Administrator
- Create, edit, delete all companies and contacts
- View all sensitive fields (ID, Passport)
- Configure plugin settings
- Import/export contacts
- Link contacts to any project/task/event
- Receive all notification types

### Project Manager
- Create, edit, delete companies and contacts
- View sensitive fields (ID, Passport) if enabled in settings
- Link contacts to own projects/tasks
- Import/export contacts
- Receive notifications for own projects

### Team Member
- View all contacts (read-only)
- View non-sensitive fields only
- Link contacts to own tasks (if user_can_assign_tasks enabled)
- Cannot create/edit/delete companies or contacts
- Receive notifications for tasks they own

### Observer
- View all contacts (read-only)
- View non-sensitive fields only
- Cannot link contacts to anything
- Cannot create/edit/delete
- No notifications

### Client (requires Clients Pro plugin)
- View only contacts linked to their projects
- View non-sensitive fields only
- Cannot create/edit/delete
- Cannot link contacts
- No notifications

## Email Notifications

### Contact Linked to Project

**Triggered:** When contact is added to a project

**Sent to:** Project Manager

**Subject:** [wProject] Contact added to [Project Name]

**Body Template:**
```
Hi [PM Name],

[Contact Name] from [Company Name] has been linked to the project "[Project Name]".

Contact Details:
Email: [Primary Email]
Phone: [Primary Phone]
Role: [Role]

View Project: [Project URL]
View Contact: [Contact Detail URL]

---
[Site Name]
```

### Contact Linked to Task

**Triggered:** When contact is added to a task

**Sent to:** Task owner

**Subject:** [wProject] Contact added to task: [Task Name]

**Body Template:**
```
Hi [Task Owner Name],

[Contact Name] from [Company Name] has been linked to your task "[Task Name]".

Contact Details:
Email: [Primary Email]
Phone: [Primary Phone]

View Task: [Task URL]
View Contact: [Contact Detail URL]

---
[Site Name]
```

### Contact Added to Event

**Triggered:** When contact is invited to calendar event

**Sent to:** Contact (if email exists) + Event creator

**Subject:** [wProject] Event Invitation: [Event Title]

**Body Template (to Contact):**
```
Hi [Contact Name],

You've been invited to the following event:

Event: [Event Title]
Date: [Event Date]
Time: [Start Time] - [End Time] ([Timezone])
Location: [Location]

[Event Description]

RSVP:
[Accept Link] [Decline Link] [Tentative Link]

View Event: [Event URL]

---
[Site Name]
```

## Quick Start Guide

### Scenario 1: Client Management

1. **Create Client Company**
   - Navigate to Contacts → Add New Company
   - Enter: "Acme Corporation"
   - Type: Client
   - Website: https://acme.com
   - Main Email: info@acme.com
   - Save

2. **Add Client Contacts**
   - Click "Acme Corporation" → Add Contact
   - First: John | Last: Doe
   - Role: CEO
   - Email: john@acme.com (Work, Preferred)
   - Cell: +1-555-0101 (Mobile, Preferred)
   - LinkedIn: https://linkedin.com/in/johndoe
   - Save

3. **Link to Project**
   - Open existing project (or create new)
   - Scroll to Contacts section
   - Click "Add Contact"
   - Select: John Doe (Acme Corporation)
   - Save

4. **Create Task for Client**
   - Create new task in project
   - Select client's project
   - External Contact: John Doe
   - Check "Send notification to contact"
   - Save

### Scenario 2: Vendor Management

1. **Create Vendor Company**
   - Contacts → Add New Company
   - Enter: "Design Studio LLC"
   - Type: Vendor
   - Main Phone: +1-555-0200
   - Save

2. **Add Vendor Contacts**
   - Add primary contact (Account Manager)
   - Add secondary contact (Creative Director)
   - Tag both with: "Design", "External"

3. **Schedule Meeting**
   - Calendar → New Event
   - Event Type: Meeting
   - Attendees: (select team members)
   - External Contacts: (select vendor contacts)
   - Send invitations: ✓
   - Save

### Scenario 3: Bulk Import Contacts

1. **Prepare CSV File**
   - Required columns: company_name, first_name, last_name
   - Optional: email, phone, role, department, etc.
   - Example:
     ```
     company_name,first_name,last_name,email,phone,role
     "Acme Corp",John,Doe,john@acme.com,555-0101,CEO
     "Acme Corp",Jane,Smith,jane@acme.com,555-0102,CFO
     ```

2. **Import via UI**
   - Contacts → Import CSV
   - Upload file
   - Map columns to fields
   - Preview import
   - Execute import
   - Review results

3. **Verify Import**
   - Check contact list
   - Verify company grouping
   - Check for duplicates
   - Fix any issues

## Troubleshooting

### Common Issues

#### Contact Photos Not Displaying

**Symptoms:** Avatar shows initials instead of photo

**Solutions:**
1. Verify Gravatar email is correct
2. Check if Gravatar exists at gravatar.com
3. Try manual photo upload
4. Clear browser cache
5. Check file upload permissions on server

#### Duplicate Contacts After Import

**Symptoms:** Same person appears multiple times

**Solutions:**
1. Enable "Prevent Duplicate Imports" in settings
2. Choose duplicate detection field (email recommended)
3. Clean CSV before import (remove duplicates)
4. Manually merge duplicates via contact edit

#### Contacts Not Appearing in Project

**Symptoms:** Contact linked but not showing in project panel

**Solutions:**
1. Verify contact is actually linked (check activity timeline)
2. Clear browser cache
3. Check user permissions (Observers can't see contact details)
4. Verify contact is not deleted
5. Check for JavaScript errors in console

#### Email Notifications Not Sending

**Symptoms:** No emails received when linking contacts

**Solutions:**
1. Verify notification is enabled in settings
2. Check WordPress email settings (wp_mail)
3. Install SMTP plugin (WP Mail SMTP recommended)
4. Verify sender email is valid
5. Check spam folder
6. Test email with different address

#### Cannot Delete Company

**Symptoms:** Delete button disabled or error message

**Solutions:**
1. Check if company has contacts (must delete contacts first)
2. Verify user permissions (only Admin/PM can delete)
3. Check if company is linked to active projects
4. Force delete from database (last resort)

## FAQ

### Can I have a contact work for multiple companies?

No, in V1 each contact belongs to one company. If someone works at multiple companies, create separate contact records for each company. This will be improved in V2 with many-to-many relationships.

### How many contacts can I store?

There is no hard limit. The plugin has been tested with 10,000+ contacts. Performance depends on your server resources and database configuration.

### Can I link one contact to multiple projects?

Yes! Contacts can be linked to unlimited projects, tasks, and calendar events.

### How do I sync with Google Contacts?

Google Contacts sync is planned for V2. In V1, use CSV export from Google Contacts, then import to wProject.

### Can clients view contacts?

If Clients Pro plugin is installed, clients can view contacts linked to their projects only. They cannot view all contacts or sensitive fields.

### What happens if I deactivate the plugin?

Data remains in the database. Reactivating the plugin restores all functionality. To completely remove data, use the uninstall process (see below).

### Can I customize which fields are required?

Not in V1. Required fields are: company_name (for companies), first_name, last_name, company_id (for contacts). Custom field requirements will be added in V2.

### How do I backup my contacts?

Use CSV Export to create a backup file. Store it securely offsite. For complete backup, include database tables (see Database Schema section).

## Uninstall

### Option 1: Deactivate Only (Keeps Data)

1. Go to Plugins → Installed Plugins
2. Find wProject Contacts Pro
3. Click "Deactivate"
4. Data remains in database
5. Reactivating restores full functionality

### Option 2: Complete Removal (Deletes Data)

⚠️ **WARNING:** This permanently deletes all companies, contacts, and relationships. Export your data first!

1. **Export all contacts to CSV** (Contacts → Export CSV → All Contacts)
2. Deactivate the plugin
3. Click "Delete" on plugin row
4. Confirm deletion
5. Plugin runs `uninstall.php` which:
   - Drops all custom database tables
   - Deletes all contact/company post types (if used)
   - Removes contact_tag taxonomy and terms
   - Deletes all plugin options from wp_options
   - Removes uploaded contact photos from media library

### Manual Cleanup (if uninstall fails)

Run these SQL queries in phpMyAdmin:

```sql
-- Drop all Contacts Pro tables
DROP TABLE IF EXISTS wp_wproject_contact_events;
DROP TABLE IF EXISTS wp_wproject_contact_tasks;
DROP TABLE IF EXISTS wp_wproject_contact_projects;
DROP TABLE IF EXISTS wp_wproject_contact_socials;
DROP TABLE IF EXISTS wp_wproject_contact_phones;
DROP TABLE IF EXISTS wp_wproject_contact_emails;
DROP TABLE IF EXISTS wp_wproject_contacts;
DROP TABLE IF EXISTS wp_wproject_companies;

-- Remove plugin options
DELETE FROM wp_options WHERE option_name LIKE 'wproject_contacts_pro_%';

-- Remove taxonomy (terms and relationships)
DELETE FROM wp_term_taxonomy WHERE taxonomy = 'contact_tag';
DELETE FROM wp_terms WHERE term_id IN (
    SELECT term_id FROM wp_term_taxonomy WHERE taxonomy = 'contact_tag'
);
```

## Roadmap

### Version 1.1 (Q1 2026)
- Performance optimization for large contact lists (10,000+)
- Advanced search with filters (by company, tag, role, etc.)
- Contact merge tool (combine duplicate contacts)
- Bulk edit contacts
- Contact history log (track all changes)

### Version 1.5 (Q2 2026)
- vCard import functionality
- Contact custom fields (user-defined fields)
- Contact categories (separate from tags)
- Contact groups (mailing lists)
- Email integration (send emails directly from wProject)

### Version 2.0 (Q3 2026)
- Google Contacts sync (OAuth integration)
- Outlook/Exchange sync (Microsoft Graph API)
- Many-to-many company relationships (contact works at multiple companies)
- Contact portal (external login for contacts)
- Advanced reporting and analytics
- Contact scoring (engagement metrics)

### Version 2.5 (Q4 2026)
- Mobile app integration
- Contact relationship mapping (org charts)
- Deal/opportunity tracking per contact
- Contract management
- Document library per contact
- Time tracking per contact (client billing)

## Support

### Getting Help

1. **Documentation**: Check this README.md and DEVELOPMENT-PLAN.md
2. **GitHub Issues**: Report bugs or request features
3. **wProject Support**: Contact Rocket Apps for theme-related issues

### Reporting Bugs

When reporting bugs, please include:
- WordPress version
- PHP version
- wProject theme version
- Contacts Pro version
- Browser and OS
- Steps to reproduce
- Expected vs actual behavior
- Screenshots (if applicable)
- JavaScript console errors (if applicable)

### Feature Requests

Feature requests are welcome! Please:
- Check if feature is already planned (see Roadmap)
- Explain the use case (why you need it)
- Provide examples (how it should work)
- Consider contributing (pull requests welcome)

## Credits

**Developed for:** wProject Custom  
**Based on:** wProject Theme by Rocket Apps  
**Inspired by:** wProject Calendar Pro, Contacts Pro (original)  
**Icons:** Feather Icons  
**Date/Time:** Moment.js  
**Architecture:** WordPress Plugin API

## License

This plugin is proprietary software developed for wProject Custom. All rights reserved.

**Restrictions:**
- Cannot be redistributed
- Cannot be resold
- Cannot be used outside of wProject Custom installation
- Modifications allowed for personal use only

**Included Libraries:**
- Feather Icons (MIT License)
- Moment.js (MIT License)
- jQuery (MIT License)

---

**Last Updated:** November 29, 2025  
**Documentation Version:** 1.0  
**Plugin Version:** 1.0.0
