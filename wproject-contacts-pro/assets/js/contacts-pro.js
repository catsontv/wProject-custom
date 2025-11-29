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
                    if (response.success) {
                        if (callbacks.success) {
                            callbacks.success(response.data);
                        }
                    } else {
                        if (callbacks.error) {
                            callbacks.error(response.data ? response.data.message : 'Unknown error');
                        }
                    }
                },
                error: function(xhr, status, error) {
                    if (callbacks.error) {
                        callbacks.error('Request failed: ' + error);
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

            // Add Contact form submission
            $(document).on('submit', '#add-contact-form', function(e) {
                e.preventDefault();
                ContactsPage.submitContactForm($(this));
            });

            // Add Company form submission
            $(document).on('submit', '#add-company-form', function(e) {
                e.preventDefault();
                ContactsPage.submitCompanyForm($(this));
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
        },

        /**
         * Submit contact form
         */
        submitContactForm: function($form) {
            const email = $form.find('[name="email"]').val();
            const phone = $form.find('[name="phone"]').val();
            const position = $form.find('[name="position"]').val();

            const formData = {
                first_name: $form.find('[name="first_name"]').val(),
                last_name: $form.find('[name="last_name"]').val(),
                company_id: $form.find('[name="company_id"]').val(),
                role: position // PHP expects 'role' not 'position'
            };

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

            const submitBtn = $form.find('[type="submit"]');
            const originalText = submitBtn.text();

            ContactsAjax.createContact(formData, {
                beforeSend: function() {
                    submitBtn.prop('disabled', true).text('Adding...');
                },
                success: function(data) {
                    alert('Contact added successfully!');
                    $form[0].reset();
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
        },

        /**
         * Submit company form
         */
        submitCompanyForm: function($form) {
            const formData = {
                company_name: $form.find('[name="name"]').val(),
                company_website: $form.find('[name="website"]').val(),
                company_phone: $form.find('[name="phone"]').val(),
                company_email: $form.find('[name="email"]').val(),
                company_notes: $form.find('[name="address"]').val()
            };

            const submitBtn = $form.find('[type="submit"]');
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
                        $select.append('<option value="' + company.id + '">' + company.name + '</option>');
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
            const email = contact.email || 'No email';
            const phone = contact.phone || 'No phone';
            const position = contact.position || '';
            const company = contact.company_name || '';

            return `
                <div class="contact-card" data-id="${contact.id}">
                    <div class="contact-avatar">
                        <i data-feather="user"></i>
                    </div>
                    <div class="contact-info">
                        <h3>${name}</h3>
                        ${position ? '<p class="contact-position">' + position + '</p>' : ''}
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
            console.log('🚀 NEW VERSION 1.0.1 LOADED - If you see this, the cache is cleared! 🚀');
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
