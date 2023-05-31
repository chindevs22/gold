document.addEventListener('DOMContentLoaded', function () {
    initialize_cmcal_calendar();
});
var cmca_calendars = {};
var cmcal_month_days_with_events = {};

function get_CMCAL_var(cmcal_vars, name) {
    return cmcal_vars[name];
}
function set_CMCAL_var(cmcal_vars, name, value) {
    return cmcal_vars[name] = value;
}
function get_CMCAL_var_multilingual(cmcal_vars, name) {
    if (cmcal_vars['wpml'])
        return cmcal_vars[name + "_" + cmcal_vars['language_code']];
    else
        return cmcal_vars[name];
}
function get_CMCAL_var_date_format(cmcal_vars, name) {
    var format = cmcal_vars[name];
    if (format == "custom")
        format = cmcal_vars[name + "_Custom"];
    return format;
}

function get_CMCAL_maxtime(cmcal_vars) {
    var minTime = cmcal_vars["minTime"];
    var maxTime = cmcal_vars["maxTime"];
    if (cmcal_vars["maxTime"] == '00:00:00')
        return "24:00:00";
    if (moment(minTime, 'HH:mm') >= moment(maxTime, 'HH:mm'))
        return "24:00:00";

    return maxTime;

}

function get_cmcal_tooltip_styles(tooltipSettings) {
    var tooltip_show_animation = tooltipSettings.tooltip_show_animation;
    var tooltip_show_animation_time = parseInt(tooltipSettings.tooltip_show_animation_time);
    var animation_show_function = function () {
        if (tooltip_show_animation == 'fade') {
            jQuery(this).fadeIn(tooltip_show_animation_time);
        } else if (tooltip_show_animation == 'slide_down') {
            jQuery(this).slideDown(tooltip_show_animation_time);
        }
    }
    var tooltip_hide_animation = tooltipSettings.tooltip_hide_animation;
    var tooltip_hide_animation_time = parseInt(tooltipSettings.tooltip_hide_animation_time);
    var animation_hide_function = function () {
        if (tooltip_hide_animation == 'fade') {
            jQuery(this).fadeOut(tooltip_hide_animation_time);
        } else if (tooltip_hide_animation == 'slide_down') {
            jQuery(this).slideUp(tooltip_hide_animation_time);
        }
    }
    var extra_classes = '';
    if (tooltipSettings.tooltip_shadow == '1') {
        extra_classes += 'qtip-shadow ';
    }
    if (tooltipSettings.tooltip_rounded == '1') {
        extra_classes += 'qtip-rounded ';
    }
    var cmcal_tooltip_styles = {
        content: {
            text: tooltipSettings.tooltip_fixed_template,
        },
        show: {
            solo: tooltipSettings.tooltip_solo == "true" ? true : false,
            delay: tooltipSettings.tooltip_show_delay,
            event: tooltipSettings.tooltip_show_event == "hover" ? 'mouseenter' : 'click',
            effect: animation_show_function,
        },
        hide: {
            delay: tooltipSettings.tooltip_hide_delay,
            event: tooltipSettings.tooltip_hide_event == "hover" ? 'mouseleave' : 'click',
            effect: animation_hide_function,
            inactive: parseInt(tooltipSettings.tooltip_inactive)
        },
        position: {
            my: tooltipSettings.tooltip_my,
            at: tooltipSettings.tooltip_at,
            target: tooltipSettings.tooltip_target == "event" ? 'default' : 'mouse',
            adjust: {
                mouse: tooltipSettings.tooltip_mouse == "true" ? true : false,
            }
        },
        style: {
            def: false,
            classes: "cmcal-calendar-container cmcal-tooltip " + extra_classes + "cmcal-calendar-" + tooltipSettings.calendar_id
        }
    };
    // If modal, set correct paremeters
    if (tooltipSettings.tooltip_modal == '1') {
        cmcal_tooltip_styles.content.button = "true";
        cmcal_tooltip_styles.show.modal = {
            on: true
        };
        cmcal_tooltip_styles.position.my = "center";
        cmcal_tooltip_styles.position.at = "center";
        cmcal_tooltip_styles.position.target = jQuery(window);
    }
    return cmcal_tooltip_styles
}

function cmcal_responsive_styles(calendar, view_name, width, responsiveViews) {
    var cmca = cmca_calendars[calendar.data("cmcal-id")];
    var jmediaquery = window.matchMedia("(max-width: " + width + "px)");
//    if (window.innerWidth < width) {
    if (jmediaquery.matches) {
        if (!calendar.data("cmcal_view_changed_from_responsive")) {
            calendar.data("cmcal_view_changed_from_responsive", true);
            calendar.data("cmcal_last_selected_view_name", view_name);
            cmca.changeView(responsiveViews[view_name]);
        }
    } else {
        if (calendar.data("cmcal_view_changed_from_responsive")) {
            cmca.changeView(calendar.data("cmcal_last_selected_view_name"));
            calendar.data("cmcal_view_changed_from_responsive", false);
        }
    }
}

