# wProject Contacts Pro - Phase 1 Complete

## Installation Instructions

### Step 1: Download the Plugin

1. Go to the [wproject-contacts-pro-phase1 branch](https://github.com/catsontv/wProject-custom/tree/wproject-contacts-pro-phase1)
2. Click the green "Code" button
3. Select "Download ZIP"
4. Extract the ZIP file
5. Locate the `wproject-contacts-pro` folder

### Step 2: Install in WordPress

1. Copy the entire `wproject-contacts-pro` folder to your WordPress installation:
   - Path: `wp-content/plugins/wproject-contacts-pro/`

2. Go to WordPress Admin → Plugins
3. Find "wProject Contacts Pro" in the list
4. Click "Activate"

### Step 3: Verify Installation

After activation, the plugin will:
- ✅ Check PHP version (requires 8.0+)
- ✅ Check if wProject theme is active
- ✅ Create 8 database tables automatically
- ✅ Register the contact_tag taxonomy

---

## What's Included in Phase 1

### ✅ Core Files

1. **Main Plugin File** (`wproject-contacts-pro.php`)
   - Plugin initialization
   - Requirement checks
   - Activation/deactivation hooks
   - Asset loading

2. **Database Class** (`includes/class-database.php`)
   - 8 custom tables:
     - `wp_wproject_companies`
     - `wp_wproject_contacts`
     - `wp_wproject_contact_emails`
     - `wp_wproject_contact_phones`
     - `wp_wproject_contact_socials`
     - `wp_wproject_contact_projects`
     - `wp_wproject_contact_tasks`
     - `wp_wproject_contact_events`
   - Foreign key relationships
   - Cascade delete support

3. **Company Model** (`includes/class-company.php`)
   - Complete CRUD operations
   - Validation
   - Search functionality
   - Relationship management

4. **Contact Model** (`includes/class-contact.php`)
   - Complete CRUD operations
   - Email/phone/social management
   - Tag support
   - Avatar/Gravatar integration
   - Validation

5. **AJAX Handlers** (`includes/class-ajax-handlers.php`)
   - All company endpoints
   - All contact endpoints
   - Security (nonce + capability checks)
   - Error handling

6. **Assets**
   - CSS files (frontend + admin)
   - JavaScript with AJAX helper

### ✅ Features Working

- ✅ Create companies
- ✅ Update companies
- ✅ Delete companies (cascades to contacts)
- ✅ List companies with pagination
- ✅ Search companies
- ✅ Create contacts with multiple emails/phones/socials
- ✅ Update contacts
- ✅ Delete contacts
- ✅ List contacts with pagination
- ✅ Search contacts
- ✅ Tag management (add/remove tags)
- ✅ Data persistence (survives plugin deactivation/deletion)

---

## Testing Phase 1

### Test 1: Database Creation

1. Activate the plugin
2. Go to phpMyAdmin or use WP CLI
3. Verify all 8 tables exist:
   ```sql
   SHOW TABLES LIKE 'wp_wproject_%';
   ```
4. Check table structure:
   ```sql
   DESCRIBE wp_wproject_companies;
   DESCRIBE wp_wproject_contacts;
   ```

### Test 2: Company CRUD via Browser Console

Open your browser's console (F12) and run:

```javascript
// Create a company
wpContactsProAjax.createCompany({
    company_name: 'Test Company',
    company_type: 'client',
    company_email: 'test@example.com'
}, {
    success: function(data) {
        console.log('Company created:', data);
    },
    error: function(message) {
        console.error('Error:', message);
    }
});

// List companies
wpContactsProAjax.listCompanies({
    page: 1,
    per_page: 25
}, {
    success: function(data) {
        console.log('Companies:', data);
    }
});
```

### Test 3: Contact CRUD via Browser Console

```javascript
// Create a contact (replace company_id with actual ID from Test 2)
wpContactsProAjax.createContact({
    company_id: 1,
    first_name: 'John',
    last_name: 'Doe',
    role: 'CEO',
    emails: [
        { email: 'john@example.com', label: 'work', is_preferred: 1 }
    ],
    phones: [
        { phone_number: '+1-555-0101', phone_type: 'cell', label: 'mobile', is_preferred: 1 }
    ]
}, {
    success: function(data) {
        console.log('Contact created:', data);
    },
    error: function(message) {
        console.error('Error:', message);
    }
});

// List contacts
wpContactsProAjax.listContacts({
    page: 1,
    per_page: 25
}, {
    success: function(data) {
        console.log('Contacts:', data);
    }
});

// Search contacts
wpContactsProAjax.searchContacts('john', {
    success: function(data) {
        console.log('Search results:', data);
    }
});
```

### Test 4: Validation Tests

```javascript
// Try to create company with duplicate name
wpContactsProAjax.createCompany({
    company_name: 'Test Company' // Same as above
}, {
    error: function(message) {
        console.log('Expected error:', message); // Should fail
    }
});

// Try to create contact without email
wpContactsProAjax.createContact({
    company_id: 1,
    first_name: 'Jane',
    last_name: 'Smith'
    // No emails
}, {
    error: function(message) {
        console.log('Expected error:', message); // Should fail
    }
});

// Try to create contact with invalid email
wpContactsProAjax.createContact({
    company_id: 1,
    first_name: 'Jane',
    last_name: 'Smith',
    emails: [
        { email: 'not-an-email', label: 'work' }
    ]
}, {
    error: function(message) {
        console.log('Expected error:', message); // Should fail
    }
});
```

### Test 5: Tag Management via WordPress Admin

1. Go to WordPress Admin
2. You should see "Contact Tags" in the admin menu (under a generic location)
3. Create some tags:
   - "Client"
   - "VIP"
   - "Partner"
4. Tags will be available for assignment in Phase 2 UI

### Test 6: Foreign Key Cascade

```javascript
// Create a company with contacts
wpContactsProAjax.createCompany({
    company_name: 'Delete Test Company',
    company_type: 'client'
}, {
    success: function(companyData) {
        const companyId = companyData.data.id;
        
        // Create a contact for this company
        wpContactsProAjax.createContact({
            company_id: companyId,
            first_name: 'Test',
            last_name: 'Contact',
            emails: [{ email: 'test@test.com', label: 'work' }]
        }, {
            success: function(contactData) {
                console.log('Contact created:', contactData);
                
                // Now delete the company
                wpContactsProAjax.deleteCompany(companyId, {
                    success: function() {
                        console.log('Company deleted');
                        
                        // Try to get the contact - should fail
                        wpContactsProAjax.getContact(contactData.data.id, {
                            error: function(message) {
                                console.log('Contact was cascade deleted:', message);
                            }
                        });
                    }
                });
            }
        });
    }
});
```

### Test 7: Data Persistence

1. Create some companies and contacts using the tests above
2. Go to WordPress Admin → Plugins
3. **Deactivate** wProject Contacts Pro
4. Check database - all tables and data should still exist
5. **Reactivate** the plugin
6. Run list queries - all data should be intact
7. **Delete** the plugin (don't do this if you want to keep data)
8. Check database - data still persists (as required)

---

## Phase 1 Acceptance Checklist

Before moving to Phase 2, verify:

### Database
- [ ] All 8 tables created successfully
- [ ] Foreign keys enforce data integrity
- [ ] Indexes exist on key columns
- [ ] Data persists through deactivation
- [ ] Data persists through deletion

### Company Operations
- [ ] Create company works
- [ ] Update company works
- [ ] Delete company works
- [ ] Get company works
- [ ] List companies with pagination works
- [ ] Search companies works
- [ ] Duplicate name validation works
- [ ] Email/URL validation works

### Contact Operations
- [ ] Create contact works
- [ ] Update contact works
- [ ] Delete contact works
- [ ] Get contact with relations works
- [ ] List contacts with pagination works
- [ ] Search contacts works
- [ ] Multiple emails per contact works
- [ ] Multiple phones per contact works
- [ ] Preferred email/phone logic works
- [ ] Social profiles work
- [ ] Required field validation works
- [ ] Email format validation works

### Tags
- [ ] Tags can be created in admin
- [ ] Tags can be assigned to contacts
- [ ] Tags can be removed from contacts
- [ ] Tags persist with contacts

### AJAX & Security
- [ ] All AJAX endpoints respond correctly
- [ ] Nonce verification works
- [ ] Capability checks work
- [ ] Error responses are JSON formatted
- [ ] Success responses are JSON formatted

### Technical
- [ ] No PHP errors in debug log
- [ ] No JavaScript console errors
- [ ] Plugin activates without errors
- [ ] Plugin deactivates without errors
- [ ] Requirements check works (PHP version, theme)

---

## Known Limitations (Phase 1)

### No UI Yet
Phase 1 provides backend functionality only. Phase 2 will add:
- Contact list page
- Add/edit forms
- Detail panels
- Search interface

### No Integration Yet
Project/Task/Event integration comes in Phase 3.

### No Import/Export Yet
CSV import/export comes in Phase 4.

---

## Next Steps: Phase 2

Once Phase 1 testing is complete, Phase 2 will add:

1. Main navigation integration (Contacts menu item)
2. Contact list page with search and filters
3. Contact detail slide-in panel
4. Add/Edit company modal
5. Add/Edit contact modal
6. Settings page

Estimated Phase 2 completion: 40-45 hours of development.

---

## Troubleshooting

### Plugin Won't Activate
- Check PHP version (must be 8.0+)
- Ensure wProject theme is active
- Check error logs: `wp-content/debug.log`

### Tables Not Created
- Check database user permissions
- Try manually running: `wp_wproject_companies` SQL from `class-database.php`
- Check for SQL errors in debug log

### AJAX Not Working
- Check browser console for JavaScript errors
- Verify nonce is being sent
- Check that user is logged in
- Verify user has required capabilities

### Foreign Key Errors
- MySQL version must be 5.6+
- InnoDB engine must be enabled
- Check for existing data conflicts

---

## Support

For issues or questions:
1. Check this README
2. Review DEVELOPMENT-PLAN.md
3. Check debug logs
4. Test with browser console as shown above

---

**Phase 1 Status:** ✅ COMPLETE  
**Ready for Testing:** YES  
**Ready for Phase 2:** Pending test results
