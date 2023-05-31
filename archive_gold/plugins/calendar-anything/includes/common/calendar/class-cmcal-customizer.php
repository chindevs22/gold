<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Cmcal_Customizer {

    public function is_null_or_empty_string($question) {
        return (!isset($question) || trim($question) === '');
    }

    public function get_calendar_styles_init($calendar_id) {
        $styles = CMCAL()->dal->get_calendar_custom_styles($calendar_id);
        return $this->get_calendar_styles($styles, $calendar_id);
    }

    public function get_color($parameters) {
        $color = $parameters[0];
        $transparent = $parameters[1];

        return $transparent == "true" ? "transparent" : $color;
    }

    public function get_font_weight($parameters) {
        $fonts_variant = $parameters[0];
        if (empty($fonts_variant))
            return "";
        return str_replace("italic", "", $fonts_variant);
    }

    public function get_font_style($parameters) {
        $fonts_variant = $parameters[0];
        if (empty($fonts_variant))
            return "";
        return (strpos($fonts_variant, 'italic') !== false) ? "italic" : "normal";
    }

    public function get_number_with_px($parameters) {
        $size = $parameters[0];
        if ($this->is_null_or_empty_string($size))
            return "";
        return $size . "px";
    }

    public function get_number_with_percentage($parameters) {
        $size = $parameters[0];
        if ($this->is_null_or_empty_string($size))
            return "";
        return $size . "%";
    }

    public function get_number_with_px_zero_if_empty($parameters) {
        $size = $parameters[0];
        if ($this->is_null_or_empty_string($size))
            return "0";
        return $size . "px";
    }

    public function get_border_spacing($parameters) {
        if (empty($parameters[0]) && empty($parameters[1]))
            return "";
        $top_bottom = empty($parameters[0]) ? "0" : $parameters[0];
        $right_left = empty($parameters[1]) ? "0" : $parameters[1];

        return $right_left . "px" . " " . $top_bottom . "px";
    }

    public function get_day_number_display($parameters) {
        $size = $parameters[0];
        if (empty($size))
            return "0";
        return $size == "0" ? "inline" : "inline-block";
    }

    public function get_border_collapse($parameters) {
        $border_spacing = $parameters[0];
        if (empty($border_spacing))
            return "";
        return "separate";
    }

    public function get_border_transparent($parameters) {
        $border_spacing = $parameters[0];
        if (empty($border_spacing))
            return "";
        return $border_spacing . "px solid transparent!important";
    }

    private function get_style_selector($setting, $calendar_id, $isMasterSelector) {

        $arr = explode(',', $setting["output"]["selector"]);
        $MasterSelector = (isset($setting["MasterSelector"]) && !is_null($setting["MasterSelector"])) ? $setting["MasterSelector"] : ".cmcal-calendar-container.cmcal-calendar-{calendar-id}";
        $MasterSelector = str_replace("{calendar-id}", $calendar_id, $MasterSelector);
        $class = $MasterSelector . ($isMasterSelector ? "" : " ");
        array_walk($arr, function(&$value, $key, $params) {
            $value = $params[0] . $value;
        }, array($class));

        return implode(",", $arr);
    }

    public function get_calendar_styles($styles, $calendar_id) {
        $calendar_style = "";

        $settings = CMCAL()->customizer_settings->get_calendar_settings();
        foreach ($settings as $setting) {
            if (isset($setting["output"])) {
                $isMasterSelector = isset($setting["isMasterSelector"]) ? $setting["isMasterSelector"] : false;
                //Single Editor
                if (!is_array($setting['id'])) {
                    if (!empty($styles[$setting['id']])) {
                        $important = (isset($setting["important"]) && $setting["important"]) ? " !important" : "";
                        if (!empty($setting["output"])) {
                            $calendar_style .= $this->get_style_selector($setting, $calendar_id, $isMasterSelector) . " {"
                                    . $setting["output"]["property"] . ":" . $styles[$setting['id']] . $important . ";" .
                                    "}";
                        }
                    }
                } else {
                    //Multi Editors
                    $important = (isset($setting["important"]) && $setting["important"]) ? " !important" : "";
                    $calendar_multi_styles = "";
                    foreach ($setting["output"]["property"] as $key => $value) {
                        $val = '';
                        switch ($value['type']) {
                            case "id":
                                $dbfield = $setting['id'][$value['name']];
                                $val = $styles[$dbfield];
                                break;

                            case "function";
                                $function = $value['name'];
                                $parameters = [];
                                foreach ($value["parameters"] as $parameter) {
                                    $param_val = "";
                                    switch ($parameter['type']) {
                                        case "id":
                                            $dbfield = $setting['id'][$parameter['name']];
                                            $param_val = $styles[$dbfield];
                                            break;
                                    }
                                    $parameters[] = $param_val;
                                }
                                $val = call_user_func(array($this, $function), $parameters);
                                break;
                        }
                        if (!empty($val)) {
                            $calendar_multi_styles .= $key . ":" . $val . $important . ";";
                        }
                    }
                    if (!empty($calendar_multi_styles)) {
                        $calendar_style .= $this->get_style_selector($setting, $calendar_id, $isMasterSelector) . " {";
                        $calendar_style .= $calendar_multi_styles;
                        $calendar_style .= " }";
                    }
                }
            }
        }

        $calendar_style .= $this->custom_css($styles, $calendar_id);
        return $calendar_style;
    }

    public function custom_css($styles, $calendar_id) {
        $calendar_id_class = ".cmcal-calendar-container.cmcal-calendar-" . $calendar_id;
        $dynamic_css = ' ';
        $responsiveWidth = $styles["responsiveWidth"];
        $responsiveBtnSize = $styles["responsiveBtnSize"];
        $responsiveTitleSize = $styles["responsiveTitleSize"];
        if (!($this->is_null_or_empty_string($responsiveWidth))) {
            $dynamic_css .= '@media (max-width: ' . $responsiveWidth . 'px) {';
            $dynamic_css .= $calendar_id_class . ' .fc-toolbar .fc-left,' . $calendar_id_class . ' .fc-toolbar .fc-right { margin-bottom: 10px;  }';
            $dynamic_css .= $calendar_id_class . ' .fc-toolbar .fc-center { display: block;  }';
            $dynamic_css .= $calendar_id_class . ' .fc-toolbar .fc-right { float: left;  }';
            $dynamic_css .= $calendar_id_class . ' .fc-toolbar .fc-button { font-size: ' . $responsiveBtnSize . 'px;line-height: ' . $responsiveBtnSize . 'px; }';
            $dynamic_css .= $calendar_id_class . ' .fc-toolbar h2 { font-size: ' . $responsiveTitleSize . 'px;line-height: ' . $responsiveTitleSize . 'px; }';
            $dynamic_css .= '}';
        }
        if (isset($styles["body_cell_padding_left"]) && !empty($styles["body_cell_padding_left"])) {
            $dynamic_css .= $calendar_id_class . ' .fc-unthemed .fc-popover { margin-left: -' . $styles["body_cell_padding_left"] . 'px;  }';
        }
        // Modal CSS
        if ($styles["tooltip_modal"] == '1') {
            $dynamic_css .= $calendar_id_class . ' .fc-event { cursor: pointer;  }';
        }
        
        // Custom CSS textarea
        $calendar_custom_css = $styles["custom_css"];
        if (!($this->is_null_or_empty_string($calendar_custom_css))) {
            $calendar_custom_css = str_replace('{calendar_id_class}', $calendar_id_class, $calendar_custom_css);
            $dynamic_css .= $calendar_custom_css;
        }
        
        return $dynamic_css;
    }

    public function localize_calendar_script($calendar_id) {
        $js_vars = array();
        $schema = is_ssl() ? 'https' : 'http';
        $js_vars['ajaxurl'] = admin_url('admin-ajax.php', $schema);
        $js_vars['shortname'] = CMCAL()->shortname;
        $js_vars['data_action'] = 'CMCAL_get_events';

        //For Multilingual
        if (function_exists('icl_object_id')) {
            $js_vars['language_code'] = ICL_LANGUAGE_CODE;
            $js_vars['wpml'] = true;
        } else {
            $js_vars['language_code'] = get_bloginfo('language'); //WPLANG;// get_locale();
            $js_vars['wpml'] = false;
        }
        // Fix for serbian cyrillic languages
        if ($js_vars['language_code'] == 'sr-RS') {
            $js_vars['language_code'] = 'sr-cyrl';
        }
        $calendar_custom_settings = CMCAL()->dal->get_calendar_custom_settings($calendar_id);

        $settings = CMCAL()->customizer_settings->get_calendar_settings();
        $stds = array_column($settings, 'std', 'id');
        $js_settings = CMCAL()->customizer_settings->get_calendar_custom_settings_editors();
        foreach ($js_settings as $key => $value) {
            $editor = $value["id"];
            $std = isset($stds[$editor]) ? $stds[$editor] : "";
            $js_vars[$editor] = !empty($calendar_custom_settings[$editor]) ? $calendar_custom_settings[$editor] : $std;
        }

        $js_vars["event_template"] = do_shortcode($js_vars["event_template"]);
        $js_vars["event_template_list"] = do_shortcode($js_vars["event_template_list"]);

        //Toolbar Settings  
        $db_toolbar_settings = stripslashes($calendar_custom_settings["toolbar_settings"]);
        $header_settings = json_decode($db_toolbar_settings, true);

        $js_vars["toolbar_json"] = $db_toolbar_settings;
        if(!empty($header_settings)){
            $js_vars["toolbar_left"] = $this->populate_toolbar_setting($header_settings["left"]);
            $js_vars["toolbar_center"] = $this->populate_toolbar_setting($header_settings["center"]);
            $js_vars["toolbar_right"] = $this->populate_toolbar_setting($header_settings["right"]);
        }

        if (!isset(CMCAL()->calendar_setup_options[$calendar_id]["event_rendering"]) || CMCAL()->calendar_setup_options[$calendar_id]["event_rendering"] != "ajax") {
            $js_vars["all_events"] = CMCAL()->dal->get_events(null, null, null, $calendar_id, true);
        }

        $js_vars["events_date_range"] = CMCAL()->utils->get_events_date_range($calendar_id);
        $js_vars["events_date_range_navigation_type"] = 'disabled_buttons';
        if (isset(CMCAL()->calendar_setup_options[$calendar_id]["events_date_range_navigation_type"])) {
            $js_vars["events_date_range_navigation_type"] = CMCAL()->calendar_setup_options[$calendar_id]["events_date_range_navigation_type"];
        }

        wp_localize_script('codemine-calendar-js', 'CMCAL_vars_' . $calendar_id, apply_filters('cmcal_js_vars', $js_vars, $calendar_id));
    }

    private function populate_toolbar_setting($header_setting) {
        if (!isset($header_setting))
            return "";
        $header_settings_string = "";
        foreach ($header_setting as $option) {
            switch ($option['type']) {
                case "name":
                    $header_settings_string .= $option['value'];
                    break;
                case "gap":
                    $header_settings_string .= $option['value'] == "0" ? "," : " ";
                    break;
            }
        }
        return $header_settings_string;
    }

}
