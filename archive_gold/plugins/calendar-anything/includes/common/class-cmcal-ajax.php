<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Cmcal_Ajax {

    public function __construct() {

        add_action('wp_ajax_CMCAL_get_events', array($this, 'CMCAL_get_events'));
        add_action('wp_ajax_nopriv_CMCAL_get_events', array($this, 'CMCAL_get_events'));

        add_action('wp_ajax_CMCAL_get_preview_styles', array($this, 'CMCAL_get_preview_styles'));
        add_action('wp_ajax_nopriv_CMCAL_get_preview_styles', array($this, 'CMCAL_get_preview_styles'));

        add_action('wp_ajax_CMCAL_get_tax_lov', array($this, 'CMCAL_get_tax_lov'));
        add_action('wp_ajax_nopriv_CMCAL_get_tax_lov', array($this, 'CMCAL_get_tax_lov'));

        add_action('wp_ajax_CMCAL_edit_calendar', array($this, 'CMCAL_edit_calendar'));
        add_action('wp_ajax_nopriv_CMCAL_edit_calendar', array($this, 'CMCAL_edit_calendar'));
    }

    public function CMCAL_get_events() {
        $calendar_id = (isset($_GET['calendar_id']) ? $_GET['calendar_id'] : "");
        if (!isset($_GET['start']) || !isset($_GET['end'])) {
            die("Please provide a date range.");
        }

        // Parse the start/end parameters.
        // These are assumed to be ISO8601 strings with no time nor timezone, like "2013-12-29".
        // Since no timezone will be present, they will parsed as UTC.

        $range_start = CMCAL()->utils->parseDateTime($_GET['start']);
        $range_end = CMCAL()->utils->parseDateTime($_GET['end']);

        // Parse the timezone parameter if it is present.
        $timezone = null;
        if (isset($_GET['timezone'])) {
            $timezone = new DateTimeZone($_GET['timezone']);
        }
        $output_arrays = CMCAL()->dal->get_events($range_start, $range_end, $timezone, $calendar_id);

        // Send JSON to the client.
        echo json_encode($output_arrays);
        wp_die();
    }

    public function CMCAL_get_preview_styles() {
        $array = array();
        foreach (CMCAL()->customizer_settings->get_calendar_custom_styles_editors()as $setting) {
            $array[$setting["id"]] = $_POST[$setting["id"]];
        }
        $calendar_id = isset($_POST["calendar_id"]) ? $_POST["calendar_id"] : "";
        echo CMCAL()->customizer->get_calendar_styles($array, $calendar_id);
        wp_die();
    }

    public function CMCAL_get_tax_lov() {
        $tax_name = (isset($_GET['tax_name']) ? $_GET['tax_name'] : "");

        if (!isset($_GET['tax_name'])) {
            wp_die();
        }
        if (!empty(CMCAL()->calendar_custom_filters)) {
            foreach (CMCAL()->calendar_custom_filters as $custom_filter) {
                if ($custom_filter["id"] == $tax_name) {
                    $custom_filter_values = array();
                    foreach ($custom_filter["values"] as $key => $value) {
                        $custom_filter_values[] = array("term_id" => $key, "name" => $value,);
                    }
                    echo json_encode($custom_filter_values);
                    wp_die();
                }
            }
        }
        $terms = get_terms($tax_name);
        $tax_name = $_GET['tax_name'];
        $arr[] = array(
            'completeName' => 'completeName1',
            'slug' => 'slug1',
            'id' => 'id1'
        );
        $arr[] = array(
            'completeName' => 'completeName2',
            'slug' => 'slug2',
            'id' => 'id2'
        );

        // Send JSON to the client.
        echo json_encode($terms);
        wp_die();
    }

    public function CMCAL_edit_calendar() {
        $return = null;
        $taxonomy = "cmcal_calendar";

        $calendar_action = $_POST["calendar_action"];

        $calendar_id = $_POST["calendar_id"];
        $calendar_name = $_POST["calendar_name"];

        switch ($calendar_action) {
            case "insert":
                $calendar_name = $_POST["calendar_name"];
                $return = wp_insert_term($calendar_name, $taxonomy, array('slug' => '',));
                break;
            case "update";
                $calendar_id = $_POST["calendar_id"];
                $calendar_name = $_POST["calendar_name"];
                $return = wp_update_term($calendar_id, $taxonomy, array('name' => $calendar_name, 'slug' => ''));
                break;
            case "delete";
                $calendar_id = $_POST["calendar_id"];
                $return = wp_delete_term($calendar_id, $taxonomy);
                break;
        }

        if (!is_wp_error($return)) {
            echo json_encode(array(
                'success' => true,
                'calendar_name' => $calendar_name,
            ));
        } else {
            wp_send_json_error();
        }
        wp_die();
    }

}