function cmcal_get_defaultDate(defaultDateMode, defaultDate_MovableDate, defaultDate_CertainDate) {
    switch (defaultDateMode) {
        case "0":
            switch (defaultDate_MovableDate) {
                case "0":
                    return  moment().format('YYYY-MM-DD');
                    break;
                case "1":
                    return moment().startOf('week').format('YYYY-MM-DD');
                    break;
                case "2":
                    return moment().startOf('isoweek').format('YYYY-MM-DD');
                    break;
                case "3":
                    return moment().startOf('month').format('YYYY-MM-DD');
                    break;
            }
            break;
        case "1":
            return  (defaultDate_CertainDate == "" ? moment() : moment(defaultDate_CertainDate, "MM/D/YYYY")).format('YYYY-MM-DD');
            break;
    }
}

function cmcal_get_titleFormat(date, format) {
    return  moment(date.date).format(format);
}

function cmcal_enumerateDaysBetweenDates(startDate, endDate, startDateMonth, endDateMonth) {
    var daysOfYear = [];
    var currDate = startDate > startDateMonth ? startDate : startDateMonth;
    var lastDate = currDate;
    if (endDate != null)
    {
        lastDate = endDate < endDateMonth ? endDate : endDateMonth;
        for (var d = currDate; d <= lastDate; d.setDate(d.getDate() + 1)) {
            var striped_date = cmcal_get_date_without_TimezoneOffset(d);
            daysOfYear.push(moment(striped_date).format('YYYY-MM-DD'));
        }
    } else {
        var striped_date = cmcal_get_date_without_TimezoneOffset(currDate);
        daysOfYear.push(moment(striped_date).format('YYYY-MM-DD'));
    }
    return daysOfYear;
}

function cmcal_set_filter_calendar(calendar, calendar_id, cmcal_vars) {
    var filter_area = jQuery(".cmcal-calendar-" + calendar_id + " .cmcal-calendar-filter-area");
    filter_area.empty();
    var template = get_CMCAL_var(cmcal_vars, "filter_template");
    if (get_CMCAL_var(cmcal_vars, "filter_visibility") == 'false')
        return;
    //Search
    var search_box_name = 'cmcal-search-' + calendar_id;
    template = template.replace("[search_box]", "<input type='text' name='" + search_box_name + "' placeholder='" + get_CMCAL_var_multilingual(cmcal_vars, "search_box_label") + "'/>");
    template = template.replace("[search_box_label]", get_CMCAL_var_multilingual(cmcal_vars, "search_box_label"));

    //Date Filter
    var date_navigator_box_name = 'cmcal-date-navigator-' + calendar_id;
    template = template.replace("[date_navigator]", "<input type='text' name='" + date_navigator_box_name + "' placeholder='" + get_CMCAL_var_multilingual(cmcal_vars, "date_navigator_label") + "'/>");
    template = template.replace("[date_navigator_label]", get_CMCAL_var_multilingual(cmcal_vars, "date_navigator_label"));

    //Filter Taxonomies
    for (i = 0; i < taxonomies_event_template_filter.length; i++) {
        var tax_name = taxonomies_event_template_filter[i];
        var tax_box_name = 'cmcal-tax-filter-filter_box_' + tax_name + "-" + calendar_id;
        template = template.replace("[filter_box_" + tax_name + "]", "<select name='" + tax_box_name + "'></select>");
        template = template.replace("[filter_box_" + tax_name + "_label]", get_CMCAL_var_multilingual(cmcal_vars, "filter_box_" + tax_name + "_label"));

    }
    var cmca_OtherCalendar_id = cmcal_get_navToOtherCalendar(cmcal_vars);
    var cmca_OtherCalendar = null;
    if (cmca_OtherCalendar_id != null) {
        cmca_OtherCalendar = jQuery("#cmcal_calendar_" + cmca_OtherCalendar_id);
        var CMCAL_vars_OtherCalendar_id = window["CMCAL_vars_" + cmca_OtherCalendar_id];
        if (typeof (CMCAL_vars_OtherCalendar_id) != "undefined") {
            if (CMCAL_vars_OtherCalendar_id.filter_visibility == "false") {
                CMCAL_vars_OtherCalendar_id.filter_visibility = "true"
                CMCAL_vars_OtherCalendar_id.filter_parent_calendar_id = calendar.data("cmcal-id");
            }
        }
    }
    filter_area.append(template);

    //Search Setup
    jQuery('input[name=' + search_box_name + ']').on('keyup', function (e) {
        cmcal_filterCalendar(calendar);
        if (cmca_OtherCalendar != null) {
            cmcal_filterCalendar(cmca_OtherCalendar);
        }
    });

    //Date Filter Setup
    jQuery('input[name=' + date_navigator_box_name + ']').datepicker({
        //comment the beforeShow handler if you want to see the ugly overlay
        beforeShow: function () {
            setTimeout(function () {
                jQuery('.ui-datepicker').css('z-index', 99999999999999);
            }, 0);
        }
    });
    jQuery('input[name=' + date_navigator_box_name + ']').on('change', function (e) {
        var date = jQuery(this).datepicker('getDate');
        var cmca = cmca_calendars[calendar.data("cmcal-id")];
        if (date == null)
            date = cmca.getOption('defaultDate');
        else
            date = moment(date).format('YYYY-MM-DD');
        cmca.gotoDate(date);
        if (cmca_OtherCalendar_id != null) {
            var cmca_Other = cmca_calendars[cmca_OtherCalendar_id];
            if (typeof (cmca_Other) != "undefined") {
                cmca_Other.gotoDate(date);
            }
        }
    });

    //Filter Taxonomies Setup
    for (i = 0; i < taxonomies_event_template_filter.length; i++) {
        var tax_name = taxonomies_event_template_filter[i];
        var tax_box_name = 'cmcal-tax-filter-filter_box_' + tax_name + "-" + calendar_id;
        var tax_box_selector = 'select[name=' + tax_box_name + ']';
        cmcal_set_filter_options_of_tax(tax_box_selector, cmcal_vars, tax_name);
        jQuery(tax_box_selector).on('change', function (e) {
            cmcal_filterCalendar(calendar);
            if (cmca_OtherCalendar != null) {
                cmcal_filterCalendar(cmca_OtherCalendar);
            }
        });
    }
}

