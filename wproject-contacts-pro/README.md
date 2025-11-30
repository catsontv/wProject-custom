# wProject Contacts Pro

**Version:** 1.0.12
**Status:** Phase 2 Complete - Core CRUD Operations Working
**Author:** Custom Development for wProject
**Requires:** wProject Theme 5.7.2+, WordPress 5.0+, PHP 8.0+
**License:** Commercial

## 🎉 Current Status

**Phase 2 COMPLETE** - All core contact and company management features are working!

✅ Create, edit, delete contacts
✅ Create, edit, delete companies
✅ Contacts can be created without company assignment
✅ Filter view: All / Contacts / Companies
✅ Modal forms for streamlined UX
✅ Full AJAX CRUD operations
✅ Comprehensive error handling

**Ready for Phase 3**: Integration with Projects, Tasks, and Calendar

---

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

### Data Persistence & Safety
- **Update-Safe**: All contact data persists through plugin updates
- **Deactivation-Safe**: Data remains intact when plugin is deactivated
- **Uninstall-Safe**: Data is preserved even if plugin is deleted
- **Manual Control**: Data is only removed through explicit database cleanup (see Uninstall section)
- **No Automatic Deletion**: Plugin never deletes your contact data automatically

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
4. Database tables are created automatically on first activation

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
├── uninstall.php                   # Optional cleanup (disabled by default)
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

## Data Persistence Policy

### Philosophy

**Your contact data is valuable and permanent.** Unlike typical WordPress plugins that delete data on uninstall, wProject Contacts Pro treats your contact database as a core business asset that should never be automatically deleted.

### What This Means

✅ **Data persists through:**
- Plugin updates (all versions)
- Plugin deactivation
- Plugin deletion/uninstall
- WordPress updates
- Theme changes
- Server migrations (if database is preserved)

❌ **Data is NEVER automatically deleted by:**
- Clicking "Deactivate"
- Clicking "Delete" in plugin list
- Running WordPress uninstaller
- Updating to new version

### Why This Approach?

1. **Accidental Protection**: Prevents catastrophic data loss from accidental plugin deletion
2. **Business Continuity**: Your contacts remain safe during system maintenance
3. **Migration Friendly**: Simplifies moving between servers/hosts
4. **Upgrade Safe**: Eliminates fear of losing data during updates
5. **Explicit Control**: You decide when and how to remove data

### How to Remove Data (If Needed)

If you genuinely need to delete all contact data, see the "Manual Data Removal" section under Uninstall below.

## Uninstall

### Option 1: Deactivate (Recommended)

**Effect:** Plugin functionality disabled, all data preserved

1. Go to **Plugins → Installed Plugins**
2. Find **wProject Contacts Pro**
3. Click **Deactivate**
4. ✅ All companies, contacts, and relationships remain in database
5. ✅ Reactivating restores full functionality immediately
6. ✅ No data export needed

**When to use:** Temporarily disable plugin, troubleshoot conflicts, or prepare for upgrade

### Option 2: Delete Plugin (Data Still Preserved)

**Effect:** Plugin files removed, all data preserved

1. Deactivate the plugin (see Option 1)
2. Click **Delete** on plugin row
3. Confirm deletion
4. ✅ Plugin files removed from server
5. ✅ All database tables remain intact
6. ✅ All contact data preserved
7. ✅ Can reinstall plugin anytime to restore functionality

**When to use:** Clean up unused plugins while keeping data for future use

### Option 3: Manual Data Removal (Permanent Deletion)

⚠️ **DANGER ZONE - IRREVERSIBLE OPERATION**

**Effect:** ALL contact data permanently deleted

**⚠️ WARNING:** This action:
- Deletes ALL companies, contacts, emails, phones, social profiles
- Removes ALL project/task/event relationships
- Deletes ALL contact tags
- Removes ALL contact photos from media library
- CANNOT be undone
- Backup is your ONLY recovery option

**Prerequisites:**
1. **BACKUP FIRST** - Export all contacts to CSV (Contacts → Export CSV → All Contacts)
2. Verify backup file contains all data
3. Store backup in safe location (offsite recommended)
4. Deactivate and delete the plugin
5. Ensure you have database access (phpMyAdmin or similar)

