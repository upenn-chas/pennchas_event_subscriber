jQuery(document).ready(function () {
    function toggleRadioOptions(checkbox) {
        var field_is_campus_wide_value = jQuery('#edit-field-is-campus-wide-value').val();
        var field_intended_audience_value = jQuery('#edit-field-intended-audience-value').val();
        var currentUrlWithoutParams = window.location.origin + window.location.pathname;

        var type;
        if (checkbox.prop('checked')) {
            type = jQuery('#edit-type-chas-event').val();
        } else {
            type = jQuery('#edit-type-notices').val();
        }
        
        console.log(type);
        var url = currentUrlWithoutParams + '?type=' + type + '&field_is_campus_wide_value=' + field_is_campus_wide_value + '&field_intended_audience_value=' + field_intended_audience_value;
        return url;
    }

    // Simplified on click handler
    jQuery('#edit-type-exclude-notices').on('change', function() {
        var url = toggleRadioOptions(jQuery(this)); 
        window.location.href = url;
    });
});