function cmcal_set_filter_options_of_tax(tax_box_selector, cmcal_vars, tax_name) {
    jQuery(tax_box_selector).empty();
    jQuery.ajax({
        dataType: 'json',
        type: "GET",
        url: cmcal_vars.ajaxurl,
        data: {action: "CMCAL_get_tax_lov", tax_name: tax_name},
        success: function (data) {
            var all_events_text = get_CMCAL_var_multilingual(cmcal_vars, "filter_box_" + tax_name + "_all_events_text");
            jQuery(tax_box_selector).append('<option value="">' + all_events_text + '</option>');
            // Use jQuery's each to iterate over the opts value
            jQuery.each(data, function (i, d) {
                // You will need to alter the below to get the right values from your json object.  Guessing that d.id / d.modelName are columns in your carModels data
                jQuery(tax_box_selector).append('<option value="' + d.term_id + '">' + d.name + '</option>');
            });
        }
    });

    if (get_CMCAL_var(cmcal_vars, "select2_for_tax") == 'true') {
        jQuery(tax_box_selector).select2({
            width: '100%',
            placeholder: '',
            allowClear: true,
        });
    }
}

function initialize_cmcal_calendar() {

    jQuery('.cmcal-calendar').each(function () {

        var calendar = jQuery(this);
        var calendar_id = calendar.data("cmcal-id");
        var cmcal_vars = window["CMCAL_vars_" + calendar_id];
        cmcal_set_filter_calendar(calendar, calendar_id, cmcal_vars);
        calendar.data("cmcal_view_changed_from_responsive", false);
        var responsiveViews = {
            dayGridMonth: get_CMCAL_var(cmcal_vars, "dayGridMonthResponsiveView"),
            dayGridWeek: get_CMCAL_var(cmcal_vars, "dayGridWeekResponsiveView"),
            dayGridDay: get_CMCAL_var(cmcal_vars, "dayGridDayResponsiveView"),
            timeGridWeek: get_CMCAL_var(cmcal_vars, "timeGridWeekResponsiveView"),
            timeGridDay: get_CMCAL_var(cmcal_vars, "timeGridDayResponsiveView"),
            listYear: get_CMCAL_var(cmcal_vars, "listYearResponsiveView"),
            listMonth: get_CMCAL_var(cmcal_vars, "listMonthResponsiveView"),
            listWeek: get_CMCAL_var(cmcal_vars, "listWeekResponsiveView"),
            listDay: get_CMCAL_var(cmcal_vars, "listDayResponsiveView"),
            listDuration: get_CMCAL_var(cmcal_vars, "listDurationResponsiveView"),
        };
        var calendar_settings = {
            calendar_id: calendar_id,
            plugins: ['dayGrid', 'timeGrid', 'list', 'interaction'],
            timeZone: false,
            defaultView: get_CMCAL_var(cmcal_vars, "defaultView"),
            firstDay: get_CMCAL_var(cmcal_vars, "firstDay") == "-1" ? moment().weekday() : parseInt(get_CMCAL_var(cmcal_vars, "firstDay")),
            minTime: get_CMCAL_var(cmcal_vars, "minTime"),
            maxTime: get_CMCAL_maxtime(cmcal_vars), // get_CMCAL_var(cmcal_vars, "maxTime"),
            slotLabelFormat: function (date) {
                return cmcal_listFormat(date, cmcal_vars, "slotLabelFormat");
            },
            allDaySlot: get_CMCAL_var(cmcal_vars, "allDaySlot") == "true" ? true : false,
            showNonCurrentDates: get_CMCAL_var(cmcal_vars, "showNonCurrentDates") == "true" ? true : false,
            slotDuration: cmcal_convert_zero_to_one(get_CMCAL_var(cmcal_vars, "slotDuration")) * 60 * 1000,
            slotLabelInterval: get_CMCAL_var(cmcal_vars, "slotLabelInterval") * 60 * 1000,
            slotEventOverlap: get_CMCAL_var(cmcal_vars, "slotEventOverlap") == "true" ? true : false,
            dir: get_CMCAL_var(cmcal_vars, "isRTL") == "true" ? "rtl" : "ltr",
            hiddenDays: get_CMCAL_var(cmcal_vars, "hiddenDays") ? JSON.parse(get_CMCAL_var(cmcal_vars, "hiddenDays")) : [],
//            timeFormat: get_CMCAL_var_date_format(cmcal_vars, "timeFormat"),
            fixedWeekCount: get_CMCAL_var(cmcal_vars, "fixedWeekCount") == "true" ? true : false,
            eventLimit: (get_CMCAL_var(cmcal_vars, "eventsLimitEnabled") == "1" && get_CMCAL_var(cmcal_vars, "eventLimit") != "") ? parseInt(get_CMCAL_var(cmcal_vars, "eventLimit")) : "", // for all non-agenda views
            eventLimitText: get_CMCAL_var_multilingual(cmcal_vars, "eventLimitText"),
            aspectRatio: get_CMCAL_var(cmcal_vars, "aspectRatio") != "" ? parseFloat(get_CMCAL_var(cmcal_vars, "aspectRatio")).toFixed(2) : "1.35",
//            navLinks: get_CMCAL_var(cmcal_vars, "navLinks") != "false" ? true : false,
            navLinks: get_CMCAL_var(cmcal_vars, "navLinks") == "true" ? true : false,
            noEventsMessage: get_CMCAL_var_multilingual(cmcal_vars, "noEventsMessage"),
            dayPopoverFormat: function (date) {
                return cmcal_listFormat(date, cmcal_vars, "dayPopoverFormat");
            },
            nextDayThreshold: "00:00:00",
            defaultDate: cmcal_get_defaultDate(get_CMCAL_var(cmcal_vars, "defaultDateMode"), get_CMCAL_var(cmcal_vars, "defaultDate_MovableDate"), get_CMCAL_var(cmcal_vars, "defaultDate_CertainDate")),
            editable: false,
            titleRangeSeparator: ' \u2013 ',
            height: "auto",
            locale: get_CMCAL_var(cmcal_vars, "language_code"),
            defaultTimedEventDuration: cmcal_minutes_to_time(get_CMCAL_var(cmcal_vars, "eventDuration")),
            buttonText: {
                today: get_CMCAL_var_multilingual(cmcal_vars, "buttonText_today"),
                dayGridMonth: get_CMCAL_var_multilingual(cmcal_vars, "buttonText_month"),
                dayGridWeek: get_CMCAL_var_multilingual(cmcal_vars, "buttonText_week"),
                timeGridWeek: get_CMCAL_var_multilingual(cmcal_vars, "buttonText_agendaWeek"),
                dayGridDay: get_CMCAL_var_multilingual(cmcal_vars, "buttonText_day"),
                timeGridDay: get_CMCAL_var_multilingual(cmcal_vars, "buttonText_agendaDay"),
                listYear: get_CMCAL_var_multilingual(cmcal_vars, "buttonText_listYear"),
                listMonth: get_CMCAL_var_multilingual(cmcal_vars, "buttonText_listMonth"),
                listWeek: get_CMCAL_var_multilingual(cmcal_vars, "buttonText_listWeek"),
                listDay: get_CMCAL_var_multilingual(cmcal_vars, "buttonText_listDay"),
                listDuration: get_CMCAL_var_multilingual(cmcal_vars, "buttonText_listDuration"), // version 2.0 add
            },
            views: {
                dayGridMonth: {
                    columnHeaderText: function (date) {
                        return cmcal_columnHeaderFormat(date, jQuery(this)[0].calendar_id, "columnFormatMonth");
                    }
                },
                dayGridWeek: {
                    columnHeaderText: function (date) {
                        return cmcal_columnHeaderFormat(date, jQuery(this)[0].calendar_id, "columnFormatWeek");
                    }
                },
                dayGridDay: {
                    columnHeaderText: function (date) {
                        return cmcal_columnHeaderFormat(date, jQuery(this)[0].calendar_id, "columnFormatDay");
                    }
                },
                timeGridWeek: {
                    columnHeaderText: function (date) {
                        return cmcal_columnHeaderFormat(date, jQuery(this)[0].calendar_id, "columnFormatWeek");
                    }
                },
                timeGridDay: {
                    columnHeaderText: function (date) {
                        return cmcal_columnHeaderFormat(date, jQuery(this)[0].calendar_id, "columnFormatDay");
                    }
                },
                listYear: {
                    listDayFormat: function (date) {
                        return cmcal_listFormat(date, cmcal_vars, "listDayFormat_Year");
                    },
                    listDayAltFormat: function (date) {
                        return cmcal_listFormat(date, cmcal_vars, "listDayAltFormat_Year");
                    }
                },
                listMonth: {
                    listDayFormat: function (date) {
                        return cmcal_listFormat(date, cmcal_vars, "listDayFormat_Month");
                    },
                    listDayAltFormat: function (date) {
                        return cmcal_listFormat(date, cmcal_vars, "listDayAltFormat_Month");
                    }
                },
                listWeek: {
                    listDayFormat: function (date) {
                        return cmcal_listFormat(date, cmcal_vars, "listDayFormat_Week");
                    },
                    listDayAltFormat: function (date) {
                        return cmcal_listFormat(date, cmcal_vars, "listDayAltFormat_Week");
                    }
                },
                listDay: {
                    listDayFormat: function (date) {
                        return cmcal_listFormat(date, cmcal_vars, "listDayFormat_Day");
                    },
                    listDayAltFormat: function (date) {
                        return cmcal_listFormat(date, cmcal_vars, "listDayAltFormat_Day");
                    }
                },
                listDuration: {
                    type: 'list',
                    duration: {days: cmcal_convert_null_or_zero_to_1(get_CMCAL_var(cmcal_vars, "list_duration_days"))},
                    listDayFormat: function (date) {
                        return cmcal_listFormat(date, cmcal_vars, "listDayFormat_Duration");
                    },
                    listDayAltFormat: function (date) {
                        return cmcal_listFormat(date, cmcal_vars, "listDayAltFormat_Duration");
                    }
                },
            },
            header: get_CMCAL_var(cmcal_vars, "toolbar_visibility") == "true" ? {
                left: get_CMCAL_var(cmcal_vars, "toolbar_left"),
                center: get_CMCAL_var(cmcal_vars, "toolbar_center"),
                right: get_CMCAL_var(cmcal_vars, "toolbar_right"),
            } : false,
            datesRender: function (info) {
                cmcal_month_days_with_events = {};
                var view = info.view;
                var month_events_style = get_CMCAL_var(cmcal_vars, "month_events_style");
                var month_hidden_events = "cmcal-month-hidden-events";
                var vertical_middle_days = "vertical-middle-days";
                var navToOtherCalendar = "cmcal-navToOtherCalendar";
                calendar.removeClass(month_hidden_events).removeClass(vertical_middle_days).removeClass(navToOtherCalendar);
                var navLinks = get_CMCAL_var(cmcal_vars, "navLinks");
                if (navLinks == "navToOtherCalendar") {
                    calendar.addClass(navToOtherCalendar);
                }
                if (view.type == "dayGridMonth")
                {
                    if (month_events_style == "hidden") {
                        calendar.addClass(month_hidden_events);
                        var monthDay_verticalAlign = get_CMCAL_var(cmcal_vars, "monthDay_verticalAlign");
                        if (monthDay_verticalAlign == "middle")
                        {
                            calendar.addClass(vertical_middle_days);
                        }
                    }

                    ////Trailing zeros////////////////////////////////////////////////////////////////
                    if (get_CMCAL_var(cmcal_vars, "dayNumberLeadingZeros") == "true") {
                        calendar.find('.fc-day-number').each(function () {
                            var day = jQuery(this).html();
                            if (day.length == 1)
                                jQuery(this).html("0" + day);
                        });
                    }
                }
                calendar.find(cmcal_non_business_days_selector(calendar, cmcal_vars)).addClass("cmcal-nonbusinessDays");
                cmcal_month_days_navigation(calendar, cmcal_vars, info.view.type);

                var events_date_range_navigation_type = get_CMCAL_var(cmcal_vars, "events_date_range_navigation_type");
                if (events_date_range_navigation_type != "enabled_buttons") {
                    var events_date_range = get_CMCAL_var(cmcal_vars, "events_date_range");
                    var currentStart = moment(view.currentStart);
                    var currentEnd = moment(view.currentEnd);
                    currentStart = moment(currentStart.format('YYYY-MM-DD'));
                    currentEnd = moment(currentEnd.format('YYYY-MM-DD'));
                    if (events_date_range.min_date != null) {
                        var minDate = moment(events_date_range.min_date);
                        minDate = moment(minDate.format('YYYY-MM-DD'));
                        var button = calendar.find(".fc-prev-button");
                        cmcal_disable_prev_next(button, minDate, currentStart, currentEnd, events_date_range_navigation_type);
                        var button = calendar.find(".fc-prevYear-button");
                        cmcal_disable_prev_next(button, minDate.year(), currentStart.year(), currentEnd.year(), events_date_range_navigation_type);
                    }
                    // Version 1.26: Ability to change toolbar title text with custom js code
                    if (events_date_range.max_date != null) {
                        var maxDate = moment(events_date_range.max_date);
                        maxDate = moment(maxDate.format('YYYY-MM-DD'));
                        var button = calendar.find(".fc-next-button");
                        cmcal_disable_prev_next(button, maxDate, currentStart, currentEnd, events_date_range_navigation_type);
                        var button = calendar.find(".fc-nextYear-button");
                        cmcal_disable_prev_next(button, maxDate.year(), currentStart.year(), currentEnd.year(), events_date_range_navigation_type);
                    }
                }
                if (typeof cmcal_custom_title == 'function') {
                    var locale = get_CMCAL_var(cmcal_vars, "language_code");
                    var start = moment(info.view.currentStart)
                    var end = moment(info.view.currentEnd).subtract(1, 'days');
                    var new_title = cmcal_custom_title(start, end, locale, info.view.type, calendar_id);
                    if (new_title != null)
                        calendar.find('.fc-toolbar > div h2').empty().append(new_title);
                }
                if (jQuery.inArray(view.type, ["listYear", "listMonth", "listWeek", "listDay", "listDuration"]) != -1) {
                    var list_events_sort_order = get_CMCAL_var(cmcal_vars, "list_events_sort_order");
                    if (list_events_sort_order == 'desc') {
                        var renderedEvents = calendar.find('.fc-list-table tr'); // use js only on selected calendar
                        var reorderedEvents = [];
                        var blockEvents = null;
                        if (renderedEvents.length > 0) {
                            renderedEvents.map(function (key, event) {
                                if (jQuery(event).hasClass('fc-list-heading')) {
                                    if (blockEvents) {
                                        reorderedEvents.unshift(blockEvents.children());
                                    }
                                    blockEvents = jQuery('<tbody></tbody>');
                                }
                                blockEvents.append(event);
                            });
                            reorderedEvents.unshift(blockEvents.children());
                            calendar.find('.fc-list-table tbody').html(reorderedEvents); // use js only on selected calendar
                        }
                    }
                }
                jQuery(document).triggerHandler('cmcal_after_datesRender', {calendar: calendar, info: info});
            },
            eventRender: function (event, element) {
                //Filter Event visibility
                var event_visible = jQuery(document).triggerHandler('cmcal_event_visible', {calendar: calendar, event: event.event, view: event.view});
                if (typeof (event_visible) != 'undefined' && !event_visible) {
                    return false;
                }
                var view = event.view.type;
                var month_events_style = get_CMCAL_var(cmcal_vars, "month_events_style");
                var isListView = false;
                if (jQuery.inArray(view, ["listYear", "listMonth", "listWeek", "listDay", "listDuration"]) != -1)
                    isListView = true;

                /////Template////////////////////////////////////////////////////////////////
                var template = isListView ? get_CMCAL_var(cmcal_vars, "event_template_list") : get_CMCAL_var(cmcal_vars, "event_template");
                if (template == "")
                    template = '[event_title]';
                var fixed_template = "";
                if (template != "")
                    fixed_template = cmcal_fix_template(event.event, template, get_CMCAL_var_date_format(cmcal_vars, "eventDateFormat"), get_CMCAL_var_date_format(cmcal_vars, "eventTimeFormat"), cmcal_vars);

                if (isListView)
                {
                    if (fixed_template != "") {
                        if (event.event.url != '')
                            fixed_template = '<a href="' + event.event.url + '" style="color:' + event.event.textColor + ';">' + fixed_template + '</a>';

                        event.el.innerHTML = '<td class="fc-widget-content" style="background-color:' + event.event.backgroundColor + '; color:' + event.event.textColor + ';">' + fixed_template + '</td>';
                    }
                } else if (view == "dayGridMonth" && month_events_style == "dots") {
                    event.el.innerHTML = '';
                    event.el.classList.add("fc-event-dot");
                } else if (view == "dayGridMonth" && month_events_style == "hidden") {
                    event.el.innerHTML = '';
                    event.el.classList.add("fc-event-hidden");
                } else {
                    if (fixed_template != "") {
                        event.el.innerHTML = fixed_template;
                    }
                }

                /////Filter - Search////////////////////////////////////////////////////////////////
                if (get_CMCAL_var(cmcal_vars, "filter_visibility") == 'true')
                {
                    //Search
                    var filter_calendar_id = typeof (cmcal_vars.filter_parent_calendar_id) != "undefined" ? cmcal_vars.filter_parent_calendar_id : calendar_id;
                    var search_box = jQuery(".cmcal-calendar-filter-area input[name=" + 'cmcal-search-' + filter_calendar_id + "]");
                    if (search_box.length > 0) {
                        var filter_val = search_box.val();
                        if (!(event.event.title.toUpperCase().indexOf(filter_val.toUpperCase()) >= 0))
                            return false;
                    }

                    //Filter
                    for (i = 0; i < taxonomies_event_template_filter.length; i++) {
                        var tax_name = taxonomies_event_template_filter[i];
                        var tax = "filter_box_" + tax_name;
                        var filter_box = jQuery(".cmcal-calendar-filter-area select[name=" + 'cmcal-tax-filter-' + tax + "-" + filter_calendar_id + "]");
                        if (filter_box.length > 0) {
                            var filter_val = filter_box.val();
                            if (filter_val != null && filter_val != "") {
                                var event_tax_ids = event.event.extendedProps[tax_name + "-ids"]
                                if (typeof event_tax_ids == 'undefined')
                                    return false;
                                var filter_val_converted = isNaN(parseInt(filter_val)) ? filter_val : parseInt(filter_val);
                                if (filter_val && !(event_tax_ids.includes(filter_val_converted)))
                                    return false;
                            }
                        }
                    }
                }

                /////Tooltip////////////////////////////////////////////////////////////////
                if (get_CMCAL_var(cmcal_vars, "tooltipEnabled") == "1") {
                    var tooltip_template = get_CMCAL_var(cmcal_vars, "tooltip_template");
                    var tooltip_fixed_template = cmcal_fix_template(event.event, tooltip_template, get_CMCAL_var_date_format(cmcal_vars, "eventDateFormat"), get_CMCAL_var_date_format(cmcal_vars, "eventTimeFormat"), cmcal_vars);
                    var tooltipSettings = {
                        tooltip_modal: get_CMCAL_var(cmcal_vars, "tooltip_modal"),
                        tooltip_at: get_CMCAL_var(cmcal_vars, "tooltip_at"),
                        tooltip_my: get_CMCAL_var(cmcal_vars, "tooltip_my"),
                        tooltip_fixed_template: tooltip_fixed_template,
                        calendar_id: calendar_id,
                        tooltip_rounded: get_CMCAL_var(cmcal_vars, "tooltip_rounded"),
                        tooltip_shadow: get_CMCAL_var(cmcal_vars, "tooltip_shadow"),
                        tooltip_inactive: get_CMCAL_var(cmcal_vars, "tooltip_inactive"),
                        tooltip_solo: get_CMCAL_var(cmcal_vars, "tooltip_solo"),
                        tooltip_show_event: get_CMCAL_var(cmcal_vars, "tooltip_show_event"),
                        tooltip_show_delay: get_CMCAL_var(cmcal_vars, "tooltip_show_delay"),
                        tooltip_hide_event: get_CMCAL_var(cmcal_vars, "tooltip_hide_event"),
                        tooltip_hide_delay: get_CMCAL_var(cmcal_vars, "tooltip_hide_delay"),
                        tooltip_target: get_CMCAL_var(cmcal_vars, "tooltip_target"),
                        tooltip_mouse: get_CMCAL_var(cmcal_vars, "tooltip_mouse"),
                        tooltip_show_animation: get_CMCAL_var(cmcal_vars, "tooltip_show_animation"),
                        tooltip_hide_animation: get_CMCAL_var(cmcal_vars, "tooltip_hide_animation"),
                        tooltip_show_animation_time: get_CMCAL_var(cmcal_vars, "tooltip_show_animation_time"),
                        tooltip_hide_animation_time: get_CMCAL_var(cmcal_vars, "tooltip_hide_animation_time"),
                    };
                    var cmcal_tooltip_styles = get_cmcal_tooltip_styles(tooltipSettings);


                    jQuery(event.el).qtip(cmcal_tooltip_styles);
                }

                // Add cmcal-pastday-event class
                var today_stripped = cmcal_get_date_without_TimezoneOffset(new Date());
                var eventEnd_stripped;
                var eventStart_stripped = cmcal_get_date_without_TimezoneOffset(event.event.start);
                if (!event.event.end) {
                    if (eventStart_stripped < today_stripped) {
                        jQuery(event.el).addClass("cmcal-pastday-event");
                    }
                } else {
                    eventEnd_stripped = cmcal_get_date_without_TimezoneOffset(event.event.end)
                    if (eventEnd_stripped < today_stripped) {
                        jQuery(event.el).addClass("cmcal-pastday-event");
                    }
                }

                /////Add has-events class form months////////////////////////////////////////////////////////////////
                if (view == "dayGridMonth")
                {
                    var event_days = cmcal_enumerateDaysBetweenDates(event.event.start, event.event.end, event.view.activeStart, event.view.activeEnd);
                    var event_days_length = event_days.length;
                    var event_day;
                    var fc_day_bg;
                    for (i = 0; i < event_days_length; i++) {
                        event_day = event_days[i];
                        //Check if day has already been marked
                        if (!cmcal_month_days_with_events.hasOwnProperty(event_day)) {
                            fc_day_bg = calendar.find('.fc-bg .fc-day[data-date=' + event_day + '],  .fc-day-top[data-date=' + event_day + ']');
                            fc_day_bg.addClass("has-events");
                            cmcal_month_days_with_events[event_day] = event_day;
                        }
                    }
                }
                if (view == "dayGridMonth" && month_events_style == "hidden") {
                    return false;
                }
            },
            viewSkeletonRender: function (info) {
                cmcal_month_days_navigation(calendar, cmcal_vars, info.view.type);
//                cmcal_month_cell_hover(calendar, cmcal_vars, info.view.type);
            },
            eventClick: function (info) {
                if (info.event.url && info.event.extendedProps.url_new_window == "true") {
                    window.open(info.event.url);
                    info.jsEvent.preventDefault();
                }
            },
            windowResize: function (view) {
                cmcal_responsive_styles(calendar, view.type, get_CMCAL_var(cmcal_vars, "responsiveWidth"), responsiveViews);
            },
            loading: function (bool) {
                if (get_CMCAL_var(cmcal_vars, "loading_gif") == "true") {
                    if (bool) {
                        calendar.parent(".cmcal-calendar-container").prepend('<div class="cmcal-loading"></div>');
                        calendar.parent(".cmcal-calendar-container").addClass('cmcal-container-disabled-on-loading');
                    } else
                    {
                        calendar.parent(".cmcal-calendar-container").find('.cmcal-loading').remove();
                        calendar.parent(".cmcal-calendar-container").removeClass('cmcal-container-disabled-on-loading');
                    }
                }
            },
            dateClick: function (info) {
                var navToOtherCalendar_id = cmcal_get_navToOtherCalendar(cmcal_vars);
                if (navToOtherCalendar_id != null) {
                    var cmca = cmca_calendars[navToOtherCalendar_id];
                    cmca.gotoDate(info.date);
                    info.jsEvent.preventDefault();
                }
            }
        };
        var all_events = get_CMCAL_var(cmcal_vars, "all_events");
        if (all_events) {
            calendar_settings.events = all_events;

        } else {
            calendar_settings.eventSources = [{
                    url: cmcal_vars.ajaxurl,
                    extraParams: {action: cmcal_vars.data_action, calendar_id: calendar_id},
                }];
        }
        // var cmca_id = document.getElementById(calendar.attr('id'));
        // Filter calendar settings
        var calendar_settings_new = jQuery(document).triggerHandler('cmcal_settings_before_init', {calendar: calendar, calendar_settings: calendar_settings});
        if (typeof (calendar_settings_new) != 'undefined') {
            calendar_settings = calendar_settings_new;
        }
        var cmca = new FullCalendar.Calendar(jQuery(this)[0], calendar_settings);
        cmca.render();
        cmca_calendars[calendar_id] = cmca;
        var cmcal_last_selected_view_name = calendar.data("cmcal_last_selected_view_name");
        if (typeof cmcal_last_selected_view_name == "undefined")
            cmcal_last_selected_view_name = get_CMCAL_var(cmcal_vars, "defaultView");
        cmcal_responsive_styles(jQuery(this), cmcal_last_selected_view_name, get_CMCAL_var(cmcal_vars, "responsiveWidth"), responsiveViews);

    });
}


