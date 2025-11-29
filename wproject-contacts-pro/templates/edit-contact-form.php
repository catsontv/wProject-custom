<?php
/**
 * Edit Contact Form Template
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<form id="edit-contact-form" class="contacts-pro-form contact-form">
    <input type="hidden" id="edit_contact_id" name="contact_id" />

    <!-- Basic Information -->
    <div class="form-section">
        <h3><?php _e('Basic Information', 'wproject-contacts-pro'); ?></h3>

        <div class="form-row two-columns">
            <div class="form-group required">
                <label for="edit_first_name"><?php _e('First Name', 'wproject-contacts-pro'); ?> *</label>
                <input type="text" id="edit_first_name" name="first_name" required />
                <span class="field-error"></span>
            </div>

            <div class="form-group required">
                <label for="edit_last_name"><?php _e('Last Name', 'wproject-contacts-pro'); ?> *</label>
                <input type="text" id="edit_last_name" name="last_name" required />
                <span class="field-error"></span>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group required">
                <label for="edit_company_id"><?php _e('Company', 'wproject-contacts-pro'); ?> *</label>
                <select id="edit_company_id" name="company_id" required class="company-select">
                    <option value=""><?php _e('Select a company...', 'wproject-contacts-pro'); ?></option>
                    <!-- Companies loaded via AJAX -->
                </select>
                <span class="field-error"></span>
            </div>
        </div>

        <div class="form-row two-columns">
            <div class="form-group">
                <label for="edit_role"><?php _e('Role/Title', 'wproject-contacts-pro'); ?></label>
                <input type="text" id="edit_role" name="role" />
                <span class="field-error"></span>
            </div>

            <div class="form-group">
                <label for="edit_department"><?php _e('Department', 'wproject-contacts-pro'); ?></label>
                <input type="text" id="edit_department" name="department" />
                <span class="field-error"></span>
            </div>
        </div>
    </div>

    <!-- Email Addresses -->
    <div class="form-section">
        <h3><?php _e('Email Addresses', 'wproject-contacts-pro'); ?></h3>
        <div class="repeatable-fields" id="edit-email-fields">
            <!-- Email fields loaded dynamically -->
        </div>
        <button type="button" class="btn btn-secondary btn-sm" id="edit-add-email-btn">
            <i data-feather="plus"></i>
            <?php _e('Add Email', 'wproject-contacts-pro'); ?>
        </button>
    </div>

    <!-- Phone Numbers -->
    <div class="form-section">
        <h3><?php _e('Phone Numbers', 'wproject-contacts-pro'); ?></h3>

        <h4><?php _e('Mobile', 'wproject-contacts-pro'); ?></h4>
        <div class="repeatable-fields" id="edit-cell-phone-fields">
            <!-- Cell phone fields loaded dynamically -->
        </div>
        <button type="button" class="btn btn-secondary btn-sm" id="edit-add-cell-phone-btn">
            <i data-feather="plus"></i>
            <?php _e('Add Mobile', 'wproject-contacts-pro'); ?>
        </button>

        <h4><?php _e('Landline', 'wproject-contacts-pro'); ?></h4>
        <div class="repeatable-fields" id="edit-local-phone-fields">
            <!-- Local phone fields loaded dynamically -->
        </div>
        <button type="button" class="btn btn-secondary btn-sm" id="edit-add-local-phone-btn">
            <i data-feather="plus"></i>
            <?php _e('Add Landline', 'wproject-contacts-pro'); ?>
        </button>
    </div>

    <!-- Social Profiles -->
    <div class="form-section">
        <h3><?php _e('Social Profiles', 'wproject-contacts-pro'); ?></h3>

        <div class="form-row">
            <div class="form-group">
                <label for="edit_linkedin_url">
                    <i data-feather="linkedin"></i>
                    <?php _e('LinkedIn', 'wproject-contacts-pro'); ?>
                </label>
                <input type="url" id="edit_linkedin_url" name="linkedin_url" placeholder="https://linkedin.com/in/username" />
                <span class="field-error"></span>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="edit_twitter_url">
                    <i data-feather="twitter"></i>
                    <?php _e('Twitter/X', 'wproject-contacts-pro'); ?>
                </label>
                <input type="url" id="edit_twitter_url" name="twitter_url" placeholder="https://twitter.com/username" />
                <span class="field-error"></span>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="edit_facebook_url">
                    <i data-feather="facebook"></i>
                    <?php _e('Facebook', 'wproject-contacts-pro'); ?>
                </label>
                <input type="url" id="edit_facebook_url" name="facebook_url" placeholder="https://facebook.com/username" />
                <span class="field-error"></span>
            </div>
        </div>
    </div>

    <!-- Additional Information -->
    <div class="form-section">
        <h3><?php _e('Additional Information', 'wproject-contacts-pro'); ?></h3>

        <div class="form-row">
            <div class="form-group">
                <label for="edit_notes"><?php _e('Notes', 'wproject-contacts-pro'); ?></label>
                <textarea id="edit_notes" name="notes" rows="4"></textarea>
                <span class="field-error"></span>
            </div>
        </div>

        <?php if (current_user_can('manage_options') || current_user_can('edit_others_posts')): ?>
        <div class="form-row two-columns">
            <div class="form-group">
                <label for="edit_contact_id_number"><?php _e('ID Number', 'wproject-contacts-pro'); ?></label>
                <input type="text" id="edit_contact_id_number" name="contact_id_number" />
                <span class="field-hint"><?php _e('Admin/PM only', 'wproject-contacts-pro'); ?></span>
            </div>

            <div class="form-group">
                <label for="edit_passport_number"><?php _e('Passport Number', 'wproject-contacts-pro'); ?></label>
                <input type="text" id="edit_passport_number" name="passport_number" />
                <span class="field-hint"><?php _e('Admin/PM only', 'wproject-contacts-pro'); ?></span>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Form Actions -->
    <div class="form-actions">
        <button type="button" class="btn btn-danger" id="delete-contact-btn">
            <i data-feather="trash-2"></i>
            <?php _e('Delete Contact', 'wproject-contacts-pro'); ?>
        </button>
        <div class="form-actions-right">
            <button type="button" class="btn btn-secondary modal-close"><?php _e('Cancel', 'wproject-contacts-pro'); ?></button>
            <button type="submit" class="btn btn-primary">
                <span class="btn-text"><?php _e('Update Contact', 'wproject-contacts-pro'); ?></span>
                <span class="btn-loading" style="display: none;">
                    <i data-feather="loader" class="spinning"></i>
                    <?php _e('Updating...', 'wproject-contacts-pro'); ?>
                </span>
            </button>
        </div>
    </div>
</form>

<script>
// Initialize Feather icons in form
if (typeof feather !== 'undefined') {
    feather.replace();
}
</script>
