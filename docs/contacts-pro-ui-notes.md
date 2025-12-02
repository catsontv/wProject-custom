wProject Contacts Pro – UI & Data Flow Notes (fix/contacts-form-responsive)

Scope:
This document summarizes the key structural and behavioral changes made in the fix/contacts-form-responsive branch for the wProject Contacts Pro plugin, so future visual/UI work can build on the current architecture without breaking functionality.

1) Contacts list – layout and structure

- View:
  - Admin page that shows the Contacts tab/list.
- Layout:
  - Contacts are displayed in a responsive HTML table (not card/grid view anymore).
  - Key HTML elements:
    - Table body ID: #contacts-table-body
    - Old grid container: #contacts-grid (no longer used for rendering; kept only for backward compatibility / legacy markup).
- Rendering:
  - JavaScript file: assets/js/contacts-pro.js (handle used by WordPress enqueue for Contacts Pro).
  - Main controller: ContactsPage object.
  - Rendering function:
    - renderContacts() / renderAll() → loops through contact data and calls renderContactTableRow(contact).
  - Each row is a <tr> with cells for:
    - Company (clickable)
    - Contact name (clickable)
    - Role / Department (depending on the column config)
    - Email
    - Phone
    - Actions (buttons, if any)

2) Companies list – layout and structure

- Companies are also rendered into a table structure (similar pattern).
- Table body ID: #companies-table-body (or equivalent, depending on template).
- Rendering function:
  - renderCompanies() → renderCompanyTableRow(company).

3) Detail panels (side panels)

- Contacts:
  - Clicking a contact name in the table opens a right‑side detail panel.
  - JS: handled in ContactsPage event bindings (click handler attached to contact name link in the row).
  - The panel shows:
    - Basic info: name, company, role, department, notes, etc.
    - Emails: list with labels and preferred flags.
    - Phones: list with type/label and preferred flags.
    - Actions: Edit and Delete buttons, styled as primary/secondary buttons.

- Companies:
  - Clicking the company name in the contacts table (or company table) opens the company detail panel.
  - JS: similar pattern to contacts; a CompanyDetailPanel (or equivalent) renderer is used.
  - The panel shows:
    - Company name and meta info.
    - Related contacts (if any).
    - Contact info fields (like phones/emails if defined).
    - Actions: Edit and Delete buttons.

4) Button styling – consistency between contacts and companies

- Goal:
  - Make company Edit/Delete buttons visually match contact Edit/Delete buttons.
- CSS/HTML conventions (current design):
  - Buttons use the same classes for both contacts and companies.
    - e.g. .button, .button-primary, .button-secondary or plugin‑specific button classes.
  - Icons:
    - Icons are rendered using the same system (e.g. Feather icons or Dashicons).
    - Edit button: pencil/edit icon.
    - Delete button: trash/bin icon.
- Implementation detail:
  - Company panel markup for buttons was updated to mirror the contact panel:
    - Same class names.
    - Same HTML structure (icon span + label text).
    - Same order and spacing.

If you later adjust button styles:
- Update the shared button CSS classes instead of changing only one panel.
- If you change icon library or icon markup, update both:
  - Contact detail panel buttons
  - Company detail panel buttons

5) Data loading – backend APIs and minimal list view data

- AJAX actions (registered via WordPress):
  - contacts_pro_list_contacts
    - Returns a list of contacts with minimal data for list view + some relations.
  - contacts_pro_list_companies
    - Returns a list of companies with minimal data.
  - contacts_pro_get_contact
    - Returns full detail for a single contact (used by the detail panel).
  - contacts_pro_get_company
    - Returns full detail for a single company.

- PHP source (simplified):
  - Contact listing:
    - Class: class-contact.php (or similarly named class in the plugin).
    - Method: list_all() (or equivalent).
    - Important behavior (as of fix/contacts-form-responsive):
      - For each contact in the list, we now load:
        - Emails:
          - Query: FROM {prefix}wproject_contact_emails
          - Ordered by is_preferred DESC, id ASC
          - Stored in $contact->emails
        - Phones:
          - Query: FROM {prefix}wproject_contact_phones
          - Ordered by is_preferred DESC, id ASC
          - Stored in $contact->phones  ← this was previously missing
        - Calculated fields:
          - $contact->primary_email
          - $contact->primary_phone (may be null if no phone)

- Why this matters:
  - The JS contacts list only works with the data sent by contacts_pro_list_contacts.
  - Before the fix, phones were not included in that response (phones array was always empty), so the PHONE column in the table could never show data.
  - Now phones are loaded at list view level, enabling the table to show a useful phone number without needing a second API call.

6) Phone selection logic – how the PHONE column is chosen

- Location:
  - JavaScript: contacts-pro.js
  - Function: a helper inside renderContactTableRow(contact) (or a dedicated function used by it).
- Input data:
  - contact.phones: array of phone objects (from PHP).
  - contact.primary_phone: single value, may be null.
- Strategy:
  - The code tries multiple strategies to choose the best phone to show:
    1) If primary_phone exists and is usable:
       - Use contact.primary_phone.
    2) Else, if phones array is present and not empty:
       - Try to find a preferred cell/mobile:
         - is_preferred flag and/or phone_type/label containing cell or mobile.
       - If none, try any cell/mobile (based on phone_type/label).
       - If none, try any preferred phone (is_preferred).
       - If none, use the first phone in the array.
    3) If no phones at all:
       - Show an empty cell (or a dash, depending on design).
- Output:
  - A user-friendly phone string.
  - Optionally wrapped in a <a href="tel:..."> link.

If you change how phones should be prioritized:
- Adjust the phone selection helper in contacts-pro.js.
- Keep the PHP behavior (loading emails and phones for list view) as-is, unless changing database structure.

7) Versioning and cache-busting

- Plugin version:
  - Updated to 2.2.2 during this work.
- Console logging:
  - On init, contacts-pro.js logs:
    - VERSION 2.2.2 - ENHANCED DEBUGGING & FIXES!
    - It also logs AJAX requests, responses, and contact data used for rendering.
- Frontend asset cache-busting:
  - WordPress attaches ?ver={plugin_version_or_asset_version} to the JS file.
  - When making JS changes in the future:
    - Bump the plugin version in the main plugin file.
    - Confirm in browser console that the new version string appears.
    - Do a hard refresh after updating the plugin.

8) How to safely make future visual tweaks

When changing the UI in this area in the future:

- Do:
  - Keep IDs and hooks that JS expects:
    - #contacts-table-body
    - Clickable selectors for contact and company names.
  - Keep the basic data attributes if used (e.g., data-id, data-company-id).
  - Preserve the right-side panel containers and their IDs/classes.
  - If restructuring HTML, verify:
    - Contacts still appear in the table.
    - Clicking a contact still opens the detail panel.
    - Companies still show and their panel opens correctly.
  - Use browser console to check for JS errors after changes.

- Avoid:
  - Removing or renaming key IDs/classes without updating JS.
  - Changing AJAX action names or response structure unless updating the JS logic accordingly.
  - Stripping the phones/emails relations from the list-all PHP methods.

Summary of key functional changes in this branch (for quick reference)

- Contacts list now correctly receives phones data on list view:
  - PHP: list_all() / contacts_pro_list_contacts now loads $contact->phones.
- Contacts table PHONE column:
  - Uses a robust multi-step strategy to choose and display the best phone number.
- Company detail panel:
  - Edit/Delete buttons now use same markup and classes as contact detail panel buttons for visual consistency.
- Version bump and logging:
  - Plugin version: 2.2.2
  - JS console includes versioned init logs useful for debugging.

