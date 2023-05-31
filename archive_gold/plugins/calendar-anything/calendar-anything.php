<?php

/**
 * Plugin Name: Calendar Anything
 * Plugin URI: https://themeforest.net/user/codemine
 * Description: Show any existing WordPress custom post type in a calendar.
 * Author: codemine
 * Author URI: https://themeforest.net/user/codemine
 * Text Domain: calendar-anything
 * Domain Path: /languages/
 * Version: 2.33
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('Codemine_Calendar')) :

    final class Codemine_Calendar {

        public $version = '2.33';
        protected static $_instance = null;
        public $shortname = "cmcal";
        public $default_event_post_type = '';
        public $default_event_taxonomy_category = '';
        public $event_calendar = '';
        public $preview_theme_name = "";

        public static function instance() {
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        /**
         * Cloning is forbidden.
         * @since 2.1
         */
        public function __clone() {
            _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'woocommerce'), '2.1');
        }

        /**
         * Unserializing instances of this class is forbidden.
         * @since 2.1
         */
        public function __wakeup() {
            _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'woocommerce'), '2.1');
        }

        public function __construct() {
            $this->define_constants();
            $this->include_setup();
            if (!empty($this->setup_options)) {
                $this->includes();
                $this->init_hooks();
            }
        }

        /**
         * Define constant if not already set.
         *
         * @param  string $name
         * @param  string|bool $value
         */
        private function define($name, $value) {
            if (!defined($name)) {
                define($name, $value);
            }
        }

        /**
         * Define Constants.
         */
        private function define_constants() {

            $this->default_event_post_type = $this->shortname . '_event';
            $this->default_event_taxonomy_category = $this->shortname . '_event_category';
            $this->event_calendar = $this->shortname . '_calendar';
            $upload_dir = wp_upload_dir();

            $this->define('Codemine_Calendar_PLUGIN_FILE', __FILE__);
            $this->define('Codemine_Calendar_PLUGIN_BASENAME', plugin_basename(__FILE__));
            $this->define('Codemine_Calendar_PLUGIN_DIR_PATH', plugin_dir_path(__FILE__));
            $this->define('Codemine_Calendar_VERSION', $this->version);
        }

        /**
         * Include required core files used in admin and on the frontend.
         */
        public function include_setup() {
            if (is_admin()) {
                include_once(__DIR__ . '/includes/backend/cmb2/init.php');
//                include_once(__DIR__ . '/includes/backend/cmb2-taxonomy/init.php');
                include_once(__DIR__ . '/includes/backend/cmb2-conditionals/cmb2-conditionals.php');
            }
            include_once('includes/backend/setup/cmcal-setup-helpers.php');
            include_once('includes/backend/setup/class-cmcal-setup.php');
            include_once('includes/backend/setup/class-cmcal-setup-date-fields.php');
            include_once('includes/backend/setup/class-cmcal-setup-calendars.php');
            include_once('includes/backend/setup/class-cmcal-setup-post-fields.php');
            include_once('includes/backend/setup/class-cmcal-setup-taxonomies.php');
            include_once('includes/backend/setup/class-cmcal-setup-color-fields.php');
            include_once('includes/backend/setup/class-cmcal-setup-meta-fields.php');
            include_once('includes/backend/setup/class-cmcal-setup-custom-fields.php');
            $this->setup_options = $this->get_setup_options();
        }

        public function get_setup_options() {
            $setup_options = array();
            $all_options = array();
            $post_types = cmcal_setup_get_option(cmcal_setup_instance()->option_prefix . "post_type");
            if (!empty($post_types)) {

                $setup_options["post_type_page"] = 'admin.php?';
                $setup_options["post_types"] = $post_types;
                //Post Fields
                $setup_options["include_image"] = cmcal_setup_post_fields_get_option(cmcal_setup_post_fields_instance()->option_prefix . "include_image");
                $setup_options["image_size"] = cmcal_setup_post_fields_get_option(cmcal_setup_post_fields_instance()->option_prefix . "image_size");
                $setup_options["image_size_width"] = cmcal_setup_post_fields_get_option(cmcal_setup_post_fields_instance()->option_prefix . "image_size_width");
                $setup_options["image_size_height"] = cmcal_setup_post_fields_get_option(cmcal_setup_post_fields_instance()->option_prefix . "image_size_height");
                $setup_options["include_content"] = cmcal_setup_post_fields_get_option(cmcal_setup_post_fields_instance()->option_prefix . "include_content");
                $setup_options["include_post_author"] = cmcal_setup_post_fields_get_option(cmcal_setup_post_fields_instance()->option_prefix . "include_post_author");
                $setup_options["include_post_date"] = cmcal_setup_post_fields_get_option(cmcal_setup_post_fields_instance()->option_prefix . "include_post_date");
                $setup_options["include_excerpt"] = cmcal_setup_post_fields_get_option(cmcal_setup_post_fields_instance()->option_prefix . "include_excerpt");
                $setup_options["include_permalink"] = cmcal_setup_post_fields_get_option(cmcal_setup_post_fields_instance()->option_prefix . "include_permalink");

                //Color Fields
                $arr = array("background" => "background", "border" => "border", "text" => "text",);
                foreach ($arr as $val) {
                    $setup_options["enable_" . $val . "_color"] = cmcal_setup_color_fields_get_option(cmcal_setup_color_fields_instance()->option_prefix . "enable_" . $val . "_color");
                    $setup_options["enable_tax_" . $val . "_color"] = cmcal_setup_color_fields_get_option(cmcal_setup_color_fields_instance()->option_prefix . "enable_tax_" . $val . "_color");
                }

                //Custom Fields
                $setup_options["custom_fields"] = cmcal_setup_custom_fields_get_option(cmcal_setup_custom_fields_instance()->option_prefix . "custom_fields_group");

                //Rendering
                $all_options["general_options"] = $setup_options;

                $setup_options = array();
                foreach ($post_types as $post_type) {
                    $setup_options["post_type"] = $post_type;

                    //Date Fields
                    $setup_options["start_end_dates_mode"] = cmcal_setup_date_fields_get_option(cmcal_setup_date_fields_instance()->option_prefix . $post_type . "start_end_dates_mode");
                    if ($setup_options["start_end_dates_mode"] == "meta_keys") {
                        $setup_options["start_date_meta_key"] = cmcal_setup_date_fields_get_option(cmcal_setup_date_fields_instance()->option_prefix . $post_type . "start_date_meta_key");
                        $setup_options["start_time_meta_key"] = cmcal_setup_date_fields_get_option(cmcal_setup_date_fields_instance()->option_prefix . $post_type . "start_time_meta_key");
                        $setup_options["end_date_meta_key"] = cmcal_setup_date_fields_get_option(cmcal_setup_date_fields_instance()->option_prefix . $post_type . "end_date_meta_key");
                        $setup_options["end_time_meta_key"] = cmcal_setup_date_fields_get_option(cmcal_setup_date_fields_instance()->option_prefix . $post_type . "end_time_meta_key");
                    }
                    $setup_options["all_day_enabled"] = cmcal_setup_date_fields_get_option(cmcal_setup_date_fields_instance()->option_prefix . $post_type . "all_day_enabled");
                    if ($setup_options["all_day_enabled"] == "meta_key") {
                        $setup_options["all_day_meta_key"] = cmcal_setup_date_fields_get_option(cmcal_setup_date_fields_instance()->option_prefix . $post_type . "all_day_meta_key");
                        $setup_options["all_day_meta_key_value"] = cmcal_setup_date_fields_get_option(cmcal_setup_date_fields_instance()->option_prefix . $post_type . "all_day_meta_key_value");
                    }
                    $setup_options["repetition_enabled"] = cmcal_setup_date_fields_get_option(cmcal_setup_date_fields_instance()->option_prefix . $post_type . "repetition_enabled");

                    //Taxonomies
                    $post_type_taxonomy = cmcal_setup_taxonomies_get_option(cmcal_setup_taxonomies_instance()->option_prefix . $post_type . "taxonomy");
                    $setup_options["post_type_taxonomy_is_set"] = $post_type_taxonomy && !empty($post_type_taxonomy);
                    $setup_options["post_type_taxonomy"] = $post_type_taxonomy;
                    $taxonomies_event_template_filter = cmcal_setup_taxonomies_get_option(cmcal_setup_taxonomies_instance()->option_prefix . $post_type . "taxonomies_event_template_filter");
                    $setup_options["taxonomies_event_template_filter"] = $taxonomies_event_template_filter;

                    //Meta Fields
                    $meta_keys_event_template = cmcal_setup_meta_fields_get_option(cmcal_setup_meta_fields_instance()->option_prefix . $post_type . "meta_keys_event_template");
                    $setup_options["meta_keys_event_template"] = $meta_keys_event_template;

                    $all_options["post_types_options"][$post_type] = $setup_options;
                }
            }
            return $all_options;
        }

        public function get_calendars_setup_options() {
            $all_options = array();
            //Rendering
            $terms = get_terms('cmcal_calendar', array(
                'hide_empty' => false,
                'orderby' => 'id',
                'order' => 'ASC',
            ));

            if (!empty($terms)) {
                foreach ($terms as $term) {
                    $term_id = $term->term_id;
                    $id_prefix = cmcal_setup_calendars_instance()->option_prefix . $term_id . "_";
                    $calendar_settings = cmcal_setup_calendars_get_option($id_prefix . "calendar_settings");
                    if (!empty($calendar_settings))
                        $all_options[$term_id] = $calendar_settings[0];
                }
            }
            return $all_options;
        }

        /**
         * Include required core files used in admin and on the frontend.
         */
        public function includes() {

            include_once( 'includes/common/class-cmcal-dal.php' );
            include_once( 'includes/common/class-cmcal-lov.php' );
            include_once( 'includes/common/class-cmcal-ajax.php');
            include_once( 'includes/common/calendar/class-cmcal-customizer.php');
            include_once( 'includes/common/calendar/class-cmcal-customizer-settings.php');
            include_once( 'includes/common/calendar/class-cmcal-calendar-renderer.php');
            //googlefonts
            $this->googlefonts = include_once('includes/backend/fonts/googlefonts.php');
            $this->basicfonts = include_once('includes/backend/fonts/basicfonts.php');

            include_once( 'includes/common/calendar/class-cmcal-utils.php');
            include_once( 'includes/common/calendar/class-cmcal-event.php');
            include_once( 'includes/common/class-cmcal-init.php');
            include_once( 'includes/common/class-cmcal-ical.php');
            
            if (is_admin()) {
                include_once('includes/backend/customize-calendar/class-cmcal-customize-calendar.php');
                include_once('includes/backend/class-cmcal-renderers.php');
                $this->renderers = new Cmcal_Renderers();
                include_once('includes/backend/events/class-cmcal-events.php');
            } else {
                include_once('includes/frontend/class-cmcal-frontend.php');
            }

            $this->dal = new Cmcal_Dal();
            $this->lov = new Cmcal_Lov();
            $this->ajax = new Cmcal_Ajax();
            $this->utils = new Cmcal_Utils();
            $this->customizer = new Cmcal_Customizer();
            $this->customizer_settings = new Cmcal_Customizer_Settings();
            $this->calendar_renderer = new Cmcal_Calendar_Renderer();
            new Cmcal_Init();
            $this->ical = new Cmcal_Ical();
            $this->isSiteMultilingual = function_exists('icl_object_id');
        }

        /**
         * Hook into actions and filters.
         */
        private function init_hooks() {
            add_action('init', array($this, 'init'), 10);
        }

        /**
         * Init Plugin Backend/Frontend
         */
        public function init() {
            if (is_admin()) {
                //Init Backend
                $backend_customize_calendar = new Cmcal_Customize_Calendar();
                $backend_customize_calendar->init();
                $backend_events = new Cmcal_Events();
                $backend_events->init();
                $cmcal_activate_cmb2_tax = apply_filters('cmcal_activate_cmb2_tax', true);
                if ($cmcal_activate_cmb2_tax) {
                    if (!class_exists('CMB2_Taxonomy', false)) {
                        include_once(__DIR__ . '/includes/backend/cmb2-taxonomy/init.php');
                    }
                }
                load_plugin_textdomain('calendar-anything', false, dirname(plugin_basename(__FILE__)) . '/languages/');
            } else {
                //Init Frontend
                $frontend = new Cmcal_Frontend();
                $frontend->init();
            }

            $this->calendar_setup_options = $this->get_calendars_setup_options();
            $custom_filters = array();
            $this->calendar_custom_filters = apply_filters('cmcal_custom_filters', $custom_filters);
        }

    }

    endif;

function CMCAL() {
    return Codemine_Calendar::instance();
}

// Global for backwards compatibility.
$GLOBALS['Codemine_Calendar'] = CMCAL();
