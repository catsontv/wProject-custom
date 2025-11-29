<?php
/**
 * Settings Page Integration with wProject
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add Contacts Pro to wProject Pro Addons menu
 */
function contacts_pro_add_settings_nav() {
    ?>
    <li data="contacts-pro" id="contacts-pro">
        <i data-feather="users"></i>
        <?php _e('Contacts Pro', 'wproject-contacts-pro'); ?>
    </li>
    <?php
}
add_action('wproject_admin_pro_nav_start', 'contacts_pro_add_settings_nav', 3);

/**
 * Add Contacts Pro settings content
 */
function contacts_pro_add_settings_content() {
    // Get current settings
    $settings = get_option('wproject_contacts_pro_settings', array());

    // Default settings
    $defaults = array(
        'default_company_type' => 'client',
        'require_photo' => false,
        'gravatar_fallback' => true,
        'id_passport_visibility' => 'admins_only',
        'visible_columns' => array('photo', 'company', 'contact', 'email', 'phone', 'role'),
        'predefined_roles' => array('CEO', 'CFO', 'CTO', 'Manager', 'Director', 'Coordinator', 'Assistant', 'Other'),
        'notify_project_link' => true,
        'notify_task_link' => true,
        'notify_event_invite' => true,
        'email_sender_name' => get_bloginfo('name'),
        'email_sender_email' => get_bloginfo('admin_email'),
        'csv_delimiter' => ',',
        'csv_qualifier' => '"',
        'prevent_duplicates' => true,
        'duplicate_field' => 'email',
        'import_batch_size' => 100,
    );

    $settings = wp_parse_args($settings, $defaults);
    ?>

    <div class="settings-div contacts-pro" id="contacts-pro-settings">
        <h3>
            <i data-feather="users"></i>
            <?php _e('Contacts Pro', 'wproject-contacts-pro'); ?>
            <span>v<?php echo WPROJECT_CONTACTS_PRO_VERSION; ?></span>
        </h3>

        <div class="settings-content">

            <!-- Settings Tabs -->
            <div class="wproject-tabs">
                <li class="active" data-tab="general"><?php _e('General', 'wproject-contacts-pro'); ?></li>
                <li data-tab="columns"><?php _e('Columns', 'wproject-contacts-pro'); ?></li>
                <li data-tab="roles"><?php _e('Roles', 'wproject-contacts-pro'); ?></li>
                <li data-tab="notifications"><?php _e('Notifications', 'wproject-contacts-pro'); ?></li>
                <li data-tab="import-export"><?php _e('Import/Export', 'wproject-contacts-pro'); ?></li>
            </div>

            <div class="wproject-dash-box-container">

                <!-- General Settings Tab -->
                <section class="active" id="tab-general">
                    <h2><?php _e('General Settings', 'wproject-contacts-pro'); ?></h2>

                    <form id="contacts-pro-general-settings" class="contacts-settings-form">

                        <p>
                            <label for="default_company_type"><?php _e('Default Company Type', 'wproject-contacts-pro'); ?></label>
                            <span><?php _e('The default type when creating a new company', 'wproject-contacts-pro'); ?></span>
                            <select id="default_company_type" name="default_company_type">
                                <option value="client" <?php selected($settings['default_company_type'], 'client'); ?>><?php _e('Client', 'wproject-contacts-pro'); ?></option>
                                <option value="vendor" <?php selected($settings['default_company_type'], 'vendor'); ?>><?php _e('Vendor', 'wproject-contacts-pro'); ?></option>
                                <option value="partner" <?php selected($settings['default_company_type'], 'partner'); ?>><?php _e('Partner', 'wproject-contacts-pro'); ?></option>
                                <option value="other" <?php selected($settings['default_company_type'], 'other'); ?>><?php _e('Other', 'wproject-contacts-pro'); ?></option>
                            </select>
                        </p>

                        <p>
                            <label>
                                <input type="checkbox" id="require_photo" name="require_photo" <?php checked($settings['require_photo'], true); ?> />
                                <?php _e('Require Photo Upload', 'wproject-contacts-pro'); ?>
                            </label>
                            <span><?php _e('Make contact photo mandatory when adding contacts', 'wproject-contacts-pro'); ?></span>
                        </p>

                        <p>
                            <label>
                                <input type="checkbox" id="gravatar_fallback" name="gravatar_fallback" <?php checked($settings['gravatar_fallback'], true); ?> />
                                <?php _e('Use Gravatar Fallback', 'wproject-contacts-pro'); ?>
                            </label>
                            <span><?php _e('Use Gravatar if no photo is uploaded', 'wproject-contacts-pro'); ?></span>
                        </p>

                        <p>
                            <label for="id_passport_visibility"><?php _e('ID/Passport Visibility', 'wproject-contacts-pro'); ?></label>
                            <span><?php _e('Who can view ID numbers and passport information', 'wproject-contacts-pro'); ?></span>
                            <select id="id_passport_visibility" name="id_passport_visibility">
                                <option value="admins_only" <?php selected($settings['id_passport_visibility'], 'admins_only'); ?>><?php _e('Admins Only', 'wproject-contacts-pro'); ?></option>
                                <option value="admins_pms" <?php selected($settings['id_passport_visibility'], 'admins_pms'); ?>><?php _e('Admins & PMs', 'wproject-contacts-pro'); ?></option>
                                <option value="all" <?php selected($settings['id_passport_visibility'], 'all'); ?>><?php _e('All Users', 'wproject-contacts-pro'); ?></option>
                            </select>
                        </p>

                        <p>
                            <input type="submit" value="<?php _e('Save General Settings', 'wproject-contacts-pro'); ?>" />
                        </p>
                    </form>
                </section>

                <!-- Column Visibility Tab -->
                <section id="tab-columns">
                    <h2><?php _e('Column Visibility', 'wproject-contacts-pro'); ?></h2>
                    <p><?php _e('Choose which columns to display in the contacts list', 'wproject-contacts-pro'); ?></p>

                    <form id="contacts-pro-columns-settings" class="contacts-settings-form">
                        <?php
                        $all_columns = array(
                            'photo' => __('Photo', 'wproject-contacts-pro'),
                            'company' => __('Company', 'wproject-contacts-pro'),
                            'contact' => __('Contact Name', 'wproject-contacts-pro'),
                            'role' => __('Role/Title', 'wproject-contacts-pro'),
                            'department' => __('Department', 'wproject-contacts-pro'),
                            'email' => __('Email', 'wproject-contacts-pro'),
                            'phone' => __('Phone', 'wproject-contacts-pro'),
                            'tags' => __('Tags', 'wproject-contacts-pro'),
                            'date_added' => __('Date Added', 'wproject-contacts-pro'),
                        );

                        foreach ($all_columns as $column_key => $column_label):
                            $checked = in_array($column_key, $settings['visible_columns']);
                        ?>
                        <p>
                            <label>
                                <input type="checkbox" name="visible_columns[]" value="<?php echo esc_attr($column_key); ?>" <?php checked($checked, true); ?> />
                                <?php echo esc_html($column_label); ?>
                            </label>
                        </p>
                        <?php endforeach; ?>

                        <p>
                            <input type="submit" value="<?php _e('Save Column Settings', 'wproject-contacts-pro'); ?>" />
                        </p>
                    </form>
                </section>

                <!-- Roles Configuration Tab -->
                <section id="tab-roles">
                    <h2><?php _e('Role Configuration', 'wproject-contacts-pro'); ?></h2>
                    <p><?php _e('Manage predefined role options for contacts', 'wproject-contacts-pro'); ?></p>

                    <form id="contacts-pro-roles-settings" class="contacts-settings-form">
                        <div id="roles-list">
                            <?php foreach ($settings['predefined_roles'] as $index => $role): ?>
                            <p class="role-item">
                                <input type="text" name="predefined_roles[]" value="<?php echo esc_attr($role); ?>" />
                                <button type="button" class="remove-role-btn">
                                    <i data-feather="minus-circle"></i>
                                    <?php _e('Remove', 'wproject-contacts-pro'); ?>
                                </button>
                            </p>
                            <?php endforeach; ?>
                        </div>

                        <p>
                            <button type="button" id="add-role-btn" class="wproject-button">
                                <i data-feather="plus"></i>
                                <?php _e('Add Role', 'wproject-contacts-pro'); ?>
                            </button>
                        </p>

                        <p>
                            <input type="submit" value="<?php _e('Save Roles', 'wproject-contacts-pro'); ?>" />
                        </p>
                    </form>
                </section>

                <!-- Notifications Tab -->
                <section id="tab-notifications">
                    <h2><?php _e('Notification Settings', 'wproject-contacts-pro'); ?></h2>
                    <p><?php _e('Configure email notifications', 'wproject-contacts-pro'); ?></p>

                    <form id="contacts-pro-notifications-settings" class="contacts-settings-form">

                        <h3><?php _e('Notification Triggers', 'wproject-contacts-pro'); ?></h3>

                        <p>
                            <label>
                                <input type="checkbox" id="notify_project_link" name="notify_project_link" <?php checked($settings['notify_project_link'], true); ?> />
                                <?php _e('Notify when contact is linked to a project', 'wproject-contacts-pro'); ?>
                            </label>
                        </p>

                        <p>
                            <label>
                                <input type="checkbox" id="notify_task_link" name="notify_task_link" <?php checked($settings['notify_task_link'], true); ?> />
                                <?php _e('Notify when contact is linked to a task', 'wproject-contacts-pro'); ?>
                            </label>
                        </p>

                        <p>
                            <label>
                                <input type="checkbox" id="notify_event_invite" name="notify_event_invite" <?php checked($settings['notify_event_invite'], true); ?> />
                                <?php _e('Notify when contact is invited to an event', 'wproject-contacts-pro'); ?>
                            </label>
                        </p>

                        <h3><?php _e('Email Sender', 'wproject-contacts-pro'); ?></h3>

                        <p>
                            <label for="email_sender_name"><?php _e('Sender Name', 'wproject-contacts-pro'); ?></label>
                            <input type="text" id="email_sender_name" name="email_sender_name" value="<?php echo esc_attr($settings['email_sender_name']); ?>" />
                        </p>

                        <p>
                            <label for="email_sender_email"><?php _e('Sender Email', 'wproject-contacts-pro'); ?></label>
                            <input type="email" id="email_sender_email" name="email_sender_email" value="<?php echo esc_attr($settings['email_sender_email']); ?>" />
                        </p>

                        <p>
                            <input type="submit" value="<?php _e('Save Notification Settings', 'wproject-contacts-pro'); ?>" />
                        </p>
                    </form>
                </section>

                <!-- Import/Export Tab -->
                <section id="tab-import-export">
                    <h2><?php _e('Import/Export Settings', 'wproject-contacts-pro'); ?></h2>
                    <p><?php _e('Configure CSV import and export behavior', 'wproject-contacts-pro'); ?></p>

                    <form id="contacts-pro-import-export-settings" class="contacts-settings-form">

                        <h3><?php _e('CSV Format', 'wproject-contacts-pro'); ?></h3>

                        <p>
                            <label for="csv_delimiter"><?php _e('Field Delimiter', 'wproject-contacts-pro'); ?></label>
                            <select id="csv_delimiter" name="csv_delimiter">
                                <option value="," <?php selected($settings['csv_delimiter'], ','); ?>><?php _e('Comma (,)', 'wproject-contacts-pro'); ?></option>
                                <option value=";" <?php selected($settings['csv_delimiter'], ';'); ?>><?php _e('Semicolon (;)', 'wproject-contacts-pro'); ?></option>
                                <option value="\t" <?php selected($settings['csv_delimiter'], "\t"); ?>><?php _e('Tab', 'wproject-contacts-pro'); ?></option>
                            </select>
                        </p>

                        <p>
                            <label for="csv_qualifier"><?php _e('Text Qualifier', 'wproject-contacts-pro'); ?></label>
                            <select id="csv_qualifier" name="csv_qualifier">
                                <option value='"' <?php selected($settings['csv_qualifier'], '"'); ?>><?php _e('Double Quote (")', 'wproject-contacts-pro'); ?></option>
                                <option value="'" <?php selected($settings['csv_qualifier'], "'"); ?>><?php _e('Single Quote (\')', 'wproject-contacts-pro'); ?></option>
                            </select>
                        </p>

                        <h3><?php _e('Import Behavior', 'wproject-contacts-pro'); ?></h3>

                        <p>
                            <label>
                                <input type="checkbox" id="prevent_duplicates" name="prevent_duplicates" <?php checked($settings['prevent_duplicates'], true); ?> />
                                <?php _e('Prevent Duplicate Imports', 'wproject-contacts-pro'); ?>
                            </label>
                        </p>

                        <p>
                            <label for="duplicate_field"><?php _e('Duplicate Detection Field', 'wproject-contacts-pro'); ?></label>
                            <select id="duplicate_field" name="duplicate_field">
                                <option value="email" <?php selected($settings['duplicate_field'], 'email'); ?>><?php _e('Email Address', 'wproject-contacts-pro'); ?></option>
                                <option value="name" <?php selected($settings['duplicate_field'], 'name'); ?>><?php _e('Full Name', 'wproject-contacts-pro'); ?></option>
                                <option value="id_number" <?php selected($settings['duplicate_field'], 'id_number'); ?>><?php _e('ID Number', 'wproject-contacts-pro'); ?></option>
                            </select>
                        </p>

                        <p>
                            <label for="import_batch_size"><?php _e('Import Batch Size', 'wproject-contacts-pro'); ?></label>
                            <select id="import_batch_size" name="import_batch_size">
                                <option value="50" <?php selected($settings['import_batch_size'], 50); ?>>50</option>
                                <option value="100" <?php selected($settings['import_batch_size'], 100); ?>>100</option>
                                <option value="200" <?php selected($settings['import_batch_size'], 200); ?>>200</option>
                                <option value="500" <?php selected($settings['import_batch_size'], 500); ?>>500</option>
                            </select>
                        </p>

                        <p>
                            <input type="submit" value="<?php _e('Save Import/Export Settings', 'wproject-contacts-pro'); ?>" />
                        </p>
                    </form>
                </section>

            </div>
        </div>
    </div>

    <script>
    // Initialize Feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    </script>

    <?php
}
add_action('wproject_admin_pro_nav_end', 'contacts_pro_add_settings_content');

