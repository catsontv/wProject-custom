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
     * Initialize on document ready
     */
    $(document).ready(function() {
        console.log('wProject Contacts Pro initialized');
        
        // Add any initialization code here
    });
    
})(jQuery);
