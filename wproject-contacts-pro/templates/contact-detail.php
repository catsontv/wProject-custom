<?php
/**
 * Contact Detail Panel Template
 * This template is loaded via AJAX when viewing contact details
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// This template expects $contact variable to be set
if (!isset($contact) || !$contact) {
    echo '<p>' . __('Contact not found.', 'wproject-contacts-pro') . '</p>';
    return;
}
?>

<div class="contact-detail-header">
    <div class="contact-avatar">
        <?php if ($contact->photo_url): ?>
            <img src="<?php echo esc_url($contact->photo_url); ?>" alt="<?php echo esc_attr($contact->get_full_name()); ?>" />
        <?php else: ?>
            <img src="<?php echo esc_url($contact->get_avatar_url()); ?>" alt="<?php echo esc_attr($contact->get_full_name()); ?>" />
        <?php endif; ?>
    </div>
    <div class="contact-header-info">
        <h2><?php echo esc_html($contact->get_full_name()); ?></h2>
        <p class="contact-company-role">
            <?php echo esc_html($contact->get_company()->company_name); ?>
            <?php if ($contact->role): ?>
                - <span><?php echo esc_html($contact->role); ?></span>
            <?php endif; ?>
        </p>
    </div>
    <button class="panel-close-btn" id="close-contact-panel">
        <i data-feather="x"></i>
    </button>
</div>

<div class="contact-detail-body">

    <!-- Quick Actions -->
    <div class="contact-quick-actions">
        <?php if ($primary_email = $contact->get_primary_email()): ?>
        <a href="mailto:<?php echo esc_attr($primary_email->email); ?>" class="quick-action-btn">
            <i data-feather="mail"></i>
            <?php _e('Email', 'wproject-contacts-pro'); ?>
        </a>
        <?php endif; ?>

        <?php if ($primary_phone = $contact->get_primary_phone()): ?>
        <a href="tel:<?php echo esc_attr($primary_phone->number); ?>" class="quick-action-btn">
            <i data-feather="phone"></i>
            <?php _e('Call', 'wproject-contacts-pro'); ?>
        </a>
        <?php endif; ?>

        <?php if (current_user_can('edit_posts')): ?>
        <button class="quick-action-btn" id="edit-contact-action" data-contact-id="<?php echo esc_attr($contact->id); ?>">
            <i data-feather="edit"></i>
            <?php _e('Edit', 'wproject-contacts-pro'); ?>
        </button>
        <?php endif; ?>

        <?php if (current_user_can('delete_posts')): ?>
        <button class="quick-action-btn btn-danger" id="delete-contact-action" data-contact-id="<?php echo esc_attr($contact->id); ?>">
            <i data-feather="trash-2"></i>
            <?php _e('Delete', 'wproject-contacts-pro'); ?>
        </button>
        <?php endif; ?>
    </div>

    <!-- Contact Information -->
    <div class="contact-info-section">
        <h3><?php _e('Contact Information', 'wproject-contacts-pro'); ?></h3>

        <!-- Emails -->
        <?php if ($contact->emails && count($contact->emails) > 0): ?>
        <div class="info-group">
            <h4>
                <i data-feather="mail"></i>
                <?php _e('Email Addresses', 'wproject-contacts-pro'); ?>
            </h4>
            <ul class="info-list">
                <?php foreach ($contact->emails as $email): ?>
                <li>
                    <a href="mailto:<?php echo esc_attr($email->email); ?>">
                        <?php echo esc_html($email->email); ?>
                    </a>
                    <span class="info-label"><?php echo esc_html(ucfirst($email->label)); ?></span>
                    <?php if ($email->is_preferred): ?>
                        <span class="preferred-badge">★</span>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Phones -->
        <?php if ($contact->phones && count($contact->phones) > 0): ?>
        <div class="info-group">
            <h4>
                <i data-feather="phone"></i>
                <?php _e('Phone Numbers', 'wproject-contacts-pro'); ?>
            </h4>
            <ul class="info-list">
                <?php foreach ($contact->phones as $phone): ?>
                <li>
                    <a href="tel:<?php echo esc_attr($phone->number); ?>">
                        <?php echo esc_html($phone->number); ?>
                    </a>
                    <span class="info-label">
                        <?php echo esc_html(ucfirst(str_replace('_', ' ', $phone->label))); ?>
                        (<?php echo esc_html(ucfirst($phone->type)); ?>)
                    </span>
                    <?php if ($phone->is_preferred): ?>
                        <span class="preferred-badge">★</span>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Social Profiles -->
        <?php if ($contact->socials && count($contact->socials) > 0): ?>
        <div class="info-group">
            <h4>
                <i data-feather="share-2"></i>
                <?php _e('Social Profiles', 'wproject-contacts-pro'); ?>
            </h4>
            <ul class="info-list social-links">
                <?php foreach ($contact->socials as $social): ?>
                <li>
                    <a href="<?php echo esc_url($social->url); ?>" target="_blank" rel="noopener">
                        <i data-feather="<?php echo esc_attr($social->platform); ?>"></i>
                        <?php echo esc_html(ucfirst($social->platform)); ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- ID and Passport (Admin/PM only) -->
        <?php if (current_user_can('manage_options') || current_user_can('edit_others_posts')): ?>
            <?php if ($contact->contact_id_number || $contact->passport_number): ?>
            <div class="info-group">
                <h4>
                    <i data-feather="credit-card"></i>
                    <?php _e('Identification', 'wproject-contacts-pro'); ?>
                </h4>
                <ul class="info-list">
                    <?php if ($contact->contact_id_number): ?>
                    <li>
                        <strong><?php _e('ID Number:', 'wproject-contacts-pro'); ?></strong>
                        <?php echo esc_html($contact->contact_id_number); ?>
                    </li>
                    <?php endif; ?>
                    <?php if ($contact->passport_number): ?>
                    <li>
                        <strong><?php _e('Passport:', 'wproject-contacts-pro'); ?></strong>
                        <?php echo esc_html($contact->passport_number); ?>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Notes -->
    <?php if ($contact->notes): ?>
    <div class="contact-notes-section">
        <h3><?php _e('Notes', 'wproject-contacts-pro'); ?></h3>
        <div class="notes-content" contenteditable="<?php echo current_user_can('edit_posts') ? 'true' : 'false'; ?>" data-contact-id="<?php echo esc_attr($contact->id); ?>">
            <?php echo wp_kses_post(nl2br($contact->notes)); ?>
        </div>
        <?php if (current_user_can('edit_posts')): ?>
        <p class="notes-hint"><?php _e('Click to edit. Changes save automatically.', 'wproject-contacts-pro'); ?></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Activity Timeline (Phase 3) -->
    <div class="contact-activity-section">
        <h3><?php _e('Activity', 'wproject-contacts-pro'); ?></h3>
        <div class="activity-timeline">
            <div class="timeline-filters">
                <button class="timeline-filter-btn active" data-filter="all"><?php _e('All', 'wproject-contacts-pro'); ?></button>
                <button class="timeline-filter-btn" data-filter="projects"><?php _e('Projects', 'wproject-contacts-pro'); ?></button>
                <button class="timeline-filter-btn" data-filter="tasks"><?php _e('Tasks', 'wproject-contacts-pro'); ?></button>
                <button class="timeline-filter-btn" data-filter="events"><?php _e('Events', 'wproject-contacts-pro'); ?></button>
            </div>
            <div class="timeline-items" id="timeline-items">
                <!-- Timeline items loaded via AJAX -->
                <div class="timeline-empty">
                    <i data-feather="activity"></i>
                    <p><?php _e('No activity yet.', 'wproject-contacts-pro'); ?></p>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
// Initialize Feather icons in panel
if (typeof feather !== 'undefined') {
    feather.replace();
}
</script>