function cmcal_disable_prev_next(button, checkDate, currentStart, currentEnd, events_date_range_navigation_type) {
    var cssclass = events_date_range_navigation_type == "hidden_buttons" ? 'cmcal-fc-button-hidden' : 'fc-state-disabled';
    if (checkDate >= currentStart && checkDate <= currentEnd) {
        button.prop('disabled', true);
        button.addClass(cssclass);
    } else {
        button.removeClass(cssclass);
        button.prop('disabled', false);
    }
}


function cmcal_filterCalendar(calendar) {
    if (calendar.length > 0) {
        var cmca = cmca_calendars[calendar.data("cmcal-id")];
        var fc_day_bg = calendar.find('.fc-bg .fc-day, .fc-day-top');
        fc_day_bg.removeClass("has-events");
        cmca.rerenderEvents();
        cmca.updateSize(); // version 1.29
    }
}

function cmcal_get_navToOtherCalendar(cmcal_vars) {
    var navToOtherCalendar_id = null;
    var navLinks = get_CMCAL_var(cmcal_vars, "navLinks");
    if (navLinks == "navToOtherCalendar") {
        navToOtherCalendar_id = get_CMCAL_var(cmcal_vars, "navToOtherCalendar_id");
    }
    return navToOtherCalendar_id;
}

