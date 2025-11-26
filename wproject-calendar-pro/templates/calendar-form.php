<?php
/**
 * Calendar Form Template
 *
 * Modal for creating/editing calendars
 *
 * @package wProject Calendar Pro
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<!-- Calendar Modal -->
<div id="calendar-modal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><?php _e( 'Create Calendar', 'wproject-calendar-pro' ); ?></h3>
            <button class="modal-close" aria-label="Close">&times;</button>
        </div>

        <form id="calendar-form" class="calendar-form">
            <input type="hidden" id="calendar-id" name="calendar_id" value="">

            <div class="form-group">
                <label for="calendar-name">
                    <?php _e( 'Calendar Name', 'wproject-calendar-pro' ); ?>
                    <span class="required">*</span>
                </label>
                <input
                    type="text"
                    id="calendar-name"
                    name="name"
                    placeholder="<?php esc_attr_e( 'e.g., Work Calendar', 'wproject-calendar-pro' ); ?>"
                    required
                    maxlength="255"
                >
            </div>

            <div class="form-group">
                <label for="calendar-description">
                    <?php _e( 'Description', 'wproject-calendar-pro' ); ?>
                </label>
                <textarea
                    id="calendar-description"
                    name="description"
                    placeholder="<?php esc_attr_e( 'Optional description for this calendar', 'wproject-calendar-pro' ); ?>"
                    rows="4"
                    maxlength="1000"
                ></textarea>
            </div>

            <div class="form-row">
                <div class="form-group form-group-half">
                    <label for="calendar-color">
                        <?php _e( 'Color', 'wproject-calendar-pro' ); ?>
                    </label>
                    <input
                        type="color"
                        id="calendar-color"
                        name="color"
                        value="#00bcd4"
                    >
                </div>

                <div class="form-group form-group-half">
                    <label for="calendar-visibility">
                        <?php _e( 'Visibility', 'wproject-calendar-pro' ); ?>
                    </label>
                    <select id="calendar-visibility" name="visibility">
                        <option value="private"><?php _e( 'Private (Only me)', 'wproject-calendar-pro' ); ?></option>
                        <option value="team"><?php _e( 'Team (Team members)', 'wproject-calendar-pro' ); ?></option>
                        <option value="public"><?php _e( 'Public (Everyone)', 'wproject-calendar-pro' ); ?></option>
                    </select>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" id="calendar-form-cancel">
                    <?php _e( 'Cancel', 'wproject-calendar-pro' ); ?>
                </button>
                <button type="submit" class="btn btn-primary">
                    <?php _e( 'Create Calendar', 'wproject-calendar-pro' ); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.modal-content {
    background: #fff;
    border-radius: 5px;
    box-shadow: 0 5px 30px rgba(0, 0, 0, 0.3);
    width: 90%;
    max-width: 500px;
    padding: 0;
    overflow: hidden;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    border-bottom: 1px solid #e0e0e0;
    background: #f9f9f9;
}

.modal-header h3 {
    margin: 0;
    font-size: 1.3em;
    color: #5b606c;
}

.modal-close {
    background: none;
    border: none;
    font-size: 28px;
    cursor: pointer;
    color: #999;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-close:hover {
    color: #5b606c;
}

.calendar-form {
    padding: 25px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #5b606c;
    font-size: 0.95em;
}

.form-group input[type="text"],
.form-group input[type="color"],
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 3px;
    font-family: inherit;
    font-size: 1em;
}

.form-group input[type="text"]:focus,
.form-group input[type="color"]:focus,
.form-group textarea:focus,
.form-group select:focus {
    border-color: #00bcd4;
    outline: none;
    box-shadow: 0 0 0 3px rgba(0, 188, 212, 0.1);
}

.form-group textarea {
    resize: vertical;
}

.form-row {
    display: flex;
    gap: 15px;
}

.form-group-half {
    flex: 1;
}

.required {
    color: #e74c3c;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 15px 25px;
    border-top: 1px solid #e0e0e0;
    background: #f9f9f9;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 3px;
    cursor: pointer;
    font-family: 'Quicksand', sans-serif;
    font-weight: 600;
    font-size: 0.95em;
    transition: all 0.2s ease;
}

.btn-cancel {
    background: #f3f3f3;
    color: #5b606c;
}

.btn-cancel:hover {
    background: #e0e0e0;
}

.btn-primary {
    background: #00bcd4;
    color: #fff;
}

.btn-primary:hover {
    background: #0097a7;
}

@media (max-width: 600px) {
    .modal-content {
        width: 95%;
        max-width: 100%;
    }

    .form-row {
        flex-direction: column;
    }

    .form-group-half {
        flex: 1;
    }
}
</style>
