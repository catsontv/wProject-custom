<?php
/**
 * Event Detail Template
 *
 * Modal for viewing event details
 *
 * @package wProject Calendar Pro
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div id="event-detail-modal" class="calendar-modal">
    <div class="calendar-modal-content">
        <input type="hidden" id="detail-event-id" value="">
        <div class="calendar-modal-header">
            <h3 id="detail-title"></h3>
            <button class="calendar-modal-close">&times;</button>
        </div>

        <div class="calendar-modal-body">
            <div class="event-detail">
                <div class="event-detail-label"><?php _e( 'When', 'wproject-calendar-pro' ); ?></div>
                <div class="event-detail-value">
                    <span id="detail-start"></span> - <span id="detail-end"></span>
                </div>
            </div>

            <div class="event-detail">
                <div class="event-detail-label"><?php _e( 'Type', 'wproject-calendar-pro' ); ?></div>
                <div class="event-detail-value" id="detail-type"></div>
            </div>

            <div class="event-detail">
                <div class="event-detail-label"><?php _e( 'Location', 'wproject-calendar-pro' ); ?></div>
                <div class="event-detail-value" id="detail-location"></div>
            </div>

            <div class="event-detail">
                <div class="event-detail-label"><?php _e( 'Description', 'wproject-calendar-pro' ); ?></div>
                <div class="event-detail-value" id="detail-description"></div>
            </div>
        </div>

        <div class="calendar-modal-footer">
            <button class="btn btn-danger btn-delete-event"><?php _e( 'Delete', 'wproject-calendar-pro' ); ?></button>
            <button class="btn btn-primary btn-edit-event"><?php _e( 'Edit', 'wproject-calendar-pro' ); ?></button>
            <button class="btn btn-secondary btn-cancel"><?php _e( 'Close', 'wproject-calendar-pro' ); ?></button>
        </div>
    </div>
</div>
