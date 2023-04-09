<?php

if (!defined('ABSPATH')) {
    exit;
}

class EventM_Bulk_Emails_Service {

    private $dao;
    private static $instance = null;
    
    private function __construct() {}
    
    public static function get_instance()
    {   
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function load_new_emails_page() {
        $data= new stdClass();
        $filter = array();
        $filter = array(
            'numberposts' => -1,
            'post_status' => 'publish',
            'order' => 'DESC',
            'post_type' => EM_EVENT_POST_TYPE,
            'meta_query' => array(
                array(
                    'key' => em_append_meta_key( 'enable_booking' ),
                    'value' => 1,
                    'compare' => '=',
                    'type' => 'NUMERIC,'
                ),
            ),
        );
        $event_service = EventM_Factory::get_service('EventM_Service');
        $events = $event_service->get_events($filter);
        $data->events = array();
        //$data->events[] = array( 'id' => 0, 'title' => esc_html__( 'Select Events', 'eventprime-event-calendar-management' ) );
        if (!empty($events)) {
            foreach ($events as $event) {
                $tmp = new stdClass();
                $tmp->id = $event->ID;
                if ($event->post_parent > 0) {
                    $date = em_showDateTime( $event_service->get_meta( $event->ID, 'start_date' ), false, "m/d/Y" );
                    $tmp->title = $event->post_title . ' - ' . $date;
                } else {
                    $tmp->title = $event->post_title;
                }
                $data->events[] = $tmp;
            }
        }
        
        return $data;
    }

    public function send_bulk_emails() {
        $email_address = event_m_get_param( 'email_address' );
        $cc_email_address = event_m_get_param('cc_email_address');
        $email_subject = event_m_get_param( 'email_subject' );
        $content = event_m_get_param( 'content' );
        $event_id = event_m_get_param( 'event_id' );
        $headers = array( 'Content-Type: text/html; charset=UTF-8' );
        if( ! empty( $cc_email_address ) ) {
            if ( filter_var( $cc_email_address, FILTER_VALIDATE_EMAIL ) ) {
                array_push( $headers , "Cc: $cc_email_address" );
            }else{
                $error_msg = esc_html__( $cc_email_address. ' is not a valid email', 'eventprime-event-calendar-management' );
                return array( "error" => 1, "message" => $error_msg );
            }
        }
        if( empty( $email_subject ) ) {
            $error_msg = esc_html__( 'Subject is a required field', 'eventprime-event-calendar-management' );
            return array( "error" => 1, "message" => $error_msg );
        }
        if( empty( $content ) ) {
            $error_msg = esc_html__( 'Email content is a required field', 'eventprime-event-calendar-management' );
            return array( "error" => 1, "message" => $error_msg );
        }
        
        $email_address = explode( ',', $email_address );
        if( count( $email_address ) > 0 ) {
            foreach( $email_address as $email ){
                if ( !filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
                    $error_msg = esc_html__( $email. ' is not a valid email', 'eventprime-event-calendar-management' );
                    return array( "error" => 1, "message" => $error_msg );
                }
            }

            foreach( $email_address as $email ) {
                wp_mail( $email, $email_subject, $content, $headers );
            }
        }
        wp_send_json_success( array( 'success' => 1, "message" => esc_html__( 'Email send successfully', 'eventprime-event-calendar-management' ) ) );
    }

    public function format_attendees_email_addresses( $attendee_names = array() ){
        $userEmail = array();
        foreach( $attendee_names as $attendee_email ) {
            if( is_array( $attendee_email ) ) { 
                foreach( $attendee_email as $value ){
                    if( filter_var( $value, FILTER_VALIDATE_EMAIL ) ) {
                        // valid email address
                        $userEmail[] = $value;
                    } 
                }
            }else{
                if( filter_var( $attendee_email, FILTER_VALIDATE_EMAIL ) ) {
                    // valid email address
                    $userEmail[] = $attendee_email;
                } 
            }
        }
        return $userEmail;
    }
}