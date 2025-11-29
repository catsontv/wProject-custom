<?php
/**
 * Company Entity Class
 * 
 * Handles all company-related database operations
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WProject_Company {
    
    public $id;
    public $company_name;
    public $company_website;
    public $company_phone;
    public $company_email;
    public $company_type;
    public $company_logo_url;
    public $company_notes;
    public $created_by;
    public $created_at;
    public $updated_at;
    
    /**
     * Constructor
     */
    public function __construct($data = array()) {
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }
    
    /**
     * Create a new company
     */
    public static function create($data) {
        global $wpdb;
        
        // Validate required fields
        $validation = self::validate($data);
        if (is_wp_error($validation)) {
            return $validation;
        }
        
        // Check for duplicate company name
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}wproject_companies WHERE company_name = %s",
            $data['company_name']
        ));
        
        if ($existing) {
            return new WP_Error('duplicate_company', __('A company with this name already exists.', 'wproject-contacts-pro'));
        }
        
        // Prepare insert data
        $insert_data = array(
            'company_name' => sanitize_text_field($data['company_name']),
            'company_website' => !empty($data['company_website']) ? esc_url_raw($data['company_website']) : null,
            'company_phone' => !empty($data['company_phone']) ? sanitize_text_field($data['company_phone']) : null,
            'company_email' => !empty($data['company_email']) ? sanitize_email($data['company_email']) : null,
            'company_type' => !empty($data['company_type']) ? sanitize_text_field($data['company_type']) : 'client',
            'company_logo_url' => !empty($data['company_logo_url']) ? esc_url_raw($data['company_logo_url']) : null,
            'company_notes' => !empty($data['company_notes']) ? wp_kses_post($data['company_notes']) : null,
            'created_by' => get_current_user_id(),
        );
        
        // Insert into database
        $result = $wpdb->insert(
            $wpdb->prefix . 'wproject_companies',
            $insert_data,
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d')
        );
        
        if ($result === false) {
            return new WP_Error('db_error', __('Failed to create company.', 'wproject-contacts-pro'));
        }
        
        // Return the new company object
        return self::get($wpdb->insert_id);
    }
    
    /**
     * Get company by ID
     */
    public static function get($id) {
        global $wpdb;
        
        $company = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}wproject_companies WHERE id = %d",
            $id
        ), ARRAY_A);
        
        if (!$company) {
            return new WP_Error('not_found', __('Company not found.', 'wproject-contacts-pro'));
        }
        
        return new self($company);
    }
    
    /**
     * Update company
     */
    public static function update($id, $data) {
        global $wpdb;
        
        // Check if company exists
        $company = self::get($id);
        if (is_wp_error($company)) {
            return $company;
        }
        
        // Validate data
        $validation = self::validate($data, $id);
        if (is_wp_error($validation)) {
            return $validation;
        }
        
        // Check for duplicate name (excluding current company)
        if (!empty($data['company_name'])) {
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}wproject_companies WHERE company_name = %s AND id != %d",
                $data['company_name'],
                $id
            ));
            
            if ($existing) {
                return new WP_Error('duplicate_company', __('A company with this name already exists.', 'wproject-contacts-pro'));
            }
        }
        
        // Prepare update data
        $update_data = array();
        
        if (isset($data['company_name'])) {
            $update_data['company_name'] = sanitize_text_field($data['company_name']);
        }
        if (isset($data['company_website'])) {
            $update_data['company_website'] = !empty($data['company_website']) ? esc_url_raw($data['company_website']) : null;
        }
        if (isset($data['company_phone'])) {
            $update_data['company_phone'] = !empty($data['company_phone']) ? sanitize_text_field($data['company_phone']) : null;
        }
        if (isset($data['company_email'])) {
            $update_data['company_email'] = !empty($data['company_email']) ? sanitize_email($data['company_email']) : null;
        }
        if (isset($data['company_type'])) {
            $update_data['company_type'] = sanitize_text_field($data['company_type']);
        }
        if (isset($data['company_logo_url'])) {
            $update_data['company_logo_url'] = !empty($data['company_logo_url']) ? esc_url_raw($data['company_logo_url']) : null;
        }
        if (isset($data['company_notes'])) {
            $update_data['company_notes'] = !empty($data['company_notes']) ? wp_kses_post($data['company_notes']) : null;
        }
        
        if (empty($update_data)) {
            return new WP_Error('no_data', __('No data to update.', 'wproject-contacts-pro'));
        }
        
        // Update database
        $result = $wpdb->update(
            $wpdb->prefix . 'wproject_companies',
            $update_data,
            array('id' => $id),
            null,
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('db_error', __('Failed to update company.', 'wproject-contacts-pro'));
        }
        
        // Return updated company
        return self::get($id);
    }
    
    /**
     * Delete company
     */
    public static function delete($id) {
        global $wpdb;
        
        // Check if company exists
        $company = self::get($id);
        if (is_wp_error($company)) {
            return $company;
        }
        
        // Delete (cascades to contacts via foreign key)
        $result = $wpdb->delete(
            $wpdb->prefix . 'wproject_companies',
            array('id' => $id),
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('db_error', __('Failed to delete company.', 'wproject-contacts-pro'));
        }
        
        return true;
    }
    
    /**
     * List all companies with filters and pagination
     */
    public static function list_all($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'page' => 1,
            'per_page' => 25,
            'orderby' => 'company_name',
            'order' => 'ASC',
            'type' => null,
            'search' => null,
        );
        
        $args = wp_parse_args($args, $defaults);
        
        // Build WHERE clause
        $where = array('1=1');
        $where_values = array();
        
        if (!empty($args['type'])) {
            $where[] = 'company_type = %s';
            $where_values[] = $args['type'];
        }
        
        if (!empty($args['search'])) {
            $where[] = '(company_name LIKE %s OR company_email LIKE %s)';
            $search_term = '%' . $wpdb->esc_like($args['search']) . '%';
            $where_values[] = $search_term;
            $where_values[] = $search_term;
        }
        
        $where_clause = implode(' AND ', $where);
        
        // Build ORDER BY clause
        $allowed_orderby = array('company_name', 'company_type', 'created_at');
        $orderby = in_array($args['orderby'], $allowed_orderby) ? $args['orderby'] : 'company_name';
        $order = strtoupper($args['order']) === 'DESC' ? 'DESC' : 'ASC';
        
        // Calculate offset
        $offset = ($args['page'] - 1) * $args['per_page'];
        
        // Get total count
        $count_query = "SELECT COUNT(*) FROM {$wpdb->prefix}wproject_companies WHERE $where_clause";
        if (!empty($where_values)) {
            $count_query = $wpdb->prepare($count_query, $where_values);
        }
        $total = $wpdb->get_var($count_query);
        
        // Get companies
        $query = "SELECT * FROM {$wpdb->prefix}wproject_companies 
                  WHERE $where_clause 
                  ORDER BY $orderby $order 
                  LIMIT %d OFFSET %d";
        
        $query_values = array_merge($where_values, array($args['per_page'], $offset));
        $results = $wpdb->get_results($wpdb->prepare($query, $query_values), ARRAY_A);
        
        $companies = array();
        foreach ($results as $row) {
            $companies[] = new self($row);
        }
        
        return array(
            'companies' => $companies,
            'total' => $total,
            'page' => $args['page'],
            'per_page' => $args['per_page'],
            'total_pages' => ceil($total / $args['per_page']),
        );
    }
    
    /**
     * Search companies by name
     */
    public static function search($query) {
        return self::list_all(array(
            'search' => $query,
            'per_page' => 100,
        ));
    }
    
    /**
     * Get all contacts for this company
     */
    public function get_contacts() {
        global $wpdb;
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}wproject_contacts WHERE company_id = %d ORDER BY first_name ASC",
            $this->id
        ), ARRAY_A);
        
        $contacts = array();
        foreach ($results as $row) {
            $contacts[] = new WProject_Contact($row);
        }
        
        return $contacts;
    }
    
    /**
     * Get contact count for this company
     */
    public function get_contact_count() {
        global $wpdb;
        
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}wproject_contacts WHERE company_id = %d",
            $this->id
        ));
    }
    
    /**
     * Validate company data
     */
    private static function validate($data, $id = null) {
        $errors = array();
        
        // Validate company name
        if (empty($data['company_name'])) {
            $errors[] = __('Company name is required.', 'wproject-contacts-pro');
        } elseif (strlen($data['company_name']) > 255) {
            $errors[] = __('Company name must be less than 255 characters.', 'wproject-contacts-pro');
        }
        
        // Validate email if provided
        if (!empty($data['company_email']) && !is_email($data['company_email'])) {
            $errors[] = __('Invalid email address.', 'wproject-contacts-pro');
        }
        
        // Validate website if provided
        if (!empty($data['company_website']) && !filter_var($data['company_website'], FILTER_VALIDATE_URL)) {
            $errors[] = __('Invalid website URL.', 'wproject-contacts-pro');
        }
        
        // Validate company type
        if (!empty($data['company_type'])) {
            $valid_types = array('client', 'vendor', 'partner', 'other');
            if (!in_array($data['company_type'], $valid_types)) {
                $errors[] = __('Invalid company type.', 'wproject-contacts-pro');
            }
        }
        
        if (!empty($errors)) {
            return new WP_Error('validation_error', implode(' ', $errors));
        }
        
        return true;
    }
    
    /**
     * Convert to array
     */
    public function to_array() {
        return array(
            'id' => $this->id,
            'company_name' => $this->company_name,
            'company_website' => $this->company_website,
            'company_phone' => $this->company_phone,
            'company_email' => $this->company_email,
            'company_type' => $this->company_type,
            'company_logo_url' => $this->company_logo_url,
            'company_notes' => $this->company_notes,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'contact_count' => $this->get_contact_count(),
        );
    }
}
