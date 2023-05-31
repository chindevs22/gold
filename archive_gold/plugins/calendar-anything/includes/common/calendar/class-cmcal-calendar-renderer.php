<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Cmcal_Calendar_Renderer {

    public function get_calendar($calendar_id, $atts) {
        $extra_class = isset($atts["css_class"]) ? $atts["css_class"] : '';
        $attributes = '';
        foreach ($atts as $key => $att) {
            if (strpos($key, 'attr-') === 0) {
                $attributes .= ' ' . substr($key, strlen('attr-')) . '="' . $att . '" ';
            }
        }
        $id = "cmcal_calendar_" . $calendar_id;
        $class = "cmcal-calendar";
        $class_container = "cmcal-calendar-" . $calendar_id;
        $output = "";
        $output .= '<div class="cmcal-calendar-container ' . $class_container . '">';
        $output .= '<div class="cmcal-calendar-filter-area "></div>';
        $output .= '<div id="' . esc_attr($id) . '" class="' . esc_attr($class) . ' ' . esc_attr($extra_class) . '"' . ' data-cmcal-id ="' . esc_attr($calendar_id) . '" ' . $attributes . '></div>';
        $output .= '</div>';
        return $output;
    }

    public function get_styles_script($calendar_id) {
        ?> 
        <style id="cmcal_custom_styles_<?php echo esc_attr($calendar_id) ?>">
            <?php echo CMCAL()->customizer->get_calendar_styles_init($calendar_id) ?> 
        </style>
        <?php
    }

    public function get_template_shortcuts_script() {
        $output = 'function cmcal_fix_template(event, template, eventDateFormat, eventTimeFormat, cmcal_vars) {';

        foreach (CMCAL()->lov->get_event_template_shortcuts() as $setting) {
            $output .= "template =  template.split('" . $setting['shortcode'] . "').join(" . $setting['event_attr'] . ");";
        }

        $output .= 'var regExp = /\[([^\]]+)\]/gmi;
                while ((match = regExp.exec(template)) != null) {
                    var event_val = event[match[1]];
                    if (event_val != undefined)
                        template = template.replace(match[0], event_val);
                }

                return template;
            }';
        return $output;
    }

    public function get_taxonomies_event_template_filter() {

        $output = 'var taxonomies_event_template_filter = [';

        $options = CMCAL()->setup_options;
        $general_options = $options['general_options'];
        $post_types_options = $options['post_types_options'];
        $post_types = $general_options["post_types"];

        $taxonomies = CMCAL()->lov->get_all_taxonomies_event_template_filter($post_types_options);
        if ($taxonomies) {
            foreach ($taxonomies as $tax) {
                $output .= '"' . $tax . '",';
            }
        }

        if (!empty(CMCAL()->calendar_custom_filters)) {
            foreach (CMCAL()->calendar_custom_filters as $custom_filter) {
                $output .= '"' . $custom_filter["id"] . '",';
            }
        }
        $output .= '];';
        return $output;
    }

}
