# wProject Contacts Pro - Development Plan

**Document Version:** 1.0  
**Last Updated:** November 29, 2025  
**Target Plugin Version:** 1.0.0  
**Estimated Total Development Time:** 120-150 hours

---

## Table of Contents

1. [Development Philosophy](#development-philosophy)
2. [Technical Stack](#technical-stack)
3. [Phase 1: Core Foundation](#phase-1-core-foundation)
4. [Phase 2: UI & User Experience](#phase-2-ui--user-experience)
5. [Phase 3: Integration & Relationships](#phase-3-integration--relationships)
6. [Phase 4: Import/Export & Polish](#phase-4-importexport--polish)
7. [Testing Strategy](#testing-strategy)
8. [Deployment Checklist](#deployment-checklist)
9. [Post-Launch Maintenance](#post-launch-maintenance)

---

## Development Philosophy

### Core Principles

1. **Data First**: Database integrity is paramount - never sacrifice data safety for features
2. **wProject Native**: Match theme's UX patterns exactly - users shouldn't notice it's a plugin
3. **Progressive Enhancement**: Build foundation first, add features incrementally
4. **Test Before Move**: Each phase has clear acceptance criteria before proceeding
5. **No Baby Steps**: Each phase delivers substantial, testable functionality

### Success Criteria

✅ **Must Have (V1)**
- Complete company and contact CRUD operations
- Multiple emails/phones with labels
- Link contacts to projects, tasks, events
- Activity timeline
- Quick actions
- CSV import/export + vCard export
- wProject UI consistency
- Data persistence through updates/uninstalls

⏸️ **Nice to Have (V2)**
- vCard import
- Google/Outlook sync
- Advanced reporting
- Contact custom fields

---

## Technical Stack

### Backend
- **Language**: PHP 8.0+
- **Framework**: WordPress Plugin API
- **Database**: MySQL 5.6+ with custom tables
- **ORM**: WordPress $wpdb with prepared statements
- **Security**: Nonces, capability checks, sanitization, escaping

### Frontend
- **HTML/CSS**: Match wProject theme exactly
- **JavaScript**: jQuery 1.9.1 (wProject bundled)
- **Icons**: Feather Icons (wProject bundled)
- **AJAX**: wp.ajax with nonce verification
- **Forms**: HTML5 validation + JS validation

### Libraries (Already in wProject)
- jQuery 1.9.1
- Feather Icons
- Moment.js
- js-cookie

### New Dependencies (None)
- Zero new libraries - use wProject's existing stack

---

## Phase 1: Core Foundation

**Goal:** Database, models, and basic CRUD functionality  
**Duration:** 35-40 hours  
**Priority:** CRITICAL - Nothing works without this

### 1.1 Plugin Initialization (5-6 hours)

**Files to Create:**
- `wproject-contacts-pro.php` - Main plugin file
- `includes/class-database.php` - Database installer and migrations
- `uninstall.php` - Empty file (data persistence requirement)

**Tasks:**
1. Plugin header with metadata
2. Activation hook (create database tables)
3. Deactivation hook (do nothing - preserve data)
4. Version checking for future migrations
5. Admin notice if wProject theme not active
6. Admin notice if PHP < 8.0

**Database Tables to Create:**
```sql
wp_wproject_companies
wp_wproject_contacts
wp_wproject_contact_emails
wp_wproject_contact_phones
wp_wproject_contact_socials
wp_wproject_contact_projects
wp_wproject_contact_tasks
wp_wproject_contact_events
```

**Testing:**
- [ ] Plugin activates without errors
- [ ] All 8 tables created with correct structure
- [ ] Foreign keys work (cascade deletes)
- [ ] Indexes created properly
- [ ] Deactivate/reactivate preserves tables
- [ ] Delete plugin preserves tables
- [ ] Admin notices display when requirements not met

---

### 1.2 Company Model & CRUD (8-10 hours)

**Files to Create:**
- `includes/class-company.php` - Company entity class
- `admin/company-admin.php` - Company management interface

**Class: WProject_Company**

**Properties:**
```php
public $id;
public $company_name;
public $company_website;
public $company_phone;
public $company_email;
public $company_type; // client|vendor|partner|other
public $company_logo_url;
public $company_notes;
public $created_by;
public $created_at;
public $updated_at;
```

**Methods:**
```php
public static function create($data); // Insert new company
public static function get($id); // Fetch by ID
public static function update($id, $data); // Update company
public static function delete($id); // Delete (cascade to contacts)
public static function list_all($args); // List with filters/pagination
public static function search($query); // Search by name
public function get_contacts(); // Get all contacts for company
public function get_contact_count(); // Count contacts
```

**Validation Rules:**
- `company_name`: Required, max 255 chars, unique
- `company_website`: Optional, valid URL format
- `company_email`: Optional, valid email format
- `company_phone`: Optional, max 50 chars
- `company_type`: One of [client, vendor, partner, other]

**Testing:**
- [ ] Create company with valid data succeeds
- [ ] Create company with duplicate name fails
- [ ] Create company with invalid email fails
- [ ] Get company by ID returns correct data
- [ ] Update company modifies fields
- [ ] Update company updates `updated_at` timestamp
- [ ] Delete company removes from database
- [ ] Delete company cascades to contacts (via FK)
- [ ] List companies with pagination works
- [ ] Search companies by name works
- [ ] Get contact count returns correct number

---

### 1.3 Contact Model & CRUD (10-12 hours)

**Files to Create:**
- `includes/class-contact.php` - Contact entity class
- `admin/contact-admin.php` - Contact management interface

**Class: WProject_Contact**

**Properties:**
```php
public $id;
public $company_id;
public $first_name;
public $last_name;
public $role;
public $department;
public $photo_url;
public $gravatar_email;
public $contact_id_number;
public $passport_number;
public $notes;
public $created_by;
public $created_at;
public $updated_at;

// Relationships (loaded separately)
public $emails = []; // Array of email objects
public $phones = []; // Array of phone objects
public $socials = []; // Array of social objects
public $tags = []; // Array of tag terms
```

**Methods:**
```php
public static function create($data); // Insert contact + related data
public static function get($id); // Fetch by ID with all relations
public static function update($id, $data); // Update contact + relations
public static function delete($id); // Delete (cascade relations)
public static function list_all($args); // List with filters/pagination
public static function search($query); // Search by name/email/phone
public function get_company(); // Get parent company object
public function get_full_name(); // "First Last"
public function get_primary_email(); // Email with is_preferred=1
public function get_primary_phone(); // Phone with is_preferred=1
public function get_avatar_url(); // Gravatar or uploaded photo
public function get_projects(); // Linked projects
public function get_tasks(); // Linked tasks
public function get_events(); // Linked events
public function get_activity_timeline(); // All activities chronological
```

**Email/Phone/Social Management:**
```php
public function add_email($email, $label, $is_preferred);
public function update_email($email_id, $data);
public function delete_email($email_id);
public function set_preferred_email($email_id);

public function add_phone($number, $type, $label, $is_preferred);
public function update_phone($phone_id, $data);
public function delete_phone($phone_id);
public function set_preferred_phone($phone_id);

public function add_social($platform, $url);
public function update_social($social_id, $url);
public function delete_social($social_id);
```

**Validation Rules:**
- `first_name`: Required, max 100 chars
- `last_name`: Required, max 100 chars
- `company_id`: Required, must exist in companies table
- `role`: Optional, max 100 chars
- `department`: Optional, max 100 chars
- `emails`: At least one required, all must be valid email format
- `phones`: At least one recommended (warning only)
- `socials`: Optional, must be valid URLs

**Testing:**
- [ ] Create contact with required fields succeeds
- [ ] Create contact without company_id fails
- [ ] Create contact with invalid email fails
- [ ] Create contact with multiple emails succeeds
- [ ] Only one email can be preferred
- [ ] Only one phone can be preferred
- [ ] Get contact by ID loads all relations
- [ ] Update contact modifies fields and relations
- [ ] Delete contact removes all related data
- [ ] Search by name returns correct results
- [ ] Search by email returns correct results
- [ ] Search by phone returns correct results
- [ ] get_primary_email returns preferred email
- [ ] get_avatar_url falls back to Gravatar
- [ ] get_full_name formats correctly

---

### 1.4 Contact Tags Taxonomy (3-4 hours)

**Files to Modify:**
- `wproject-contacts-pro.php` - Register taxonomy

**Taxonomy: `contact_tag`**

**Configuration:**
```php
register_taxonomy('contact_tag', null, [
    'hierarchical' => false, // Tags, not categories
    'labels' => [
        'name' => __('Contact Tags', 'wproject-contacts-pro'),
        'singular_name' => __('Contact Tag', 'wproject-contacts-pro'),
        'search_items' => __('Search Tags', 'wproject-contacts-pro'),
        'all_items' => __('All Tags', 'wproject-contacts-pro'),
        'edit_item' => __('Edit Tag', 'wproject-contacts-pro'),
        'update_item' => __('Update Tag', 'wproject-contacts-pro'),
        'add_new_item' => __('Add New Tag', 'wproject-contacts-pro'),
        'new_item_name' => __('New Tag Name', 'wproject-contacts-pro'),
        'menu_name' => __('Tags', 'wproject-contacts-pro'),
    ],
    'public' => false, // Not publicly queryable
    'show_ui' => true, // Show in admin
    'show_admin_column' => false,
    'show_in_rest' => true, // For Gutenberg compatibility
    'rewrite' => false,
]);
```

**Tag Management Methods (add to WProject_Contact):**
```php
public function add_tag($tag_name); // Add tag to contact
public function remove_tag($tag_id); // Remove tag from contact
public function get_tags(); // Get all tags for contact
public static function get_contacts_by_tag($tag_id); // Get all contacts with tag
```

**Testing:**
- [ ] Taxonomy registers without errors
- [ ] Can create tags via admin
- [ ] Can assign tags to contacts
- [ ] Can remove tags from contacts
- [ ] Can search/filter contacts by tag
- [ ] Tags display in contact list
- [ ] Deleting contact removes tag relationships
- [ ] Unused tags remain in database

---

### 1.5 AJAX Handlers Foundation (8-10 hours)

**Files to Create:**
- `includes/class-ajax-handlers.php` - All AJAX endpoint handlers

**Endpoints to Implement:**

**Company Endpoints:**
```php
contacts_pro_create_company // Create new company
contacts_pro_update_company // Update company
contacts_pro_delete_company // Delete company
contacts_pro_get_company // Fetch single company
contacts_pro_list_companies // List all with filters
```

**Contact Endpoints:**
```php
contacts_pro_create_contact // Create contact + emails/phones/socials
contacts_pro_update_contact // Update contact + relations
contacts_pro_delete_contact // Delete contact
contacts_pro_get_contact // Fetch single contact with relations
contacts_pro_list_contacts // List all with filters/pagination
contacts_pro_search_contacts // Search by name/email/phone
```

**Security for All Endpoints:**
1. Verify nonce
2. Check user capabilities
3. Sanitize all input
4. Validate data
5. Escape all output
6. Return JSON responses

**Standard Response Format:**
```json
{
  "success": true|false,
  "data": {...} or null,
  "message": "Human readable message",
  "errors": [] or null
}
```

**Testing:**
- [ ] All endpoints require valid nonce
- [ ] All endpoints check user capabilities
- [ ] Invalid data returns error response
- [ ] Valid data returns success response
- [ ] Create endpoints return new ID
- [ ] Update endpoints return updated data
- [ ] Delete endpoints confirm deletion
- [ ] List endpoints support pagination
- [ ] Search endpoints return relevant results
- [ ] All responses follow standard format

---

### Phase 1 Acceptance Criteria

**Before proceeding to Phase 2, verify:**

✅ **Database:**
- [ ] All 8 tables exist with correct schema
- [ ] Foreign keys enforce data integrity
- [ ] Indexes improve query performance
- [ ] Data persists through plugin deactivation
- [ ] Data persists through plugin deletion

✅ **Models:**
- [ ] Company CRUD operations work
- [ ] Contact CRUD operations work
- [ ] Email/phone/social management works
- [ ] Tag assignment works
- [ ] All validations enforce rules
- [ ] All methods have error handling

✅ **AJAX:**
- [ ] All endpoints respond correctly
- [ ] Security checks pass
- [ ] Error handling works
- [ ] JSON responses are valid

✅ **Testing:**
- [ ] 100% of Phase 1 tests pass
- [ ] No PHP errors in debug.log
- [ ] No JavaScript console errors
- [ ] Memory usage acceptable (<64MB)

---

## Phase 2: UI & User Experience

**Goal:** Build user-facing interfaces matching wProject design  
**Duration:** 40-45 hours  
**Priority:** HIGH - Users need to see and use the data

### 2.1 Main Navigation Integration (3-4 hours)

**Files to Modify:**
- `wproject-contacts-pro.php` - Add menu filter
- `assets/css/contacts-pro.css` - Menu styling

**Task:**
Add "Contacts" menu item to wProject's main left navigation

**Hook:**
```php
add_filter('wproject_nav_menu_items', 'contacts_pro_add_menu_item', 10, 1);
```

**Menu Item:**
```php
[
    'id' => 'contacts',
    'label' => __('Contacts', 'wproject-contacts-pro'),
    'icon' => 'users', // Feather icon
    'url' => home_url('/contacts'),
    'capability' => 'read', // All logged-in users
    'position' => 35, // Between Tasks and Calendar
]
```

**Testing:**
- [ ] "Contacts" appears in main navigation
- [ ] Icon displays correctly (Feather users icon)
- [ ] Clicking navigates to /contacts page
- [ ] Menu item highlights when active
- [ ] Works in dark mode
- [ ] Responsive on mobile

---

### 2.2 Contact List Page (12-15 hours)

**Files to Create:**
- `templates/contact-list.php` - Main list view
- `templates/company-card.php` - Company list item
- `templates/contact-card.php` - Contact list item
- `assets/css/contacts-pro.css` - List styling
- `assets/js/contacts-list.js` - List interactions

**Layout Structure:**
```
+---------------------------------------+
|  [Search]           [+ Company] [+]  |  <- Header
+---------------------------------------+
| [All] [By Company] [By Tag]          |  <- Tabs
+---------------------------------------+
| ☰ Photo | Name | Company | Role ... |  <- Column Headers (sortable)
+---------------------------------------+
| [Avatar] John Doe | Acme | CEO ...   |  <- Contact Row
| [Avatar] Jane Smith | Acme | CFO ... |  
+---------------------------------------+
| Showing 1-25 of 150    [< 1 2 3 >]  |  <- Pagination
+---------------------------------------+
```

**Features:**
1. **Tabbed View:**
   - All Contacts (flat list)
   - By Company (grouped by company)
   - By Tag (grouped by tag)

2. **Search:**
   - Real-time filtering
   - Search by name, email, phone, company
   - Debounced input (300ms)

3. **Sortable Columns:**
   - Click header to sort ASC/DESC
   - Sort by: Name, Company, Role, Date Added

4. **Column Visibility:**
   - User can toggle columns on/off
   - Settings saved per user
   - Default: 9 core columns visible

5. **Bulk Actions:**
   - Select multiple contacts (checkbox)
   - Export selected
   - Tag selected
   - Delete selected (with confirmation)

6. **Pagination:**
   - 25, 50, 100 per page options
   - Previous/Next buttons
   - Page numbers (1 2 3...)

**CSS Requirements:**
- Match wProject task list styling exactly
- Use wProject color variables
- Support dark mode
- Responsive breakpoints
- Feather icons for actions

**JavaScript:**
- AJAX load contacts (no page refresh)
- Inline editing (double-click name)
- Quick actions on hover
- Keyboard navigation (arrow keys)

**Testing:**
- [ ] List displays contacts correctly
- [ ] Search filters in real-time
- [ ] Sorting works for all columns
- [ ] Pagination works correctly
- [ ] Bulk actions work
- [ ] Column visibility toggles work
- [ ] Responsive on mobile/tablet
- [ ] Dark mode styling correct
- [ ] Loading states display
- [ ] Empty state displays when no contacts

---

### 2.3 Contact Detail Slide-in Panel (10-12 hours)

**Files to Create:**
- `templates/contact-detail.php` - Slide-in panel
- `assets/css/contact-detail.css` - Panel styling
- `assets/js/contact-detail.js` - Panel interactions

**Layout:**
```
+---------------------------+
|  [Avatar]     [X Close]   |  <- Header
|  John Doe                 |
|  Acme Corp - CEO          |
+---------------------------+
| Emails:                   |  <- Contact Info
|  ✉ john@acme.com (Work) ★ |
|  ✉ jdoe@gmail.com (Pers) |
|                           |
| Phones:                   |
|  ☎ +1-555-0101 (Mobile) ★|
|  ☎ +1-555-0102 (Office) |
|                           |
| Social:                   |
|  [in] [tw] [fb]           |
+---------------------------+
| [✉] [☎] [Task] [Proj] ... |  <- Quick Actions
+---------------------------+
| Activity Timeline         |  <- Timeline
| [All][Projects][Tasks]    |
|                           |
|  • Linked to Project X    |
|    Nov 28, 2025           |
|  • Assigned to Task Y     |
|    Nov 27, 2025           |
+---------------------------+
| Tags: [Client] [VIP]      |  <- Tags
| Notes: [Editable...]      |  <- Notes
+---------------------------+
```

**Features:**
1. **Slide-in Animation:**
   - Slides from right (like project panel)
   - Overlay dims background
   - ESC key closes panel
   - Click outside closes panel

2. **Contact Info Display:**
   - All emails with labels
   - All phones with labels
   - Preferred marked with ★
   - Clickable mailto: and tel: links
   - Social icons link to profiles

3. **Quick Actions:**
   - Email: Opens default email client
   - Call: Triggers tel: link
   - Create Task: Pre-fills contact
   - Create Project: Pre-links contact
   - Edit: Opens edit modal
   - Delete: Confirms then deletes

4. **Activity Timeline:**
   - Tabbed: All | Projects | Tasks | Events
   - Chronological order (newest first)
   - Icons for each activity type
   - Clickable links to related items
   - Empty state if no activities

5. **Tags & Notes:**
   - Tag chips (removable)
   - Add tag dropdown
   - Inline editable notes
   - Auto-save notes on blur

**Permissions:**
- ID/Passport only visible to Admin & PM
- Edit/Delete only available to Admin & PM
- All users can view basic info

**Testing:**
- [ ] Panel slides in from right
- [ ] Panel closes on ESC key
- [ ] Panel closes on overlay click
- [ ] All contact info displays correctly
- [ ] Preferred email/phone marked
- [ ] Quick actions trigger correctly
- [ ] Activity timeline loads
- [ ] Timeline tabs filter correctly
- [ ] Tags can be added/removed
- [ ] Notes save automatically
- [ ] Permissions enforced
- [ ] Dark mode styling correct

---

### 2.4 Add/Edit Company Modal (6-7 hours)

**Files to Create:**
- `templates/add-company-form.php` - Add modal
- `templates/edit-company-form.php` - Edit modal
- `assets/js/company-form.js` - Form handling

**Form Fields:**
1. Company Name* (text, required)
2. Website (URL)
3. Main Phone (tel)
4. Main Email (email)
5. Company Type (dropdown: Client, Vendor, Partner, Other)
6. Logo (image upload)
7. Notes (textarea)

**Validation:**
- Client-side: HTML5 + JavaScript
- Server-side: PHP sanitization + validation
- Real-time feedback (on blur)
- Submit button disabled until valid

**Features:**
- Modal overlay (dims background)
- AJAX form submission
- Loading state during save
- Success message on save
- Error messages inline
- ESC key closes modal

**Testing:**
- [ ] Modal opens on button click
- [ ] Required fields validated
- [ ] Email format validated
- [ ] URL format validated
- [ ] Logo upload works
- [ ] Form submits via AJAX
- [ ] Success message displays
- [ ] Error messages display
- [ ] Modal closes after save
- [ ] Company list refreshes

---

### 2.5 Add/Edit Contact Modal (10-12 hours)

**Files to Create:**
- `templates/add-contact-form.php` - Add modal
- `templates/edit-contact-form.php` - Edit modal
- `assets/js/contact-form.js` - Form handling

**Form Fields:**
1. First Name* (text, required)
2. Last Name* (text, required)
3. Company* (searchable dropdown, required)
4. Role (dropdown + custom input)
5. Department (text)
6. Photo (upload or Gravatar email)

**Repeatable Fields:**
7. Emails (multiple with [+ Add Email])
   - Email input
   - Label dropdown (Work, Personal, Assistant, Other)
   - Preferred checkbox
   - Remove button

8. Cell Phones (multiple with [+ Add Cell Phone])
   - Phone input
   - Label dropdown (Mobile, Assistant Mobile, Other)
   - Preferred checkbox
   - Remove button

9. Local Phones (multiple with [+ Add Local Phone])
   - Phone input
   - Label dropdown (Office, Home, Fax, Other)
   - Preferred checkbox
   - Remove button

**Social Profiles:**
10. LinkedIn URL
11. Twitter/X URL
12. Facebook URL

**Additional:**
13. ID Number (text, Admin/PM only)
14. Passport Number (text, Admin/PM only)
15. Tags (multi-select autocomplete)
16. Notes (textarea)

**Dynamic Behaviors:**
- Only one email can be preferred (unchecks others)
- Only one phone can be preferred (unchecks others)
- At least one email required
- Company dropdown loads via AJAX
- Role dropdown has predefined + "Other" option
- Tags autocomplete from existing tags

**Validation:**
- All email inputs must be valid email format
- All phone inputs allow international format
- All URL inputs must be valid URLs
- First name, last name, company required
- At least one email required
- At least one phone recommended (warning, not error)

**Testing:**
- [ ] Modal opens correctly
- [ ] All fields render
- [ ] Company dropdown searchable
- [ ] Add email button adds row
- [ ] Remove email button removes row
- [ ] Only one email can be preferred
- [ ] Add phone buttons work
- [ ] Only one phone can be preferred
- [ ] Role dropdown includes predefined
- [ ] Tags autocomplete
- [ ] ID/Passport only visible to Admin/PM
- [ ] Form validates before submit
- [ ] Success message displays
- [ ] Contact list refreshes

---

### 2.6 Settings Page (4-5 hours)

**Files to Create:**
- `admin/settings.php` - Settings page
- `assets/js/settings.js` - Settings interactions

**Location:** wProject → Pro Addons → Contacts Pro

**Settings Tabs:**

**1. General Settings:**
- Default Company Type (dropdown)
- Require Photo Upload (yes/no)
- Gravatar Fallback (yes/no)
- ID/Passport Visibility (Admins Only | Admins & PMs | All)

**2. Column Visibility:**
- Checkboxes for each column
- Default: 9 core columns checked
- Preview of list with selected columns

**3. Role Configuration:**
- List of predefined roles (editable)
- Add custom role button
- Remove role button
- Reorder roles (drag & drop)

**4. Notification Settings:**
- Notify when contact linked to project (yes/no)
- Notify when contact linked to task (yes/no)
- Notify when contact added to event (yes/no)
- Sender Name (text)
- Sender Email (email)

**5. Import/Export Settings:**
- CSV Field Delimiter (dropdown)
- CSV Text Qualifier (dropdown)
- Prevent Duplicate Imports (yes/no)
- Duplicate Detection Field (dropdown)
- Import Batch Size (dropdown)

**Testing:**
- [ ] Settings page loads
- [ ] All tabs work
- [ ] Settings save correctly
- [ ] Settings load on page refresh
- [ ] Preview updates in real-time
- [ ] Role reordering works
- [ ] All dropdowns have correct options

---

### Phase 2 Acceptance Criteria

**Before proceeding to Phase 3, verify:**

✅ **Navigation:**
- [ ] Contacts menu item appears
- [ ] Menu item navigates correctly
- [ ] Menu item highlights when active

✅ **Contact List:**
- [ ] List displays all contacts
- [ ] Search works in real-time
- [ ] Sorting works
- [ ] Pagination works
- [ ] Bulk actions work
- [ ] Column visibility toggles work
- [ ] Responsive design works
- [ ] Dark mode works

✅ **Contact Detail Panel:**
- [ ] Panel slides in correctly
- [ ] All info displays
- [ ] Quick actions work
- [ ] Activity timeline loads
- [ ] Tags can be managed
- [ ] Notes save automatically
- [ ] Permissions enforced

✅ **Forms:**
- [ ] Add company modal works
- [ ] Edit company modal works
- [ ] Add contact modal works
- [ ] Edit contact modal works
- [ ] All validations work
- [ ] AJAX submissions work
- [ ] Error handling works

✅ **Settings:**
- [ ] Settings page loads
- [ ] All settings save
- [ ] Settings apply correctly

✅ **Design:**
- [ ] Matches wProject styling exactly
- [ ] Uses wProject colors
- [ ] Uses Feather icons
- [ ] Dark mode works
- [ ] Responsive on all devices

---

## Phase 3: Integration & Relationships

**Goal:** Connect contacts to projects, tasks, and calendar events  
**Duration:** 25-30 hours  
**Priority:** HIGH - Core value proposition

### 3.1 Project Integration (8-10 hours)

**Files to Create:**
- `includes/class-integrations.php` - Integration logic
- `templates/project-contacts-panel.php` - Project page panel
- `assets/js/project-integration.js` - Project interactions

**Features:**

**1. Contacts Panel on Project Page:**
Add new section to project detail page (after team section)

```html
<div class="project-contacts">
  <h3>Contacts</h3>
  <div class="contact-list">
    <!-- Contact cards -->
  </div>
  <button class="add-contact-btn">+ Add Contact</button>
</div>
```

**2. Add Contact to Project:**
- Button opens modal with contact selector
- Multi-select dropdown (search by name/company)
- Selected contacts added via AJAX
- Panel updates without page refresh

**3. Contact Cards in Panel:**
- Contact photo
- Full name
- Company name
- Role
- Primary email (clickable)
- Primary phone (clickable)
- Remove button (unlink)

**4. Remove Contact from Project:**
- Click X icon
- Confirm dialog
- Unlink via AJAX
- Panel updates

**5. Project Creation:**
Add "Link Contacts" field to new project form
- Optional multi-select dropdown
- Contacts can be added during creation
- Contacts can be added/edited after creation

**Database Operations:**
```php
WProject_Contact::link_to_project($contact_id, $project_term_id);
WProject_Contact::unlink_from_project($contact_id, $project_term_id);
WProject_Contact::get_projects($contact_id);
WProject_Project::get_contacts($project_term_id);
```

**AJAX Endpoints:**
```php
contacts_pro_link_to_project
contacts_pro_unlink_from_project
contacts_pro_get_project_contacts
```

**Testing:**
- [ ] Contacts panel appears on project page
- [ ] Add contact button opens modal
- [ ] Contact selector searches correctly
- [ ] Multiple contacts can be selected
- [ ] Contacts link to project
- [ ] Contact cards display correctly
- [ ] Email/phone links work
- [ ] Remove button unlinks contact
- [ ] Confirmation dialog works
- [ ] Panel updates via AJAX
- [ ] Project list shows contact count
- [ ] Contact linking during project creation works

---

### 3.2 Task Integration (7-8 hours)

**Files to Modify:**
- `includes/class-integrations.php` - Add task methods
- Task form template (via wProject hooks)
- `assets/js/task-integration.js` - Task interactions

**Features:**

**1. External Contact Field in Task Form:**
Add field to create/edit task form

```html
<div class="form-group">
  <label>External Contact</label>
  <select name="external_contact" id="external-contact-select">
    <option value="">None</option>
    <!-- Populated via AJAX -->
  </select>
</div>
```

**2. Contact Selector:**
- Single-select dropdown (one contact per task in V1)
- Search by name or company
- Shows: Photo + Name (Company)
- Optional: "Send notification" checkbox

**3. Task Detail View:**
Display linked contact below task description

```html
<div class="task-contact">
  <img src="avatar" alt="">
  <div>
    <strong>John Doe</strong> (Acme Corp)
    <div class="quick-actions">
      <a href="mailto:john@acme.com">✉ Email</a>
      <a href="tel:+15550101">☎ Call</a>
    </div>
  </div>
</div>
```

**4. Task List:**
Add optional column: "Contact"
- Shows contact name if linked
- Icon indicates contact presence
- Tooltip shows full name + company

**Database Operations:**
```php
WProject_Contact::link_to_task($contact_id, $task_post_id);
WProject_Contact::unlink_from_task($contact_id, $task_post_id);
WProject_Contact::get_tasks($contact_id);
WProject_Task::get_contact($task_post_id);
```

**AJAX Endpoints:**
```php
contacts_pro_link_to_task
contacts_pro_unlink_from_task
contacts_pro_get_task_contact
```

**Notification:**
If "Send notification" checked, email contact when linked

**Testing:**
- [ ] Contact field appears in task form
- [ ] Contact dropdown loads contacts
- [ ] Contact can be selected
- [ ] Contact links to task on save
- [ ] Contact displays in task detail
- [ ] Email/phone quick actions work
- [ ] Contact unlinks when removed
- [ ] Task list column shows contact
- [ ] Notification email sends if checked
- [ ] Activity timeline shows task link

---

### 3.3 Calendar Integration (7-8 hours)

**Files to Modify:**
- `includes/class-integrations.php` - Add event methods
- Calendar Pro event form (via hooks)
- `assets/js/calendar-integration.js` - Event interactions

**Prerequisite:** wProject Calendar Pro must be installed

**Features:**

**1. External Contacts Field in Event Form:**
Add field below team attendees

```html
<div class="form-group">
  <label>External Contacts</label>
  <select name="external_contacts[]" multiple id="external-contacts-select">
    <!-- Populated via AJAX -->
  </select>
  <label>
    <input type="checkbox" name="send_invitations"> Send invitation emails
  </label>
</div>
```

**2. Contact Multi-Selector:**
- Multi-select dropdown
- Search by name or company
- Shows: Photo + Name (Company)
- Hold Ctrl/Cmd to select multiple

**3. Event Detail View:**
Add section for external contacts

```html
<div class="event-attendees">
  <h4>Team Attendees</h4>
  <!-- Existing team members -->
  
  <h4>External Contacts</h4>
  <div class="external-contacts">
    <!-- Contact avatars in row -->
  </div>
</div>
```

**4. Calendar View:**
- Event tooltip shows team + external contacts
- Icon indicates external contacts present

**5. RSVP Tracking:**
- Store RSVP status in wp_wproject_contact_events
- Display status in event detail
- Update via email link clicks (if email sent)

**Database Operations:**
```php
WProject_Contact::link_to_event($contact_id, $event_id, $send_invitation);
WProject_Contact::unlink_from_event($contact_id, $event_id);
WProject_Contact::get_events($contact_id);
WProject_Event::get_contacts($event_id);
WProject_Contact::update_rsvp($contact_id, $event_id, $status);
```

**AJAX Endpoints:**
```php
contacts_pro_link_to_event
contacts_pro_unlink_from_event
contacts_pro_get_event_contacts
contacts_pro_update_rsvp
```

**Email Invitation:**
If "Send invitations" checked, email all contacts

**Testing:**
- [ ] External contacts field appears in event form
- [ ] Contact multi-selector works
- [ ] Multiple contacts can be selected
- [ ] Contacts link to event on save
- [ ] Contacts display in event detail
- [ ] Calendar tooltip shows contacts
- [ ] Icon indicates external contacts
- [ ] Invitation emails send if checked
- [ ] RSVP status stores correctly
- [ ] RSVP updates via email links
- [ ] Activity timeline shows event link

---

### 3.4 Activity Timeline (3-4 hours)

**Files to Create:**
- `includes/class-activity-timeline.php` - Timeline builder
- `templates/activity-timeline.php` - Timeline display

**Timeline Logic:**

```php
class WProject_Activity_Timeline {
  public static function get_for_contact($contact_id, $filter = 'all') {
    // Fetch all activities
    $activities = [];
    
    if ($filter === 'all' || $filter === 'projects') {
      $projects = WProject_Contact::get_projects($contact_id);
      foreach ($projects as $project) {
        $activities[] = [
          'type' => 'project',
          'id' => $project->id,
          'title' => $project->name,
          'date' => $project->linked_at,
          'url' => get_term_link($project->id),
          'icon' => 'folder',
        ];
      }
    }
    
    if ($filter === 'all' || $filter === 'tasks') {
      $tasks = WProject_Contact::get_tasks($contact_id);
      // Similar logic
    }
    
    if ($filter === 'all' || $filter === 'events') {
      $events = WProject_Contact::get_events($contact_id);
      // Similar logic
    }
    
    // Sort by date descending
    usort($activities, function($a, $b) {
      return strtotime($b['date']) - strtotime($a['date']);
    });
    
    return $activities;
  }
}
```

**Display:**
```html
<div class="activity-timeline">
  <div class="timeline-filters">
    <button class="active" data-filter="all">All</button>
    <button data-filter="projects">Projects</button>
    <button data-filter="tasks">Tasks</button>
    <button data-filter="events">Events</button>
  </div>
  
  <div class="timeline-items">
    <div class="timeline-item">
      <i data-feather="folder"></i>
      <div>
        <a href="#">Project Name</a>
        <span class="date">Nov 28, 2025</span>
      </div>
    </div>
    <!-- More items -->
  </div>
  
  <div class="timeline-empty" style="display:none;">
    No activities yet.
  </div>
</div>
```

**Features:**
- Tabs filter by type
- Chronological order (newest first)
- Infinite scroll (load more)
- Icons for each type (Feather)
- Clickable links to related items
- Empty state if no activities

**Testing:**
- [ ] Timeline displays all activities
- [ ] Filters work correctly
- [ ] Activities sorted by date
- [ ] Links navigate correctly
- [ ] Icons display correctly
- [ ] Empty state displays when needed
- [ ] Infinite scroll loads more

---

### Phase 3 Acceptance Criteria

**Before proceeding to Phase 4, verify:**

✅ **Project Integration:**
- [ ] Contacts panel appears on projects
- [ ] Contacts can be added to projects
- [ ] Contacts can be removed from projects
- [ ] Multiple contacts per project work
- [ ] Project list shows contact count

✅ **Task Integration:**
- [ ] Contact field appears in task form
- [ ] Contact can be assigned to task
- [ ] Contact displays in task detail
- [ ] Notification email sends
- [ ] Task list shows contact

✅ **Calendar Integration:**
- [ ] External contacts field works
- [ ] Multiple contacts can be invited
- [ ] Invitation emails send
- [ ] RSVP tracking works
- [ ] Contacts display in event detail

✅ **Activity Timeline:**
- [ ] Timeline displays all activities
- [ ] Filters work
- [ ] Activities link correctly
- [ ] Timeline updates when relationships change

✅ **Data Integrity:**
- [ ] Deleting contact removes relationships
- [ ] Deleting project removes relationships
- [ ] Deleting task removes relationships
- [ ] Deleting event removes relationships
- [ ] Foreign keys enforce integrity

---

## Phase 4: Import/Export & Polish

**Goal:** Complete import/export features and final polish  
**Duration:** 20-25 hours  
**Priority:** MEDIUM - Nice to have but not critical for launch

### 4.1 CSV Import (8-10 hours)

**Files to Create:**
- `includes/class-import-export.php` - Import/export logic
- `templates/import-csv.php` - Import UI
- `assets/js/import-export.js` - Import interactions

**Import Workflow:**

**Step 1: Upload CSV**
```html
<div class="import-step-1">
  <h3>Upload CSV File</h3>
  <input type="file" accept=".csv" id="csv-upload">
  <button id="upload-btn">Upload</button>
</div>
```

**Step 2: Map Columns**
```html
<div class="import-step-2">
  <h3>Map Columns</h3>
  <table>
    <tr>
      <th>CSV Column</th>
      <th>Maps To</th>
    </tr>
    <tr>
      <td>company_name</td>
      <td><select><option selected>Company Name</option></select></td>
    </tr>
    <!-- Auto-detect or manual mapping -->
  </table>
  <button id="preview-btn">Preview</button>
</div>
```

**Step 3: Preview**
```html
<div class="import-step-3">
  <h3>Preview Import</h3>
  <p>Found: <strong>50 companies</strong>, <strong>150 contacts</strong></p>
  <p>Duplicates: <strong>5 contacts</strong> (will be skipped)</p>
  
  <table>
    <tr><th>Company</th><th>Contact</th><th>Email</th><th>Status</th></tr>
    <tr><td>Acme Corp</td><td>John Doe</td><td>john@acme.com</td><td>✅ Ready</td></tr>
    <tr><td>Beta Inc</td><td>Jane Smith</td><td>jane@beta.com</td><td>⚠️ Duplicate</td></tr>
  </table>
  
  <button id="import-btn">Import</button>
  <button id="cancel-btn">Cancel</button>
</div>
```

**Step 4: Import Progress**
```html
<div class="import-step-4">
  <h3>Importing...</h3>
  <div class="progress-bar">
    <div class="progress" style="width: 45%;">45%</div>
  </div>
  <p>Imported 45 of 100 contacts...</p>
</div>
```

**Step 5: Results**
```html
<div class="import-step-5">
  <h3>Import Complete</h3>
  <div class="results">
    <p>✅ Successfully imported: <strong>95 contacts</strong></p>
    <p>⚠️ Skipped duplicates: <strong>5 contacts</strong></p>
    <p>❌ Failed: <strong>0 contacts</strong></p>
  </div>
  <button id="view-contacts-btn">View Contacts</button>
  <button id="download-log-btn">Download Log</button>
</div>
```

**Features:**
- Multi-step wizard
- Auto-detect column mapping
- Duplicate detection (by email)
- Batch processing (100 at a time)
- Progress bar
- Error logging
- Rollback on critical failure

**CSV Format:**
```csv
company_name,first_name,last_name,email,phone,role,department
"Acme Corp",John,Doe,john@acme.com,555-0101,CEO,Executive
"Acme Corp",Jane,Smith,jane@acme.com,555-0102,CFO,Finance
```

**Testing:**
- [ ] CSV file upload works
- [ ] Column mapping auto-detects
- [ ] Manual mapping works
- [ ] Preview displays correctly
- [ ] Duplicate detection works
- [ ] Import processes in batches
- [ ] Progress bar updates
- [ ] Results display correctly
- [ ] Error log downloadable
- [ ] Failed imports don't break database

---

### 4.2 CSV Export (4-5 hours)

**Files to Modify:**
- `includes/class-import-export.php` - Add export methods
- `templates/contact-list.php` - Add export button

**Export Options:**

**1. Export All Contacts**
- Button in contact list header
- Exports all contacts to CSV
- Includes all fields

**2. Export Selected Contacts**
- Bulk action in contact list
- Select contacts via checkbox
- Exports only selected

**3. Export Filtered Contacts**
- Export current search/filter results
- Respects active filters
- Exports visible contacts only

**CSV Format:**
```csv
company_name,company_website,company_email,company_phone,first_name,last_name,role,department,email_1,email_1_label,email_2,email_2_label,phone_1,phone_1_label,phone_2,phone_2_label,linkedin,twitter,facebook,id_number,passport_number,tags,notes
```

**Features:**
- Generate CSV server-side
- Stream download (don't load in memory)
- UTF-8 encoding
- Proper escaping
- Filename: contacts-export-YYYY-MM-DD.csv

**Testing:**
- [ ] Export all works
- [ ] Export selected works
- [ ] Export filtered works
- [ ] CSV format correct
- [ ] All data exported
- [ ] UTF-8 characters work
- [ ] Large exports don't timeout
- [ ] Downloaded file opens in Excel

---

### 4.3 vCard Export (3-4 hours)

**Files to Modify:**
- `includes/class-import-export.php` - Add vCard methods
- `templates/contact-detail.php` - Add vCard button

**vCard Format (v3.0):**
```vcard
BEGIN:VCARD
VERSION:3.0
FN:John Doe
N:Doe;John;;;
ORG:Acme Corp
TITLE:CEO
EMAIL;TYPE=WORK:john@acme.com
EMAIL;TYPE=HOME:jdoe@gmail.com
TEL;TYPE=CELL:+1-555-0101
TEL;TYPE=WORK:+1-555-0102
URL:https://linkedin.com/in/johndoe
PHOTO;TYPE=JPEG;ENCODING=b:[base64]
NOTE:Notes text here
END:VCARD
```

**Features:**
- Export single contact from detail panel
- Export multiple contacts (bulk action)
- Include photo (base64 encoded)
- Include all emails/phones with types
- Include social URLs
- Standard vCard 3.0 format

**Testing:**
- [ ] Single contact export works
- [ ] Multiple contact export works
- [ ] vCard format valid
- [ ] Imports into Apple Contacts
- [ ] Imports into Google Contacts
- [ ] Imports into Outlook
- [ ] Photos included
- [ ] All fields populated

---

### 4.4 Email Notifications (3-4 hours)

**Files to Create:**
- `includes/class-notifications.php` - Notification system

**Notification Types:**

**1. Contact Linked to Project**
- Sent to: Project Manager
- Trigger: Contact added to project
- Template: See README.md

**2. Contact Linked to Task**
- Sent to: Task Owner
- Trigger: Contact added to task
- Template: See README.md

**3. Contact Added to Event**
- Sent to: Contact + Event Creator
- Trigger: Contact invited to event
- Template: See README.md

**Email Configuration:**
- Use wp_mail() function
- HTML email with plain text fallback
- Sender name/email from settings
- Include wProject branding
- Unsubscribe link (future)

**Features:**
- Email queueing (don't block requests)
- Batch sending (if multiple contacts)
- Failure logging
- Retry logic (3 attempts)
- HTML templates

**Testing:**
- [ ] Project link notification sends
- [ ] Task link notification sends
- [ ] Event invitation sends
- [ ] Emails formatted correctly
- [ ] Links work in emails
- [ ] Sender name/email correct
- [ ] Failures logged
- [ ] Retries work

---

### 4.5 Final Polish (2-3 hours)

**Tasks:**

**1. Loading States**
- Add spinners to all AJAX operations
- Disable buttons during operations
- Show skeleton screens while loading

**2. Error Handling**
- User-friendly error messages
- Fallback UI for failures
- Graceful degradation

**3. Empty States**
- "No contacts yet" message
- "Add your first contact" CTA
- "No results found" for searches

**4. Tooltips**
- Helpful tooltips on hover
- Explain complex features
- Keyboard shortcuts

**5. Accessibility**
- ARIA labels
- Keyboard navigation
- Focus management
- Screen reader friendly

**6. Performance**
- Optimize database queries
- Add caching where appropriate
- Minify CSS/JS
- Lazy load images

**7. Documentation**
- Inline help text
- Contextual tips
- Link to full documentation

**Testing:**
- [ ] All loading states display
- [ ] All errors show user-friendly messages
- [ ] Empty states display correctly
- [ ] Tooltips appear on hover
- [ ] Keyboard navigation works
- [ ] ARIA labels correct
- [ ] Performance acceptable (<2s load)
- [ ] Help documentation accessible

---

### Phase 4 Acceptance Criteria

**Before launching V1, verify:**

✅ **Import/Export:**
- [ ] CSV import wizard works
- [ ] CSV export works (all options)
- [ ] vCard export works
- [ ] Large imports/exports handle well
- [ ] Error handling robust

✅ **Notifications:**
- [ ] All notification types send
- [ ] Emails formatted correctly
- [ ] Links work in emails
- [ ] Failures logged

✅ **Polish:**
- [ ] Loading states everywhere
- [ ] Error messages user-friendly
- [ ] Empty states helpful
- [ ] Tooltips informative
- [ ] Accessibility standards met
- [ ] Performance acceptable
- [ ] Documentation complete

✅ **Overall Quality:**
- [ ] Zero critical bugs
- [ ] No PHP errors
- [ ] No JavaScript console errors
- [ ] All features tested
- [ ] UI consistent with wProject
- [ ] Dark mode works everywhere
- [ ] Responsive on all devices

---

## Testing Strategy

### Unit Testing (Optional but Recommended)

**Framework:** PHPUnit

**Test Coverage:**
- Model methods (CRUD operations)
- Validation logic
- Database operations
- AJAX handlers

**Example Test:**
```php
class CompanyTest extends WP_UnitTestCase {
  public function test_create_company_with_valid_data() {
    $data = [
      'company_name' => 'Test Company',
      'company_type' => 'client',
    ];
    $company = WProject_Company::create($data);
    $this->assertNotNull($company->id);
    $this->assertEquals('Test Company', $company->company_name);
  }
  
  public function test_create_company_with_duplicate_name_fails() {
    // ...
  }
}
```

### Manual Testing Checklist

**Phase 1: Database & Models**
- [ ] All tables created
- [ ] CRUD operations work
- [ ] Validations enforce rules
- [ ] Foreign keys cascade
- [ ] Data persists correctly

**Phase 2: UI & UX**
- [ ] All pages load
- [ ] All forms work
- [ ] All modals work
- [ ] Styling matches wProject
- [ ] Responsive design works
- [ ] Dark mode works

**Phase 3: Integrations**
- [ ] Project integration works
- [ ] Task integration works
- [ ] Calendar integration works
- [ ] Activity timeline works
- [ ] Relationships persist

**Phase 4: Import/Export**
- [ ] CSV import works
- [ ] CSV export works
- [ ] vCard export works
- [ ] Notifications send
- [ ] Polish complete

### Browser Testing

**Desktop:**
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)

**Mobile:**
- [ ] iOS Safari
- [ ] Android Chrome

**Tablet:**
- [ ] iPad Safari
- [ ] Android Chrome

### Performance Testing

**Metrics:**
- Contact list load time: <2s (1000 contacts)
- Contact detail load time: <500ms
- Form submission time: <1s
- CSV import time: <30s (1000 contacts)
- CSV export time: <10s (1000 contacts)

**Tools:**
- Chrome DevTools (Network, Performance)
- Query Monitor plugin
- PHP profiling (Xdebug)

### Security Testing

**Checks:**
- [ ] All nonces verified
- [ ] All capabilities checked
- [ ] All input sanitized
- [ ] All output escaped
- [ ] SQL injection prevented
- [ ] XSS prevented
- [ ] CSRF prevented
- [ ] Sensitive data protected

---

## Deployment Checklist

### Pre-Launch

**Code Quality:**
- [ ] All tests pass
- [ ] No PHP errors/warnings
- [ ] No JavaScript console errors
- [ ] Code commented adequately
- [ ] Functions documented

**Documentation:**
- [ ] README.md complete
- [ ] DEVELOPMENT-PLAN.md complete
- [ ] Inline documentation added
- [ ] User guide created
- [ ] Screenshots added

**Database:**
- [ ] Backup database before deploy
- [ ] Test database migrations
- [ ] Verify data persistence
- [ ] Verify rollback procedure

**Performance:**
- [ ] CSS minified
- [ ] JavaScript minified
- [ ] Images optimized
- [ ] Database queries optimized
- [ ] Caching configured

**Security:**
- [ ] All security checks pass
- [ ] Sensitive data encrypted
- [ ] File permissions correct
- [ ] No debug code left
- [ ] No console.log statements

### Launch

**Steps:**
1. Backup production database
2. Upload plugin files
3. Activate plugin
4. Verify tables created
5. Test basic functionality
6. Import test contacts
7. Verify integrations work
8. Test on production data
9. Monitor for errors
10. Announce to users

### Post-Launch

**Monitoring:**
- [ ] Check error logs daily (first week)
- [ ] Monitor performance metrics
- [ ] Gather user feedback
- [ ] Track usage analytics
- [ ] Document issues/bugs

**Support:**
- [ ] Create support documentation
- [ ] Set up bug tracking
- [ ] Establish update schedule
- [ ] Plan feature roadmap

---

## Post-Launch Maintenance

### Bug Fixes (Ongoing)

**Priority Levels:**
- **Critical:** Data loss, security, complete failure (fix within 24h)
- **High:** Feature broken, major UX issue (fix within 1 week)
- **Medium:** Minor bug, workaround exists (fix within 1 month)
- **Low:** Cosmetic, minor annoyance (fix when time permits)

### Version 1.1 (Q1 2026)

**Focus:** Performance and Usability

**Features:**
- Performance optimization (10,000+ contacts)
- Advanced search filters
- Contact merge tool
- Bulk edit contacts
- Contact history log

**Estimated Effort:** 40-50 hours

### Version 1.5 (Q2 2026)

**Focus:** Enhanced Import/Export

**Features:**
- vCard import
- Contact custom fields
- Contact categories
- Contact groups
- Email integration

**Estimated Effort:** 50-60 hours

### Version 2.0 (Q3 2026)

**Focus:** External Integrations

**Features:**
- Google Contacts sync
- Outlook/Exchange sync
- Many-to-many company relationships
- Contact portal
- Advanced reporting
- Contact scoring

**Estimated Effort:** 80-100 hours

---

## Estimated Timeline

### Development Schedule

**Assuming:** 1 full-time developer, 40 hours/week

| Phase | Duration | Weeks |
|-------|----------|-------|
| Phase 1: Core Foundation | 35-40h | 1 week |
| Phase 2: UI & UX | 40-45h | 1 week |
| Phase 3: Integrations | 25-30h | 0.75 weeks |
| Phase 4: Import/Export | 20-25h | 0.5 weeks |
| **Total Development** | **120-140h** | **3.5 weeks** |
| Testing & QA | 15-20h | 0.5 weeks |
| Documentation | 5-10h | 0.25 weeks |
| Buffer (bugs, changes) | 10-20h | 0.5 weeks |
| **Total Project** | **150-190h** | **4.75 weeks** |

**Realistic Launch:** 5-6 weeks from start

---

## Resources

### Documentation
- [WordPress Plugin API](https://codex.wordpress.org/Plugin_API)
- [wProject Theme Documentation](https://rocketapps.com.au/wproject-theme/)
- [Feather Icons](https://feathericons.com/)
- [jQuery Documentation](https://api.jquery.com/)

### Tools
- [Local by Flywheel](https://localwp.com/) - Local WordPress development
- [Query Monitor](https://wordpress.org/plugins/query-monitor/) - Debug queries
- [Debug Bar](https://wordpress.org/plugins/debug-bar/) - Debug WordPress
- [PHPUnit](https://phpunit.de/) - Unit testing
- [Chrome DevTools](https://developer.chrome.com/docs/devtools/) - Frontend debugging

### External Libraries (if needed later)
- [Google OAuth](https://developers.google.com/identity/protocols/oauth2) - Google Contacts sync
- [Microsoft Graph API](https://docs.microsoft.com/en-us/graph/) - Outlook sync
- [PHPVCard](https://github.com/jeroendesloovere/vcard) - vCard generation (alternative)

---

**Document End**

**Next Steps:**
1. Review and approve this development plan
2. Set up development environment
3. Begin Phase 1: Core Foundation
4. Follow testing checklist at end of each phase
5. Launch V1.0.0

**Questions or Changes?**
Update this document before starting development to avoid scope creep.
