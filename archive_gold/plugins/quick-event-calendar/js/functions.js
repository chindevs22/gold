jQuery(document).ready(function(){
    jQuery('.qcc_use_date_meta_hidden').hide();
    if(jQuery('#qcc_use_date_meta').is(':checked')) {
        jQuery('.qcc_use_date_meta_hidden').slideDown();
    } else {
        jQuery('.qcc_use_date_meta_hidden').slideUp();
    }

    jQuery('.qcc-color-picker').wpColorPicker();

    jQuery(document).on('click', '#qcc_use_date_meta', function(e) {
        if(jQuery('#qcc_use_date_meta').is(':checked')) {
            jQuery('.qcc_use_date_meta_hidden').slideDown();
        } else {
            jQuery('.qcc_use_date_meta_hidden').slideUp();
        }
    });
});
