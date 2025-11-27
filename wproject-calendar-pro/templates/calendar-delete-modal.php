<?php
/**
 * Calendar Delete Modal Template
 *
 * Modal for deleting calendars with transfer/delete options
 *
 * @package wProject Calendar Pro
 * @since 1.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<!-- Calendar Delete Modal -->
<div id="calendar-delete-modal" class="calendar-modal">
    <div class="calendar-modal-content">
        <div class="calendar-modal-header">
            <h3><?php _e( 'Delete Calendar', 'wproject-calendar-pro' ); ?></h3>
            <button class="calendar-modal-close" aria-label="Close">&times;</button>
        </div>

        <div class="calendar-modal-body">
            <input type="hidden" id="delete-calendar-id" value="">

            <div class="delete-calendar-warning">
                <p>
                    <strong><?php _e( 'Calendar:', 'wproject-calendar-pro' ); ?></strong>
                    <span id="delete-calendar-name"></span>
                </p>
                <p id="delete-calendar-event-count" class="calendar-event-count"></p>
            </div>

            <div class="calendar-delete-options">
                <p class="delete-options-label">
                    <strong><?php _e( 'What would you like to do with the events in this calendar?', 'wproject-calendar-pro' ); ?></strong>
                </p>

                <label class="delete-option-item">
                    <input type="radio" name="delete_option" value="transfer" checked>
                    <div class="option-details">
                        <strong><?php _e( 'Transfer events to default calendar', 'wproject-calendar-pro' ); ?></strong>
                        <p class="option-description">
                            <?php _e( 'All events will be moved to your Personal calendar, then this calendar will be deleted.', 'wproject-calendar-pro' ); ?>
                        </p>
                    </div>
                </label>

                <label class="delete-option-item delete-option-danger">
                    <input type="radio" name="delete_option" value="delete_all">
                    <div class="option-details">
                        <strong><?php _e( 'Delete calendar and all events permanently', 'wproject-calendar-pro' ); ?></strong>
                        <p class="option-description">
                            ⚠️ <?php _e( 'Warning: This action cannot be undone. All events, reminders, and attendee information will be permanently deleted.', 'wproject-calendar-pro' ); ?>
                        </p>
                    </div>
                </label>
            </div>

            <div class="calendar-delete-confirmation" style="display: none;">
                <label>
                    <input type="checkbox" id="delete-confirmation-check">
                    <?php _e( 'I understand this action cannot be undone', 'wproject-calendar-pro' ); ?>
                </label>
            </div>
        </div>

        <div class="calendar-modal-footer">
            <button type="button" class="btn btn-secondary" id="delete-calendar-cancel">
                <?php _e( 'Cancel', 'wproject-calendar-pro' ); ?>
            </button>
            <button type="button" class="btn btn-danger" id="delete-calendar-confirm" disabled>
                <?php _e( 'Delete Calendar', 'wproject-calendar-pro' ); ?>
            </button>
        </div>
    </div>
</div>

<style>
.calendar-delete-options {
    margin: 20px 0;
}

.delete-options-label {
    margin-bottom: 15px;
    font-size: 14px;
}

.delete-option-item {
    display: flex;
    gap: 12px;
    padding: 15px;
    border: 2px solid #ddd;
    border-radius: 5px;
    margin-bottom: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.delete-option-item:hover {
    border-color: #00bcd4;
    background-color: #f5f5f5;
}

.delete-option-item input[type="radio"] {
    margin-top: 3px;
}

.delete-option-item.delete-option-danger:hover {
    border-color: #f44336;
    background-color: #ffebee;
}

.delete-option-item input[type="radio"]:checked + .option-details {
    color: #00bcd4;
}

.delete-option-danger input[type="radio"]:checked + .option-details {
    color: #f44336;
}

.option-details strong {
    display: block;
    margin-bottom: 5px;
}

.option-description {
    font-size: 13px;
    color: #666;
    margin: 0;
}

.delete-calendar-warning {
    padding: 15px;
    background-color: #fff3cd;
    border-left: 4px solid #ffc107;
    margin-bottom: 20px;
    border-radius: 3px;
}

.delete-calendar-warning p {
    margin: 5px 0;
}

.calendar-event-count {
    font-weight: bold;
    color: #f44336;
}

.calendar-delete-confirmation {
    margin-top: 15px;
    padding: 12px;
    background-color: #ffebee;
    border-radius: 3px;
}

.calendar-delete-confirmation label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    font-weight: 500;
}

.btn-danger {
    background-color: #f44336;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 3px;
    cursor: pointer;
}

.btn-danger:hover:not(:disabled) {
    background-color: #d32f2f;
}

.btn-danger:disabled {
    background-color: #ccc;
    cursor: not-allowed;
}
</style>
