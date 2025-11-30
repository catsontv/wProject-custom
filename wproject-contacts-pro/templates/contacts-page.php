<?php
/**
 * Template for displaying contacts page
 *
 * This template follows wProject's page structure
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
        </div>
    </div>

    <div class="contacts-filters">
        <div class="filter-tabs">
            <button class="filter-tab active" data-filter="all"><?php _e('All', 'wproject-contacts-pro'); ?></button>
            <button class="filter-tab" data-filter="contacts"><?php _e('Contacts', 'wproject-contacts-pro'); ?></button>
            <button class="filter-tab" data-filter="companies"><?php _e('Companies', 'wproject-contacts-pro'); ?></button>
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
                <form id="add-contact-form" action="javascript:void(0);" method="post">
                    <div class="form-group">
                        <label for="contact-first-name"><?php _e('First Name', 'wproject-contacts-pro'); ?> *</label>
                        <input type="text" id="contact-first-name" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="contact-last-name"><?php _e('Last Name', 'wproject-contacts-pro'); ?> *</label>
                        <input type="text" id="contact-last-name" name="last_name" required>
                    </div>
                    <div class="form-group">
                        <label for="contact-email"><?php _e('Email', 'wproject-contacts-pro'); ?></label>
                        <input type="email" id="contact-email" name="email">
                    </div>
                    <div class="form-group">
                        <label for="contact-phone"><?php _e('Phone', 'wproject-contacts-pro'); ?></label>
                        <input type="tel" id="contact-phone" name="phone">
                    </div>
                    <div class="form-group">
                        <label for="contact-company"><?php _e('Company', 'wproject-contacts-pro'); ?></label>
                        <select id="contact-company" name="company_id">
                            <option value=""><?php _e('Select Company', 'wproject-contacts-pro'); ?></option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="contact-position"><?php _e('Position', 'wproject-contacts-pro'); ?></label>
                        <input type="text" id="contact-position" name="position">
                    </div>
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
                <form id="add-company-form" action="javascript:void(0);" method="post">
                    <div class="form-group">
                        <label for="company-name"><?php _e('Company Name', 'wproject-contacts-pro'); ?> *</label>
                        <input type="text" id="company-name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="company-website"><?php _e('Website', 'wproject-contacts-pro'); ?></label>
                        <input type="url" id="company-website" name="website">
                    </div>
                    <div class="form-group">
                        <label for="company-phone"><?php _e('Phone', 'wproject-contacts-pro'); ?></label>
                        <input type="tel" id="company-phone" name="phone">
                    </div>
                    <div class="form-group">
                        <label for="company-email"><?php _e('Email', 'wproject-contacts-pro'); ?></label>
                        <input type="email" id="company-email" name="email">
                    </div>
                    <div class="form-group">
                        <label for="company-address"><?php _e('Address', 'wproject-contacts-pro'); ?></label>
                        <textarea id="company-address" name="address" rows="3"></textarea>
                    </div>
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

        <?php do_action('page_end'); ?>

    </section>
    <!--/ End Section /-->

    <?php get_template_part('inc/right'); ?>
    <?php get_template_part('inc/help'); ?>

</div>

<?php get_footer(); ?>
