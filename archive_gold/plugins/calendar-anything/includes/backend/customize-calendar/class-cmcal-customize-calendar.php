<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Cmcal_Customize_Calendar {

    public function init() {
        add_action('admin_menu', array($this, 'codemine_calendar_admin_menu'));

        // Enqueue scripts
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('wp_ajax_CMCAL_export', array($this, 'CMCAL_export'));
        add_action('wp_ajax_CMCAL_import', array($this, 'CMCAL_import'));
    }

    public function CMCAL_export() {
        $calendar_id = $_POST["calendar_id"];
        $db_fields = CMCAL()->customizer_settings->get_calendar_custom_settings_db_fields();
        $arr = [];
        foreach ($db_fields as $value) {
            $arr [$value] = get_option($value . "_" . $calendar_id);
        }
        $res = json_encode($arr);

        echo json_encode(array(
            'success' => true,
            'res' => $res,
                ), true);
        wp_die();
    }

    public function CMCAL_import() {
        $calendar_id = $_POST["calendar_id"];
        $import_data1 = wp_unslash($_POST["import_data"]);
        ;
        $import_data = json_decode($import_data1, true);
        foreach ($import_data as $key => $value)
            update_option($key . "_" . $calendar_id, $value);

        echo json_encode(array(
            'success' => true,
                ), true);
        wp_die();
    }

    function codemine_calendar_admin_menu() {
        CMCAL()->preview_theme_name = "";
        $menu_slug = 'customize-calendar';

        if (isset($_GET['page']) && $_GET['page'] == $menu_slug) {
            if (empty($this->get_calendar_id())) {
                wp_redirect(admin_url('/edit-tags.php?taxonomy=cmcal_calendar&post_type=event'), 301);
            }
            if (isset($_REQUEST['formaction']) && 'save_customize_calendar' == $_REQUEST['formaction']) {
                $this->save_calendar_settings();
            }
            if (isset($_REQUEST['formaction']) && 'save_calendar_theme' == $_REQUEST['formaction']) {
                $this->save_calendar_theme();
            }
            if (isset($_REQUEST['formaction']) && strpos($_REQUEST['formaction'], 'load_theme_') !== false) {
                CMCAL()->preview_theme_name = str_replace("load_theme_", "", $_REQUEST['formaction']);
            }
        }

        $parent_slug = CMCAL()->setup_options["general_options"]["post_type_page"];
        $capability = apply_filters('cmcal_capability', 'manage_options');
        add_submenu_page($parent_slug, esc_html__('Customize Calendar', 'calendar-anything'), esc_html__('Customize Calendar', 'calendar-anything'), $capability, $menu_slug, array($this, 'customize_calendar_html_page'));
        remove_submenu_page($parent_slug, $menu_slug);
    }

    function get_calendar_editors_settings() {
        $settings = CMCAL()->customizer_settings->get_calendar_settings();
        $db_fields = CMCAL()->customizer_settings->get_calendar_custom_settings_db_fields();
        $arr = [];
        foreach ($db_fields as $value) {
            $arr [$value] = array();
        }
        foreach ($settings as $value) {
            if (isset($value['id'])) {
                if ($this->check_if_store_in_db($value)) {
                    if (!is_array($value['id'])) {
                        $arr [$value['db_field']][$value['id']] = isset($_REQUEST[$value['id']]) ? $_REQUEST[$value['id']] : "";
                    } else {
                        foreach ($value['id'] as $id) {
                            if (isset($_REQUEST[$id]))
                                $arr [$value['db_field']][$id] = isset($_REQUEST[$id]) ? $_REQUEST[$id] : "";
                        }
                    }
                }
            }
        }
        return $arr;
    }

    function check_if_store_in_db($value) {
        $store_in_db = true;
        //Check if requires other setting
        if (isset($value['required']) && is_array($value['required'])) {
            $store_in_db = false;

            $expression_inner = [];
            $operator_inner = ' || ';
            if (isset($value['required']['operator'])) {
                if ($value['required']['operator'] == "AND")
                    $operator_inner = ' && ';
                foreach ($value['required'] as $key => $value) {
                    if (is_array($value)) {
                        $expression_inner[] = $this->get_required_expression($value);
                    }
                }
            } else {
                $expression_inner[] = $this->get_required_expression($value['required']);
            }
            if (eval("return (" . join($operator_inner, $expression_inner) . ");"))
                $store_in_db = true;
        }
        return $store_in_db;
    }

    function get_required_expression($item) {
        $expression = [];
        foreach ($item as $item_inner) {
            $check_el_val = isset($_REQUEST[$item_inner["editor_id"]]) ? $_REQUEST[$item_inner["editor_id"]] : "";
            $op = $item_inner[0] == "=" ? "==" : $item_inner[0];
            $compare_val = $item_inner[1];
            $expression[] = "(" . "'" . $check_el_val . "'" . $op . "'" . $compare_val . "'" . ")";
        }
        return "(" . join(" || ", $expression) . ")";
    }

    function save_calendar_settings() {
        $arr = $this->get_calendar_editors_settings();
        foreach ($arr as $key => $value)
            update_option($key . "_" . $this->get_calendar_id(), serialize($value));
    }

    function save_calendar_theme() {
        $arr = $this->get_calendar_editors_settings();

        $directory = Codemine_Calendar_PLUGIN_DIR_PATH . "includes/backend/calendar-themes";
        $themes = array_diff(scandir($directory), array('..', '.'));
        $nexrThemeNumber = 1;
        if (!empty($themes)) {
            $nexrThemeNumber = (max(str_replace(".php", "", str_replace("theme_", "", $themes))) + 1);
        }
        $filename = $directory . "/theme_" . $nexrThemeNumber . "/theme_settings.php";

        $dirname = dirname($filename);
        if (!is_dir($dirname)) {
            mkdir($dirname, 0755, true);
        }
        $myfile = fopen($filename, "w") or die("Unable to open file!");
        fwrite($myfile, "<?php");
        $txt = " return '" . serialize($arr) . "';";
        fwrite($myfile, $txt);
        fclose($myfile);
    }

    function get_calendar_id() {
        return isset($_GET["calendar_id"]) ? $_GET["calendar_id"] : "";
    }

    function customize_calendar_html_page() {
        $calendar_id = $this->get_calendar_id();
        $calendar = get_term_by('id', $calendar_id, 'cmcal_calendar');
        if (empty($calendar)) {
            exit;
        }
        ?> 
        <div class="cmcal-customize-calendar-holder">
            <div class='cmcal-customize-calendar-title'>
                <h2><span><?php esc_html_e('Calendar', 'calendar-anything'); ?></span> <?php echo esc_html($calendar->name); ?></h2>   
                <pre><?php echo '[calendar_anything id="' . esc_attr($calendar_id) . '"]'; ?></pre>
            </div>
            <table class="cmcal-customize-calendar-container">
                <tr valign="top">
                    <td class="cmcal-settings" scope="row">
                        <h2 class="cmcal-settings-header"><?php esc_html_e('Settings', 'calendar-anything'); ?></h2>
                        <div class="cmcal-settings-back-button"></div>
                        <?php CMCAL()->renderers->create_form_customize_calendar(CMCAL()->customizer_settings->get_calendar_settings(), $this->get_calendar_id()); ?> 
                    </td>
                    <td class="cmcal-preview">
                        <h2 class="cmcal-preview-header"><?php esc_html_e('Preview', 'calendar-anything'); ?></h2>
                        <div class="cmcal-preview-container">
                            <?php echo CMCAL()->calendar_renderer->get_calendar($this->get_calendar_id(), array()); ?> 
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }

    public function admin_enqueue_scripts() {
        if (isset($_GET['page']) && $_GET['page'] == "customize-calendar") {

            //style
            $googlefonts_url = CMCAL()->dal->get_googlefonts_urls($this->get_calendar_id());
            $required_for_fullcalendar = "";
            if (!empty($googlefonts_url)) {
                wp_enqueue_style('cmcal-googlefonts-css', $googlefonts_url, array()); // Style script
//                $required_for_fullcalendar = 'cmcal-googlefonts-css';
            }

            wp_enqueue_style('fullcalendar-min-css', plugins_url('assets/fullcalendar/packages/core/main.min.css', Codemine_Calendar_PLUGIN_FILE), empty($required_for_fullcalendar) ? array() : array($required_for_fullcalendar));
            wp_enqueue_style('fullcalendar-daygrid-min-css', plugins_url('assets/fullcalendar/packages/daygrid/main.min.css', Codemine_Calendar_PLUGIN_FILE), empty($required_for_fullcalendar) ? array() : array($required_for_fullcalendar));
            wp_enqueue_style('fullcalendar-timegrid-min-css', plugins_url('assets/fullcalendar/packages/timegrid/main.min.css', Codemine_Calendar_PLUGIN_FILE), empty($required_for_fullcalendar) ? array() : array($required_for_fullcalendar));
            wp_enqueue_style('fullcalendar-list-min-css', plugins_url('assets/fullcalendar/packages/list/main.min.css', Codemine_Calendar_PLUGIN_FILE), empty($required_for_fullcalendar) ? array() : array($required_for_fullcalendar));

            wp_enqueue_style('cmcal-calendar-fixes-css', plugins_url('assets/css/cmcal-calendar-fixes.css', Codemine_Calendar_PLUGIN_FILE), array('fullcalendar-min-css'));
            wp_enqueue_style('cmcal-editors-css', plugins_url('assets/css/cmcal-editors.css', Codemine_Calendar_PLUGIN_FILE), array('fullcalendar-min-css'));
            wp_enqueue_style('select2-css', plugins_url('assets/select2/select2.min.css', Codemine_Calendar_PLUGIN_FILE), array());
            wp_enqueue_style('jquery-qtip-min-css', plugins_url('assets/qtip/jquery.qtip.min.css', Codemine_Calendar_PLUGIN_FILE), array());
            //script
            wp_enqueue_script('jquery.blockUI', plugins_url('assets/js/jquery.blockUI.js', Codemine_Calendar_PLUGIN_FILE), array('jquery'));
            wp_enqueue_script('superagent-min-js', plugins_url('assets/superagent/superagent.min.js', Codemine_Calendar_PLUGIN_FILE), array('jquery'));
            wp_enqueue_script('moment-with-locales-min-js', plugins_url('assets/moment/moment-with-locales.min.js', Codemine_Calendar_PLUGIN_FILE), array('jquery'));
            wp_enqueue_script('fullcalendar-min-js', plugins_url('assets/fullcalendar/packages/core/main.min.js', Codemine_Calendar_PLUGIN_FILE), array('jquery'));
            wp_enqueue_script('fullcalendar-daygrid-min-js', plugins_url('assets/fullcalendar/packages/daygrid/main.min.js', Codemine_Calendar_PLUGIN_FILE), array('jquery'));
            wp_enqueue_script('fullcalendar-timegrid-min-js', plugins_url('assets/fullcalendar/packages/timegrid/main.min.js', Codemine_Calendar_PLUGIN_FILE), array('jquery'));
            wp_enqueue_script('fullcalendar-list-min-js', plugins_url('assets/fullcalendar/packages/list/main.min.js', Codemine_Calendar_PLUGIN_FILE), array('jquery'));
            wp_enqueue_script('fullcalendar-moment-min-js', plugins_url('assets/fullcalendar/packages/moment/main.min.js', Codemine_Calendar_PLUGIN_FILE), array('jquery'));

            //language
            wp_enqueue_script('fullcalendar-language-js', plugins_url('assets/fullcalendar/packages/core/locales-all.min.js', Codemine_Calendar_PLUGIN_FILE), array('fullcalendar-min-js'));
            wp_enqueue_script('jquery-qtip-min-js', plugins_url('assets/qtip/jquery.qtip.min.js', Codemine_Calendar_PLUGIN_FILE), array('jquery'));
            wp_enqueue_script('select2-js', plugins_url('assets/select2/select2.full.min.js', Codemine_Calendar_PLUGIN_FILE), array());
            wp_enqueue_script('codemine-calendar-js', plugins_url('assets/js/codemine-calendar.js', Codemine_Calendar_PLUGIN_FILE), array('fullcalendar-min-js', 'select2-js', 'jquery-ui-sortable'));
            wp_enqueue_script('codemine-calendar-renderers-js', plugins_url('assets/js/codemine-calendar-renderers.js', Codemine_Calendar_PLUGIN_FILE), array('fullcalendar-min-js', 'select2-js', 'codemine-calendar-js'));
            $renderers_script = CMCAL()->renderers->create_form_customize_calendar_script(CMCAL()->customizer_settings->get_calendar_settings(), $this->get_calendar_id());
            wp_add_inline_script('codemine-calendar-renderers-js', $renderers_script, 'before');
            //Include only for backend
            wp_enqueue_script('googleapis-webfont-js', 'https://ajax.googleapis.com/ajax/libs/webfont/1.5.18/webfont.js', array('jquery'));

            CMCAL()->customizer->localize_calendar_script($this->get_calendar_id());

            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker');

            $output = CMCAL()->calendar_renderer->get_template_shortcuts_script();
            $output .= CMCAL()->calendar_renderer->get_taxonomies_event_template_filter();
            wp_add_inline_script('codemine-calendar-js', $output, 'before');
            CMCAL()->calendar_renderer->get_styles_script($this->get_calendar_id());
            wp_enqueue_script('jquery-ui-datepicker');
            wp_register_style('jquery-ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css');
            wp_enqueue_style('jquery-ui');
        }
    }

}
