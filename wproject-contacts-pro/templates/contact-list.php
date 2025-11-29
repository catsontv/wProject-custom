<?php
/**
 * Template for Contacts List Page
 * This template displays the main contacts list with search, sorting, and filtering
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get wProject header
get_header();

// Get current user
$current_user = wp_get_current_user();
?>

<div id="content" class="contacts-content">

    <!-- Page Header -->
    <div class="contacts-header">
        <h1><?php _e('Contacts', 'wproject-contacts-pro'); ?></h1>
        <div class="header-actions">
            <input type="search" id="contacts-search" placeholder="<?php _e('Search contacts...', 'wproject-contacts-pro'); ?>" />
            <button class="btn btn-primary" id="add-contact-btn">
                <i data-feather="plus"></i>
                <?php _e('Add Contact', 'wproject-contacts-pro'); ?>
            </button>
            <button class="btn btn-secondary" id="add-company-btn">
                <i data-feather="briefcase"></i>
                <?php _e('Add Company', 'wproject-contacts-pro'); ?>
            </button>
        </div>
    </div>

    <!-- Tabs -->
    <div class="contacts-tabs">
        <button class="tab-button active" data-tab="all">
            <i data-feather="users"></i>
            <?php _e('All Contacts', 'wproject-contacts-pro'); ?>
        </button>
        <button class="tab-button" data-tab="by-company">
            <i data-feather="briefcase"></i>
            <?php _e('By Company', 'wproject-contacts-pro'); ?>
        </button>
        <button class="tab-button" data-tab="by-tag">
            <i data-feather="tag"></i>
            <?php _e('By Tag', 'wproject-contacts-pro'); ?>
        </button>
    </div>

    <!-- Contacts Table -->
    <div class="contacts-table-container">
        <div class="contacts-loading" style="display: none;">
            <div class="spinner"></div>
            <p><?php _e('Loading contacts...', 'wproject-contacts-pro'); ?></p>
        </div>

        <div class="contacts-table-wrapper">
            <table class="contacts-table" id="contacts-table">
                <thead>
                    <tr>
                        <th class="col-checkbox">
                            <input type="checkbox" id="select-all-contacts" />
                        </th>
                        <th class="col-photo"></th>
                        <th class="col-company sortable" data-sort="company">
                            <i data-feather="briefcase"></i>
                            <?php _e('Company', 'wproject-contacts-pro'); ?>
                            <span class="sort-indicator"></span>
                        </th>
                        <th class="col-contact sortable" data-sort="name">
                            <i data-feather="user"></i>
                            <?php _e('Contact', 'wproject-contacts-pro'); ?>
                            <span class="sort-indicator"></span>
                        </th>
                        <th class="col-email sortable" data-sort="email">
                            <i data-feather="mail"></i>
                            <?php _e('Email', 'wproject-contacts-pro'); ?>
                            <span class="sort-indicator"></span>
                        </th>
                        <th class="col-phone sortable" data-sort="phone">
                            <i data-feather="phone"></i>
                            <?php _e('Phone', 'wproject-contacts-pro'); ?>
                            <span class="sort-indicator"></span>
                        </th>
                        <th class="col-actions"></th>
                    </tr>
                </thead>
                <tbody id="contacts-tbody">
                    <!-- Contacts will be loaded via AJAX -->
                </tbody>
            </table>
        </div>

        <!-- Empty State -->
        <div class="contacts-empty" style="display: none;">
            <i data-feather="users"></i>
            <h3><?php _e('No contacts yet', 'wproject-contacts-pro'); ?></h3>
            <p><?php _e('Add your first contact to get started.', 'wproject-contacts-pro'); ?></p>
            <button class="btn btn-primary" id="add-first-contact-btn">
                <i data-feather="plus"></i>
                <?php _e('Add Contact', 'wproject-contacts-pro'); ?>
            </button>
        </div>

        <!-- No Results State -->
        <div class="contacts-no-results" style="display: none;">
            <i data-feather="search"></i>
            <h3><?php _e('No contacts found', 'wproject-contacts-pro'); ?></h3>
            <p><?php _e('Try adjusting your search or filters.', 'wproject-contacts-pro'); ?></p>
        </div>
    </div>

    <!-- Pagination -->
    <div class="contacts-pagination" id="contacts-pagination" style="display: none;">
        <div class="pagination-info">
            <span id="pagination-info-text"></span>
        </div>
        <div class="pagination-controls">
            <button class="btn-pagination" id="prev-page" disabled>
                <i data-feather="chevron-left"></i>
                <?php _e('Previous', 'wproject-contacts-pro'); ?>
            </button>
            <div class="page-numbers" id="page-numbers"></div>
            <button class="btn-pagination" id="next-page">
                <?php _e('Next', 'wproject-contacts-pro'); ?>
                <i data-feather="chevron-right"></i>
            </button>
        </div>
        <div class="pagination-per-page">
            <label><?php _e('Per page:', 'wproject-contacts-pro'); ?></label>
            <select id="per-page-select">
                <option value="25">25</option>
                <option value="50" selected>50</option>
                <option value="100">100</option>
            </select>
        </div>
    </div>

    <!-- Bulk Actions Bar (shown when contacts are selected) -->
    <div class="bulk-actions-bar" id="bulk-actions-bar" style="display: none;">
        <div class="bulk-selection-info">
            <span id="bulk-selection-count">0</span> <?php _e('contacts selected', 'wproject-contacts-pro'); ?>
        </div>
        <div class="bulk-actions">
            <button class="btn-bulk" id="bulk-export-btn">
                <i data-feather="download"></i>
                <?php _e('Export', 'wproject-contacts-pro'); ?>
            </button>
            <button class="btn-bulk" id="bulk-tag-btn">
                <i data-feather="tag"></i>
                <?php _e('Add Tag', 'wproject-contacts-pro'); ?>
            </button>
            <button class="btn-bulk btn-danger" id="bulk-delete-btn">
                <i data-feather="trash-2"></i>
                <?php _e('Delete', 'wproject-contacts-pro'); ?>
            </button>
        </div>
        <button class="btn-bulk-close" id="bulk-close-btn">
            <i data-feather="x"></i>
        </button>
    </div>

</div>

<!-- Contact Detail Panel (loaded dynamically) -->
<div class="contact-detail-panel" id="contact-detail-panel" style="display: none;">
    <div class="panel-overlay"></div>
    <div class="panel-content">
        <!-- Content loaded via AJAX -->
    </div>
</div>

<!-- Add Company Modal -->
<div class="modal" id="add-company-modal" style="display: none;">
    <div class="modal-overlay"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h2><?php _e('Add Company', 'wproject-contacts-pro'); ?></h2>
            <button class="modal-close">
                <i data-feather="x"></i>
            </button>
        </div>
        <div class="modal-body">
            <?php include(WPROJECT_CONTACTS_PRO_PATH . 'templates/add-company-form.php'); ?>
        </div>
    </div>
</div>

<!-- Edit Company Modal -->
<div class="modal" id="edit-company-modal" style="display: none;">
    <div class="modal-overlay"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h2><?php _e('Edit Company', 'wproject-contacts-pro'); ?></h2>
            <button class="modal-close">
                <i data-feather="x"></i>
            </button>
        </div>
        <div class="modal-body">
            <?php include(WPROJECT_CONTACTS_PRO_PATH . 'templates/edit-company-form.php'); ?>
        </div>
    </div>
</div>

<!-- Add Contact Modal -->
<div class="modal" id="add-contact-modal" style="display: none;">
    <div class="modal-overlay"></div>
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h2><?php _e('Add Contact', 'wproject-contacts-pro'); ?></h2>
            <button class="modal-close">
                <i data-feather="x"></i>
            </button>
        </div>
        <div class="modal-body">
            <?php include(WPROJECT_CONTACTS_PRO_PATH . 'templates/add-contact-form.php'); ?>
        </div>
    </div>
</div>

<!-- Edit Contact Modal -->
<div class="modal" id="edit-contact-modal" style="display: none;">
    <div class="modal-overlay"></div>
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h2><?php _e('Edit Contact', 'wproject-contacts-pro'); ?></h2>
            <button class="modal-close">
                <i data-feather="x"></i>
            </button>
        </div>
        <div class="modal-body">
            <?php include(WPROJECT_CONTACTS_PRO_PATH . 'templates/edit-contact-form.php'); ?>
        </div>
    </div>
</div>

<script>
// Initialize Feather icons when page loads
document.addEventListener('DOMContentLoaded', function() {
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});
</script>

<?php
// Get wProject footer
get_footer();
?>
