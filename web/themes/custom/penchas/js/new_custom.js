jQuery(document).ready(function () {
    function toggleRadioOptions($this) {
        var checkbox = $this;
        var field_is_campus_wide_value = jQuery('#edit-field-is-campus-wide-value').val();
        var field_intended_audience_value = jQuery('#edit-field-intended-audience-value').val();
        var currentUrlWithoutParams = window.location.origin + window.location.pathname;

        var type;
        if (checkbox.prop('checked')) {
        // var type = jQuery('#edit-type-notices').val();
        var type = jQuery('#edit-type-chas-event').val();
        } else {
        // var type = jQuery('#edit-type-chas-event').val();
        var type = jQuery('#edit-type-notices').val();
        }
        // console.log(type);
        console.log(currentUrlWithoutParams+'?type='+type+'&field_is_campus_wide_value='+field_is_campus_wide_value+'&field_intended_audience_value='+field_intended_audience_value);
        var url = currentUrlWithoutParams+'?type='+type+'&field_is_campus_wide_value='+field_is_campus_wide_value+'&field_intended_audience_value='+field_intended_audience_value;
        return url;
    }
        
    // Call toggleRadioOptions on checkbox change
    jQuery('#edit-type-exclude-notices').on('click',function() {
        var url = toggleRadioOptions(jQuery(this).find('input'));
        // window.location.href = url;
    });

})