**Manual Deletion Process:**

1. **Access Database** via phpMyAdmin or command line

2. **Select Your WordPress Database** (usually prefixed with `wp_`)

3. **Run SQL Cleanup Queries:**

```sql
-- ============================================
-- wProject Contacts Pro - MANUAL DATA REMOVAL
-- ============================================
-- WARNING: THIS PERMANENTLY DELETES ALL DATA
-- BACKUP FIRST - CANNOT BE UNDONE
-- ============================================

-- Step 1: Drop relationship tables (no foreign key constraints)
DROP TABLE IF EXISTS wp_wproject_contact_events;
DROP TABLE IF EXISTS wp_wproject_contact_tasks;
DROP TABLE IF EXISTS wp_wproject_contact_projects;

-- Step 2: Drop dependent tables
DROP TABLE IF EXISTS wp_wproject_contact_socials;
DROP TABLE IF EXISTS wp_wproject_contact_phones;
DROP TABLE IF EXISTS wp_wproject_contact_emails;

-- Step 3: Drop main contacts table
DROP TABLE IF EXISTS wp_wproject_contacts;

-- Step 4: Drop companies table
DROP TABLE IF EXISTS wp_wproject_companies;

-- Step 5: Remove plugin options
DELETE FROM wp_options WHERE option_name LIKE 'wproject_contacts_pro_%';

-- Step 6: Remove contact tags taxonomy
DELETE tt FROM wp_term_taxonomy tt
WHERE tt.taxonomy = 'contact_tag';

DELETE t FROM wp_terms t
WHERE NOT EXISTS (
    SELECT 1 FROM wp_term_taxonomy tt 
    WHERE tt.term_id = t.term_id
);

-- Step 7: Remove contact photos from uploads
-- (Manual step - see below)
```

4. **Remove Contact Photos** (if any were uploaded):
   - Navigate to `wp-content/uploads/contacts-pro/`
   - Delete the entire `contacts-pro` folder
   - Or via FTP/File Manager

5. **Verify Deletion:**

```sql
-- Check if tables still exist (should return 0 rows)
SHOW TABLES LIKE 'wp_wproject_contact%';
SHOW TABLES LIKE 'wp_wproject_companies';

-- Check if options remain (should return 0 rows)
SELECT * FROM wp_options WHERE option_name LIKE 'wproject_contacts_pro_%';

-- Check if taxonomy remains (should return 0 rows)
SELECT * FROM wp_term_taxonomy WHERE taxonomy = 'contact_tag';
```

6. **Confirm Backup:**
   - Open CSV export file
   - Verify all contacts are present
   - Store backup securely

**Recovery:**
- If you deleted data by mistake, restore from backup CSV immediately
- Reinstall plugin
- Use CSV Import to restore contacts
- Relationships will need manual re-linking

### Summary Table

| Action | Plugin Active | Plugin Files | Database Tables | Contact Data |
|--------|---------------|--------------|-----------------|-------------|
| **Deactivate** | ❌ No | ✅ Yes | ✅ Yes | ✅ Preserved |
| **Delete Plugin** | ❌ No | ❌ No | ✅ Yes | ✅ Preserved |
| **Manual SQL Cleanup** | ❌ No | ❌ No | ❌ No | ❌ **DELETED** |

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

Data remains in the database. Reactivating the plugin restores all functionality immediately. Nothing is lost.

### What happens if I delete the plugin?

Data STILL remains in the database. This is intentional to prevent accidental data loss. See the Uninstall section for details.

### How do I completely remove all contact data?

See "Option 3: Manual Data Removal" in the Uninstall section. This requires manual SQL queries and is intentionally difficult to prevent accidental deletion.

### Can I customize which fields are required?

Not in V1. Required fields are: company_name (for companies), first_name, last_name, company_id (for contacts). Custom field requirements will be added in V2.

### How do I backup my contacts?

Use CSV Export to create a backup file. Store it securely offsite. For complete backup, include database tables (see Database Schema section) in your WordPress database backup.

### Will my contacts survive a WordPress migration?

Yes, as long as your database is migrated. Contact data is stored in custom database tables that move with your WordPress database.

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
