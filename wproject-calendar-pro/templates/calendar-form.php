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
<div id="calendar-modal" class="calendar-modal">
    <div class="calendar-modal-content">
        <div class="calendar-modal-header">
            <h3><?php _e( 'Create Calendar', 'wproject-calendar-pro' ); ?></h3>
            <button class="calendar-modal-close" aria-label="Close">&times;</button>
        </div>

        <form id="calendar-form" class="calendar-modal-body">
            <input type="hidden" id="calendar-id" name="calendar_id" value="">

            <div class="calendar-form-group">
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

            <div class="calendar-form-group">
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

            <div style="display: flex; gap: 15px;">
                <div class="calendar-form-group" style="flex: 1;">
                    <label for="calendar-color">
                        <?php _e( 'Color', 'wproject-calendar-pro' ); ?>
                    </label>
                    <input
                        type="color"
                        id="calendar-color"
                        name="color"
                        value="#00bcd4"
                        style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 3px;"
                    >
                </div>

                <div class="calendar-form-group" style="flex: 1;">
                    <label for="calendar-visibility">
                        <?php _e( 'Visibility', 'wproject-calendar-pro' ); ?>
                    </label>
                    <select id="calendar-visibility" name="visibility" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 3px;">
                        <option value="private"><?php _e( 'Private (Only me)', 'wproject-calendar-pro' ); ?></option>
                        <option value="team"><?php _e( 'Team (Team members)', 'wproject-calendar-pro' ); ?></option>
                        <option value="public"><?php _e( 'Public (Everyone)', 'wproject-calendar-pro' ); ?></option>
                    </select>
                </div>
            </div>

            <div class="calendar-modal-footer">
                <button type="button" class="btn btn-secondary" id="calendar-form-cancel">
                    <?php _e( 'Cancel', 'wproject-calendar-pro' ); ?>
                </button>
                <button type="button" class="btn btn-primary">
                    <?php _e( 'Create Calendar', 'wproject-calendar-pro' ); ?>
                </button>
            </div>
        </form>
    </div>
</div>
