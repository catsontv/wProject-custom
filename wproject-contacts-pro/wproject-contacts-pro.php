<?php
/**
 * Plugin Name: wProject Contacts Pro
 * Plugin URI: https://rocketapps.com.au/wproject-contacts-pro/
 * Description: Comprehensive contact and company management system for wProject theme
 * Version: 1.0.0
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
define('WPROJECT_CONTACTS_PRO_VERSION', '1.0.0');
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

        // Phase 2: Navigation integration
        add_filter('body_class', array($this, 'add_body_class'));
        add_action('template_redirect', array($this, 'contacts_page_template'));

        // Phase 2: Settings page
        if (is_admin()) {
            require_once WPROJECT_CONTACTS_PRO_PATH . 'admin/settings.php';
        }
    }
    
    /**
     * Include required files
     */
    private function includes() {
        require_once WPROJECT_CONTACTS_PRO_PATH . 'includes/class-database.php';
        require_once WPROJECT_CONTACTS_PRO_PATH . 'includes/class-company.php';
        require_once WPROJECT_CONTACTS_PRO_PATH . 'includes/class-contact.php';
        require_once WPROJECT_CONTACTS_PRO_PATH . 'includes/class-ajax-handlers.php';
        
        // Initialize AJAX handlers
        WProject_Contacts_Ajax::init();
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
        
        // Create database tables
        WProject_Contacts_Database::create_tables();
        
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

    /**
     * Add body class for contacts page
     */
    public function add_body_class($classes) {
        if ($this->is_contacts_page()) {
            $classes[] = 'contacts-page';
        }
        return $classes;
    }

    /**
     * Check if current page is contacts page
     */
    private function is_contacts_page() {
        $current_url = trim($_SERVER['REQUEST_URI'], '/');
        $home_path = trim(parse_url(home_url(), PHP_URL_PATH), '/');
        if ($home_path) {
            $current_url = str_replace($home_path, '', $current_url);
            $current_url = ltrim($current_url, '/');
        }
        return (str_starts_with($current_url, 'contacts'));
    }

    /**
     * Load contacts page template
     */
    public function contacts_page_template() {
        if ($this->is_contacts_page()) {
            // Check if user is logged in
            if (!is_user_logged_in()) {
                auth_redirect();
                exit;
            }

            // Load contacts list template
            include(WPROJECT_CONTACTS_PRO_PATH . 'templates/contact-list.php');
            exit;
        }
    }
}

// Initialize plugin
WProject_Contacts_Pro::get_instance();

/**
 * Integration Hooks
 */

// Add Contacts Pro menu item to wProject admin
function wproject_contacts_pro_admin_nav() {
    ?>
    <li data="contacts-pro" id="contacts-pro" <?php if(isset($_GET['section']) && $_GET['section'] == 'contacts-pro') { echo 'class="selected"'; } ?>>
        <img src="<?php echo WPROJECT_CONTACTS_PRO_URL; ?>assets/images/icon.svg" alt="Contacts Pro" />
        <?php _e('Contacts Pro', 'wproject-contacts-pro'); ?>
    </li>
    <?php
}
add_action('wproject_admin_pro_nav_start', 'wproject_contacts_pro_admin_nav', 5);

// Add Contacts Pro settings panel to wProject admin
function wproject_contacts_pro_admin_settings() {
    require_once WPROJECT_CONTACTS_PRO_PATH . 'admin/admin-settings.php';
}
add_action('wproject_admin_settings_div_end', 'wproject_contacts_pro_admin_settings');

// Display contacts page on frontend
function wproject_contacts_pro_display_page() {
    if (!function_exists('wProject')) {
        return;
    }

    if (is_page('contacts')) {
        include WPROJECT_CONTACTS_PRO_PATH . 'templates/contacts-list.php';
    }
}
add_action('page_end', 'wproject_contacts_pro_display_page', 5);

// Add Contacts navigation item to main sidebar
function wproject_contacts_pro_sidebar_nav() {
    $options = get_option('wproject_contacts_pro_settings');
    $enable_contacts = isset($options['enable_contacts']) ? $options['enable_contacts'] : 'yes';

    if ($enable_contacts === 'yes') {
        $contacts_page = get_page_by_path('contacts');
        if ($contacts_page) {
            $contacts_url = get_permalink($contacts_page->ID);
            $current_class = is_page('contacts') ? ' class="selected"' : '';
            ?>
            <li<?php echo $current_class; ?>>
                <a href="<?php echo esc_url($contacts_url); ?>">
                    <i data-feather="users"></i>
                    <?php _e('Contacts', 'wproject-contacts-pro'); ?>
                </a>
            </li>
            <?php
        }
    }
}
add_action('side_nav', 'wproject_contacts_pro_sidebar_nav', 15);

// Add Contact option to CREATE button dropdown
function wproject_contacts_pro_create_menu() {
    $options = get_option('wproject_contacts_pro_settings');
    $enable_contacts = isset($options['enable_contacts']) ? $options['enable_contacts'] : 'yes';

    if ($enable_contacts === 'yes') {
        ?>
        <li class="create-contact">
            <i data-feather="user-plus"></i>
            <span><?php _e('Contact', 'wproject-contacts-pro'); ?></span>
        </li>
        <?php
    }
}
add_action('wproject_create_menu_end', 'wproject_contacts_pro_create_menu', 10);
