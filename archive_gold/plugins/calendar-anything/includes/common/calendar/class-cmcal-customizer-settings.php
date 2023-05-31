<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Cmcal_Customizer_Settings {

    private $calendar_settings = [];
    private $class_calendar_setting = "cmcal-calendar-setting";

    public function get_calendar_settings() {
        if (!empty($this->calendar_settings))
            return $this->calendar_settings;

        $arr = [];
        foreach ($this->Themes() as $sub_arr) {
            $arr[] = $sub_arr;
        }
        foreach ($this->General_Settings() as $sub_arr) {
            $arr[] = $sub_arr;
        }
        foreach ($this->Filter_Settings() as $sub_arr) {
            $arr[] = $sub_arr;
        }
        foreach ($this->Toolbar_Settings() as $sub_arr) {
            $arr[] = $sub_arr;
        }
        foreach ($this->Month_View_Settings() as $sub_arr) {
            $arr[] = $sub_arr;
        }
        foreach ($this->Agenda_View_Settings() as $sub_arr) {
            $arr[] = $sub_arr;
        }
        foreach ($this->List_View_Settings() as $sub_arr) {
            $arr[] = $sub_arr;
        }
        foreach ($this->Event_Settings() as $sub_arr) {
            $arr[] = $sub_arr;
        }
        foreach ($this->Events_Limit_Settings() as $sub_arr) {
            $arr[] = $sub_arr;
        }
        foreach ($this->Responsive_Settings() as $sub_arr) {
            $arr[] = $sub_arr;
        }
        foreach ($this->Tooltip_Settings() as $sub_arr) {
            $arr[] = $sub_arr;
        }
        foreach ($this->Custom_CSS_Settings() as $sub_arr) {
            $arr[] = $sub_arr;
        }
        foreach ($this->Import_Export_Settings() as $sub_arr) {
            $arr[] = $sub_arr;
        }

        $this->calendar_settings = $arr;
        return $arr;
    }

    private function Themes() {
        $arr = array(
            array("name" => esc_html__("Themes", 'calendar-anything'),
                "type" => "sub-section-3",
                "class" => "cmcal-section-header",
            ),
        );
        $arr[] = array("name" => "", "type" => "subsection_opening_tag");
        $arr[] = array("name" => "",
            "db_field" => CMCAL()->shortname . "_customizer_themes",
            "id" => "selectedTheme",
            "type" => "themes",
            "class" => $this->class_calendar_setting,
        );
        $arr[] = array("type" => "subsection_closing_tag");

        return $arr;
    }

    private function General_Settings() {
        $arr = array(
            array("name" => esc_html__("General Settings", 'calendar-anything'),
                "type" => "sub-section-3",
                "class" => "cmcal-section-header",
            ),
            //////////////////////////////////////////////////////////
            //////////////////Settings////////////////////////////////
            //////////////////////////////////////////////////////////
            array("name" => esc_html__("Settings", 'calendar-anything'), "type" => "subsection_opening_tag"),
            array("name" => esc_html__("Default View", 'calendar-anything'),
                "info" => "Choose the default View of the calendar.",
                "db_field" => CMCAL()->shortname . "_customizer_general_settings",
                "id" => "defaultView",
                "type" => "select",
                "options" => CMCAL()->lov->get_defaultView_options(),
                "std" => "dayGridMonth",
                "class" => $this->class_calendar_setting,
            ),
            array("name" => esc_html__("Default Date", 'calendar-anything'),
                "db_field" => CMCAL()->shortname . "_customizer_general_settings",
                "id" => "defaultDateMode",
                "type" => "radio",
                "options" => array("0" => "Movable Date", "1" => "Certain Date"),
                "std" => "0",
                "class" => $this->class_calendar_setting,
            ),
            array("name" => esc_html__("Movable Date", 'calendar-anything'),
                "db_field" => CMCAL()->shortname . "_customizer_general_settings",
                "id" => "defaultDate_MovableDate",
                "type" => "select",
                "options" => CMCAL()->lov->get_defaultDate_MovableDate_options(),
                "std" => "0",
                "class" => $this->class_calendar_setting,
                'required' => array(
                    array('editor_id' => "defaultDateMode", '=', '0'),
                )
            ),
            array("name" => esc_html__("Certain Date", 'calendar-anything'),
                "db_field" => CMCAL()->shortname . "_customizer_general_settings",
                "id" => "defaultDate_CertainDate",
                "type" => "date-picker",
                "class" => $this->class_calendar_setting,
                'required' => array(
                    array('editor_id' => "defaultDateMode", '=', '1'),
                )
            ),
            array("name" => esc_html__("First Week Day", 'calendar-anything'),
                "db_field" => CMCAL()->shortname . "_customizer_general_settings",
                "id" => "firstDay",
                "type" => "select",
                "options" => CMCAL()->lov->get_calendar_firstDay_numbers(),
                "std" => "0",
                "class" => $this->class_calendar_setting,
            ),
            array("name" => esc_html__("Visible Days", 'calendar-anything'),
                "db_field" => CMCAL()->shortname . "_customizer_general_settings",
                "id" => "hiddenDays",
                "type" => "hidden_days",
                "class" => $this->class_calendar_setting,
            ),
            array("name" => esc_html__("Business Days", 'calendar-anything'),
                "db_field" => CMCAL()->shortname . "_customizer_general_settings",
                "id" => "businessDays",
                "type" => "business_days",
                "class" => $this->class_calendar_setting,
            ),
            array("name" => esc_html__("Aspect Ratio (Width/Height)", 'calendar-anything'),
                "db_field" => CMCAL()->shortname . "_customizer_general_settings",
                "id" => "aspectRatio",
                "std" => "1.35",
                "type" => "number",
                "class" => $this->class_calendar_setting,
            ),
            array("name" => esc_html__("Enable Navigation Links", 'calendar-anything'),
                "db_field" => CMCAL()->shortname . "_customizer_general_settings",
                "id" => "navLinks",
                "type" => "radio",
                "options" => array(
                    "true" => esc_html__('Enabled', 'calendar-anything'),
                    "false" => esc_html__('Disabled', 'calendar-anything'),
                    "navToOtherCalendar" => esc_html__('Navigate To Other Calendar', 'calendar-anything')
                ),
                "std" => "true",
                "class" => $this->class_calendar_setting,
            ),
            array("name" => esc_html__("Other Calendar ID", 'calendar-anything'),
                "db_field" => CMCAL()->shortname . "_customizer_general_settings",
                "id" => "navToOtherCalendar_id",
                "type" => "text",
                "class" => $this->class_calendar_setting,
                'required' => array(
                    array('editor_id' => "navLinks", '=', 'navToOtherCalendar'),
                )
            ),
            array("name" => esc_html__("Right-to-left Mode", 'calendar-anything'),
                "db_field" => CMCAL()->shortname . "_customizer_general_settings",
                "id" => "isRTL",
                "type" => "radio",
                "options" => array(
                    "false" => esc_html__('Left-to-right', 'calendar-anything'),
                    "true" => esc_html__('Right-to-left', 'calendar-anything')
                ),
                "std" => "false",
                "class" => $this->class_calendar_setting,
            ),
            array("name" => esc_html__("Show Loading GIF", 'calendar-anything'),
                "db_field" => CMCAL()->shortname . "_customizer_general_settings",
                "id" => "loading_gif",
                "type" => "radio",
                "options" => array(
                    "true" => esc_html__('Show', 'calendar-anything'),
                    "false" => esc_html__('Hide', 'calendar-anything')
                ),
                "std" => "false",
                "class" => $this->class_calendar_setting,
            ),
        );

        $arr[] = array("type" => "subsection_closing_tag");

        //////////////////////////////////////////////////////////
        //////////////////Fonts///////////////////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Fonts", 'calendar-anything'), "type" => "subsection_opening_tag");
        $arr[] = $this->Fonts_helper("calendar_all", esc_html__("Calendar Fonts", 'calendar-anything'), " 
                                body .fc,
                                .fc ,   
                                .fc button,
                                .fc a,
                                .fc h2
                                "
        );
        $arr[] = $this->Fonts_helper("column_header", esc_html__("Day Header Fonts", 'calendar-anything'), "     
                                .fc-head,
                                .fc-head th,
                                .fc-head a,
                                .fc-list-heading td,
                                .fc-list-heading a
                                "
        );
        $arr[] = $this->Colors_helper(CMCAL()->shortname . "_customizer_colors", "day_header_link_hover_color", esc_html__("Day Header Link Hover Font Color", 'calendar-anything'), "color", "
                                .fc-head .fc-day-header a:hover,
                                .fc-list-heading a:hover"
        );

        $arr[] = array("type" => "subsection_closing_tag");


        //////////////////////////////////////////////////////////
        //////////////////Background colors///////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Background Colors", 'calendar-anything'), "type" => "subsection_opening_tag");
        $arr[] = $this->Colors_helper(CMCAL()->shortname . "_customizer_colors", "general_background", esc_html__("Calendar Background Color", 'calendar-anything'), "background-color", "
                                .fc,
                                .fc-list-empty-wrap1"
        );
        $arr[] = $this->Colors_helper(CMCAL()->shortname . "_customizer_colors", "header_background", esc_html__("Header Background", 'calendar-anything'), "background-color", "
                                .fc-head,
                                .fc-unthemed .fc-divider,
                                .fc-unthemed .fc-popover .fc-header,
                                .fc-unthemed .fc-list-heading td,
                                thead.fc-head th.fc-day-header"
        );

        $arr[] = $this->Colors_helper(CMCAL()->shortname . "_customizer_colors", "body_background", esc_html__("Body Background", 'calendar-anything'), "background-color", "
                                    .fc-list-table .fc-list-item, 
                                    .fc-unthemed .fc-list-empty,
                                    .fc-list-view .fc-scroller,
                                    .fc-list-view .fc-list-empty-wrap1,
                                    .fc-body"
        );
        $arr[] = $this->Colors_helper(CMCAL()->shortname . "_customizer_colors", "today_background", esc_html__("Today Background Color (No list views)", 'calendar-anything'), "background-color", "
                                .fc .fc-bg .fc-day.fc-today,
                                .fc .fc-bg .fc-day.fc-other-month.fc-today,
                                .fc .fc-bg .fc-day.cmcal-nonbusinessDays.fc-today", true
        );
        $arr[] = $this->Colors_helper(CMCAL()->shortname . "_customizer_colors", "non_business_day_background", esc_html__("Non Business Day Background Color", 'calendar-anything'), "background-color", "
                                .fc .fc-bg .fc-day.cmcal-nonbusinessDays,
                                .fc .fc-bg .fc-day.fc-other-month.cmcal-nonbusinessDays", true
        );
        $arr[] = array("type" => "subsection_closing_tag");


        //////////////////////////////////////////////////////////
        //////////////////Borders/////////////////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Borders", 'calendar-anything'), "type" => "subsection_opening_tag");
        $arr[] = $this->Borders_helper("table_header", esc_html__("Table Header Borders", 'calendar-anything'), false, "
                                .fc .fc-head  td, 
                                .fc-unthemed .fc-list-heading td,
                                .fc .fc-head-container th
                                ", false, true);
        $arr[] = $this->Borders_helper("table_body", esc_html__("Table Body Cell Borders", 'calendar-anything'), false, "
                               .fc .fc-body td,
                               .fc-unthemed .fc-list-item td
                                ", false, true);

        $arr[] = array("type" => "subsection_closing_tag");

        //////////////////////////////////////////////////////////
        //////////////////Margins/Paddings////////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Margins/Paddings", 'calendar-anything'), "type" => "subsection_opening_tag");
        $arr[] = $this->Margins_Paddings_helper("table_column_header", "padding", esc_html__("Table Date Header Padding", 'calendar-anything'), "
                                .fc .fc-row th.fc-widget-header,
                                .fc-list-heading .fc-widget-header");
        $arr[] = $this->Margins_Paddings_helper("body_cell", "padding", esc_html__("Body Cell Padding", 'calendar-anything'), "
                                .fc .fc-row td,
                                .fc .fc-list-item td,
                                .fc td.fc-axis");
        $arr[] = array("type" => "subsection_closing_tag");

        return $arr;
    }

    private function Filter_Settings() {
        $arr = array(
            array("name" => esc_html__("Filter Settings", 'calendar-anything'),
                "type" => "sub-section-3",
                "class" => "cmcal-section-header",
        ));

        //////////////////////////////////////////////////////////
        //////////////////Enable/Disable Filter//////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("type" => "subsection_opening_tag");
        $arr[] = array("name" => esc_html__("Enable/Disable Filter", 'calendar-anything'),
            "db_field" => CMCAL()->shortname . "_customizer_filter_settings",
            "id" => "filter_visibility",
            "type" => "radio",
            "options" => array(
                "true" => esc_html__('Enable', 'calendar-anything'),
                "false" => esc_html__('Disable', 'calendar-anything')
            ),
            "std" => "false",
            "class" => $this->class_calendar_setting,
            'is_required_for_sections' => array("section_class" => CMCAL()->shortname . "-filter-subsection", "required_value" => "true")
        );

        $arr[] = array("type" => "subsection_closing_tag");
        //////////////////////////////////////////////////////////
        //////////////////Settings//////////////////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Settings", 'calendar-anything'), "type" => "subsection_opening_tag", "subsection_header_content_class" => CMCAL()->shortname . "-filter-subsection");
        $arr[] = array("name" => esc_html__("Use Select2 for Taxonomies", 'calendar-anything'),
            "db_field" => CMCAL()->shortname . "_customizer_filter_settings",
            "id" => "select2_for_tax",
            "type" => "radio",
            "options" => array(
                "true" => esc_html__('Enable', 'calendar-anything'),
                "false" => esc_html__('Disable', 'calendar-anything')
            ),
            "std" => "false",
            "class" => $this->class_calendar_setting,
        );
        $arr[] = array("type" => "subsection_closing_tag");

        //////////////////////////////////////////////////////////
        //////////////////Labels//////////////////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Labels", 'calendar-anything'), "type" => "subsection_opening_tag", "subsection_header_content_class" => CMCAL()->shortname . "-filter-subsection");

        $Text = $this->Text_Multilingual_helper(esc_html__("Search Box Placeholder", 'calendar-anything'), "_customizer_filter_settings", "search_box_label", "");
        foreach ($Text as $value)
            $arr[] = $value;

        $Text = $this->Text_Multilingual_helper(esc_html__("Date Navigator Placeholder", 'calendar-anything'), "_customizer_filter_settings", "date_navigator_label", "");
        foreach ($Text as $value)
            $arr[] = $value;

        $options = CMCAL()->setup_options;
        $post_types_options = $options['post_types_options'];

        $taxonomies = CMCAL()->lov->get_all_taxonomies_event_template_filter($post_types_options);
        if ($taxonomies) {
            foreach ($taxonomies as $tax) {
                $Text = $this->Text_Multilingual_helper(esc_html__("Filter Dropdown Text for Taxonomy", 'calendar-anything') . ' ' . $tax, "_customizer_filter_settings", "filter_box_" . $tax . "_all_events_text", "");
                foreach ($Text as $value)
                    $arr[] = $value;
            }
        }

        if (!empty(CMCAL()->calendar_custom_filters)) {
            foreach (CMCAL()->calendar_custom_filters as $custom_filter) {
                $Text = $this->Text_Multilingual_helper(esc_html__("Filter Dropdown Text for Filter", 'calendar-anything') . ' ' . $custom_filter["id"], "_customizer_filter_settings", "filter_box_" . $custom_filter["id"] . "_all_events_text", "");
                foreach ($Text as $value)
                    $arr[] = $value;
            }
        }

        $arr[] = array("type" => "subsection_closing_tag");

        //////////////////////////////////////////////////////////
        //////////////////Background colors///////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Background Colors", 'calendar-anything'), "type" => "subsection_opening_tag", "subsection_header_content_class" => CMCAL()->shortname . "-filter-subsection");
        $arr[] = $this->Colors_helper(CMCAL()->shortname . "_customizer_colors", "filter_background", esc_html__("Filter Background Color", 'calendar-anything'), "background-color", "
                                .cmcal-calendar-filter-area select, .cmcal-calendar-filter-area input"
        );
        $arr[] = array("type" => "subsection_closing_tag");

        //////////////////////////////////////////////////////////
        //////////////////Fonts///////////////////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Fonts", 'calendar-anything'), "type" => "subsection_opening_tag", "subsection_header_content_class" => CMCAL()->shortname . "-filter-subsection");
        $arr[] = $this->Fonts_helper("filter_fonts", esc_html__("Filter Fonts", 'calendar-anything'), "
                                 .cmcal-calendar-filter-area select, .cmcal-calendar-filter-area input, .cmcal-calendar-filter-area input::placeholder
                                "
        );
        $arr[] = array("type" => "subsection_closing_tag");

        //////////////////////////////////////////////////////////
        //////////////////Borders/////////////////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Borders", 'calendar-anything'), "type" => "subsection_opening_tag", "subsection_header_content_class" => CMCAL()->shortname . "-filter-subsection");
        $arr[] = $this->Borders_helper("filter_border", esc_html__("Filter Borders", 'calendar-anything'), true, "
                                .cmcal-calendar-filter-area select, .cmcal-calendar-filter-area input
                                ");
        $arr[] = array("type" => "subsection_closing_tag");

        //////////////////////////////////////////////////////////
        //////////////////Margins/Paddings////////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Margins/Paddings", 'calendar-anything'), "type" => "subsection_opening_tag", "subsection_header_content_class" => CMCAL()->shortname . "-filter-subsection");
        $arr[] = $this->Margins_Paddings_helper("filter", "margin", esc_html__("Filter Margin", 'calendar-anything'), "
                                .cmcal-calendar-filter-area select, .cmcal-calendar-filter-area input
                                ");

        $arr[] = $this->Margins_Paddings_helper("filter", "padding", esc_html__("Filter Padding", 'calendar-anything'), "
                                .cmcal-calendar-filter-area select, .cmcal-calendar-filter-area input
                                ");
        $arr[] = array("type" => "subsection_closing_tag");

        //////////////////////////////////////////////////////////
        //////////////////Filter Template////////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Template", 'calendar-anything'), "type" => "subsection_opening_tag", "subsection_header_content_class" => CMCAL()->shortname . "-filter-subsection");
        $arr[] = array("name" => esc_html__("Template", 'calendar-anything'),
            "db_field" => CMCAL()->shortname . "_customizer_filter_settings",
            "id" => "filter_template",
            "type" => "textarea",
            "std" => "",
            "class" => $this->class_calendar_setting,
            "isHtml" => "true",
        );
        $arr[] = array("name" => esc_html__("Available Shortcodes", 'calendar-anything'),
            "type" => "available_shortcodes",
            "options" => CMCAL()->lov->get_filter_template_shortcuts(),
        );
        $arr[] = array("type" => "subsection_closing_tag");
        return $arr;
    }

    private function Toolbar_Settings() {
        $arr = [];
        $arr[] = array("name" => esc_html__("Toolbar Settings", 'calendar-anything'),
            "type" => "sub-section-3",
            "class" => "cmcal-section-header",
        );

        //////////////////////////////////////////////////////////
        //////////////////Enable/Disable Toolbar//////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("type" => "subsection_opening_tag");
        $arr[] = array("name" => esc_html__("Enable/Disable Toolbar", 'calendar-anything'),
            "db_field" => CMCAL()->shortname . "_customizer_toolbar_settings",
            "id" => "toolbar_visibility",
            "type" => "radio",
            "options" => array(
                "true" => esc_html__('Enable', 'calendar-anything'),
                "false" => esc_html__('Disable', 'calendar-anything')
            ),
            "std" => "false",
            "class" => $this->class_calendar_setting,
            'is_required_for_sections' => array("section_class" => CMCAL()->shortname . "-toolbar-subsection", "required_value" => "true")
        );

        $arr[] = array("type" => "subsection_closing_tag");

        //////////////////////////////////////////////////////////
        //////////////////Buttons Displayed///////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Buttons Displayed", 'calendar-anything'), "type" => "subsection_opening_tag", "subsection_header_content_class" => CMCAL()->shortname . "-toolbar-subsection");

        $arr[] = array("name" => esc_html__("Toolbar Containers", 'calendar-anything'),
            "db_field" => CMCAL()->shortname . "_customizer_toolbar_settings",
            "id" => "toolbar_settings",
            "type" => "toolbar_settings",
        );

        $arr[] = array("type" => "subsection_closing_tag");

        //////////////////////////////////////////////////////////
        //////////////////Buttons Text////////////////////////////
        //////////////////////////////////////////////////////////

        $arr[] = array("name" => esc_html__("Buttons Text", 'calendar-anything'), "type" => "subsection_opening_tag", "subsection_header_content_class" => CMCAL()->shortname . "-toolbar-subsection");

        $buttonText = $this->Text_Multilingual_helper(esc_html__("Button Today Text", 'calendar-anything'), "_customizer_toolbar_settings", "buttonText_today", "today");
        foreach ($buttonText as $value)
            $arr[] = $value;

        $buttonText = $this->Text_Multilingual_helper(esc_html__("Button Month Text", 'calendar-anything'), "_customizer_toolbar_settings", "buttonText_month", "month");
        foreach ($buttonText as $value)
            $arr[] = $value;

        $buttonText = $this->Text_Multilingual_helper(esc_html__("Button Week Text", 'calendar-anything'), "_customizer_toolbar_settings", "buttonText_week", "week");
        foreach ($buttonText as $value)
            $arr[] = $value;

        $buttonText = $this->Text_Multilingual_helper(esc_html__("Button Agenda Week Text", 'calendar-anything'), "_customizer_toolbar_settings", "buttonText_agendaWeek", "agendaWeek");
        foreach ($buttonText as $value)
            $arr[] = $value;

        $buttonText = $this->Text_Multilingual_helper(esc_html__("Button Day Text", 'calendar-anything'), "_customizer_toolbar_settings", "buttonText_day", "day");
        foreach ($buttonText as $value)
            $arr[] = $value;

        $buttonText = $this->Text_Multilingual_helper(esc_html__("Button Agenda Day Text", 'calendar-anything'), "_customizer_toolbar_settings", "buttonText_agendaDay", "agendaDay");
        foreach ($buttonText as $value)
            $arr[] = $value;

        $buttonText = $this->Text_Multilingual_helper(esc_html__("Button List Year Text", 'calendar-anything'), "_customizer_toolbar_settings", "buttonText_listYear", "listYear");
        foreach ($buttonText as $value)
            $arr[] = $value;

        $buttonText = $this->Text_Multilingual_helper(esc_html__("Button List Month Text", 'calendar-anything'), "_customizer_toolbar_settings", "buttonText_listMonth", "listMonth");
        foreach ($buttonText as $value)
            $arr[] = $value;

        $buttonText = $this->Text_Multilingual_helper(esc_html__("Button List Week Text", 'calendar-anything'), "_customizer_toolbar_settings", "buttonText_listWeek", "listWeek");
        foreach ($buttonText as $value)
            $arr[] = $value;

        $buttonText = $this->Text_Multilingual_helper(esc_html__("Button List Day Text", 'calendar-anything'), "_customizer_toolbar_settings", "buttonText_listDay", "listDay");
        foreach ($buttonText as $value)
            $arr[] = $value;
        $buttonText = $this->Text_Multilingual_helper(esc_html__("Button List Duration Text", 'calendar-anything'), "_customizer_toolbar_settings", "buttonText_listDuration", "listDuration");
        foreach ($buttonText as $value)
            $arr[] = $value;
        $arr[] = array("type" => "subsection_closing_tag");

        //////////////////////////////////////////////////////////
        //////////////////Selected Button Mode////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Selected Button Mode", 'calendar-anything'), "type" => "subsection_opening_tag", "subsection_header_content_class" => CMCAL()->shortname . "-toolbar-subsection");

        $arr[] = array("name" => esc_html__("Toolbar Selected View Button Display", 'calendar-anything'),
            "db_field" => CMCAL()->shortname . "_customizer_toolbar_settings",
            "id" => "selected_view_button_display",
            "type" => "select",
            "options" => array(array("id" => "", "value" => esc_html__("Selected", 'calendar-anything')), array("id" => "none", "value" => esc_html__("Hidden", 'calendar-anything'))),
            "std" => "Selected",
            "output" => array(
                "selector" => "
                                .fc-button.fc-button-active",
                "property" => "display",
            ),
        );
        $arr[] = array("type" => "subsection_closing_tag");


        //////////////////////////////////////////////////////////
        //////////////////Background colors///////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Background Colors", 'calendar-anything'), "type" => "subsection_opening_tag", "subsection_header_content_class" => CMCAL()->shortname . "-toolbar-subsection");
        $arr[] = $this->Colors_helper(CMCAL()->shortname . "_customizer_colors", "toolbar_background", esc_html__("Toolbar", 'calendar-anything'), "background-color", "
                                .fc-toolbar"
        );
        $arr[] = $this->Colors_helper(CMCAL()->shortname . "_customizer_colors", "toolbar_buttons_background", esc_html__("Toolbar Buttons Background Color", 'calendar-anything'), "background-color", "
                                .fc-toolbar .fc-button"
        );
        $arr[] = $this->Colors_helper(CMCAL()->shortname . "_customizer_colors", "toolbar_buttons_active_background", esc_html__("Toolbar Buttons Active/Hover Background Color", 'calendar-anything'), "background-color", "
                                .fc-toolbar .fc-button:hover,
                                .fc-toolbar .fc-button.fc-button-active"
        );
        $arr[] = $this->Colors_helper(CMCAL()->shortname . "_customizer_colors", "toolbar_buttons_disabled_background", esc_html__("Toolbar Buttons Disabled Background Color", 'calendar-anything'), "background-color", "
                                .fc-toolbar .fc-button.fc-state-disabled"
        );
        $arr[] = array("type" => "subsection_closing_tag");

        //////////////////////////////////////////////////////////
        //////////////////Borders/////////////////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Borders", 'calendar-anything'), "type" => "subsection_opening_tag", "subsection_header_content_class" => CMCAL()->shortname . "-toolbar-subsection");
        $arr[] = $this->Borders_helper("toolbar", esc_html__("Toolbar Borders", 'calendar-anything'), true, "
                                .fc-toolbar
                                ");
        $arr[] = $this->Borders_helper("toolbar_buttons", esc_html__("Toolbar Buttons Borders", 'calendar-anything'), true, "
                                .fc-button,
                                .fc-state-default.fc-corner-right,
                                .fc-state-default.fc-corner-left
                                ");
        $arr[] = array("type" => "subsection_closing_tag");



        //////////////////////////////////////////////////////////
        //////////////////Fonts///////////////////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Fonts", 'calendar-anything'), "type" => "subsection_opening_tag", "subsection_header_content_class" => CMCAL()->shortname . "-toolbar-subsection");
        $arr[] = $this->Fonts_helper("toolbar_buttons", esc_html__("Toolbar Buttons Fonts", 'calendar-anything'), "
                                 .fc-toolbar .fc-button
                                "
        );
        $arr[] = $this->Colors_helper(CMCAL()->shortname . "_customizer_colors", "toolbar_buttons_active_font_color", esc_html__("Toolbar Buttons Active/Hover Font Color", 'calendar-anything'), "color", "
                                .fc-toolbar .fc-button:hover,
                                .fc-toolbar .fc-button.fc-button-active"
        );
        $arr[] = $this->Fonts_helper("toolbar_title", esc_html__("Toolbar Title Fonts", 'calendar-anything'), "
                                .fc-toolbar h2
                                "
        );
        $arr[] = array("type" => "subsection_closing_tag");

        //////////////////////////////////////////////////////////
        //////////////////Margins/Paddings////////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Margins/Paddings", 'calendar-anything'), "type" => "subsection_opening_tag", "subsection_header_content_class" => CMCAL()->shortname . "-toolbar-subsection");
        $arr[] = $this->Margins_Paddings_helper("toolbar", "margin", esc_html__("Toolbar Margin", 'calendar-anything'), "
                                .fc-toolbar
                                ");

        $arr[] = $this->Margins_Paddings_helper("toolbar", "padding", esc_html__("Toolbar Padding", 'calendar-anything'), "
                                .fc-toolbar
                                ");
        $arr[] = $this->Margins_Paddings_helper("toolbar_buttons", "margin", esc_html__("Toolbar Buttons Margin", 'calendar-anything'), "
                                .fc button,
                                .fc-button.fc-button-active,
                                .fc .fc-button-group>*,
                                .fc-center h2 
                                ");
        $arr[] = $this->Margins_Paddings_helper("toolbar_buttons", "padding", esc_html__("Toolbar Buttons Padding", 'calendar-anything'), "
                                .fc button,
                                .fc-button.fc-button-active,
                                .fc-center h2
                                ");
        $arr[] = array("type" => "subsection_closing_tag");
        return $arr;
    }

    private function Month_View_Settings() {
        $arr = array(
            array("name" => esc_html__("Month View", 'calendar-anything'),
                "type" => "sub-section-3",
                "class" => "cmcal-section-header",
            ),
            //////////////////////////////////////////////////////////
            //////////////////Settings////////////////////////////////
            //////////////////////////////////////////////////////////
            array("name" => esc_html__("Settings", 'calendar-anything'), "type" => "subsection_opening_tag"),
            array("name" => esc_html__("Events Style", 'calendar-anything'),
                "db_field" => CMCAL()->shortname . "_customizer_minimal_month",
                "id" => "month_events_style",
                "type" => "radio",
                "options" => array(
                    "template" => esc_html__('Template', 'calendar-anything'),
                    "hidden" => esc_html__('Hidden', 'calendar-anything')
                ),
                "std" => "template",
                "class" => $this->class_calendar_setting,
            ),
            array("name" => esc_html__("Fixed Week Count Mode", 'calendar-anything'),
                "db_field" => CMCAL()->shortname . "_customizer_general_settings",
                "id" => "fixedWeekCount",
                "type" => "radio",
                "options" => array(
                    "false" => esc_html__('Either 4, 5, or 6 Weeks, Depending on the Month.', 'calendar-anything'),
                    "true" => esc_html__('Always 6 Weeks Tall', 'calendar-anything')
                ),
                "std" => "false",
                "class" => $this->class_calendar_setting,
            ),
            array("name" => esc_html__("Show Other Month Days", 'calendar-anything'),
                "db_field" => CMCAL()->shortname . "_customizer_general_settings",
                "id" => "showNonCurrentDates",
                "type" => "radio",
                "options" => array(
                    "true" => esc_html__('Enabled', 'calendar-anything'),
                    "false" => esc_html__('Disabled', 'calendar-anything'),
                ),
                "std" => "true",
                "class" => $this->class_calendar_setting,
            ),
            array("name" => esc_html__("Navigation for Days Without Events", 'calendar-anything'),
                "db_field" => CMCAL()->shortname . "_customizer_general_settings",
                "id" => "navigation_days_without_events",
                "type" => "radio",
                "options" => array(
                    "true" => esc_html__('Enabled', 'calendar-anything'),
                    "false" => esc_html__('Disabled', 'calendar-anything'),
                ),
                "std" => "true",
                "class" => $this->class_calendar_setting,
            ),
            array("name" => esc_html__("Day Number Leading Zeros", 'calendar-anything'),
                "db_field" => CMCAL()->shortname . "_customizer_general_settings",
                "id" => "dayNumberLeadingZeros",
                "type" => "radio",
                "options" => array(
                    "true" => esc_html__('Enabled', 'calendar-anything'),
                    "false" => esc_html__('Disabled', 'calendar-anything'),
                ),
                "std" => "false",
                "class" => $this->class_calendar_setting,
            ),
            array("name" => esc_html__("Day Number Circle Size (px)", 'calendar-anything'),
                "db_field" => CMCAL()->shortname . "_customizer_minimal_month",
                "id" => array(
                    "height" => "day_number_circle_size"
                ),
                "type" => "number",
                "class" => "refresh-calendar-after-styles-callback",
                "output" => array(
                    "selector" => "
                    .fc-day-number,
                    .fc-other-month .fc-day-number
                    ",
                    "property" => array(
                        "line-height" => array("type" => "function", "name" => "get_number_with_px", "parameters" => array(array("type" => "id", "name" => "height"))),
                        "width" => array("type" => "function", "name" => "get_number_with_px", "parameters" => array(array("type" => "id", "name" => "height"))),
                        "display" => array("type" => "function", "name" => "get_day_number_display", "parameters" => array(array("type" => "id", "name" => "height"))),
                    ),
                ),
            ),
            array("name" => esc_html__("Dot Size (px)", 'calendar-anything'),
                "db_field" => CMCAL()->shortname . "_customizer_minimal_month",
                "id" => array(
                    "height" => "event_dot_size"
                ),
                "type" => "number",
                "class" => "refresh-calendar-after-styles-callback",
                "output" => array(
                    "selector" => ".fc .fc-event-dot",
                    "property" => array(
                        "height" => array("type" => "function", "name" => "get_number_with_px", "parameters" => array(array("type" => "id", "name" => "height"))),
                        "width" => array("type" => "function", "name" => "get_number_with_px", "parameters" => array(array("type" => "id", "name" => "height"))),
                    ),
                ),
                'required' => array(
                    array('editor_id' => "month_events_style", '=', 'dots'),
                )
            ),
            array("name" => esc_html__("Day Number Vertical Align", 'calendar-anything'),
                "db_field" => CMCAL()->shortname . "_customizer_minimal_month",
                "id" => "monthDay_verticalAlign",
                "type" => "select",
                "options" => CMCAL()->lov->get_vertivcalAlign(),
                "std" => "",
                "class" => $this->class_calendar_setting,
                'required' => array(
                    array('editor_id' => "month_events_style", '=', 'hidden'),
                )
            ),
        );
        $arr[] = array("type" => "subsection_closing_tag");

        //////////////////////////////////////////////////////////
        //////////////////Background colors///////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Background Colors", 'calendar-anything'), "type" => "subsection_opening_tag");
        $arr[] = $this->Colors_helper(CMCAL()->shortname . "_customizer_colors", "day_number_circle", esc_html__("Day Number Circle Color", 'calendar-anything'), "background-color", "
                                .fc-day-number"
        );
        $arr[] = $this->Colors_helper(CMCAL()->shortname . "_customizer_colors", "today_background_number", esc_html__("Today Number Circle Color", 'calendar-anything'), "background-color", "
                                .fc-today .fc-day-number", true
        );
        $arr[] = $this->Colors_helper(CMCAL()->shortname . "_customizer_colors", "chess_odd_background", esc_html__("Chess Style Odd Cells", 'calendar-anything'), "background-color", "
                                .fc-dayGridMonth-view .fc-week:nth-child(odd) .fc-bg table td:nth-child(odd) ,
                                .fc-dayGridMonth-view .fc-week:nth-child(even) .fc-bg table td:nth-child(even) "
        );
        $arr[] = $this->Colors_helper(CMCAL()->shortname . "_customizer_colors", "chess_even_background", esc_html__("Chess Style Even Cells", 'calendar-anything'), "background-color", "
                                .fc-dayGridMonth-view .fc-week:nth-child(even) .fc-bg table td:nth-child(odd) ,
                                .fc-dayGridMonth-view .fc-week:nth-child(odd) .fc-bg table td:nth-child(even)"
        );
        $arr[] = $this->Colors_helper(CMCAL()->shortname . "_customizer_colors", "other_month_cell_background", esc_html__("Other Month Cell Background", 'calendar-anything'), "background-color", "
                                .fc .fc-bg .fc-day.fc-other-month", true
        );
        $arr[] = array("type" => "subsection_closing_tag");

        //////////////////////////////////////////////////////////
        //////////////////Fonts///////////////////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Fonts", 'calendar-anything'), "type" => "subsection_opening_tag");
        $arr[] = $this->Fonts_helper("month_day_number", esc_html__("Day Number Fonts", 'calendar-anything'), "
                                .fc .fc-row td.fc-day-top, 
                                .fc .fc-row td.fc-day-top a, 
                                .fc .fc-bg td.fc-day,
                                "
        );
        $arr[] = $this->Fonts_helper("month_today_number_font", esc_html__("Today Number Font", 'calendar-anything'), "
                                .fc-today .fc-day-number,
                                .fc .fc-row td.fc-today a, 
                                "
        );
        $arr[] = $this->Fonts_helper("month_day_number_otherMonth", esc_html__("Day Number Fonts (Other Month)", 'calendar-anything'), "
                                .fc-other-month .fc-day-number, 
                                .fc .fc-bg td.fc-day.fc-other-month,
                                .fc .fc-row td.fc-day-top.fc-other-month a
                                "
        );
        $arr[] = array("type" => "subsection_closing_tag");
        //////////////////////////////////////////////////////////
        //////////////////Styling of Days/Cells with events///////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Styling of Days/Cells with Events", 'calendar-anything'), "type" => "subsection_opening_tag");
        $arr[] = $this->Colors_helper(CMCAL()->shortname . "_customizer_minimal_month", "minimal_monthDay_has_events_background", esc_html__("Cell Background Color", 'calendar-anything'), "background-color", "
                                .fc .fc-bg .fc-day.has-events,
                                .fc .fc-bg .fc-day.fc-other-month.has-events,
                                .fc .fc-bg .fc-day.cmcal-nonbusinessDays.has-events,
                                .fc .fc-bg .fc-day.fc-today.has-events", true
        );
        $arr[] = $this->Colors_helper(CMCAL()->shortname . "_customizer_minimal_month", "minimal_monthDay_has_events_day_background", esc_html__("Day Circle Background Color", 'calendar-anything'), "background-color", "
                                .fc .fc-content-skeleton .fc-day-top.has-events .fc-day-number", true
        );
        $arr[] = $this->Fonts_helper("minimal_monthDay_has_events_month_day_number", esc_html__("Day Number Fonts", 'calendar-anything'), "
                                .fc .fc-content-skeleton .fc-day-top.has-events .fc-day-number"
        );
        $arr[] = array("type" => "subsection_closing_tag");

        //////////////////////////////////////////////////////////
        //////////////////Dates Format////////////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Dates Format", 'calendar-anything'), "type" => "subsection_opening_tag");

        $date_formats = $this->Date_Format_helper(esc_html__("Month View Header Format", 'calendar-anything'), "columnFormatMonth", "ddd");
        foreach ($date_formats as $value)
            $arr[] = $value;

        $arr[] = array("type" => "subsection_closing_tag");
        return $arr;
    }

    private function Agenda_View_Settings() {
        $arr = array(
            array("name" => esc_html__("Agenda View", 'calendar-anything'),
                "type" => "sub-section-3",
                "class" => "cmcal-section-header",
            ),
            //////////////////////////////////////////////////////////
            //////////////////Settings////////////////////////////////
            //////////////////////////////////////////////////////////
            array("name" => esc_html__("Settings", 'calendar-anything'), "type" => "subsection_opening_tag"),
            array("name" => esc_html__("Show/Hide All Day Slot", 'calendar-anything'),
                "db_field" => CMCAL()->shortname . "_customizer_general_settings",
                "id" => "allDaySlot",
                "type" => "radio",
                "options" => array(
                    "true" => esc_html__('Show', 'calendar-anything'),
                    "false" => esc_html__('Hide', 'calendar-anything'),
                ),
                "std" => "true",
                "class" => $this->class_calendar_setting,
            ),
            array("name" => esc_html__("Slot Event Overlap", 'calendar-anything'),
                "db_field" => CMCAL()->shortname . "_customizer_general_settings",
                "id" => "slotEventOverlap",
                "type" => "radio",
                "options" => array(
                    "true" => esc_html__('True', 'calendar-anything'),
                    "false" => esc_html__('False', 'calendar-anything'),
                ),
                "std" => "true",
                "class" => $this->class_calendar_setting,
            ),
            array("name" => esc_html__("Slot Duration (minutes)", 'calendar-anything'),
                "db_field" => CMCAL()->shortname . "_customizer_general_settings",
                "id" => "slotDuration",
                "type" => "number",
                "info" => "The frequency for displaying time slots.",
                "std" => "30",
                "class" => $this->class_calendar_setting,
            ),
            array("name" => esc_html__("Slot Label Interval (minutes)", 'calendar-anything'),
                "db_field" => CMCAL()->shortname . "_customizer_general_settings",
                "id" => "slotLabelInterval",
                "info" => "Determines how often the time-axis is labeled with text displaying the date/time of slots.",
                "type" => "number",
                "std" => "60",
                "class" => $this->class_calendar_setting,
            ),
            array("name" => esc_html__("Min Time", 'calendar-anything'),
                "db_field" => CMCAL()->shortname . "_customizer_general_settings",
                "id" => "minTime",
                "type" => "select",
                "options" => CMCAL()->lov->get_times(),
                "std" => "24:00",
                "class" => $this->class_calendar_setting,
            ),
            array("name" => esc_html__("Max Time", 'calendar-anything'),
                "db_field" => CMCAL()->shortname . "_customizer_general_settings",
                "id" => "maxTime",
                "type" => "select",
                "options" => CMCAL()->lov->get_times(),
                "std" => "00:00",
                "class" => $this->class_calendar_setting,
            ),
            array("name" => esc_html__("Agenda Slot Height (px)", 'calendar-anything'),
                "db_field" => CMCAL()->shortname . "_customizer_general_settings",
                "id" => array(
                    "height" => "agenda_slot_height"
                ),
                "type" => "number",
                "class" => "refresh-calendar-after-styles-callback",
                "output" => array(
                    "selector" => ".fc-time-grid .fc-slats td",
                    "property" => array(
                        "height" => array("type" => "function", "name" => "get_number_with_px", "parameters" => array(array("type" => "id", "name" => "height"))),
                    ),
                ),
            ),);

        $arr[] = array("type" => "subsection_closing_tag");

        //////////////////////////////////////////////////////////
        //////////////////Background colors///////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Background Colors", 'calendar-anything'), "type" => "subsection_opening_tag");
        $arr[] = $this->Colors_helper(CMCAL()->shortname . "_customizer_colors", "body_background_row_odd", esc_html__("Body Background (Row Odd)", 'calendar-anything'), "background-color", "
                                    .fc-slats table tr:nth-child(odd)"
        );
        $arr[] = $this->Colors_helper(CMCAL()->shortname . "_customizer_colors", "body_background_row_even", esc_html__("Body Background (Row Even)", 'calendar-anything'), "background-color", "
                                    .fc-slats table tr:nth-child(even)"
        );
        $arr[] = $this->Colors_helper(CMCAL()->shortname . "_customizer_colors", "agenda_time_axis_background", esc_html__("Time Axis Background", 'calendar-anything'), "background-color", "
                                    .fc-axis.fc-time"
        );
        $arr[] = array("type" => "subsection_closing_tag");

        //////////////////////////////////////////////////////////
        //////////////////Fonts///////////////////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Fonts", 'calendar-anything'), "type" => "subsection_opening_tag");
        $arr[] = $this->Fonts_helper("row_header", esc_html__("Slots Fonts", 'calendar-anything'), "
                                .fc .fc-axis"
        );
        $arr[] = $this->Colors_helper(CMCAL()->shortname . "_customizer_colors", "agenda_today_color", esc_html__("Today Header Font Color", 'calendar-anything'), "color", "
                                .fc-agenda-view .fc-head th.fc-today a"
        );

        $arr[] = array("type" => "subsection_closing_tag");

        //////////////////////////////////////////////////////////
        //////////////////Dates Format////////////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Dates Format", 'calendar-anything'), "type" => "subsection_opening_tag");

        $date_formats = $this->Date_Format_helper(esc_html__("Slot Label Format", 'calendar-anything'), "slotLabelFormat", "h:mm a");
        foreach ($date_formats as $value)
            $arr[] = $value;

        $date_formats = $this->Date_Format_helper(esc_html__("Week (Basic/Agenda) View Header Format", 'calendar-anything'), "columnFormatWeek", "ddd MM/YY");
        foreach ($date_formats as $value)
            $arr[] = $value;

        $date_formats = $this->Date_Format_helper(esc_html__("Day (Basic/Agenda) View Header Format", 'calendar-anything'), "columnFormatDay", "dddd");
        foreach ($date_formats as $value)
            $arr[] = $value;

        $arr[] = array("type" => "subsection_closing_tag");
        return $arr;
    }

    private function List_View_Settings() {
        $arr = array(
            array("name" => esc_html__("List View", 'calendar-anything'),
                "type" => "sub-section-3",
                "class" => "cmcal-section-header",
            ),
        );


        //////////////////////////////////////////////////////////
        //////////////////Settings////////////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Settings", 'calendar-anything'), "type" => "subsection_opening_tag");

        $arr[] = array("name" => esc_html__("List Duration (days)", 'calendar-anything'),
            "db_field" => CMCAL()->shortname . "_customizer_general_settings",
            "id" => "list_duration_days",
            "type" => "number",
//            "info" => "The frequency for displaying time slots.",
            "std" => "10",
            "class" => $this->class_calendar_setting,
        );

        $arr[] = array("name" => esc_html__("List Events Sort Order", 'calendar-anything'),
            "db_field" => CMCAL()->shortname . "_customizer_general_settings",
            "id" => "list_events_sort_order",
            "type" => "radio",
            "options" => array(
                "asc" => esc_html__('Ascending', 'calendar-anything'),
                "desc" => esc_html__('Descending', 'calendar-anything'),
            ),
            "std" => "asc",
            "class" => $this->class_calendar_setting,
        );

        $arr[] = array("name" => esc_html__("Show Day Headings", 'calendar-anything'),
            "db_field" => CMCAL()->shortname . "_customizer_general_settings",
            "id" => "show_list_header",
            "type" => "select",
            "options" => array(array("id" => "", "value" => esc_html__("Show", 'calendar-anything')), array("id" => "none", "value" => esc_html__("Hide", 'calendar-anything'))),
            "std" => "selected",
            "output" => array(
                "selector" => "
                                .fc-list-heading",
                "property" => "display",
            ),
        );
        $Text = $this->Text_Multilingual_helper(esc_html__("No Events Message", 'calendar-anything'), "_customizer_general_settings", "noEventsMessage", "");
        foreach ($Text as $value)
            $arr[] = $value;
        $arr[] = array("type" => "subsection_closing_tag");

        //////////////////////////////////////////////////////////
        //////////////////Dates Format////////////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Dates Format", 'calendar-anything'), "type" => "subsection_opening_tag");

        $date_formats = $this->Date_Format_helper(esc_html__("Year View (Left)", 'calendar-anything'), "listDayFormat_Year", "MMMM D, YYYY");
        foreach ($date_formats as $value)
            $arr[] = $value;

        $date_formats = $this->Date_Format_helper(esc_html__("Year View (Right)", 'calendar-anything'), "listDayAltFormat_Year", "dddd");
        foreach ($date_formats as $value)
            $arr[] = $value;


        $date_formats = $this->Date_Format_helper(esc_html__("Month View (Left)", 'calendar-anything'), "listDayFormat_Month", "MMMM D, YYYY");
        foreach ($date_formats as $value)
            $arr[] = $value;

        $date_formats = $this->Date_Format_helper(esc_html__("Month View (Right)", 'calendar-anything'), "listDayAltFormat_Month", "dddd");
        foreach ($date_formats as $value)
            $arr[] = $value;

        $date_formats = $this->Date_Format_helper(esc_html__("Week View (Left)", 'calendar-anything'), "listDayFormat_Week", "dddd");
        foreach ($date_formats as $value)
            $arr[] = $value;

        $date_formats = $this->Date_Format_helper(esc_html__("Week View (Right)", 'calendar-anything'), "listDayAltFormat_Week", "MMMM D, YYYY");
        foreach ($date_formats as $value)
            $arr[] = $value;

        $date_formats = $this->Date_Format_helper(esc_html__("Day View (Left)", 'calendar-anything'), "listDayFormat_Day", "dddd");
        foreach ($date_formats as $value)
            $arr[] = $value;

        $date_formats = $this->Date_Format_helper(esc_html__("Day View (Right)", 'calendar-anything'), "listDayAltFormat_Day", "MMMM D, YYYY");
        foreach ($date_formats as $value)
            $arr[] = $value;

        $date_formats = $this->Date_Format_helper(esc_html__("Duration View (Left)", 'calendar-anything'), "listDayFormat_Duration", "MMMM D, YYYY");
        foreach ($date_formats as $value)
            $arr[] = $value;

        $date_formats = $this->Date_Format_helper(esc_html__("Duration View (Right)", 'calendar-anything'), "listDayAltFormat_Duration", "dddd");
        foreach ($date_formats as $value)
            $arr[] = $value;

        $arr[] = array("type" => "subsection_closing_tag");


        //////////////////////////////////////////////////////////
        //////////////////Fonts///////////////////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Fonts", 'calendar-anything'), "type" => "subsection_opening_tag");
        $arr[] = $this->Fonts_helper("noEventsMessage", esc_html__("No Events Message Fonts", 'calendar-anything'), " 
                                .fc-unthemed .fc-list-empty
                                "
        );

        $arr[] = array("type" => "subsection_closing_tag");

        //////////////////////////////////////////////////////////
        //////////////////Margins/Paddings////////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Margins/Paddings", 'calendar-anything'), "type" => "subsection_opening_tag");
        $arr[] = $this->Margins_Paddings_helper("no_events_padding", "padding", esc_html__("No Events Message padding", 'calendar-anything'), "
                                .fc-list-empty");
        $arr[] = array("type" => "subsection_closing_tag");

        return $arr;
    }

    private function Event_Settings() {
        $arr = array(
            array("name" => esc_html__("Event Settings", 'calendar-anything'),
                "type" => "sub-section-3",
                "class" => "cmcal-section-header",
            ),
            array("name" => esc_html__("Settings", 'calendar-anything'), "type" => "subsection_opening_tag"),
            array("name" => esc_html__("Default event duration (minutes)", 'calendar-anything'),
                "db_field" => CMCAL()->shortname . "_customizer_general_settings",
                "id" => "eventDuration",
                "type" => "number",
                "std" => "60",
                "info" => "A fallback duration for timed events without a specified end value.",
                "class" => $this->class_calendar_setting,
            ),
        );
        $arr[] = array("type" => "subsection_closing_tag");

        //////////////////////////////////////////////////////////
        //////////////////Dates Format////////////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Dates Format", 'calendar-anything'), "type" => "subsection_opening_tag");

        $date_formats = $this->Date_Format_helper(esc_html__("Event Date Format", 'calendar-anything'), "eventDateFormat", "MMMM D, YYYY");
        foreach ($date_formats as $value)
            $arr[] = $value;

        $date_formats = $this->Date_Format_helper(esc_html__("Event Time Format", 'calendar-anything'), "eventTimeFormat", "h:mm a");
        foreach ($date_formats as $value)
            $arr[] = $value;

        $arr[] = array("type" => "subsection_closing_tag");

        //////////////////////////////////////////////////////////
        //////////////////Background colors///////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Background Colors", 'calendar-anything'), "type" => "subsection_opening_tag");
        $arr[] = $this->Colors_helper(CMCAL()->shortname . "_customizer_colors", "event_background", esc_html__("Event Background", 'calendar-anything'), "background-color", "
                                .fc-event,                                
                                .fc-list-table .fc-list-item .fc-widget-content"
        );
        $arr[] = $this->Colors_helper(CMCAL()->shortname . "_customizer_colors", "event_background_hover", esc_html__("Event Background Hover", 'calendar-anything'), "background-color", "
                                .fc-event:hover,                                
                                .fc-list-item:hover .fc-widget-content", true
        );
        $arr[] = $this->Colors_helper(CMCAL()->shortname . "_customizer_colors", "event_dot", esc_html__("Event Dot Color (List-view)", 'calendar-anything'), "background-color", "
                                .fc-event-dot"
        );
        $arr[] = array("type" => "subsection_closing_tag");

        //////////////////////////////////////////////////////////
        //////////////////Fonts///////////////////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Fonts", 'calendar-anything'), "type" => "subsection_opening_tag");
        $arr[] = $this->Fonts_helper("event", esc_html__("Event Fonts", 'calendar-anything'), "
                                .fc-event-container,
                                a.fc-event,
                                .fc-unthemed td.fc-event-container .fc-event,
                                .fc-list-item td,
                                .fc-list-item td a
                                "
        );
        $arr[] = $this->Colors_helper(CMCAL()->shortname . "_customizer_colors", "event_hover_font_color", esc_html__("Event Hover Font Color", 'calendar-anything'), "color", "
                                a.fc-event:hover,
                                .fc-unthemed td.fc-event-container .fc-event:hover,
                                .fc-list-item td a:hover,
                                .fc-list-item:hover td,
                                .fc-list-item:hover td a"
        );
        $arr[] = array("type" => "subsection_closing_tag");

        //////////////////////////////////////////////////////////
        //////////////////Borders/////////////////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Borders", 'calendar-anything'), "type" => "subsection_opening_tag");
        $arr[] = $this->Borders_helper("event", esc_html__("Event Borders", 'calendar-anything'), true, "
                                .fc a.fc-event,
                                .fc-list-item td.fc-widget-content
                                ");
        $arr[] = array("type" => "subsection_closing_tag");

        //////////////////////////////////////////////////////////
        //////////////////Margins/Paddings////////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Margins/Paddings", 'calendar-anything'), "type" => "subsection_opening_tag");
        $arr[] = $this->Margins_Paddings_helper("event", "margin", esc_html__("Event Margin", 'calendar-anything'), "
                                .fc-event,
                                .fc-list-item .fc-widget-content
                                ");
        $arr[] = $this->Margins_Paddings_helper("event", "padding", esc_html__("Event Padding", 'calendar-anything'), "
                                .fc-event,
                                .fc-event.fc-h-event,
                                .fc .fc-list-item td.fc-widget-content
                                ");
        $arr[] = array("type" => "subsection_closing_tag");

        //////////////////////////////////////////////////////////
        //////////////////Event Template////////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Templates", 'calendar-anything'), "type" => "subsection_opening_tag");
        $arr[] = array("name" => esc_html__("Template", 'calendar-anything'),
            "db_field" => CMCAL()->shortname . "_customizer_event_template",
            "id" => "event_template",
            "type" => "textarea",
            "std" => "",
            "class" => $this->class_calendar_setting,
            "isHtml" => "true",
        );
        $arr[] = array("name" => esc_html__("Template for List Views", 'calendar-anything'),
            "db_field" => CMCAL()->shortname . "_customizer_event_template",
            "id" => "event_template_list",
            "type" => "textarea",
            "std" => "",
            "class" => $this->class_calendar_setting,
            "isHtml" => "true",
        );
        $arr[] = array("name" => esc_html__("Available Shortcodes", 'calendar-anything'),
            "type" => "available_shortcodes",
            "options" => CMCAL()->lov->get_event_template_shortcuts(),
        );
        $arr[] = array("type" => "subsection_closing_tag");
        return $arr;
    }

    private function Events_Limit_Settings() {
        $arr = array(
            array("name" => esc_html__("Events Limit (Month View)", 'calendar-anything'),
                "type" => "sub-section-3",
                "class" => "cmcal-section-header",
            ),
            //////////////////////////////////////////////////////////
            //////////////////Enable/Disable Tooltip////////////
            //////////////////////////////////////////////////////////
            array("type" => "subsection_opening_tag"),
            array("name" => esc_html__("Enable/Disable Events Limit", 'calendar-anything'),
                "db_field" => CMCAL()->shortname . "_customizer_event_limit_settings",
                "id" => "eventsLimitEnabled",
                "type" => "radio",
                "options" => array(
                    "0" => esc_html__('Disabled', 'calendar-anything'),
                    "1" => esc_html__('Enabled', 'calendar-anything'),
                ),
                "std" => "0",
                "class" => $this->class_calendar_setting,
                'is_required_for_sections' => array("section_class" => CMCAL()->shortname . "-event-limit-subsection", "required_value" => "1")
            ),
            array("type" => "subsection_closing_tag"),
            //////////////////////////////////////////////////////////
            //////////////////Settings////////////////////////////////
            //////////////////////////////////////////////////////////
            array("name" => esc_html__("Settings", 'calendar-anything'), "type" => "subsection_opening_tag", "subsection_header_content_class" => CMCAL()->shortname . "-event-limit-subsection"),
            array("name" => esc_html__("Events Limit number (including button)", 'calendar-anything'),
                "db_field" => CMCAL()->shortname . "_customizer_event_limit_settings",
                "id" => "eventLimit",
                "type" => "number",
                "class" => $this->class_calendar_setting,
            ),
        );


        $buttonText = $this->Text_Multilingual_helper(esc_html__("Events Limit Button Text", 'calendar-anything'), "_customizer_event_limit_settings", "eventLimitText", "more");
        foreach ($buttonText as $value)
            $arr[] = $value;

        $arr[] = array("type" => "subsection_closing_tag");

        //////////////////////////////////////////////////////////
        //////////////////Fonts///////////////////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Fonts", 'calendar-anything'), "type" => "subsection_opening_tag", "subsection_header_content_class" => CMCAL()->shortname . "-event-limit-subsection");
        $arr[] = $this->Fonts_helper("events_limit_button", esc_html__("Events Limit Button Fonts", 'calendar-anything'), " 
                                .fc .fc-more-cell,
                                .fc a.fc-more
                                "
        );
        $arr[] = $this->Colors_helper(CMCAL()->shortname . "_customizer_event_limit_settings", "events_limit_button_font_hover", esc_html__("Events Limit Button Fonts Hover", 'calendar-anything'), "color", "
                                .fc a.fc-more:hover"
        );
        $arr[] = $this->Fonts_helper("popover_header", esc_html__("Popover Header Fonts", 'calendar-anything'), " 
                                .fc-popover .fc-header ,
                                .fc-popover .fc-header .fc-title,
                                .fc-unthemed .fc-popover .fc-header .fc-close
                                "
        );

        $arr[] = array("type" => "subsection_closing_tag");


        //////////////////////////////////////////////////////////
        //////////////////Background colors///////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Background colors", 'calendar-anything'), "type" => "subsection_opening_tag", "subsection_header_content_class" => CMCAL()->shortname . "-event-limit-subsection");
        $arr[] = $this->Colors_helper(CMCAL()->shortname . "_customizer_event_limit_settings", "events_limit_button_background", esc_html__("Events Limit Button Background Color", 'calendar-anything'), "background-color", "
                                .fc a.fc-more"
        );
        $arr[] = $this->Colors_helper(CMCAL()->shortname . "_customizer_event_limit_settings", "events_limit_button_background_hover", esc_html__("Events Limit Button Background Color Hover", 'calendar-anything'), "background-color", "
                                .fc a.fc-more:hover"
        );
        $arr[] = $this->Colors_helper(CMCAL()->shortname . "_customizer_event_limit_settings", "popover_header_background", esc_html__("Popover Header Background Color", 'calendar-anything'), "background-color", "
                                .fc .fc-popover .fc-header"
        );
        $arr[] = $this->Colors_helper(CMCAL()->shortname . "_customizer_event_limit_settings", "popover_body_background", esc_html__("Popover Body Background Color", 'calendar-anything'), "background-color", "
                                .fc .fc-popover .fc-body"
        );
        $arr[] = array("type" => "subsection_closing_tag");


        //////////////////////////////////////////////////////////
        //////////////////Borders/////////////////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Borders", 'calendar-anything'), "type" => "subsection_opening_tag", "subsection_header_content_class" => CMCAL()->shortname . "-event-limit-subsection");
        $arr[] = $this->Borders_helper("events_limit_button", esc_html__("Events Limit Button Borders", 'calendar-anything'), true, "
                                .fc a.fc-more
                                ");

        $arr[] = $this->Borders_helper("popover_header", esc_html__("Popover Header Borders", 'calendar-anything'), true, "
                                .fc .fc-popover .fc-header
                                ");
        $arr[] = $this->Borders_helper("popover_body", esc_html__("Popover Body Borders", 'calendar-anything'), true, "
                                .fc .fc-popover .fc-body
                                ");
        $arr[] = array("type" => "subsection_closing_tag");
        //////////////////////////////////////////////////////////
        //////////////////Margins/Paddings////////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Margins/Paddings", 'calendar-anything'), "type" => "subsection_opening_tag", "subsection_header_content_class" => CMCAL()->shortname . "-event-limit-subsection");
        $arr[] = $this->Margins_Paddings_helper("events_limit_button_outer", "margin", esc_html__("Events Limit Button Margin", 'calendar-anything'), "
                                .fc .fc-more-cell div
                                ");
        $arr[] = $this->Margins_Paddings_helper("events_limit_button", "padding", esc_html__("Events Limit Button Padding", 'calendar-anything'), "
                                .fc a.fc-more
                                ");
        $arr[] = $this->Margins_Paddings_helper("popover_header", "padding", esc_html__("Popover Header Padding", 'calendar-anything'), "
                                .fc .fc-popover .fc-header
                                ");
        $arr[] = $this->Margins_Paddings_helper("popover_body", "padding", esc_html__("Popover Body Padding", 'calendar-anything'), "
                                .fc .fc-popover .fc-body
                                ");
        $arr[] = array("type" => "subsection_closing_tag");

        //////////////////////////////////////////////////////////
        //////////////////Dates Format////////////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Dates Format", 'calendar-anything'), "type" => "subsection_opening_tag", "subsection_header_content_class" => CMCAL()->shortname . "-event-limit-subsection");

        $date_formats = $this->Date_Format_helper(esc_html__("Popover Day Format", 'calendar-anything'), "dayPopoverFormat", "dddd, MMMM D");
        foreach ($date_formats as $value)
            $arr[] = $value;
        $arr[] = array("type" => "subsection_closing_tag");

        return $arr;
    }

    private function Responsive_Settings() {
        $arr = array(
            array("name" => esc_html__("Responsive Settings", 'calendar-anything'),
                "type" => "sub-section-3",
                "class" => "cmcal-section-header",
            ),
            //////////////////////////////////////////////////////////
            //////////////////Settings////////////////////////////////
            //////////////////////////////////////////////////////////
            array("name" => esc_html__("Settings", 'calendar-anything'), "type" => "subsection_opening_tag"),
            array("name" => esc_html__("Responsive Width (px)", 'calendar-anything'),
                "db_field" => CMCAL()->shortname . "_customizer_responsive_settings",
                "id" => "responsiveWidth",
                "type" => "number",
                "class" => $this->class_calendar_setting . ' refresh-calendar-styles',
                "output" => array(),
            ),
            array("name" => esc_html__("Responsive Toolbar Button Font Size / Line Height (px)", 'calendar-anything'),
                "db_field" => CMCAL()->shortname . "_customizer_responsive_settings",
                "id" => "responsiveBtnSize",
                "type" => "number",
                "output" => array(),
            ),
            array("name" => esc_html__("Responsive Toolbar Title Font Size / Line Height (px)", 'calendar-anything'),
                "db_field" => CMCAL()->shortname . "_customizer_responsive_settings",
                "id" => "responsiveTitleSize",
                "type" => "number",
                "output" => array(),
            ),
        );

        $arr[] = array("type" => "subsection_closing_tag");

        //////////////////////////////////////////////////////////
        //////////////////Responsive Views////////////////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Responsive Views", 'calendar-anything'), "type" => "subsection_opening_tag");
        foreach (CMCAL()->lov->get_defaultView_options() as $val) {
            $arr[] = array("name" => $val["value"] . " Responsive View",
                "db_field" => CMCAL()->shortname . "_customizer_responsive_settings",
                "id" => "" . $val["id"] . "ResponsiveView",
                "type" => "select",
                "options" => CMCAL()->lov->get_defaultView_options(),
                "std" => $val["id"],
                "class" => $this->class_calendar_setting . " responsiveView",
            );
        }
        $arr[] = array("type" => "subsection_closing_tag");
        return $arr;
    }

    private function Tooltip_Settings() {
        $arr = array(
            array("name" => esc_html__("Tooltip Settings", 'calendar-anything'),
                "type" => "sub-section-3",
                "class" => "cmcal-section-header",
            ),);
        //////////////////////////////////////////////////////////
        //////////////////Enable/Disable Tooltip////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("type" => "subsection_opening_tag");
        $arr[] = array("name" => esc_html__("Enable/Disable Tooltip", 'calendar-anything'),
            "db_field" => CMCAL()->shortname . "_customizer_tooltip",
            "id" => "tooltipEnabled",
            "type" => "radio",
            "options" => array(
                "0" => esc_html__('Disabled', 'calendar-anything'),
                "1" => esc_html__('Enabled', 'calendar-anything'),
            ),
            "std" => "0",
            "class" => $this->class_calendar_setting,
            'is_required_for_sections' => array("section_class" => CMCAL()->shortname . "-tooltip-subsection", "required_value" => "1")
        );
        $arr[] = array("type" => "subsection_closing_tag");
        //////////////////////////////////////////////////////////
        //////////////////Settings////////////////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Settings", 'calendar-anything'), "type" => "subsection_opening_tag", "subsection_header_content_class" => CMCAL()->shortname . "-tooltip-subsection");

        $arr[] = array("name" => esc_html__("Modal Mode", 'calendar-anything'),
            "db_field" => CMCAL()->shortname . "_customizer_tooltip",
            "id" => "tooltip_modal",
            "type" => "radio",
            "options" => array(
                "0" => esc_html__('Disabled', 'calendar-anything'),
                "1" => esc_html__('Enabled', 'calendar-anything'),
            ),
            "std" => "0",
            "class" => $this->class_calendar_setting,
            "output" => array(),
        );
        $arr[] = array("name" => esc_html__("Tooltip Corner Position", 'calendar-anything'),
            "db_field" => CMCAL()->shortname . "_customizer_tooltip",
            "id" => "tooltip_my",
            "type" => "select",
            "options" => CMCAL()->lov->get_tooltip_position(),
            "std" => "bottom center",
            "class" => $this->class_calendar_setting,
            'required' => array(
                array('editor_id' => "tooltip_modal", '=', '0'),
            )
        );
        $arr[] = array("name" => esc_html__("Tooltip Event Corner Position", 'calendar-anything'),
            "db_field" => CMCAL()->shortname . "_customizer_tooltip",
            "id" => "tooltip_at",
            "type" => "select",
            "options" => CMCAL()->lov->get_tooltip_position(),
            "std" => "top center",
            "class" => $this->class_calendar_setting,
            'required' => array(
                array('editor_id' => "tooltip_modal", '=', '0'),
            )
        );
        $arr[] = array("name" => esc_html__("Rounded Corners", 'calendar-anything'),
            "db_field" => CMCAL()->shortname . "_customizer_tooltip",
            "id" => "tooltip_rounded",
            "type" => "radio",
            "options" => array(
                "0" => esc_html__('Disabled', 'calendar-anything'),
                "1" => esc_html__('Enabled', 'calendar-anything'),
            ),
            "std" => "0",
            "class" => $this->class_calendar_setting,
        );
        $arr[] = array("name" => esc_html__("Drop Shadow", 'calendar-anything'),
            "db_field" => CMCAL()->shortname . "_customizer_tooltip",
            "id" => "tooltip_shadow",
            "type" => "radio",
            "options" => array(
                "0" => esc_html__('Disabled', 'calendar-anything'),
                "1" => esc_html__('Enabled', 'calendar-anything'),
            ),
            "std" => "0",
            "class" => $this->class_calendar_setting,
        );
        $arr[] = array("name" => esc_html__("Hide When Inactive Time (in ms)", 'calendar-anything'),
            "info" => "Time in milliseconds in which the tooltip should be hidden if it remains inactive e.g. isn't interacted with. If set to 0, tooltip will not hide when inactive.",
            "db_field" => CMCAL()->shortname . "_customizer_tooltip",
            "id" => "tooltip_inactive",
            "std" => "0",
            "type" => "number",
            "class" => $this->class_calendar_setting,
        );
        $arr[] = array("name" => esc_html__("Hide Other Tooltips When Showing", 'calendar-anything'),
            "db_field" => CMCAL()->shortname . "_customizer_tooltip",
            "id" => "tooltip_solo",
            "type" => "radio",
            "options" => array(
                "false" => esc_html__('Disabled', 'calendar-anything'),
                "true" => esc_html__('Enabled', 'calendar-anything'),
            ),
            "std" => "false",
            "class" => $this->class_calendar_setting,
        );
        $arr[] = array("name" => esc_html__("Show on", 'calendar-anything'),
            "db_field" => CMCAL()->shortname . "_customizer_tooltip",
            "id" => "tooltip_show_event",
            "type" => "radio",
            "options" => array(
                "hover" => esc_html__('Hover', 'calendar-anything'),
                "click" => esc_html__('Click', 'calendar-anything'),
            ),
            "std" => "hover",
            "class" => $this->class_calendar_setting,
        );
        $arr[] = array("name" => esc_html__("Show Delay (in ms)", 'calendar-anything'),
            "db_field" => CMCAL()->shortname . "_customizer_tooltip",
            "id" => "tooltip_show_delay",
            "std" => "90",
            "type" => "number",
            "class" => $this->class_calendar_setting,
        );
        $arr[] = array("name" => esc_html__("Hide on", 'calendar-anything'),
            "db_field" => CMCAL()->shortname . "_customizer_tooltip",
            "id" => "tooltip_hide_event",
            "type" => "radio",
            "options" => array(
                "hover" => esc_html__('Hover out', 'calendar-anything'),
                "click" => esc_html__('Click', 'calendar-anything'),
            ),
            "std" => "hover",
            "class" => $this->class_calendar_setting,
        );
        $arr[] = array("name" => esc_html__("Hide Delay (in ms)", 'calendar-anything'),
            "db_field" => CMCAL()->shortname . "_customizer_tooltip",
            "id" => "tooltip_hide_delay",
            "std" => "0",
            "type" => "number",
            "class" => $this->class_calendar_setting,
        );
        $arr[] = array("name" => esc_html__("Show Animation", 'calendar-anything'),
            "db_field" => CMCAL()->shortname . "_customizer_tooltip",
            "id" => "tooltip_show_animation",
            "type" => "select",
            "options" => CMCAL()->lov->get_tooltip_animation(),
            "std" => "fade",
            "class" => $this->class_calendar_setting,
        );
        $arr[] = array("name" => esc_html__("Show Animation Time (in ms)", 'calendar-anything'),
            "db_field" => CMCAL()->shortname . "_customizer_tooltip",
            "id" => "tooltip_show_animation_time",
            "std" => "90",
            "type" => "number",
            "class" => $this->class_calendar_setting,
        );
        $arr[] = array("name" => esc_html__("Hide Animation", 'calendar-anything'),
            "db_field" => CMCAL()->shortname . "_customizer_tooltip",
            "id" => "tooltip_hide_animation",
            "type" => "select",
            "options" => CMCAL()->lov->get_tooltip_animation(),
            "std" => "fade",
            "class" => $this->class_calendar_setting,
        );
        $arr[] = array("name" => esc_html__("Hide Animation Time (in ms)", 'calendar-anything'),
            "db_field" => CMCAL()->shortname . "_customizer_tooltip",
            "id" => "tooltip_hide_animation_time",
            "std" => "90",
            "type" => "number",
            "class" => $this->class_calendar_setting,
        );
        $arr[] = array("name" => esc_html__("Target", 'calendar-anything'),
            "db_field" => CMCAL()->shortname . "_customizer_tooltip",
            "id" => "tooltip_target",
            "type" => "radio",
            "options" => array(
                "event" => esc_html__('Event', 'calendar-anything'),
                "mouse" => esc_html__('Mouse', 'calendar-anything'),
            ),
            "std" => "event",
            "class" => $this->class_calendar_setting,
        );
        $arr[] = array("name" => esc_html__("Follow Mouse", 'calendar-anything'), // only show when target == mouse
            "db_field" => CMCAL()->shortname . "_customizer_tooltip",
            "id" => "tooltip_mouse",
            "type" => "radio",
            "options" => array(
                "false" => esc_html__('Disabled', 'calendar-anything'),
                "true" => esc_html__('Enabled', 'calendar-anything'),
            ),
            "std" => "false",
            "class" => $this->class_calendar_setting,
        );
        $arr[] = array("type" => "subsection_closing_tag");
        //////////////////////////////////////////////////////////
        //////////////////Background colors///////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Background Colors", 'calendar-anything'), "type" => "subsection_opening_tag", "subsection_header_content_class" => CMCAL()->shortname . "-tooltip-subsection");
        $arr[] = array("name" => esc_html__("Tooltip Background", 'calendar-anything'),
            "important" => false,
            "isMasterSelector" => true,
            "db_field" => CMCAL()->shortname . "_customizer_colors",
            "id" => array(
                "color" => "tooltip_background" . "_color",
                "transparent" => "tooltip_background" . "_transparent",
            ),
            "color_std" => "",
            "output" => array(
                "selector" => ".cmcal-tooltip,.cmcal-tooltip .qtip-tip",
                "property" => array(
                    "background-color" => array("type" => "function", "name" => "get_color", "parameters" => array(
                            array("type" => "id", "name" => "color"),
                            array("type" => "id", "name" => "transparent"),
                        )),
                    "border-color" => array("type" => "function", "name" => "get_color", "parameters" => array(
                            array("type" => "id", "name" => "color"),
                            array("type" => "id", "name" => "transparent"),
                        )),
                ),
            ),
            "class" => "refresh-calendar-after-styles-callback",
            "type" => "color-picker-with-transparent",
        );
        $arr[] = array("type" => "subsection_closing_tag");

        //////////////////////////////////////////////////////////
        //////////////////Fonts///////////////////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Fonts", 'calendar-anything'), "type" => "subsection_opening_tag", "subsection_header_content_class" => CMCAL()->shortname . "-tooltip-subsection");
        $arr[] = $this->Fonts_helper("tooltip", esc_html__("Tooltip Fonts", 'calendar-anything'), ".cmcal-tooltip .qtip-content", true
        );
        $arr[] = array("type" => "subsection_closing_tag");

        //////////////////////////////////////////////////////////
        //////////////////Borders/////////////////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Borders", 'calendar-anything'), "type" => "subsection_opening_tag", "subsection_header_content_class" => CMCAL()->shortname . "-tooltip-subsection");
        $arr[] = $this->Borders_helper("tooltip", esc_html__("Tooltip Borders", 'calendar-anything'), true, ".cmcal-tooltip,.cmcal-tooltip .qtip-tip", true, true);
        $arr[] = array("type" => "subsection_closing_tag");

        //////////////////////////////////////////////////////////
        //////////////////Margins/Paddings////////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Margins/Paddings", 'calendar-anything'), "type" => "subsection_opening_tag", "subsection_header_content_class" => CMCAL()->shortname . "-tooltip-subsection");
        $arr[] = $this->Margins_Paddings_helper("tooltip", "padding", esc_html__("Tooltip Padding", 'calendar-anything'), ".cmcal-tooltip", true);
        $arr[] = array("type" => "subsection_closing_tag");

        //////////////////////////////////////////////////////////
        //////////////////Tooltip Template////////////////////////
        //////////////////////////////////////////////////////////
        $arr[] = array("name" => esc_html__("Templates", 'calendar-anything'), "type" => "subsection_opening_tag", "subsection_header_content_class" => CMCAL()->shortname . "-tooltip-subsection");
        $arr[] = array("name" => esc_html__("Template", 'calendar-anything'),
            "db_field" => CMCAL()->shortname . "_customizer_tooltip",
            "id" => "tooltip_template",
            "type" => "textarea",
            "std" => "",
            "class" => $this->class_calendar_setting,
            "isHtml" => "true",
        );
        $arr[] = array("name" => esc_html__("Available Shortcodes", 'calendar-anything'),
            "type" => "available_shortcodes",
            "options" => CMCAL()->lov->get_event_template_shortcuts(),
        );
        $arr[] = array("type" => "subsection_closing_tag");
        return $arr;
    }

    private function Custom_CSS_Settings() {
        $arr = array(
            array("name" => esc_html__("Custom CSS", 'calendar-anything'),
                "type" => "sub-section-3",
                "class" => "cmcal-section-header",
            ),
        );
        $arr[] = array("type" => "subsection_opening_tag");
        $arr[] = array("name" => esc_html__("Enter your custom CSS here", 'calendar-anything'),
            "db_field" => CMCAL()->shortname . "_custom_css",
            "id" => "custom_css",
            "type" => "textarea",
            "std" => "",
//            "class" => $this->class_calendar_setting,
            "isHtml" => "true",
            "output" => array(),
        );
        $arr[] = array("name" => "Example", "type" => "custom_css_instructions");
        $arr[] = array("type" => "subsection_closing_tag");
        return $arr;
    }

    private function Import_Export_Settings() {
        $arr = array(
            array("name" => esc_html__("Import/Export", 'calendar-anything'),
                "type" => "sub-section-3",
                "class" => "cmcal-section-header",
            ),
        );
//        $arr[] = array("name" => "", "type" => "subsection_opening_tag");  
        $arr[] = array("name" => esc_html__("Export", 'calendar-anything'), "type" => "subsection_opening_tag", "subsection_header_content_class" => CMCAL()->shortname . "-export-subsection");

        $arr[] = array(
//            "name" => "",
//            "db_field" => CMCAL()->shortname . "_customizer_themes",
//            "id" => "selectedTheme",
            "type" => "export",
//            "class" => $this->class_calendar_setting,
        );
        $arr[] = array("type" => "subsection_closing_tag");
        $arr[] = array("name" => esc_html__("Import", 'calendar-anything'), "type" => "subsection_opening_tag", "subsection_header_content_class" => CMCAL()->shortname . "-import-subsection");

        $arr[] = array(
//            "name" => "",
//            "db_field" => CMCAL()->shortname . "_customizer_themes",
//            "id" => "selectedTheme",
            "type" => "import",
//            "class" => $this->class_calendar_setting,
        );
        $arr[] = array("type" => "subsection_closing_tag");

        return $arr;
    }

    //////Helpers/////////////////////////////////////////////

    private function Margins_Paddings_helper($keyword, $type, $name, $selector, $isMasterSelector = false, $MasterSelector = null) {
        return

                array("name" => $name,
                    "isMasterSelector" => $isMasterSelector,
                    "MasterSelector" => $MasterSelector,
                    "db_field" => CMCAL()->shortname . "_customizer_margins_paddings",
                    "id" => array(
                        "top" => $keyword . "_" . $type . "_top",
                        "right" => $keyword . "_" . $type . "_right",
                        "bottom" => $keyword . "_" . $type . "_bottom",
                        "left" => $keyword . "_" . $type . "_left",
                    ),
                    "output" => array(
                        "selector" => $selector,
                        "property" => array(
                            $type . "-top" => array("type" => "function", "name" => "get_number_with_px", "parameters" => array(array("type" => "id", "name" => "top"))),
                            $type . "-right" => array("type" => "function", "name" => "get_number_with_px", "parameters" => array(array("type" => "id", "name" => "right"))),
                            $type . "-bottom" => array("type" => "function", "name" => "get_number_with_px", "parameters" => array(array("type" => "id", "name" => "bottom"))),
                            $type . "-left" => array("type" => "function", "name" => "get_number_with_px", "parameters" => array(array("type" => "id", "name" => "left"))),
                        ),
                    ),
                    "type" => "margin_padding",
        );
    }

    private function Text_Multilingual_helper($name, $db_field, $id, $std, $section_class = '', $class = '') {
        $arr = [];

        if (function_exists('icl_get_languages')) {
            //get list of used languages from WPML
            $langs = icl_get_languages();
            //Set current language for language based variables in theme.

            foreach ($langs as $language) {
                $language_not_active_class = $language['active'] == 1 ? "" : "language_not_active";
                $language_code = $language['language_code'];
                $arr[] = array("name" => $name,
                    "db_field" => CMCAL()->shortname . $db_field,
                    "id" => $id . "_" . $language_code,
                    "type" => "text",
                    "std" => $std,
                    "section_class" => " text_multilingual" . $section_class . " " . $language_not_active_class,
                    "class" => $this->class_calendar_setting . " " . $class,
                );
            }
        } else {
            $arr[] = array("name" => $name,
                "db_field" => CMCAL()->shortname . $db_field,
                "id" => $id,
                "type" => "text",
                "std" => $std,
                "section_class" => " text_multilingual" . $section_class,
                "class" => $this->class_calendar_setting . " " . $class,
            );
        }

        return $arr;
    }

    private function Fonts_helper($keyword, $name, $selector, $isMasterSelector = false, $MasterSelector = null, $section_class = '') {
        return
                array("name" => $name,
                    "isMasterSelector" => $isMasterSelector,
                    "MasterSelector" => $MasterSelector,
                    "db_field" => CMCAL()->shortname . "_customizer_fonts",
                    "id" => array(
                        "familly" => $keyword . "_fonts_familly",
                        "variant" => $keyword . "_fonts_variant",
                        "size" => $keyword . "_fonts_size",
                        "line_height" => $keyword . "_fonts_line_height",
                        "text_align" => $keyword . "_fonts_text_align",
                        "text_transform" => $keyword . "_fonts_text_transform",
                        "color" => $keyword . "_fonts_color",
                    ),
                    "output" => array(
                        "selector" => $selector,
                        "property" => array(
                            "font-family" => array("type" => "id", "name" => "familly"),
                            "font-style" => array("type" => "function", "name" => "get_font_style", "parameters" => array(array("type" => "id", "name" => "variant"))),
                            "font-weight" => array("type" => "function", "name" => "get_font_weight", "parameters" => array(array("type" => "id", "name" => "variant"))),
                            "font-size" => array("type" => "id", "name" => "size"),
                            "line-height" => array("type" => "id", "name" => "line_height"),
                            "text-align" => array("type" => "id", "name" => "text_align"),
                            "text-transform" => array("type" => "id", "name" => "text_transform"),
                            "color" => array("type" => "id", "name" => "color"),
                        ),
                    ),
                    "type" => "fonts",
                    "section_class" => " " . $section_class,
        );
    }

    private function Borders_helper($keyword, $name, $includeRadius, $selector, $isMasterSelector = false, $important = false, $MasterSelector = null, $section_class = '') {
        $arr = array("name" => $name,
            "isMasterSelector" => $isMasterSelector,
            "MasterSelector" => $MasterSelector,
            "important" => $important,
            "includeRadius" => $includeRadius,
            "db_field" => CMCAL()->shortname . "_customizer_borders",
            "class" => "refresh-calendar-after-styles-callback",
            "id" => array(
                "top" => $keyword . "_borders_top",
                "right" => $keyword . "_borders_right",
                "bottom" => $keyword . "_borders_bottom",
                "left" => $keyword . "_borders_left",
                "style" => $keyword . "_borders_style",
                "color" => $keyword . "_borders_color",
                "transparent" => $keyword . "_borders_transparent",
            ),
            "output" => array(
                "selector" => $selector,
                "property" => array(
                    "border-top-width" => array("type" => "function", "name" => "get_number_with_px_zero_if_empty", "parameters" => array(array("type" => "id", "name" => "top"))),
                    "border-right-width" => array("type" => "function", "name" => "get_number_with_px_zero_if_empty", "parameters" => array(array("type" => "id", "name" => "right"))),
                    "border-bottom-width" => array("type" => "function", "name" => "get_number_with_px_zero_if_empty", "parameters" => array(array("type" => "id", "name" => "bottom"))),
                    "border-left-width" => array("type" => "function", "name" => "get_number_with_px_zero_if_empty", "parameters" => array(array("type" => "id", "name" => "left"))),
                    "border-style" => array("type" => "id", "name" => "style"),
                    "border-color" => array("type" => "function", "name" => "get_color", "parameters" => array(
                            array("type" => "id", "name" => "color"),
                            array("type" => "id", "name" => "transparent")),
                    ),
                ),
            ),
            "type" => "borders",
            "section_class" => " " . $section_class,
        );
        if ($includeRadius) {
            $arr["id"]["top-left-radius"] = $keyword . "_borders_radius_top_left";
            $arr["id"]["top-right-radius"] = $keyword . "_borders_radius_top_right";
            $arr["id"]["bottom-left-radius"] = $keyword . "_borders_radius_bottom_left";
            $arr["id"]["bottom-right-radius"] = $keyword . "_borders_radius_bottom_right";

            $arr["output"]["property"]["border-top-left-radius"] = array("type" => "function", "name" => "get_number_with_px", "parameters" => array(array("type" => "id", "name" => "top-left-radius")));
            $arr["output"]["property"]["border-top-right-radius"] = array("type" => "function", "name" => "get_number_with_px", "parameters" => array(array("type" => "id", "name" => "top-right-radius")));
            $arr["output"]["property"]["border-bottom-left-radius"] = array("type" => "function", "name" => "get_number_with_px", "parameters" => array(array("type" => "id", "name" => "bottom-left-radius")));
            $arr["output"]["property"]["border-bottom-right-radius"] = array("type" => "function", "name" => "get_number_with_px", "parameters" => array(array("type" => "id", "name" => "bottom-right-radius")));
        }
        return $arr;
    }

    private function Colors_helper($db_field, $keyword, $name, $property, $selector, $important = false, $isMasterSelector = false, $MasterSelector = null, $required = null, $color_std = '', $section_class = '') {
        $return = array("name" => $name,
            "important" => $important,
            "isMasterSelector" => $isMasterSelector,
            "MasterSelector" => $MasterSelector,
            "db_field" => $db_field,
            "id" => array(
                "color" => $keyword . "_color",
                "transparent" => $keyword . "_transparent",
            ),
            "color_std" => $color_std,
            "output" => array(
                "selector" => $selector,
                "property" => array(
                    $property => array("type" => "function", "name" => "get_color", "parameters" => array(
                            array("type" => "id", "name" => "color"),
                            array("type" => "id", "name" => "transparent"),
                        )),
                ),
            ),
            "type" => "color-picker-with-transparent",
            "section_class" => " " . $section_class,
        );
        if ($required != null) {
            $return['required'] = $required;
        }
        return $return;
    }

    private function Date_Format_helper($name, $id, $std, $options = array(), $allow_custom = true, $section_class = "", $class = "") {
        $arr = [];
        $options[$std] = $std;
        if ($allow_custom) {
            $options["custom"] = esc_html__("Custom", 'calendar-anything');
        }

        $arr[] = array("name" => $name,
            "db_field" => CMCAL()->shortname . "_customizer_date_formats",
            "id" => $id,
            "type" => "radio",
            "options" => $options,
            "std" => $std,
            "class" => $this->class_calendar_setting,
        );
        if ($allow_custom) {
            $arr[] = array("name" => "Custom " . $name,
                "db_field" => CMCAL()->shortname . "_customizer_date_formats",
                "id" => $id . "_Custom",
                "type" => "text",
                "std" => $std,
                "class" => $this->class_calendar_setting,
                'required' => array(
                    array('editor_id' => $id, '=', 'custom'),
                )
            );
        }
        return $arr;
    }

    private $calendar_custom_styles_editors = [];

    public function get_calendar_custom_styles_editors() {
        if (!empty($this->calendar_custom_styles_editors))
            return $this->calendar_custom_styles_editors;

        $arr = [];
        $settings = $this->get_calendar_settings();

        foreach ($settings as $setting) {
            if (isset($setting["output"]) && isset($setting['id'])) {
                $isHtml = isset($setting['isHtml']) ? $setting['isHtml'] : "false";
                if (!is_array($setting['id']))
                    $arr[] = array("db_field" => $setting['db_field'], "id" => $setting['id'], "isHtml" => $isHtml);
                else {
                    foreach ($setting['id'] as $ids) {
                        $arr[] = array("db_field" => $setting['db_field'], "id" => $ids, "isHtml" => $isHtml);
                    }
                }
            }
        }
        $this->calendar_custom_styles_editors = $arr;
        return $arr;
    }

    private $calendar_custom_settings_editors = [];

    public function get_calendar_custom_settings_editors() {
        $arr = [];
        $settings = $this->get_calendar_settings();
        foreach ($settings as $setting) {
            if (isset($setting["class"]) && isset($setting["id"]) && isset($setting["db_field"])) {
                if (strpos($setting["class"], $this->class_calendar_setting) !== false) {
                    $isHtml = isset($setting['isHtml']) ? $setting['isHtml'] : "false";
                    $arr[] = array("db_field" => $setting['db_field'], "id" => $setting['id'], "isHtml" => $isHtml);
                }
            }
        }
        $this->calendar_custom_settings_editors = $arr;
        return $arr;
    }

    private $calendar_custom_settings_db_fields = [];

    public function get_calendar_custom_settings_db_fields() {
        if (!empty($this->calendar_custom_settings_db_fields))
            return $this->calendar_custom_settings_db_fields;
        $arr = [];
        $settings = $this->get_calendar_settings();
        $outputs = array_column($settings, 'db_field');
        $arr = array_unique($outputs);

        $this->calendar_custom_settings_db_fields = $arr;
        return $arr;
    }

}