/**
 * Save settings via AJAX
 */
function contacts_pro_save_settings() {
    // Verify nonce
    check_ajax_referer('wproject_contacts_pro_nonce', 'nonce');

    // Check capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Insufficient permissions', 'wproject-contacts-pro')));
    }

    // Get current settings
    $settings = get_option('wproject_contacts_pro_settings', array());

    // Update settings based on which form was submitted
    $form_type = sanitize_text_field($_POST['form_type'] ?? '');

    switch ($form_type) {
        case 'general':
            $settings['default_company_type'] = sanitize_text_field($_POST['default_company_type'] ?? 'client');
            $settings['require_photo'] = isset($_POST['require_photo']);
            $settings['gravatar_fallback'] = isset($_POST['gravatar_fallback']);
            $settings['id_passport_visibility'] = sanitize_text_field($_POST['id_passport_visibility'] ?? 'admins_only');
            break;

        case 'columns':
            $settings['visible_columns'] = array_map('sanitize_text_field', $_POST['visible_columns'] ?? array());
            break;

        case 'roles':
            $settings['predefined_roles'] = array_map('sanitize_text_field', $_POST['predefined_roles'] ?? array());
            break;

        case 'notifications':
            $settings['notify_project_link'] = isset($_POST['notify_project_link']);
            $settings['notify_task_link'] = isset($_POST['notify_task_link']);
            $settings['notify_event_invite'] = isset($_POST['notify_event_invite']);
            $settings['email_sender_name'] = sanitize_text_field($_POST['email_sender_name'] ?? '');
            $settings['email_sender_email'] = sanitize_email($_POST['email_sender_email'] ?? '');
            break;

        case 'import_export':
            $settings['csv_delimiter'] = sanitize_text_field($_POST['csv_delimiter'] ?? ',');
            $settings['csv_qualifier'] = sanitize_text_field($_POST['csv_qualifier'] ?? '"');
            $settings['prevent_duplicates'] = isset($_POST['prevent_duplicates']);
            $settings['duplicate_field'] = sanitize_text_field($_POST['duplicate_field'] ?? 'email');
            $settings['import_batch_size'] = intval($_POST['import_batch_size'] ?? 100);
            break;
    }

    // Save settings
    update_option('wproject_contacts_pro_settings', $settings);

    wp_send_json_success(array('message' => __('Settings saved successfully', 'wproject-contacts-pro')));
}
add_action('wp_ajax_contacts_pro_save_settings', 'contacts_pro_save_settings');
