/**
 * Calendar Pro Admin JavaScript
 *
 * Handles color picker and admin interface interactions
 *
 * @package wProject Calendar Pro
 * @since 1.0.0
 */

(function($) {
    'use strict';

    $(document).ready(function() {

        // Initialize color pickers
        if ($.fn.wpColorPicker) {
            $('.colour-picker').wpColorPicker();
        }

    });

})(jQuery);
