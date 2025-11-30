<?php
/**
 * Contact Entity Class
 * 
 * Handles all contact-related database operations including emails, phones, socials, and tags
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WProject_Contact {
    
    public $id;
    public $company_id;
    public $first_name;
    public $last_name;
    public $role;
    public $department;
    public $photo_url;
    public $gravatar_email;
    public $contact_id_number;
    public $passport_number;
    public $notes;
    public $created_by;
    public $created_at;
    public $updated_at;
    
    // Relationships (loaded separately)
    public $emails = array();
    public $phones = array();
    public $socials = array();
    public $tags = array();
    
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
     * Create a new contact
     */
    public static function create($data) {
        global $wpdb;
        
        // Validate required fields
        $validation = self::validate($data);
        if (is_wp_error($validation)) {
            return $validation;
        }
        
        // Start transaction
        $wpdb->query('START TRANSACTION');
        
        try {
            // Prepare insert data
            $insert_data = array(
                'company_id' => !empty($data['company_id']) ? intval($data['company_id']) : null,
                'first_name' => sanitize_text_field($data['first_name']),
                'last_name' => sanitize_text_field($data['last_name']),
                'role' => !empty($data['role']) ? sanitize_text_field($data['role']) : null,
                'department' => !empty($data['department']) ? sanitize_text_field($data['department']) : null,
                'photo_url' => !empty($data['photo_url']) ? esc_url_raw($data['photo_url']) : null,
                'gravatar_email' => !empty($data['gravatar_email']) ? sanitize_email($data['gravatar_email']) : null,
                'contact_id_number' => !empty($data['contact_id_number']) ? sanitize_text_field($data['contact_id_number']) : null,
                'passport_number' => !empty($data['passport_number']) ? sanitize_text_field($data['passport_number']) : null,
                'notes' => !empty($data['notes']) ? wp_kses_post($data['notes']) : null,
                'created_by' => get_current_user_id(),
            );
            
            // Insert contact
            $result = $wpdb->insert(
                $wpdb->prefix . 'wproject_contacts',
                $insert_data,
                array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d')
            );

            if ($result === false) {
                $error_msg = 'Failed to create contact.';
                if ($wpdb->last_error) {
                    $error_msg .= ' Database error: ' . $wpdb->last_error;
                }
                error_log('wProject Contacts Pro - Create Contact Error: ' . $error_msg);
                error_log('wProject Contacts Pro - Insert Data: ' . print_r($insert_data, true));
                throw new Exception(__($error_msg, 'wproject-contacts-pro'));
            }
            
            $contact_id = $wpdb->insert_id;
            
            // Add emails
            if (!empty($data['emails']) && is_array($data['emails'])) {
                foreach ($data['emails'] as $email_data) {
                    self::add_email_internal($contact_id, $email_data);
                }
            }
            
            // Add phones
            if (!empty($data['phones']) && is_array($data['phones'])) {
                foreach ($data['phones'] as $phone_data) {
                    self::add_phone_internal($contact_id, $phone_data);
                }
            }
            
            // Add socials
            if (!empty($data['socials']) && is_array($data['socials'])) {
                foreach ($data['socials'] as $social_data) {
                    self::add_social_internal($contact_id, $social_data);
                }
            }
            
            // Add tags
            if (!empty($data['tags']) && is_array($data['tags'])) {
                wp_set_object_terms($contact_id, $data['tags'], 'contact_tag');
            }
            
            // Commit transaction
            $wpdb->query('COMMIT');
            
            // Return the new contact object
            return self::get($contact_id);
            
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            return new WP_Error('db_error', $e->getMessage());
        }
    }
    
    /**
     * Get contact by ID with all relations
     */
    public static function get($id) {
        global $wpdb;
        
        $contact_data = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}wproject_contacts WHERE id = %d",
            $id
        ), ARRAY_A);
        
        if (!$contact_data) {
            return new WP_Error('not_found', __('Contact not found.', 'wproject-contacts-pro'));
        }
        
        $contact = new self($contact_data);
        
        // Load emails
        $contact->emails = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}wproject_contact_emails WHERE contact_id = %d ORDER BY is_preferred DESC, id ASC",
            $id
        ), ARRAY_A);
        
        // Load phones
        $contact->phones = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}wproject_contact_phones WHERE contact_id = %d ORDER BY is_preferred DESC, id ASC",
            $id
        ), ARRAY_A);
        
        // Load socials
        $contact->socials = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}wproject_contact_socials WHERE contact_id = %d",
            $id
        ), ARRAY_A);
        
        // Load tags
        $terms = wp_get_object_terms($id, 'contact_tag');
        if (!is_wp_error($terms)) {
            $contact->tags = $terms;
        }
        
        return $contact;
    }
    
    /**
     * Update contact
     */
    public static function update($id, $data) {
        global $wpdb;
        
        // Check if contact exists
        $contact = self::get($id);
        if (is_wp_error($contact)) {
            return $contact;
        }
        
        // Validate data
        $validation = self::validate($data, $id);
        if (is_wp_error($validation)) {
            return $validation;
        }
        
        // Start transaction
        $wpdb->query('START TRANSACTION');
        
        try {
            // Prepare update data for main contact
            $update_data = array();

            if (isset($data['company_id'])) $update_data['company_id'] = !empty($data['company_id']) ? intval($data['company_id']) : null;
            if (isset($data['first_name'])) $update_data['first_name'] = sanitize_text_field($data['first_name']);
            if (isset($data['last_name'])) $update_data['last_name'] = sanitize_text_field($data['last_name']);
            if (isset($data['role'])) $update_data['role'] = !empty($data['role']) ? sanitize_text_field($data['role']) : null;
            if (isset($data['department'])) $update_data['department'] = !empty($data['department']) ? sanitize_text_field($data['department']) : null;
            if (isset($data['photo_url'])) $update_data['photo_url'] = !empty($data['photo_url']) ? esc_url_raw($data['photo_url']) : null;
            if (isset($data['gravatar_email'])) $update_data['gravatar_email'] = !empty($data['gravatar_email']) ? sanitize_email($data['gravatar_email']) : null;
            if (isset($data['contact_id_number'])) $update_data['contact_id_number'] = !empty($data['contact_id_number']) ? sanitize_text_field($data['contact_id_number']) : null;
            if (isset($data['passport_number'])) $update_data['passport_number'] = !empty($data['passport_number']) ? sanitize_text_field($data['passport_number']) : null;
            if (isset($data['notes'])) $update_data['notes'] = !empty($data['notes']) ? wp_kses_post($data['notes']) : null;
            
            // Update main contact if there's data
            if (!empty($update_data)) {
                $result = $wpdb->update(
                    $wpdb->prefix . 'wproject_contacts',
                    $update_data,
                    array('id' => $id),
                    null,
                    array('%d')
                );
                
                if ($result === false) {
                    throw new Exception(__('Failed to update contact.', 'wproject-contacts-pro'));
                }
            }
            
            // Update emails if provided
            if (isset($data['emails'])) {
                // Delete existing emails
                $wpdb->delete($wpdb->prefix . 'wproject_contact_emails', array('contact_id' => $id), array('%d'));
                
                // Add new emails
                if (is_array($data['emails'])) {
                    foreach ($data['emails'] as $email_data) {
                        self::add_email_internal($id, $email_data);
                    }
                }
            }
            
            // Update phones if provided
            if (isset($data['phones'])) {
                // Delete existing phones
                $wpdb->delete($wpdb->prefix . 'wproject_contact_phones', array('contact_id' => $id), array('%d'));
                
                // Add new phones
                if (is_array($data['phones'])) {
                    foreach ($data['phones'] as $phone_data) {
                        self::add_phone_internal($id, $phone_data);
                    }
                }
            }
            
            // Update socials if provided
            if (isset($data['socials'])) {
                // Delete existing socials
                $wpdb->delete($wpdb->prefix . 'wproject_contact_socials', array('contact_id' => $id), array('%d'));
                
                // Add new socials
                if (is_array($data['socials'])) {
                    foreach ($data['socials'] as $social_data) {
                        self::add_social_internal($id, $social_data);
                    }
                }
            }
            
            // Update tags if provided
            if (isset($data['tags'])) {
                wp_set_object_terms($id, $data['tags'], 'contact_tag');
            }
            
            // Commit transaction
            $wpdb->query('COMMIT');
            
            // Return updated contact
            return self::get($id);
            
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            return new WP_Error('db_error', $e->getMessage());
        }
    }
    
    /**
     * Delete contact
     */
    public static function delete($id) {
        global $wpdb;
        
        // Check if contact exists
        $contact = self::get($id);
        if (is_wp_error($contact)) {
            return $contact;
        }
        
        // Delete (cascades to emails, phones, socials via foreign key)
        $result = $wpdb->delete(
            $wpdb->prefix . 'wproject_contacts',
            array('id' => $id),
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('db_error', __('Failed to delete contact.', 'wproject-contacts-pro'));
        }
        
        // Delete tags
        wp_delete_object_term_relationships($id, 'contact_tag');
        
        return true;
    }
    
    /**
     * List all contacts with filters and pagination
     */
    public static function list_all($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'page' => 1,
            'per_page' => 25,
            'orderby' => 'first_name',
            'order' => 'ASC',
            'company_id' => null,
            'tag' => null,
            'search' => null,
        );
        
        $args = wp_parse_args($args, $defaults);
        
        // Build WHERE clause
        $where = array('1=1');
        $where_values = array();
        
        if (!empty($args['company_id'])) {
            $where[] = 'company_id = %d';
            $where_values[] = $args['company_id'];
        }
        
        if (!empty($args['search'])) {
            $where[] = '(first_name LIKE %s OR last_name LIKE %s OR role LIKE %s)';
            $search_term = '%' . $wpdb->esc_like($args['search']) . '%';
            $where_values[] = $search_term;
            $where_values[] = $search_term;
            $where_values[] = $search_term;
        }
        
        $where_clause = implode(' AND ', $where);
        
        // Build ORDER BY clause
        $allowed_orderby = array('first_name', 'last_name', 'role', 'created_at');
        $orderby = in_array($args['orderby'], $allowed_orderby) ? $args['orderby'] : 'first_name';
        $order = strtoupper($args['order']) === 'DESC' ? 'DESC' : 'ASC';
        
        // Calculate offset
        $offset = ($args['page'] - 1) * $args['per_page'];
        
        // Get total count
        $count_query = "SELECT COUNT(*) FROM {$wpdb->prefix}wproject_contacts WHERE $where_clause";
        if (!empty($where_values)) {
            $count_query = $wpdb->prepare($count_query, $where_values);
        }
        $total = $wpdb->get_var($count_query);
        
        // Get contacts
        $query = "SELECT * FROM {$wpdb->prefix}wproject_contacts 
                  WHERE $where_clause 
                  ORDER BY $orderby $order 
                  LIMIT %d OFFSET %d";
        
        $query_values = array_merge($where_values, array($args['per_page'], $offset));
        $results = $wpdb->get_results($wpdb->prepare($query, $query_values), ARRAY_A);
        
        $contacts = array();
        foreach ($results as $row) {
            $contact = new self($row);
            // Load minimal relations for list view
            $contact->emails = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}wproject_contact_emails WHERE contact_id = %d ORDER BY is_preferred DESC LIMIT 1",
                $row['id']
            ), ARRAY_A);
            $contacts[] = $contact;
        }
        
        return array(
            'contacts' => $contacts,
            'total' => $total,
            'page' => $args['page'],
            'per_page' => $args['per_page'],
            'total_pages' => ceil($total / $args['per_page']),
        );
    }
    
    /**
     * Search contacts
     */
    public static function search($query) {
        return self::list_all(array(
            'search' => $query,
            'per_page' => 100,
        ));
    }
    
    /**
     * Get company object
     */
    public function get_company() {
        return WProject_Company::get($this->company_id);
    }
    
    /**
     * Get full name
     */
    public function get_full_name() {
        return trim($this->first_name . ' ' . $this->last_name);
    }
    
    /**
     * Get primary email
     */
    public function get_primary_email() {
        if (empty($this->emails)) {
            return null;
        }
        
        foreach ($this->emails as $email) {
            if (!empty($email['is_preferred'])) {
                return $email['email'];
            }
        }
        
        return $this->emails[0]['email'];
    }
    
    /**
     * Get primary phone
     */
    public function get_primary_phone() {
        if (empty($this->phones)) {
            return null;
        }
        
        foreach ($this->phones as $phone) {
            if (!empty($phone['is_preferred'])) {
                return $phone['phone_number'];
            }
        }
        
        return $this->phones[0]['phone_number'];
    }
    
    /**
     * Get avatar URL
     */
    public function get_avatar_url($size = 96) {
        if (!empty($this->photo_url)) {
            return $this->photo_url;
        }
        
        if (!empty($this->gravatar_email)) {
            return get_avatar_url($this->gravatar_email, array('size' => $size));
        }
        
        // Fallback to primary email
        $primary_email = $this->get_primary_email();
        if ($primary_email) {
            return get_avatar_url($primary_email, array('size' => $size));
        }
        
        return get_avatar_url('', array('size' => $size));
    }
    
    /**
     * Add tag to contact
     */
    public function add_tag($tag_name) {
        wp_add_object_terms($this->id, $tag_name, 'contact_tag');
        // Reload tags
        $this->tags = wp_get_object_terms($this->id, 'contact_tag');
    }
    
    /**
     * Remove tag from contact
     */
    public function remove_tag($tag_id) {
        wp_remove_object_terms($this->id, $tag_id, 'contact_tag');
        // Reload tags
        $this->tags = wp_get_object_terms($this->id, 'contact_tag');
    }
    
    /**
     * Get contacts by tag
     */
    public static function get_contacts_by_tag($tag_id) {
        $objects = get_objects_in_term($tag_id, 'contact_tag');
        if (is_wp_error($objects)) {
            return array();
        }
        
        $contacts = array();
        foreach ($objects as $contact_id) {
            $contact = self::get($contact_id);
            if (!is_wp_error($contact)) {
                $contacts[] = $contact;
            }
        }
        
        return $contacts;
    }
    
    /**
     * Internal method to add email
     */
    private static function add_email_internal($contact_id, $email_data) {
        global $wpdb;
        
        if (empty($email_data['email']) || !is_email($email_data['email'])) {
            return false;
        }
        
        return $wpdb->insert(
            $wpdb->prefix . 'wproject_contact_emails',
            array(
                'contact_id' => $contact_id,
                'email' => sanitize_email($email_data['email']),
                'label' => !empty($email_data['label']) ? sanitize_text_field($email_data['label']) : 'work',
                'is_preferred' => !empty($email_data['is_preferred']) ? 1 : 0,
            ),
            array('%d', '%s', '%s', '%d')
        );
    }
    
    /**
     * Internal method to add phone
     */
    private static function add_phone_internal($contact_id, $phone_data) {
        global $wpdb;
        
        if (empty($phone_data['phone_number'])) {
            return false;
        }
        
        return $wpdb->insert(
            $wpdb->prefix . 'wproject_contact_phones',
            array(
                'contact_id' => $contact_id,
                'phone_number' => sanitize_text_field($phone_data['phone_number']),
                'phone_type' => !empty($phone_data['phone_type']) ? sanitize_text_field($phone_data['phone_type']) : 'cell',
                'label' => !empty($phone_data['label']) ? sanitize_text_field($phone_data['label']) : null,
                'is_preferred' => !empty($phone_data['is_preferred']) ? 1 : 0,
            ),
            array('%d', '%s', '%s', '%s', '%d')
        );
    }
    
    /**
     * Internal method to add social
     */
    private static function add_social_internal($contact_id, $social_data) {
        global $wpdb;
        
        if (empty($social_data['platform']) || empty($social_data['profile_url'])) {
            return false;
        }
        
        return $wpdb->insert(
            $wpdb->prefix . 'wproject_contact_socials',
            array(
                'contact_id' => $contact_id,
                'platform' => sanitize_text_field($social_data['platform']),
                'profile_url' => esc_url_raw($social_data['profile_url']),
            ),
            array('%d', '%s', '%s')
        );
    }
    
    /**
     * Validate contact data
     */
    private static function validate($data, $id = null) {
        $errors = array();
        
        // Validate required fields
        if (empty($data['first_name'])) {
            $errors[] = __('First name is required.', 'wproject-contacts-pro');
        }
        
        if (empty($data['last_name'])) {
            $errors[] = __('Last name is required.', 'wproject-contacts-pro');
        }

        // Company is optional, but if provided, validate it exists
        if (!empty($data['company_id'])) {
            // Check if company exists
            $company = WProject_Company::get($data['company_id']);
            if (is_wp_error($company)) {
                $errors[] = __('Invalid company selected.', 'wproject-contacts-pro');
            }
        }
        
        // Validate emails if provided
        if (!empty($data['emails']) && is_array($data['emails'])) {
            $has_valid_email = false;
            foreach ($data['emails'] as $email_data) {
                if (!empty($email_data['email'])) {
                    if (!is_email($email_data['email'])) {
                        $errors[] = sprintf(__('Invalid email address: %s', 'wproject-contacts-pro'), $email_data['email']);
                    } else {
                        $has_valid_email = true;
                    }
                }
            }
            if (!$has_valid_email && $id === null) {
                $errors[] = __('At least one email address is required.', 'wproject-contacts-pro');
            }
        } elseif ($id === null) {
            $errors[] = __('At least one email address is required.', 'wproject-contacts-pro');
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
        $company = $this->get_company();
        
        return array(
            'id' => $this->id,
            'company_id' => $this->company_id,
            'company_name' => !is_wp_error($company) ? $company->company_name : '',
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->get_full_name(),
            'role' => $this->role,
            'department' => $this->department,
            'photo_url' => $this->photo_url,
            'avatar_url' => $this->get_avatar_url(),
            'gravatar_email' => $this->gravatar_email,
            'contact_id_number' => $this->contact_id_number,
            'passport_number' => $this->passport_number,
            'notes' => $this->notes,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'emails' => $this->emails,
            'phones' => $this->phones,
            'socials' => $this->socials,
            'tags' => array_map(function($term) {
                return array('id' => $term->term_id, 'name' => $term->name);
            }, $this->tags),
            'primary_email' => $this->get_primary_email(),
            'primary_phone' => $this->get_primary_phone(),
        );
    }
}
