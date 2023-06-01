
jQuery(document).ready(function () {
    jQuery('#cmcal-calendar-save').on('click', function () {
        jQuery.unblockUI();
        jQuery.blockUI({message: "<div class='cmcal-edit-form-spinner'></div>", blockMsgClass: 'cmcal-edit-form-blockUI'});
        var data = {
            'action': 'CMCAL_edit_calendar',
            'calendar_action': jQuery('#calendar_action').val(),
            'calendar_name': jQuery('#calendar_name').val(),
            'calendar_id': jQuery('#calendar_id').val(),
        };
        jQuery.post(CMCAL_admin_edit_calendar_vars.ajaxurl, data, function (response) {
            if (response.success == true) {
                location.reload();
            }
        }, "json");
    });
    jQuery('#cmcal-calendar-add-new').on('click', function () {
        jQuery.unblockUI();
        jQuery.blockUI({
            message: jQuery('#cmcal-calendar-edit-form'), blockMsgClass: 'cmcal-edit-form-blockUI'
        });
        jQuery('#calendar_action').val("insert");
        jQuery('#calendar_id').val("");
        jQuery('#calendar_name').val("");

    });
    jQuery('input[name="cmcal-calendar-update"]').on('click', function () {
        jQuery.unblockUI();
        jQuery.blockUI({
            message: jQuery('#cmcal-calendar-edit-form'), blockMsgClass: 'cmcal-edit-form-blockUI'
        });
        jQuery('#calendar_action').val("update");
        jQuery('#calendar_id').val(jQuery(this).data("calendar_id"));
        jQuery('#calendar_name').val(jQuery(this).data("calendar_name"));

    });
    jQuery('input[name="cmcal-calendar-delete"]').on('click', function () {
        var c = confirm(CMCAL_admin_edit_calendar_vars.delete_options_confirm_message);
        if (c)
        {
            jQuery.unblockUI();
            jQuery.blockUI({message: "<div class='cmcal-edit-form-spinner'></div>", blockMsgClass: 'cmcal-edit-form-blockUI'});
            var data = {
                'action': 'CMCAL_edit_calendar',
                'calendar_action': "delete",
                'calendar_id': jQuery(this).data("calendar_id"),
            };
            jQuery.post(CMCAL_admin_edit_calendar_vars.ajaxurl, data, function (response) {
                if (response.success == true) {
                    location.reload();
                }
            }, "json");
        }
    });
    jQuery('#cmcal-calendar-cancel').on('click', function () {
        jQuery.unblockUI();
    });

    jQuery('.cmcal-calendar-settings-section .preview.button.cmcal-btn').on('click', function (e) {
        e.stopPropagation();
    });
});