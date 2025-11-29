<?php
/**
 * Edit Company Form Template
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<form id="edit-company-form" class="contacts-pro-form">
    <input type="hidden" id="edit_company_id" name="company_id" />

    <div class="form-row">
        <div class="form-group required">
            <label for="edit_company_name"><?php _e('Company Name', 'wproject-contacts-pro'); ?> *</label>
            <input type="text" id="edit_company_name" name="company_name" required />
            <span class="field-error"></span>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="edit_company_website"><?php _e('Website', 'wproject-contacts-pro'); ?></label>
            <input type="url" id="edit_company_website" name="company_website" placeholder="https://" />
            <span class="field-error"></span>
        </div>
    </div>

    <div class="form-row two-columns">
        <div class="form-group">
            <label for="edit_company_phone"><?php _e('Main Phone', 'wproject-contacts-pro'); ?></label>
            <input type="tel" id="edit_company_phone" name="company_phone" />
            <span class="field-error"></span>
        </div>

        <div class="form-group">
            <label for="edit_company_email"><?php _e('Main Email', 'wproject-contacts-pro'); ?></label>
            <input type="email" id="edit_company_email" name="company_email" />
            <span class="field-error"></span>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="edit_company_type"><?php _e('Company Type', 'wproject-contacts-pro'); ?></label>
            <select id="edit_company_type" name="company_type">
                <option value="client"><?php _e('Client', 'wproject-contacts-pro'); ?></option>
                <option value="vendor"><?php _e('Vendor', 'wproject-contacts-pro'); ?></option>
                <option value="partner"><?php _e('Partner', 'wproject-contacts-pro'); ?></option>
                <option value="other"><?php _e('Other', 'wproject-contacts-pro'); ?></option>
            </select>
            <span class="field-error"></span>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="edit_company_notes"><?php _e('Notes', 'wproject-contacts-pro'); ?></label>
            <textarea id="edit_company_notes" name="company_notes" rows="4"></textarea>
            <span class="field-error"></span>
        </div>
    </div>

    <div class="form-actions">
        <button type="button" class="btn btn-danger" id="delete-company-btn">
            <i data-feather="trash-2"></i>
            <?php _e('Delete Company', 'wproject-contacts-pro'); ?>
        </button>
        <div class="form-actions-right">
            <button type="button" class="btn btn-secondary modal-close"><?php _e('Cancel', 'wproject-contacts-pro'); ?></button>
            <button type="submit" class="btn btn-primary">
                <span class="btn-text"><?php _e('Update Company', 'wproject-contacts-pro'); ?></span>
                <span class="btn-loading" style="display: none;">
                    <i data-feather="loader" class="spinning"></i>
                    <?php _e('Updating...', 'wproject-contacts-pro'); ?>
                </span>
            </button>
        </div>
    </div>
</form>
