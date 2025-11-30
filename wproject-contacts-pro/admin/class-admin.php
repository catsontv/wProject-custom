<?php
/**
 * Admin functionality for wProject Contacts Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

class WProject_Contacts_Admin {

    /**
     * Initialize admin functionality
     */
    public static function init() {
        // Add admin menu
        add_action('admin_menu', array(__CLASS__, 'add_admin_menu'));

        // Add to wProject admin navigation
        add_action('wproject_admin_pro_nav_start', array(__CLASS__, 'add_admin_nav_item'), 15);

        // Add to main frontend navigation
        add_action('nav_end', array(__CLASS__, 'add_frontend_nav_item'));
    }

    /**
     * Add admin menu page
     */
    public static function add_admin_menu() {
        $icon_url = WPROJECT_CONTACTS_PRO_URL . 'assets/images/admin-icon.svg';

        add_menu_page(
            __('Contacts Pro', 'wproject-contacts-pro'),
            __('Contacts Pro', 'wproject-contacts-pro'),
            'manage_options',
            'wproject-contacts-pro',
            array(__CLASS__, 'render_admin_page'),
            $icon_url,
            32
        );
    }

    /**
     * Add navigation item to wProject admin settings
     */
    public static function add_admin_nav_item() {
        $selected = (isset($_GET['section']) && $_GET['section'] == 'contacts-pro') ? 'class="selected"' : '';
        ?>
        <li data="contacts-pro" id="contacts-pro" <?php echo $selected; ?>>
            <img src="<?php echo WPROJECT_CONTACTS_PRO_URL; ?>assets/images/icon.svg" />
            <?php _e('Contacts Pro', 'wproject-contacts-pro'); ?>
        </li>
        <?php
    }

    /**
     * Add navigation item to main frontend navigation (after Team)
     */
    public static function add_frontend_nav_item() {
        $user = wp_get_current_user();
        $user_role = !empty($user->roles) ? $user->roles[0] : '';

        // Only show to authorized users
        if (in_array($user_role, array('administrator', 'project_manager', 'team_member', 'observer'))) {
            // Get the contacts page URL
            $contacts_url = home_url('/contacts/');
            ?>
            <li><a href="<?php echo esc_url($contacts_url); ?>"><i data-feather="user-check"></i><?php _e('Contacts', 'wproject-contacts-pro'); ?></a></li>
            <?php
        }
    }

    /**
     * Render admin page
     */
    public static function render_admin_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Contacts Pro', 'wproject-contacts-pro'); ?></h1>
            <div class="wproject-contacts-admin">
                <p><?php _e('Manage your contacts and companies from the frontend Contacts page.', 'wproject-contacts-pro'); ?></p>
                <p><a href="<?php echo home_url('/contacts/'); ?>" class="button button-primary"><?php _e('Go to Contacts Page', 'wproject-contacts-pro'); ?></a></p>
            </div>
        </div>
        <?php
    }

    /**
     * Add settings section to wProject admin
     */
    public static function add_settings_section() {
        ?>
        <div class="settings-div contacts-pro">
            <h3><?php _e('Contacts Pro', 'wproject-contacts-pro'); ?> <span>v<?php echo WPROJECT_CONTACTS_PRO_VERSION; ?></span></h3>
            <p><?php _e('Comprehensive contact and company management for wProject.', 'wproject-contacts-pro'); ?></p>
            <p><a href="<?php echo admin_url('admin.php?page=wproject-contacts-pro'); ?>" class="button"><?php _e('Manage Contacts', 'wproject-contacts-pro'); ?></a></p>
        </div>
        <?php
    }
}
