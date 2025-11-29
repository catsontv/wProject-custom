<?php
/*
Plugin Name:    Calendar Pro for wProject
Plugin URI:     https://rocketapps.com.au/product/calendar-pro/
Description:    Advanced calendar and event management for wProject with recurring events, reminders, and team collaboration.
Version:        1.0.3
Author:         Rocket Apps
Author URI:     https://rocketapps.com.au
Text Domain:    wproject-calendar-pro
Author Email:   support@rocketapps.com.au
Domain Path:    /languages/
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/* Look for translation file */
function wp_calendar_pro_textdomain() {
    load_plugin_textdomain( 'wproject-calendar-pro', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'wp_calendar_pro_textdomain' );

/* Constants */
define('CALENDAR_PRO_VERSION', '1.0.3');
define('CALENDAR_PRO_PLUGIN_PATH', plugin_dir_path( __FILE__ ));
define('CALENDAR_PRO_PLUGIN_URL', plugin_dir_url( __FILE__ ));

/* Add Calendar Pro interface menu item into admin */
function add_new_calendar_nav_item() { ?>
    <li data="calendar-pro" id="calendar-pro" <?php if(isset($_GET['section']) && $_GET['section'] == 'calendar-pro') { echo 'class="selected"'; } ?>>
        <img src="<?php echo plugins_url('/assets/images/icon.svg', __FILE__);?>" />
        <?php _e( 'Calendar Pro', 'wproject-calendar-pro' ); ?>
    </li>
<?php }
add_action('wproject_admin_pro_nav_start', 'add_new_calendar_nav_item', 5);

/* wProject theme version check */
function wproject_theme_version_check_calendar() {
    $wproject_theme = wp_get_theme();
    $theme_version  = $wproject_theme->get( 'Version' );
    return $theme_version;
}

/* Get plugin version */
function calendarPluginVersion() {
    $plugin_data = get_plugin_data( __FILE__ );
    $plugin_version = $plugin_data['Version'];
    return $plugin_version;
}

/* Add Calendar Pro settings div into admin */
function add_new_calendar_settings() {
    require_once( CALENDAR_PRO_PLUGIN_PATH . 'admin/admin-settings.php' );
}
add_action('wproject_admin_settings_div_end', 'add_new_calendar_settings');

/* Enqueue frontend scripts and styles */
function calendar_pro_scripts() {
    if(function_exists('wProject')) {
        // FullCalendar library (required dependency)
        wp_enqueue_style(
            'fullcalendar',
            'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css',
            array(),
            '6.1.10'
        );

        wp_enqueue_script(
            'fullcalendar',
            'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js',
            array(),
            '6.1.10',
            true
        );

        // Calendar Pro styles
        wp_enqueue_style('calendar_pro_css', CALENDAR_PRO_PLUGIN_URL . 'assets/css/calendar.css', array('fullcalendar'), CALENDAR_PRO_VERSION);

        // Add dark mode styles conditionally
        $dark_mode = isset(user_details()['dark_mode']) ? user_details()['dark_mode'] : '';
        if($dark_mode == 'yes') {
            wp_enqueue_style('calendar_pro_css_dark', CALENDAR_PRO_PLUGIN_URL . 'assets/css/calendar-dark.css', array('calendar_pro_css'), CALENDAR_PRO_VERSION);
        }

        // Calendar Pro JavaScript
        wp_enqueue_script('calendar_pro_js', CALENDAR_PRO_PLUGIN_URL . 'assets/js/calendar.js', array('jquery', 'fullcalendar'), CALENDAR_PRO_VERSION, true);

        // Localize script with AJAX parameters
        $parameters = array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('calendar_inputs')
        );
        wp_localize_script('calendar_pro_js', 'calendar_inputs', $parameters);
    }
}
add_action('wp_enqueue_scripts', 'calendar_pro_scripts');

