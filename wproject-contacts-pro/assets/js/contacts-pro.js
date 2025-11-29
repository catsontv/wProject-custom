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
     * Contacts UI Controller
     */
    const ContactsUI = {

        /**
         * Load contacts list
         */
        loadContactsList: function() {
            const $tableBody = $('#contacts-table-body');
            const $loading = $('.contacts-loading');
            const $empty = $('.contacts-empty');

            ContactsAjax.listCompanies({}, {
                beforeSend: function() {
                    $loading.show();
                    $tableBody.empty();
                },
                success: function(data) {
                    $loading.hide();

                    if (data.companies && data.companies.length > 0) {
                        data.companies.forEach(function(company) {
                            ContactsUI.renderContactRow(company);
                        });
                        $empty.hide();
                    } else {
                        $empty.show();
                    }

                    // Re-initialize feather icons
                    if (typeof feather !== 'undefined') {
                        feather.replace();
                    }
                },
                error: function(message) {
                    $loading.hide();
                    console.error('Error loading contacts:', message);
                }
            });
        },

        /**
         * Render contact row in table
         */
        renderContactRow: function(company) {
            const $tableBody = $('#contacts-table-body');

            // Get primary contact if available
            const primaryEmail = company.company_email || '';
            const primaryPhone = company.company_phone || '';
            const contactName = company.contact_name || '-';

            const row = `
                <tr data-company-id="${company.id}" class="contact-row">
                    <td><strong>${company.company_name}</strong></td>
                    <td>${contactName}</td>
                    <td>${primaryEmail}</td>
                    <td>${primaryPhone}</td>
                </tr>
            `;

            $tableBody.append(row);
        },

        /**
         * Show contact form
         */
        showContactForm: function(companyId = null) {
            $('.contacts-list-view').hide();
            $('.contact-detail-view').hide();
            $('.contact-form-view').show();

            if (companyId) {
                // Load company data for editing
                ContactsUI.loadCompanyForEdit(companyId);
            } else {
                // Reset form for new contact
                $('#contact-form')[0].reset();
                $('#contact_id').val('');
            }
        },

        /**
         * Load company for editing
         */
        loadCompanyForEdit: function(companyId) {
            ContactsAjax.getCompany(companyId, {
                success: function(data) {
                    const company = data;

                    // Populate form fields
                    $('#contact_id').val(company.id);
                    $('#company_name').val(company.company_name || '');
                    $('#company_email').val(company.company_email || '');
                    $('#company_phone').val(company.company_phone || '');
                    $('#company_website').val(company.company_website || '');
                    $('#company_abn').val(company.company_abn || '');
                    $('#contact_notes').val(company.company_notes || '');

                    // Address fields
                    $('#address_street').val(company.address_street || '');
                    $('#address_line2').val(company.address_line2 || '');
                    $('#address_city').val(company.address_city || '');
                    $('#address_state').val(company.address_state || '');
                    $('#address_country').val(company.address_country || '');
                    $('#address_postcode').val(company.address_postcode || '');

                    // Contact person fields
                    $('#contact_name').val(company.contact_name || '');
                    $('#contact_title').val(company.contact_title || '');
                    $('#contact_email').val(company.contact_email || '');
                    $('#contact_mobile').val(company.contact_mobile || '');
                    $('#contact_phone').val(company.contact_phone || '');

                    // Communication fields
                    $('#comm_slack').val(company.comm_slack || '');
                    $('#comm_teams').val(company.comm_teams || '');
                    $('#comm_google_meet').val(company.comm_google_meet || '');
                    $('#comm_skype').val(company.comm_skype || '');
                    $('#comm_other').val(company.comm_other || '');

                    // Social fields
                    $('#social_facebook').val(company.social_facebook || '');
                    $('#social_instagram').val(company.social_instagram || '');
                    $('#social_twitter').val(company.social_twitter || '');
                    $('#social_linkedin').val(company.social_linkedin || '');
                    $('#social_other').val(company.social_other || '');
                },
                error: function(message) {
                    alert('Error loading contact: ' + message);
                }
            });
        },

        /**
         * Hide contact form
         */
        hideContactForm: function() {
            $('.contact-form-view').hide();
            $('.contact-detail-view').hide();
            $('.contacts-list-view').show();
        },

        /**
         * Save contact
         */
        saveContact: function(formData) {
            const companyId = formData.contact_id;

            const callbacks = {
                beforeSend: function() {
                    $('#contact-form button[type="submit"]').prop('disabled', true).text('Saving...');
                },
                success: function(data) {
                    alert('Contact saved successfully!');
                    ContactsUI.hideContactForm();
                    ContactsUI.loadContactsList();
                },
                error: function(message) {
                    alert('Error saving contact: ' + message);
                },
                complete: function() {
                    $('#contact-form button[type="submit"]').prop('disabled', false).text('Save Contact');
                }
            };

            if (companyId) {
                ContactsAjax.updateCompany(companyId, formData, callbacks);
            } else {
                ContactsAjax.createCompany(formData, callbacks);
            }
        }
    };

    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        console.log('wProject Contacts Pro initialized');

        // Load contacts list on contacts page
        if ($('.contacts-pro-container').length > 0) {
            ContactsUI.loadContactsList();
        }

        // Handle CREATE button click for contacts
        $(document).on('click', '.create-contact', function(e) {
            e.preventDefault();
            ContactsUI.showContactForm();
        });

        // Handle contact form submission
        $('#contact-form').on('submit', function(e) {
            e.preventDefault();

            const formData = {
                contact_id: $('#contact_id').val(),
                company_name: $('#company_name').val(),
                company_email: $('#company_email').val(),
                company_phone: $('#company_phone').val(),
                company_website: $('#company_website').val(),
                company_abn: $('#company_abn').val(),
                company_notes: $('#contact_notes').val(),
                address_street: $('#address_street').val(),
                address_line2: $('#address_line2').val(),
                address_city: $('#address_city').val(),
                address_state: $('#address_state').val(),
                address_country: $('#address_country').val(),
                address_postcode: $('#address_postcode').val(),
                contact_name: $('#contact_name').val(),
                contact_title: $('#contact_title').val(),
                contact_email: $('#contact_email').val(),
                contact_mobile: $('#contact_mobile').val(),
                contact_phone: $('#contact_phone').val(),
                comm_slack: $('#comm_slack').val(),
                comm_teams: $('#comm_teams').val(),
                comm_google_meet: $('#comm_google_meet').val(),
                comm_skype: $('#comm_skype').val(),
                comm_other: $('#comm_other').val(),
                social_facebook: $('#social_facebook').val(),
                social_instagram: $('#social_instagram').val(),
                social_twitter: $('#social_twitter').val(),
                social_linkedin: $('#social_linkedin').val(),
                social_other: $('#social_other').val()
            };

            ContactsUI.saveContact(formData);
        });

        // Handle cancel button
        $('.cancel-contact-form').on('click', function(e) {
            e.preventDefault();
            ContactsUI.hideContactForm();
        });

        // Handle contact row click to edit
        $(document).on('click', '.contact-row', function() {
            const companyId = $(this).data('company-id');
            ContactsUI.showContactForm(companyId);
        });

        // Handle "All Contacts" sidebar click
        $('.show-contacts-list').on('click', function() {
            ContactsUI.hideContactForm();
        });
    });

    // Expose to global scope
    window.wpContactsProUI = ContactsUI;

})(jQuery);
