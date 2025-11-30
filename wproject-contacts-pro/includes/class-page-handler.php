<?php
/**
 * Page Handler for wProject Contacts Pro
 * Handles page template loading and routing
 */

if (!defined('ABSPATH')) {
    exit;
}

class WProject_Contacts_Page_Handler {

    /**
     * Initialize page handler
     */
    public static function init() {
        // Hook into template loading
        add_filter('template_include', array(__CLASS__, 'load_contacts_template'));

        // Register rewrite rules
        add_action('init', array(__CLASS__, 'add_rewrite_rules'));

        // Add query vars
        add_filter('query_vars', array(__CLASS__, 'add_query_vars'));
    }

    /**
     * Add rewrite rules for contacts page
     */
    public static function add_rewrite_rules() {
        add_rewrite_rule('^contacts/?$', 'index.php?wpc_page=contacts', 'top');
        add_rewrite_rule('^contacts/([0-9]+)/?$', 'index.php?wpc_page=contact&wpc_id=$matches[1]', 'top');
    }

    /**
     * Add custom query vars
     */
    public static function add_query_vars($vars) {
        $vars[] = 'wpc_page';
        $vars[] = 'wpc_id';
        return $vars;
    }

    /**
     * Load contacts template
     */
    public static function load_contacts_template($template) {
        global $wp_query;

        $wpc_page = get_query_var('wpc_page');

        // Check if we're on our custom contacts route
        if ($wpc_page === 'contacts') {
            $custom_template = WPROJECT_CONTACTS_PRO_PATH . 'templates/contacts-page.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }

        // Check if we're on a WordPress page with slug 'contacts'
        if (is_page()) {
            $page = get_post();
            if ($page && (strtolower($page->post_name) === 'contacts' || strtolower($page->post_title) === 'contacts')) {
                $custom_template = WPROJECT_CONTACTS_PRO_PATH . 'templates/contacts-page.php';
                if (file_exists($custom_template)) {
                    return $custom_template;
                }
            }
        }

        return $template;
    }

    /**
     * Flush rewrite rules on activation
     */
    public static function flush_rules() {
        self::add_rewrite_rules();
        flush_rewrite_rules();
    }
}