/* Enqueue admin scripts and styles */
function calendar_pro_admin_scripts( $hook ) {
    if ( 'toplevel_page_wproject-calendar-pro' === $hook ) {
        wp_enqueue_style( 'calendar_pro_admin_css', CALENDAR_PRO_PLUGIN_URL . 'assets/css/calendar-admin.css', array(), CALENDAR_PRO_VERSION );
        wp_enqueue_script( 'calendar_pro_admin_js', CALENDAR_PRO_PLUGIN_URL . 'assets/js/calendar-admin.js', array('jquery'), CALENDAR_PRO_VERSION, true );
    }

    // Enqueue color picker for settings page
    if ( strpos($hook, 'wproject') !== false ) {
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'calendar-settings-handle', CALENDAR_PRO_PLUGIN_URL . 'assets/js/calendar-admin.js', array( 'wp-color-picker' ), CALENDAR_PRO_VERSION, true );
    }
}
add_action( 'admin_enqueue_scripts', 'calendar_pro_admin_scripts' );

/* Include core classes */
require_once( CALENDAR_PRO_PLUGIN_PATH . 'includes/class-calendar-core.php' );
require_once( CALENDAR_PRO_PLUGIN_PATH . 'includes/class-event-manager.php' );
require_once( CALENDAR_PRO_PLUGIN_PATH . 'includes/class-calendar-manager.php' );
require_once( CALENDAR_PRO_PLUGIN_PATH . 'includes/class-permissions.php' );
require_once( CALENDAR_PRO_PLUGIN_PATH . 'includes/class-recurring-events.php' );
require_once( CALENDAR_PRO_PLUGIN_PATH . 'includes/class-reminders.php' );
require_once( CALENDAR_PRO_PLUGIN_PATH . 'includes/class-sharing.php' );
require_once( CALENDAR_PRO_PLUGIN_PATH . 'includes/class-meetings.php' );
require_once( CALENDAR_PRO_PLUGIN_PATH . 'includes/class-trash-manager.php' );
require_once( CALENDAR_PRO_PLUGIN_PATH . 'includes/class-task-calendar-integration.php' );

/* Initialize Calendar Core */
function calendar_pro_init() {
    if ( class_exists( 'WProject_Calendar_Core' ) ) {
        WProject_Calendar_Core::get_instance();
    }
}
add_action( 'init', 'calendar_pro_init' );

/* Activation hook */
register_activation_hook( __FILE__, 'calendar_pro_activate' );
function calendar_pro_activate() {
    if ( class_exists( 'WProject_Calendar_Core' ) ) {
        WProject_Calendar_Core::activate();
    }
}

/* Deactivation hook */
register_deactivation_hook( __FILE__, 'calendar_pro_deactivate' );
function calendar_pro_deactivate() {
    if ( class_exists( 'WProject_Calendar_Core' ) ) {
        WProject_Calendar_Core::deactivate();
    }
}

/* AJAX Handlers */
require_once( CALENDAR_PRO_PLUGIN_PATH . 'includes/ajax-handlers.php' );

/* Display calendar on dashboard */
function calendar_pro_display_dashboard() {
    if ( ! function_exists('wProject') ) {
        return;
    }

    $options = get_option( 'wproject_settings' );
    $calendar_show_dashboard = isset($options['calendar_show_dashboard']) ? $options['calendar_show_dashboard'] : '';

    if ( $calendar_show_dashboard ) {
        include CALENDAR_PRO_PLUGIN_PATH . 'templates/calendar-view.php';
    }
}
add_action( 'gantt_pro_dashboard_page', 'calendar_pro_display_dashboard', 5 );

/* Display calendar on project pages */
function calendar_pro_display_project() {
    if ( ! function_exists('wProject') ) {
        return;
    }

    $options = get_option( 'wproject_settings' );
    $calendar_show_project = isset($options['calendar_show_project']) ? $options['calendar_show_project'] : '';

    if ( $calendar_show_project ) {
        // Detect current project context
        $current_project = get_queried_object();
        $project_id = null;
        $project_name = null;

        if ( $current_project && isset( $current_project->term_id ) && $current_project->taxonomy === 'project' ) {
            $project_id = $current_project->term_id;
            $project_name = $current_project->name;
        }

        include CALENDAR_PRO_PLUGIN_PATH . 'templates/calendar-view.php';
    }
}
add_action( 'gantt_pro_project_page', 'calendar_pro_display_project', 5 );
