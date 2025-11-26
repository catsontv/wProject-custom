<?php
/**
 * Calendar Management Modal Template
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

<div id="calendar-management-modal" class="calendar-modal">
    <div class="calendar-modal-content">
        <div class="calendar-modal-header">
            <h3 id="calendar-modal-title"><?php _e( 'New Calendar', 'wproject-calendar-pro' ); ?></h3>
            <button class="calendar-modal-close">&times;</button>
        </div>

        <div class="calendar-modal-body">
            <form id="calendar-form">
                <input type="hidden" id="calendar-form-id" name="calendar_id" value="">

                <div class="form-row">
                    <label for="calendar-name">
                        <?php _e( 'Calendar Name', 'wproject-calendar-pro' ); ?>
                        <span class="required">*</span>
                    </label>
                    <input type="text" 
                           id="calendar-name" 
                           name="calendar_name" 
                           placeholder="<?php esc_attr_e( 'My Calendar', 'wproject-calendar-pro' ); ?>" 
                           maxlength="255" 
                           required>
                </div>

                <div class="form-row">
                    <label for="calendar-description">
                        <?php _e( 'Description', 'wproject-calendar-pro' ); ?>
                    </label>
                    <textarea id="calendar-description" 
                              name="calendar_description" 
                              rows="3" 
                              placeholder="<?php esc_attr_e( 'Optional description', 'wproject-calendar-pro' ); ?>"></textarea>
                </div>

                <div class="form-row">
                    <label><?php _e( 'Color', 'wproject-calendar-pro' ); ?></label>
                    <div class="calendar-color-picker">
                        <input type="hidden" id="calendar-color-value" name="calendar_color" value="#00bcd4">
                        
                        <div class="calendar-color-options">
                            <div class="calendar-color-option selected" data-color="#00bcd4" style="background-color: #00bcd4;" title="Cyan"></div>
                            <div class="calendar-color-option" data-color="#4caf50" style="background-color: #4caf50;" title="Green"></div>
                            <div class="calendar-color-option" data-color="#ff9800" style="background-color: #ff9800;" title="Orange"></div>
                            <div class="calendar-color-option" data-color="#f44336" style="background-color: #f44336;" title="Red"></div>
                            <div class="calendar-color-option" data-color="#9c27b0" style="background-color: #9c27b0;" title="Purple"></div>
                            <div class="calendar-color-option" data-color="#2196f3" style="background-color: #2196f3;" title="Blue"></div>
                            <div class="calendar-color-option" data-color="#ffeb3b" style="background-color: #ffeb3b;" title="Yellow"></div>
                            <div class="calendar-color-option" data-color="#607d8b" style="background-color: #607d8b;" title="Gray"></div>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <label for="calendar-visibility">
                        <?php _e( 'Visibility', 'wproject-calendar-pro' ); ?>
                    </label>
                    <select id="calendar-visibility" name="calendar_visibility">
                        <option value="private"><?php _e( 'Private (Only Me)', 'wproject-calendar-pro' ); ?></option>
                        <option value="team"><?php _e( 'Team', 'wproject-calendar-pro' ); ?></option>
                        <option value="public"><?php _e( 'Public', 'wproject-calendar-pro' ); ?></option>
                    </select>
                </div>
            </form>
        </div>

        <div class="calendar-modal-footer">
            <button class="btn btn-secondary btn-cancel">
                <?php _e( 'Cancel', 'wproject-calendar-pro' ); ?>
            </button>
            <button class="btn btn-primary btn-save-calendar">
                <?php _e( 'Save Calendar', 'wproject-calendar-pro' ); ?>
            </button>
        </div>
    </div>
</div>
