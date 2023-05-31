<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Cmcal_Frontend {

    public function init() {
        $calendar_register_scripts_priority = apply_filters('cmcal_frontend_scripts_priority', 1000);
        add_action('wp_enqueue_scripts', array($this, 'calendar_register_scripts'), $calendar_register_scripts_priority);
        add_shortcode('calendar_anything', array($this, 'calendar_anything_func'));
        add_filter('widget_text', 'do_shortcode');
    }

    function calendar_anything_func($atts) {
        $show_calendar = apply_filters('cmcal_show_calendar', true, $atts["id"]);

        if (!$show_calendar)
            return '';

        $term = get_term($atts["id"]);
        if (empty($term)) {
            return '';
        }

        wp_enqueue_style('cmcal-googlefonts-css-' . $atts["id"]); // Style script
        wp_enqueue_style('fullcalendar-min-css');
        wp_enqueue_style('jquery-qtip-min-css');
        wp_enqueue_style('fullcalendar-daygrid-min-css');
        wp_enqueue_style('fullcalendar-timegrid-min-css');
        wp_enqueue_style('fullcalendar-list-min-css');
        wp_enqueue_style('select2-css');
        wp_enqueue_style('jquery-ui');
        wp_enqueue_style('cmcal-calendar-fixes-css');
        
        //script
        wp_enqueue_script('moment-min-js');
        wp_enqueue_script('fullcalendar-min-js');
        wp_enqueue_script('fullcalendar_safari_polyfix');
        wp_enqueue_script('fullcalendar-language-js');
        wp_enqueue_script('jquery-qtip-min-js');
        wp_enqueue_script('codemine-calendar-js');
        wp_enqueue_script('superagent-min-js');
        wp_enqueue_script('moment-with-locales-min-js');
        wp_enqueue_script('fullcalendar-daygrid-min-js');
        wp_enqueue_script('fullcalendar-timegrid-min-js');
        wp_enqueue_script('fullcalendar-list-min-js');
        wp_enqueue_script('fullcalendar-moment-min-js');
        wp_enqueue_script('fullcalendar-interaction-js');
        wp_enqueue_script('select2-js');
        wp_enqueue_script('jquery-ui-datepicker');
        // Fix for wp_localize_script not working on block themes
        if ( ! wp_script_is( 'codemine-calendar-js', 'registered' ) ) {
            wp_register_script('codemine-calendar-js', plugins_url('assets/js/codemine-calendar.js', Codemine_Calendar_PLUGIN_FILE), array('fullcalendar-min-js'), true, true); 
        }
        wp_enqueue_script('codemine-calendar-js');

        CMCAL()->customizer->localize_calendar_script($atts["id"]);
        return CMCAL()->calendar_renderer->get_calendar($atts["id"], $atts);
    }

    public function calendar_register_scripts() {

        //googlefonts
        $calendars = CMCAL()->dal->get_calendars();
        $required_for_fullcalendar = array();
        foreach ($calendars as $key) {
            $googlefonts_url = CMCAL()->dal->get_googlefonts_urls($key);
            if (!empty($googlefonts_url)) {
                $googlefonts_style = 'cmcal-googlefonts-css-' . $key;
                wp_register_style($googlefonts_style, $googlefonts_url, array());
            }
        }
        wp_register_script('fullcalendar_safari_polyfix', 'https://cdn.polyfill.io/v2/polyfill.min.js?features=Intl.~locale.en');
        wp_register_style('fullcalendar-min-css', plugins_url('assets/fullcalendar/packages/core/main.min.css', Codemine_Calendar_PLUGIN_FILE), empty($required_for_fullcalendar) ? array() : array($required_for_fullcalendar));
        // Add styles in head using wordpress wp_add_inline_style
        foreach ($calendars as $key) {
            $calendar_styles = CMCAL()->customizer->get_calendar_styles_init($key);
            wp_add_inline_style('fullcalendar-min-css', $calendar_styles);
        }
        wp_register_style('fullcalendar-daygrid-min-css', plugins_url('assets/fullcalendar/packages/daygrid/main.min.css', Codemine_Calendar_PLUGIN_FILE), empty($required_for_fullcalendar) ? array() : array($required_for_fullcalendar));
        wp_register_style('fullcalendar-timegrid-min-css', plugins_url('assets/fullcalendar/packages/timegrid/main.min.css', Codemine_Calendar_PLUGIN_FILE), empty($required_for_fullcalendar) ? array() : array($required_for_fullcalendar));
        wp_register_style('fullcalendar-list-min-css', plugins_url('assets/fullcalendar/packages/list/main.min.css', Codemine_Calendar_PLUGIN_FILE), empty($required_for_fullcalendar) ? array() : array($required_for_fullcalendar));

        wp_register_style('cmcal-calendar-fixes-css', plugins_url('assets/css/cmcal-calendar-fixes.css', Codemine_Calendar_PLUGIN_FILE), array('fullcalendar-min-css'));
        wp_register_style('select2-css', plugins_url('assets/select2/select2.min.css', Codemine_Calendar_PLUGIN_FILE), array());
        wp_register_style('jquery-qtip-min-css', plugins_url('assets/qtip/jquery.qtip.min.css', Codemine_Calendar_PLUGIN_FILE), array()); // Javascript
        //script
        wp_register_script('superagent-min-js', plugins_url('assets/superagent/superagent.min.js', Codemine_Calendar_PLUGIN_FILE), array('jquery'));
        wp_register_script('moment-with-locales-min-js', plugins_url('assets/moment/moment-with-locales.min.js', Codemine_Calendar_PLUGIN_FILE), array('jquery'));

        wp_register_script('fullcalendar-min-js', plugins_url('assets/fullcalendar/packages/core/main.min.js', Codemine_Calendar_PLUGIN_FILE), array('jquery'));
        wp_register_script('fullcalendar-daygrid-min-js', plugins_url('assets/fullcalendar/packages/daygrid/main.min.js', Codemine_Calendar_PLUGIN_FILE), array('jquery'));
        wp_register_script('fullcalendar-timegrid-min-js', plugins_url('assets/fullcalendar/packages/timegrid/main.min.js', Codemine_Calendar_PLUGIN_FILE), array('jquery'));
        wp_register_script('fullcalendar-list-min-js', plugins_url('assets/fullcalendar/packages/list/main.min.js', Codemine_Calendar_PLUGIN_FILE), array('jquery'));
        wp_register_script('fullcalendar-moment-min-js', plugins_url('assets/fullcalendar/packages/moment/main.min.js', Codemine_Calendar_PLUGIN_FILE), array('jquery'));

        //language
        wp_register_script('fullcalendar-language-js', plugins_url('assets/fullcalendar/packages/core/locales-all.min.js', Codemine_Calendar_PLUGIN_FILE), array('fullcalendar-min-js'));
        
        //interaction for navigate to other calendar option
        wp_register_script('fullcalendar-interaction-js', plugins_url('assets/fullcalendar/packages/interaction/main.min.js', Codemine_Calendar_PLUGIN_FILE), array('fullcalendar-min-js'));

        wp_register_script('select2-js', plugins_url('assets/select2/select2.full.min.js', Codemine_Calendar_PLUGIN_FILE), array());
        wp_register_script('jquery-qtip-min-js', plugins_url('assets/qtip/jquery.qtip.min.js', Codemine_Calendar_PLUGIN_FILE), array('jquery'), true, true); // Javascript
        wp_register_script('codemine-calendar-js', plugins_url('assets/js/codemine-calendar.js', Codemine_Calendar_PLUGIN_FILE), array('fullcalendar-min-js'), true, true); // Javascript

        $output = CMCAL()->calendar_renderer->get_template_shortcuts_script();
        $output .= CMCAL()->calendar_renderer->get_taxonomies_event_template_filter();
        wp_add_inline_script('codemine-calendar-js', $output, 'before');
        wp_register_style('jquery-ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css');
    }

}
