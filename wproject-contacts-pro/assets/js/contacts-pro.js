/**
 * wProject Contacts Pro - Frontend JavaScript
 * Phase 2 Complete - All Features Implemented
 * Version: 2.0.0
 */

(function($) {
    'use strict';

    // Check if wpContactsPro is defined
    if (typeof wpContactsPro === 'undefined') {
        console.error('wProject Contacts Pro: Configuration not loaded');
        return;
    }

    /**
     * AJAX Helper
     */
    const ContactsAjax = {

        /**
         * Make AJAX request
         */
        request: function(action, data, callbacks) {
            data = data || {};
            data.action = action;
            data.nonce = wpContactsPro.nonce;

            console.log('=== AJAX Request ===');
            console.log('Action:', action);
            console.log('Data being sent:', data);

            $.ajax({
                url: wpContactsPro.ajaxurl,
                type: 'POST',
                data: data,
                beforeSend: function() {
                    if (callbacks.beforeSend) {
                        callbacks.beforeSend();
                    }
                },
                success: function(response) {
                    console.log('=== AJAX Response ===');
                    console.log('Response:', response);

                    if (response.success) {
                        console.log('✓ Success!');
                        if (callbacks.success) {
                            callbacks.success(response.data);
                        }
                    } else {
                        console.error('✗ Server returned error');
                        console.error('Error data:', response.data);
                        if (callbacks.error) {
                            callbacks.error(response.data ? response.data.message : 'Unknown error');
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    if (callbacks.error) {
                        let errorMsg = 'Request failed: ' + error;
                        callbacks.error(errorMsg);
                    }
                },
                complete: function() {
                    if (callbacks.complete) {
                        callbacks.complete();
                    }
                }
            });
        },

        createCompany: function(data, callbacks) {
            this.request('contacts_pro_create_company', data, callbacks);
        },

        updateCompany: function(id, data, callbacks) {
            data.id = id;
            this.request('contacts_pro_update_company', data, callbacks);
        },

        deleteCompany: function(id, callbacks) {
            this.request('contacts_pro_delete_company', { id: id }, callbacks);
        },

        getCompany: function(id, callbacks) {
            this.request('contacts_pro_get_company', { id: id }, callbacks);
        },

        listCompanies: function(params, callbacks) {
            this.request('contacts_pro_list_companies', params, callbacks);
        },

        createContact: function(data, callbacks) {
            this.request('contacts_pro_create_contact', data, callbacks);
        },

        updateContact: function(id, data, callbacks) {
            data.id = id;
            this.request('contacts_pro_update_contact', data, callbacks);
        },

        deleteContact: function(id, callbacks) {
            this.request('contacts_pro_delete_contact', { id: id }, callbacks);
        },

        getContact: function(id, callbacks) {
            this.request('contacts_pro_get_contact', { id: id }, callbacks);
        },

        listContacts: function(params, callbacks) {
            this.request('contacts_pro_list_contacts', params, callbacks);
        },

        searchContacts: function(query, callbacks) {
            this.request('contacts_pro_search_contacts', { query: query }, callbacks);
        }
    };

    // Expose to global scope
    window.wpContactsProAjax = ContactsAjax;

    /**
     * Modal Handler
     */
    const ModalHandler = {
        open: function(modalId) {
            $('#' + modalId).fadeIn(300);
            $('body').addClass('wpc-modal-open');

            // Re-initialize feather icons
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        },

        close: function(modalId) {
            $('#' + modalId).fadeOut(300);
            $('body').removeClass('wpc-modal-open');
        },

        closeAll: function() {
            $('.wpc-modal').fadeOut(300);
            $('body').removeClass('wpc-modal-open');
        }
    };

    /**
     * Detail Panel Handler
     */
    const DetailPanel = {
        open: function(contactId) {
            const $panel = $('#contact-detail-panel');
            $panel.fadeIn(300).addClass('open');
            $('body').addClass('wpc-panel-open');

            // Load contact data
            this.loadContact(contactId);
        },

        close: function() {
            const $panel = $('#contact-detail-panel');
            $panel.fadeOut(300).removeClass('open');
            $('body').removeClass('wpc-panel-open');
        },

        loadContact: function(contactId) {
            const $panelBody = $('#contact-detail-panel .wpc-panel-body');
            $panelBody.html('<div class="loading">Loading contact...</div>');

            ContactsAjax.getContact(contactId, {
                success: function(contact) {
                    const html = DetailPanel.renderContactDetail(contact);
                    $panelBody.html(html);

                    // Re-initialize feather icons
                    if (typeof feather !== 'undefined') {
                        feather.replace();
                    }
                },
                error: function(message) {
                    $panelBody.html('<div class="error">Error loading contact: ' + message + '</div>');
                }
            });
        },

        renderContactDetail: function(contact) {
            const name = contact.first_name + ' ' + contact.last_name;
            const company = contact.company_name || 'No company';
            const role = contact.role || '';

            let html = `
                <div class="contact-detail-header">
                    <div class="contact-detail-avatar">
                        <i data-feather="user"></i>
                    </div>
                    <div class="contact-detail-info">
                        <h2>${name}</h2>
                        <p class="company">${company}${role ? ' - ' + role : ''}</p>
                    </div>
                </div>

                <div class="contact-detail-section">
                    <h3><i data-feather="mail"></i> Email Addresses</h3>
                    <div class="contact-detail-items">
            `;

            if (contact.emails && contact.emails.length > 0) {
                contact.emails.forEach(function(email) {
                    const preferred = email.is_preferred == 1 ? '<i data-feather="star" class="preferred"></i>' : '';
                    html += `
                        <div class="contact-detail-item">
                            <a href="mailto:${email.email}">${email.email}</a>
                            <span class="label">${email.label}</span>
                            ${preferred}
                        </div>
                    `;
                });
            } else {
                html += '<p class="no-data">No email addresses</p>';
            }

            html += `
                    </div>
                </div>

                <div class="contact-detail-section">
                    <h3><i data-feather="phone"></i> Phone Numbers</h3>
                    <div class="contact-detail-items">
            `;

            if (contact.phones && contact.phones.length > 0) {
                contact.phones.forEach(function(phone) {
                    const preferred = phone.is_preferred == 1 ? '<i data-feather="star" class="preferred"></i>' : '';
                    html += `
                        <div class="contact-detail-item">
                            <a href="tel:${phone.phone_number}">${phone.phone_number}</a>
                            <span class="label">${phone.label}</span>
                            ${preferred}
                        </div>
                    `;
                });
            } else {
                html += '<p class="no-data">No phone numbers</p>';
            }

            html += `
                    </div>
                </div>

                <div class="contact-detail-section">
                    <h3><i data-feather="share-2"></i> Social Profiles</h3>
                    <div class="contact-detail-socials">
            `;

            if (contact.socials && contact.socials.length > 0) {
                contact.socials.forEach(function(social) {
                    let icon = 'link';
                    if (social.platform === 'linkedin') icon = 'linkedin';
                    if (social.platform === 'twitter') icon = 'twitter';
                    if (social.platform === 'facebook') icon = 'facebook';

                    html += `
                        <a href="${social.profile_url}" target="_blank" class="social-link">
                            <i data-feather="${icon}"></i>
                        </a>
                    `;
                });
            } else {
                html += '<p class="no-data">No social profiles</p>';
            }

            html += `
                    </div>
                </div>

                <div class="contact-detail-actions">
                    <button class="button button-primary quick-action-email" data-email="${contact.primary_email || ''}">
                        <i data-feather="mail"></i> Email
                    </button>
                    <button class="button quick-action-call" data-phone="${contact.primary_phone || ''}">
                        <i data-feather="phone"></i> Call
                    </button>
                    <button class="button edit-contact-from-panel" data-id="${contact.id}">
                        <i data-feather="edit"></i> Edit
                    </button>
                    <button class="button delete-contact-from-panel" data-id="${contact.id}">
                        <i data-feather="trash-2"></i> Delete
                    </button>
                </div>
            `;

            if (contact.notes) {
                html += `
                    <div class="contact-detail-section">
                        <h3><i data-feather="file-text"></i> Notes</h3>
                        <div class="contact-notes">${contact.notes}</div>
                    </div>
                `;
            }

            return html;
        }
    };

    /**
     * Form Field Handlers
     */
    const FormFieldHandlers = {

        emailIndex: 1,
        cellPhoneIndex: 1,
        localPhoneIndex: 1,

        addEmailField: function() {
            const index = this.emailIndex++;
            const html = `
                <li class="email-field-group" data-index="${index}">
                    <div class="repeater-field">
                        <div class="field-row">
                            <div class="field-input">
                                <input type="email" name="emails[${index}][email]" placeholder="Email Address">
                            </div>
                            <div class="field-label">
                                <select name="emails[${index}][label]">
                                    <option value="work">Work</option>
                                    <option value="personal">Personal</option>
                                    <option value="assistant">Assistant</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="field-preferred">
                                <label>
                                    <input type="checkbox" name="emails[${index}][is_preferred]" value="1" class="email-preferred">
                                    <span>Preferred</span>
                                </label>
                            </div>
                            <div class="field-remove">
                                <button type="button" class="remove-email">
                                    <i data-feather="trash-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </li>
            `;

            $('#email-fields-container').append(html);
            this.updateRemoveButtons('#email-fields-container', '.remove-email');

            // Re-initialize feather icons
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        },

        addCellPhoneField: function() {
            const index = this.cellPhoneIndex++;
            const html = `
                <li class="cell-phone-field-group" data-index="${index}">
                    <div class="repeater-field">
                        <div class="field-row">
                            <div class="field-input">
                                <input type="tel" name="cell_phones[${index}][phone_number]" placeholder="Mobile Number">
                            </div>
                            <div class="field-label">
                                <select name="cell_phones[${index}][label]">
                                    <option value="mobile">Mobile</option>
                                    <option value="assistant_mobile">Assistant Mobile</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="field-preferred">
                                <label>
                                    <input type="checkbox" name="cell_phones[${index}][is_preferred]" value="1" class="cell-phone-preferred">
                                    <span>Preferred</span>
                                </label>
                            </div>
                            <div class="field-remove">
                                <button type="button" class="remove-cell-phone">
                                    <i data-feather="trash-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </li>
            `;

            $('#cell-phone-fields-container').append(html);
            this.updateRemoveButtons('#cell-phone-fields-container', '.remove-cell-phone');

            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        },

        addLocalPhoneField: function() {
            const index = this.localPhoneIndex++;
            const html = `
                <li class="local-phone-field-group" data-index="${index}">
                    <div class="repeater-field">
                        <div class="field-row">
                            <div class="field-input">
                                <input type="tel" name="local_phones[${index}][phone_number]" placeholder="Office Number">
                            </div>
                            <div class="field-label">
                                <select name="local_phones[${index}][label]">
                                    <option value="office">Office</option>
                                    <option value="home">Home</option>
                                    <option value="fax">Fax</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="field-preferred">
                                <label>
                                    <input type="checkbox" name="local_phones[${index}][is_preferred]" value="1" class="local-phone-preferred">
                                    <span>Preferred</span>
                                </label>
                            </div>
                            <div class="field-remove">
                                <button type="button" class="remove-local-phone">
                                    <i data-feather="trash-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </li>
            `;

            $('#local-phone-fields-container').append(html);
            this.updateRemoveButtons('#local-phone-fields-container', '.remove-local-phone');

            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        },

        updateRemoveButtons: function(containerSelector, buttonSelector) {
            const $container = $(containerSelector);
            const count = $container.find('li').length;

            if (count > 1) {
                $container.find(buttonSelector).show();
            } else {
                $container.find(buttonSelector).hide();
            }
        },

        handlePreferredCheckbox: function(checkbox, className) {
            if ($(checkbox).is(':checked')) {
                // Uncheck all other checkboxes of the same type
                $('.' + className).not(checkbox).prop('checked', false);
            }
        }
    };

    /**
     * Contacts Page Handler
     */
    const ContactsPage = {

        currentFilter: 'all',
        currentCompanyFilter: '',
        currentTagFilter: '',

        /**
         * Initialize
         */
        init: function() {
            console.log('ContactsPage.init() called - Phase 2 Complete');
            this.bindEvents();
            this.filterContacts('all');
            this.loadCompanies();
            this.loadCompanyFilterOptions();
            this.loadTagFilterOptions();
            console.log('ContactsPage.init() completed');
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            console.log('Binding events...');

            // Add Contact button
            $(document).on('click', '#add-contact-btn', function(e) {
                e.preventDefault();
                ContactsPage.resetContactForm();
                ModalHandler.open('add-contact-modal');
            });

            // Add Company button
            $(document).on('click', '#add-company-btn', function(e) {
                e.preventDefault();
                ContactsPage.resetCompanyForm();
                ModalHandler.open('add-company-modal');
            });

            // Close modal buttons
            $(document).on('click', '.wpc-modal-close', function(e) {
                e.preventDefault();
                const modalId = $(this).data('modal');
                ModalHandler.close(modalId);
            });

            // Close modal on outside click
            $(document).on('click', '.wpc-modal', function(e) {
                if ($(e.target).hasClass('wpc-modal')) {
                    ModalHandler.closeAll();
                }
            });

            // Close panel
            $(document).on('click', '.wpc-panel-close, .wpc-panel-overlay', function(e) {
                e.preventDefault();
                DetailPanel.close();
            });

            // ESC key to close
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    ModalHandler.closeAll();
                    DetailPanel.close();
                }
            });

            // Add email field
            $(document).on('click', '#add-email-btn', function(e) {
                e.preventDefault();
                FormFieldHandlers.addEmailField();
            });

            // Remove email field
            $(document).on('click', '.remove-email', function(e) {
                e.preventDefault();
                $(this).closest('li').remove();
                FormFieldHandlers.updateRemoveButtons('#email-fields-container', '.remove-email');
            });

            // Add cell phone field
            $(document).on('click', '#add-cell-phone-btn', function(e) {
                e.preventDefault();
                FormFieldHandlers.addCellPhoneField();
            });

            // Remove cell phone field
            $(document).on('click', '.remove-cell-phone', function(e) {
                e.preventDefault();
                $(this).closest('li').remove();
                FormFieldHandlers.updateRemoveButtons('#cell-phone-fields-container', '.remove-cell-phone');
            });

            // Add local phone field
            $(document).on('click', '#add-local-phone-btn', function(e) {
                e.preventDefault();
                FormFieldHandlers.addLocalPhoneField();
            });

            // Remove local phone field
            $(document).on('click', '.remove-local-phone', function(e) {
                e.preventDefault();
                $(this).closest('li').remove();
                FormFieldHandlers.updateRemoveButtons('#local-phone-fields-container', '.remove-local-phone');
            });

            // Handle preferred checkboxes
            $(document).on('change', '.email-preferred', function() {
                FormFieldHandlers.handlePreferredCheckbox(this, 'email-preferred');
            });

            $(document).on('change', '.cell-phone-preferred', function() {
                FormFieldHandlers.handlePreferredCheckbox(this, 'cell-phone-preferred');
            });

            $(document).on('change', '.local-phone-preferred', function() {
                FormFieldHandlers.handlePreferredCheckbox(this, 'local-phone-preferred');
            });

            // Contact form submit
            $(document).on('click', '#submit-contact-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                ContactsPage.submitContactForm($('#add-contact-form'));
                return false;
            });

            // Company form submit
            $(document).on('click', '#submit-company-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                ContactsPage.submitCompanyForm($('#add-company-form'));
                return false;
            });

            // Search
            $(document).on('input', '#contacts-search', function() {
                const query = $(this).val();
                ContactsPage.searchContacts(query);
            });

            // Filter tabs
            $(document).on('click', '.filter-tab', function() {
                $('.filter-tab').removeClass('active');
                $(this).addClass('active');
                const filter = $(this).data('filter');
                ContactsPage.filterContacts(filter);
            });

            // Company filter
            $(document).on('change', '#company-filter', function() {
                ContactsPage.currentCompanyFilter = $(this).val();
                ContactsPage.applyFilters();
            });

            // Tag filter
            $(document).on('change', '#tag-filter', function() {
                ContactsPage.currentTagFilter = $(this).val();
                ContactsPage.applyFilters();
            });

            // View contact detail
            $(document).on('click', '.view-contact', function(e) {
                e.preventDefault();
                const contactId = $(this).data('id');
                DetailPanel.open(contactId);
            });

            // Click on contact card to view detail
            $(document).on('click', '.contact-card', function(e) {
                // Don't open if clicking on action buttons
                if ($(e.target).closest('.contact-actions').length > 0) {
                    return;
                }

                const contactId = $(this).data('id');
                const cardType = $(this).data('type');

                if (cardType !== 'company' && contactId) {
                    DetailPanel.open(contactId);
                }
            });

            // Quick actions from detail panel
            $(document).on('click', '.quick-action-email', function(e) {
                e.preventDefault();
                const email = $(this).data('email');
                if (email) {
                    window.location.href = 'mailto:' + email;
                } else {
                    alert('No email address available');
                }
            });

            $(document).on('click', '.quick-action-call', function(e) {
                e.preventDefault();
                const phone = $(this).data('phone');
                if (phone) {
                    window.location.href = 'tel:' + phone;
                } else {
                    alert('No phone number available');
                }
            });

            // Edit contact from panel
            $(document).on('click', '.edit-contact-from-panel', function(e) {
                e.preventDefault();
                const contactId = $(this).data('id');
                DetailPanel.close();
                setTimeout(function() {
                    ContactsPage.editContact(contactId);
                }, 300);
            });

            // Delete contact from panel
            $(document).on('click', '.delete-contact-from-panel', function(e) {
                e.preventDefault();
                const contactId = $(this).data('id');
                if (confirm('Are you sure you want to delete this contact?')) {
                    ContactsPage.deleteContact(contactId);
                    DetailPanel.close();
                }
            });

            // Edit contact button
            $(document).on('click', '.edit-contact', function(e) {
                e.preventDefault();
                const contactId = $(this).data('id');
                ContactsPage.editContact(contactId);
            });

            // Delete contact button
            $(document).on('click', '.delete-contact', function(e) {
                e.preventDefault();
                const contactId = $(this).data('id');
                ContactsPage.deleteContact(contactId);
            });

            // Edit company button
            $(document).on('click', '.edit-company', function(e) {
                e.preventDefault();
                const companyId = $(this).data('id');
                ContactsPage.editCompany(companyId);
            });

            // Delete company button
            $(document).on('click', '.delete-company', function(e) {
                e.preventDefault();
                const companyId = $(this).data('id');
                ContactsPage.deleteCompany(companyId);
            });
        },

        /**
         * Reset contact form
         */
        resetContactForm: function() {
            const $form = $('#add-contact-form');
            $form[0].reset();
            $form.removeData('edit-id');
            $('#add-contact-modal .wpc-modal-header h2').text('Add Contact');
            $('#submit-contact-btn').text('Add Contact');

            // Reset to single email/phone fields
            $('#email-fields-container').find('li:not(:first)').remove();
            $('#cell-phone-fields-container').find('li:not(:first)').remove();
            $('#local-phone-fields-container').find('li:not(:first)').remove();

            // Reset preferred checkboxes
            $('.email-preferred').first().prop('checked', true);
            $('.cell-phone-preferred').first().prop('checked', true);
            $('.local-phone-preferred').first().prop('checked', false);

            FormFieldHandlers.updateRemoveButtons('#email-fields-container', '.remove-email');
            FormFieldHandlers.updateRemoveButtons('#cell-phone-fields-container', '.remove-cell-phone');
            FormFieldHandlers.updateRemoveButtons('#local-phone-fields-container', '.remove-local-phone');
        },

        /**
         * Reset company form
         */
        resetCompanyForm: function() {
            const $form = $('#add-company-form');
            $form[0].reset();
            $form.removeData('edit-id');
            $('#add-company-modal .wpc-modal-header h2').text('Add Company');
            $('#submit-company-btn').text('Add Company');
        },

        /**
         * Edit contact
         */
        editContact: function(contactId) {
            ContactsAjax.getContact(contactId, {
                success: function(contact) {
                    ContactsPage.populateContactForm(contact);
                    ModalHandler.open('add-contact-modal');
                },
                error: function(message) {
                    alert('Error loading contact: ' + message);
                }
            });
        },

        /**
         * Populate contact form with data
         */
        populateContactForm: function(contact) {
            const $form = $('#add-contact-form');

            // Store contact ID
            $form.data('edit-id', contact.id);

            // Change modal title and button
            $('#add-contact-modal .wpc-modal-header h2').text('Edit Contact');
            $('#submit-contact-btn').text('Update Contact');

            // Basic fields
            $('#contact-first-name').val(contact.first_name || '');
            $('#contact-last-name').val(contact.last_name || '');
            $('#contact-company').val(contact.company_id || '');
            $('#contact-role-select').val(contact.role || '');
            $('#contact-department').val(contact.department || '');
            $('#contact-linkedin').val(contact.linkedin || '');
            $('#contact-twitter').val(contact.twitter || '');
            $('#contact-facebook').val(contact.facebook || '');
            $('#contact-id-number').val(contact.contact_id_number || '');
            $('#contact-passport').val(contact.passport_number || '');
            $('#contact-tags').val(contact.tags_string || '');
            $('#contact-notes').val(contact.notes || '');

            // Populate emails
            $('#email-fields-container').empty();
            FormFieldHandlers.emailIndex = 0;
            if (contact.emails && contact.emails.length > 0) {
                contact.emails.forEach(function(email, index) {
                    if (index === 0) {
                        const html = `
                            <li class="email-field-group" data-index="${index}">
                                <div class="repeater-field">
                                    <div class="field-row">
                                        <div class="field-input">
                                            <input type="email" name="emails[${index}][email]" value="${email.email}" placeholder="Email Address">
                                        </div>
                                        <div class="field-label">
                                            <select name="emails[${index}][label]">
                                                <option value="work" ${email.label === 'work' ? 'selected' : ''}>Work</option>
                                                <option value="personal" ${email.label === 'personal' ? 'selected' : ''}>Personal</option>
                                                <option value="assistant" ${email.label === 'assistant' ? 'selected' : ''}>Assistant</option>
                                                <option value="other" ${email.label === 'other' ? 'selected' : ''}>Other</option>
                                            </select>
                                        </div>
                                        <div class="field-preferred">
                                            <label>
                                                <input type="checkbox" name="emails[${index}][is_preferred]" value="1" class="email-preferred" ${email.is_preferred == 1 ? 'checked' : ''}>
                                                <span>Preferred</span>
                                            </label>
                                        </div>
                                        <div class="field-remove">
                                            <button type="button" class="remove-email" style="display:none;">
                                                <i data-feather="trash-2"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        `;
                        $('#email-fields-container').append(html);
                        FormFieldHandlers.emailIndex = 1;
                    } else {
                        FormFieldHandlers.emailIndex = index;
                        FormFieldHandlers.addEmailField();
                        const $newField = $('#email-fields-container li:last');
                        $newField.find('input[type="email"]').val(email.email);
                        $newField.find('select').val(email.label);
                        $newField.find('.email-preferred').prop('checked', email.is_preferred == 1);
                        FormFieldHandlers.emailIndex++;
                    }
                });
            }

            // Similar for cell phones and local phones
            $('#cell-phone-fields-container').empty();
            FormFieldHandlers.cellPhoneIndex = 0;
            const cellPhones = contact.phones ? contact.phones.filter(p => p.phone_type === 'cell') : [];
            if (cellPhones.length > 0) {
                cellPhones.forEach(function(phone, index) {
                    if (index === 0) {
                        const html = `
                            <li class="cell-phone-field-group" data-index="${index}">
                                <div class="repeater-field">
                                    <div class="field-row">
                                        <div class="field-input">
                                            <input type="tel" name="cell_phones[${index}][phone_number]" value="${phone.phone_number}" placeholder="Mobile Number">
                                        </div>
                                        <div class="field-label">
                                            <select name="cell_phones[${index}][label]">
                                                <option value="mobile" ${phone.label === 'mobile' ? 'selected' : ''}>Mobile</option>
                                                <option value="assistant_mobile" ${phone.label === 'assistant_mobile' ? 'selected' : ''}>Assistant Mobile</option>
                                                <option value="other" ${phone.label === 'other' ? 'selected' : ''}>Other</option>
                                            </select>
                                        </div>
                                        <div class="field-preferred">
                                            <label>
                                                <input type="checkbox" name="cell_phones[${index}][is_preferred]" value="1" class="cell-phone-preferred" ${phone.is_preferred == 1 ? 'checked' : ''}>
                                                <span>Preferred</span>
                                            </label>
                                        </div>
                                        <div class="field-remove">
                                            <button type="button" class="remove-cell-phone" style="display:none;">
                                                <i data-feather="trash-2"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        `;
                        $('#cell-phone-fields-container').append(html);
                        FormFieldHandlers.cellPhoneIndex = 1;
                    } else {
                        FormFieldHandlers.cellPhoneIndex = index;
                        FormFieldHandlers.addCellPhoneField();
                        const $newField = $('#cell-phone-fields-container li:last');
                        $newField.find('input[type="tel"]').val(phone.phone_number);
                        $newField.find('select').val(phone.label);
                        $newField.find('.cell-phone-preferred').prop('checked', phone.is_preferred == 1);
                        FormFieldHandlers.cellPhoneIndex++;
                    }
                });
            }

            $('#local-phone-fields-container').empty();
            FormFieldHandlers.localPhoneIndex = 0;
            const localPhones = contact.phones ? contact.phones.filter(p => p.phone_type === 'local') : [];
            if (localPhones.length > 0) {
                localPhones.forEach(function(phone, index) {
                    if (index === 0) {
                        const html = `
                            <li class="local-phone-field-group" data-index="${index}">
                                <div class="repeater-field">
                                    <div class="field-row">
                                        <div class="field-input">
                                            <input type="tel" name="local_phones[${index}][phone_number]" value="${phone.phone_number}" placeholder="Office Number">
                                        </div>
                                        <div class="field-label">
                                            <select name="local_phones[${index}][label]">
                                                <option value="office" ${phone.label === 'office' ? 'selected' : ''}>Office</option>
                                                <option value="home" ${phone.label === 'home' ? 'selected' : ''}>Home</option>
                                                <option value="fax" ${phone.label === 'fax' ? 'selected' : ''}>Fax</option>
                                                <option value="other" ${phone.label === 'other' ? 'selected' : ''}>Other</option>
                                            </select>
                                        </div>
                                        <div class="field-preferred">
                                            <label>
                                                <input type="checkbox" name="local_phones[${index}][is_preferred]" value="1" class="local-phone-preferred" ${phone.is_preferred == 1 ? 'checked' : ''}>
                                                <span>Preferred</span>
                                            </label>
                                        </div>
                                        <div class="field-remove">
                                            <button type="button" class="remove-local-phone" style="display:none;">
                                                <i data-feather="trash-2"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        `;
                        $('#local-phone-fields-container').append(html);
                        FormFieldHandlers.localPhoneIndex = 1;
                    } else {
                        FormFieldHandlers.localPhoneIndex = index;
                        FormFieldHandlers.addLocalPhoneField();
                        const $newField = $('#local-phone-fields-container li:last');
                        $newField.find('input[type="tel"]').val(phone.phone_number);
                        $newField.find('select').val(phone.label);
                        $newField.find('.local-phone-preferred').prop('checked', phone.is_preferred == 1);
                        FormFieldHandlers.localPhoneIndex++;
                    }
                });
            }

            FormFieldHandlers.updateRemoveButtons('#email-fields-container', '.remove-email');
            FormFieldHandlers.updateRemoveButtons('#cell-phone-fields-container', '.remove-cell-phone');
            FormFieldHandlers.updateRemoveButtons('#local-phone-fields-container', '.remove-local-phone');

            // Re-initialize feather icons
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        },

        /**
         * Delete contact
         */
        deleteContact: function(contactId) {
            if (!confirm('Are you sure you want to delete this contact?')) {
                return;
            }

            ContactsAjax.deleteContact(contactId, {
                success: function() {
                    alert('Contact deleted successfully!');
                    ContactsPage.filterContacts(ContactsPage.currentFilter);
                },
                error: function(message) {
                    alert('Error deleting contact: ' + message);
                }
            });
        },

        /**
         * Edit company
         */
        editCompany: function(companyId) {
            ContactsAjax.getCompany(companyId, {
                success: function(company) {
                    $('#company-name').val(company.company_name || '');
                    $('#company-type').val(company.company_type || 'client');
                    $('#company-email').val(company.company_email || '');
                    $('#company-phone').val(company.company_phone || '');
                    $('#company-website').val(company.company_website || '');
                    $('#company-logo').val(company.company_logo_url || '');
                    $('#company-notes').val(company.company_notes || '');

                    $('#add-company-form').data('edit-id', companyId);
                    $('#add-company-modal .wpc-modal-header h2').text('Edit Company');
                    $('#submit-company-btn').text('Update Company');

                    ModalHandler.open('add-company-modal');
                },
                error: function(message) {
                    alert('Error loading company: ' + message);
                }
            });
        },

        /**
         * Delete company
         */
        deleteCompany: function(companyId) {
            if (!confirm('Are you sure you want to delete this company?')) {
                return;
            }

            ContactsAjax.deleteCompany(companyId, {
                success: function() {
                    alert('Company deleted successfully!');
                    ContactsPage.filterContacts(ContactsPage.currentFilter);
                    ContactsPage.loadCompanies();
                },
                error: function(message) {
                    alert('Error deleting company: ' + message);
                }
            });
        },

        /**
         * Submit contact form
         */
        submitContactForm: function($form) {
            console.log('=== Submitting Contact Form ===');

            const editId = $form.data('edit-id');
            const isEdit = editId ? true : false;

            console.log('Form mode:', isEdit ? 'EDIT (ID: ' + editId + ')' : 'CREATE');

            const formData = {
                first_name: $form.find('[name="first_name"]').val(),
                last_name: $form.find('[name="last_name"]').val(),
                department: $form.find('[name="department"]').val() || '',
                notes: $form.find('[name="notes"]').val() || ''
            };

            // Company ID
            const companyId = $form.find('[name="company_id"]').val();
            if (companyId && companyId !== '') {
                formData.company_id = companyId;
            }

            // Role
            const role = $form.find('[name="role"]').val();
            if (role && role !== '') {
                formData.role = role;
            }

            // Collect emails
            const emails = [];
            $form.find('.email-field-group').each(function() {
                const email = $(this).find('input[type="email"]').val();
                if (email) {
                    emails.push({
                        email: email,
                        label: $(this).find('select[name*="[label]"]').val(),
                        is_preferred: $(this).find('.email-preferred').is(':checked') ? 1 : 0
                    });
                }
            });
            if (emails.length > 0) {
                formData.emails = emails;
            }

            // Collect cell phones
            const cellPhones = [];
            $form.find('.cell-phone-field-group').each(function() {
                const phone = $(this).find('input[type="tel"]').val();
                if (phone) {
                    cellPhones.push({
                        phone_number: phone,
                        phone_type: 'cell',
                        label: $(this).find('select[name*="[label]"]').val(),
                        is_preferred: $(this).find('.cell-phone-preferred').is(':checked') ? 1 : 0
                    });
                }
            });

            // Collect local phones
            const localPhones = [];
            $form.find('.local-phone-field-group').each(function() {
                const phone = $(this).find('input[type="tel"]').val();
                if (phone) {
                    localPhones.push({
                        phone_number: phone,
                        phone_type: 'local',
                        label: $(this).find('select[name*="[label]"]').val(),
                        is_preferred: $(this).find('.local-phone-preferred').is(':checked') ? 1 : 0
                    });
                }
            });

            // Combine phones
            const allPhones = cellPhones.concat(localPhones);
            if (allPhones.length > 0) {
                formData.phones = allPhones;
            }

            // Social profiles
            const socials = [];
            const linkedin = $form.find('[name="linkedin"]').val();
            const twitter = $form.find('[name="twitter"]').val();
            const facebook = $form.find('[name="facebook"]').val();

            if (linkedin) socials.push({ platform: 'linkedin', profile_url: linkedin });
            if (twitter) socials.push({ platform: 'twitter', profile_url: twitter });
            if (facebook) socials.push({ platform: 'facebook', profile_url: facebook });

            if (socials.length > 0) {
                formData.socials = socials;
            }

            // ID and Passport
            const idNumber = $form.find('[name="contact_id_number"]').val();
            const passport = $form.find('[name="passport_number"]').val();
            if (idNumber) formData.contact_id_number = idNumber;
            if (passport) formData.passport_number = passport;

            // Tags
            const tags = $form.find('[name="tags"]').val();
            if (tags) {
                formData.tags = tags.split(',').map(tag => tag.trim()).filter(tag => tag);
            }

            console.log('Final form data to be sent:', formData);

            const submitBtn = $('#submit-contact-btn');
            const originalText = submitBtn.text();
            const successMessage = isEdit ? 'Contact updated successfully!' : 'Contact added successfully!';
            const loadingText = isEdit ? 'Updating...' : 'Adding...';

            if (isEdit) {
                ContactsAjax.updateContact(editId, formData, {
                    beforeSend: function() {
                        submitBtn.prop('disabled', true).text(loadingText);
                    },
                    success: function(data) {
                        alert(successMessage);
                        ContactsPage.resetContactForm();
                        ModalHandler.close('add-contact-modal');
                        ContactsPage.filterContacts(ContactsPage.currentFilter);
                    },
                    error: function(message) {
                        alert('Error: ' + message);
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).text(originalText);
                    }
                });
            } else {
                ContactsAjax.createContact(formData, {
                    beforeSend: function() {
                        submitBtn.prop('disabled', true).text(loadingText);
                    },
                    success: function(data) {
                        alert(successMessage);
                        ContactsPage.resetContactForm();
                        ModalHandler.close('add-contact-modal');
                        ContactsPage.filterContacts(ContactsPage.currentFilter);
                    },
                    error: function(message) {
                        alert('Error: ' + message);
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).text(originalText);
                    }
                });
            }
        },

        /**
         * Submit company form
         */
        submitCompanyForm: function($form) {
            console.log('=== Submitting Company Form ===');

            const editId = $form.data('edit-id');
            const isEdit = editId ? true : false;

            const formData = {
                company_name: $form.find('[name="name"]').val(),
                company_type: $form.find('[name="company_type"]').val(),
                company_website: $form.find('[name="website"]').val(),
                company_phone: $form.find('[name="phone"]').val(),
                company_email: $form.find('[name="email"]').val(),
                company_logo_url: $form.find('[name="company_logo_url"]').val(),
                company_notes: $form.find('[name="notes"]').val()
            };

            console.log('Company form data:', formData);

            const submitBtn = $('#submit-company-btn');
            const originalText = submitBtn.text();
            const successMessage = isEdit ? 'Company updated successfully!' : 'Company added successfully!';
            const loadingText = isEdit ? 'Updating...' : 'Adding...';

            if (isEdit) {
                ContactsAjax.updateCompany(editId, formData, {
                    beforeSend: function() {
                        submitBtn.prop('disabled', true).text(loadingText);
                    },
                    success: function(data) {
                        alert(successMessage);
                        ContactsPage.resetCompanyForm();
                        ModalHandler.close('add-company-modal');
                        ContactsPage.filterContacts(ContactsPage.currentFilter);
                        ContactsPage.loadCompanies();
                    },
                    error: function(message) {
                        alert('Error: ' + message);
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).text(originalText);
                    }
                });
            } else {
                ContactsAjax.createCompany(formData, {
                    beforeSend: function() {
                        submitBtn.prop('disabled', true).text(loadingText);
                    },
                    success: function(data) {
                        alert(successMessage);
                        ContactsPage.resetCompanyForm();
                        ModalHandler.close('add-company-modal');
                        ContactsPage.filterContacts(ContactsPage.currentFilter);
                        ContactsPage.loadCompanies();
                    },
                    error: function(message) {
                        alert('Error: ' + message);
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).text(originalText);
                    }
                });
            }
        },

        /**
         * Load companies for dropdown
         */
        loadCompanies: function() {
            ContactsAjax.listCompanies({}, {
                success: function(data) {
                    const companies = data.companies || [];
                    const $select = $('#contact-company');
                    $select.find('option:not(:first)').remove();
                    companies.forEach(function(company) {
                        $select.append('<option value="' + company.id + '">' + company.company_name + '</option>');
                    });
                }
            });
        },

        /**
         * Load company filter options
         */
        loadCompanyFilterOptions: function() {
            ContactsAjax.listCompanies({}, {
                success: function(data) {
                    const companies = data.companies || [];
                    const $select = $('#company-filter');
                    companies.forEach(function(company) {
                        $select.append('<option value="' + company.id + '">' + company.company_name + '</option>');
                    });
                }
            });
        },

        /**
         * Load tag filter options (placeholder for now)
         */
        loadTagFilterOptions: function() {
            // Placeholder - would load from WordPress taxonomy
        },

        /**
         * Apply filters
         */
        applyFilters: function() {
            const params = {
                company_id: this.currentCompanyFilter,
                tag: this.currentTagFilter
            };

            ContactsAjax.listContacts(params, {
                success: function(data) {
                    ContactsPage.renderContacts(data.contacts || []);
                },
                error: function(message) {
                    $('#contacts-grid').html('<p>Error loading contacts: ' + message + '</p>');
                }
            });
        },

        /**
         * Render contacts
         */
        renderContacts: function(contacts) {
            const $grid = $('#contacts-grid');
            $grid.empty();

            if (contacts.length === 0) {
                $grid.html('<p class="no-contacts">No contacts found. Click "Add Contact" to get started.</p>');
                return;
            }

            contacts.forEach(function(contact) {
                const html = ContactsPage.renderContactCard(contact);
                $grid.append(html);
            });

            // Re-initialize feather icons
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        },

        /**
         * Render contact card
         */
        renderContactCard: function(contact) {
            const name = contact.first_name + ' ' + contact.last_name;
            const email = contact.primary_email || 'No email';
            const phone = contact.primary_phone || 'No phone';
            const role = contact.role || '';
            const company = contact.company_name || '';

            return `
                <div class="contact-card" data-id="${contact.id}" style="cursor: pointer;">
                    <div class="contact-avatar">
                        <i data-feather="user"></i>
                    </div>
                    <div class="contact-info">
                        <h3>${name}</h3>
                        ${role ? '<p class="contact-role">' + role + '</p>' : ''}
                        ${company ? '<p class="contact-company">' + company + '</p>' : ''}
                        <p class="contact-email"><i data-feather="mail"></i> ${email}</p>
                        <p class="contact-phone"><i data-feather="phone"></i> ${phone}</p>
                    </div>
                    <div class="contact-actions">
                        <button class="edit-contact" data-id="${contact.id}">
                            <i data-feather="edit"></i>
                        </button>
                        <button class="delete-contact" data-id="${contact.id}">
                            <i data-feather="trash-2"></i>
                        </button>
                    </div>
                </div>
            `;
        },

        /**
         * Render company card
         */
        renderCompanyCard: function(company) {
            const name = company.company_name || 'Unnamed Company';
            const email = company.company_email || 'No email';
            const phone = company.company_phone || 'No phone';
            const website = company.company_website || '';
            const type = company.company_type || 'client';

            return `
                <div class="contact-card company-card" data-id="${company.id}" data-type="company">
                    <div class="contact-avatar">
                        <i data-feather="briefcase"></i>
                    </div>
                    <div class="contact-info">
                        <h3>${name}</h3>
                        <p class="contact-role">${type.charAt(0).toUpperCase() + type.slice(1)}</p>
                        ${website ? '<p class="contact-company"><i data-feather="globe"></i> ' + website + '</p>' : ''}
                        <p class="contact-email"><i data-feather="mail"></i> ${email}</p>
                        <p class="contact-phone"><i data-feather="phone"></i> ${phone}</p>
                    </div>
                    <div class="contact-actions">
                        <button class="edit-company" data-id="${company.id}">
                            <i data-feather="edit"></i>
                        </button>
                        <button class="delete-company" data-id="${company.id}">
                            <i data-feather="trash-2"></i>
                        </button>
                    </div>
                </div>
            `;
        },

        /**
         * Search contacts
         */
        searchContacts: function(query) {
            if (!query) {
                this.filterContacts(this.currentFilter);
                return;
            }

            ContactsAjax.searchContacts(query, {
                success: function(data) {
                    ContactsPage.renderContacts(data.contacts || []);
                }
            });
        },

        /**
         * Filter contacts and companies
         */
        filterContacts: function(filter) {
            console.log('Filtering by: ' + filter);
            this.currentFilter = filter;

            const $grid = $('#contacts-grid');
            $grid.html('<div class="loading">Loading...</div>');

            if (filter === 'all') {
                let contactsData = [];
                let companiesData = [];
                let loadedCount = 0;

                ContactsAjax.listContacts({}, {
                    success: function(data) {
                        contactsData = data.contacts || [];
                        loadedCount++;
                        if (loadedCount === 2) {
                            ContactsPage.renderAll(contactsData, companiesData);
                        }
                    }
                });

                ContactsAjax.listCompanies({}, {
                    success: function(data) {
                        companiesData = data.companies || [];
                        loadedCount++;
                        if (loadedCount === 2) {
                            ContactsPage.renderAll(contactsData, companiesData);
                        }
                    }
                });
            } else if (filter === 'contacts') {
                ContactsAjax.listContacts({}, {
                    success: function(data) {
                        ContactsPage.renderContacts(data.contacts || []);
                    },
                    error: function(message) {
                        $grid.html('<p>Error loading contacts: ' + message + '</p>');
                    }
                });
            } else if (filter === 'companies') {
                ContactsAjax.listCompanies({}, {
                    success: function(data) {
                        ContactsPage.renderCompanies(data.companies || []);
                    },
                    error: function(message) {
                        $grid.html('<p>Error loading companies: ' + message + '</p>');
                    }
                });
            }
        },

        /**
         * Render all (contacts and companies)
         */
        renderAll: function(contacts, companies) {
            const $grid = $('#contacts-grid');
            $grid.empty();

            if (contacts.length === 0 && companies.length === 0) {
                $grid.html('<p class="no-contacts">No contacts or companies found. Click "Add Contact" or "Add Company" to get started.</p>');
                return;
            }

            // Render companies first
            companies.forEach(function(company) {
                const html = ContactsPage.renderCompanyCard(company);
                $grid.append(html);
            });

            // Then render contacts
            contacts.forEach(function(contact) {
                const html = ContactsPage.renderContactCard(contact);
                $grid.append(html);
            });

            // Re-initialize feather icons
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        },

        /**
         * Render companies only
         */
        renderCompanies: function(companies) {
            const $grid = $('#contacts-grid');
            $grid.empty();

            if (companies.length === 0) {
                $grid.html('<p class="no-contacts">No companies found. Click "Add Company" to get started.</p>');
                return;
            }

            companies.forEach(function(company) {
                const html = ContactsPage.renderCompanyCard(company);
                $grid.append(html);
            });

            // Re-initialize feather icons
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        }
    };

    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        try {
            console.log('🎉 VERSION 2.0.0 - PHASE 2 COMPLETE! 🎉');
            console.log('=== wProject Contacts Pro Initialization ===');
            console.log('jQuery version:', $.fn.jquery);
            console.log('wpContactsPro defined:', typeof wpContactsPro !== 'undefined');

            // Check if wpContactsPro is defined
            if (typeof wpContactsPro === 'undefined') {
                console.error('CRITICAL ERROR: wpContactsPro is not defined!');
                return;
            }

            console.log('wpContactsPro config:', wpContactsPro);

            // Check for contacts page element
            const $contactsPage = $('.wproject-contacts-page');
            console.log('Contacts page element found:', $contactsPage.length);

            if ($contactsPage.length) {
                console.log('✓ Contacts page detected - initializing...');
                ContactsPage.init();
                console.log('✓ ContactsPage initialized successfully');
            } else {
                console.log('⚠ Contacts page element not found - skipping initialization');
            }

            console.log('=== Initialization Complete ===');
        } catch (error) {
            console.error('❌ FATAL ERROR during initialization:', error);
        }
    });

})(jQuery);
