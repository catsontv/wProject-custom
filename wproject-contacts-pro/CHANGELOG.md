# Changelog

All notable changes to wProject Contacts Pro will be documented in this file.

## [1.0.12] - 2025-11-30

### 🎉 PHASE 2 COMPLETE - Full CRUD Operations Working

### Fixed
- **CRITICAL:** Fixed database schema - `company_id` column now allows NULL
- Added automatic schema upgrade on plugin activation
- Changed foreign key constraint from `ON DELETE CASCADE` to `ON DELETE SET NULL`
- Contacts can now be created without selecting a company
- Fixed JavaScript version message display

### Added
- Automatic database schema migration system
- `upgrade_schema()` method to handle existing installations
- Comprehensive error logging for database operations
- Schema upgrade detection and execution on activation

### Technical Details
- Modified `wp_wproject_contacts` table schema
- Added migration support for existing installations
- Enhanced activation hook to run schema upgrades


## [1.0.11] - 2025-11-30

### Fixed
- Set `company_id` to NULL instead of 0 when no company selected (PHP side)
- Fixed AJAX handler to use NULL default instead of 0
- Updated both create and update methods to handle NULL company_id

### Added
- Extensive debugging in class-contact.php with database error logging
- Extensive debugging in class-ajax-handlers.php with request/response logging
- Detailed error messages showing exact database failures


## [1.0.10] - 2025-11-30

### Fixed
- JavaScript now excludes `company_id` from form data if empty (prevents empty string)
- JavaScript now excludes `role` from form data if empty
- Prevents sending empty strings to database that expect NULL

### Added
- Enhanced rendering debug logging
- `renderAll()` debug output showing contacts/companies counts
- `renderCompanies()` debug output with HTML generation logs


## [1.0.9] - 2025-11-30

### Fixed
- Made `company_id` optional for contact creation (validation layer)
- Fixed empty `company_id` validation - now allows contacts without company

### Added
- Company edit functionality with modal pre-fill
- Company delete functionality with confirmation
- Complete update mode for company forms (create/edit switching)
- Reset company modal to create mode when clicking Add Company
- Event handlers for edit/delete company buttons


## [1.0.8] - 2025-11-30

### Added
- **Company filtering** - Companies now display in grid view!
- `currentFilter` property to track active filter state
- `filterContacts()` implementation for all/contacts/companies filters
- `renderCompanyCard()` to display companies in grid format
- `renderCompanies()` for companies-only view
- `renderAll()` for both contacts and companies

### Fixed
- Filter state persists across create/update/delete operations
- Companies visible when clicking "Companies" filter button


## [1.0.7] - 2025-11-30

### Fixed
- **CRITICAL:** JavaScript context issue - fixed method reference losing `this` context
- Changed from storing method in variable to direct if/else method calls
- Contact creation and update now work properly

### Technical Details
```javascript
// BEFORE (broken):
const ajaxMethod = isEdit ? ContactsAjax.updateContact : ContactsAjax.createContact;
ajaxMethod(formData, {...}); // this = undefined

// AFTER (working):
if (isEdit) {
    ContactsAjax.updateContact(editId, formData, {...});
} else {
    ContactsAjax.createContact(formData, {...});
}
```


## [1.0.6] - 2025-11-30

### Fixed
- **CRITICAL:** WordPress AJAX form interception - completely bypassed
- Changed form submit buttons from `type="submit"` to `type="button"`
- Switched from form submit handlers to button click handlers
- Added `action="javascript:void(0);"` to forms

### Technical Details
- WordPress's ajax.min.js was intercepting form submissions
- Console showed button clicks but not form submit events
- Solution: Bypass browser/WordPress form submission entirely


## [1.0.5] - 2025-11-30

### Fixed
- Added `action="javascript:void(0);"` to prevent WordPress AJAX interception
- Enhanced form submission event handling


## [1.0.4] - 2025-11-30

### Added
- Full contact edit functionality
- Modal pre-fill for editing contacts
- Dynamic modal title/button text switching (Add/Edit modes)

### Fixed
- Form submission interception handling
- Edit contact workflow implementation


## [1.0.3] - 2025-11-30

### Added
- Edit contact handlers with AJAX endpoint integration
- Delete contact handlers with confirmation dialogs
- Comprehensive debugging throughout AJAX flow


## [1.0.2] - 2025-11-30

### Fixed
- **CRITICAL:** AJAX response structure - removed double-nesting
- Field name mismatches: `primary_email`, `primary_phone`, `role`, `company_name`
- JavaScript field mapping to match PHP expectations

### Added
- Detailed debugging logs for all AJAX requests/responses
- Console logging for form submission flow


## [1.0.1] - 2025-11-29

### Added
- Initial plugin structure
- Database tables creation
- Basic AJAX endpoints
- Contact and Company models


## Phase 2 Status: ✅ COMPLETE

### Working Features
✅ Create contacts (with or without company)
✅ Edit contacts
✅ Delete contacts
✅ Create companies
✅ Edit companies
✅ Delete companies
✅ Company filtering and display
✅ Contact filtering and display
✅ All/Contacts/Companies filter tabs
✅ Modal forms for create/edit
✅ AJAX CRUD operations
✅ Error handling and validation

### Known Issues
None - All Phase 2 functionality working!

### Next Phase
Ready to proceed to **Phase 3: Integration & Relationships**
- Link contacts to projects
- Link contacts to tasks
- Link contacts to calendar events
- Activity timeline
- Contact history tracking


## Development Journey Summary

This plugin went through extensive debugging to achieve full CRUD functionality:

1. **v1.0.1-1.0.2**: Fixed AJAX response structures and field mappings
2. **v1.0.3-1.0.4**: Implemented edit/delete functionality
3. **v1.0.5-1.0.7**: Fixed WordPress form interception and JavaScript context issues
4. **v1.0.8**: Implemented company filtering and rendering
5. **v1.0.9**: Made company optional for contacts
6. **v1.0.10-1.0.12**: Fixed database schema to allow NULL company_id

**Total Development Time**: ~12 iterations over 2 days
**Result**: Fully functional Phase 2 implementation
