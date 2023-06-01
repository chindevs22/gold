
//////////////////////////////////////////////////////////////////////////////////////////////////////
var cmcal_ColorValueChanged = false;
var cmcal_ColorValueChanged_Time = null;
var cmcal_ColorValueChanged_Editor = "";

jQuery(function () {

    var cmcal_vars_customizer = window["CMCAL_vars_" + jQuery('.cmcal-calendar').data("cmcal-id")];

////////////datepicker/////////////////////////////////////////////////////////////////////////////////
    jQuery('.cmcal-datepicker').datepicker(jQuery.datepicker.regional[ "en" ]);

//////////////////////////////////////////////////////////////////////////////////////////////////////
////////////Show Hide/////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////

    jQuery('.cmcal-section-header').on('click', function () {
        if (jQuery(this).hasClass("open"))
            return;
        cmcal_section_visibility(this);
    });
    function cmcal_section_visibility(el) {
        if (jQuery(el).hasClass("open"))
        {
            jQuery('.cmcal-section-header').show();
        } else
        {
            jQuery('.cmcal-section-header').hide();
            jQuery(el).show();
        }
        jQuery(el).toggleClass('open');
        jQuery('.cmcal-settings-back-button').toggleClass('open');
        jQuery(el).nextUntil('.cmcal-section-header').toggleClass('open');
    }

    jQuery('.cmcal-settings-back-button').on('click', function () {
        cmcal_section_visibility(jQuery('.cmcal-section-header.open'));
    });

    jQuery('.cmcal-subsection-header').on('click', function () {
        jQuery(this).toggleClass('open-subsection');
        jQuery(this).next('.cmcal-subsection-content').toggleClass('open-subsection');
    });
//////////////////////////////////////////////////////////////////////////////////////////////////////
////////////refresh-scripts-styles/////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////
    function cmcal_style_editor_changed(el) {
        if (!el.hasClass('cmcal-calendar-setting')) {
            var refresh_calendar = el.hasClass('refresh-calendar-after-styles-callback');
            refresh_cmcal_styles(refresh_calendar);
        } else {
            if (el.hasClass('refresh-calendar-styles')) {
                refresh_cmcal_styles(true);
            }
        }
    }
//////////////////////////////////////////////////////////////////////////////////////////////////////
////////////cmcal-checkbox/////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////
    jQuery('.cmcal-checkbox').on('change', function () {
        var id = jQuery(this).data("id");
        var checked_val = jQuery(this).data("checked-val");
        var unchecked_val = jQuery(this).data("unchecked-val");
        jQuery("#" + id).val((jQuery(this).is(":checked") ? checked_val : unchecked_val));
        cmcal_style_editor_changed(jQuery(this));
    });
//////////////////////////////////////////////////////////////////////////////////////////////////////
////////////cmcal-text-number/////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////
    jQuery('input.cmcal-text-number').on('keydown', function (e) {
        allowOnlyNumbers(e);
    });
    jQuery('input.cmcal-text-number').on('change', function (e) {
        cmcal_style_editor_changed(jQuery(this));
    });
//////////////////////////////////////////////////////////////////////////////////////////////////////
////////////cmcal-select/////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////

    jQuery('select.cmcal_select').on('change', function (e) {
        cmcal_style_editor_changed(jQuery(this));
    });
    jQuery('select.cmcal_border_style').select2({
//        allowClear: true,
//        placeholder: ""
    });
//////////////////////////////////////////////////////////////////////////////////////////////////////
////////////Color Picker//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////
    var cmcal_wpColorPicker_Options = {
        change: function (event, ui) {
            cmcal_ColorValueChanged_Time = (new Date()).getTime();
            cmcal_ColorValueChanged = true;
            cmcal_ColorValueChanged_Editor = jQuery(this);
        },
        clear: function (event, ui) {
            cmcal_ColorValueChanged_Time = (new Date()).getTime();
            cmcal_ColorValueChanged = true;
            cmcal_ColorValueChanged_Editor = jQuery(this);
        },
    };
    jQuery('input.cmcal-colorpicker').wpColorPicker(cmcal_wpColorPicker_Options);

    setInterval(function () {
        var now = (new Date()).getTime();
        if (cmcal_ColorValueChanged_Time != null && ((now - cmcal_ColorValueChanged_Time) > 2000) && cmcal_ColorValueChanged) {
            cmcal_style_editor_changed(cmcal_ColorValueChanged_Editor);
        }

    }, 1000);
    jQuery('.cmcal-checkbox.cmcal-color-picker-transparent').each(function () {
        cmcal_change_color_picker_visibility(this);
    });
    jQuery('.cmcal-checkbox.cmcal-color-picker-transparent').on('change', function () {
        cmcal_change_color_picker_visibility(this);
    });
    function cmcal_change_color_picker_visibility(el) {
        var id = jQuery(el).data("id");
        var color_picker = jQuery(el).closest('div.color-picker').find('.wp-picker-container');
        if (jQuery("#" + id).val() == "true")
            color_picker.hide();
        else
            color_picker.show();
    }

//////////////////////////////////////////////////////////////////////////////////////////////////////
////////////General settings///////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////

    function refresh_cmcal_calendar(el) {
        var calendar_id = jQuery('.cmcal-calendar').data("cmcal-id");
        var cmca = cmca_calendars[calendar_id];
        var view_name = cmca.view.type;
        jQuery('.qtip').qtip('destroy', true);
        cmca.destroy();
        initialize_cmcal_calendar();
        if (el != "" && (el.id != "defaultView") && !jQuery(el).hasClass("responsiveView")) {
            cmca = cmca_calendars[calendar_id];
            var current_view_name = cmca.view.type;
            if (current_view_name != view_name)
                cmca.changeView(view_name);
        }
    }

//////////////////////////////////////////////////////////////////////////////////////////////////////
////////////General settings///////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////

    jQuery('.cmcal-calendar-setting').on('change', function () {
        cmcal_vars_customizer[this.id] = jQuery(this).val();
        //Re-render calendar for live preview
        refresh_cmcal_calendar(this);
    });


//////////////////////////////////////////////////////////////////////////////////////////////////////
////////////Toolbar settings///////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////
    if (jQuery("#toolbar_settings").length) {
        jQuery("#toolbar_settings").val(get_CMCAL_var(cmcal_vars_customizer, "toolbar_json"));
    }

    jQuery('.cmcal_toolbar_select, .cmcal_toolbar_gap').on('change', function () {
        recalculate_cmcal_toolbar();
    });
    function recalculate_cmcal_toolbar() {
        var left = [];
        var center = [];
        var right = [];
        var left_str = '';
        var center_str = '';
        var right_str = '';
        jQuery('.cmcal_toolbar_editor').each(function () {
            var el = jQuery(this);
            var container = el.data("container");
            var type = el.data("type");
            var val = type == "gap" ? (el.is(":checked") ? "1" : "0") : el.val();
            var calendar_val = type == "gap" ? (el.is(":checked") ? " " : ",") : el.val();
            var obj = {'type': type, 'value': val};
            switch (container) {
                case "left":
                    left.push(obj);
                    left_str += calendar_val;
                    break;
                case "center":
                    center.push(obj);
                    center_str += calendar_val;
                    break;
                case "right":
                    right.push(obj);
                    right_str += calendar_val;
                    break;
            }
        });

        set_CMCAL_var(cmcal_vars_customizer, "toolbar_left", left_str);
        set_CMCAL_var(cmcal_vars_customizer, "toolbar_center", center_str);
        set_CMCAL_var(cmcal_vars_customizer, "toolbar_right", right_str);
        var array = {'left': left, 'center': center, 'right': right};
        jQuery("#toolbar_settings").val(JSON.stringify(array));
        //Re-render calendar for live preview
        refresh_cmcal_calendar("");
    }

    jQuery('.add_cmcal_toolbar_setting').on('click', function () {
        var data_container = jQuery(this).data("container");
        var container = jQuery('.cmcal-toolbar-' + data_container + '-container');
        if (container.has(".cmcal_toolbar_select_container").length > 0) {
            var gap = jQuery(jQuery('.cmcal-hs-editors-template-gap').html());
            var gap_editor = gap.find(".cmcal_toolbar_gap");
            gap_editor.data("container", data_container);
            gap_editor.on('change', function () {
                recalculate_cmcal_toolbar();
            });
            gap.insertBefore(this);
        }

        var select = jQuery(jQuery('.cmcal-hs-editors-template-name').html());
        var select_editor = select.find(".cmcal_toolbar_select");
        select_editor.data("container", data_container);
        select_editor.on('change', function () {
            recalculate_cmcal_toolbar();
        });
        var delete_editor = select.find(".delete_cmcal_toolbar_setting");
        delete_editor.on('click', function () {
            remove_cmcal_toolbar(this);
        });
        select.insertBefore(this);
        recalculate_cmcal_toolbar();
    });
    jQuery('.delete_cmcal_toolbar_setting').on('click', function () {
        remove_cmcal_toolbar(this);
    });
    function remove_cmcal_toolbar(el) {
        var select_container = jQuery(el).parent('.cmcal_toolbar_select_container');
        var prev = select_container.prev('div.cmcal_toolbar_gap_container')
        if (prev.length > 0)
            prev.remove();
        else
            select_container.next('div.cmcal_toolbar_gap_container').remove();
        select_container.remove();
        recalculate_cmcal_toolbar();
    }

//////////////////////////////////////////////////////////////////////////////////////////////////////
////////////Fonts/////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////

////////////Familly/////////////////////////////////////////////////////////////////////////////////////
    jQuery('select.cmcal_font_select, .cmcal_font_textalign, .cmcal_font_texttransform').select2({
        allowClear: true,
        placeholder: ""
    });
    jQuery('select.cmcal_font_select.font-famillies').on('change', function () {
        var container = jQuery(this).closest('.cmcal-section-content');
        var variants_editor = container.find('.cmcal_font_select.font-variants');
        var subsets_editor = container.find('.cmcal_font_select.font-subsets');
        variants_editor.find('option').remove();
        subsets_editor.html("");
        if (jQuery(this).val() != "") {
//            var variants = jQuery(this).find('option[value="' + jQuery(this).val() + '"]').data("variants");
            var variants = fonts[jQuery(this).val()]["variants"];
            cmcal_populate_font_variants(variants, variants_editor);
//            var subsets = jQuery(this).find('option[value="' + jQuery(this).val() + '"]').data("subsets");
            var subsets = fonts[jQuery(this).val()]["subsets"];
            cmcal_populate_font_subsets(subsets, subsets_editor);
            var str_variants = cmcal_populate_font_url_substring(variants);
            var str_subsets = cmcal_populate_font_url_substring(subsets);
            WebFont.load({
                google: {
                    families: [jQuery(this).val() + str_variants + str_subsets]
                }
            });
        } else {
            cmcal_populate_font_variants(null, variants_editor);
        }

        variants_editor.val(null);
        refresh_cmcal_styles(false);
    });
    function cmcal_populate_font_url_substring(values) {
        var str = "";
        if (values != null) {
            var arr = [];
            for (i = 0; i < values.length; i++)
                arr.push(values[i].id);
            str = ':' + arr.join(",");
        }
        return str;
    }

    jQuery('select.cmcal_font_select.font-variants').on('change', function () {
        refresh_cmcal_styles(false);
    });

    jQuery('.other_option.cmcal-calendar-setting').on('change', function () {
        var prev_input = jQuery(this).prevAll('input');
        var changed_val = jQuery(this).val();
        prev_input.val(changed_val);
        refresh_cmcal_styles(false);
    });

    function cmcal_populate_font_variants(variants, variants_editor) {
        if (variants == null) {
            variants = [];
            variants.push({id: "normal", name: "Normal"});
            variants.push({id: "bold", name: "Bold"});
            variants.push({id: "bolder", name: "Bolder"});
            variants.push({id: "lighter", name: "Lighter"});
        }
        if (variants != null) {
            for (i = 0; i < variants.length; i++) {
                var opt = document.createElement('option');
                opt.value = variants[i].id;
                opt.innerHTML = variants[i].name;
                variants_editor.append(opt);
            }
        }
    }

    function cmcal_populate_font_subsets(subsets, subsets_editor) {
        if (subsets != null) {
            var arr_subsets = [];
            for (i = 0; i < subsets.length; i++)
                arr_subsets.push(subsets[i].name);
            subsets_editor.html(arr_subsets.join(","));
        }
    }

////////////Size/////////////////////////////////////////////////////////////////////////////////////
    jQuery('input.cmcal_font_select_size.size').on('keydown', function (e) {
        allowOnlyNumbers(e);
    });
    jQuery('input.cmcal_font_select_size.size, select.cmcal_font_select_size.pxem ').on('change', function (e) {
        HiddenNumberPxEm_SetValue(jQuery(this));
    });
    jQuery('select.cmcal_font_select_size.pxem').select2();
    function HiddenNumberPxEm_SetValue(changed_element) {
        var hiddenid = changed_element.data('inputid');
        var size = jQuery('.cmcal_font_select_size.size[data-inputid="' + hiddenid + '"]').val();
        var pxem = jQuery('.cmcal_font_select_size.pxem[data-inputid="' + hiddenid + '"]').val();
        jQuery('input#' + hiddenid).val(size != "" ? size + pxem : "");
        refresh_cmcal_styles(false);
    }

//////////////////////////////////////////////////////////////////////////////////////////////////////
////////////refresh-calendar-after-styles-callback/////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////

    jQuery('input.cmcal_hidden_days_setting').on('change', function (e) {
        var arr = [];
        jQuery('input.cmcal_hidden_days_setting').each(function () {
            var el = jQuery(this);
            if (!el.is(":checked"))
                arr.push(parseInt(el.data("checked-val")));
        });

        set_CMCAL_var(cmcal_vars_customizer, "hiddenDays", JSON.stringify(arr));
        jQuery("#hiddenDays").val(JSON.stringify(arr));
        //Re-render calendar for live preview
        refresh_cmcal_calendar("");
    });

    jQuery('input.cmcal_business_days_setting').on('change', function (e) {
        var arr = [];
        jQuery('input.cmcal_business_days_setting').each(function () {
            var el = jQuery(this);
            if (el.is(":checked"))
                arr.push(parseInt(el.data("checked-val")));
        });

        set_CMCAL_var(cmcal_vars_customizer, "businessDays", JSON.stringify(arr));
        jQuery("#businessDays").val(JSON.stringify(arr));
        //Re-render calendar for live preview
        refresh_cmcal_calendar("");
    });


    ////////////cmcal-editor-info////////////////////////////////////////////////////////////////
    jQuery('.cmcal-editor-info').qtip(
            {
                style: {
                    classes: 'qtip-dark'
                },
                position: {
                    my: 'bottom center',
                    at: 'top center'
                }
            }
    );

//////////////////////////////////////////////////////////////////////////////////////////////////////
////////////cmcal_required///////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////
    init_cmcal_required();
    function init_cmcal_required() {
        jQuery.each(cmcal_required, function (el, item) {
            var element = jQuery("[name=" + el + "]");
            var element_container = element.closest(".cmcal-section-content");
            element_container.hide();
            var show = false;
            var expression = "'1'=='1'";
            if (Array.isArray(item)) {
                expression = get_cmcal_required_expression(item);
            } else {
                var expression_inner = [];
                var operator_inner = ' || ';
                jQuery.each(item, function (j, item_inner) {
                    if (Array.isArray(item_inner)) {
                        expression_inner.push(get_cmcal_required_expression(item_inner));
                    } else if (j == 'operator' && item_inner == "AND") {
                        operator_inner = ' && ';
                    }

                });
                expression = "(" + expression_inner.join(operator_inner) + ")";
            }
            if (eval(expression))
                show = true;

            if (show)
                element_container.show();

        });
    }
    function get_cmcal_required_expression(item) {
        var expression = [];
        jQuery.each(item, function (j, item_inner) {

            var check_el_val = cmcal_get_value_based_on_type(item_inner["editor_id"]);

            //Get operator
            var op = item_inner[0] == "=" ? "==" : item_inner[0];

            //Get compare value
            var compare_val = "'" + item_inner[1] + "'";

            expression.push("(" + check_el_val + op + compare_val + ")");
        });
        return "(" + expression.join(" || ") + ")";
    }
    jQuery.each(cmcal_required_editors_on_change, function (el, item) {
        var check_el = jQuery("[name=" + item + "]");
        check_el.on('change', function (e) {
            init_cmcal_required();
        });
    });

    init_cmcal_is_required_for_sections();
    function init_cmcal_is_required_for_sections() {
        jQuery.each(cmcal_is_required_for_sections, function (el, item) {
            var check_el_val = cmcal_get_value_based_on_type(el);
            var compare_val = "'" + item.required_value + "'";
            var show = check_el_val == compare_val;
            var element_container = jQuery("." + item.section_class);
            element_container.addClass("always-hide");
            if (show)
                element_container.removeClass("always-hide");

            //Append disabled
            var cmcal_section = element_container.prevAll(".cmcal-section-header:first");
            var class_disabled = '.cmcal-section-header-disabled';
            cmcal_section.find(class_disabled).hide();
            if (!show)
                cmcal_section.find(class_disabled).show();
        });
    }
    jQuery.each(cmcal_is_required_for_sections, function (el, item) {
        var check_el = jQuery("[name=" + el + "]");
        check_el.on('change', function (e) {
            init_cmcal_is_required_for_sections();
        });
    });

    function cmcal_get_value_based_on_type(el) {
        var check_el = jQuery("[name=" + el + "]");
        //Get value based on type
        var check_el_val = "";
        if (check_el.attr('type') == "radio")
            check_el_val = jQuery("[name=" + el + "]:checked").val();
        else
            check_el_val = check_el.val();
        check_el_val = "'" + check_el_val + "'";

        return check_el_val;
    }


    jQuery('#cmcal-calendar-export').on('click', function () {
        jQuery.unblockUI();
        jQuery.blockUI({message: "<div class='cmcal-spinner'></div>", blockMsgClass: 'cmcal-import-export-spinner'});
        var data = {
            'action': 'CMCAL_export',
            'calendar_id': jQuery('.cmcal-calendar').data("cmcal-id"),
        };
        jQuery.post(cmcal_vars_customizer.ajaxurl, data, function (response) {
            if (response.success == true) {
                jQuery(".cmcal-export-text").html(response.res);
                jQuery.unblockUI();
            }
        }, "json");
    });

    jQuery('#cmcal-calendar-import').on('click', function () {
        jQuery.unblockUI();
        jQuery.blockUI({message: "<div class='cmcal-spinner'></div>", blockMsgClass: 'cmcal-import-export-spinner'});
        var data = {
            'action': 'CMCAL_import',
            'calendar_id': jQuery('.cmcal-calendar').data("cmcal-id"),
            'import_data': jQuery(".cmcal-import-text").val(),
        };
        jQuery.post(cmcal_vars_customizer.ajaxurl, data, function (response) {
            if (response.success == true) {
                location.reload();
            }
        }, "json");
    });
});
//////////////////////////////////////////////////////////////////////////////////////////////////////
////////////HELPERS///////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////
function replaceAll(str, find, replace) {
    return str.replace(new RegExp(find, 'g'), replace);
}

function allowOnlyNumbers(e)
{
    // Allow: backspace, delete, tab, escape, enter and .
    if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
            // Allow: Ctrl+A, Command+A
                    (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
                    // Allow: home, end, left, right, down, up
                            (e.keyCode >= 35 && e.keyCode <= 40)) {
                // let it happen, don't do anything
                return;
            }
            // Ensure that it is a number and stop the keypress
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        }
