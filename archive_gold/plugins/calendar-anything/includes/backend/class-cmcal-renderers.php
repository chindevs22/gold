<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Cmcal_Renderers {

///////////////////////////////////////////////////////////////////////////////////////////////////////////
    //themes
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    function create_section_for_themes($value) {
        $this->create_opening_tag($value);
        $val = !empty(CMCAL()->preview_theme_name) ? CMCAL()->preview_theme_name : $value['db_value'][$value['id']];
        $themeNames = array('Month Green', 'Month Bordered Blue', 'Month Black Rounded', 'Month Orange', 'Agenda Week Purple', 'Agenda Week Turquoise', 'Month Minimal Blue', 'Month Minimal Orange', 'Month Minimal Pink', 'List Year Orange Square', 'List Year Purple Rounded');
        //Move selected theme to the top
        $keyThemeName = array_search($val, $themeNames);
        if ($keyThemeName != 0) {
            $movethemeNames = $themeNames[$keyThemeName];
            unset($themeNames[$keyThemeName]);
            array_unshift($themeNames, $movethemeNames);
        }

        echo '<div class="cmcal-theme-browser">';
        foreach ($themeNames as $themeName) {
            $active_class = $themeName == $val ? "active" : "";
            $active_span = $themeName == $val ? "<span>" . esc_html__('Active', 'calendar-anything') . ": </span>" : "";
            $button_name = $themeName == $val ? esc_html__('Defaults Preview', 'calendar-anything') : esc_html__('Preview', 'calendar-anything');

            $theme_screenshot = plugins_url('includes/backend/calendar-themes/' . $themeName . '/theme_screenshot.png', Codemine_Calendar_PLUGIN_FILE);
            echo '
            <div class="theme ' . $active_class . '"  >
		<div class="theme-screenshot">
                    <img src="' . esc_url($theme_screenshot) . '" alt="">
		</div>
		<h2 class="theme-name" >' . $active_span . $themeName . '</h2>
                <div class="theme-actions">
                    <input name="load_theme_' . $themeName . '" type="button" class="button-primary" value="' . esc_attr($button_name) . '" onclick="submit_form(this, document.forms[\'customize_calendar_form\'])" />
                </div>
            </div>';
        }
        echo '</div>';

        echo '<input type="hidden" id="' . esc_attr($value['id']) . '" name="' . esc_attr($value['id']) . '" value="' . esc_attr($val) . '" />';
        $this->create_closing_tag($value);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    //margin/padding
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    function create_section_for_border_spacing($value) {
        $this->create_opening_tag($value);

        $ids = array_filter((array) $value['id']);

        $val_top_bottom = $value['db_value'][$ids["top_bottom"]];
        $val_right_left = $value['db_value'][$ids["righ_left"]];

        echo '<table class="cmcal-margin-padding-editors-container">';
        echo '<tr>';
        echo '<td>';
        //Top
        echo '<span class="cmcal-arrow">Top-Bottom (px)</span>';
        $this->create_section_for_number_inner($ids["top_bottom"], $val_top_bottom, "");
        echo '</td>';
        echo '<td>';
        //Right
        echo '<span class="cmcal-arrow">Right-Left (px)</span>';
        $this->create_section_for_number_inner($ids["righ_left"], $val_right_left, "");
        echo '</td>';

        echo '</tr>';

        echo '</table>';

        $this->create_closing_tag($value);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    //hidden days
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    function create_section_for_hidden_days($value) {
        $this->create_opening_tag($value);

        $val = $value['db_value'][$value['id']];
        $hidden_days = json_decode($val, true);

        $days = CMCAL()->lov->get_calendar_day_numbers();

        echo '<table class="cmcal-hidden-days-editors-container">';
        foreach ($days as $day) {
            $checked_value = $day['id'];
            if ($hidden_days != null) {
                if (in_array($day['id'], $hidden_days)) {
                    $checked_value = "-";
                }
            }
            echo '<tr>';
            echo '<td>';
            $this->create_section_for_checkbox_inner("hidden-days-" . $day['id'], $checked_value, $day['value'], "cmcal_hidden_days_setting", $day['id'], "-", "");
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';

        echo '<input type="hidden" id="' . esc_attr($value['id']) . '" name="' . esc_attr($value['id']) . '" value="' . esc_attr($val) . '" />';
        $this->create_closing_tag($value);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    //business days
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    function create_section_for_business_days($value) {
        $this->create_opening_tag($value);

        $val = $value['db_value'][$value['id']];
        $business_days = json_decode($val, true);

        $days = CMCAL()->lov->get_calendar_day_numbers();

        echo '<table class="cmcal-business-days-editors-container">';
        foreach ($days as $day) {
            $checked_value = $day['id'];
            if ($business_days != null) {
                if (!in_array($day['id'], $business_days)) {
                    $checked_value = "-";
                }
            }
            echo '<tr>';
            echo '<td>';
            $this->create_section_for_checkbox_inner("business-days-" . $day['id'], $checked_value, $day['value'], "cmcal_business_days_setting", $day['id'], "-", "");
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';

        echo '<input type="hidden" id="' . $value['id'] . '" name="' . $value['id'] . '" value="' . esc_attr($val) . '" />';
        $this->create_closing_tag($value);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    //margin/padding
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    function create_section_for_margin_padding($value) {
        $this->create_opening_tag($value);

        $ids = array_filter((array) $value['id']);

        $val_top = $value['db_value'][$ids["top"]];
        $val_right = $value['db_value'][$ids["right"]];
        $val_bottom = $value['db_value'][$ids["bottom"]];
        $val_left = $value['db_value'][$ids["left"]];

        echo '<table class="cmcal-margin-padding-editors-container">';
        echo '<tr>';
        echo '<td>';
        //Top
        echo '<span class="cmcal-arrow top" ></span>';
        $this->create_section_for_number_inner($ids["top"], $val_top, "");
        echo '<span class="cmcal-arrow-units" >px</span>';
        echo '</td>';
        echo '<td>';
        //Right
        echo '<span class="cmcal-arrow right"></span>';
        $this->create_section_for_number_inner($ids["right"], $val_right, "");
        echo '<span class="cmcal-arrow-units" >px</span>';
        echo '</td>';
        echo '<td>';
        //Bottom
        echo '<span class="cmcal-arrow bottom"></span>';
        $this->create_section_for_number_inner($ids["bottom"], $val_bottom, "");
        echo '<span class="cmcal-arrow-units" >px</span>';
        echo '</td>';
        echo '<td>';
        //Left
        echo '<span class="cmcal-arrow left"></span>';
        $this->create_section_for_number_inner($ids["left"], $val_left, "");
        echo '<span class="cmcal-arrow-units" >px</span>';
        echo '</td>';
        echo '</tr>';

        echo '</table>';

        $this->create_closing_tag($value);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    //Borders
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////

    function create_section_for_borders($value) {
        $this->create_opening_tag($value);

        $ids = array_filter((array) $value['id']);

        $val_top = $value['db_value'][$ids["top"]];
        $val_right = $value['db_value'][$ids["right"]];
        $val_bottom = $value['db_value'][$ids["bottom"]];
        $val_left = $value['db_value'][$ids["left"]];
        $val_style = $value['db_value'][$ids["style"]];

        $class = isset($value['class']) ? $value['class'] : "";

        echo '<table class="cmcal-border-editors-container">';
        echo '<tr>';
        echo '<td class="cmcal_border_style_td">';
        //Style
        echo '<span class="border_setting_header">Style</span>';
        $this->create_section_for_select_inner($ids["style"], CMCAL()->lov->get_border_styles(), $val_style, "cmcal_border_style");
        echo '</td>';
        echo '<td>';
        //Top
        echo '<span class="cmcal-arrow top" ></span>';
        $this->create_section_for_number_inner($ids["top"], $val_top, $class);
        echo '<span class="cmcal-arrow-units" >px</span>';
        echo '</td>';
        echo '<td>';
        //Right
        echo '<span class="cmcal-arrow right"></span>';
        $this->create_section_for_number_inner($ids["right"], $val_right, $class);
        echo '<span class="cmcal-arrow-units" >px</span>';
        echo '</td>';
        echo '<td>';
        //Bottom
        echo '<span class="cmcal-arrow bottom"></span>';
        $this->create_section_for_number_inner($ids["bottom"], $val_bottom, $class);
        echo '<span class="cmcal-arrow-units" >px</span>';
        echo '</td>';
        echo '<td>';
        //Left
        echo '<span class="cmcal-arrow left"></span>';
        $this->create_section_for_number_inner($ids["left"], $val_left, $class);
        echo '<span class="cmcal-arrow-units" >px</span>';
        echo '</td>';
        echo '</tr>';



        if ($value['includeRadius']) {
            $val_top_left_radius = $value['db_value'][$ids["top-left-radius"]];
            $val_top_right_radius = $value['db_value'][$ids["top-right-radius"]];
            $val_bottom_left_radius = $value['db_value'][$ids["bottom-left-radius"]];
            $val_bottom_right_radius = $value['db_value'][$ids["bottom-right-radius"]];

            echo '<tr>';
            echo '<td class="cmcal_border_style_td">';
            //Style
            echo '<span class="border_setting_header">Radius</span>';

            echo '</td>';
            echo '<td>';
            //Top
            echo '<span class="cmcal-arrow top-left" ></span>';
            $this->create_section_for_number_inner($ids["top-left-radius"], $val_top_left_radius, $class);
            echo '<span class="cmcal-arrow-units" >px</span>';
            echo '</td>';
            echo '<td>';
            //Right
            echo '<span class="cmcal-arrow top-right"></span>';
            $this->create_section_for_number_inner($ids["top-right-radius"], $val_top_right_radius, $class);
            echo '<span class="cmcal-arrow-units" >px</span>';
            echo '</td>';
            echo '<td>';
            //Bottom
            echo '<span class="cmcal-arrow bottom-left"></span>';
            $this->create_section_for_number_inner($ids["bottom-left-radius"], $val_bottom_left_radius, $class);
            echo '<span class="cmcal-arrow-units" >px</span>';
            echo '</td>';
            echo '<td>';
            //Left
            echo '<span class="cmcal-arrow bottom-right"></span>';
            $this->create_section_for_number_inner($ids["bottom-right-radius"], $val_bottom_right_radius, $class);
            echo '<span class="cmcal-arrow-units" >px</span>';
            echo '</td>';
            echo '</tr>';
        }



        echo '<tr>';
        echo '<td colspan="5" class="cmcal_border_color">';
        //Color
        echo '<span class="border_setting_header">Color</span>';
        $this->create_section_for_color_picker_with_transparent_inner($value);
        echo '</td>';
        echo '</tr>';
        echo '</table>';

        $this->create_closing_tag($value);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    //Fonts
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    function create_section_for_fonts($value) {
        $this->create_opening_tag($value);
        $fonts = CMCAL()->basicfonts + CMCAL()->googlefonts;

        $ids = array_filter((array) $value['id']);
        $val_familly = $value['db_value'][$ids["familly"]];
        $val_variant = $value['db_value'][$ids["variant"]];
        $val_size = $value['db_value'][$ids["size"]];
        $val_line_height = $value['db_value'][$ids["line_height"]];
        $val_text_align = $value['db_value'][$ids["text_align"]];
        $val_text_transform = $value['db_value'][$ids["text_transform"]];
        $val_color = $value['db_value'][$ids["color"]];
        $std_color = (isset($value['std_color']) ? $value['std_color'] : "");

        echo '<table class="cmcal-font-editors-container">';
        echo '<tr>';
        echo '<td>';
        //font-familly
        $this->create_editors_for_font_familly($ids, $fonts, $val_familly);
        echo '</td>';
        echo '<td>';
        //variants
        $this->create_editors_for_font_variants($ids, $fonts, $val_familly, $val_variant);
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>';
        //size
        $this->create_editors_for_font_number_pxem($ids["size"], $val_size, "Font Size");
        echo '</td>';
        echo '<td>';
        //size
        $this->create_editors_for_font_number_pxem($ids["line_height"], $val_line_height, "Line Height");
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        // text align
        echo '<td>';
        echo '<label>Text Align</label>';
        $this->create_section_for_select_inner($ids["text_align"], CMCAL()->lov->get_font_text_align(), $val_text_align, 'cmcal_font_textalign');
        echo '</td>';
        echo '<td>';
        //Text transform
        echo '<label>Text Transform</label>';
        $this->create_section_for_select_inner($ids["text_transform"], CMCAL()->lov->get_textTransform(), $val_text_transform, 'cmcal_font_texttransform');
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>';
        //Color
        $this->create_editors_for_font_color($ids["color"], $val_color, $std_color);
        echo '</td>';
        //Subsets
        echo '<td>';
        $this->create_editors_for_font_subsets($ids, $fonts, $val_familly);
        echo '</td>';
        echo '</tr>';
        echo '</table>';

        $this->create_closing_tag($value);
    }

    private function create_editors_for_font_familly($ids, $fonts, $val_familly) {
        echo '<label>' . esc_html__('Font Family', 'calendar-anything') . '</label>';
        echo '<select class="cmcal_font_select font-famillies" id="' . esc_attr($ids["familly"]) . '" name="' . esc_attr($ids["familly"]) . '">';
        echo '<option ></option>';
        while ($font = current($fonts)) {
            $selected = key($fonts) == $val_familly ? 'selected="selected"' : "";
            echo '<option value="' . esc_attr(key($fonts)) . '" ' . $selected . '">' . key($fonts) . '</option>';
            next($fonts);
        }
        echo '</select>';
    }

    private function create_editors_for_font_variants($ids, $fonts, $val_familly, $val_variant) {
        $font_variants = [];
        if (isset($fonts[$val_familly])) {
            $font_variants = $fonts[$val_familly]['variants'];
        } else {
            $font_variants = array(
                array("id" => "normal", "name" => esc_html__('Normal', 'calendar-anything')),
                array("id" => "bold", "name" => esc_html__('Bold', 'calendar-anything')),
                array("id" => "bolder", "name" => esc_html__('Bolder', 'calendar-anything')),
                array("id" => "lighter", "name" => esc_html__('Lighter', 'calendar-anything')),
            );
        }

        echo '<label>' . esc_html__('Font Variants', 'calendar-anything') . '</label>';
        echo '<select class="cmcal_font_select font-variants" id="' . esc_attr($ids["variant"]) . '" name="' . esc_attr($ids["variant"]) . '" >';
        echo '<option ></option>';
        while ($font_variant = current($font_variants)) {
            $selected = $font_variant["id"] == $val_variant ? 'selected="selected"' : "";
            echo '<option value="' . esc_attr($font_variant["id"]) . '" ' . $selected . '   >' . $font_variant["name"] . '</option>';
            next($font_variants);
        }
        echo '</select>';
    }

    private function create_editors_for_font_subsets($ids, $fonts, $val_familly) {
        $font_subsets = isset($fonts[$val_familly]) ? $fonts[$val_familly]['subsets'] : [];
        $subsets_str = "";
        echo '<label>' . esc_html__('Font Subsets', 'calendar-anything') . '</label>';
        echo '<div class="cmcal_font_select font-subsets">';
        while ($font_subset = current($font_subsets)) {
            $subsets_str = $subsets_str . $font_subset["name"] . ', ';
            next($font_subsets);
        }
        echo rtrim($subsets_str, ", ");
        echo '</div>';

        $font_variants = isset($fonts[$val_familly]) ? $fonts[$val_familly]['variants'] : [];
    }

    private function create_editors_for_font_number_pxem($id, $val, $label) {
        $val_pxem = (strpos($val, 'em') !== false) ? "em" : "px";
        $selected_px = $val_pxem == 'px' ? 'selected="selected"' : "";
        $selected_em = $val_pxem == 'em' ? 'selected="selected"' : "";
        $val_number = str_replace($val_pxem, "", $val);

        echo '<div class="cmcal-font-editor-container-size">';
        echo '<label>' . $label . '</label>';
        echo '<input type="text" class="cmcal_font_select_size size" id="' . esc_attr($id) . '_value" name="' . esc_attr($id) . '_value" min="0" data-inputid="' . esc_attr($id) . '" value="' . esc_attr($val_number) . '">';
        echo '</div>';
        //size px-em
        echo '<div class="cmcal-font-editor-container-pxem">';
        echo '<label></label>';
        echo '<select class="cmcal_font_select_size pxem" id="' . esc_attr($id) . '_pxem" name="' . esc_attr($id) . '_pxem" data-inputid="' . esc_attr($id) . '" >';
        echo '<option value="px" ' . $selected_px . '>px</option>';
        echo '<option value="em" ' . $selected_em . '>em</option>';
        echo '</select>';
        echo '</div>';
        //size hidden
        echo '<input type="hidden"id="' . esc_attr($id) . '" name="' . esc_attr($id) . '" value="' . esc_attr($val) . '">';
    }

    private function create_editors_for_font_color($id, $val, $std_color) {
        $color_value = "";
        if ($val === FALSE) {
            $color_value = $std_color;
        } else {
            $color_value = $val;
        }

        echo '<label>' . esc_html__('Color', 'calendar-anything') . '</label>';
        echo '<input type="text" id="' . esc_attr($id) . '" name="' . esc_attr($id) . '" value="' . esc_attr($color_value) . '" class="cmcal-colorpicker" />';
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    //Toolbar Settings Editors
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    function create_section_for_toolbar_settings($value) {
        $this->create_opening_tag($value);
        $dbval = $value['db_value'][$value['id']];
        $val = stripslashes($dbval);
        $toolbar_settings = json_decode($val, true);
        $this->create_editors_for_toolbar_settings($toolbar_settings["left"], "left", esc_html__("Left", 'calendar-anything'));
        $this->create_editors_for_toolbar_settings($toolbar_settings["center"], "center", esc_html__("Center", 'calendar-anything'));
        $this->create_editors_for_toolbar_settings($toolbar_settings["right"], "right", esc_html__("Right", 'calendar-anything'));

        $this->create_template_for_toolbar_settings();

        echo '<input type="hidden" id="' . esc_attr($value['id']) . '" name="' . esc_attr($value['id']) . '" value="' . esc_attr($val) . '" />';

        $this->create_closing_tag($value);
    }

    private function create_editors_for_toolbar_settings($toolbar_setting, $container, $container_name) {
        echo '<h4>' . sprintf(__('Toolbar %s Container', 'calendar-anything'), $container_name) . '</h4>';
        echo '<div class="cmcal-toolbar-' . $container . '-container">';
        $dd_options = CMCAL()->lov->get_toolbar_settings_options();
        if (isset($toolbar_setting)) {
            foreach ($toolbar_setting as $option) {
                switch ($option['type']) {
                    case "name":
                        $this->create_editor_select_for_toolbar_settings($dd_options, $option['type'], $option['value'], $container);
                        break;
                    case "gap":
                        $this->create_editor_checkbox_for_toolbar_settings($option['type'], $option['value'], $container);
                        break;
                }
            }
        }
        echo '<input type="button" class="button-primary add_cmcal_toolbar_setting" value="' . esc_attr__('Add', 'calendar-anything') . '" data-container="' . $container . '">';
        echo'</div>';
    }

    private function create_editor_select_for_toolbar_settings($dd_options, $option_type, $option_value, $container) {
        echo '<div class="cmcal_toolbar_select_container">';

        echo '<input type="button" class="button-primary delete_cmcal_toolbar_setting" value="' . esc_attr__('Delete', 'calendar-anything') . '" data-container="' . $container . '">';
        echo '<select class="cmcal_toolbar_select cmcal_toolbar_editor" data-type="' . esc_attr($option_type) . '" data-container="' . esc_attr($container) . '">';
        foreach ($dd_options as $dd_option) {
            $selected = $dd_option['id'] == $option_value ? 'selected="selected"' : "";
            echo '<option value="' . esc_attr($dd_option['id']) . '" ' . $selected . '>' . $dd_option['value'] . '</option>';
        }
        echo '</select>';
        echo'</div>';
    }

    private function create_editor_checkbox_for_toolbar_settings($option_type, $option_value, $container) {
        $checked = $option_value == "1" ? "checked" : "";
        echo '<div class="cmcal_toolbar_gap_container">';
        echo esc_html__('Insert gap for next setting', 'calendar-anything') . ' <input type="checkbox" name="Gap" value="1" ' . $checked . ' class="cmcal_toolbar_gap cmcal_toolbar_editor" data-type="' . esc_attr($option_type) . '" data-container="' . esc_attr($container) . '">';
        echo'</div>';
    }

    private function create_template_for_toolbar_settings() {
        $dd_options = CMCAL()->lov->get_toolbar_settings_options();
        echo '<div class = "cmcal-hs-editors-template-name">';
        $this->create_editor_select_for_toolbar_settings($dd_options, "name", "", "");
        echo '</div>';
        echo '<div class = "cmcal-hs-editors-template-gap">';
        $this->create_editor_checkbox_for_toolbar_settings("gap", "0", "");
        echo '</div>';
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    //General Editors
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////

    function create_section_for_available_shortcodes($value) {
        $this->create_opening_tag($value);
        foreach ($value['options'] as $option) {
            echo '<div class="shortcode">' . $option['shortcode'] . '</div>';
        }
        $this->create_closing_tag($value);
    }

    function create_section_for_radio($value) {
        $this->create_opening_tag($value);
        foreach ($value['options'] as $option_value => $option_text) {
            $checked = ' ';
            if ($value['db_value'][$value['id']] == $option_value) {
                $checked = ' checked="checked" ';
            } else if (($value['db_value'][$value['id']] == null || $value['db_value'][$value['id']] == '') && $value['std'] == $option_value) {
                $checked = ' checked="checked" ';
            } else {
                $checked = ' ';
            }
            echo '<div class="cmcal-radio ">'
            . '<label><input type="radio" name="' . esc_attr($value['id']) . '" id="' . esc_attr($value['id']) . '" value="' . esc_attr($option_value) . '" ' . $checked . '" class=" ' . (isset($value['class']) ? $value['class'] : "") . '" />' . $option_text . '</label></div>';
        }
        $this->create_closing_tag($value);
    }

    function create_section_for_text($value) {
        $this->create_opening_tag($value);
        $text = "";
        if ($value['db_value'][$value['id']] === FALSE || $value['db_value'][$value['id']] == '') {
            $text = (isset($value['std']) ? $value['std'] : "");
        } else {
            $text = $value['db_value'][$value['id']];
        }

        echo '<input type="text" id="' . esc_attr($value['id']) . '" name="' . esc_attr($value['id']) . '" value="' . esc_attr($text) . '" class=" ' . (isset($value['class']) ? $value['class'] : "") . '" />';
        $this->create_closing_tag($value);
    }

    function create_section_for_textarea($value) {
        $this->create_opening_tag($value);
        echo '<textarea name="' . esc_attr($value['id']) . '" id="' . esc_attr($value['id']) . '" type="textarea" cols="" rows="10" class=" ' . (isset($value['class']) ? $value['class'] : "") . '">';
        $val = $value['db_value'][$value['id']];
        if ($val != "") {
            echo esc_html($val);
        } else {
            echo (isset($value['std']) ? $value['std'] : "");
        }
        echo '</textarea>';
        $this->create_closing_tag($value);
    }

    function create_section_for_date_picker($value) {
        $this->create_opening_tag($value);
        $text = "";
        if ($value['db_value'][$value['id']] === FALSE) {
            $text = (isset($value['std']) ? $value['std'] : "");
        } else {
            $text = $value['db_value'][$value['id']];
        }

        echo '<input type="text" id="' . esc_attr($value['id']) . '" name="' . esc_attr($value['id']) . '" value="' . esc_attr($text) . '" class="cmcal-datepicker ' . (isset($value['class']) ? $value['class'] : "") . '" />';
        $this->create_closing_tag($value);
    }

    function create_section_for_color_picker_with_transparent($value) {
        $this->create_opening_tag($value);

        $this->create_section_for_color_picker_with_transparent_inner($value);

        $this->create_closing_tag($value);
    }

    function create_section_for_color_picker_with_transparent_inner($value) {
        $class = (isset($value['class']) ? $value['class'] : "");

        $ids = array_filter((array) $value['id']);
        $id_color = $ids["color"];
        $id_transparent = $ids["transparent"];

        $val_transparent = isset($value['db_value'][$ids["transparent"]]) ? $value['db_value'][$ids["transparent"]] : "false";
        $val_color = "";
        if ($value['db_value'][$ids["color"]] === FALSE) {
            $val_color = (isset($value['color_std']) ? $value['std'] : "");
        } else {
            $val_color = $value['db_value'][$ids["color"]];
        }

        echo '<div class="color-picker">';
        echo '<input type="text" id="' . esc_attr($id_color) . '" name="' . esc_attr($id_color) . '" value="' . esc_attr($val_color) . '" class="cmcal-colorpicker ' . $class . '" />';
        $this->create_section_for_checkbox_inner($id_transparent, $val_transparent, "transparent", "cmcal-color-picker-transparent " . $class, "true", "false", "");
        echo "</div>";
    }

    function create_section_for_select($value) {
        $this->create_opening_tag($value);

        $id = $value['id'];
        $options = $value['options'];
        $val = isset($value['db_value'][$value['id']]) && !empty($value['db_value'][$value['id']]) ? $value['db_value'][$value['id']] : $value['std'];
        $class = isset($value['class']) ? $value['class'] : "";
        $this->create_section_for_select_inner($id, $options, $val, $class);

        $this->create_closing_tag($value);
    }

    function create_section_for_select_inner($id, $options, $val, $class) {
        echo '<select class="cmcal_select ' . (isset($class) ? $class : "") . '" id="' . esc_attr($id) . '" name="' . esc_attr($id) . '" />';
        foreach ($options as $dd_option) {
            $selected = $dd_option['id'] == $val ? 'selected="selected"' : "";
            echo '<option value="' . esc_attr($dd_option['id']) . '" ' . $selected . '>' . $dd_option['value'] . '</option>';
        }
        echo '</select>';
    }

    function create_section_for_multi_select($value) {
        $this->create_opening_tag($value);
        echo '<ul class="cmcal-checklist" id="' . esc_attr($value['id']) . '" >';
        foreach ($value['options'] as $option_value => $option_list) {
            $checked = " ";
            if ($value['db_value'][$value['id'] . "_" . $option_value]) {
                $checked = " checked='checked' ";
            }
            echo "<li>n";
            echo '<input type="checkbox" name="' . esc_attr($value['id']) . "_" . $option_value . '" value="true" ' . $checked . ' class="depth-' . ($option_list['depth'] + 1) . '" />' . $option_list['title'];
            echo "</li>n";
        }
        echo "</ul>";
        $this->create_closing_tag($value);
    }

    function create_section_for_checkbox($value) {
        $this->create_opening_tag($value);

        $id = $value['id'];
        $header = $value['name'];
        $val = $value['db_value'][$value['id']];
        $class = $value['class'];
        $checked_val = $value['checked_val'];
        $unchecked_val = $value['unchecked_val'];
        $header_class = $value['header_class'];
        create_section_for_checkbox_inner($id, $val, $header, $class, $checked_val, $unchecked_val, $header_class);

        $this->create_closing_tag($value);
    }

    function create_section_for_checkbox_inner($id, $val, $header, $class, $checked_val, $unchecked_val, $header_class) {
        $checked = $val == $checked_val ? "checked" : "";
        $val = empty($val) ? $unchecked_val : $val;
        echo '<div class="cmcal-checkboc-container">';
        echo '<span class="' . esc_attr($header_class) . '"><input type="checkbox" class="cmcal-checkbox ' . $class . '" data-id="' . esc_attr($id) . '"  data-checked-val="' . esc_attr($checked_val) . '" data-unchecked-val="' . esc_attr($unchecked_val) . '" ' . $checked . ' > ' . $header;
        echo '<input type="hidden" name="' . esc_attr($id) . '"  id="' . esc_attr($id) . '"  value="' . esc_attr($val) . '"   >';
        echo'</div>';
    }

    function create_section_for_number($value) {
        $this->create_opening_tag($value);

        $id = !is_array($value['id']) ? $value['id'] : array_values($value['id'])[0];
        if (($value['db_value'][$id] == null || $value['db_value'][$id] == '') && isset($value['std'])) {
            $val = (isset($value['std']) ? $value['std'] : "");
        } else {
            $val = $value['db_value'][$id];
        }
        $class = isset($value['class']) ? $value['class'] : "";
        $this->create_section_for_number_inner($id, $val, $class);

        $this->create_closing_tag($value);
    }

    function create_section_for_import($value) {
        $this->create_opening_tag($value);
        echo '<div class="cmcal-import-desc-text">' . esc_html__('Input your backup file below and hit import to restore your calendar theme from a backup.', 'calendar-anything') . '</div>';
        echo '<textarea type="textarea" class="cmcal-import-text" ></textarea><a type="textarea" id="cmcal-calendar-import" class="button-primary">' . esc_html__('Import', 'calendar-anything') . '</a>';
        $this->create_closing_tag($value);
    }

    function create_section_for_export($value) {
        $this->create_opening_tag($value);
        echo '<div class="cmcal-export-desc-text">' . esc_html__('Here you can copy your current calendar theme.', 'calendar-anything') . '</div>';
        echo '<a type="textarea" id="cmcal-calendar-export" class="button-primary">' . esc_html__('Export', 'calendar-anything') . '</a> <textarea type="textarea" class="cmcal-export-text" ></textarea>';
        $this->create_closing_tag($value);
    }

    private function create_section_for_number_inner($id, $val, $class) {
        echo '<input type="text" class="cmcal-text-number ' . $class . '" id="' . esc_attr($id) . '" name="' . esc_attr($id) . '" min="0"  value="' . esc_attr($val) . '">';
    }

    function create_suf_header_3($value) {
        echo '<h3 class="suf-header-3 ' . (isset($value['class']) ? $value['class'] : "") . '">' . $value['name'] . "<span class='cmcal-section-header-disabled'> (" . esc_html__('Disabled', 'calendar-anything') . ")</span>" . "</h3>";
    }

    function create_opening_tag($value) {
        $info = "";
        if (isset($value['info']) && !empty($value['info'])) {
            $info = "<span title='" . esc_attr($value['info']) . "' class='cmcal-editor-info'></span>";
        }
        $group_class = "";
        if (isset($value['grouping'])) {
            $group_class = "suf-grouping-rhs";
        }
        echo '<div class="suf-section fix cmcal-section-content ' . (isset($value['section_class']) ? $value['section_class'] : "") . '">' . "\n";
        if ($group_class != "") {
            echo "<div class='$group_class fix'>\n";
        }
        if (isset($value['name'])) {
            echo "<h4 class='cmcal-setting-header'>" . $value['name'] . $info . "</h4>\n";
        }
        if (isset($value['desc']) && !(isset($value['type']) && $value['type'] == 'checkbox')) {
            echo esc_html($value['desc']) . "<br />";
        }
        if (isset($value['note'])) {
            echo "<span class=\"note\">" . $value['note'] . "</span><br />";
        }
    }

    function create_closing_tag($value) {
        if (isset($value['grouping'])) {
            echo "</div>\n";
        }
        echo "</div>\n";
    }

    function create_subsection_opening_tag($value) {
        $class = (isset($value['subsection_header_content_class']) ? $value['subsection_header_content_class'] : "");
        $open_subsection = "open-subsection";
        if (!empty($value['name'])) {
            echo '<h3 class="cmcal-subsection-header ' . $class . ' ' . (isset($value['subsection_header_class']) ? $value['subsection_header_class'] : "") . '">' . $value['name'] . "</h3>";
            $open_subsection = "";
        }
        echo '<div class="cmcal-subsection-content ' . $class . ' ' . $open_subsection . " " . (isset($value['subsection_class']) ? $value['subsection_class'] : "") . '">' . "\n";
    }

    function create_subsection_closing_tag($value) {
        echo "</div>\n";
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    //Edit Form
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    function get_required_editor_ids($array) {
        $keys = array();
        if (array_key_exists('editor_id', $array)) {
            $keys[$array["editor_id"]] = $array["editor_id"];
        }
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $keys = array_merge($keys, $this->get_required_editor_ids($value));
            }
        }
        return $keys;
    }

    function create_form_customize_calendar($options, $calendar_id) {
        $db_settings = CMCAL()->dal->get_calendar_db_settings($calendar_id);

        echo "<form name='customize_calendar_form' method='post'  >\n";
        wp_nonce_field('update-options');

        //Set selected value from DB
        foreach ($options as $value) {
            if (isset($value["id"]) && isset($value["db_field"])) {
                if (!is_array($value['id'])) {
                    $val = isset($db_settings[$value["db_field"]][$value["id"]]) ? $db_settings[$value["db_field"]][$value["id"]] : "";
                    if (isset($value['isHtml']) && $value['isHtml'] == "true")
                        $val = stripslashes($val);
                    $value["db_value"][$value["id"]] = $val;
                } else {
                    foreach ($value['id'] as $ids) {
                        $val = isset($db_settings[$value["db_field"]][$ids]) ? $db_settings[$value["db_field"]][$ids] : "";
                        if (isset($value['isHtml']) && $value['isHtml'] == "true")
                            $val = stripslashes($val);
                        $value["db_value"][$ids] = $val;
                    }
                }
            }

            switch ($value['type']) {
                case "sub-section-3":
                    $this->create_suf_header_3($value);
                    break;
                case "subsection_opening_tag":
                    $this->create_subsection_opening_tag($value);
                    break;
                case "subsection_closing_tag":
                    $this->create_subsection_closing_tag($value);
                    break;
                case "text";
                    $this->create_section_for_text($value);
                    break;
                case "textarea":
                    $this->create_section_for_textarea($value);
                    break;
                case "multi-select":
                    $this->create_section_for_multi_select($value);
                    break;
                case "radio":
                    $this->create_section_for_radio($value);
                    break;
                case "date-picker":
                    $this->create_section_for_date_picker($value);
                    break;
                case "color-picker-with-transparent":
                    $this->create_section_for_color_picker_with_transparent($value);
                    break;
                case "select":
                    $this->create_section_for_select($value);
                    break;
                case "select-2":
                    $this->create_section_for_category_select('second section', $value);
                    break;
                case "toolbar_settings":
                    $this->create_section_for_toolbar_settings($value);
                    break;
                case "available_shortcodes":
                    $this->create_section_for_available_shortcodes($value);
                    break;
                case "fonts":
                    $this->create_section_for_fonts($value);
                    break;
                case "borders":
                    $this->create_section_for_borders($value);
                    break;
                case "margin_padding":
                    $this->create_section_for_margin_padding($value);
                    break;
                case "number":
                    $this->create_section_for_number($value);
                    break;
                case "border_spacing":
                    $this->create_section_for_border_spacing($value);
                    break;
                case "themes":
                    $this->create_section_for_themes($value);
                    break;
                case "hidden_days":
                    $this->create_section_for_hidden_days($value);
                    break;
                case "business_days":
                    $this->create_section_for_business_days($value);
                    break;
                case "import":
                    $this->create_section_for_import($value);
                    break;
                case "export":
                    $this->create_section_for_export($value);
                    break;
                case "custom_css_instructions":
                    $this->create_section_for_custom_css_instructions($value);
                    break;
            }
        }
        ?> 
        <br>
        <input name="save_customize_calendar" type="button" class="button-primary" value="<?php esc_attr_e('Save', 'calendar-anything'); ?>" onclick="submit_form(this, document.forms['customize_calendar_form'])" />
        <?php
        $show_savecalendar = apply_filters('cmcal_savecalendar', false);
        if ($show_savecalendar) {
            ?>
            <input name="save_calendar_theme" type="button" class="button-primary" value="<?php esc_attr_e('Save Calendar Theme', 'calendar-anything'); ?>" onclick="submit_form(this, document.forms['customize_calendar_form'])" />
            <?php
        }
        ?>
        <input type="hidden" name="formaction" value="default" />
        </form>
        <?php
    }

    function create_form_customize_calendar_script($options, $calendar_id) {
        $required = array();
        $is_required_for_sections = array();
        $required_editors_on_change = array();

        //Set selected value from DB
        foreach ($options as $value) {
            if (isset($value["required"]) && isset($value["id"])) {
                if (!is_array($value['id'])) {
                    $required[$value['id']] = $value["required"];
                } else {
                    foreach ($value['id'] as $ids) {
                        $required[$ids] = $value["required"];
                    }
                }
            }

            if (isset($value["required"])) {
                $required_editors_on_change = array_merge($required_editors_on_change, $this->get_required_editor_ids($value["required"]));
            }

            if (isset($value["is_required_for_sections"]) && isset($value["id"])) {
                if (!is_array($value['id'])) {
                    $is_required_for_sections[$value['id']] = $value["is_required_for_sections"];
                }
            }
        }

        $script = '
            var cmcal_required = ' . json_encode($required, true) . ';
            var cmcal_is_required_for_sections = ' . json_encode($is_required_for_sections, true) . ';
            var cmcal_required_editors_on_change = ' . json_encode($required_editors_on_change, true) . ';
            var fonts = ' . json_encode((CMCAL()->basicfonts + CMCAL()->googlefonts), true) . ';
            function submit_form(element, form) {
                form["formaction"].value = element.name;
                form.submit();
            }
   
            function refresh_cmcal_styles(refresh_calendar) {
                var data = {
                    "action": "CMCAL_get_preview_styles",
                    "calendar_id":"' . (isset($_GET["calendar_id"]) ? $_GET["calendar_id"] : "") . '",';
        foreach (CMCAL()->customizer_settings->get_calendar_custom_styles_editors()as $setting) {
            $script .= '"' . $setting["id"] . '":' . 'jQuery("#' . $setting["id"] . '").val(),';
        }

        $script .= '};
                
                jQuery.post(ajaxurl, data, function (response) {
                    var calendar_id = "' . (isset($_GET["calendar_id"]) ? $_GET["calendar_id"] : "") . '";
                    jQuery("#cmcal_custom_styles_" + calendar_id).html(response);
                    cmcal_ColorValueChanged = false;
                    cmcal_ColorValueChanged_Time = null;
                    if (refresh_calendar)
                    {
                        jQuery(".qtip").qtip("destroy", true);
                        var cmca = cmca_calendars[calendar_id];
                        cmca.destroy();
                        initialize_cmcal_calendar();
                    }
                });
            }';
        return $script;
    }

    function create_section_for_custom_css_instructions($value) {
        $this->create_opening_tag($value);
        echo '<div>You can use a syntax like this:<br><pre>{calendar_id_class} .fc-toolbar h2 {
color: green!important;
}</pre></div><br>';
        $this->create_closing_tag($value);
    }

}