function cmcal_month_days_navigation(calendar, cmcal_vars, view) {
    if (view == "dayGridMonth") {
        if (get_CMCAL_var(cmcal_vars, "navigation_days_without_events") == "false") {
            var days_withoyt_events = calendar.find('.fc-day-top').not(".has-events").find(".fc-day-number");
            days_withoyt_events.removeAttr("data-goto");
        }
    }
}

function cmcal_columnHeaderFormat(date, calendar_id, columnHeaderFormat) {
    var cmcal_vars = window["CMCAL_vars_" + calendar_id];
    var format = get_CMCAL_var_date_format(cmcal_vars, columnHeaderFormat);
    var striped_date = cmcal_get_date_without_TimezoneOffset(date);
    return moment(striped_date).locale(cmcal_vars['language_code']).format(format);
}

function cmcal_listFormat(date, cmcal_vars, columnHeaderFormat) {
    var format = get_CMCAL_var_date_format(cmcal_vars, columnHeaderFormat);
    if (columnHeaderFormat == 'slotLabelFormat') {
        return moment(date.date.marker).utc().locale(cmcal_vars['language_code']).format(format);
    }
    var striped_date = cmcal_get_date_without_TimezoneOffset(date.date.marker);
    return moment(striped_date).locale(cmcal_vars['language_code']).format(format);
}

