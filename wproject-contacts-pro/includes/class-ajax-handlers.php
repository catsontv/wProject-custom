<?php
/**
 * AJAX Handlers Class
 * 
 * Handles all AJAX requests for companies and contacts
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WProject_Contacts_Ajax {
    
    /**
     * Initialize AJAX handlers
     */
    public static function init() {
        // Company endpoints
        add_action('wp_ajax_contacts_pro_create_company', array(__CLASS__, 'create_company'));
        add_action('wp_ajax_contacts_pro_update_company', array(__CLASS__, 'update_company'));
        add_action('wp_ajax_contacts_pro_delete_company', array(__CLASS__, 'delete_company'));
        add_action('wp_ajax_contacts_pro_get_company', array(__CLASS__, 'get_company'));
        add_action('wp_ajax_contacts_pro_list_companies', array(__CLASS__, 'list_companies'));

        // Contact endpoints
        add_action('wp_ajax_contacts_pro_create_contact', array(__CLASS__, 'create_contact'));
        add_action('wp_ajax_contacts_pro_update_contact', array(__CLASS__, 'update_contact'));
        add_action('wp_ajax_contacts_pro_delete_contact', array(__CLASS__, 'delete_contact'));
        add_action('wp_ajax_contacts_pro_get_contact', array(__CLASS__, 'get_contact'));
        add_action('wp_ajax_contacts_pro_list_contacts', array(__CLASS__, 'list_contacts'));
        add_action('wp_ajax_contacts_pro_search_contacts', array(__CLASS__, 'search_contacts'));

        // Settings endpoints
        add_action('wp_ajax_save_contacts_pro_setting', array(__CLASS__, 'save_setting'));
    }
    
    /**
     * Verify nonce and capabilities
     */
    private static function verify_request($capability = 'read') {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wproject_contacts_pro_nonce')) {
            wp_send_json_error(array(
                'message' => __('Security check failed.', 'wproject-contacts-pro'),
            ), 403);
        }
        
        // Check user capabilities
        if (!current_user_can($capability)) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to perform this action.', 'wproject-contacts-pro'),
            ), 403);
        }
    }
    
    /**
     * Create company
     */
    public static function create_company() {
        self::verify_request('edit_posts');
        
        $data = array(
            'company_name' => isset($_POST['company_name']) ? sanitize_text_field($_POST['company_name']) : '',
            'company_website' => isset($_POST['company_website']) ? esc_url_raw($_POST['company_website']) : '',
            'company_phone' => isset($_POST['company_phone']) ? sanitize_text_field($_POST['company_phone']) : '',
            'company_email' => isset($_POST['company_email']) ? sanitize_email($_POST['company_email']) : '',
            'company_type' => isset($_POST['company_type']) ? sanitize_text_field($_POST['company_type']) : 'client',
            'company_logo_url' => isset($_POST['company_logo_url']) ? esc_url_raw($_POST['company_logo_url']) : '',
            'company_notes' => isset($_POST['company_notes']) ? wp_kses_post($_POST['company_notes']) : '',
        );
        
        $company = WProject_Company::create($data);
        
        if (is_wp_error($company)) {
            wp_send_json_error(array(
                'message' => $company->get_error_message(),
            ));
        }
        
        wp_send_json_success(array(
            'message' => __('Company created successfully.', 'wproject-contacts-pro'),
            'data' => $company->to_array(),
        ));
    }
    
    /**
     * Update company
     */
    public static function update_company() {
        self::verify_request('edit_posts');
        
        if (!isset($_POST['id'])) {
            wp_send_json_error(array(
                'message' => __('Company ID is required.', 'wproject-contacts-pro'),
            ));
        }
        
        $id = intval($_POST['id']);
        $data = array();
        
        if (isset($_POST['company_name'])) $data['company_name'] = sanitize_text_field($_POST['company_name']);
        if (isset($_POST['company_website'])) $data['company_website'] = esc_url_raw($_POST['company_website']);
        if (isset($_POST['company_phone'])) $data['company_phone'] = sanitize_text_field($_POST['company_phone']);
        if (isset($_POST['company_email'])) $data['company_email'] = sanitize_email($_POST['company_email']);
        if (isset($_POST['company_type'])) $data['company_type'] = sanitize_text_field($_POST['company_type']);
        if (isset($_POST['company_logo_url'])) $data['company_logo_url'] = esc_url_raw($_POST['company_logo_url']);
        if (isset($_POST['company_notes'])) $data['company_notes'] = wp_kses_post($_POST['company_notes']);
        
        $company = WProject_Company::update($id, $data);
        
        if (is_wp_error($company)) {
            wp_send_json_error(array(
                'message' => $company->get_error_message(),
            ));
        }
        
        wp_send_json_success(array(
            'message' => __('Company updated successfully.', 'wproject-contacts-pro'),
            'data' => $company->to_array(),
        ));
    }
    
    /**
     * Delete company
     */
    public static function delete_company() {
        self::verify_request('delete_posts');
        
        if (!isset($_POST['id'])) {
            wp_send_json_error(array(
                'message' => __('Company ID is required.', 'wproject-contacts-pro'),
            ));
        }
        
        $id = intval($_POST['id']);
        $result = WProject_Company::delete($id);
        
        if (is_wp_error($result)) {
            wp_send_json_error(array(
                'message' => $result->get_error_message(),
            ));
        }
        
        wp_send_json_success(array(
            'message' => __('Company deleted successfully.', 'wproject-contacts-pro'),
        ));
    }
    
    /**
     * Get single company
     */
    public static function get_company() {
        self::verify_request('read');
        
        if (!isset($_POST['id'])) {
            wp_send_json_error(array(
                'message' => __('Company ID is required.', 'wproject-contacts-pro'),
            ));
        }
        
        $id = intval($_POST['id']);
        $company = WProject_Company::get($id);
        
        if (is_wp_error($company)) {
            wp_send_json_error(array(
                'message' => $company->get_error_message(),
            ));
        }
        
        wp_send_json_success(array(
            'data' => $company->to_array(),
        ));
    }
    
    /**
     * List companies
     */
    public static function list_companies() {
        self::verify_request('read');
        
        $args = array(
            'page' => isset($_POST['page']) ? intval($_POST['page']) : 1,
            'per_page' => isset($_POST['per_page']) ? intval($_POST['per_page']) : 25,
            'orderby' => isset($_POST['orderby']) ? sanitize_text_field($_POST['orderby']) : 'company_name',
            'order' => isset($_POST['order']) ? sanitize_text_field($_POST['order']) : 'ASC',
            'type' => isset($_POST['type']) ? sanitize_text_field($_POST['type']) : null,
            'search' => isset($_POST['search']) ? sanitize_text_field($_POST['search']) : null,
        );
        
        $result = WProject_Company::list_all($args);
        
        // Convert companies to arrays
        $companies = array_map(function($company) {
            return $company->to_array();
        }, $result['companies']);
        
        wp_send_json_success(array(
            'data' => array(
                'companies' => $companies,
                'total' => $result['total'],
                'page' => $result['page'],
                'per_page' => $result['per_page'],
                'total_pages' => $result['total_pages'],
            ),
        ));
    }
    
    /**
     * Create contact
     */
    public static function create_contact() {
        self::verify_request('edit_posts');
        
        $data = array(
            'company_id' => isset($_POST['company_id']) ? intval($_POST['company_id']) : 0,
            'first_name' => isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '',
            'last_name' => isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '',
            'role' => isset($_POST['role']) ? sanitize_text_field($_POST['role']) : '',
            'department' => isset($_POST['department']) ? sanitize_text_field($_POST['department']) : '',
            'photo_url' => isset($_POST['photo_url']) ? esc_url_raw($_POST['photo_url']) : '',
            'gravatar_email' => isset($_POST['gravatar_email']) ? sanitize_email($_POST['gravatar_email']) : '',
            'contact_id_number' => isset($_POST['contact_id_number']) ? sanitize_text_field($_POST['contact_id_number']) : '',
            'passport_number' => isset($_POST['passport_number']) ? sanitize_text_field($_POST['passport_number']) : '',
            'notes' => isset($_POST['notes']) ? wp_kses_post($_POST['notes']) : '',
        );
        
        // Parse emails
        if (isset($_POST['emails']) && is_array($_POST['emails'])) {
            $data['emails'] = array_map(function($email) {
                return array(
                    'email' => sanitize_email($email['email']),
                    'label' => sanitize_text_field($email['label']),
                    'is_preferred' => !empty($email['is_preferred']) ? 1 : 0,
                );
            }, $_POST['emails']);
        }
        
        // Parse phones
        if (isset($_POST['phones']) && is_array($_POST['phones'])) {
            $data['phones'] = array_map(function($phone) {
                return array(
                    'phone_number' => sanitize_text_field($phone['phone_number']),
                    'phone_type' => sanitize_text_field($phone['phone_type']),
                    'label' => sanitize_text_field($phone['label']),
                    'is_preferred' => !empty($phone['is_preferred']) ? 1 : 0,
                );
            }, $_POST['phones']);
        }
        
        // Parse socials
        if (isset($_POST['socials']) && is_array($_POST['socials'])) {
            $data['socials'] = array_map(function($social) {
                return array(
                    'platform' => sanitize_text_field($social['platform']),
                    'profile_url' => esc_url_raw($social['profile_url']),
                );
            }, $_POST['socials']);
        }
        
        // Parse tags
        if (isset($_POST['tags'])) {
            $data['tags'] = is_array($_POST['tags']) ? array_map('sanitize_text_field', $_POST['tags']) : array();
        }
        
        $contact = WProject_Contact::create($data);
        
        if (is_wp_error($contact)) {
            wp_send_json_error(array(
                'message' => $contact->get_error_message(),
            ));
        }
        
        wp_send_json_success(array(
            'message' => __('Contact created successfully.', 'wproject-contacts-pro'),
            'data' => $contact->to_array(),
        ));
    }
    
    /**
     * Update contact
     */
    public static function update_contact() {
        self::verify_request('edit_posts');
        
        if (!isset($_POST['id'])) {
            wp_send_json_error(array(
                'message' => __('Contact ID is required.', 'wproject-contacts-pro'),
            ));
        }
        
        $id = intval($_POST['id']);
        $data = array();
        
        if (isset($_POST['company_id'])) $data['company_id'] = intval($_POST['company_id']);
        if (isset($_POST['first_name'])) $data['first_name'] = sanitize_text_field($_POST['first_name']);
        if (isset($_POST['last_name'])) $data['last_name'] = sanitize_text_field($_POST['last_name']);
        if (isset($_POST['role'])) $data['role'] = sanitize_text_field($_POST['role']);
        if (isset($_POST['department'])) $data['department'] = sanitize_text_field($_POST['department']);
        if (isset($_POST['photo_url'])) $data['photo_url'] = esc_url_raw($_POST['photo_url']);
        if (isset($_POST['gravatar_email'])) $data['gravatar_email'] = sanitize_email($_POST['gravatar_email']);
        if (isset($_POST['contact_id_number'])) $data['contact_id_number'] = sanitize_text_field($_POST['contact_id_number']);
        if (isset($_POST['passport_number'])) $data['passport_number'] = sanitize_text_field($_POST['passport_number']);
        if (isset($_POST['notes'])) $data['notes'] = wp_kses_post($_POST['notes']);
        
        // Parse emails
        if (isset($_POST['emails']) && is_array($_POST['emails'])) {
            $data['emails'] = array_map(function($email) {
                return array(
                    'email' => sanitize_email($email['email']),
                    'label' => sanitize_text_field($email['label']),
                    'is_preferred' => !empty($email['is_preferred']) ? 1 : 0,
                );
            }, $_POST['emails']);
        }
        
        // Parse phones
        if (isset($_POST['phones']) && is_array($_POST['phones'])) {
            $data['phones'] = array_map(function($phone) {
                return array(
                    'phone_number' => sanitize_text_field($phone['phone_number']),
                    'phone_type' => sanitize_text_field($phone['phone_type']),
                    'label' => sanitize_text_field($phone['label']),
                    'is_preferred' => !empty($phone['is_preferred']) ? 1 : 0,
                );
            }, $_POST['phones']);
        }
        
        // Parse socials
        if (isset($_POST['socials']) && is_array($_POST['socials'])) {
            $data['socials'] = array_map(function($social) {
                return array(
                    'platform' => sanitize_text_field($social['platform']),
                    'profile_url' => esc_url_raw($social['profile_url']),
                );
            }, $_POST['socials']);
        }
        
        // Parse tags
        if (isset($_POST['tags'])) {
            $data['tags'] = is_array($_POST['tags']) ? array_map('sanitize_text_field', $_POST['tags']) : array();
        }
        
        $contact = WProject_Contact::update($id, $data);
        
        if (is_wp_error($contact)) {
            wp_send_json_error(array(
                'message' => $contact->get_error_message(),
            ));
        }
        
        wp_send_json_success(array(
            'message' => __('Contact updated successfully.', 'wproject-contacts-pro'),
            'data' => $contact->to_array(),
        ));
    }
    
    /**
     * Delete contact
     */
    public static function delete_contact() {
        self::verify_request('delete_posts');
        
        if (!isset($_POST['id'])) {
            wp_send_json_error(array(
                'message' => __('Contact ID is required.', 'wproject-contacts-pro'),
            ));
        }
        
        $id = intval($_POST['id']);
        $result = WProject_Contact::delete($id);
        
        if (is_wp_error($result)) {
            wp_send_json_error(array(
                'message' => $result->get_error_message(),
            ));
        }
        
        wp_send_json_success(array(
            'message' => __('Contact deleted successfully.', 'wproject-contacts-pro'),
        ));
    }
    
    /**
     * Get single contact
     */
    public static function get_contact() {
        self::verify_request('read');
        
        if (!isset($_POST['id'])) {
            wp_send_json_error(array(
                'message' => __('Contact ID is required.', 'wproject-contacts-pro'),
            ));
        }
        
        $id = intval($_POST['id']);
        $contact = WProject_Contact::get($id);
        
        if (is_wp_error($contact)) {
            wp_send_json_error(array(
                'message' => $contact->get_error_message(),
            ));
        }
        
        wp_send_json_success(array(
            'data' => $contact->to_array(),
        ));
    }
    
    /**
     * List contacts
     */
    public static function list_contacts() {
        self::verify_request('read');
        
        $args = array(
            'page' => isset($_POST['page']) ? intval($_POST['page']) : 1,
            'per_page' => isset($_POST['per_page']) ? intval($_POST['per_page']) : 25,
            'orderby' => isset($_POST['orderby']) ? sanitize_text_field($_POST['orderby']) : 'first_name',
            'order' => isset($_POST['order']) ? sanitize_text_field($_POST['order']) : 'ASC',
            'company_id' => isset($_POST['company_id']) ? intval($_POST['company_id']) : null,
            'search' => isset($_POST['search']) ? sanitize_text_field($_POST['search']) : null,
        );
        
        $result = WProject_Contact::list_all($args);
        
        // Convert contacts to arrays
        $contacts = array_map(function($contact) {
            return $contact->to_array();
        }, $result['contacts']);
        
        wp_send_json_success(array(
            'data' => array(
                'contacts' => $contacts,
                'total' => $result['total'],
                'page' => $result['page'],
                'per_page' => $result['per_page'],
                'total_pages' => $result['total_pages'],
            ),
        ));
    }
    
    /**
     * Search contacts
     */
    public static function search_contacts() {
        self::verify_request('read');
        
        if (!isset($_POST['query'])) {
            wp_send_json_error(array(
                'message' => __('Search query is required.', 'wproject-contacts-pro'),
            ));
        }
        
        $query = sanitize_text_field($_POST['query']);
        $result = WProject_Contact::search($query);
        
        // Convert contacts to arrays
        $contacts = array_map(function($contact) {
            return $contact->to_array();
        }, $result['contacts']);
        
        wp_send_json_success(array(
            'data' => array(
                'contacts' => $contacts,
                'total' => $result['total'],
            ),
        ));
    }

    /**
     * Save plugin setting
     */
    public static function save_setting() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wproject_contacts_pro_settings')) {
            wp_send_json_error(array(
                'message' => __('Security check failed.', 'wproject-contacts-pro'),
            ), 403);
        }

        // Check admin capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to perform this action.', 'wproject-contacts-pro'),
            ), 403);
        }

        $setting_name = isset($_POST['setting_name']) ? sanitize_text_field($_POST['setting_name']) : '';
        $setting_value = isset($_POST['setting_value']) ? sanitize_text_field($_POST['setting_value']) : '';

        if (empty($setting_name)) {
            wp_send_json_error(array(
                'message' => __('Setting name is required.', 'wproject-contacts-pro'),
            ));
        }

        // Get existing settings
        $options = get_option('wproject_contacts_pro_settings', array());

        // Update the setting
        $options[$setting_name] = $setting_value;

        // Save to database
        update_option('wproject_contacts_pro_settings', $options);

        wp_send_json_success(array(
            'message' => __('Setting saved successfully.', 'wproject-contacts-pro'),
        ));
    }
}
