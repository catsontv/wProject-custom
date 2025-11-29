<?php
/**
 * Add Contact Form Template
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<form id="add-contact-form" class="contacts-pro-form contact-form">

    <!-- Basic Information -->
    <div class="form-section">
        <h3><?php _e('Basic Information', 'wproject-contacts-pro'); ?></h3>

        <div class="form-row two-columns">
            <div class="form-group required">
                <label for="first_name"><?php _e('First Name', 'wproject-contacts-pro'); ?> *</label>
                <input type="text" id="first_name" name="first_name" required />
                <span class="field-error"></span>
            </div>

            <div class="form-group required">
                <label for="last_name"><?php _e('Last Name', 'wproject-contacts-pro'); ?> *</label>
                <input type="text" id="last_name" name="last_name" required />
                <span class="field-error"></span>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group required">
                <label for="company_id"><?php _e('Company', 'wproject-contacts-pro'); ?> *</label>
                <select id="company_id" name="company_id" required class="company-select">
                    <option value=""><?php _e('Select a company...', 'wproject-contacts-pro'); ?></option>
                    <!-- Companies loaded via AJAX -->
                </select>
                <span class="field-error"></span>
            </div>
        </div>

        <div class="form-row two-columns">
            <div class="form-group">
                <label for="role"><?php _e('Role/Title', 'wproject-contacts-pro'); ?></label>
                <input type="text" id="role" name="role" />
                <span class="field-error"></span>
            </div>

            <div class="form-group">
                <label for="department"><?php _e('Department', 'wproject-contacts-pro'); ?></label>
                <input type="text" id="department" name="department" />
                <span class="field-error"></span>
            </div>
        </div>
    </div>

    <!-- Email Addresses -->
    <div class="form-section">
        <h3><?php _e('Email Addresses', 'wproject-contacts-pro'); ?></h3>
        <div class="repeatable-fields" id="email-fields">
            <div class="repeatable-field email-field">
                <div class="field-inputs">
                    <input type="email" name="emails[0][email]" placeholder="<?php _e('Email address', 'wproject-contacts-pro'); ?>" required class="email-input" />
                    <select name="emails[0][label]" class="email-label">
                        <option value="work"><?php _e('Work', 'wproject-contacts-pro'); ?></option>
                        <option value="personal"><?php _e('Personal', 'wproject-contacts-pro'); ?></option>
                        <option value="assistant"><?php _e('Assistant', 'wproject-contacts-pro'); ?></option>
                        <option value="other"><?php _e('Other', 'wproject-contacts-pro'); ?></option>
                    </select>
                    <label class="preferred-label">
                        <input type="radio" name="preferred_email" value="0" checked class="preferred-email" />
                        <span><?php _e('Preferred', 'wproject-contacts-pro'); ?></span>
                    </label>
                </div>
                <button type="button" class="btn-icon remove-field" disabled>
                    <i data-feather="minus-circle"></i>
                </button>
            </div>
        </div>
        <button type="button" class="btn btn-secondary btn-sm" id="add-email-btn">
            <i data-feather="plus"></i>
            <?php _e('Add Email', 'wproject-contacts-pro'); ?>
        </button>
    </div>

    <!-- Phone Numbers -->
    <div class="form-section">
        <h3><?php _e('Phone Numbers', 'wproject-contacts-pro'); ?></h3>

        <h4><?php _e('Mobile', 'wproject-contacts-pro'); ?></h4>
        <div class="repeatable-fields" id="cell-phone-fields">
            <div class="repeatable-field phone-field">
                <div class="field-inputs">
                    <input type="tel" name="cell_phones[0][number]" placeholder="<?php _e('Phone number', 'wproject-contacts-pro'); ?>" class="phone-input" />
                    <select name="cell_phones[0][label]" class="phone-label">
                        <option value="mobile"><?php _e('Mobile', 'wproject-contacts-pro'); ?></option>
                        <option value="assistant_mobile"><?php _e('Assistant Mobile', 'wproject-contacts-pro'); ?></option>
                        <option value="other"><?php _e('Other', 'wproject-contacts-pro'); ?></option>
                    </select>
                    <label class="preferred-label">
                        <input type="radio" name="preferred_phone" value="cell_0" class="preferred-phone" />
                        <span><?php _e('Preferred', 'wproject-contacts-pro'); ?></span>
                    </label>
                </div>
                <button type="button" class="btn-icon remove-field">
                    <i data-feather="minus-circle"></i>
                </button>
            </div>
        </div>
        <button type="button" class="btn btn-secondary btn-sm" id="add-cell-phone-btn">
            <i data-feather="plus"></i>
            <?php _e('Add Mobile', 'wproject-contacts-pro'); ?>
        </button>

        <h4><?php _e('Landline', 'wproject-contacts-pro'); ?></h4>
        <div class="repeatable-fields" id="local-phone-fields">
            <div class="repeatable-field phone-field">
                <div class="field-inputs">
                    <input type="tel" name="local_phones[0][number]" placeholder="<?php _e('Phone number', 'wproject-contacts-pro'); ?>" class="phone-input" />
                    <select name="local_phones[0][label]" class="phone-label">
                        <option value="office"><?php _e('Office', 'wproject-contacts-pro'); ?></option>
                        <option value="home"><?php _e('Home', 'wproject-contacts-pro'); ?></option>
                        <option value="fax"><?php _e('Fax', 'wproject-contacts-pro'); ?></option>
                        <option value="other"><?php _e('Other', 'wproject-contacts-pro'); ?></option>
                    </select>
                    <label class="preferred-label">
                        <input type="radio" name="preferred_phone" value="local_0" class="preferred-phone" />
                        <span><?php _e('Preferred', 'wproject-contacts-pro'); ?></span>
                    </label>
                </div>
                <button type="button" class="btn-icon remove-field">
                    <i data-feather="minus-circle"></i>
                </button>
            </div>
        </div>
        <button type="button" class="btn btn-secondary btn-sm" id="add-local-phone-btn">
            <i data-feather="plus"></i>
            <?php _e('Add Landline', 'wproject-contacts-pro'); ?>
        </button>
    </div>

    <!-- Social Profiles -->
    <div class="form-section">
        <h3><?php _e('Social Profiles', 'wproject-contacts-pro'); ?></h3>

        <div class="form-row">
            <div class="form-group">
                <label for="linkedin_url">
                    <i data-feather="linkedin"></i>
                    <?php _e('LinkedIn', 'wproject-contacts-pro'); ?>
                </label>
                <input type="url" id="linkedin_url" name="linkedin_url" placeholder="https://linkedin.com/in/username" />
                <span class="field-error"></span>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="twitter_url">
                    <i data-feather="twitter"></i>
                    <?php _e('Twitter/X', 'wproject-contacts-pro'); ?>
                </label>
                <input type="url" id="twitter_url" name="twitter_url" placeholder="https://twitter.com/username" />
                <span class="field-error"></span>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="facebook_url">
                    <i data-feather="facebook"></i>
                    <?php _e('Facebook', 'wproject-contacts-pro'); ?>
                </label>
                <input type="url" id="facebook_url" name="facebook_url" placeholder="https://facebook.com/username" />
                <span class="field-error"></span>
            </div>
        </div>
    </div>

    <!-- Additional Information -->
    <div class="form-section">
        <h3><?php _e('Additional Information', 'wproject-contacts-pro'); ?></h3>

        <div class="form-row">
            <div class="form-group">
                <label for="notes"><?php _e('Notes', 'wproject-contacts-pro'); ?></label>
                <textarea id="notes" name="notes" rows="4"></textarea>
                <span class="field-error"></span>
            </div>
        </div>

        <?php if (current_user_can('manage_options') || current_user_can('edit_others_posts')): ?>
        <div class="form-row two-columns">
            <div class="form-group">
                <label for="contact_id_number"><?php _e('ID Number', 'wproject-contacts-pro'); ?></label>
                <input type="text" id="contact_id_number" name="contact_id_number" />
                <span class="field-hint"><?php _e('Admin/PM only', 'wproject-contacts-pro'); ?></span>
            </div>

            <div class="form-group">
                <label for="passport_number"><?php _e('Passport Number', 'wproject-contacts-pro'); ?></label>
                <input type="text" id="passport_number" name="passport_number" />
                <span class="field-hint"><?php _e('Admin/PM only', 'wproject-contacts-pro'); ?></span>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Form Actions -->
    <div class="form-actions">
        <button type="button" class="btn btn-secondary modal-close"><?php _e('Cancel', 'wproject-contacts-pro'); ?></button>
        <button type="submit" class="btn btn-primary">
            <span class="btn-text"><?php _e('Add Contact', 'wproject-contacts-pro'); ?></span>
            <span class="btn-loading" style="display: none;">
                <i data-feather="loader" class="spinning"></i>
                <?php _e('Adding...', 'wproject-contacts-pro'); ?>
            </span>
        </button>
    </div>
</form>

<script>
// Initialize Feather icons in form
if (typeof feather !== 'undefined') {
    feather.replace();
}
</script>
