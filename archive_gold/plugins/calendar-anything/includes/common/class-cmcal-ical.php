<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Cmcal_Ical {

    public function __construct() {
        add_action('init', array($this, 'add_ical_feed'));
    }

    function add_ical_feed() {
        add_feed('cmcal-ical', array($this, 'render_ical_feed'));
    }

    function render_ical_feed() {
        header('Content-Type: text/html');

        if (isset($_REQUEST['file']))
            header('Content-Disposition: attachment; filename="cmcal_ical.ics"');
        $calendar_id = $_REQUEST['calendar-id'];
        $content = $this->cmcal_ical_get_feed_content($calendar_id);
        echo $content;
    }

    function cmcal_ical_get_feed_content($calendar_id) {
        $all_events = CMCAL()->dal->get_events(null, null, null, $calendar_id, true);

        $TZID = ';TZID=/' . get_option('timezone_string'); 
        $TZID = apply_filters('cmcal_ical_timezone', $TZID, $calendar_id);

        $content = 'BEGIN:VCALENDAR' . '
VERSION:2.0' . '
PRODID:cmcal-' . $calendar_id;

        foreach ($all_events as $event) {
            $ical_event = $this->cmcal_ical_get_event($event, $TZID, $calendar_id);

            $allDay = $this->get_allday_event($event);

            $content .= '
BEGIN:VEVENT';
            foreach ($ical_event as $key => $val) {
                //Get Ical Key
                $ical_field = $key;
                if ($key == 'DTSTART' || $key == 'DTEND') {
                    $ical_field = $allDay ? $key . ';VALUE=DATE' : $key . $TZID;
                } else if ($key == 'DTSTAMP') {
                    $ical_field = $key . $TZID;
                }
                $content .= '
' . $ical_field . ':' . $val;
            }
            $content .= '
END:VEVENT';
        }

        $content .= '
END:VCALENDAR';
        return $content;
    }

    function cmcal_ical_get_event($event, $TZID, $calendar_id) {
        $event_id = $event["id"];
        $UID = $this->get_uid($event_id);

        $utc_z = empty($TZID) ? "Z" : "";
        $allDay = $this->get_allday_event($event);

//        $stamp = new DateTime($event["start"]);
//        $DTSTAMP = $stamp->format("Ymd") . 'T' . $stamp->format("His") . $utc_z;

        $start = new DateTime($event["start"]);
        $DTSTART = $start->format("Ymd");
        if (!$allDay)
            $DTSTART .= 'T' . $start->format("His") . $utc_z;

        $end = new DateTime($event["end"]);
        $DTEND = $end->format("Ymd");
        if (!$allDay)
            $DTEND .= 'T' . $end->format("His") . $utc_z;


        $SUMMARY = $event["title"];
//            $URL = $event["url"];
//            $DESCRIPTION = get_the_excerpt($event_id); //'';
//            $LOCATION = '';

        $ical_event = array(
//            'DTSTAMP' => $DTSTAMP,
            'UID' => $UID,
            'DTSTART' => $DTSTART,
            'DTEND' => $DTEND,
            'SUMMARY' => $SUMMARY,
//                'URL' => $URL,
//                'DESCRIPTION' => $DESCRIPTION,
//                'LOCATION' => $LOCATION,
        );

        $ical_event = apply_filters('cmcal_ical_event', $ical_event, $event_id, $calendar_id, $event);
        return $ical_event;
    }

    function get_allday_event($event) {
        $allDay_enabled = false;
        $allDay = false;
        $allDay_enabled = apply_filters('cmcal_ical_allDay_enabled', $allDay_enabled);
        if ($allDay_enabled)
            $allDay = (isset($event["is_allDay"]) && $event["is_allDay"] == true) ? true : false;
        return $allDay;
    }

    function get_uid($event_id) {
        $site_url = get_site_url();
        // remove http and https
        $find = array('http://', 'https://');
        $replace = '';
        $site_url = str_replace($find, $replace, $site_url);
        $output = $site_url . '-id-' . $event_id;
        return $output;
    }

}