function cmcal_language_date(cmcal_vars, date, format) {
    var striped_date = cmcal_get_date_without_TimezoneOffset(date);
    return moment(striped_date).locale(cmcal_vars['language_code']).format(format);
}

function cmcal_get_date_without_TimezoneOffset(date) {
    var striped_date = new Date(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate());
    return striped_date;
}

function cmcal_non_business_days_selector(calendar, cmcal_vars) {
    var businessDays_selector_array = [];
    var businessDays = get_CMCAL_var(cmcal_vars, "businessDays") ? JSON.parse(get_CMCAL_var(cmcal_vars, "businessDays")) : [0, 1, 2, 3, 4, 5, 6];
    if (!(businessDays.indexOf(0) > -1)) {
        businessDays_selector_array.push(".fc-sun");
    }
    if (!(businessDays.indexOf(1) > -1)) {
        businessDays_selector_array.push(".fc-mon");
    }
    if (!(businessDays.indexOf(2) > -1)) {
        businessDays_selector_array.push(".fc-tue");
    }
    if (!(businessDays.indexOf(3) > -1)) {
        businessDays_selector_array.push(".fc-wed");
    }
    if (!(businessDays.indexOf(4) > -1)) {
        businessDays_selector_array.push(".fc-thu");
    }
    if (!(businessDays.indexOf(5) > -1)) {
        businessDays_selector_array.push(".fc-fri");
    }
    if (!(businessDays.indexOf(6) > -1)) {
        businessDays_selector_array.push(".fc-sat");
    }
    return businessDays_selector_array.toString();
}

function cmcal_minutes_to_time(minutes) {
    if (!isNaN(minutes) && (parseInt(minutes, 10) > 0)) {
        var hours = Math.floor(minutes / 60);
        var minutes = minutes % 60;
        if (minutes < 10) {
            return hours + ":0" + minutes;
        }
        return hours + ":" + minutes;
    }
    return "01:00";
}

function cmcal_convert_zero_to_one(value) {
    if (value == 0) {
        return 1;
    }
    return value;
}

function cmcal_convert_null_or_zero_to_1(value) {
    if (value == 0) {
        return 1;
    }
    return parseInt(value);
}
