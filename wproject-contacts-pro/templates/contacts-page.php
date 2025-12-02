<?php
/**
 * Template for displaying contacts page
 *
 * Version 2.0.8 - Fixed repeater field buttons
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get current user
$user = wp_get_current_user();
$user_role = !empty($user->roles) ? $user->roles[0] : '';

// Check permissions
if (!in_array($user_role, array('administrator', 'project_manager', 'team_member', 'observer'))) {
    wp_die(__('You do not have permission to access this page.', 'wproject-contacts-pro'));
}

// Get wProject settings for role configuration
$wproject_settings = wProject();

// Predefined roles
$predefined_roles = array(
    'CEO', 'CFO', 'CTO', 'COO',
    'President', 'Vice President', 'Director', 'Manager',
    'Developer', 'Designer', 'Consultant', 'Accountant',
    'Sales', 'Marketing', 'Support', 'Other'
);

get_header();
?>

<div class="container">

    <?php get_template_part('inc/left'); ?>

    <!--/ Start Section /-->
    <section class="middle contacts">

        <h1><?php _e('Contacts', 'wproject-contacts-pro'); ?></h1>

        <?php do_action('page_start'); ?>

        <div class="wproject-contacts-page">
    <div class="contacts-header">
        <div class="contacts-actions">
            <button id="add-contact-btn" class="button button-primary">
                <i data-feather="user-plus"></i>
                <?php _e('Add Contact', 'wproject-contacts-pro'); ?>
            </button>
            <button id="add-company-btn" class="button">
                <i data-feather="briefcase"></i>
                <?php _e('Add Company', 'wproject-contacts-pro'); ?>
            </button>
            <button id="bulk-actions-btn" class="button" style="display:none;">
                <i data-feather="check-square"></i>
                <?php _e('Bulk Actions', 'wproject-contacts-pro'); ?>
            </button>
        </div>
    </div>

    <div class="contacts-filters">
        <div class="filter-tabs">
            <button class="filter-tab active" data-filter="all"><?php _e('All', 'wproject-contacts-pro'); ?></button>
            <button class="filter-tab" data-filter="contacts"><?php _e('Contacts', 'wproject-contacts-pro'); ?></button>
            <button class="filter-tab" data-filter="companies"><?php _e('Companies', 'wproject-contacts-pro'); ?></button>
        </div>
        <div class="filter-options">
            <select id="company-filter" class="filter-select">
                <option value=""><?php _e('All Companies', 'wproject-contacts-pro'); ?></option>
            </select>
            <select id="tag-filter" class="filter-select">
                <option value=""><?php _e('All Tags', 'wproject-contacts-pro'); ?></option>
            </select>
        </div>
        <div class="search-box">
            <input type="text" id="contacts-search" placeholder="<?php _e('Search contacts and companies...', 'wproject-contacts-pro'); ?>">
            <i data-feather="search"></i>
        </div>
    </div>

    <div class="contacts-grid" id="contacts-grid">
        <div class="loading"><?php _e('Loading contacts...', 'wproject-contacts-pro'); ?></div>
    </div>

    <!-- Add Contact Modal -->
    <div id="add-contact-modal" class="wpc-modal" style="display: none;">
        <div class="wpc-modal-content">
            <div class="wpc-modal-header">
                <h2><?php _e('Add Contact', 'wproject-contacts-pro'); ?></h2>
                <button class="wpc-modal-close" data-modal="add-contact-modal">
                    <i data-feather="x"></i>
                </button>
            </div>
            <div class="wpc-modal-body">
                <form id="add-contact-form" class="general-form" action="javascript:void(0);" method="post">

                    <fieldset>
                        <legend><?php _e('Basic Information', 'wproject-contacts-pro'); ?></legend>
                        <ul>
                            <li class="split-2">
                                <div>
                                    <label><?php _e('First Name', 'wproject-contacts-pro'); ?> *</label>
                                    <input type="text" id="contact-first-name" name="first_name" required>
                                </div>
                                <div>
                                    <label><?php _e('Last Name', 'wproject-contacts-pro'); ?> *</label>
                                    <input type="text" id="contact-last-name" name="last_name" required>
                                </div>
                            </li>
                            <li>
                                <label><?php _e('Company', 'wproject-contacts-pro'); ?></label>
                                <select id="contact-company" name="company_id">
                                    <option value=""><?php _e('Select Company', 'wproject-contacts-pro'); ?></option>
                                </select>
                            </li>
                            <li class="split-2">
                                <div>
                                    <label><?php _e('Role/Position', 'wproject-contacts-pro'); ?></label>
                                    <select id="contact-role-select" name="role">
                                        <option value=""><?php _e('Select Role', 'wproject-contacts-pro'); ?></option>
                                        <?php foreach ($predefined_roles as $role) : ?>
                                            <option value="<?php echo esc_attr($role); ?>"><?php echo esc_html($role); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label><?php _e('Department', 'wproject-contacts-pro'); ?></label>
                                    <input type="text" id="contact-department" name="department">
                                </div>
                            </li>
                        </ul>
                    </fieldset>

                    <fieldset>
                        <legend><?php _e('Email Addresses', 'wproject-contacts-pro'); ?></legend>
                        <ul id="email-fields-container">
                            <li class="email-field-group" data-index="0">
                                <div class="repeater-field">
                                    <div class="field-row">
                                        <div class="field-input">
                                            <input type="email" name="emails[0][email]" placeholder="<?php _e('Email Address', 'wproject-contacts-pro'); ?>">
                                        </div>
                                        <div class="field-label">
                                            <select name="emails[0][label]">
                                                <option value="work"><?php _e('Work', 'wproject-contacts-pro'); ?></option>
                                                <option value="personal"><?php _e('Personal', 'wproject-contacts-pro'); ?></option>
                                                <option value="assistant"><?php _e('Assistant', 'wproject-contacts-pro'); ?></option>
                                                <option value="other"><?php _e('Other', 'wproject-contacts-pro'); ?></option>
                                            </select>
                                        </div>
                                        <div class="field-preferred">
                                            <label>
                                                <input type="checkbox" name="emails[0][is_preferred]" value="1" class="email-preferred" checked>
                                                <span><?php _e('Preferred', 'wproject-contacts-pro'); ?></span>
                                            </label>
                                        </div>
                                        <div class="field-remove">
                                            <button type="button" class="remove-email" style="display:none;" title="Remove">
                                                ✕
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                        <button type="button" id="add-email-btn" class="button-secondary">
                            <i data-feather="plus"></i> <?php _e('Add Email', 'wproject-contacts-pro'); ?>
                        </button>
                    </fieldset>

                    <fieldset>
                        <legend><?php _e('Cell/Mobile Phones', 'wproject-contacts-pro'); ?></legend>
                        <ul id="cell-phone-fields-container">
                            <li class="cell-phone-field-group" data-index="0">
                                <div class="repeater-field">
                                    <div class="field-row">
                                        <div class="field-input">
                                            <input type="tel" name="cell_phones[0][phone_number]" placeholder="<?php _e('Mobile Number', 'wproject-contacts-pro'); ?>">
                                        </div>
                                        <div class="field-label">
                                            <select name="cell_phones[0][label]">
                                                <option value="mobile"><?php _e('Mobile', 'wproject-contacts-pro'); ?></option>
                                                <option value="assistant_mobile"><?php _e('Assistant Mobile', 'wproject-contacts-pro'); ?></option>
                                                <option value="other"><?php _e('Other', 'wproject-contacts-pro'); ?></option>
                                            </select>
                                        </div>
                                        <div class="field-preferred">
                                            <label>
                                                <input type="checkbox" name="cell_phones[0][is_preferred]" value="1" class="cell-phone-preferred" checked>
                                                <span><?php _e('Preferred', 'wproject-contacts-pro'); ?></span>
                                            </label>
                                        </div>
                                        <div class="field-remove">
                                            <button type="button" class="remove-cell-phone" style="display:none;" title="Remove">
                                                ✕
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                        <button type="button" id="add-cell-phone-btn" class="button-secondary">
                            <i data-feather="plus"></i> <?php _e('Add Cell Phone', 'wproject-contacts-pro'); ?>
                        </button>
                    </fieldset>

                    <fieldset>
                        <legend><?php _e('Local/Office Phones', 'wproject-contacts-pro'); ?></legend>
                        <ul id="local-phone-fields-container">
                            <li class="local-phone-field-group" data-index="0">
                                <div class="repeater-field">
                                    <div class="field-row">
                                        <div class="field-input">
                                            <input type="tel" name="local_phones[0][phone_number]" placeholder="<?php _e('Office Number', 'wproject-contacts-pro'); ?>">
                                        </div>
                                        <div class="field-label">
                                            <select name="local_phones[0][label]">
                                                <option value="office"><?php _e('Office', 'wproject-contacts-pro'); ?></option>
                                                <option value="home"><?php _e('Home', 'wproject-contacts-pro'); ?></option>
                                                <option value="fax"><?php _e('Fax', 'wproject-contacts-pro'); ?></option>
                                                <option value="other"><?php _e('Other', 'wproject-contacts-pro'); ?></option>
                                            </select>
                                        </div>
                                        <div class="field-preferred">
                                            <label>
                                                <input type="checkbox" name="local_phones[0][is_preferred]" value="1" class="local-phone-preferred">
                                                <span><?php _e('Preferred', 'wproject-contacts-pro'); ?></span>
                                            </label>
                                        </div>
                                        <div class="field-remove">
                                            <button type="button" class="remove-local-phone" style="display:none;" title="Remove">
                                                ✕
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                        <button type="button" id="add-local-phone-btn" class="button-secondary">
                            <i data-feather="plus"></i> <?php _e('Add Local Phone', 'wproject-contacts-pro'); ?>
                        </button>
                    </fieldset>

                    <fieldset>
                        <legend><?php _e('Social Profiles', 'wproject-contacts-pro'); ?></legend>
                        <ul>
                            <li>
                                <label><?php _e('LinkedIn URL', 'wproject-contacts-pro'); ?></label>
                                <input type="url" id="contact-linkedin" name="linkedin" placeholder="https://linkedin.com/in/username">
                            </li>
                            <li>
                                <label><?php _e('Twitter/X URL', 'wproject-contacts-pro'); ?></label>
                                <input type="url" id="contact-twitter" name="twitter" placeholder="https://twitter.com/username">
                            </li>
                            <li>
                                <label><?php _e('Facebook URL', 'wproject-contacts-pro'); ?></label>
                                <input type="url" id="contact-facebook" name="facebook" placeholder="https://facebook.com/username">
                            </li>
                        </ul>
                    </fieldset>

                    <?php if(in_array($user_role, array('administrator', 'project_manager'))) : ?>
                    <fieldset>
                        <legend><?php _e('Identification (Admin/PM Only)', 'wproject-contacts-pro'); ?></legend>
                        <ul>
                            <li class="split-2">
                                <div>
                                    <label><?php _e('ID Number', 'wproject-contacts-pro'); ?></label>
                                    <input type="text" id="contact-id-number" name="contact_id_number">
                                </div>
                                <div>
                                    <label><?php _e('Passport Number', 'wproject-contacts-pro'); ?></label>
                                    <input type="text" id="contact-passport" name="passport_number">
                                </div>
                            </li>
                        </ul>
                    </fieldset>
                    <?php endif; ?>

                    <fieldset>
                        <legend><?php _e('Additional Information', 'wproject-contacts-pro'); ?></legend>
                        <ul>
                            <li>
                                <label><?php _e('Tags', 'wproject-contacts-pro'); ?></label>
                                <input type="text" id="contact-tags" name="tags" placeholder="<?php _e('Type tags separated by commas', 'wproject-contacts-pro'); ?>">
                                <small><?php _e('Separate tags with commas (e.g., Client, VIP, Partner)', 'wproject-contacts-pro'); ?></small>
                            </li>
                            <li>
                                <label><?php _e('Notes', 'wproject-contacts-pro'); ?></label>
                                <textarea id="contact-notes" name="notes" rows="4"></textarea>
                            </li>
                        </ul>
                    </fieldset>

                    <div class="form-actions">
                        <button type="button" class="button wpc-modal-close" data-modal="add-contact-modal">
                            <?php _e('Cancel', 'wproject-contacts-pro'); ?>
                        </button>
                        <button type="button" id="submit-contact-btn" class="button button-primary">
                            <?php _e('Add Contact', 'wproject-contacts-pro'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Company Modal -->
    <div id="add-company-modal" class="wpc-modal" style="display: none;">
        <div class="wpc-modal-content">
            <div class="wpc-modal-header">
                <h2><?php _e('Add Company', 'wproject-contacts-pro'); ?></h2>
                <button class="wpc-modal-close" data-modal="add-company-modal">
                    <i data-feather="x"></i>
                </button>
            </div>
            <div class="wpc-modal-body">
                <form id="add-company-form" class="general-form" action="javascript:void(0);" method="post">
                    <fieldset>
                        <legend><?php _e('Company Information', 'wproject-contacts-pro'); ?></legend>
                        <ul>
                            <li>
                                <label><?php _e('Company Name', 'wproject-contacts-pro'); ?> *</label>
                                <input type="text" id="company-name" name="name" required>
                            </li>
                            <li>
                                <label><?php _e('Company Type', 'wproject-contacts-pro'); ?></label>
                                <select id="company-type" name="company_type">
                                    <option value="client"><?php _e('Client', 'wproject-contacts-pro'); ?></option>
                                    <option value="vendor"><?php _e('Vendor', 'wproject-contacts-pro'); ?></option>
                                    <option value="partner"><?php _e('Partner', 'wproject-contacts-pro'); ?></option>
                                    <option value="other"><?php _e('Other', 'wproject-contacts-pro'); ?></option>
                                </select>
                            </li>
                            <li class="split-2">
                                <div>
                                    <label><?php _e('Website', 'wproject-contacts-pro'); ?></label>
                                    <input type="url" id="company-website" name="website">
                                </div>
                                <div>
                                    <label><?php _e('Main Email', 'wproject-contacts-pro'); ?></label>
                                    <input type="email" id="company-email" name="email">
                                </div>
                            </li>
                            <li>
                                <label><?php _e('Main Phone', 'wproject-contacts-pro'); ?></label>
                                <input type="tel" id="company-phone" name="phone">
                            </li>
                            <li>
                                <label><?php _e('Logo URL', 'wproject-contacts-pro'); ?></label>
                                <input type="url" id="company-logo" name="company_logo_url" placeholder="https://example.com/logo.png">
                                <small><?php _e('Enter the direct URL to the company logo image', 'wproject-contacts-pro'); ?></small>
                            </li>
                            <li>
                                <label><?php _e('Notes', 'wproject-contacts-pro'); ?></label>
                                <textarea id="company-notes" name="notes" rows="3"></textarea>
                            </li>
                        </ul>
                    </fieldset>
                    <div class="form-actions">
                        <button type="button" class="button wpc-modal-close" data-modal="add-company-modal">
                            <?php _e('Cancel', 'wproject-contacts-pro'); ?>
                        </button>
                        <button type="button" id="submit-company-btn" class="button button-primary">
                            <?php _e('Add Company', 'wproject-contacts-pro'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Contact Detail Panel -->
    <div id="contact-detail-panel" class="wpc-detail-panel" style="display: none;">
        <div class="wpc-panel-overlay"></div>
        <div class="wpc-panel-content">
            <div class="wpc-panel-header">
                <button class="wpc-panel-close">
                    <i data-feather="x"></i>
                </button>
            </div>
            <div class="wpc-panel-body">
                <div class="loading"><?php _e('Loading contact...', 'wproject-contacts-pro'); ?></div>
            </div>
        </div>
    </div>

        </div>

        <?php do_action('page_end'); ?>

    </section>
    <!--/ End Section /-->

    <?php get_template_part('inc/right'); ?>
    <?php get_template_part('inc/help'); ?>

</div>

<?php get_footer(); ?>
