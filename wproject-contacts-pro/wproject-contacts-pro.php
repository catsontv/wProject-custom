<?php
/**
 * Plugin Name: wProject Contacts Pro
 * Plugin URI: https://rocketapps.com.au/wproject-contacts-pro/
 * Description: Comprehensive contact and company management system for wProject theme
 * Version: 1.0.2
 * Author: Rocket Apps
 * Author URI: https://rocketapps.com.au
 * Text Domain: wproject-contacts-pro
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 8.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WPROJECT_CONTACTS_PRO_VERSION', '1.0.2');
define('WPROJECT_CONTACTS_PRO_PATH', plugin_dir_path(__FILE__));
define('WPROJECT_CONTACTS_PRO_URL', plugin_dir_url(__FILE__));
define('WPROJECT_CONTACTS_PRO_FILE', __FILE__);

/**
 * Main Plugin Class
 */
class WProject_Contacts_Pro {
    
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        // Check requirements before loading
        add_action('admin_init', array($this, 'check_requirements'));
        
        // Load plugin
        add_action('plugins_loaded', array($this, 'init'));
        
        // Activation hook
        register_activation_hook(__FILE__, array($this, 'activate'));
        
        // Deactivation hook (do nothing - preserve data)
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Check if requirements are met
     */
    public function check_requirements() {
        $errors = array();
        
        // Check PHP version
        if (version_compare(PHP_VERSION, '8.0', '<')) {
            $errors[] = sprintf(
                __('wProject Contacts Pro requires PHP 8.0 or higher. You are running version %s.', 'wproject-contacts-pro'),
                PHP_VERSION
            );
        }
        
        // Check if wProject theme is active
        $theme = wp_get_theme();
        if ('wProject' !== $theme->name && 'wProject' !== $theme->parent_theme) {
            $errors[] = __('wProject Contacts Pro requires the wProject theme to be active.', 'wproject-contacts-pro');
        }
        
        // Display error notices
        if (!empty($errors)) {
            add_action('admin_notices', function() use ($errors) {
                foreach ($errors as $error) {
                    echo '<div class="notice notice-error"><p>' . esc_html($error) . '</p></div>';
                }
            });
            
            // Deactivate plugin
            deactivate_plugins(plugin_basename(__FILE__));
        }
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Load text domain
        load_plugin_textdomain('wproject-contacts-pro', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Include required files
        $this->includes();
        
        // Register taxonomy
        $this->register_taxonomy();
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }
    
    /**
     * Include required files
     */
    private function includes() {
        require_once WPROJECT_CONTACTS_PRO_PATH . 'includes/class-database.php';
        require_once WPROJECT_CONTACTS_PRO_PATH . 'includes/class-company.php';
        require_once WPROJECT_CONTACTS_PRO_PATH . 'includes/class-contact.php';
        require_once WPROJECT_CONTACTS_PRO_PATH . 'includes/class-ajax-handlers.php';
        require_once WPROJECT_CONTACTS_PRO_PATH . 'includes/class-page-handler.php';
        require_once WPROJECT_CONTACTS_PRO_PATH . 'admin/class-admin.php';

        // Initialize components
        WProject_Contacts_Ajax::init();
        WProject_Contacts_Page_Handler::init();
        WProject_Contacts_Admin::init();
    }
    
    /**
     * Register contact tags taxonomy
     */
    private function register_taxonomy() {
        register_taxonomy('contact_tag', null, array(
            'hierarchical' => false,
            'labels' => array(
                'name' => __('Contact Tags', 'wproject-contacts-pro'),
                'singular_name' => __('Contact Tag', 'wproject-contacts-pro'),
                'search_items' => __('Search Tags', 'wproject-contacts-pro'),
                'all_items' => __('All Tags', 'wproject-contacts-pro'),
                'edit_item' => __('Edit Tag', 'wproject-contacts-pro'),
                'update_item' => __('Update Tag', 'wproject-contacts-pro'),
                'add_new_item' => __('Add New Tag', 'wproject-contacts-pro'),
                'new_item_name' => __('New Tag Name', 'wproject-contacts-pro'),
                'menu_name' => __('Tags', 'wproject-contacts-pro'),
            ),
            'public' => false,
            'show_ui' => true,
            'show_admin_column' => false,
            'show_in_rest' => true,
            'rewrite' => false,
        ));
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_assets() {
        // CSS
        wp_enqueue_style(
            'wproject-contacts-pro',
            WPROJECT_CONTACTS_PRO_URL . 'assets/css/contacts-pro.css',
            array(),
            WPROJECT_CONTACTS_PRO_VERSION
        );
        
        // JavaScript
        wp_enqueue_script(
            'wproject-contacts-pro',
            WPROJECT_CONTACTS_PRO_URL . 'assets/js/contacts-pro.js',
            array('jquery'),
            WPROJECT_CONTACTS_PRO_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('wproject-contacts-pro', 'wpContactsPro', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wproject_contacts_pro_nonce'),
        ));
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets() {
        wp_enqueue_style(
            'wproject-contacts-pro-admin',
            WPROJECT_CONTACTS_PRO_URL . 'assets/css/admin.css',
            array(),
            WPROJECT_CONTACTS_PRO_VERSION
        );
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Check requirements
        if (version_compare(PHP_VERSION, '8.0', '<')) {
            die(__('wProject Contacts Pro requires PHP 8.0 or higher.', 'wproject-contacts-pro'));
        }

        // Load database class for activation
        require_once WPROJECT_CONTACTS_PRO_PATH . 'includes/class-database.php';
        require_once WPROJECT_CONTACTS_PRO_PATH . 'includes/class-page-handler.php';

        // Create database tables
        WProject_Contacts_Database::create_tables();

        // Flush rewrite rules for contacts page
        WProject_Contacts_Page_Handler::flush_rules();

        // Set version
        update_option('wproject_contacts_pro_version', WPROJECT_CONTACTS_PRO_VERSION);

        // Set activation flag for welcome redirect
        set_transient('wproject_contacts_pro_activated', true, 30);
    }
    
    /**
     * Plugin deactivation
     * Note: We don't drop tables - data persists
     */
    public function deactivate() {
        // Do nothing - preserve data
    }
}

// Initialize plugin
WProject_Contacts_Pro::get_instance();
