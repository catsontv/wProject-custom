/**
 * wProject Contacts Pro - Frontend JavaScript
 * Phase 1: Basic AJAX functionality
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
            console.log('AJAX URL:', wpContactsPro.ajaxurl);
            console.log('Nonce:', wpContactsPro.nonce);

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
                    console.error('AJAX Error Details:');
                    console.error('Status:', status);
                    console.error('Error:', error);
                    console.error('Response Text:', xhr.responseText);
                    console.error('Response Status:', xhr.status);
                    console.error('Full XHR:', xhr);

                    if (callbacks.error) {
                        let errorMsg = 'Request failed: ' + error;
                        if (xhr.responseText) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                errorMsg = response.message || response.data?.message || errorMsg;
                            } catch(e) {
                                errorMsg += ' (Server response: ' + xhr.responseText.substring(0, 200) + ')';
                            }
                        }
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
        
        /**
         * Create company
         */
        createCompany: function(data, callbacks) {
            this.request('contacts_pro_create_company', data, callbacks);
        },
        
        /**
         * Update company
         */
        updateCompany: function(id, data, callbacks) {
            data.id = id;
            this.request('contacts_pro_update_company', data, callbacks);
        },
        
        /**
         * Delete company
         */
        deleteCompany: function(id, callbacks) {
            this.request('contacts_pro_delete_company', { id: id }, callbacks);
        },
        
        /**
         * Get company
         */
        getCompany: function(id, callbacks) {
            this.request('contacts_pro_get_company', { id: id }, callbacks);
        },
        
        /**
         * List companies
         */
        listCompanies: function(params, callbacks) {
            this.request('contacts_pro_list_companies', params, callbacks);
        },
        
        /**
         * Create contact
         */
        createContact: function(data, callbacks) {
            this.request('contacts_pro_create_contact', data, callbacks);
        },
        
        /**
         * Update contact
         */
        updateContact: function(id, data, callbacks) {
            data.id = id;
            this.request('contacts_pro_update_contact', data, callbacks);
        },
        
        /**
         * Delete contact
         */
        deleteContact: function(id, callbacks) {
            this.request('contacts_pro_delete_contact', { id: id }, callbacks);
        },
        
        /**
         * Get contact
         */
        getContact: function(id, callbacks) {
            this.request('contacts_pro_get_contact', { id: id }, callbacks);
        },
        
        /**
         * List contacts
         */
        listContacts: function(params, callbacks) {
            this.request('contacts_pro_list_contacts', params, callbacks);
        },
        
        /**
         * Search contacts
         */
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
     * Contacts Page Handler
     */
    const ContactsPage = {

        /**
         * Initialize
         */
        init: function() {
            console.log('ContactsPage.init() called');
            this.bindEvents();
            this.loadContacts();
            this.loadCompanies();
            console.log('ContactsPage.init() completed');
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            console.log('Binding events...');

            // Add Contact button
            $(document).on('click', '#add-contact-btn', function(e) {
                console.log('Add Contact button clicked');
                e.preventDefault();

                // Reset form and modal to create mode
                $('#add-contact-form')[0].reset();
                $('#add-contact-form').removeData('edit-id');
                $('#add-contact-modal .wpc-modal-header h2').text('Add Contact');
                $('#submit-contact-btn').text('Add Contact');

                ModalHandler.open('add-contact-modal');
            });

            // Add Company button
            $(document).on('click', '#add-company-btn', function(e) {
                e.preventDefault();
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

            // Contact form submit button (BYPASS form submission)
            $(document).on('click', '#submit-contact-btn', function(e) {
                console.log('!!! CONTACT SUBMIT BUTTON CLICKED !!!');
                e.preventDefault();
                e.stopPropagation();
                ContactsPage.submitContactForm($('#add-contact-form'));
                return false;
            });

            // Company form submit button (BYPASS form submission)
            $(document).on('click', '#submit-company-btn', function(e) {
                console.log('!!! COMPANY SUBMIT BUTTON CLICKED !!!');
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

            // Edit contact button
            $(document).on('click', '.edit-contact', function(e) {
                e.preventDefault();
                const contactId = $(this).data('id');
                console.log('Edit contact clicked:', contactId);

                // Load contact data and populate modal
                ContactsAjax.getContact(contactId, {
                    success: function(contact) {
                        // Populate form fields
                        $('#contact-first-name').val(contact.first_name);
                        $('#contact-last-name').val(contact.last_name);
                        $('#contact-email').val(contact.primary_email || '');
                        $('#contact-phone').val(contact.primary_phone || '');
                        $('#contact-company').val(contact.company_id);
                        $('#contact-position').val(contact.role || '');

                        // Store contact ID in form for update
                        $('#add-contact-form').data('edit-id', contactId);

                        // Change modal title and button text
                        $('#add-contact-modal .wpc-modal-header h2').text('Edit Contact');
                        $('#submit-contact-btn').text('Update Contact');

                        // Open modal
                        ModalHandler.open('add-contact-modal');
                    },
                    error: function(message) {
                        alert('Error loading contact: ' + message);
                    }
                });
            });

            // Delete contact button
            $(document).on('click', '.delete-contact', function(e) {
                e.preventDefault();
                const contactId = $(this).data('id');
                console.log('Delete contact clicked:', contactId);

                if (confirm('Are you sure you want to delete this contact?')) {
                    ContactsAjax.deleteContact(contactId, {
                        beforeSend: function() {
                            console.log('Deleting contact:', contactId);
                        },
                        success: function(data) {
                            alert('Contact deleted successfully!');
                            ContactsPage.loadContacts();
                        },
                        error: function(message) {
                            alert('Error deleting contact: ' + message);
                        }
                    });
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

            const email = $form.find('[name="email"]').val();
            const phone = $form.find('[name="phone"]').val();
            const position = $form.find('[name="position"]').val();

            console.log('Form values:', {
                email: email,
                phone: phone,
                position: position,
                first_name: $form.find('[name="first_name"]').val(),
                last_name: $form.find('[name="last_name"]').val(),
                company_id: $form.find('[name="company_id"]').val()
            });

            const formData = {
                first_name: $form.find('[name="first_name"]').val(),
                last_name: $form.find('[name="last_name"]').val(),
                company_id: $form.find('[name="company_id"]').val(),
                role: position // PHP expects 'role' not 'position'
            };

            if (isEdit) {
                formData.id = editId;
            }

            // PHP expects emails as an array
            if (email) {
                formData.emails = [{
                    email: email,
                    label: 'work',
                    is_preferred: 1
                }];
            }

            // PHP expects phones as an array
            if (phone) {
                formData.phones = [{
                    phone_number: phone,
                    phone_type: 'work',
                    label: 'work',
                    is_preferred: 1
                }];
            }

            console.log('Final form data to be sent:', formData);

            const submitBtn = $('#submit-contact-btn');
            const originalText = submitBtn.text();

            const successMessage = isEdit ? 'Contact updated successfully!' : 'Contact added successfully!';
            const loadingText = isEdit ? 'Updating...' : 'Adding...';

            // Call the appropriate method directly to preserve context
            if (isEdit) {
                ContactsAjax.updateContact(editId, formData, {
                    beforeSend: function() {
                        submitBtn.prop('disabled', true).text(loadingText);
                    },
                    success: function(data) {
                        alert(successMessage);
                        $form[0].reset();
                        $form.removeData('edit-id');

                        // Reset modal to create mode
                        $('#add-contact-modal .wpc-modal-header h2').text('Add Contact');
                        $('#submit-contact-btn').text('Add Contact');

                        ModalHandler.close('add-contact-modal');
                        ContactsPage.loadContacts();
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
                        $form[0].reset();
                        $form.removeData('edit-id');

                        // Reset modal to create mode
                        $('#add-contact-modal .wpc-modal-header h2').text('Add Contact');
                        $('#submit-contact-btn').text('Add Contact');

                        ModalHandler.close('add-contact-modal');
                        ContactsPage.loadContacts();
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

            const formData = {
                company_name: $form.find('[name="name"]').val(),
                company_website: $form.find('[name="website"]').val(),
                company_phone: $form.find('[name="phone"]').val(),
                company_email: $form.find('[name="email"]').val(),
                company_notes: $form.find('[name="address"]').val()
            };

            console.log('Company form data to be sent:', formData);

            const submitBtn = $('#submit-company-btn');
            const originalText = submitBtn.text();

            ContactsAjax.createCompany(formData, {
                beforeSend: function() {
                    submitBtn.prop('disabled', true).text('Adding...');
                },
                success: function(data) {
                    alert('Company added successfully!');
                    $form[0].reset();
                    ModalHandler.close('add-company-modal');
                    ContactsPage.loadContacts();
                    ContactsPage.loadCompanies();
                },
                error: function(message) {
                    alert('Error: ' + message);
                },
                complete: function() {
                    submitBtn.prop('disabled', false).text(originalText);
                }
            });
        },

        /**
         * Load contacts
         */
        loadContacts: function() {
            ContactsAjax.listContacts({}, {
                success: function(data) {
                    ContactsPage.renderContacts(data.contacts || []);
                },
                error: function(message) {
                    $('#contacts-grid').html('<p>Error loading contacts: ' + message + '</p>');
                }
            });
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

            // Re-initialize feather icons if available
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
                <div class="contact-card" data-id="${contact.id}">
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
         * Search contacts
         */
        searchContacts: function(query) {
            if (!query) {
                this.loadContacts();
                return;
            }

            ContactsAjax.searchContacts(query, {
                success: function(data) {
                    ContactsPage.renderContacts(data.contacts || []);
                }
            });
        },

        /**
         * Filter contacts
         */
        filterContacts: function(filter) {
            // This will be implemented in a future phase
            console.log('Filtering by: ' + filter);
        }
    };

    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        try {
            console.log('🎉 VERSION 1.0.7 - CONTACT CREATION FIX! 🎉');
            console.log('=== wProject Contacts Pro Initialization ===');
            console.log('jQuery version:', $.fn.jquery);
            console.log('wpContactsPro defined:', typeof wpContactsPro !== 'undefined');

            // Check if wpContactsPro is defined
            if (typeof wpContactsPro === 'undefined') {
                console.error('CRITICAL ERROR: wpContactsPro is not defined! Script localization failed.');
                console.error('This means the AJAX URL and nonce are not available.');
                return;
            }

            console.log('wpContactsPro config:', wpContactsPro);
            console.log('AJAX URL:', wpContactsPro.ajaxurl);
            console.log('Nonce:', wpContactsPro.nonce);

            // Check for contacts page element
            const $contactsPage = $('.wproject-contacts-page');
            console.log('Contacts page element found:', $contactsPage.length);

            if ($contactsPage.length) {
                console.log('✓ Contacts page detected - initializing...');
                ContactsPage.init();
                console.log('✓ ContactsPage initialized successfully');
            } else {
                console.log('⚠ Contacts page element not found - skipping initialization');
                console.log('Looking for: .wproject-contacts-page');
            }

            console.log('=== Initialization Complete ===');
        } catch (error) {
            console.error('❌ FATAL ERROR during initialization:', error);
            console.error('Error name:', error.name);
            console.error('Error message:', error.message);
            console.error('Stack trace:', error.stack);
        }
    });
    
})(jQuery);
