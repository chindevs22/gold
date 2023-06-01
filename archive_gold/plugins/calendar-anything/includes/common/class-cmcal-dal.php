<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Cmcal_Dal {
    /*
      ================================================================
      Get events for calendar
      ================================================================
     */

    private $temp_array;

    public function get_events($range_start, $range_end, $timezone, $calendar_id, $all_events = false) {
        $events_date_range = CMCAL()->utils->get_events_date_range($calendar_id);
        if (!empty($events_date_range['min_date']) || !empty($events_date_range['max_date'])) {

            if (!empty($events_date_range['min_date'])) {
                $min_date = new Datetime($events_date_range['min_date']);
                if ($all_events || ($min_date > $range_start)) {
                    $range_start = $min_date;
                }
            }
            if (!empty($events_date_range['max_date'])) {
                $max_date = new Datetime($events_date_range['max_date']);
                if ($all_events || ($max_date < $range_end)) {
                    $range_end = $max_date;
                }
            }
            $all_events = false;
        }
        //FOR TRANSIENT


        $transient_date_sub_name = "all_events";

        if (!$all_events) {
            $transient_date_sub_name_range_start = empty($range_start) ? "_" : ($range_start->format('Ymd'));
            $transient_date_sub_name_range_end = empty($range_end) ? "_" : ($range_end->format('Ymd'));
            $transient_date_sub_name = $transient_date_sub_name_range_start . '_' . $transient_date_sub_name_range_end;
        }

        $transient_name = '_cmcal_trans_calendar_events_' . $calendar_id . '_' . $transient_date_sub_name;
        $event_cashing_enabled = !isset(CMCAL()->calendar_setup_options[$calendar_id]["event_cashing"]) ? false : ( CMCAL()->calendar_setup_options[$calendar_id]["event_cashing"] == "yes");
        if (!$event_cashing_enabled || ($event_cashing_enabled && (false === ( $output_arrays = get_transient($transient_name) )))) {
            $input_arrays = $this->json_events_from_db_general($range_start, $range_end, $timezone, $calendar_id, $all_events);

            // Accumulate an output array of event data arrays.
            $output_arrays = array();
            foreach ($input_arrays as $array) {
                // Convert the input array into a useful Event object
                $event = new Cmcal_Event($array, $timezone);
                $event = apply_filters('cmcal_calendar_event', $event, $calendar_id, $range_start, $range_end); // version 1.27
                $output_arrays[] = $event->toArray();
            }

            //FOR TRANSIENT
            if ($event_cashing_enabled)
                set_transient($transient_name, $output_arrays, MONTH_IN_SECONDS);
        }
        $output_arrays = apply_filters('cmcal_calendar_events', $output_arrays, $calendar_id, $range_start, $range_end);
        return $output_arrays;
    }

    private function get_event_posts_ids($calendar_id) {
        $post_types = CMCAL()->setup_options["general_options"]["post_types"];
        $post_types_all = array();
        $post_types_selected = array();
        foreach ($post_types as $post_type) {
            $posts_included = !isset(CMCAL()->calendar_setup_options[$calendar_id][$post_type . 'posts_included']) ? "all" : CMCAL()->calendar_setup_options[$calendar_id][$post_type . 'posts_included'];
            if ($posts_included == 'selected') {
                $post_types_selected[$post_type] = $post_type;
            } else if ($posts_included == 'none') {
                
            } else {
                $post_types_all[$post_type] = $post_type;
            }
        }
        $posts = array();
        if (!empty($post_types_selected)) {
            $query_args = array(
                'post_type' => $post_types_selected,
                'post_status' => array('publish', 'future'),
                'posts_per_page' => -1,
                'fields' => "ids",
                'tax_query' => array(
                    array(
                        'taxonomy' => 'cmcal_calendar',
                        'field' => 'term_id',
                        'terms' => $calendar_id,
                    ),
                ),
            );
            $query_args = apply_filters('cmcal_calendar_posts_query', $query_args, $calendar_id);
            $query = new WP_Query($query_args);
            $posts = array_merge($posts, $query->posts);
        }
        if (!empty($post_types_all)) {
            $query_args = array(
                'post_type' => $post_types_all,
                'post_status' => array('publish', 'future'),
                'posts_per_page' => -1,
                'fields' => "ids",
            );
            $query_args = apply_filters('cmcal_calendar_posts_query', $query_args, $calendar_id);
            $query = new WP_Query($query_args);
            $posts = array_merge($posts, $query->posts);
        }
        return $posts;
    }

    private function get_event_all_day($id, $post_type, $post_type_options, $general_options) {
        $temp_array = array();
        switch ($post_type_options["all_day_enabled"]) {
            case "codemine_field":
                $temp_array['allDay'] = $this->get_cmb2_post_meta_boolean($id, 'codemine_event_' . $post_type . '_all_day');
                break;
            case "all_events_all_day":
                $temp_array['allDay'] = true;
                break;
            case "meta_key":
                $all_day_meta_key_value = $post_type_options["all_day_meta_key_value"];
                $val = get_post_meta($id, $post_type_options["all_day_meta_key"], true);
                $temp_array['allDay'] = $all_day_meta_key_value == $val;
                break;
        }

        return $temp_array;
    }

    private function get_event_custom_styles($id, $post_type, $post_type_options, $general_options, $calendar_id) {
        $temp_array = array();
        $post_type_taxonomy = $post_type_options["post_type_taxonomy"];
        $post_type_taxonomy_is_set = $post_type_options["post_type_taxonomy_is_set"];

        $event_general_action_on_click = isset(CMCAL()->calendar_setup_options[$calendar_id]["event_general_action_on_click"]) ? CMCAL()->calendar_setup_options[$calendar_id]["event_general_action_on_click"] : "goto_permalink";

        if ($event_general_action_on_click == "custom") {
            $action_on_click = get_post_meta($id, 'codemine_event_' . $post_type . '_action_on_click', true);
            $url = null;
            if ($action_on_click == "goto_permalink") {
                $url = get_the_permalink($id);
            }
            if ($action_on_click == "goto_custom_url") {
                $url = get_post_meta($id, 'codemine_event_' . $post_type . '_custom_url', true);
            }
            if ($url != null) {
                $temp_array['url'] = $url;
                $temp_array['url_new_window'] = get_post_meta($id, 'codemine_event_' . $post_type . '_url_new_window', true);
            }
        } else {
            $event_general_url_new_window = isset(CMCAL()->calendar_setup_options[$calendar_id]["event_general_url_new_window"]) ? CMCAL()->calendar_setup_options[$calendar_id]["event_general_url_new_window"] : "false";
            $url = get_the_permalink($id);
            $temp_array['url'] = $url;
            $temp_array['url_new_window'] = empty($event_general_url_new_window) ? false : $event_general_url_new_window;
        }
        if ($post_type_taxonomy_is_set) {
            $terms = get_the_terms($id, $post_type_taxonomy);
            if (!empty($terms)) {
                $term_id = $terms[0]->term_id;
                if ($general_options["enable_tax_background_color"] == "yes") {
                    $term_backgroundColor = get_term_meta($term_id, 'codemine_event_taxonomy_backgroundColor', true);
                    if ($term_backgroundColor != null) {
                        $temp_array['backgroundColor'] = $term_backgroundColor;
                    }
                }
                if ($general_options["enable_tax_border_color"] == "yes") {
                    $term_borderColor = get_term_meta($term_id, 'codemine_event_taxonomy_borderColor', true);
                    if ($term_borderColor != null) {
                        $temp_array['borderColor'] = $term_borderColor;
                    }
                }
                if ($general_options["enable_tax_text_color"] == "yes") {
                    $term_textColor = get_term_meta($term_id, 'codemine_event_taxonomy_textColor', true);
                    if ($term_textColor != null) {
                        $temp_array['textColor'] = $term_textColor;
                    }
                }
            }
        }
        if ($general_options["enable_background_color"] == "yes") {
            $event_backgroundColor = get_post_meta($id, 'codemine_event_' . $post_type . '_backgroundColor', true);
            if ($event_backgroundColor != null) {
                $temp_array['backgroundColor'] = get_post_meta($id, 'codemine_event_' . $post_type . '_backgroundColor', true);
            }
        }
        if ($general_options["enable_border_color"] == "yes") {
            $event_borderColor = get_post_meta($id, 'codemine_event_' . $post_type . '_borderColor', true);
            if ($event_borderColor != null) {
                $temp_array['borderColor'] = $event_borderColor;
            }
        }
        if ($general_options["enable_text_color"] == "yes") {
            $event_textColor = get_post_meta($id, 'codemine_event_' . $post_type . '_textColor', true);
            if ($event_textColor != null) {
                $temp_array['textColor'] = $event_textColor;
            }
        }

        return $temp_array;
    }

    private function get_event_fields_and_meta_keys($id, $post_type, $post_type_options, $general_options) {
        $temp_array = array();

        //meta_keys
        $meta_keys = $post_type_options["meta_keys_event_template"];
        if ($meta_keys) {
            foreach ($meta_keys as $meta_key) {
                $temp_array[$meta_key] = get_post_meta($id, $meta_key, true);
            }
        }

        //Custom Fields
        $custom_fields = $general_options["custom_fields"];
        if (!empty($custom_fields)) {
            foreach ($custom_fields as $cf) {
                if (isset($cf["shortcode"]) && isset($cf["id"])) {
                    $shortcode = $cf["shortcode"];
                    $cf_id = 'codemine_event_' . $post_type . '_cf_' . $cf["id"];
                    $temp_array[$shortcode] = get_post_meta($id, $cf_id, true);
                }
            }
        }
        if ($general_options["include_image"] == "include") {
            $size = $general_options["image_size"];
            $temp_array['post_image'] = get_the_post_thumbnail($id, $size);
        }
        if ($general_options["include_image"] == "include_custom_size") {
            $size = array($general_options["image_size_width"], $general_options["image_size_height"]);
            $temp_array['post_image'] = get_the_post_thumbnail($id, $size);
        }
        if ($general_options["include_content"] == "include") {
            $temp_array['post_content'] = do_shortcode(apply_filters('the_content', get_post_field('post_content', $id)));
        }

        if ($general_options["include_post_author"] == "include") {
            $temp_array['post_author'] = get_the_author_meta('display_name', get_post_field('post_author', $id));
        }
        if ($general_options["include_post_date"] == "include") {
            $temp_array['post_date'] = get_post_field('post_date', $id);
        }
        if ($general_options["include_excerpt"] == "include") {
            $temp_array['excerpt'] = get_the_excerpt($id);
        }
        if ($general_options["include_permalink"] == "include") {
            $temp_array['permalink'] = get_the_permalink($id);
        }
        return $temp_array;
    }

    private function get_event_taxonomies($id, $post_type, $post_type_options, $general_options) {
        $temp_array = array();

        //Shortcodes for taxonomies
        $taxonomies = $post_type_options["taxonomies_event_template_filter"];
        if ($taxonomies) {
            foreach ($taxonomies as $tax) {
                $terms = get_the_terms($id, $tax);
                $ids = array();
                $names = array();
                if ($terms) {
                    foreach ($terms as $term) {
                        $ids[] = $term->term_id;
                        $names[] = $term->name;
                    }
                }
                $temp_array[$tax] = $names;
                $temp_array[$tax . "-ids"] = $ids;
            }
        }
        return $temp_array;
    }

    private function get_event_start_end_date($id, $post_type, $post_type_options, $general_options, $calendar_id, $EM_Event = null) {
        $temp_array = array();

        $start_end_dates_mode = $post_type_options["start_end_dates_mode"];
        if ($start_end_dates_mode == "publish_date") {
            $post_date = get_post_field('post_date', $id);
            $timestamp = strtotime($post_date);

            $date = date('Y-n-j', $timestamp);
            $time = date('H:i', $timestamp);
            $temp_array['start_date'] = $date;
            $temp_array['end_date'] = $date;
            $temp_array['start_time'] = $time;
            $temp_array['end_time'] = $time;
        } else if ($start_end_dates_mode == "meta_keys") {
            $start_date_meta_key = strtotime(get_post_meta($id, $post_type_options["start_date_meta_key"], true));
            $end_date_meta_key = strtotime(get_post_meta($id, $post_type_options["end_date_meta_key"], true));
            $start_time_meta_key = strtotime(get_post_meta($id, $post_type_options["start_time_meta_key"], true));
            $end_time_meta_key = strtotime(get_post_meta($id, $post_type_options["end_time_meta_key"], true));

            $temp_array['start_date'] = date('Y-n-j', $start_date_meta_key);
            $temp_array['end_date'] = date('Y-n-j', $end_date_meta_key);
            $temp_array['start_time'] = date('H:i', $start_time_meta_key);
            $temp_array['end_time'] = date('H:i', $end_time_meta_key);
        } else {
            $temp_array['start_date'] = get_post_meta($id, 'codemine_event_' . $post_type . '_date', true);
            $temp_array['end_date'] = get_post_meta($id, 'codemine_event_' . $post_type . '_end_date', true);
            $start_time_meta_key = strtotime(get_post_meta($id, 'codemine_event_' . $post_type . '_start_time', true));
            $end_time_meta_key = strtotime(get_post_meta($id, 'codemine_event_' . $post_type . '_end_time', true));
            $temp_array['start_time'] = date('H:i', $start_time_meta_key);
            $temp_array['end_time'] = date('H:i', $end_time_meta_key);
        }

        $temp_array = apply_filters('cmcal_event_start_end_date', $temp_array, $id, $EM_Event, $post_type, $calendar_id);
        $temp_array = $this->fix_start_end_datetime($temp_array);

        return $temp_array;
    }

    private function fix_start_end_datetime($temp_array) {
        $start_date = $temp_array['start_date'];
        $end_date = $temp_array['end_date'];
        $start_time = $temp_array['start_time'];
        $end_time = $temp_array['end_time'];
        $start_dateTime = $start_date . 'T' . $start_time;
        $temp_array['start_dateTime'] = $start_dateTime;
        if ($end_date != null) {
            $end_dateTime = $end_date . 'T' . $end_time;
        } else {
            $end_dateTime = $start_date . 'T' . $end_time;
        }
        $temp_array['end_dateTime'] = $end_dateTime;
        return $temp_array;
    }

    private function json_events_from_db_general($range_start, $range_end, $timezone, $calendar_id, $all_events) {
        $posts = $this->get_event_posts_ids($calendar_id);
        $json_array = array();
        $options = CMCAL()->setup_options;
        $general_options = $options['general_options'];
        $post_types_options = $options['post_types_options'];
        foreach ($posts as $id) {
            $post_type = get_post_type($id);
            $post_type_options = $post_types_options[$post_type];
            $event = null;
            if ($post_type == "event" && class_exists("EM_Event"))
                $event = new EM_Event($id, 'post_id');
            $temp_array = array();

            $start_end_date = $this->get_event_start_end_date($id, $post_type, $post_type_options, $general_options, $calendar_id, $event);

            $start_date = $start_end_date['start_date'];
            $end_date = $end_date_to_check = $start_end_date['end_date'];

            if (!empty($start_date)) {

                $codemine_event_repeatable = get_post_meta($id, 'codemine_event_' . $post_type . '_repeatable', true);
                $repeatable_end_date = null;
                if ($codemine_event_repeatable == 'yes') {
                    $repeatable_end_date = $end_date_to_check = get_post_meta($id, 'codemine_event_' . $post_type . '_repeatable_end_date', true);
                }
                if ($all_events || ($start_date && $this->isWithinDayRange($start_date, $end_date_to_check, $range_start, $range_end))) {
                    $temp_array['id'] = $id;
                    $temp_array['title'] = get_the_title($id);
                    $temp_array['start'] = $start_end_date['start_dateTime'];
                    $temp_array['end'] = $start_end_date['end_dateTime'];

                    if ($post_type == "event" && class_exists("EM_Event")) {
                        $temp_array['booked_spaces'] = $event->get_bookings()->get_booked_spaces();
                        $temp_array['spaces'] = $event->get_spaces();
                        $temp_array['remaining_spaces'] = $temp_array['spaces'] - $temp_array['booked_spaces'];
                    }
                    $temp_array = $temp_array + $this->get_event_all_day($id, $post_type, $post_type_options, $general_options);
                    $temp_array = $temp_array + $this->get_event_custom_styles($id, $post_type, $post_type_options, $general_options, $calendar_id);
                    $temp_array = $temp_array + $this->get_event_taxonomies($id, $post_type, $post_type_options, $general_options);
                    $temp_array = $temp_array + $this->get_event_fields_and_meta_keys($id, $post_type, $post_type_options, $general_options);

                    //Apply filter to existing data
                    $temp_array = apply_filters('cmcal_event_data', $temp_array, $calendar_id, $id, $event);

                    if ($post_type_options["repetition_enabled"] == 'yes') {
                        // create event repetitions
                        $this->temp_array = $temp_array;
                        if ($codemine_event_repeatable == 'yes') {
                            if ($start_date && $this->isWithinDayRange($start_date, $end_date, $range_start, $range_end)) {
                                $json_array[] = $temp_array;
                            }
                            $json_array = array_merge($json_array, $this->create_repeating_events($id, $start_date, $start_end_date['start_time'], $end_date, $start_end_date['end_time'], $repeatable_end_date, $range_start, $range_end, $post_type));
                        } else {
                            $json_array[] = $temp_array;
                        }
                        $json_array = array_merge($json_array, $this->create_repeating_events_certain_dates($id, $all_events, $repeatable_end_date, $range_start, $range_end, $post_type, $codemine_event_repeatable));
                    } else {
                        $json_array[] = $temp_array;
                    }
                }
            }
        }
        return $json_array;
    }

    public function isWithinDayRange($eventStart, $eventEnd, $rangeStart, $rangeEnd) {
        if (empty($rangeStart) && empty($rangeEnd))
            return true;
        // Normalize our event's dates for comparison with the all-day range.
        $eventStart = CMCAL()->utils->stripTime(new DateTime($eventStart));

        if (!$eventEnd) {
            // No end time? Only check if the start is within range.
            if (!empty($rangeStart) && empty($rangeEnd)) {
                return $eventStart >= $rangeStart;
            }
            return $eventStart <= $rangeEnd && $eventStart >= $rangeStart;
        } else {
            // Check if the two ranges intersect.
            $eventEnd = CMCAL()->utils->stripTime(new DateTime($eventEnd));
            if (!empty($rangeStart) && !empty($rangeEnd)) {
                return $eventStart <= $rangeEnd && $eventEnd >= $rangeStart;
            } else if (!empty($rangeStart) && empty($rangeEnd)) {
                return $eventEnd >= $rangeStart;
            } else if (empty($rangeStart) && !empty($rangeEnd)) {
                return $eventStart <= $rangeEnd;
            }
        }
    }

    private function create_repeating_events($id, $start_date, $start_time, $end_date, $end_time, $repeatable_end_date, $range_start, $range_end, $post_type) {
        $return = array();
        $array = $this->temp_array;
        $repetition = $this->get_event_repetition($id, $post_type);
        $start_date = new DateTime($start_date);

        if ($end_date != null) {
            $end_date = new DateTime($end_date);
        } else {
            $end_date = clone $start_date;
        }

        // version 2.1
        $exclude_dates_db = get_post_meta($id, 'codemine_event_' . $post_type . '_repetition_dates_exclude_group', true);
        $exclude_dates = array();
        if (!empty($exclude_dates_db)) {
            foreach ($exclude_dates_db as $exclude_date) {
                $temp = isset($exclude_date['codemine_event_' . $post_type . '_date']) ? $exclude_date['codemine_event_' . $post_type . '_date'] : null;
                if (!empty($temp)) {
                    $temp = new DateTime($temp);
                    $exclude_dates[] = $temp->format('Y-m-d');
                }
            }
        }

        $repeatable_end_date = new DateTime($repeatable_end_date);
        while ($start_date->format('Y-m-d') < $repeatable_end_date->format('Y-m-d')) {
            $date_interval = $this->get_next_n_interval($repetition);
            $start_date->add(new DateInterval($date_interval));

            $rep_day = $start_date->format('Y-m-d') . 'T' . $start_time;

            if ($end_date->format('Y-m-d') != null) {
                $end_date->add(new DateInterval($date_interval));
                $rep_end_day = $end_date->format('Y-m-d') . 'T' . $end_time;
            }
            if ($this->isWithinDayRange($rep_day, $rep_end_day, $range_start, $range_end)) {
                if ($start_date->format('Y-m-d') <= $repeatable_end_date->format('Y-m-d')) {
                    $array['start'] = $rep_day;
                    $array['end'] = $rep_end_day;
                    if (!in_array($start_date->format('Y-m-d'), $exclude_dates)) {
                        $return[] = $array;
                    }
                }
            }
        }
        return $return;
    }

    private function create_repeating_events_certain_dates($id, $all_events, $repeatable_end_date, $range_start, $range_end, $post_type, $codemine_event_repeatable) {
        $return = array();
        $array = $this->temp_array;
        $certain_dates = get_post_meta($id, 'codemine_event_' . $post_type . '_certain_dates_group', true);
        $temp_array = array();
        if (!empty($certain_dates)) {
            foreach ($certain_dates as $certain_date) {
                $temp_array['start_date'] = isset($certain_date['codemine_event_' . $post_type . '_date']) ? $certain_date['codemine_event_' . $post_type . '_date'] : null;
                $temp_array['end_date'] = isset($certain_date['codemine_event_' . $post_type . '_end_date']) ? $certain_date['codemine_event_' . $post_type . '_end_date'] : null;
                $temp_array['start_time'] = isset($certain_date['codemine_event_' . $post_type . '_start_time']) ? $certain_date['codemine_event_' . $post_type . '_start_time'] : null;
                $temp_array['end_time'] = isset($certain_date['codemine_event_' . $post_type . '_end_time']) ? $certain_date['codemine_event_' . $post_type . '_end_time'] : null;
                $temp_array = $this->fix_start_end_datetime($temp_array);
                $start_date = $temp_array['start_date'];
                $end_date = $end_date_to_check = $temp_array['end_date'];
                if (!empty($start_date)) {
                    if ($all_events || ($start_date && $this->isWithinDayRange($start_date, $end_date_to_check, $range_start, $range_end))) {
                        $array['start'] = $temp_array['start_dateTime'];
                        $array['end'] = $temp_array['end_dateTime'];
                        $return[] = $array;
                    }
                    $is_certain_date_repeatable = isset($certain_date['codemine_event_' . $post_type . '_repeatable_certain']) ? $certain_date['codemine_event_' . $post_type . '_repeatable_certain'] : 'no';
                    if ($is_certain_date_repeatable == 'yes' && $codemine_event_repeatable == 'yes') {
                        $return = array_merge($return, $this->create_repeating_events($id, $start_date, $temp_array['start_time'], $end_date, $temp_array['end_time'], $repeatable_end_date, $range_start, $range_end, $post_type));
                    }
                }
            }
        }
        return $return;
    }

    private function get_next_n_interval($repetition) {
        $codemine_event_repetition = $repetition["rep_type"];
        $rep = $repetition["rep_n"];
        if ($codemine_event_repetition == 'daily') {
            $output = 'P' . $rep . 'D';
        } else if ($codemine_event_repetition == 'weekly') {
            $rep = $rep * 7;
            $output = 'P' . $rep . 'D';
        } else if ($codemine_event_repetition == 'monthly') {
            $output = 'P' . $rep . 'M';
        } else if ($codemine_event_repetition == 'yearly') {
            $output = 'P' . $rep . 'Y';
        }
        return $output;
    }

    private function get_event_repetition($id, $post_type) {
        $rep = null;
        $codemine_event_repetition = get_post_meta($id, 'codemine_event_' . $post_type . '_repetition', true);
        if ($codemine_event_repetition == 'daily') {
            $rep = get_post_meta($id, 'codemine_event_' . $post_type . '_repetition_daily_n', true);
        } else if ($codemine_event_repetition == 'weekly') {
            $rep = get_post_meta($id, 'codemine_event_' . $post_type . '_repetition_weekly_n', true);
        } else if ($codemine_event_repetition == 'monthly') {
            $rep = get_post_meta($id, 'codemine_event_' . $post_type . '_repetition_monthly_n', true);
        } else if ($codemine_event_repetition == 'yearly') {
            $rep = get_post_meta($id, 'codemine_event_' . $post_type . '_repetition_yearly_n', true);
        }
        return array("rep_type" => $codemine_event_repetition, "rep_n" => $rep);
    }

    private function get_cmb2_post_meta_boolean($post_id, $key) {
        $post_meta = get_post_meta($post_id, $key, true);
        if ($post_meta == 'true') {
            return true;
        } else if ($post_meta == 'true') {
            return false;
        }
    }

    /*
      ================================================================
      Get settings calendar
      ================================================================
     */

    private $preview_theme_settings = [];

    public function get_calendar_db_settings($calendar_id) {

        $array = array();
        if (!empty(CMCAL()->preview_theme_name)) {
            if (empty($this->preview_theme_settings))
                $this->preview_theme_settings = unserialize(include_once(Codemine_Calendar_PLUGIN_DIR_PATH . 'includes/backend/calendar-themes/' . CMCAL()->preview_theme_name . '/theme_settings.php'));
            foreach (CMCAL()->customizer_settings->get_calendar_custom_settings_db_fields()as $setting) {
                $array[$setting] = isset($this->preview_theme_settings[$setting]) ? $this->preview_theme_settings[$setting] : "";
            }
        } else {
            $this->preview_theme_settings = [];
            foreach (CMCAL()->customizer_settings->get_calendar_custom_settings_db_fields()as $setting) {
                $array[$setting] = unserialize(get_option($setting . "_" . $calendar_id));
            }
        }

        return $array;
    }

    /*
      ================================================================
      Get custom styles for calendar
      ================================================================
     */

    public function get_calendar_custom_styles($calendar_id) {
        $db_settings = $this->get_calendar_db_settings($calendar_id);
        $array = array();
        foreach (CMCAL()->customizer_settings->get_calendar_custom_styles_editors()as $setting) {
            $val = isset($db_settings[$setting["db_field"]][$setting["id"]]) ? $db_settings[$setting["db_field"]][$setting["id"]] : "";
            if (isset($setting['isHtml']) && $setting['isHtml'] == "true")
                $val = stripslashes($val);
            $array[$setting["id"]] = $val;
        }
        return $array;
    }

    /*
      ================================================================
      Get custom settings of calendar
      ================================================================
     */

    public function get_calendar_custom_settings($calendar_id) {
        $db_settings = $this->get_calendar_db_settings($calendar_id);
        $array = array();
        foreach (CMCAL()->customizer_settings->get_calendar_custom_settings_editors()as $setting) {
            $val = isset($db_settings[$setting["db_field"]][$setting["id"]]) ? $db_settings[$setting["db_field"]][$setting["id"]] : "";
            if (isset($setting['isHtml']) && $setting['isHtml'] == "true")
                $val = stripslashes($val);
            $array[$setting["id"]] = $val;
        }
        $toolbar_settings = "";
        if(isset($db_settings[CMCAL()->shortname . "_customizer_toolbar_settings"]["toolbar_settings"])){
            $toolbar_settings = $db_settings[CMCAL()->shortname . "_customizer_toolbar_settings"]["toolbar_settings"];
        }
        $array["toolbar_settings"] = $toolbar_settings;
        return $array;
    }

    public function get_googlefonts_urls($calendar_id) {
        $font_editors = [];
        $selectedfonts = [];
        $settings = CMCAL()->customizer_settings->get_calendar_settings();
        $db_settings = $this->get_calendar_db_settings($calendar_id);
        foreach ($settings as $setting) {
            if (isset($setting["type"]) && isset($setting["id"]) && isset($setting["id"]["familly"]) && isset($setting["db_field"])) {
                if ($setting["type"] == "fonts") {
                    $db_field = $setting['db_field'];
                    $id = $setting['id']["familly"];
                    $selectedfonts[$id] = isset($db_settings[$db_field][$id]) ? $db_settings[$db_field][$id] : "";
                }
            }
        }
        $googlefonts_urls = [];
        $fonts = CMCAL()->googlefonts;

        $distinct_selectedfonts = array_unique($selectedfonts);
        foreach ($distinct_selectedfonts as $font) {
            if (!empty($font) && isset($fonts[$font])) {
                $fullfont = [];

                //familly
                $fontfamilly = str_replace(" ", "+", $font);
                $fullfont[] = $fontfamilly;

                //variants
                $fontVariants = [];
                foreach ($fonts[$font]['variants'] as $variant)
                    $fontVariants[] = $variant["id"];
                $fullfont[] = join(',', $fontVariants);

                //variants
                $fontSubsets = [];
                foreach ($fonts[$font]['subsets'] as $variant)
                    $fontSubsets[] = $variant["id"];
                $fullfont[] = join(',', $fontSubsets);


                $googlefonts_urls[] = join(':', $fullfont);
            }
        }
        if (!empty($googlefonts_urls))
            return "https://fonts.googleapis.com/css?family=" . join('|', $googlefonts_urls);
        return "";
    }

    public function get_calendars() {
        $calendars = get_terms(array(
            'taxonomy' => 'cmcal_calendar',
            'hide_empty' => false,
            'fields' => "ids",
        ));
        return $calendars;
    }

}
