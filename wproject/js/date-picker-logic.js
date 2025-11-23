jQuery(document).ready(function() {
    // Set a minimum date for both fields
    var today = moment().format("YYYY-MM-DD");
    jQuery(".pick-start-date").attr("min", today);
    jQuery(".pick-end-date").attr("min", today);

    // When a date is selected in either field, update the disabled dates
    jQuery(".pick-start-date, .pick-end-date").change(function() {
        var startDate = moment(jQuery(".pick-start-date").val(), "YYYY-MM-DD");
        var endDate = moment(jQuery(".pick-end-date").val(), "YYYY-MM-DD");

        // Disable dates before the project start date or after the project end date
        jQuery(".pick-start-date").attr("max", endDate.format("YYYY-MM-DD"));
        jQuery(".pick-end-date").attr("min", startDate.format("YYYY-MM-DD"));

        // Disable dates that are not within the project date range
        jQuery(".pick-start-date, .pick-end-date").each(function() {
        var field = jQuery(this);
        jQuery(".pick-start-date, .pick-end-date").not(field).each(function() {
            var otherField = jQuery(this);
            var otherFieldValue = moment(otherField.val(), "YYYY-MM-DD");
            var minDate = field.attr("min") ? moment(field.attr("min"), "YYYY-MM-DD") : null;
            var maxDate = field.attr("max") ? moment(field.attr("max"), "YYYY-MM-DD") : null;

            jQuery("option", otherField).each(function() {
            var optionValue = moment(jQuery(this).val(), "YYYY-MM-DD");
            if ((minDate && optionValue.isBefore(minDate)) || (maxDate && optionValue.isAfter(maxDate))) {
                jQuery(this).prop("disabled", true);
            } else {
                jQuery(this).prop("disabled", false);
            }
            });
        });
        });
    });
});