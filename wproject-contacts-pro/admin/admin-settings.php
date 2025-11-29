<?php
/**
 * Admin Settings for wProject Contacts Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

$options = get_option('wproject_contacts_pro_settings');
$enable_contacts = isset($options['enable_contacts']) ? $options['enable_contacts'] : 'yes';
$contacts_per_page = isset($options['contacts_per_page']) ? $options['contacts_per_page'] : 25;
$enable_company_import = isset($options['enable_company_import']) ? $options['enable_company_import'] : 'yes';
$enable_contact_export = isset($options['enable_contact_export']) ? $options['enable_contact_export'] : 'yes';
?>

<li data="contacts-pro" id="contacts-pro" <?php if(isset($_GET['section']) && $_GET['section'] == 'contacts-pro') { echo 'class="selected"'; } ?>>
    <img src="<?php echo WPROJECT_CONTACTS_PRO_URL; ?>assets/images/icon.svg" alt="Contacts Pro" />
    <?php _e('Contacts Pro', 'wproject-contacts-pro'); ?>
</li>

<div class="admin-box contacts-pro-settings" <?php if(!isset($_GET['section']) || $_GET['section'] != 'contacts-pro') { echo 'style="display:none;"'; } ?>>

    <h3>
        <i data-feather="users"></i>
        <?php _e('Contacts Pro Settings', 'wproject-contacts-pro'); ?>
    </h3>

    <p class="description"><?php _e('Configure your contacts and company management settings.', 'wproject-contacts-pro'); ?></p>

    <!-- General Settings -->
    <div class="setting-row">
        <label for="enable_contacts">
            <strong><?php _e('Enable Contacts', 'wproject-contacts-pro'); ?></strong>
            <em><?php _e('Show the Contacts section in the navigation menu.', 'wproject-contacts-pro'); ?></em>
        </label>
        <select name="enable_contacts" id="enable_contacts">
            <option value="yes" <?php selected($enable_contacts, 'yes'); ?>><?php _e('Yes', 'wproject-contacts-pro'); ?></option>
            <option value="no" <?php selected($enable_contacts, 'no'); ?>><?php _e('No', 'wproject-contacts-pro'); ?></option>
        </select>
    </div>

    <!-- Contacts Per Page -->
    <div class="setting-row">
        <label for="contacts_per_page">
            <strong><?php _e('Contacts Per Page', 'wproject-contacts-pro'); ?></strong>
            <em><?php _e('Number of contacts to display per page in the list view.', 'wproject-contacts-pro'); ?></em>
        </label>
        <input
            type="number"
            name="contacts_per_page"
            id="contacts_per_page"
            value="<?php echo esc_attr($contacts_per_page); ?>"
            min="10"
            max="100"
            step="5"
        />
    </div>

    <!-- Import/Export Settings -->
    <div class="setting-row">
        <label for="enable_company_import">
            <strong><?php _e('Enable Company Import', 'wproject-contacts-pro'); ?></strong>
            <em><?php _e('Allow importing contacts from CSV files.', 'wproject-contacts-pro'); ?></em>
        </label>
        <select name="enable_company_import" id="enable_company_import">
            <option value="yes" <?php selected($enable_company_import, 'yes'); ?>><?php _e('Yes', 'wproject-contacts-pro'); ?></option>
            <option value="no" <?php selected($enable_company_import, 'no'); ?>><?php _e('No', 'wproject-contacts-pro'); ?></option>
        </select>
    </div>

    <div class="setting-row">
        <label for="enable_contact_export">
            <strong><?php _e('Enable Contact Export', 'wproject-contacts-pro'); ?></strong>
            <em><?php _e('Allow exporting contacts to CSV files.', 'wproject-contacts-pro'); ?></em>
        </label>
        <select name="enable_contact_export" id="enable_contact_export">
            <option value="yes" <?php selected($enable_contact_export, 'yes'); ?>><?php _e('Yes', 'wproject-contacts-pro'); ?></option>
            <option value="no" <?php selected($enable_contact_export, 'no'); ?>><?php _e('No', 'wproject-contacts-pro'); ?></option>
        </select>
    </div>

    <!-- Database Info -->
    <div class="setting-row">
        <label>
            <strong><?php _e('Database Information', 'wproject-contacts-pro'); ?></strong>
            <em><?php _e('Current contact and company statistics.', 'wproject-contacts-pro'); ?></em>
        </label>
        <div class="database-stats">
            <?php
            global $wpdb;
            $companies_table = $wpdb->prefix . 'wproject_companies';
            $contacts_table = $wpdb->prefix . 'wproject_contacts';

            $company_count = $wpdb->get_var("SELECT COUNT(*) FROM $companies_table");
            $contact_count = $wpdb->get_var("SELECT COUNT(*) FROM $contacts_table");
            ?>
            <p>
                <i data-feather="briefcase"></i>
                <?php printf(__('Companies: %d', 'wproject-contacts-pro'), $company_count); ?>
            </p>
            <p>
                <i data-feather="users"></i>
                <?php printf(__('Contact Persons: %d', 'wproject-contacts-pro'), $contact_count); ?>
            </p>
        </div>
    </div>

    <!-- Plugin Info -->
    <div class="setting-row">
        <label>
            <strong><?php _e('Plugin Information', 'wproject-contacts-pro'); ?></strong>
        </label>
        <div class="plugin-info">
            <p><?php _e('Version:', 'wproject-contacts-pro'); ?> <strong><?php echo WPROJECT_CONTACTS_PRO_VERSION; ?></strong></p>
            <p><?php _e('Database Version:', 'wproject-contacts-pro'); ?> <strong><?php echo get_option('wproject_contacts_pro_version', '1.0.0'); ?></strong></p>
        </div>
    </div>

    <script>
        // Save settings via AJAX
        jQuery(document).ready(function($) {
            $('#contacts_per_page, #enable_contacts, #enable_company_import, #enable_contact_export').on('change', function() {
                var settingName = $(this).attr('name');
                var settingValue = $(this).val();

                // Save to wproject_contacts_pro_settings option
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'save_contacts_pro_setting',
                        setting_name: settingName,
                        setting_value: settingValue,
                        nonce: '<?php echo wp_create_nonce('wproject_contacts_pro_settings'); ?>'
                    },
                    success: function(response) {
                        // Show success indicator
                        var $input = $('[name="' + settingName + '"]');
                        $input.css('border-color', '#46b450');
                        setTimeout(function() {
                            $input.css('border-color', '');
                        }, 1000);
                    }
                });
            });
        });
    </script>

</div>
