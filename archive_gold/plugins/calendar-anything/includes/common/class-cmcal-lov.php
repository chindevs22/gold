<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Cmcal_Lov {

    public function get_toolbar_settings_options() {
        return array(
            array("id" => "title", "value" => "title"),
            array("id" => "prev", "value" => "prev"),
            array("id" => "next", "value" => "next"),
            array("id" => "prevYear", "value" => "prevYear"),
            array("id" => "nextYear", "value" => "nextYear"),
            array("id" => "today", "value" => "today"),
            array("id" => "dayGridMonth", "value" => "month"),
            array("id" => "dayGridWeek", "value" => "basicWeek"),
            array("id" => "dayGridDay", "value" => "basicDay"),
            array("id" => "timeGridWeek", "value" => "agendaWeek"),
            array("id" => "timeGridDay", "value" => "agendaDay"),
            array("id" => "listYear", "value" => "listYear"),
            array("id" => "listMonth", "value" => "listMonth"),
            array("id" => "listWeek", "value" => "listWeek"),
            array("id" => "listDay", "value" => "listDay"),
            array("id" => "listDuration", "value" => "listDuration"),
        );
    }

    public function get_defaultView_options() {
        return array(
            array("id" => "dayGridMonth", "value" => esc_html__("Month", 'calendar-anything')),
            array("id" => "dayGridWeek", "value" => esc_html__("Basic Week", 'calendar-anything')),
            array("id" => "dayGridDay", "value" => esc_html__("Basic Day", 'calendar-anything')),
            array("id" => "timeGridWeek", "value" => esc_html__("Agenda Week", 'calendar-anything')),
            array("id" => "timeGridDay", "value" => esc_html__("Agenda Day", 'calendar-anything')),
            array("id" => "listYear", "value" => esc_html__("List Year", 'calendar-anything')),
            array("id" => "listMonth", "value" => esc_html__("List Month", 'calendar-anything')),
            array("id" => "listWeek", "value" => esc_html__("List Week", 'calendar-anything')),
            array("id" => "listDay", "value" => esc_html__("List Day", 'calendar-anything')),
            array("id" => "listDuration", "value" => esc_html__("List Duration", 'calendar-anything')),
        );
    }

    public function get_calendar_day_numbers() {
        return array(
            array("id" => "0", "value" => esc_html__("Sunday", 'calendar-anything')),
            array("id" => "1", "value" => esc_html__("Monday", 'calendar-anything')),
            array("id" => "2", "value" => esc_html__("Tuesday", 'calendar-anything')),
            array("id" => "3", "value" => esc_html__("Wednesday", 'calendar-anything')),
            array("id" => "4", "value" => esc_html__("Thursday", 'calendar-anything')),
            array("id" => "5", "value" => esc_html__("Friday", 'calendar-anything')),
            array("id" => "6", "value" => esc_html__("Saturday", 'calendar-anything')),
        );
    }

    public function get_calendar_firstDay_numbers() {
        return array(
            array("id" => "-1", "value" => esc_html__("Today", 'calendar-anything')),
            array("id" => "0", "value" => esc_html__("Sunday", 'calendar-anything')),
            array("id" => "1", "value" => esc_html__("Monday", 'calendar-anything')),
            array("id" => "2", "value" => esc_html__("Tuesday", 'calendar-anything')),
            array("id" => "3", "value" => esc_html__("Wednesday", 'calendar-anything')),
            array("id" => "4", "value" => esc_html__("Thursday", 'calendar-anything')),
            array("id" => "5", "value" => esc_html__("Friday", 'calendar-anything')),
            array("id" => "6", "value" => esc_html__("Saturday", 'calendar-anything')),
        );
    }

    public function get_event_template_shortcuts() {
        $options = CMCAL()->setup_options;
        $general_options = $options['general_options'];
        $post_types = $general_options["post_types"];
        $post_types_options = $options['post_types_options'];
        $arr = array(
            array("shortcode" => "[event_title]", "event_attr" => "event.title"),
            array("shortcode" => "[event_start]", "event_attr" => "(event.start != null ? cmcal_language_date(cmcal_vars, event.start, eventDateFormat) : '')"),
            array("shortcode" => "[event_end]", "event_attr" => "(event.end != null ? cmcal_language_date(cmcal_vars, event.end, eventDateFormat) : '')"),
            array("shortcode" => "[event_start_time]", "event_attr" => "(event.start != null ? moment.utc(event.start).locale(cmcal_vars['language_code']).format(eventTimeFormat) : '')"),
            array("shortcode" => "[event_end_time]", "event_attr" => "(event.end != null ? moment.utc(event.end).locale(cmcal_vars['language_code']).format(eventTimeFormat) : '')"),
        );
        if ($general_options["include_image"] == "include") {
            $arr[] = array("shortcode" => "[post_image]", "event_attr" => "event.extendedProps['post_image']");
        }
        if ($general_options["include_content"] == "include") {
            $arr[] = array("shortcode" => "[post_content]", "event_attr" => "event.extendedProps['post_content']");
        }
        if ($general_options["include_post_author"] == "include") {
            $arr[] = array("shortcode" => "[post_author]", "event_attr" => "event.extendedProps['post_author']");
        }
        if ($general_options["include_post_date"] == "include") {
            $arr[] = array("shortcode" => "[post_date]", "event_attr" => "event.extendedProps['post_date']");
        }
        if ($general_options["include_excerpt"] == "include") {
            $arr[] = array("shortcode" => "[excerpt]", "event_attr" => "event.extendedProps['excerpt']");
        }
        if ($general_options["include_permalink"] == "include") {
            $arr[] = array("shortcode" => "[permalink]", "event_attr" => "event.extendedProps['permalink']");
        }

        //Shortcodes for taxonomies
        $taxonomies = $this->get_all_taxonomies_event_template_filter($post_types_options);
        if ($taxonomies) {
            foreach ($taxonomies as $tax) {
                $arr[] = array("shortcode" => "[" . $tax . "]", "event_attr" => "(typeof(event.extendedProps['" . $tax . "']) != 'undefined' ? event.extendedProps['" . $tax . "'].join(', ') : '')");
                $arr[] = array("shortcode" => "[" . $tax . "-ids]", "event_attr" => "event.extendedProps['" . $tax . "-ids']");
            }
        }

        foreach ($post_types_options as $post_type => $post_type_options) {
            //Shortcodes for meta_keys
            $meta_keys = $post_type_options["meta_keys_event_template"];
            if ($meta_keys) {
                foreach ($meta_keys as $meta_key) {
                    $arr[] = array("shortcode" => "[" . $meta_key . "]", "event_attr" => "event.extendedProps['" . $meta_key . "']");
                }
            }
        }

        if (in_array("event", $post_types) && class_exists("EM_Event")) {
            $arr[] = array("shortcode" => "[booked_spaces]", "event_attr" => "event.extendedProps['booked_spaces']");
            $arr[] = array("shortcode" => "[spaces]", "event_attr" => "event.extendedProps['spaces']");
            $arr[] = array("shortcode" => "[remaining_spaces]", "event_attr" => "event.extendedProps['remaining_spaces']");
        }

        //Custom Fields
        $custom_fields = $general_options["custom_fields"];
        if ($custom_fields != null) {
            foreach ($custom_fields as $cf) {
                if (isset($cf["shortcode"]))
                    $arr[] = array("shortcode" => "[" . $cf["shortcode"] . "]", "event_attr" => "event.extendedProps['" . $cf["shortcode"] . "']"); // version 1.29
            }
        }

        // Extra fields (version 1.27)
        $extra_fields = apply_filters('cmcal_extra_fields', array());
        if (!empty($extra_fields)) {
            foreach ($extra_fields as $extra_field) {
                $arr[] = array("shortcode" => "[" . $extra_field . "]", "event_attr" => "event.extendedProps['" . $extra_field . "']");
            }
        }
        return $arr;
    }

    public function get_all_taxonomies_event_template_filter($post_types_options) {
        $all_taxonomies = array();
        foreach ($post_types_options as $post_type => $post_type_options) {
            $taxonomies = $post_type_options["taxonomies_event_template_filter"];
            if ($taxonomies) {
                foreach ($taxonomies as $tax) {
                    $all_taxonomies[] = $tax;
                }
            }
        }
        $result = array_unique($all_taxonomies);
        return $result;
    }

    public function get_filter_template_shortcuts() {
        $options = CMCAL()->setup_options;
        $post_types_options = $options['post_types_options'];
        $arr = array(
            array("shortcode" => "[search_box]"),
            array("shortcode" => "[date_navigator]"),
        );
        
        //Shortcodes for taxonomies
        $taxonomies = $this->get_all_taxonomies_event_template_filter($post_types_options);
        if ($taxonomies) {
            foreach ($taxonomies as $tax) {
                $arr[] = array("shortcode" => "[filter_box_" . $tax . "]");
            }
        }
        if (!empty(CMCAL()->calendar_custom_filters)) {
            foreach (CMCAL()->calendar_custom_filters as $custom_filter) {
                $arr[] = array("shortcode" => "[filter_box_" . $custom_filter["id"] . "]");
            }
        }
        return $arr;
    }

    public function get_border_styles() {
        return array(
            array("id" => "", "value" => ""),
            array("id" => "solid", "value" => esc_html__("Solid", 'calendar-anything')),
            array("id" => "dotted", "value" => esc_html__("Dotted", 'calendar-anything')),
            array("id" => "dashed", "value" => esc_html__("Dashed", 'calendar-anything')),
            array("id" => "double", "value" => esc_html__("Double", 'calendar-anything')),
            array("id" => "groove", "value" => esc_html__("Groove", 'calendar-anything')),
            array("id" => "ridge", "value" => esc_html__("Ridge", 'calendar-anything')),
            array("id" => "inset", "value" => esc_html__("Inset", 'calendar-anything')),
            array("id" => "outset", "value" => esc_html__("Outset", 'calendar-anything')),
            array("id" => "none", "value" => esc_html__("None", 'calendar-anything')),
            array("id" => "hidden", "value" => esc_html__("Hidden", 'calendar-anything')),
        );
    }

    public function get_defaultDate_MovableDate_options() {
        return array(
            array("id" => "0", "value" => esc_html__("Today", 'calendar-anything')),
            array("id" => "1", "value" => esc_html__("Start of Week (Sunday)", 'calendar-anything')),
            array("id" => "2", "value" => esc_html__("Start of Week (Monday)", 'calendar-anything')),
            array("id" => "3", "value" => esc_html__("1st of Month", 'calendar-anything')),
        );
    }

    public function get_font_text_align() {
        return array(
            array("id" => "", "value" => ""),
            array("id" => "inherit", "value" => esc_html__("Inherit", 'calendar-anything')),
            array("id" => "left", "value" => esc_html__("Left", 'calendar-anything')),
            array("id" => "right", "value" => esc_html__("Right", 'calendar-anything')),
            array("id" => "center", "value" => esc_html__("Center", 'calendar-anything')),
            array("id" => "justify", "value" => esc_html__("Justify", 'calendar-anything')),
            array("id" => "initial", "value" => esc_html__("Initial", 'calendar-anything')),
        );
    }

    public function get_vertivcalAlign() {
        return array(
            array("id" => "", "value" => ""),
            array("id" => "top", "value" => esc_html__("Top", 'calendar-anything')),
            array("id" => "middle", "value" => esc_html__("Middle", 'calendar-anything')),
        );
    }

    public function get_textTransform() {
        return array(
            array("id" => "", "value" => ""),
            array("id" => "none", "value" => esc_html__("None", 'calendar-anything')),
            array("id" => "capitalize", "value" => esc_html__("Capitalize", 'calendar-anything')),
            array("id" => "uppercase", "value" => esc_html__("Uppercase", 'calendar-anything')),
            array("id" => "lowercase", "value" => esc_html__("Lowercase", 'calendar-anything')),
        );
    }

    public function get_tooltip_position() {
        return array(
            array("id" => "top left", "value" => esc_html__("Top Left", 'calendar-anything')),
            array("id" => "top center", "value" => esc_html__("Top Center", 'calendar-anything')),
            array("id" => "top right", "value" => esc_html__("Top Right", 'calendar-anything')),
            array("id" => "center left", "value" => esc_html__("Center Left", 'calendar-anything')),
            array("id" => "center center", "value" => esc_html__("Center Center", 'calendar-anything')),
            array("id" => "center right", "value" => esc_html__("Center Right", 'calendar-anything')),
            array("id" => "bottom Left", "value" => esc_html__("Bottom Left", 'calendar-anything')),
            array("id" => "bottom center", "value" => esc_html__("Bottom Center", 'calendar-anything')),
            array("id" => "bottom right", "value" => esc_html__("Bottom Right", 'calendar-anything')),
        );
    }

    public function get_tooltip_animation() {
        return array(
            array("id" => "fade", "value" => esc_html__("Fade", 'calendar-anything')),
            array("id" => "slide_down", "value" => esc_html__("Slide", 'calendar-anything')),
        );
    }

    public function get_times() {
        $arr = array();
        for ($x = 0; $x <= 23; $x++) {
            for ($y = 0; $y <= 3; $y++) {
                $time = str_pad($x, 2, "0", STR_PAD_LEFT) . ':' . str_pad(($y * 15), 2, "0", STR_PAD_LEFT);
                $arr[] = array("id" => $time, "value" => $time);
            }
        }
        return $arr;
    }

}
