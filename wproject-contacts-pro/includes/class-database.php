<?php
/**
 * Database Management Class
 * 
 * Handles creation and migration of all database tables
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WProject_Contacts_Database {
    
    /**
     * Create all database tables
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Companies table
        $sql_companies = "CREATE TABLE {$wpdb->prefix}wproject_companies (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            company_name varchar(255) NOT NULL,
            company_website varchar(255) DEFAULT NULL,
            company_phone varchar(50) DEFAULT NULL,
            company_email varchar(255) DEFAULT NULL,
            company_type enum('client','vendor','partner','other') DEFAULT 'client',
            company_logo_url varchar(500) DEFAULT NULL,
            company_notes text DEFAULT NULL,
            created_by bigint(20) unsigned NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY company_name (company_name),
            KEY created_by (created_by),
            KEY company_type (company_type),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        // Contacts table
        $sql_contacts = "CREATE TABLE {$wpdb->prefix}wproject_contacts (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            company_id bigint(20) unsigned NOT NULL,
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            role varchar(100) DEFAULT NULL,
            department varchar(100) DEFAULT NULL,
            photo_url varchar(500) DEFAULT NULL,
            gravatar_email varchar(255) DEFAULT NULL,
            contact_id_number varchar(100) DEFAULT NULL,
            passport_number varchar(100) DEFAULT NULL,
            notes text DEFAULT NULL,
            created_by bigint(20) unsigned NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY company_id (company_id),
            KEY created_by (created_by),
            KEY full_name (first_name, last_name),
            KEY created_at (created_at),
            CONSTRAINT fk_contact_company FOREIGN KEY (company_id) 
                REFERENCES {$wpdb->prefix}wproject_companies(id) 
                ON DELETE CASCADE
        ) $charset_collate;";
        
        // Contact Emails table
        $sql_emails = "CREATE TABLE {$wpdb->prefix}wproject_contact_emails (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            contact_id bigint(20) unsigned NOT NULL,
            email varchar(255) NOT NULL,
            label enum('work','personal','assistant','other') DEFAULT 'work',
            is_preferred tinyint(1) DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY contact_id (contact_id),
            KEY email (email),
            KEY is_preferred (is_preferred),
            CONSTRAINT fk_email_contact FOREIGN KEY (contact_id) 
                REFERENCES {$wpdb->prefix}wproject_contacts(id) 
                ON DELETE CASCADE
        ) $charset_collate;";
        
        // Contact Phones table
        $sql_phones = "CREATE TABLE {$wpdb->prefix}wproject_contact_phones (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            contact_id bigint(20) unsigned NOT NULL,
            phone_number varchar(50) NOT NULL,
            phone_type enum('cell','local') DEFAULT 'cell',
            label varchar(50) DEFAULT NULL,
            is_preferred tinyint(1) DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY contact_id (contact_id),
            KEY phone_type (phone_type),
            KEY is_preferred (is_preferred),
            CONSTRAINT fk_phone_contact FOREIGN KEY (contact_id) 
                REFERENCES {$wpdb->prefix}wproject_contacts(id) 
                ON DELETE CASCADE
        ) $charset_collate;";
        
        // Contact Socials table
        $sql_socials = "CREATE TABLE {$wpdb->prefix}wproject_contact_socials (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            contact_id bigint(20) unsigned NOT NULL,
            platform enum('linkedin','twitter','facebook','instagram','other') NOT NULL,
            profile_url varchar(500) NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY contact_id (contact_id),
            KEY platform (platform),
            CONSTRAINT fk_social_contact FOREIGN KEY (contact_id) 
                REFERENCES {$wpdb->prefix}wproject_contacts(id) 
                ON DELETE CASCADE
        ) $charset_collate;";
        
        // Contact-Project relationships
        $sql_projects = "CREATE TABLE {$wpdb->prefix}wproject_contact_projects (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            contact_id bigint(20) unsigned NOT NULL,
            project_term_id bigint(20) unsigned NOT NULL,
            linked_by bigint(20) unsigned NOT NULL,
            linked_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY contact_project (contact_id, project_term_id),
            KEY contact_id (contact_id),
            KEY project_term_id (project_term_id),
            KEY linked_at (linked_at),
            CONSTRAINT fk_project_contact FOREIGN KEY (contact_id) 
                REFERENCES {$wpdb->prefix}wproject_contacts(id) 
                ON DELETE CASCADE
        ) $charset_collate;";
        
        // Contact-Task relationships
        $sql_tasks = "CREATE TABLE {$wpdb->prefix}wproject_contact_tasks (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            contact_id bigint(20) unsigned NOT NULL,
            task_post_id bigint(20) unsigned NOT NULL,
            linked_by bigint(20) unsigned NOT NULL,
            notification_sent tinyint(1) DEFAULT 0,
            linked_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY contact_task (contact_id, task_post_id),
            KEY contact_id (contact_id),
            KEY task_post_id (task_post_id),
            KEY linked_at (linked_at),
            CONSTRAINT fk_task_contact FOREIGN KEY (contact_id) 
                REFERENCES {$wpdb->prefix}wproject_contacts(id) 
                ON DELETE CASCADE
        ) $charset_collate;";
        
        // Contact-Event relationships
        $sql_events = "CREATE TABLE {$wpdb->prefix}wproject_contact_events (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            contact_id bigint(20) unsigned NOT NULL,
            event_id bigint(20) unsigned NOT NULL,
            invitation_sent tinyint(1) DEFAULT 0,
            rsvp_status enum('pending','accepted','declined','tentative') DEFAULT 'pending',
            linked_by bigint(20) unsigned NOT NULL,
            linked_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY contact_event (contact_id, event_id),
            KEY contact_id (contact_id),
            KEY event_id (event_id),
            KEY rsvp_status (rsvp_status),
            KEY linked_at (linked_at),
            CONSTRAINT fk_event_contact FOREIGN KEY (contact_id) 
                REFERENCES {$wpdb->prefix}wproject_contacts(id) 
                ON DELETE CASCADE
        ) $charset_collate;";
        
        // Execute table creation
        dbDelta($sql_companies);
        dbDelta($sql_contacts);
        dbDelta($sql_emails);
        dbDelta($sql_phones);
        dbDelta($sql_socials);
        dbDelta($sql_projects);
        dbDelta($sql_tasks);
        dbDelta($sql_events);
        
        // Create contact-tag term relationships table (uses WP term taxonomy)
        // This is handled by WordPress core when we register the taxonomy
        
        return true;
    }
    
    /**
     * Check if tables exist
     */
    public static function tables_exist() {
        global $wpdb;
        
        $tables = array(
            $wpdb->prefix . 'wproject_companies',
            $wpdb->prefix . 'wproject_contacts',
            $wpdb->prefix . 'wproject_contact_emails',
            $wpdb->prefix . 'wproject_contact_phones',
            $wpdb->prefix . 'wproject_contact_socials',
            $wpdb->prefix . 'wproject_contact_projects',
            $wpdb->prefix . 'wproject_contact_tasks',
            $wpdb->prefix . 'wproject_contact_events',
        );
        
        foreach ($tables as $table) {
            if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Drop all tables (used for testing/uninstall)
     * Note: Not called automatically - data persistence requirement
     */
    public static function drop_tables() {
        global $wpdb;
        
        // Drop in reverse order due to foreign keys
        $tables = array(
            $wpdb->prefix . 'wproject_contact_events',
            $wpdb->prefix . 'wproject_contact_tasks',
            $wpdb->prefix . 'wproject_contact_projects',
            $wpdb->prefix . 'wproject_contact_socials',
            $wpdb->prefix . 'wproject_contact_phones',
            $wpdb->prefix . 'wproject_contact_emails',
            $wpdb->prefix . 'wproject_contacts',
            $wpdb->prefix . 'wproject_companies',
        );
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
        
        return true;
    }
}
