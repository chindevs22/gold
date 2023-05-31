<?php

//--------------------------------------------------------------------------------------------------
// Utilities for our event-fetching scripts.
//
// Requires PHP 5.2.0 or higher.
//--------------------------------------------------------------------------------------------------
// PHP will fatal error if we attempt to use the DateTime class without this being set.
//date_default_timezone_set('UTC');



if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Cmcal_Utils {

// Date Utilities
//----------------------------------------------------------------------------------------------
// Parses a string into a DateTime object, optionally forced into the given timezone.
    function parseDateTime($string, $timezone = null) {
        $date = new DateTime(
                $string, $timezone ? $timezone : new DateTimeZone('UTC')
                // Used only when the string is ambiguous.
                // Ignored if string has a timezone offset in it.
        );
        if ($timezone) {
            // If our timezone was ignored above, force it.
            $date->setTimezone($timezone);
        }
        return $date;
    }

// Takes the year/month/date values of the given DateTime and converts them to a new DateTime,
// but in UTC.
    function stripTime($datetime) {
        return new DateTime($datetime->format('Y-m-d'));
    }

    function get_events_date_range($calendar_id) {
        $events_min_date = null;
        $events_min_date_number = $this->get_calendar_general_option($calendar_id, "events_min_date_number");
        $events_min_date_type = $this->get_calendar_general_option($calendar_id, "events_min_date_type");
        if ((!empty($events_min_date_number) || $events_min_date_number == '0') && !empty($events_min_date_type)) {
            $events_min_date = $this->get_date_min($events_min_date_number, $events_min_date_type);
        }
        $events_max_date = null;
        $events_max_date_number = $this->get_calendar_general_option($calendar_id, "events_max_date_number");
        $events_max_date_type = $this->get_calendar_general_option($calendar_id, "events_max_date_type");
        if ((!empty($events_max_date_number) || $events_max_date_number == '0') && !empty($events_max_date_type)) {
            $events_max_date = $this->get_date_max($events_max_date_number, $events_max_date_type);
        }
        $output = array(
            'min_date' => $events_min_date,
            'max_date' => $events_max_date,
        );
        $output = apply_filters('cmcal_events_date_range', $output, $calendar_id);
        return $output;
    }

    function get_date_interval($n, $type) {
        if ($type == 'days') {
            $output = 'P' . $n . 'D';
        } else if ($type == 'weeks') {
            $n = $n * 7;
            $output = 'P' . $n . 'D';
        } else if ($type == 'months') {
            $output = 'P' . $n . 'M';
        } else if ($type == 'years') {
            $output = 'P' . $n . 'Y';
        }

        return $output;
    }

    function get_date_min($n, $type) {
        $date_now = new DateTime();
        $date_now->sub(new DateInterval($this->get_date_interval($n, $type)));
        if ($type == 'days') {
            
        } else if ($type == 'weeks') {
            return date('Y-m-d', strtotime('Monday this week ' . $date_now->format('Y-m-d')));
        } else if ($type == 'months') {
            return date('Y-m-d', strtotime('first day of this month ' . $date_now->format('Y-m-d')));
        } else if ($type == 'years') {
            return date('Y-m-d', strtotime('first day of january ' . $date_now->format('Y-m-d')));
        }

        return $date_now->format('Y-m-d');
    }

    function get_date_max($n, $type) {
        $date_now = new DateTime();
        $date_now->add(new DateInterval($this->get_date_interval($n, $type)));
        if ($type == 'days') {
            
        } else if ($type == 'weeks') {
            return date('Y-m-d', strtotime('Sunday this week ' . $date_now->format('Y-m-d')));
        } else if ($type == 'months') {
            return date('Y-m-d', strtotime('last day of this month ' . $date_now->format('Y-m-d')));
        } else if ($type == 'years') {
            return date('Y-m-d', strtotime('last day of december ' . $date_now->format('Y-m-d')));
        }

        return $date_now->format('Y-m-d');
    }

    function get_calendar_general_option($calendar_id, $option_name) {
        $option = '';
        if (isset(CMCAL()->calendar_setup_options[$calendar_id][$option_name]) && (!empty(CMCAL()->calendar_setup_options[$calendar_id][$option_name]) || CMCAL()->calendar_setup_options[$calendar_id][$option_name] == '0')) {
            $option = CMCAL()->calendar_setup_options[$calendar_id][$option_name];
        }
        return $option;
    }

    function calendar_setup_post_type_is_set() {
        $post_type = cmcal_setup_get_option(cmcal_setup_instance()->option_prefix . "post_type");
        return !empty($post_type);
    }

    function cmcal_cmb2_metabox_form($metabox_id, $key) {
        if ($this->calendar_setup_post_type_is_set()) {
            cmb2_metabox_form($metabox_id, $key);
        }
        else {
            echo "PLEASE";
        }
    }

}
