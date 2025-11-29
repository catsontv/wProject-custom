<?php if (!defined('ABSPATH')) { exit; } ?>
<!--/ Start Contacts List /-->
<div class="contacts-pro-container">

    <!-- Contact List View -->
    <div class="contacts-list-view">
        <table class="contacts-table">
            <thead>
                <tr>
                    <th><i data-feather="briefcase"></i> <?php _e('Company', 'wproject-contacts-pro'); ?></th>
                    <th><i data-feather="user"></i> <?php _e('Contact', 'wproject-contacts-pro'); ?></th>
                    <th><i data-feather="mail"></i> <?php _e('Email', 'wproject-contacts-pro'); ?></th>
                    <th><i data-feather="phone"></i> <?php _e('Phone', 'wproject-contacts-pro'); ?></th>
                </tr>
            </thead>
            <tbody id="contacts-table-body">
                <!-- Contacts will be loaded via AJAX -->
            </tbody>
        </table>
        <div class="contacts-loading" style="text-align: center; padding: 40px;">
            <p><?php _e('Loading contacts...', 'wproject-contacts-pro'); ?></p>
        </div>
        <div class="contacts-empty" style="display:none; text-align: center; padding: 40px;">
            <p><?php _e('No contacts found. Click the CREATE button to add your first contact.', 'wproject-contacts-pro'); ?></p>
        </div>
    </div>

    <!-- Contact Detail View (hidden by default) -->
    <div class="contact-detail-view" style="display:none;">
        <div class="contact-detail-content">
            <!-- Content loaded via AJAX -->
        </div>
    </div>

    <!-- Contact Form (hidden by default) -->
    <div class="contact-form-view" style="display:none;">
        <form id="contact-form" class="wproject-contact-form">
            <input type="hidden" name="action" value="save_contact" />
            <input type="hidden" name="contact_id" id="contact_id" value="" />
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('wproject_contacts_pro_nonce'); ?>" />

            <div class="form-sections">

                <!-- Company Section -->
                <div class="form-section">
                    <h3><?php _e('COMPANY', 'wproject-contacts-pro'); ?></h3>

                    <div class="form-row">
                        <label><?php _e('NAME', 'wproject-contacts-pro'); ?></label>
                        <input type="text" name="company_name" id="company_name" required />
                    </div>

                    <div class="form-row">
                        <label><?php _e('EMAIL', 'wproject-contacts-pro'); ?></label>
                        <input type="email" name="company_email" id="company_email" />
                    </div>

                    <div class="form-row">
                        <label><?php _e('PHONE', 'wproject-contacts-pro'); ?></label>
                        <input type="text" name="company_phone" id="company_phone" />
                    </div>

                    <div class="form-row">
                        <label><?php _e('WEBSITE', 'wproject-contacts-pro'); ?></label>
                        <input type="url" name="company_website" id="company_website" />
                    </div>

                    <div class="form-row">
                        <label><?php _e('ABN / ACN', 'wproject-contacts-pro'); ?></label>
                        <input type="text" name="company_abn" id="company_abn" />
                    </div>
                </div>

                <!-- Address Section -->
                <div class="form-section">
                    <h3><?php _e('ADDRESS', 'wproject-contacts-pro'); ?></h3>

                    <div class="form-row">
                        <label><?php _e('STREET ADDRESS', 'wproject-contacts-pro'); ?></label>
                        <input type="text" name="address_street" id="address_street" />
                    </div>

                    <div class="form-row">
                        <label><?php _e('ADDRESS LINE 2', 'wproject-contacts-pro'); ?></label>
                        <input type="text" name="address_line2" id="address_line2" />
                    </div>

                    <div class="form-row">
                        <label><?php _e('CITY', 'wproject-contacts-pro'); ?></label>
                        <input type="text" name="address_city" id="address_city" />
                    </div>

                    <div class="form-row">
                        <label><?php _e('STATE', 'wproject-contacts-pro'); ?></label>
                        <input type="text" name="address_state" id="address_state" />
                    </div>

                    <div class="form-row-group">
                        <div class="form-row">
                            <label><?php _e('COUNTRY', 'wproject-contacts-pro'); ?></label>
                            <input type="text" name="address_country" id="address_country" />
                        </div>

                        <div class="form-row">
                            <label><?php _e('POST CODE', 'wproject-contacts-pro'); ?></label>
                            <input type="text" name="address_postcode" id="address_postcode" />
                        </div>
                    </div>
                </div>

                <!-- Contact Person Section -->
                <div class="form-section">
                    <h3><?php _e('CONTACT PERSON', 'wproject-contacts-pro'); ?></h3>

                    <div class="form-row">
                        <label><?php _e('NAME', 'wproject-contacts-pro'); ?></label>
                        <input type="text" name="contact_name" id="contact_name" />
                    </div>

                    <div class="form-row">
                        <label><?php _e('TITLE', 'wproject-contacts-pro'); ?></label>
                        <input type="text" name="contact_title" id="contact_title" />
                    </div>

                    <div class="form-row">
                        <label><?php _e('EMAIL', 'wproject-contacts-pro'); ?></label>
                        <input type="email" name="contact_email" id="contact_email" />
                    </div>

                    <div class="form-row">
                        <label><?php _e('MOBILE', 'wproject-contacts-pro'); ?></label>
                        <input type="text" name="contact_mobile" id="contact_mobile" />
                    </div>

                    <div class="form-row">
                        <label><?php _e('PHONE (LANDLINE)', 'wproject-contacts-pro'); ?></label>
                        <input type="text" name="contact_phone" id="contact_phone" />
                    </div>
                </div>

                <!-- Communications Section -->
                <div class="form-section">
                    <h3><?php _e('COMMS', 'wproject-contacts-pro'); ?></h3>

                    <div class="form-row">
                        <label><?php _e('SLACK', 'wproject-contacts-pro'); ?></label>
                        <input type="text" name="comm_slack" id="comm_slack" />
                    </div>

                    <div class="form-row">
                        <label><?php _e('MICROSOFT TEAMS', 'wproject-contacts-pro'); ?></label>
                        <input type="text" name="comm_teams" id="comm_teams" />
                    </div>

                    <div class="form-row">
                        <label><?php _e('GOOGLE MEET', 'wproject-contacts-pro'); ?></label>
                        <input type="text" name="comm_google_meet" id="comm_google_meet" />
                    </div>

                    <div class="form-row">
                        <label><?php _e('SKYPE', 'wproject-contacts-pro'); ?></label>
                        <input type="text" name="comm_skype" id="comm_skype" />
                    </div>

                    <div class="form-row">
                        <label><?php _e('OTHER', 'wproject-contacts-pro'); ?></label>
                        <input type="text" name="comm_other" id="comm_other" />
                    </div>
                </div>

                <!-- Social Section -->
                <div class="form-section">
                    <h3><?php _e('SOCIAL', 'wproject-contacts-pro'); ?></h3>

                    <div class="form-row">
                        <label><?php _e('FACEBOOK', 'wproject-contacts-pro'); ?></label>
                        <input type="url" name="social_facebook" id="social_facebook" />
                    </div>

                    <div class="form-row">
                        <label><?php _e('INSTAGRAM', 'wproject-contacts-pro'); ?></label>
                        <input type="url" name="social_instagram" id="social_instagram" />
                    </div>

                    <div class="form-row">
                        <label><?php _e('TWITTER', 'wproject-contacts-pro'); ?></label>
                        <input type="url" name="social_twitter" id="social_twitter" />
                    </div>

                    <div class="form-row">
                        <label><?php _e('LINKEDIN', 'wproject-contacts-pro'); ?></label>
                        <input type="url" name="social_linkedin" id="social_linkedin" />
                    </div>

                    <div class="form-row">
                        <label><?php _e('OTHER', 'wproject-contacts-pro'); ?></label>
                        <input type="url" name="social_other" id="social_other" />
                    </div>
                </div>

                <!-- Information Section -->
                <div class="form-section">
                    <h3><?php _e('INFORMATION', 'wproject-contacts-pro'); ?></h3>

                    <div class="form-row">
                        <label><?php _e('NOTES', 'wproject-contacts-pro'); ?></label>
                        <textarea name="contact_notes" id="contact_notes" rows="6"></textarea>
                    </div>
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" class="btn"><?php _e('Save Contact', 'wproject-contacts-pro'); ?></button>
                <button type="button" class="btn-light cancel-contact-form"><?php _e('Cancel', 'wproject-contacts-pro'); ?></button>
            </div>
        </form>
    </div>

</div>

<?php
/* Side nav items for contacts page */
function contacts_pro_nav() {
    if (is_page('contacts')) {
        ?>
        <li class="show-contacts-list"><a><i data-feather="list"></i><?php _e('All Contacts', 'wproject-contacts-pro'); ?></a></li>
        <?php
    }
}
add_action('side_nav', 'contacts_pro_nav', 20);

/* Help section */
function contacts_pro_help() {
    if (is_page('contacts')) {
        ?>
        <h4><?php _e('Contacts Management', 'wproject-contacts-pro'); ?></h4>
        <p><?php _e('Manage your company contacts, including contact persons, addresses, communication channels, and social media links.', 'wproject-contacts-pro'); ?></p>
        <p><?php _e('Click the CREATE button to add a new contact, or click on any contact in the list to view or edit their details.', 'wproject-contacts-pro'); ?></p>
        <?php
    }
}
add_action('help_start', 'contacts_pro_help');
?>
<!--/ End Contacts List /-->
