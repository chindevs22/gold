<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class EventM_AJAX {

    protected $request;
    protected $services= array();
    private static $instance;
    protected $table = array();
    
    public static function get_instance()
    {   
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
    
    private function __construct() {
        $this->services['venue']= EventM_Factory::get_service('EventM_Venue_Service');
        $this->services['event']= EventM_Factory::get_service('EventM_Service');
        $this->services['performer']= EventM_Factory::get_service('EventM_Performer_Service');
        $this->services['event_type']= EventM_Factory::get_service('EventTypeM_Service');
        $this->services['setting']= EventM_Factory::get_service('EventM_Setting_Service');
        $this->services['booking']= EventM_Factory::get_service('EventM_Booking_Service');
        $this->services['bulk_emails']= EventM_Factory::get_service('EventM_Bulk_Emails_Service');
        $this->services['event_organizer']= EventM_Factory::get_service('EventOrganizerM_Service');
        $this->request = EventM_Raw_Request::get_instance();
        $this->add_ajax_events();
        // table option
        $this->table['multi_price'] = get_ep_table_name('em_price_options');
    }

    public function add_ajax_events() {
        add_action('wp_ajax_em_load_strings', array($this, 'load_data'));
        $ajax_events = array('save_venue' => false,
            'save_performer' => false,
            'save_event_type' => false,
            'save_event' => false,
            'save_popup_event'=>false,
            'save_dropped_event'=>false,
            'delete_posts' => false,
            'duplicate_posts' => false,
            'delete_terms' => false,
            'duplicate_terms' => false,
            'save_global_settings' => false,
            'save_booking' => false,
            'cancel_booking' => false,
            'load_event_dates' => true,
            'load_venue_addresses' => true,
            'register_user' => true,
            'check_bookable' => true,
            'login_user' => true,
            'book_seat' => true,
            'load_event_seats' => true,
            'load_payment_configuration' => true,
            'update_booking' => true,
            'print_ticket' => false,
            'export_bookings' => false,
            'event_details' => true,
            'show_booking_details' => false,
            'cancel_booking_by_user' => false,
            'get_venue_capcity' => true,
            'verify_booking' => true,
            'download_booking_details' => true,
            'confirm_booking_without_payment' => true,
            'rm_custom_datas' => true,
            'resend_mail' => true,
            'booking_cancellation_mail' => true,
            'booking_confirm_mail' => true,
            'booking_refund_mail' => true,
            'booking_pending_mail' => true,
            'load_event_for_booking' => true,
            'delete_order' => true,
            'add_event_tour_completed' => false,
            'event_tour_completed' => false,
            'load_admin_events'=> false,
            'deactivation_feedback'=> false,
            'frontend_event_submit_form_strings' => true,
            'submit_frontend_event' => true,
            'event_submitted_mail' => true,
            'event_approved_mail' => false,
            'upload_image_from_frontend' => true,
            'load_masonry_events_data' => true,
            'delete_fes_event' => false,
            'export_submittion_attendees' => false,
            'get_attendees_email_by_event_id' => false,
            'send_bulk_emails' => false,
            'fetch_offers' => false,
            'dismiss_notice_action' => false,
            'load_cards_events_data' => true,
            'save_event_price_option' => false,
            'delete_event_price_option' => false,
            'multi_price_list_sorting' => false,
            'load_list_events_data' => true,
            'save_event_organizer' => false,
            'get_organizer_data' => false,
            'save_user_custom_caps' => false,

            'load_performers_card_data' => true,
            'load_performers_box_data' => true,
            'load_performers_list_data' => true,
            'load_performer_events_card_block' => true,
            'load_performer_events_list_block' => true,
            'load_performer_events_mini_list_block' => true,

            'load_types_card_data' => true,
            'load_types_box_data' => true,
            'load_types_list_data' => true,
            'load_type_events_card_block' => true,
            'load_type_events_list_block' => true,
            'load_type_events_mini_list_block' => true,

            'load_organizers_card_data' => true,
            'load_organizers_box_data' => true,
            'load_organizers_list_data' => true,
            'load_organizer_events_card_block' => true,
            'load_organizer_events_list_block' => true,
            'load_organizer_events_mini_list_block' => true,

            'load_venues_card_data' => true,
            'load_venues_box_data' => true,
            'load_venues_list_data' => true,
            'load_venue_events_card_block' => true,
            'load_venue_events_list_block' => true,
            'load_venue_events_mini_list_block' => true,

            'remove_post_featured_image' => true,
        );
        foreach ($ajax_events as $ajax_event => $nopriv) {
            add_action('wp_ajax_em_' . $ajax_event, array($this, $ajax_event));
            if ($nopriv) {
                add_action('wp_ajax_nopriv_em_' . $ajax_event, array($this, $ajax_event));
            }
        }
    }

    public function load_data() {
        $context = event_m_get_param('em_request_context');
        if(empty($context))
            return;
        if(method_exists($this,$context)){
            $this->{$context}();
        }
        do_action('event_magic_load_strings', $context);
    }
    
    // Loads events for admin screen.
    public function load_admin_events(){
        $em_event_nonce = event_m_get_param('em_event_nonce');
        if( empty( $em_event_nonce ) || !wp_verify_nonce( $em_event_nonce, 'em_event_object_nonce' ) || empty( em_check_context_user_capabilities( array( 'view_events' ) ) ) ) {
            $error_msg = esc_html__( 'Unauthorized Access', 'eventprime-event-calendar-management' );
            wp_send_json_error( array( 'errors' => array( $error_msg ) ) );
        }
        $view = event_m_get_param('view');
        $response = $view != 'cards' ? $this->services['event']->admin_calendar_view() : $this->services['event']->load_list_page();
        wp_send_json_success($response);
    }
    
    private function admin_venues(){
        $em_event_site_nonce = event_m_get_param('em_event_site_nonce');
        if( empty( $em_event_site_nonce ) || !wp_verify_nonce( $em_event_site_nonce, 'em_venue_object_nonce' ) || empty( em_check_context_user_capabilities( array( 'view_event_sites' ) ) ) ) {
            $error_msg = esc_html__( 'Unauthorized Access', 'eventprime-event-calendar-management' );
            wp_send_json_error( array( 'errors' => array( $error_msg ) ) );
        }
        $response= $this->services['venue']->load_list_page();
        wp_send_json_success($response);
    }
    private function admin_venue(){
        $em_event_site_nonce = event_m_get_param('em_event_site_nonce');
        if( empty( $em_event_site_nonce ) || !wp_verify_nonce( $em_event_site_nonce, 'em_venue_object_nonce' ) || empty( em_check_context_user_capabilities( array( 'create_event_sites' ) ) ) ) {
            $error_msg = esc_html__( 'Unauthorized Access', 'eventprime-event-calendar-management' );
            wp_send_json_error( array( 'errors' => array( $error_msg ) ) );
        }
        $venue = $this->services['venue']->load_edit_page();
        if( isset( $venue->error ) ) {
            wp_send_json_error( array( 'errors' => array( $venue->message ) ) );
        }

        wp_send_json_success(array('term' => $venue));
    }
    
    private function admin_event(){
        $em_event_nonce = event_m_get_param('em_event_nonce');
        if( empty( $em_event_nonce ) || !wp_verify_nonce( $em_event_nonce, 'em_event_object_nonce' ) || empty( em_check_context_user_capabilities( array( 'view_events' ) ) ) ) {
            $error_msg = esc_html__( 'Unauthorized Access', 'eventprime-event-calendar-management' );
            wp_send_json_error( array( 'errors' => array( $error_msg ) ) );
        }
        $response= $this->services['event']->load_edit_page();
        wp_send_json_success($response);
    }
    
    private function admin_performer(){
        $em_performer_nonce = event_m_get_param('em_performer_nonce');
        if( empty( $em_performer_nonce ) || !wp_verify_nonce( $em_performer_nonce, 'em_performer_object_nonce' ) || empty( em_check_context_user_capabilities( array( 'create_event_performers' ) ) ) ) {
            $error_msg = esc_html__( 'Unauthorized Access', 'eventprime-event-calendar-management' );
            wp_send_json_error( array( 'errors' => array( $error_msg ) ) );
        }
        $performer = $this->services['performer']->load_edit_page();
        if( isset( $performer->error ) ) {
            wp_send_json_error( array( 'errors' => array( $performer->message ) ) );
        }
        wp_send_json_success(array('post' => $performer));
    }
    
    private function admin_performers(){
        $em_performer_nonce = event_m_get_param('em_performer_nonce');
        if( empty( $em_performer_nonce ) || !wp_verify_nonce( $em_performer_nonce, 'em_performer_object_nonce' ) || empty( em_check_context_user_capabilities( array( 'view_event_performers' ) ) ) ) {
            $error_msg = esc_html__( 'Unauthorized Access', 'eventprime-event-calendar-management' );
            wp_send_json_error( array( 'errors' => array( $error_msg ) ) );
        }
        $response = $this->services['performer']->load_list_page();
        wp_send_json_success($response);
    }
    
    private function admin_event_type(){
        $em_event_type_nonce = event_m_get_param('em_event_type_nonce');
        if( empty( $em_event_type_nonce ) || !wp_verify_nonce( $em_event_type_nonce, 'em_event_type_object_nonce' ) || empty( em_check_context_user_capabilities( array( 'create_event_types' ) ) ) ) {
            $error_msg = esc_html__( 'Unauthorized Access', 'eventprime-event-calendar-management' );
            wp_send_json_error( array( 'errors' => array( $error_msg ) ) );
        }
        $response = $this->services['event_type']->load_edit_page();
        if( isset( $response->error ) ) {
            wp_send_json_error( array( 'errors' => array( $response->message ) ) );
        }
        wp_send_json_success(array('term' => $response));
    }
    
    private function admin_event_types(){
        $em_event_type_nonce = event_m_get_param('em_event_type_nonce');
        if( empty( $em_event_type_nonce ) || !wp_verify_nonce( $em_event_type_nonce, 'em_event_type_object_nonce' ) || empty( em_check_context_user_capabilities( array( 'view_event_types' ) ) ) ) {
            $error_msg = esc_html__( 'Unauthorized Access', 'eventprime-event-calendar-management' );
            wp_send_json_error( array( 'errors' => array( $error_msg ) ) );
        }
        $response=$this->services['event_type']->load_list_page();
        wp_send_json_success($response);
    }
    
    private function admin_global_settings(){
        $this->check_permission();
        $options= $this->services['setting']->load_edit_page();
        wp_send_json_success(array('options'=>$options));
    }
    
    private function admin_bookings(){
        $this->check_permission();
        $bookings= $this->services['booking']->load_bookings();
        wp_send_json_success($bookings);
    }
    
    private function admin_booking(){
        $this->check_permission();
        $booking= $this->services['booking']->load_booking();
        wp_send_json_success(array('post'=>$booking));
    }
    
    private function admin_event_settings(){
        $this->check_permission();
        $event_settings= $this->services['event']->load_settings_page();
        wp_send_json_success(array('post'=>$event_settings));
    }
    
    public function save_venue() {
        $em_event_site_nonce = event_m_get_param('em_event_site_nonce');
        if( empty( $em_event_site_nonce ) || !wp_verify_nonce( $em_event_site_nonce, 'em_venue_object_nonce' ) || empty( em_check_context_user_capabilities( array( 'create_event_sites' ) ) ) ) {
            $error_msg = esc_html__( 'Unauthorized Access', 'eventprime-event-calendar-management' );
            wp_send_json_error( array( 'errors' => array( $error_msg ) ) );
        }

        $model = $this->request->map_request_to_model('EventM_Venue_Model');
        $model->id = absint(event_m_get_param('id'));

        // Validate data
        $errors = $this->services['venue']->validate($model);
        if (!empty($errors)) {
            wp_send_json_error(array('errors'=>$errors));
        }
        if ($model->type == "standings") {
            $model->seats = array();
            $model->seating_capacity = 0;
        }
        if ($model->type == "seats") {
            $model->standing_capacity = 0;
        }
        if(!empty($model->established)){
            $model->established= em_time($model->established);
        }
        // set user id for term 
        if( empty( $model->id ) ) {
            $model->created_by = get_current_user_id();
        } else{
            $model->last_updated_by = get_current_user_id();
        }
        $venue = $this->services['venue']->save($model);
        if ($venue instanceof WP_Error) {
            $error_msg= $venue->get_error_message(); 
            wp_send_json_error(array('errors'=>array($error_msg)));
        }
        else{
            //If type is seating then update capacity in the corresponding event if it is greater than venue capacity
            $events = $this->services['event']->get_events_by_venue($venue->id);
            foreach($events as $event){
                if(empty($event->seats)){
                    if($venue->type=='seats'){
                        $event->seating_capacity= $venue->seating_capacity;
                        $event->seats=$venue->seats;
                    }
                    else{
                        $event->seats=array();
                    }
                    $this->services['event']->update_model($event);
                }
            }
        }
        $redirect = admin_url('admin.php/?page=em_venues');
        wp_send_json_success(array('redirect' => $redirect));
    }

    public function save_performer() {
        $em_performer_nonce = event_m_get_param('em_performer_nonce');
        if( empty( $em_performer_nonce ) || !wp_verify_nonce( $em_performer_nonce, 'em_performer_object_nonce' ) || empty( em_check_context_user_capabilities( array( 'create_event_performers' ) ) ) ) {
            $error_msg = esc_html__( 'Unauthorized Access', 'eventprime-event-calendar-management' );
            wp_send_json_error( array( 'errors' => array( $error_msg ) ) );
        }
        $model = $this->request->map_request_to_model('EventM_Performer_Model');
        $model->id = absint(event_m_get_param('id'));
        // set user id for performer 
        if( empty( $model->id ) ) {
            $model->created_by = get_current_user_id();
        } else{
            $model->last_updated_by = get_current_user_id();
        }
        /* validation and website, social links data formatting */
        $errors = $this->services['performer']->validate($model);
        if (!empty($errors)) {
            wp_send_json_error(array('errors'=>$errors));
        }
        $model->performer_websites = event_m_get_param('performer_websites');
        foreach($model->performer_websites as $key => $val) { $model->performer_websites[$key] = esc_url($val); }
        foreach($model->social_links as $key => $val) { $model->social_links->$key = esc_url($val); }

        $performer = $this->services['performer']->save($model);
        
        if ($performer instanceof WP_Error) {
            $error_msg= $performer->get_error_message(); 
            wp_send_json_error(array('errors'=>array($error_msg)));
        }
        $redirect = admin_url('/admin.php?page=em_performers');
        wp_send_json_success(array('redirect'=>$redirect));
    }

    public function save_event_type(){
        $em_event_type_nonce = event_m_get_param('em_event_type_nonce');
        if( empty( $em_event_type_nonce ) || !wp_verify_nonce( $em_event_type_nonce, 'em_event_type_object_nonce' ) ) {
            $error_msg = esc_html__( 'Unauthorized Access', 'eventprime-event-calendar-management' );
            wp_send_json_error( array( 'errors' => array( $error_msg ) ) );
        }
        $id = absint(event_m_get_param('id'));
        $model = $this->request->map_request_to_model('EventM_Event_Type_Model');
        $model->id = absint(event_m_get_param('id'));
        $user_allow_save = 1;
        if( empty( $id ) ) {
            if( empty( em_check_context_user_capabilities( array( 'create_event_types' ) ) ) ) {
                $user_allow_save = 0;
            }
        } else{
            if( empty( em_check_context_user_capabilities( array( 'edit_event_types' ) ) ) ) {
                $user_allow_save = 0;
            }
            if( empty( em_check_context_user_capabilities( array( 'edit_others_event_types' ) ) ) ) {
                if( $model->created_by != get_current_user_id() ) {
                    $user_allow_save = 0;
                }
            }
        }
        // if user not allow to save then through error
        if( $user_allow_save == 0 ) {
            $error_msg = esc_html__( 'You have no permission.', 'eventprime-event-calendar-management' );
            wp_send_json_error( array( 'errors' => array( $error_msg ) ) );
        }

        // Validate data
        $errors = $this->services['event_type']->validate($model);
        if (!empty($errors)) {
            wp_send_json_error(array('errors'=>$errors));
        }

        // set user id for term 
        if( empty( $model->id ) ) {
            $model->created_by = get_current_user_id();
        } else{
            $model->last_updated_by = get_current_user_id();
        }
        $event_type = $this->services['event_type']->save($model);
        if ($event_type instanceof WP_Error) {
            $error_msg= $event_type->get_error_message(); 
            wp_send_json_error(array('errors'=>array($error_msg)));
        }
        $redirect = admin_url('admin.php/?page=em_event_types');
        wp_send_json_success(array('redirect'=>$redirect));
    }

    public function save_event() {
        $em_event_nonce = event_m_get_param('em_event_nonce');
        if( empty( $em_event_nonce ) || !wp_verify_nonce( $em_event_nonce, 'em_event_object_nonce' ) || empty( em_check_context_user_capabilities( array( 'edit_events', 'edit_others_events' ) ) ) ) {
            $error_msg = esc_html__( 'Unauthorized Access', 'eventprime-event-calendar-management' );
            wp_send_json_error( array( 'errors' => array( $error_msg ) ) );
        }
        $id = absint(event_m_get_param('id'));
        if(empty($id)){
            wp_send_json_error(array('errors'=>array(__('Invalid event ID','eventprime-event-calendar-management'))));
        }
        $screen= event_m_get_param('screen');
        // Loading existing model
        $event= $this->services['event']->load_model_from_db($id);
        if(empty($event->id)){
            wp_send_json_error(array('errors'=>array(__('Invalid event ID','eventprime-event-calendar-management'))));
        }
        
        if($screen=='event_settings'){ // Saving Event Setting screen options
            $errors=array();
            $old_status= $event->status;
            // Event type validation
            $event_type = sanitize_text_field(event_m_get_param('event_type'));
            if ($event_type=='new_event_type')
            {
                $new_event_type= event_m_get_param('new_event_type');
                if(term_exists($new_event_type,EM_EVENT_TYPE_TAX)){
                    $errors[]= __('Please use different Event Type','eventprime-event-calendar-management');
                }
            }
            // datepicker format from global settings
            $datepicker_format_arr = explode('&', em_global_settings('datepicker_format'));
            $datepicker_format = '';
            if(!empty($datepicker_format_arr) && isset($datepicker_format_arr[1])){
                $datepicker_format = $datepicker_format_arr[1] . ' H:i';
            }
            // Dates validation
            $start_date= em_timestamp(event_m_get_param('start_date'));
            $end_date= em_timestamp(event_m_get_param('end_date'));
            if ($start_date > $end_date) {
                $errors[]= __('Event Starts should be prior to Ends.','eventprime-event-calendar-management');
            }
            $enable_booking= absint(event_m_get_param('enable_booking'));
            if(!empty($enable_booking)){
                $start_booking_date =  em_timestamp(event_m_get_param('start_booking_date'));
                $last_booking_date = em_timestamp(event_m_get_param('last_booking_date'));
                if ($last_booking_date> $end_date) {
                    $errors[]=__('Last booking date can not be greater than Ends.','eventprime-event-calendar-management');
                }

                if ($start_booking_date > $last_booking_date) {
                    $errors[]=__('Start booking date should be earlier than the Last Booking date.','eventprime-event-calendar-management');
                }
                if ($start_booking_date > $start_date) {
                    $errors[]=__('Start booking date must be earlier than Event Start date.','eventprime-event-calendar-management');
                }
                if ($start_booking_date == $last_booking_date) {
                    $errors[]=__('Start booking date must be earlier than end booking date.','eventprime-event-calendar-management');
                }
            }
            if(absint(event_m_get_param('custom_link_enabled')) == 1){
                if(!preg_match('/^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/',event_m_get_param('custom_link'))){
                    $errors[]=__('Incorrect custom link. Please update custom link to a correct URL format.','eventprime-event-calendar-management');
                }
            }
            $errors= apply_filters('em_event_settings_validation',$errors);
            if(!empty($errors)){
                wp_send_json_error(array('errors'=>$errors));
            }
            $this->services['event']->save_settings($event);
        }
        else if($screen=='event_performers'){
            $this->services['event']->save_performers($event);
        }
        else if($screen=='event_organizer'){
            $errors = array();
            $emails = event_m_get_param('organizer_emails');
            $websites = event_m_get_param('organizer_websites');
            
            foreach($emails as $email) {
                if (!preg_match('/^([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z]{2,5})$/',$email)) {
                    $errors[]=__('Incorrect email format.','eventprime-event-calendar-management');
                    break;
                }
            }
            
            foreach($websites as $website) {
                if (!preg_match('/^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/',$website)) {
                    $errors[]=__('Incorrect website URL format.','eventprime-event-calendar-management');
                    break;
                }
            }
            
            if (!empty($errors)) {
                wp_send_json_error(array('errors'=>$errors));
            }

            $this->services['event']->save_organizer($event);
        }
        else if($screen=='event_social'){
            $this->services['event']->save_social($event);
        }
        else if($screen=='event_email'){
            $this->services['event']->save_email($event);
        }
        else if($screen=='event_venue'){
            $errors= apply_filters('em_event_capacity_validation',$errors,$event);
            if(!empty($errors)){
                wp_send_json_error(array('errors'=>$errors));
            }
            $this->services['event']->save_venue($event);
        }
        
        do_action('event_magic_save_event',$event->id);
        $redirect = admin_url('/admin.php?page=em_dashboard&post_id=' . $event->id);
        $response['redirect'] = $redirect;
        // event data for email
        if($screen=='event_settings'){
            $event= $this->services['event']->load_model_from_db($event->id);
            $new_status= $event->status;
            $cal_event= array();
            $cal_event['id']= $event->id;
            $cal_event['title']= $event->name;
            $cal_event['start']= date('c',$event->start_date);
            $cal_event['start_booking_date']= $event->start_booking_date;
            $cal_event['end']= date('c',$event->end_date);
            $cal_event['last_booking_date']= $event->last_booking_date;
            $cal_event['enable_booking']= $event->enable_booking;
            $cal_event['ticket_price']= $event->ticket_price;
            $cal_event['all_day']= $event->all_day;
            $cal_event['performer']= $event->performer;
            $cal_event['venue']= $event->venue;
            $cal_event['event_type']= $event->event_type;
            $cal_event['status']= $event->status;
            $cal_event= apply_filters('ep_admin_calendar_event',$cal_event,$event);
            $response['event'] = $cal_event;
            if($old_status == 'draft' && $new_status == 'publish' && $event->user_submitted == 1) {
                $response['status_changed'] = true;
                do_action( 'event_magic_after_approved_submitted_event', $event );
            } else {
                $response['status_changed'] = false;
            }
        }
        wp_send_json_success($response);
    }

    public function save_dropped_event(){
        $em_event_nonce = event_m_get_param('em_event_nonce');
        if( empty( $em_event_nonce ) || !wp_verify_nonce( $em_event_nonce, 'em_event_object_nonce' ) ) {
            $error_msg = esc_html__( 'Unauthorized Access', 'eventprime-event-calendar-management' );
            wp_send_json_error( array( 'errors' => array( $error_msg ) ) );
        }
        $id = absint(event_m_get_param('id'));
        $event = $this->services['event']->load_model_from_db($id);
        $user_allow_save = 1;
        if( empty( em_check_context_user_capabilities( array( 'edit_events' ) ) ) ) {
            $user_allow_save = 0;
        }
        if( empty( em_check_context_user_capabilities( array( 'edit_others_events' ) ) ) ) {
            if( $event->user != get_current_user_id() ) {
                $user_allow_save = 0;
            }
        }
        // if user not allow to save then through error
        if( $user_allow_save == 0 ) {
            $error_msg = esc_html__( 'You have no permission.', 'eventprime-event-calendar-management' );
            wp_send_json_error( array( 'errors' => array( $error_msg ) ) );
        }
        // datepicker format from global settings
        $datepicker_format_arr = explode('&', em_global_settings('datepicker_format'));
        $datepicker_format = '';
        if(!empty($datepicker_format_arr) && isset($datepicker_format_arr[1])){
            $datepicker_format = $datepicker_format_arr[1] . ' H:i';
        }
        $event->start_date= em_timestamp(event_m_get_param('start_date'));
        $event->end_date= em_timestamp(event_m_get_param('end_date'));
        $event->start_booking_date= em_get_local_timestamp();
        $event->last_booking_date= $event->start_date;
        $event_id = $this->services['event']->save_popup_event($event);
        $event= $this->services['event']->load_model_from_db($event_id);
        $cal_event= array();
        $cal_event['id']= $event->id;
        $cal_event['title']= $event->name;
        $cal_event['start']= date('c',$event->start_date);
        $cal_event['start_booking_date']= $event->start_booking_date;
        $cal_event['end']= date('c',$event->end_date);
        $cal_event['last_booking_date']= $event->last_booking_date;
        $cal_event['enable_booking']= $event->enable_booking;
        $cal_event['ticket_price']= $event->ticket_price;
        $cal_event['allDay']= $event->all_day;
        $cal_event['performer']= $event->performer;
        $cal_event['venue']= $event->venue;
        $cal_event['event_type']= $event->event_type;
        $cal_event['status']= $event->status;
        $cal_event['popup']= $this->services['event']->admin_event_hover_popup($event);
        $cal_event = apply_filters('ep_admin_calendar_event_after_drop',$cal_event,$event);
        $redirect = admin_url('admin.php?page=em_dashboard&post_id='.$event->id);
        $em = event_magic_instance(); 
        if(in_array('recurring_events',$em->extensions) && !empty(em_get_child_events($event->id))) {
            wp_send_json_success(array('event'=>$cal_event,'redirect'=>$redirect,'reload'=>true));
        } else {
            wp_send_json_success(array('event'=>$cal_event,'redirect'=>$redirect,'reload'=>false));
        }
    }
    
    public function save_popup_event(){
        $em_event_nonce = event_m_get_param('em_event_nonce');
        if( empty( $em_event_nonce ) || !wp_verify_nonce( $em_event_nonce, 'em_event_object_nonce' ) ) {
            $error_msg = esc_html__( 'Unauthorized Access', 'eventprime-event-calendar-management' );
            wp_send_json_error( array( 'errors' => array( $error_msg ) ) );
        }
        $id = absint(event_m_get_param('id'));
        $event = $this->services['event']->load_model_from_db($id);
        $user_allow_save = 1;
        if( empty( $id ) ) { // check for create events capability
            if( empty( em_check_context_user_capabilities( array( 'create_events' ) ) ) ) {
                $user_allow_save = 0;
            }
        } else{
            if( empty( em_check_context_user_capabilities( array( 'edit_events' ) ) ) ) {
                $user_allow_save = 0;
            }
            if( empty( em_check_context_user_capabilities( array( 'edit_others_events' ) ) ) ) {
                if( $event->user != get_current_user_id() ) {
                    $user_allow_save = 0;
                }
            }
        }
        // if user not allow to save then through error
        if( $user_allow_save == 0 ) {
            $error_msg = esc_html__( 'You have no permission.', 'eventprime-event-calendar-management' );
            wp_send_json_error( array( 'errors' => array( $error_msg ) ) );
        }
        $old_status = $event->status;
        $errors = array();
        // Data validation
        $name = htmlspecialchars_decode(event_m_get_param('title'));
        if(empty($name)){
            array_push($errors, esc_html__('Event title can not be empty.','eventprime-event-calendar-management'));
        }
        // datepicker format from global settings
        $datepicker_format = 'm/d/Y';
        $datepicker_format_arr = explode('&', em_global_settings('datepicker_format'));
        if(!empty($datepicker_format_arr) && isset($datepicker_format_arr[1])){
            $datepicker_format = $datepicker_format_arr[1] . ' H:i';
        }
        $start_date = em_timestamp(event_m_get_param('start_date'));
        $end_date = em_timestamp(event_m_get_param('end_date'));
        if(empty($start_date) || empty($end_date)){
            array_push($errors, esc_html__('Invalid event dates.','eventprime-event-calendar-management'));
        }
        if($end_date <= $start_date){
            array_push($errors, esc_html__('Start date should be prior to End date.','eventprime-event-calendar-management'));
        }
        if (!empty($errors)) {
            wp_send_json_error(array('errors' => $errors));
        }
        
        $event_id = $this->services['event']->save_popup_event($event);
        $event = $this->services['event']->load_model_from_db($event_id);
        $new_status = $event->status;
        $cal_event = array();
        $cal_event['id']                 = $event->id;
        $cal_event['title']              = $event->name;
        $cal_event['start']              = date('c',$event->start_date);
        $cal_event['start_booking_date'] = $event->start_booking_date;
        $cal_event['end']                = date('c',$event->end_date);
        $cal_event['last_booking_date']  = $event->last_booking_date;
        $cal_event['enable_booking']     = $event->enable_booking;
        $cal_event['ticket_price']       = $event->ticket_price;
        $cal_event['all_day']            = $event->all_day;
        $cal_event['performer']          = $event->performer;
        $cal_event['venue']              = $event->venue;
        $cal_event['event_type']         = $event->event_type;
        $cal_event['status']             = $event->status;
        $cal_event['organizer']          = $event->organizer;
        $cal_event['cover_image_id']     = $event->cover_image_id;
        $cal_event['popup']              = $this->services['event']->admin_event_hover_popup($event);
        $cal_event = apply_filters('ep_admin_calendar_event',$cal_event,$event);
        $redirect = admin_url('admin.php?page=em_dashboard&post_id='.$event->id);
        $em = event_magic_instance();
        $response = array(
            'event'    => $cal_event,
            'redirect' => $redirect
        );
        
        if($old_status == 'draft' && $new_status == 'publish' && $event->user_submitted == 1) {
            $response['status_changed'] = true;
            do_action( 'event_magic_after_approved_submitted_event', $event );
        } else {
            $response['status_changed'] = false;
        }
        
        if(in_array('recurring_events',$em->extensions) && !empty(em_get_child_events($event->id))) {
            $response['reload'] = true;
        } else {
            $response['reload'] = false;
        }
        
        wp_send_json_success($response);
    }
    
    function delete_posts() {
        $em_delete_nonce = event_m_get_param('em_delete_nonce');
        $allow_post_delete = 1;
        if( empty( $em_delete_nonce ) ) {
            $allow_post_delete = 0;
        } else{
            $request_type = event_m_get_param('request_type');
            if( $request_type == 'events' ) {
                if( ! wp_verify_nonce( $em_delete_nonce, 'em_event_object_nonce' ) ) {
                    $allow_post_delete = 0;
                }
            }
        }
        if( $allow_post_delete == 0 ) {
            $error_msg = esc_html__( 'Unauthorized Access', 'eventprime-event-calendar-management' );
            wp_send_json_error( array( 'errors' => array( $error_msg ) ) );
        }

        $ids = $this->request->get_param('ids');
        if(!empty($ids)){
            if(!is_array($ids)){
                $ids = explode(',', $ids);
            }
            foreach ($ids as $id) {
                $post = get_post($id);
                if($post->post_type == 'em_booking' && $post->post_status == 'completed'){
                    $response = new stdClass();
                    $response->error = true;
                    $response->message = esc_html__("Booking with Completed status will not be  delete directly. First Cancel the Booking and then try again", 'eventprime-event-calendar-management');
                    echo json_encode($response);
                    wp_die();
                }
                $child_posts = get_posts(
                    array(
                        'post_parent' => $id,
                        'post_type' => EM_EVENT_POST_TYPE,
                        'status' => 'any'
                    )
                );
                if(!empty($child_posts)) {
                    foreach ($child_posts as $child_post) {
                        wp_delete_object_term_relationships($child_post->ID, array(EM_EVENT_VENUE_TAX, EM_EVENT_TYPE_TAX));
                        wp_delete_post($child_post->ID);
                    }
                }
                wp_delete_object_term_relationships($id, array(EM_EVENT_VENUE_TAX, EM_EVENT_TYPE_TAX));
                //wp_delete_post($id,true);
                $post = array(
                    'ID' => $id,
                    'post_status' => 'trash',
                );
                wp_update_post($post);
            }
        }
        wp_die();
    }

    function delete_terms() {
        $em_delete_nonce = $this->request->get_param('em_delete_nonce');
        $allow_post_delete = 1;
        if( empty( $em_delete_nonce ) ) {
            $allow_post_delete = 0;
        } else{
            $request_type =  sanitize_text_field( $this->request->get_param('request_type') );
            if( $request_type == 'event_type' ) {
                if( ! wp_verify_nonce( $em_delete_nonce, 'em_event_type_object_nonce' ) ) {
                    $allow_post_delete = 0;
                }
            }
        }
        if( $allow_post_delete == 0 ) {
            $error_msg = esc_html__( 'Unauthorized Access', 'eventprime-event-calendar-management' );
            wp_send_json_error( array( 'errors' => array( $error_msg ) ) );
        }
        
        $tax_type= $this->request->get_param('tax_type');
        $ids=$this->request->get_param('ids');
        if(!empty($ids)){
            if(!is_array($ids)){
                $ids = explode(',', $ids);
            }
            foreach ($ids as $id) {
                $posts = em_get_attached_posts($id, $tax_type);
                foreach ($posts as $post):
                    $p = array(
                        'ID' => $post->ID,
                        'post_status' => 'trash'
                    );
                    wp_update_post($p);
                endforeach;
                wp_delete_term($id, $tax_type);
            }
        }
        $response = new stdClass();
        $response->reload = true;
        echo json_encode($response);
        wp_die();
    }

    function duplicate_posts() {
        $em_duplicate_nonce = event_m_get_param('em_duplicate_nonce');
        $allow_post_duplicate = 1;
        if( empty( $em_duplicate_nonce ) ) {
            $allow_post_duplicate = 0;
        } else{
            $request_type = event_m_get_param('request_type');
            if( $request_type == 'events' ) {
                if( ! wp_verify_nonce( $em_duplicate_nonce, 'em_event_object_nonce' ) ) {
                    $allow_post_duplicate = 0;
                }
            }
        }
        if( $allow_post_duplicate == 0 ) {
            $error_msg = esc_html__( 'Unauthorized Access', 'eventprime-event-calendar-management' );
            wp_send_json_error( array( 'errors' => array( $error_msg ) ) );
        }
        $ids = $this->request->get_param('ids');
        if(!empty($ids)){
            if(!is_array($ids)){
                $ids = explode(',', $ids);
            } 
            $new_post_id = 0;
            foreach ($ids as $key =>  $id) {
                $post = get_post($id);
                $current_user = wp_get_current_user();
                $new_post_author = $current_user->ID;
                if (isset($post) && $post != null) {
                    $args = array(
                        'comment_status' => $post->comment_status,
                        'ping_status' => $post->ping_status,
                        'post_author' => $new_post_author,
                        'post_content' => $post->post_content,
                        'post_excerpt' => $post->post_excerpt,
                        'post_name' => $post->post_name,
                        'post_parent' => $post->post_parent,
                        'post_status' => $post->post_status,
                        'post_title' => $post->post_title,
                        'post_type' => $post->post_type,
                        'menu_order' => $post->menu_order
                    );
                    $new_post_id = wp_insert_post($args);
                    // Copying Event Type taxonomy
                    $event_type_term = wp_get_object_terms($id, EM_EVENT_TYPE_TAX, array('fields' => 'slugs'));
                    if (!empty($event_type_term))
                        wp_set_object_terms($new_post_id, $event_type_term, EM_EVENT_TYPE_TAX, false);

                    // Copying Venue taxonomy
                    $venue_term = wp_get_object_terms($id, EM_EVENT_VENUE_TAX, array('fields' => 'slugs'));
                    if (!empty($venue_term))
                        wp_set_object_terms($new_post_id, $venue_term, EM_EVENT_VENUE_TAX, false);

                    /*
                     * duplicate all post meta just
                     */
                    $data = get_post_custom($id);
                    foreach ($data as $key => $values) {
                        if ($key == em_append_meta_key('seats')) {
                            $event_service = EventM_Factory::get_service('EventM_Service');
                            $event_service->create_seats_from_venue($new_post_id, $event_service->get_venue($id));
                            continue;
                        }
                        if (in_array($key, em_append_meta_key(array('booked_seats')))) {
                            continue;
                        }
                        foreach ($values as $value) {
                            add_post_meta($new_post_id, $key, maybe_unserialize($value));
                        }
                    }
                }
            }
            
        }
        wp_die();
    }

    function duplicate_terms() {
        $this->check_permission();
        em_duplicate_terms(event_m_get_param('ids'), event_m_get_param('tax_type'));
    }

    function save_global_settings() {
        $this->check_permission();
        $model = $this->request->map_request_to_model('EventM_Global_Settings_Model');
        $model= apply_filters('event_magic_gs_before_save',$model); 
        $this->services['setting']->save($model);
        do_action('em_after_gs_save');
        wp_send_json_success();
    }

    function save_booking() {
        $booking_id = absint($this->request->get_param('id'));
        $booking= $this->services['booking']->load_model_from_db($booking_id);
        $note= sanitize_text_field($this->request->get_param('note'));
        if(!empty($note)){
            $booking->notes[]= $note;
        }

        $booking= $this->services['booking']->save($booking);
        wp_send_json_success(array('post'=>$booking));
    }

    function cancel_booking() {
        $service = EventM_Factory::get_service('EventM_Booking_Service');
        $post_id = event_m_get_param('id', true);
        $response = $service->refund_booking($post_id);
        echo json_encode($response);
        die;
    }

    function load_event_dates() {
        // Get all the events dates
        $data = new stdClass();
        $data->start_dates = array();
        $data->event_ids = array();

        $event_dao = new EventM_Event_DAO();
        $events = $event_dao->get_upcoming_events_calendar();

        $event_service = EventM_Factory::get_service('EventM_Service');

        if(is_array($events)){
            foreach ($events as $event){
                $start_date = date('Y-m-d', em_get_post_meta($event->ID, 'start_date', true));
                $end_date = date('Y-m-d', em_get_post_meta($event->ID, 'end_date', true));

                if (!empty($start_date)){
                    preg_match('/[0-9]{4}\-[0-9]{1,2}-[0-9]{1,2}/', $start_date, $matches);
                    if (count($matches) > 0 && !empty($matches[0])) {

                        if (strtotime($matches[0]) <= strtotime(date('Y-m-d')) &&
                                strtotime($end_date) >= strtotime(date('Y-m-d'))):
                            $data->start_dates[] = date("Y-m-d");

                        else:
                            $data->start_dates[] = $matches[0];
                        endif;

                        $data->event_ids[] = $event->ID;
                    }

                }
            }
        }

        echo json_encode($data);
        die;
    }

    function load_venue_addresses() {
        $venue_service = EventM_Factory::get_service('EventM_Venue_Service');
        $ids = !empty($_POST['venue_id']) ? sanitize_text_field($_POST['venue_id']) : '';
        $mapData['address'] = $mapData['zoom_level'] = '';
        $addresses = $zoomLevels = array();
        if(!empty($ids)){
            $ids= explode(',', $ids);
            foreach($ids as $venue_id){
                $venue= $venue_service->load_model_from_db($venue_id);
                if(!empty($venue->id) && !empty($venue->address)){
                    array_push($addresses,$venue->address);
                    if(isset($venue->zoom_level) && !empty($venue->zoom_level)){
                        array_push($zoomLevels,$venue->zoom_level);
                    }
                }
            }
        }
        else{
            $venues= $venue_service->get_venues();
            foreach($venues as $venue){
                if(!empty($venue->id) && !empty($venue->address)){
                    array_push($addresses,$venue->address);
                    if(isset($venue->zoom_level) && !empty($venue->zoom_level)){
                        array_push($zoomLevels,$venue->zoom_level);
                    }
                }
            }
        }
        $mapData['address'] = $addresses;
        $mapData['zoom_level'] = $zoomLevels;
        echo json_encode($mapData);
        die;
    }

    function register_user() {
        $user_service = EventM_Factory::get_service('EventM_User_Service');
        $user_service->register_user();
    }

    function login_user() {
        $user_service = EventM_Factory::get_service('EventM_User_Service');
        $user_service->login_user();
    }

    function book_seat() {
        $data = $this->services['booking']->book_seat();
        echo json_encode($data);
        die;
    }

    public function load_payment_configuration() {
        $response = new stdClass();
        $response->payment_prcoessor = array();
        $response->is_payment_configured = em_is_payment_gateway_enabled();
        $event_service= $this->services['event'];
        $setting = $this->services['setting']->load_model_from_db();
        $event_id = $this->request->get_param('event_id');
        $event= $event_service->load_model_from_db($event_id);
        if(empty($event->id)){
            die;
        }
        
        $response->event_id = $event->id;
        $response->ticket_price = $event->ticket_price;
        $response->currency_symbol = em_currency_symbol();
        $currency_code = em_global_settings('currency');
        $response->currency_code = $currency_code;
        if (!empty($setting->paypal_processor)) {
            $paypal_email = $setting->paypal_email;
            if (!empty($paypal_email)) {
                $response->payment_prcoessor['paypal'] = array();
                $response->enable_modern_paypal = $setting->modern_paypal;
            }
        }
        $response = apply_filters('event_magic_load_payment_configuration', $response);
        // getting index name of array
        if(count($response->payment_prcoessor) > 0):
            $firstKey = array_keys($response->payment_prcoessor)[0];
            $response->selected_payment_method = $firstKey;
        endif;
        
        if (!count($response->payment_prcoessor))
            $response->is_payment_configured = false;
        echo json_encode($response);
        die;
    }

    public function update_booking() {
        $response = new stdClass();
        $booking_service = EventM_Factory::get_service('EventM_Booking_Service');
        $event_service = EventM_Factory::get_service('EventM_Service');
        $order_id = event_m_get_param("order_id", true);
        $event_id = event_m_get_param("event_id", true);
        if (empty($order_id)) {
            $response->updated = false;
        }

        $order_info = em_get_post_meta($order_id, 'order_info', true);

        if (!empty($order_info)) {
            $order_info['quantity'] = event_m_get_param('quantity');
            $order_info['discount'] = event_m_get_param('discount');

            // Removing previous quantity data
            $prev_order_info = em_get_post_meta($order_id, 'order_info', true);
            //$pre_booked_seats = (int) em_get_post_meta($event_id, 'booked_seats', true);
            $pre_booked_seats = $event_service->booked_seats($event_id);
            $event_service->update_booked_seats($event_id, $pre_booked_seats - $prev_order_info['quantity']);


            if (!$booking_service->check_booking_availability($event_id, $order_info)) {
                // Removing quantity from order info as well
                $order_info['quantity'] = 0;
                em_update_post_meta($order_id, 'order_info', $order_info);
                $error = new WP_Error('error_capacity', __("Booking can't be done as no seats are available.", 'eventprime-event-calendar-management'));
                echo json_encode($error);
                die;
            }
            $current_booked_seats = $pre_booked_seats - $prev_order_info['quantity'];
            $event_service->update_booked_seats($event_id, $current_booked_seats + $order_info['quantity']);


            em_update_post_meta($order_id, 'order_info', $order_info);
            $response->updated = true;
        }

        echo json_encode($response);
        die;
    }

    public function print_ticket() {
        $booking_id = absint(event_m_get_param('booking_id'));
        $seat_no = event_m_get_param('seat_no');
        $booking = $this->services['booking']->load_model_from_db($booking_id);
        if(empty($booking->id)){
            _e("No Booking with such ID",'eventprime-event-calendar-management');
            wp_die();
        }
        $ticket_html = EventM_Print::front_ticket($booking,'',$seat_no);
        echo $ticket_html;
        die;
    }

    public function download_booking_details() {
        $booking_id = absint(event_m_get_param('booking_id'));
        $booking = $this->services['booking']->load_model_from_db($booking_id);
        if(!empty($booking->id)){
            EventM_Print::details($booking);
        }
        die;
    }

    public function event_details() {
        $response = new stdClass();
        $event_id = event_m_get_param('element_id');
        $id = get_post($event_id);
        $response->title = $id->post_title;

        $terms = wp_get_post_terms($id->ID, EM_VENUE_TYPE_TAX);
        if (!empty($terms) && count($terms) > 0):
            $venue = $terms[0];
            $venue_address = em_get_term_meta($venue->term_id, 'address', true);
            $response->address = $venue_address;
        endif;

        $booking_seats = EventM_Factory::get_service('EventM_Booking_Service');
        $my_querys = $booking_seats->get_seats($event_id);

        foreach ($my_querys as $data):
            $id = $data->ID;
            $seat = get_post_meta($id, 'em_order_info');
            foreach ($seat as $data):
                $seat_sequence = $data['seat_sequences'];
                $s = implode(',', $seat_sequence);
                $response->seats = $s;
            endforeach;

            $dates = get_post($id, 'post_date');
            $response->booking_date = $dates->post_date;
        endforeach;

        echo json_encode($response);
        die;
    }

    public function show_booking_details() {
        $user = wp_get_current_user();
        if(empty($user))
            return;
        
        $booking_id = absint(event_m_get_param('id'));
        $booking= $this->services['booking']->load_model_from_db($booking_id);
        if (empty($booking->id))
            die("No such booking exists");
        
        // Check if this booking belongs to the same user
        if ($booking->user != $user->ID)
            die("User not authorized");
        
        $event= $this->services['event']->load_model_from_db($booking->event);
        if(!empty($event->venue)){
            $venue= $this->services['venue']->load_model_from_db($event->venue);
            if(empty($venue->id)){
                $venue= null;
            }
        }
        // Load view file
        include_once('templates/booking_details.php');
        die;
    }

    public function cancel_booking_by_user() {
        $response = new stdClass();
        $response->error = true;
        
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            $booking_id = event_m_get_param('post_id', true);
            $booking = $this->services['booking']->load_model_from_db($booking_id);
            
            if (empty($booking->id))
                die("No such booking exists");
            
            // Booking can not be refunded.
            if ($booking->status == 'cancelled' || $booking->status == 'refunded') {
                echo json_encode(array());
                return;
            }

            // Check if this booking belongs to the same user
            if ($user->ID != $booking->user)
                die("User not authorized");
            
            $this->services['booking']->revoke_seats($booking_id);

            // Changing booking status
            $booking->status= 'cancelled';
            $booking= $this->services['booking']->save($booking);
            $order_info = $booking->order_info;
            if (!empty($booking)) {
                $response->error = false;
                $response->status = EventM_Constants::$status[$booking->status];
                $response->status_message = 'Booking '. EventM_Constants::$status[$booking->status]. ' Successfully!';
            }
            do_action('event_magic_booking_cancelled',$booking);
        }
        echo json_encode($response);
        die;
    }

    public function get_venue_capcity() {
        $response = new stdClass();
        $venue_id = event_m_get_param('venue_id');
        $event_id = event_m_get_param('event_id');

        $service = EventM_Factory::get_service('EventM_Venue_Service');
        $venue_data = $service->load_model_from_db($venue_id);
        
        $response->capacity = (int) $service->capacity($venue_id);
        $response->seats = $service->get_seats($venue_id, $event_id);
        // venue standing capacity
        $response->stand_capacity = (int) $service->stand_capacity($venue_id);
        $response->venue_type = $venue_data->type;
        echo json_encode($response);
        die;
    }

    public function verify_booking() {
        $booking_id = event_m_get_param('booking_id', true);
        $booking = get_post($booking_id);
        if (empty($booking)){
            wp_send_json_error();
        }
        else{
            $data['gateway'] = event_m_get_param("gateway", true);
            $em = event_magic_instance();
            if(in_array('coupons', $em->extensions)){
                $data['coupon_code'] = event_m_get_param("coupon_code", true);
                if(!empty($data['gateway']) && $data['gateway'] == 'paypal' && !empty($data['coupon_code'])){
                    $data['coupon_discount'] = event_m_get_param("coupon_discount", true);
                    $data['coupon_amount'] = event_m_get_param("coupon_amount", true);
                    $data['coupon_type'] = event_m_get_param("coupon_type", true);
                    $booking_service = EventM_Factory::get_service('EventM_Booking_Service');
                    $booking = $booking_service->load_model_from_db($booking_id);
                    $booking->order_info['coupon_code'] = $data['coupon_code'];
                    $booking->order_info['coupon_discount'] = $data['coupon_discount'];
                    $booking->order_info['coupon_amount'] = $data['coupon_amount'];
                    $booking->order_info['coupon_type'] = $data['coupon_type'];
                    $booking->order_info['payment_gateway'] = $data['gateway'];
                    $bookingDao = new EventM_Booking_DAO();
                    $booking = $bookingDao->save($booking);
                }
            }
            // custom extension action on paypal data verification
            if(!empty($data['gateway']) && $data['gateway'] == 'paypal'){
                $all_order_data = $_POST['all_order_data'];
                $booking_service = EventM_Factory::get_service('EventM_Booking_Service');
                $booking = $booking_service->load_model_from_db($booking_id);
                $booking->order_info['payment_gateway'] = $data['gateway'];
                $booking = apply_filters('event_magic_paypal_data_verify_booking',$booking, $all_order_data);
                $bookingDao = new EventM_Booking_DAO();
                $booking = $bookingDao->save($booking);
                /* check if booking sms is enabled for this event */
                $is_sms_enabled = (int) em_get_post_meta( $booking->event, 'is_sms_enabled', true);
                if( isset( $is_sms_enabled ) && $is_sms_enabled == 1 ){
                    do_action( 'event_magic_send_booking_sms', $booking );
                }
            }
            wp_send_json_success();
        }
    }

    public function export_bookings() {
        $selections= event_m_get_param('selections');
        $bookings= array();
        if (!empty($selections)) {
            foreach ($selections as $id){
                array_push($bookings,$this->services['booking']->load_model_from_db($id));
            }
        }
        else{
            $bookings = $this->services['booking']->export_data();
        }
        $csv = $data = new stdClass();
        foreach ($bookings as $booking) {
            $user = get_user_by('id', $booking->user);
            $other_order_info = $booking->order_info;
            $csv = new stdClass();
            $csv->ID = $booking->id;
            if(!empty($user)){
                $csv->user_display_name = urldecode(rawurlencode($user->display_name));
                $csv->user_email = $user->user_email;
            }
            else{
                $csv->user_display_name = urldecode(rawurlencode($other_order_info['user_name']));
                $csv->user_email = $other_order_info['user_email'];
            }
            $event = $this->services['event']->load_model_from_db($booking->event);
            if(!empty($event->id)){
                $csv->event_name = urldecode(rawurlencode($event->name));
            }
            else{
                $csv->event_name = __('Event deleted','eventprime-event-calendar-management');
            }
            $csv->event_start_date = date_i18n(get_option('date_format'), $event->start_date);
            $csv->event_start_time = date_i18n(get_option('time_format'), $event->start_date);
            $csv->event_end_date = date_i18n(get_option('date_format'), $event->end_date);
            $csv->event_end_time = date_i18n(get_option('time_format'), $event->end_date);
            $csv->event_type_name = '-';
            if(!empty($event->event_type)){
                $event_type = $this->services['event_type']->load_model_from_db($event->event_type);
                if(!empty($event_type) && !empty($event_type->name)){
                    $csv->event_type_name = urldecode(rawurlencode($event_type->name));
                }
            }
            $csv->venue = '-';
            $csv->address = '-';
            $csv->seating_type = '-';
            if(!empty($event->venue)){
                $event_venue = $this->services['venue']->load_model_from_db($event->venue);
                if(!empty($event_venue)){
                    $csv->venue = urldecode(rawurlencode($event_venue->name));
                    $csv->seating_type = $event_venue->type;
                    $csv->address = $event_venue->address;
                }
            }
            $csv->attendee_name = '';
            if(!isset($other_order_info['is_custom_booking_field']) || empty($other_order_info['is_custom_booking_field'])){
                //$csv->attendee_name = implode(', ', $booking->attendee_names);
                $attData = '';$atts = 0;$ai = 1;
                if(count($booking->attendee_names) > 1){
                    $attData = 'Attendee ' . $ai . ':   ';
                }
                foreach($booking->attendee_names as $attendees){
                    $attData .= 'Name - ';
                    $attData .= $attendees;
                    break;
                }
                $csv->attendee_name = $attData;
            }
            else{
                $attData = '';$atts = 0;$ai = 1;
                if(count($booking->attendee_names) > 1){
                    $attData = 'Attendee ' . $ai . ':   ';
                }
                foreach($booking->attendee_names as $attendees){
                    $ja = 0;
                    foreach($attendees as $label => $value){
                        $attData .= $label .' - '. $value;
                        ++$ja;
                        if($ja < count($attendees)){
                            $attData .= ' , ';
                        }
                    }
                    break;
                }
                $csv->attendee_name = $attData;
            }
            $csv->seat_sequences = '-';
            if(isset($other_order_info['seat_sequences']) && !empty($other_order_info['seat_sequences'])){
                $csv->seat_sequences = implode(',', $other_order_info['seat_sequences']);
            }
            $csv->currency = em_currency_symbol();
            $orderPrice = (isset($other_order_info['subtotal']) ? $other_order_info['subtotal'] : $other_order_info['item_price']);
            $csv->price = $orderPrice;
            $csv->no_tickets = $other_order_info['quantity'];
            $amount_total = (isset($other_order_info['subtotal']) ? $other_order_info['subtotal'] : $other_order_info['quantity'] *  $other_order_info['item_price']);
            $csv->amount_total = $amount_total;
            $csv->fixed_event_price = (!empty($other_order_info['fixed_event_price']) ? $other_order_info['fixed_event_price'] : '-');
            $csv->discount = '-';$discount = 0;
            if(isset($other_order_info['discount']) && is_numeric($other_order_info['discount'])){
                $discount += $other_order_info['discount'];
            }
            if(isset($other_order_info['coupon_discount']) && is_numeric($other_order_info['coupon_discount'])){
                $discount += $other_order_info['coupon_discount'];
            }
            if(isset($other_order_info['ebd_discount_amount']) && is_numeric($other_order_info['ebd_discount_amount'])){
                $discount += $other_order_info['ebd_discount_amount'];
            }
            $csv->discount = (!empty($discount) ? $discount : '-');
            $amount_received = $amount_total - $discount;
            if(isset($other_order_info['fixed_event_price']) && !empty($other_order_info['fixed_event_price'])){
                $amount_received += $other_order_info['fixed_event_price'];
            }
            //$amount_received = apply_filters('event_magic_view_attendee_amount_received', $amount_received, $booking);
            $csv->amount_received = $amount_received;
            $payment_log = $booking->payment_log;
            $csv->payment_gateway = '';
            if(!empty($payment_log) && isset($payment_log['payment_gateway'])){
                $csv->payment_gateway = ucfirst($payment_log['payment_gateway']);
            }
            $csv->booking_status = $booking->status;
            $csv->payment_status = (isset($payment_log['offline_status']) ? ucfirst($payment_log['offline_status']) : '');
            if (isset($payment_log['payment_gateway']) && ($payment_log['payment_gateway'] == 'paypal' || $payment_log['payment_gateway'] == 'stripe' )){
                $csv->payment_status = ucfirst($payment_log['payment_status']);
            }
            $csv->payment_log = '-';$transactions = array();
            if(!empty($payment_log)){
                $except = array('multi_price_option_data', 'coupon_code', 'coupon_discount', 'coupon_amount', 'coupon_type', 'applied_ebd', 'ebd_id', 'ebd_name', 'ebd_rule_type', 'ebd_discount_type', 'ebd_discount', 'ebd_discount_amount');
                if(!empty($payment_log)){
                    foreach($payment_log as $logs_key => $logs){
                        if(in_array($logs_key, $except)){
                            unset($payment_log[$logs_key]);
                        }
                    }
                }
                $csv->payment_log = serialize($payment_log);
            }
            /* guest booking data */
            $csv->guest_booking_data = '';
            if(isset($booking->order_info['guest_booking_custom_data']) && !empty($booking->order_info['guest_booking_custom_data'])){
                $guestBookingData = array();
                foreach($booking->order_info['guest_booking_custom_data'] as $key => $value) {
                    foreach( $value as $k => $v) {        
                        $guestBookingData[] = $k.' - '.$v->value;
                    }            
                } 
                $csv->guest_booking_data = implode( ', ', $guestBookingData );
            }
            $data->posts[] = $csv;

            // check for multiple attendees
            if(count($booking->attendee_names) > 1){
                $blank_row = array("ID", "user_display_name", "user_email", "event_name", "event_start_date", "event_start_time", "event_end_date", "event_end_time", "event_type_name", "venue", "address", "seating_type", "attendee_name", "seat_sequences", "currency", "price", "no_tickets", "amount_total", "fixed_event_price", "discount", "amount_received", "payment_gateway", "booking_status", "payment_status", "payment_log");
                if(isset($other_order_info['is_custom_booking_field']) && !empty($other_order_info['is_custom_booking_field'])){
                    $atts = 0;$ai = 2;$rowNo = 1;
                    $attData = 'Attendee ' . $ai . ':   ';
                    foreach($booking->attendee_names as $attendees){
                        if( $rowNo == 1 ){
                            $rowNo++;
                            continue;
                        }
                        $ja = 0;
                        if($rowNo > 2 && $atts < count($booking->attendee_names)){
                            $attData = 'Attendee ' . $ai . ':   ';
                        }
                        foreach($attendees as $label => $value){
                            $attData .= $label .' - '. $value;
                            ++$ja;
                            if($ja < count($attendees)){
                                $attData .= ' , ';
                            }
                        }
                        ++$atts;$ai++;$rowNo++;

                        $csv = new stdClass();
                        foreach ($blank_row as $bkey => $bvalue) {
                            if($bvalue == "attendee_name"){
                                $csv->attendee_name = $attData;
                            }else{
                                $csv->{$bvalue} = '';
                            }
                        }
                        $data->posts[] = $csv;
                    }
                } else{
                    $atts = 0;$ai = 2;$rowNo = 1;
                    $attData = 'Attendee ' . $ai . ':   ';
                    foreach($booking->attendee_names as $attendees){
                        if( $rowNo == 1 ){
                            $rowNo++;
                            continue;                        
                        }
                        $ja = 0;
                        if($rowNo > 2 && $atts < count($booking->attendee_names)){
                            $attData = 'Attendee ' . $ai . ':   ';
                        }
                        $attData .= 'Name - ';
                        $attData .= $attendees;
                        ++$atts;$ai++;$rowNo++;

                        $csv = new stdClass();
                        foreach ($blank_row as $bkey => $bvalue) {
                            if($bvalue == "attendee_name"){
                                $csv->attendee_name = $attData;
                            }else{
                                $csv->{$bvalue} = '';
                            }
                        }
                        $data->posts[] = $csv;
                    }
                }
            }
        }

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="export.csv"');
        header('Cache-Control: max-age=0');
        $csv_name = 'em_Bookings' . time() . mt_rand(10, 1000000);
        $csv_path = get_temp_dir() . $csv_name . '.csv';
        $csv = fopen('php://output', "w");
        if (!$csv) {
            return false;
        }
        //Add UTF-8 header for proper encoding of the file
        fputs($csv, chr(0xEF) . chr(0xBB) . chr(0xBF));
        $csv_fields = array();
        $csv_fields[] = __('Booking ID', 'eventprime-event-calendar-management');
        $csv_fields[] = __('User Name', 'eventprime-event-calendar-management');
        $csv_fields[] = __('Email', 'eventprime-event-calendar-management');
        $csv_fields[] = __('Event Name', 'eventprime-event-calendar-management');
        $csv_fields[] = __('Start Date', 'eventprime-event-calendar-management');
        $csv_fields[] = __('Start Time', 'eventprime-event-calendar-management');
        $csv_fields[] = __('End Date', 'eventprime-event-calendar-management');
        $csv_fields[] = __('End Time', 'eventprime-event-calendar-management');
        $csv_fields[] = __('Event Type', 'eventprime-event-calendar-management');
        $csv_fields[] = __('Venue', 'eventprime-event-calendar-management');
        $csv_fields[] = __('Address', 'eventprime-event-calendar-management');
        $csv_fields[] = __('Seating Type', 'eventprime-event-calendar-management');
        $csv_fields[] = __('Attendees', 'eventprime-event-calendar-management');
        $csv_fields[] = __('Seat No.', 'eventprime-event-calendar-management');
        $csv_fields[] = __('Currency', 'eventprime-event-calendar-management');
        $csv_fields[] = __('Price', 'eventprime-event-calendar-management');
        $csv_fields[] = __('Ticket Count', 'eventprime-event-calendar-management');
        $csv_fields[] = __('Subtotal', 'eventprime-event-calendar-management');
        $csv_fields[] = __('Fixed Event Price', 'eventprime-event-calendar-management');
        $csv_fields[] = __('Discount', 'eventprime-event-calendar-management');
        $csv_fields[] = __('Amount Received', 'eventprime-event-calendar-management');
        $csv_fields[] = __('Payment Gateway', 'eventprime-event-calendar-management');
        $csv_fields[] = __('Booking Status', 'eventprime-event-calendar-management');
        $csv_fields[] = __('Payment Status', 'eventprime-event-calendar-management');
        $csv_fields[] = __('Transacton Log', 'eventprime-event-calendar-management');
        $csv_fields[] = __('Guest Booking Data', 'eventprime-event-calendar-management');
        fputcsv($csv, $csv_fields);
        foreach ($data->posts as $a) {
            if (!fputcsv($csv, array_values((array) $a))){
                return false;
            }
        }

        fclose($csv);
        wp_die();
    }

    public function check_bookable() {
        $event_id = event_m_get_param('event_id', true);
        $event_service = EventM_Factory::get_service('EventM_Service');
        $event = $event_service->load_model_from_db($event_id);

        if ($event->status == 'expired') {
            $error = new WP_Error('booking_expired', 'Booking expired.');
            echo json_encode($error);
        }

        if((isset($event->parent) && !empty($event->parent)) && (isset($event->enable_recurrence_automatic_booking) && !empty($event->enable_recurrence_automatic_booking))){
            $error = new WP_Error('booking_not_allowed', 'Booking not allowed.');
            echo json_encode($error);
        }
        
        $available_seats = $event_service->available_seats($event_id);
        if ($available_seats <= 0) {
            $error = new WP_Error('booking_finished', __('All the seats are booked.', 'eventprime-event-calendar-management'));
            echo json_encode($error);
        }
        wp_die();
    }

    public function confirm_booking_without_payment() {
        $response = new stdClass();
        $id = $this->request->get_param('booking_id', true);
        if(is_array($id) && !empty($id)){
            $id= $id[0];
        }
        $booking= $this->services['booking']->load_model_from_db($id);
        if(empty($booking->id)){
            wp_send_json_error();
        }
        $data['payment_gateway'] = 'none';
        $data['payment_status'] = 'completed';
        $data['total_amount'] = 0;
        $em = event_magic_instance();
        if(in_array('coupons', $em->extensions)){
            $data['coupon_code'] = $this->request->get_param('coupon_code', true);
            $data['coupon_discount'] = $this->request->get_param('coupon_discount', true);
            $data['coupon_amount'] = $this->request->get_param('coupon_amount', true);
            $data['coupon_type'] = $this->request->get_param('coupon_type', true);
        }
        $data = apply_filters('event_magic_add_without_payment_response', $data, $this->request->get_param('all_order_data', true));
        $this->services['booking']->confirm_booking($id, $data);
        //do_action('event_magic_booking_confirmed',$booking);
        /* check if booking sms is enabled for this event */
        $is_sms_enabled = (int) em_get_post_meta( $booking->event, 'is_sms_enabled', true);
        if( isset( $is_sms_enabled ) && $is_sms_enabled == 1 ){
            do_action('event_magic_send_booking_sms', $booking );
        }
        $response->redirect = add_query_arg(array('em_bookings'=>$id), get_permalink(em_global_settings('profile_page')));
        if(!is_user_logged_in()){
            $showBookNowForGuestUsers = em_show_book_now_for_guest_users();
            $gs_service = EventM_Factory::get_service('EventM_Setting_Service');
            $gs = $gs_service->load_model_from_db();
            if(!empty($showBookNowForGuestUsers)){
                $response->guest_booking = 1;
                $response->redirect = add_query_arg(array('id' => $id, 'is_guest' => 1), get_permalink(em_global_settings('booking_details_page')));
                /* if(!empty($gs->guest_booking_page_redirect)){
                    $redirect_url = get_permalink($gs->guest_booking_page_redirect);
                    $response->redirect = add_query_arg(array('redirect_url' => esc_url($redirect_url)), $response->redirect);
                    $response->redirect = get_page_link( $gs->guest_booking_page_redirect );
                } */
            }
        }
        echo json_encode($response);
        wp_die();
    }

    public function rm_custom_datas() {
        $post_id = event_m_get_param('post_id');
        $user_id = em_get_post_meta($post_id, 'user', true);
        if(empty($user_id)){
            wp_die(); 
        }
        if (is_registration_magic_active()) {
            $html = "";

        echo em_rm_custom_data($user_id);
        } else {
            $current_user = get_user_by('ID', $user_id);
            ?>
            <div class="em-booking-row"><span class="em-booking-label"><?php _e('Name','eventprime-event-calendar-management'); ?>:</span><span class="em-booking-detail"><?php echo $current_user->display_name; ?></span></div>
            <div class="em-booking-row"><span class="em-booking-label"><?php _e('Registered On','eventprime-event-calendar-management'); ?>:</span><span class="em-booking-detail"><?php echo $current_user->user_registered; ?></span></div>
            <?php
        }
        wp_die();
    }

    public function resend_mail() {
        $this->check_permission();
        $booking_id = event_m_get_param('post_id');
        $booking_service = EventM_Factory::get_service('EventM_Booking_Service');
        $booking= $booking_service->load_model_from_db($booking_id);
        $user= get_user_by('ID',$booking->user);
        if(empty($user))
            return false;
        $new_user_password = wp_generate_password(5);
        wp_set_password($new_user_password,$user->ID);
        EventM_Notification_Service::reset_password_mail($booking,$new_user_password);
        die;
    }

    public function booking_cancellation_mail() {
        $booking_id = event_m_get_param('post_id');
        EventM_Notification_Service::booking_cancel($booking_id);
    }

    public function booking_confirm_mail() {
        $booking_id = event_m_get_param('post_id');
        EventM_Notification_Service::booking_confirmed($booking_id);
    }

    public function booking_refund_mail() {
        $booking_id = event_m_get_param('post_id');
        EventM_Notification_Service::booking_refund($booking_id);
    }

    public function booking_pending_mail() {
        $booking_id = event_m_get_param('post_id');
        EventM_Notification_Service::booking_pending($booking_id);
    }
    
    public function event_submitted_mail() {
        $event_id = absint(event_m_get_param('post_id'));
        EventM_Notification_Service::event_submitted($event_id);
        wp_send_json_success();
    }
    
    public function event_approved_mail() {
        $event_id = absint(event_m_get_param('id'));
        EventM_Notification_Service::event_approved($event_id);
        wp_send_json_success();
    }

    public function load_event_for_booking() {
        $em = event_magic_instance();
        if (!is_user_logged_in() && (!in_array('guest-booking', $em->extensions) || empty(em_global_settings('allow_guest_bookings')))){
            wp_die("User not logged in");
        }

        // Event's data
        $event_id = $this->request->get_param('event_id', true);
        $event_service = EventM_Factory::get_service('EventM_Service');
        $event = $event_service->load_model_from_db($event_id);
        $event->available_seats = $event_service->available_seats($event_id);
        $event->start_date= em_showDateTime($event->start_date, true);
        //event ticket price
        $event->ticket_price = apply_filters('event_magic_load_calender_ticket_price', $event->ticket_price, $event);
        $event->ticket_price_has_string = 0;
        if(!is_numeric($event->ticket_price)){
            $event->ticket_price_has_string = 1;
        }
        // Venue's data
        $venue_service = EventM_Factory::get_service('EventM_Venue_Service');
        $venue = $venue_service->load_model_from_db($event->venue);
        $venue->available_seats = $event_service->available_seats($event_id);

        // price option
        $price_option_data = $this->get_event_price_opion_data($event->id);
        if(empty($price_option_data)){
            $new_price_option = $this->services['event']->add_multi_price_option($event, $event->id);
            $price_option_data = $this->get_event_price_opion_data($event->id);
        }
        $event->price_option_data = $price_option_data;
        $podata = array();
        if(!empty($price_option_data)){
            foreach($price_option_data as $price_o_data){
                $podata[$price_o_data->id] = $price_o_data->name;
            }
        }

        // check if event's seats object has seat color option
        $event_seat = $event->seats;
        $soldSeats = 0;
        $row_wise_tier = array();$row_var_id = '';
        if(!empty($event_seat)){
            $updated_event_seat = array();
            foreach ($event_seat as $row_key => $row_value) {
                foreach ($row_value as $col_key => $col_value) {
                    if($col_key == 0 && isset($col_value->variation_id)){
                        if($row_var_id == ''){
                            $row_var_id = $col_value->variation_id;
                            if(isset($podata[$row_var_id])){
                                $row_wise_tier[$row_key] = $podata[$row_var_id];
                            }
                        }
                        if($col_value->variation_id != $row_var_id){
                            $row_var_id = $col_value->variation_id;
                            if(isset($podata[$row_var_id])){
                                $row_wise_tier[$row_key] = $podata[$row_var_id];
                            }
                        }
                    }
                    $term_seat_color = $venue->seat_color;
                    $term_booked_seat_color = $venue->booked_seat_color;
                    $term_reserved_seat_color = $venue->reserved_seat_color;
                    $term_selected_seat_color = $venue->selected_seat_color;
                    /*$col_value->seatColor = '';
                    $col_value->seatBorderColor = '';*/
                    if($col_value->type == 'general'){
                        if(isset($col_value->mainSeatBorderColor)){
                            $col_value->seatColor = $col_value->mainSeatColor;
                            $col_value->seatBorderColor = $col_value->mainSeatBorderColor;
                        }
                        else if($col_value->seatColor == '#null' && !empty($term_seat_color)){
                            $col_value->seatColor = '#'.$term_seat_color;
                            $col_value->seatBorderColor = '3px solid #'.$term_seat_color;
                        }
                        else if($col_value->seatColor == '#'.$term_selected_seat_color && !empty($term_seat_color)){
                            $col_value->seatColor = '#'.$term_seat_color;
                            $col_value->seatBorderColor = '3px solid #'.$term_seat_color;
                        }
                    }
                    if($col_value->type == 'sold' && !empty($term_booked_seat_color)){
                        $col_value->seatColor = '#'.$term_booked_seat_color;
                        $col_value->seatBorderColor = '3px solid #'.$term_booked_seat_color;
                        $soldSeats++;
                    }
                    if($col_value->type == 'reserve' && !empty($term_reserved_seat_color)){
                        $col_value->seatColor = '#'.$term_reserved_seat_color;
                        $col_value->seatBorderColor = '3px solid #'.$term_reserved_seat_color;
                    }
                    $updated_event_seat[$row_key][$col_key] = $col_value;
                }
            }
            if(!empty($updated_event_seat)){
                $event->seats = $updated_event_seat;
            }
        }
        // Unset seats info as it is already included in event's data
        unset($venue->seats);
        // Mergin both event and venue data
        $event->venue = $venue;

        $event->row_wise_tier = '';
        if($event->show_tier_name_on_booking == 1){
            $event->row_wise_tier = $row_wise_tier;
        }

        // check for booked seat confliction
        if($soldSeats > 0){
            if($soldSeats != $event->booked_seats){
                // it mean's there is some confliction with actualbooking and booked seat. Now first get booking and then get all seat sequence. If found any seat which is not in booking then update seat type 
                $args = array(
                    'numberposts' => -1,
                    'post_status'=> 'completed',
                    'post_type'=> EM_BOOKING_POST_TYPE,
                    'meta_key' => em_append_meta_key('event'),
                    'meta_value' => $event_id,
                );
                $booking_posts = get_posts($args);
                $booked_seats = array();
                foreach ($booking_posts as $post) {
                    $order_info = em_get_post_meta($post->ID, 'order_info');
                    if(!empty($order_info)){
                        foreach ($order_info as $infoData) {
                            $seatSequence = $infoData['seat_sequences'];
                            if(!empty($seatSequence)){
                                foreach ($seatSequence as $see) {
                                    $booked_seats[] = $see;
                                }
                            }
                        }
                    }
                }
                if(!empty($booked_seats)){
                    if(!empty($event_seat)){
                        $updated_event_seat = array();
                        foreach ($event_seat as $row_key => $row_value) {
                            foreach ($row_value as $col_key => $col_value) {
                                if($col_value->type == 'sold'){
                                    if(!in_array($col_value->seatSequence, $booked_seats)){
                                        $col_value->type == 'general';
                                        $col_value->seatColor = '#'.$term_seat_color;
                                        $col_value->seatBorderColor = '3px solid #'.$term_seat_color;
                                    }
                                }
                                if(in_array($col_value->seatSequence, $booked_seats) && $col_value->type == 'general'){
                                    $col_value->type == 'sold';
                                    $col_value->seatColor = '#'.$term_booked_seat_color;
                                    $col_value->seatBorderColor = '3px solid #'.$term_booked_seat_color;
                                }
                                $updated_event_seat[$row_key][$col_key] = $col_value;
                            }
                        }
                        if(!empty($updated_event_seat)){
                            $event->seats = $updated_event_seat;
                        }
                    }
                }
            }
        }
        /*get total bookings with complete status for standings type*/
        $get_bookings = $this->services['booking']->get_by_event( $event_id, array( 'numberposts' => -1 ) );
        $total_completed_bookings = 0;
        foreach ($get_bookings as $single_booking):
            if($single_booking->status == 'completed'):
                $total_completed_bookings = $total_completed_bookings + $single_booking->order_info['quantity'];
            endif; 
        endforeach;  

        $event->booked_standings = $total_completed_bookings;
        // check event available standing capacity
        $event->available_standings = $event->standing_capacity - $total_completed_bookings;
        // if no venue selected then set max standing booking
        if(empty($event->venue->id)){
            $event->available_standings = 999;
        }
        // check for automatic discount
        $event->allow_automatic_discounts = 0;
        if(in_array('em_automatic_discounts', $em->extensions) && !empty(em_global_settings('allow_early_bird_discount'))){
            $event->allow_automatic_discounts = 1;
            $ebd_service = EventM_Factory::get_service('EventM_Early_Bird_Discount_Service');
            if($ebd_service){
                $active_rule_data = $ebd_service->get_active_rule_data($event);
                if(!empty($active_rule_data)){
                    $event->ebd_active_rule_data = $active_rule_data;
                }
            }
        }
        //hide price label if event has zero booking price
        $event->hide_0_price_from_frontend = 0;
        if(!empty(em_global_settings('hide_0_price_from_frontend'))){
            $event->hide_0_price_from_frontend = 1;
        }
        //check required for attendee name
        $event->required_booking_attendee_name = em_global_settings('required_booking_attendee_name');
        //custom booking field data
        $event->custom_booking_field_data = 0;
        if(!empty(em_global_settings('custom_booking_field_data'))){
            $event->custom_booking_field_data = em_global_settings('custom_booking_field_data');
        }
        //currency position
        $currency_position = em_global_settings('currency_position');
        if(empty($currency_position)){
            $currency_position = 'before';
        }
        $event->currency_position = $currency_position;
        $event->is_user_logged_in = 1;
        if (!is_user_logged_in()){
            $event->is_user_logged_in = 0;
        }
        $event = apply_filters('event_magic_filter_event_data_for_booking', $event);
        
        echo json_encode($event);
        wp_die();
    }

    public function event_tour_completed() {
        $global_options = get_option(EM_GLOBAL_SETTINGS);
        $global_options['event_tour'] = 1;
        update_option(EM_GLOBAL_SETTINGS, $global_options);
        wp_die();
    }

    public function add_event_tour_completed() {
        $global_options = get_option(EM_GLOBAL_SETTINGS);
        $global_options['add_event_tour'] = 1;
        update_option(EM_GLOBAL_SETTINGS, $global_options);
        wp_die();
    }

    public function delete_order() {
        $order_id = $this->request->get_param('order_id', true);
        $booking_service = EventM_Factory::get_service('EventM_Booking_Service');
        $booking_service->revoke_seats($order_id, 'tmp', 'general');
        wp_delete_post($order_id);
        exit(0);
    }
    
    public function deactivation_feedback() {
        $msg = event_m_get_param('msg');
        $feedback = event_m_get_param('feedback');
        $message= '';
        $from_email_address = em_get_admin_user_email();
        switch($feedback) {
            case 'feature_not_available': $body='Feature not available: '; break;
            case 'feature_not_working': $body='Feature not working: '; break;
            case 'found_a_better_plugin': $body='Found a better plugin: '; break;
            case 'plugin_broke_site': $body='Plugin broke my site'; break;
            case 'plugin_stopped_working': $body='Plugin stopped working'; break;
            case 'temporary_deactivation': $body = "It's a temporary deactivation"; break;
            case 'other': $body='Other: '; break;
            default: return;
        }
        if(!empty($feedback)) {
            $message .= $body."\n\r";
            if(!empty($msg)) {
                $message .= $msg."\n\r";
            }
            $message .= "\n\r EventPrime Version - ".EVENTPRIME_VERSION;
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8\r\n";
            $headers .= 'From:'.$from_email_address."\r\n";
            if (wp_mail('feedback@cmshelplive.com','EventPrime Feedback',$message,$headers))
                wp_send_json_success();
            else
                wp_send_json_error();
        }
    }
    
    public function frontend_event_submit_form_strings() {
        $response = $this->services['event']->load_frontend_event_submit_page();
        wp_send_json_success(array('settings'=>$response));
    }
    
    public function submit_frontend_event() {
        $errors = array();
        // Event name validation
        $event_name = sanitize_text_field(event_m_get_param('name'));
        if (empty($event_name)) {
            $errors[]= __('Event Name cannot be empty.','eventprime-event-calendar-management');
        }
        // datepicker format from global settings
        $datepicker_format_arr = explode('&', em_global_settings('datepicker_format'));
        $datepicker_format = '';
        if(!empty($datepicker_format_arr) && isset($datepicker_format_arr[1])){
            $datepicker_format = $datepicker_format_arr[1] . ' H:i';
        }
        // required fields validation
        $frontend_submission_required_options = em_global_settings('frontend_submission_required');
        if(!empty($frontend_submission_required_options)){
            foreach ($frontend_submission_required_options as $key => $value) {
                if($key == 'fes_event_description'){
                    if(!empty($value)){
                        $description = event_m_get_param('description');
                        if(empty($description)){
                            $errors[]= __('Event Description is required field.','eventprime-event-calendar-management');
                        }
                    }
                }
                if($key == 'fes_event_booking'){
                    if(!empty($value)){
                        $enable_booking = absint(event_m_get_param('enable_booking'));
                        if(empty($enable_booking)){
                            $errors[]= __('Event Booking is required field.','eventprime-event-calendar-management');
                        }
                        $start_booking_date =  em_timestamp(event_m_get_param('start_booking_date'));
                        $last_booking_date = em_timestamp(event_m_get_param('last_booking_date'));
                        if(empty($start_booking_date) || empty($last_booking_date)){
                            $errors[]= __('Event Bookings Start Date & Bookings End Date is required field.','eventprime-event-calendar-management');
                        }
                    }
                }
                if($key == 'fes_event_link'){
                    if(!empty($value)){
                        $custom_link_enabled = absint(event_m_get_param('custom_link_enabled'));
                        if(empty($custom_link_enabled)){
                            $errors[]= __('Event Custom Link is required.','eventprime-event-calendar-management');
                        }
                    }
                }
                if($key == 'fes_event_type'){
                    if(!empty($value)){
                        $add_new_event_type = event_m_get_param('event_type');
                        if(empty($add_new_event_type)){
                            $errors[]= __('Event Type is required.','eventprime-event-calendar-management');
                        }
                    }
                }
                if($key == 'fes_event_location'){
                    if(!empty($value)){
                        $add_new_venue = absint(event_m_get_param('venue'));
                        if(empty($add_new_venue)){
                            $new_venue = event_m_get_param('new_venue');
                            if(empty($new_venue)){
                                $errors[]= __('Event Site is required field.','eventprime-event-calendar-management');
                            }
                        }
                    }
                }
                if($key == 'fes_event_performer'){
                    if(!empty($value)){
                        $add_new_performer = event_m_get_param('performer');
                        if(empty($add_new_performer)){
                            $new_performer_name = event_m_get_param('new_performer_name');
                            if(empty($new_performer_name)){
                                $errors[]= __('Event Performer is required field.','eventprime-event-calendar-management');
                            }
                        }
                    }
                }
                if($key == 'fes_event_organizer'){
                    if(!empty($value)){
                        $add_new_event_organizer = event_m_get_param('organizer');
                        if(empty($add_new_event_organizer)){
                            $new_event_organizer_name = event_m_get_param('new_organizer_name');
                            if(empty($new_event_organizer_name)){
                                $errors[]= __('Event Organizer is required field.','eventprime-event-calendar-management');
                            }
                        }
                    }
                }
            }
        }
        // Dates validation
        $start_date = em_timestamp(event_m_get_param('start_date'));
        $end_date = em_timestamp(event_m_get_param('end_date'));
        if ($start_date >= $end_date) {
            $errors[]= __('Event Start Date should be prior to the End Date.','eventprime-event-calendar-management');
        }        
        $enable_booking = absint(event_m_get_param('enable_booking'));
        if (!empty($enable_booking)){
            $start_booking_date =  em_timestamp(event_m_get_param('start_booking_date'));
            $last_booking_date = em_timestamp(event_m_get_param('last_booking_date'));
            if (!empty($start_booking_date) && !empty($last_booking_date)) {
                if ($last_booking_date > $end_date) {
                    $errors[]=__('Last Booking Date can not be greater than Event End Date.','eventprime-event-calendar-management');
                }
                if ($start_booking_date > $last_booking_date) {
                    $errors[]=__('Start Booking Date should be earlier than the Last Booking Date.','eventprime-event-calendar-management');
                }
                if ($start_booking_date > $start_date) {
                    $errors[]=__('Start Booking Date must be earlier than the Event Start Date.','eventprime-event-calendar-management');
                }
                if ($start_booking_date == $last_booking_date) {
                    $errors[]=__('Start Booking Date must be earlier than the Last Booking Date.','eventprime-event-calendar-management');
                }
            } else {
                $errors[]=__('Start Booking Date and Last Booking Date cannot be empty','eventprime-event-calendar-management');
            }
        }        
        if (absint(event_m_get_param('custom_link_enabled')) == 1){
            if(!preg_match('/^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/',event_m_get_param('custom_link'))){
                $errors[]=__('Incorrect custom link. Please update custom link to a correct URL format.','eventprime-event-calendar-management');
            }
        }
        // condition on add new event type
        $add_new_event_type = event_m_get_param('event_type');
        if($add_new_event_type == 'new_event_type'){
            $add_new_event_type_name = event_m_get_param('new_event_type_name');
            $add_new_event_type_background_color = event_m_get_param('new_event_type_background_color');
            if(empty($add_new_event_type_name) || empty($add_new_event_type_background_color)){
                $errors[]=__('Event type name and Background color is required for add new event type.','eventprime-event-calendar-management');
            }
        }
        // condition on new performers
        $new_performer_name = event_m_get_param('new_performer_name');
        if(!empty($new_performer_name)){
            $selected_performers = event_m_get_param('performer');
            if(empty($selected_performers)){
                $new_performer_type = event_m_get_param('new_performer_type');
                if(empty($new_performer_type)){
                    $errors[]=__('Performer name and Performer type is required for add new Performer.','eventprime-event-calendar-management');
                }
            }
        }
        // condition on add new event organizer
        $new_event_organizer_name = event_m_get_param('new_organizer_name');
        if(!empty($new_event_organizer_name)){
            /* check if organizer name already exists */
            $term = term_exists($new_event_organizer_name, EM_EVENT_ORGANIZER_TAX);
            if (!empty($term) && isset($term['term_id'])) {
                $errors[]= __('Please use different organizer name.','eventprime-event-calendar-management');
            }
            /* organizer email validation */
            $organizer_emails = event_m_get_param('organizer_emails');
            if(!empty($organizer_emails) && isset($organizer_emails)){
                foreach($organizer_emails as $email) {
                    if(!empty($email)){ 
                        if (!preg_match('/^([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z]{2,5})$/',$email)) {
                            $errors[]=__('Incorrect email format for event organizer.','eventprime-event-calendar-management');
                            break;
                        }
                    }    
                }
            }
            /* organizer website validation */
            $organizer_websites = event_m_get_param('organizer_websites');
            if(!empty($organizer_websites) && isset($organizer_websites)){
                foreach($organizer_websites as $website) {
                    if(!empty($website)){
                        if (!preg_match('/^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/',$website)) {
                            $errors[]=__('Incorrect website URL format.','eventprime-event-calendar-management');
                            break;
                        }
                    }
                }
            }    
        }

        if(!empty($errors)){
            wp_send_json_error(array('errors'=>$errors));
        }
        
        $response = $this->services['event']->save_user_submitted_event();
        do_action('event_magic_after_event_submission', $response);
        wp_send_json_success(array('post_id'=>$response));
    }
    
    private function check_permission(){
        if(!em_is_user_admin()){
            $error_msg= __('User not allowed','eventprime-event-calendar-management');
            wp_send_json_error(array('errors'=>array($error_msg)));
        }
    }

    public function upload_image_from_frontend(){
        if(isset($_FILES["image"]) && !empty($_FILES["image"])){
            $extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
            if($extension !='jpg' && $extension !='jpeg' && $extension != 'png' && $extension != 'gif'){
                wp_send_json_error( array( 'errors' => array( 'Only Image File Allowed.' ) ) );
            }
            $file = $_FILES['image'];
            $filename = $file['name'];
            $tmp_name = $file['tmp_name'];
            $upload_dir = wp_upload_dir();
            $uploaded_file = array();
            if (move_uploaded_file($file["tmp_name"], $upload_dir['path'] . "/" . $filename)) {
                $uploaded_file['file_name'] = $filename;
                $uploaded_file['upload_url'] = $upload_dir['url'] . "/" . $filename;

                $wp_filetype = wp_check_filetype($filename, null );
                $attachment = array(
                    'guid'           => $uploaded_file['upload_url'],
                    'post_mime_type' => $wp_filetype['type'],
                    'post_title'     => preg_replace( '/\.[^.]+$/', '', $filename ),
                    'post_content'   => '',
                    'post_status'    => 'inherit'
                );
                $attachment_id = wp_insert_attachment( $attachment, $upload_dir['path'] . "/" . $filename );
     
                if ( ! is_wp_error( $attachment_id ) ) {
                    require_once(ABSPATH . "wp-admin" . '/includes/image.php');
                    $attachment_data = wp_generate_attachment_metadata( $attachment_id, $uploaded_file['upload_url'] );
                    wp_update_attachment_metadata( $attachment_id,  $attachment_data );
                    wp_send_json_success(array('attachment_id' => $attachment_id));
                }
            }
            else{
                wp_send_json_error(array('errors'=>array($upload_file['error'])));
            }
        }
    }

    private function admin_cancel_bookings(){
        $this->check_permission();
        $bookings = $this->services['booking']->cancel_bookings();
        wp_send_json_success($bookings);
    }
    
    public function load_masonry_events_data(){
        $the_query = $this->services['event']->get_mesonry_events_query();
        $html = '';$recurring = 0;
        $posts= $the_query->posts;
        $button_title = em_global_settings('button_titles');
        //$posts = apply_filters('ep_filter_front_events',$posts,$atts);
        $posts = array_filter($posts, function($post){ return $post->post_status !== 'draft'; });
        if(!empty($posts)){
            $gs_service      = EventM_Factory::get_service( 'EventM_Setting_Service' );
            $global_settings = $gs_service->load_model_from_db();
            $currency_symbol = em_currency_symbol();
            if(event_m_get_param('recurring')){
                $recurring = event_m_get_param('recurring');
            }
            foreach ($posts as $post){
                $event = $this->services['event']->load_model_from_db($post->ID);
                if($recurring == 0 && isset($event->parent) && !empty($event->parent)){
                    continue;
                }
                // check for booking allowed
                $booking_allowed = 1;
                if((isset($event->parent) && !empty($event->parent)) && (isset($event->enable_recurrence_automatic_booking) && !empty($event->enable_recurrence_automatic_booking))){
                    // if event is recurring and parent has automatic booking enable than not allowed
                    $booking_allowed = 0;
                }
                $event->url = em_get_single_event_page_url($event, $global_settings);
                $emexpired = '';
                if(em_is_event_expired($event->id)){
                    $emexpired = 'emmasonry-expired';
                }
                if(empty($event->enable_booking)){
                    $emexpired .= 'em_event_disabled';
                }
                $html .= '<div class="em_masonry difl grid-item ep-masonry-item-wrap '.$emexpired.'" id="em-event-'.$event->id.'">';
                    $html .= '<div class="em_event_cover_masonry dbfl">';
                        $thumbImage = esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png');
                        if (!empty($event->cover_image_id)): ?>
                            <?php 
                            $thumbImageData = wp_get_attachment_image_src($event->cover_image_id, 'large');
                            if(!empty($thumbImageData) && isset($thumbImageData[0])){
                                $thumbImage = $thumbImageData[0];
                            }
                            if(empty($thumbImage)){
                                $thumbImage = get_the_post_thumbnail($event->id,'large');
                                if(isset($event->parent) && !empty($event->parent) && empty($thumbImage)){
                                    $thumbImage = get_the_post_thumbnail($event->parent,'large');
                                }
                            }
                            $html .='<a href="'.$event->url.'"><img src="'.$thumbImage.'"></a>';
                            else:
                            $html .='<a href="'.$event->url.'"><img src="'.esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png').'" class="em-no-image" ></a>';
                        endif;
                    $html .='</div>';
                    $html .= '<div class="dbfl em-masonry-description-wrap">';
                        $html .= '<div class="em_event_title em_block dbfl"  title="'.$event->name.'"><a href="'.$event->url.'">'.$event->name.'</a>';
                            if(is_user_logged_in()):
                                ob_start();
                                    do_action('event_magic_wishlist_link',$event);
                                    $wishlist = ob_get_contents();
                                ob_end_clean();
                                $html .= $wishlist;
                            endif;
                        $html .= '</div>';
                        //$html .= do_action('event_magic_popup_custom_data_before_details',$event);
                        $start_date = null; $end_date = null; $start_time = null; $end_time = null; $day = null;
                        if (em_compare_event_dates($event->id)){
                            $day = date_i18n(get_option('date_format'),$event->start_date);
                            $start_time = date_i18n(get_option('time_format'),$event->start_date);
                            $end_time = date_i18n(get_option('time_format'),$event->end_date);
                        }
                        else{
                            $start_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->start_date);
                            $end_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->end_date);
                        }
                        if($event->all_day){
                            $html .= '<div class="ep-masonry-event-date-row"><span class="material-icons em_color">date_range</span><div class="ep-masonry-event-date">'.date_i18n(get_option('date_format'),$event->start_date).'<span class="em-all-day"> - '.__('ALL DAY','eventprime-event-calendar-management').'</span></div></div>';
                        }
                        elseif(!empty($day)){
                            $html .= '<div class="ep-masonry-event-date-row"><span class="material-icons em_color">date_range</span>';
                            $html .= '<div class="ep-masonry-event-date">'.$day.' - '.$start_time;
                            if(empty($event->hide_end_date)) {
                                $html .= '  to  '.$end_time;
                            }
                            $html .= '</div></div>';
                        }
                        else{
                            $html .= '<div class="ep-masonry-event-date-row"><span class="material-icons em_color">date_range</span> ';
                            $html .= '<div class="ep-masonry-event-date">'.$start_date;
                            if(empty($event->hide_end_date)) {
                                $html .= ' - '.$end_date;
                            }
                            $html .= '</div></div>';
                        }
                        if(!empty($event->description)) {
                            $html .= '<div class="ep-masonry-event-description dbfl">'.$event->description.'</div>';
                        }
                        if(!empty($event->venue)){  
                            $venue_service= EventM_Factory::get_service('EventM_Venue_Service');
                            $venue= $venue_service->load_model_from_db($event->venue);
                            if(!empty($venue->id) && !empty($venue->address)){
                                $html .= '<div class="ep-masonry-event-address-wrap dbfl" title="'.$venue->address.'"><span class="material-icons em_color">location_on_outline</span><div class="ep-masonry-event-address">'.$venue->address.'</div></div>';
                            }
                        }
                        if(!empty($event->enable_booking) && empty($event->hide_booking_status)):
                            $sum = $this->services['event']->booked_seats($event->id);
                            $capacity = em_event_seating_capcity($event->id);
                            $html .= '<div class="ep-masonry-booking-row dbfl dbfl">
                                <div class="kf-event-attr-value dbfl">';
                                    if ($capacity > 0):
                                        $html .= '<div class="dbfl">'.$sum .'/'. $capacity.'</div>';
                                        $width = ($sum / $capacity) * 100;
                                        $html .= '<div class="dbfl"><div id="progressbar" class="em_progressbar dbfl"><div style="width:'.$width .'%'.'" class="em_progressbar_fill em_bg" ></div></div></div>';
                                    else:
                                        $html .= '<div class="dbfl">' . $sum . ' '.__('Attending','eventprime-event-calendar-management').'</div>';
                                    endif;
                            $html .= '</div></div>';
                        endif;
                        $custom_data_before_footer = '';
                        ob_start();
                            do_action('event_magic_popup_custom_data_before_footer',$event);
                            $custom_data_before_footer = ob_get_contents();
                        ob_end_clean();
                        $html .= $custom_data_before_footer;
                    $html .= '</div>';
                    
                    $html .= '<div class="em-masonry-footer dbfl"><div class="em_event_price difl">';
                        $ticket_price = $event->ticket_price;
                        $ticket_price = apply_filters('event_magic_load_calender_ticket_price', $ticket_price, $event);
                        // check if show one time event fees at front enable
                        if($event->show_fixed_event_price){
                            if($event->fixed_event_price > 0){
                                $ticket_price = $event->fixed_event_price;
                            }
                        }
                        if(!is_numeric($ticket_price)){
                            $html .= $ticket_price;
                        }
                        else{
                            $html .= !empty($ticket_price) ? em_price_with_position($ticket_price) : '';
                        }
                        $html .= '</div>';
                        $html .= do_action('event_magic_card_view_after_price',$event);
                        $html .= '<div class="kf-tickets-button difr">
                            <div class="em_event_attr_box em_eventpage_register difl">';
                                if(absint($event->custom_link_enabled) == 1):
                                    $html .= '<div class="em_header_button em_event_custom_link kf-tickets">
                                        <a class="em_header_button kf-tickets" target="_blank" href="'.$event->url.'">';
                                            if(!empty(em_global_settings('hide_event_custom_link')) && !is_user_logged_in()){
                                                $html .= em_global_settings_button_title('Login to View');
                                            }
                                            else{
                                                $html .= em_global_settings_button_title('Click for Details');
                                            }
                                        $html .= '</a>
                                    </div>';
                                elseif($this->services['event']->is_bookable($event)): 
                                    $current_ts = em_current_time_by_timezone();
                                    if($event->status=='expired'):
                                        $html .= '<div class="em_header_button em_event_expired kf-tickets">'.em_global_settings_button_title('Bookings Expired').'</div>';
                                    elseif($current_ts>$event->last_booking_date):
                                        $html .= '<div class="em_header_button em_booking-closed kf-tickets">'.em_global_settings_button_title('Bookings Closed').'</div>';
                                    elseif($current_ts<$event->start_booking_date):
                                        $html .= '<div class="em_header_button em_not_started kf-tickets">'.em_global_settings_button_title('Bookings not started yet').'</div>';
                                    else:
                                        if(!empty($booking_allowed)):
                                            if(is_user_logged_in() || $showBookNowForGuestUsers):
                                                $html .= '<form action="'.get_permalink($global_settings->booking_page).'" method="post" name="em_booking">';
                                                    $html .= '<button class="em_header_button em_event-booking kf-tickets em_color" name="tickets" onclick="em_event_booking('.$event->id.')" id="em_booking">'.em_global_settings_button_title('Book Now').'</button>';
                                                    $html .= '<input type="hidden" name="event_id" value="'.$event->id.'" />';
                                                    $html .= '<input type="hidden" name="venue_id" value="'.$event->venue.'" />';
                                                $html .= '</form>';
                                            else:
                                                $html .= '<a class="em_header_button em_event-booking kf-tickets em_color" target="_blank" href="'.add_query_arg('event_id',$event->id,get_permalink($global_settings->profile_page)).'">'.em_global_settings_button_title('Book Now').'</a>';
                                            endif;
                                        endif;
                                    endif;
                                elseif($event->status == 'publish' && $event->enable_booking == 1):
                                    if(isset($event->standing_capacity) && !empty($event->standing_capacity)):
                                        $html .= '<div class="em_event_attr_box em_eventpage_register difl"><div class="em_header_button em_not_bookable kf-tickets">'.em_global_settings_button_title('All Seats Booked').'</div>
                                        </div>';
                                    else:
                                        $html .= '<div class="em_event_attr_box em_eventpage_register difl">
                                            <div class="em_header_button em_not_bookable kf-tickets">'.em_global_settings_button_title('Bookings Closed').'</div>
                                        </div>';
                                    endif;
                                endif;
                            $html .= '</div>';
                        $html .= '</div>';
                    $html .= '</div>';
                    $html .= do_action('event_magic_card_view_after_footer',$event);
                $html .= '</div>';
            }
        }
        /* wp_send_json_success($html); */
        wp_send_json_success( array( 'html' => $html, 'is_recurring' => $recurring ) );
    }

    public function delete_fes_event(){
        $event_id = event_m_get_param('event_id');
        if(!empty($event_id)){
            $event = $this->services['event']->load_model_from_db($event_id);
            if(!empty($event)){
                $loggedin_user = wp_get_current_user();
                if($loggedin_user->ID == $event->user){
                    $post = get_post($event_id);
                    if(!empty($post)){
                        $post = array(
                            'ID' => $event_id,
                            'post_status' => 'trash',
                        );
                        wp_update_post($post);
                        wp_send_json_success(array('message' => __("Event Deleted Successfully!", 'eventprime-event-calendar-management')));
                    }
                }
            }
        }
        wp_send_json_error(array('message' => __("Event Deleted Successfully!", 'eventprime-event-calendar-management')));
    }

    public function export_submittion_attendees() {
        $event_id = event_m_get_param('event_id');
        $bookings = array();
        $booking_args = array(
            'numberposts' => -1,
            'post_status' => 'completed',
            'post_type'   => 'em_booking',
            'meta_key'    => 'em_event',
            'meta_value'  => $event_id
        );
        $booking_posts = get_posts($booking_args);
        foreach ($booking_posts as $post) {
            array_push( $bookings, $this->services['booking']->load_model_from_db($post->ID) );
        }
        $csv = new stdClass();
        foreach ($bookings as $booking) {
            $user = get_user_by('id', $booking->user);
            $csv = new stdClass();
            $csv->ID = $booking->id;
            $csv->user_display_name = rawurlencode($user->display_name);
            $csv->user_email = $user->user_email;
            $other_order_info = $booking->order_info;
            $csv->price =  $other_order_info['item_price'];
            $csv->no_tickets =  $other_order_info['quantity'];
            $csv->amount_total =  $other_order_info['quantity'] *  $other_order_info['item_price'];
            $event = $this->services['event']->load_model_from_db($booking->event);
            if(!empty($event->id)){
                $csv->event_name = rawurlencode($event->name);
            }
            else{
                $csv->event_name = __('Event deleted','eventprime-event-calendar-management');
            }
            $csv->event_type_name = '';
            if(!empty($event->event_type)){
                $event_type = $this->services['event_type']->load_model_from_db($event->event_type);
                if(!empty($event_type)){
                    $csv->event_type_name = rawurlencode($event_type->name);
                }
            }
            $csv->venue = '';
            $csv->seating_type = '';
            if(!empty($event->venue)){
                $event_venue = $this->services['venue']->load_model_from_db($event->venue);
                if(!empty($event_venue)){
                    $csv->venue = rawurlencode($event_venue->name);
                    $csv->seating_type = $event_venue->type;
                }
            }
            if(!isset($booking->order_info['is_custom_booking_field']) || $booking->order_info['is_custom_booking_field'] == 0){
                $csv->attendee_name = implode(', ', $booking->attendee_names);
            }
            else{
                $attData = '';$atts = 0;
                foreach($booking->attendee_names as $attendees){
                    $ja = 0;
                    foreach($attendees as $label => $value){
                        $attData .= $label .' - '. $value;
                        ++$ja;
                        if($ja < count($attendees)){
                            $attData .= ' , ';
                        }
                    }
                    ++$atts;
                    if($atts < count($booking->attendee_names)){
                        $attData .= ' | ';
                    }
                }
                $csv->attendee_name = $attData;
            }
            $csv->seat_sequences = '';
            if(isset($other_order_info['seat_sequences']) && !empty($other_order_info['seat_sequences'])){
                $csv->seat_sequences = implode(',', $other_order_info['seat_sequences']);
            }
            $csv->status= $booking->status;
            $data->posts[] = $csv;
        }

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="attendees.csv"');
        header('Cache-Control: max-age=0');
        $csv_name = 'em_Attendees' . time() . mt_rand(10, 1000000);
        $csv_path = get_temp_dir() . $csv_name . '.csv';
        $csv = fopen('php://output', "w");
        if (!$csv) {
            return false;
        }
        //Add UTF-8 header for proper encoding of the file
        fputs($csv, chr(0xEF) . chr(0xBB) . chr(0xBF));
        $csv_fields = array();
        $csv_fields[] = __('Booking ID', 'eventprime-event-calendar-management');
        $csv_fields[] = __('User Name', 'eventprime-event-calendar-management');
        $csv_fields[] = __('Email', 'eventprime-event-calendar-management');
        $csv_fields[] = __('Price', 'eventprime-event-calendar-management');
        $csv_fields[] = __('Ticket Count', 'eventprime-event-calendar-management');
        $csv_fields[] = __('Total Amount', 'eventprime-event-calendar-management');
        $csv_fields[] = __('Event Name', 'eventprime-event-calendar-management');
        $csv_fields[] = __('Event Type', 'eventprime-event-calendar-management');
        $csv_fields[] = __('Venue', 'eventprime-event-calendar-management');
        $csv_fields[] = __('Seating Type', 'eventprime-event-calendar-management');
        $csv_fields[] = __('Attendees', 'eventprime-event-calendar-management');
        $csv_fields[] = __('Seat No.', 'eventprime-event-calendar-management');
        $csv_fields[] = __('Status', 'eventprime-event-calendar-management');
        fputcsv($csv, $csv_fields);
        foreach ($data->posts as $a) {
            if (!fputcsv($csv, array_values((array) $a)))
                return false;
        }

        fclose($csv);
        wp_die();
    }

    /**
     * load bulk email page
     */
    private function admin_bulk_emails() {
        $this->check_permission();
        $response = $this->services['bulk_emails']->load_new_emails_page();
        wp_send_json_success($response);
    }

    /**
     * get attendees email by event id
     */
    public function get_attendees_email_by_event_id() {
        $event_id = event_m_get_param('event_id');
        $bookings = $this->services['booking']->get_by_event( $event_id );
        $userEmail = array();
        if( !empty( $bookings ) ) {
            foreach( $bookings as $booking ) {
                if( !empty( $booking->order_info ) ) {
                    if( isset( $booking->order_info['user_email'] ) && !empty( $booking->order_info['user_email'] ) ) {
                        $userEmail[] = $booking->order_info['user_email'];
                    }
                }
                // check for attendee's emails
                if(isset($booking->attendee_names) && !empty($booking->attendee_names)){
                    $attendee_emails = $this->services['bulk_emails']->format_attendees_email_addresses($booking->attendee_names);
                    if(!empty($attendee_emails) && count($attendee_emails) > 0){
                        //array_push($userEmail, $attendee_emails);
                        $userEmail = $userEmail + $attendee_emails;
                    }
                }
            }
        }
        if( !empty( $userEmail ) && count( $userEmail ) > 0 ) {
            $userEmail = array_unique( $userEmail );
            wp_send_json_success( $userEmail );
        }
        else{
            wp_send_json_error( array( 'errors' => esc_html__( 'No Attendee Found', 'eventprime-event-calendar-management' ) ) );
        }
    }

    /**
     * send bulk emails
     */
    public function send_bulk_emails() {
        $this->check_permission();
        $response = $this->services['bulk_emails']->send_bulk_emails();
        if( isset( $response['error'] ) && !empty( $response['error'] ) ) {
            $error_msg = $response['message'];
            wp_send_json_error( array( 'errors' => array( $error_msg ) ) );
        }
        else{
            wp_send_json_success( $response );
        }
    }

    /**
     * fetch offers
     */
    public function fetch_offers() {
        $this->check_permission();
        $url = "https://eventprime.net/ep-offers.json";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);  
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 3);     
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        $html = curl_exec($ch);
        curl_close($ch);
        $json = json_decode($html,true);
        $html = '';
        if(!empty($json)){
            foreach($json as $offer){
                $html .= '<div class="ep-offer">
                    <div class="ep-offer-wrap">';
                        if(isset($offer['title'])){
                            $html .= '<span class="ep-offer-title"><strong>'.esc_attr($offer['title']).'</strong></span>';
                        }
                        if(isset($offer['offer'])){
                            $html .= '<span class="ep-offer-desc">'.$offer['offer'].'</span>';
                        }
                        if(isset($offer['code'])){
                            $html .= '<span class="ep-offer-code"><strong>'.esc_attr($offer['code']).'</strong></span>';
                        }
                    $html .= '</div>';
                    if(isset($offer['link'])){
                        $html .= '<div class="ep-buy-btn">';
                            $html .= '<a target="_blank" href="'.$offer['link'].'">';
                            if(isset($offer['link_title'])){
                                $html .= $offer['link_title'];
                            }
                            else{
                                $html .= esc_html__('Buy Now','eventprime-event-calendar-management');
                            }
                        $html .= '</a></div>';
                    }
                $html .= '</div>';
            }
        }
        else{
            $html .= '<div class="ep-no-offer">'.esc_html__('Sorry, no offers available right now.','eventprime-event-calendar-management').'</div>';
        }
        wp_send_json_success( $html );
    }

    /**
     * Dismiss the offer notice
     */
    public function dismiss_notice_action() {
        add_option('event_magic_dismiss_offer_notice', true);
        wp_send_json_success('Notice Dismissed');
    }

    public function load_cards_events_data(){
        $event_service = EventM_Factory::get_service('EventM_Service');
        $the_query = $event_service->get_cards_events_query();
        $posts = $the_query->posts;
        if( ! isset( $atts ) ){ $atts = array(); }
        $posts = apply_filters('ep_filter_front_events',$posts,$atts);
        $posts = array_filter($posts, function($post){ return $post->post_status !== 'draft'; });
        $timestamp = time();
        $i = 0;
        $gs_service = EventM_Factory::get_service('EventM_Setting_Service');
        $global_settings = $gs_service->load_model_from_db();
        $showBookNowForGuestUsers = em_show_book_now_for_guest_users();
        $html = $column_class = '';$recurring = 0;
        if(event_m_get_param('recurring')){
            $recurring = event_m_get_param('recurring');
        }
        foreach ($posts as $post) :
            $event = $event_service->load_model_from_db($post->ID);
            if( $recurring == 0 && isset($event->parent) && !empty($event->parent) ){
                continue;
            }
            $currency_symbol = em_currency_symbol();
            $booking_allowed = 1;
            if((isset($event->parent) && !empty($event->parent)) && (isset($event->enable_recurrence_automatic_booking) && !empty($event->enable_recurrence_automatic_booking))){
                // if event is recurring and parent has automatic booking enable than not allowed
                $booking_allowed = 0;
            }
            $event->url = em_get_single_event_page_url($event, $global_settings);
            $emcardEpired ='';
            if (em_is_event_expired($event->id)) {
                $emcardEpired ='emcard-expired';
            }
            $emcardDisable = '';
            if((empty($event->enable_booking) && absint($event->custom_link_enabled) == 0)){
                $emcardDisable = 'em_event_disabled';
            }
            $html .='<div class="em_card difl '.$emcardEpired.''.$column_class.''.$emcardDisable.'" id="em-event-'.$event->id.'">';
                $html .= '<div class="em_event_cover dbfl">';
                    $thumbImage = esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png');
                    if (!empty($event->cover_image_id)): ?>
                        <?php 
                        $thumbImageData = wp_get_attachment_image_src($event->cover_image_id, 'large');
                        if(!empty($thumbImageData) && isset($thumbImageData[0])){
                            $thumbImage = $thumbImageData[0];
                        }
                        if(empty($thumbImage)){
                            $thumbImage = get_the_post_thumbnail($event->id,'large');
                            if(isset($event->parent) && !empty($event->parent) && empty($thumbImage)){
                                $thumbImage = get_the_post_thumbnail($event->parent,'large');
                            }
                        }
                        $html .='<a href="'.$event->url.'"><img src="'.$thumbImage.'"></a>';
                        else:
                        $html .='<a href="'.$event->url.'"><img src="'.esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png').'" class="em-no-image" ></a>';
                    endif;
                $html .='</div>';
                
                $html .='<div class="dbfl em-card-description">';
                    $html .='<div class="em_event_title em_block dbfl"  title="'.$event->name.'">';
                        $html .='<a href="'.$event->url.'">'.$event->name.'</a>';
                        if(is_user_logged_in()):
                            ob_start();
                                do_action('event_magic_wishlist_link',$event);
                                $custom_data_wishlist_link = ob_get_contents();
                            ob_end_clean();
                            $html .= $custom_data_wishlist_link;
                        endif;
                    $html .='</div>';
                    $start_date = null; $end_date = null; $start_time = null; $end_time = null; $day = null;
                        if (em_compare_event_dates($event->id)){
                            $day = date_i18n(get_option('date_format'),$event->start_date);
                            $start_time = date_i18n(get_option('time_format'),$event->start_date);
                            $end_time = date_i18n(get_option('time_format'),$event->end_date);
                        }
                        else
                        {
                            $start_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->start_date);
                            $end_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->end_date);
                        }
                    if($event->all_day):
                        $html.='<div class="em_event_start difl em_color em_wrap">'.date_i18n(get_option('date_format'),$event->start_date).'<span class="em-all-day"> - '.__('ALL DAY','eventprime-event-calendar-management').'</span>
                        </div>';
                    elseif(!empty($day)):
                        $html .= '<div class="em_event_start difl em_color em_wrap">'.$day.'</div>';
                        $html .= '<div class="em_event_start difl em_color em_wrap">'.$start_time;
                        if(empty($event->hide_end_date)) {
                            $html .= '  to  '.$end_time;
                        }
                        $html .= '</div>';
                    else:
                        $html .= '<div class="em_event_start difl em_color em_wrap">'.$start_date.'</div>';
                        if(empty($event->hide_end_date)) {
                            $html .= '<div class="em_event_start difl em_color em_wrap"> - '.$end_date.' </div>';
                        }
                        
                    endif;
                        
                    if(!empty($event->venue)){  
                        $venue_service= EventM_Factory::get_service('EventM_Venue_Service');
                        $venue= $venue_service->load_model_from_db($event->venue);
                        if(!empty($venue->id)){ 
                            $html .='<div class="em_event_address dbfl" title="'.$venue->address.'">'. $venue->address.'</div>';
                        }
                    }
                    if(!empty($event->description)) {
                        $html .= '<div class="em_event_description dbfl">'.$event->description.'</div>';
                    }
                    if(!empty($event->enable_booking) && empty($event->hide_booking_status)):
                        $sum = $event_service->booked_seats($event->id);
                        $capacity = em_event_seating_capcity($event->id);
                        $html .='<div class="dbfl">
                            <div class="kf-event-attr-value dbfl">';  
                                if ($capacity > 0):
                                    $html .='<div class="dbfl">
                                        '.$sum.' / '.$capacity.' 
                                    </div>';
                                $width = ($sum / $capacity) * 100;
                                $html .='<div class="dbfl">
                                    <div id="progressbar" class="em_progressbar dbfl">
                                        <div style="width:'. $width . '%" class="em_progressbar_fill em_bg" ></div>
                                    </div>';
                                $html.='</div>';
                            
                                else:
                                    $html .='<div class="dbfl">' . $sum . ' '.__('Attending','eventprime-event-calendar-management').'</div>';
                                endif;
                            $html .='</div>';
                        $html .='</div>'; 
                    endif;
                    $custom_data_before_footer = '';
                    ob_start();
                        do_action('event_magic_popup_custom_data_before_footer',$event);
                        $custom_data_before_footer = ob_get_contents();
                    ob_end_clean();
                    $html .= $custom_data_before_footer;
                $html .='</div>';
                $html .='<div class="em-cards-footer dbfl">
                    <div class="em_event_price  difl">';
                        $ticket_price = $event->ticket_price;
                        $ticket_price = apply_filters('event_magic_load_calender_ticket_price', $ticket_price, $event);
                        if($event->show_fixed_event_price){
                            if($event->fixed_event_price > 0){
                                $ticket_price = $event->fixed_event_price;
                            }
                        }
                        if(!is_numeric($ticket_price)){
                            $html .= $ticket_price;
                        }
                        else{
                            $html .= !empty($ticket_price) ? em_price_with_position($ticket_price) : '';
                        }
                    $html .='</div>';
                    $html .=do_action('event_magic_card_view_after_price',$event);
                    $html .='<div class="kf-tickets-button difr">
                        <div class="em_event_attr_box em_eventpage_register difl">';
                            if(absint($event->custom_link_enabled) == 1):
                                $html .='<div class="em_header_button em_event_custom_link kf-tickets">
                                    <a class="ep-event-custom-link" target="_blank" href="'.$event->url.'">';
                                            
                                        if(!empty($global_settings->hide_event_custom_link) && !is_user_logged_in()){
                                            $html .= em_global_settings_button_title('Login to View');
                                        }
                                        else{
                                            $html .= em_global_settings_button_title('Click for Details');
                                        }
                                    $html .='</a>';
                                $html ='</div>';
                            elseif($event_service->is_bookable($event)): $current_ts = em_current_time_by_timezone();
                                if($event->status == 'expired'):
                                    $html .= '<div class="em_header_button em_event_expired kf-tickets">'.em_global_settings_button_title('Bookings Expired').'</div>';
                                elseif($current_ts > $event->last_booking_date):
                                    $html .='<div class="em_header_button em_booking-closed kf-tickets">'.em_global_settings_button_title('Bookings Closed').'</div>';
                                elseif($current_ts < $event->start_booking_date): 
                                    $html .='<div class="em_header_button em_not_started kf-tickets">'.em_global_settings_button_title('Bookings not started yet').'</div>';
                                else: 
                                    if(!empty($booking_allowed)):
                                        if(is_user_logged_in() || $showBookNowForGuestUsers):
                                            $html .='<form action="'.get_permalink($global_settings->booking_page).'" method="post" name="em_booking">
                                                <button class="em_header_button em_event-booking kf-tickets" name="tickets" onclick="em_event_booking('.$event->id.')" id="em_booking">'.em_global_settings_button_title('Book Now').'</button>
                                                <input type="hidden" name="event_id" value="'.$event->id.'" />
                                                <input type="hidden" name="venue_id" value="'.$event->venue.'" />
                                            </form>';
                                        else: 
                                            $html .='<a class="em_header_button kf-tickets" target="_blank" href="'. add_query_arg('event_id',$event->id,get_permalink($global_settings->profile_page)).'">'. em_global_settings_button_title('Book Now').'</a>';
                                        endif;
                                    endif;
                                endif;
                            elseif($event->status == 'publish' && $event->enable_booking == 1):
                                if(isset($event->standing_capacity) && !empty($event->standing_capacity)):
                                    $html .='<div class="em_event_attr_box em_eventpage_register difl">
                                        <div class="em_header_button em_not_bookable kf-tickets">'.em_global_settings_button_title('All Seats Booked').'</div>
                                    </div>';
                                else:
                                    $html .='<div class="em_event_attr_box em_eventpage_register difl">
                                        <div class="em_header_button em_not_bookable kf-tickets">'. em_global_settings_button_title('Bookings Closed').'</div>
                                    </div>';
                                endif;
                            endif;
                        $html.='</div>';
                    $html.='</div>';
                $html.='</div>';
                $html.=do_action('event_magic_card_view_after_footer',$event);
            $html.='</div>';
        
            $i++;
        endforeach; 
        /* wp_send_json_success($html); */
        wp_send_json_success( array( 'html' => $html, 'is_recurring' => $recurring ) );
    }

    /**
     * Price Manager List
     */
    private function admin_event_price_manager_list(){
        $em_price_manager_nonce = event_m_get_param('em_price_manager_nonce');
        if( empty( $em_price_manager_nonce ) || !wp_verify_nonce( $em_price_manager_nonce, 'em_price_manager_cap_object_nonce' ) || empty( em_check_context_user_capabilities( array( 'edit_events', 'edit_others_events' ) ) ) ) {
            $error_msg = esc_html__( 'Unauthorized Access', 'eventprime-event-calendar-management' );
            wp_send_json_error( array( 'errors' => array( $error_msg ) ) );
        }
        $event_id = event_m_get_param('post_id');
        global $wpdb;
        $table_name = $this->table['multi_price'];
        $get_price_data = $wpdb->get_results( "SELECT * FROM $table_name WHERE event_id = $event_id ORDER BY priority" );
        if( empty( $get_price_data ) ) {
            $event = $this->services['event']->load_model_from_db($event_id);
            $data = array();
            $data['event_id'] = $event_id;
            $data['name'] = esc_html__('Default Price', 'eventprime-event-calendar-management');
            $data['description'] = esc_html__('Default Price', 'eventprime-event-calendar-management');
            $data['start_date'] = (!empty($event->start_booking_date) ? date_i18n("Y-m-d H:i:s", $event->start_booking_date) : '' );
            $data['end_date'] = (!empty($event->last_booking_date) ? date_i18n("Y-m-d H:i:s", $event->last_booking_date) : '' );
            $data['price'] = $event->ticket_price;
            $data['special_price'] = '';
            $data['capacity'] = (!empty($event->standing_capacity)) ? $event->standing_capacity : $event->seating_capacity;
            $data['is_default'] = 1;
            $data['is_event_price'] = 1;
            $data['icon'] = '';
            $data['priority'] = 1;
            $data['status'] = 1;
            $data['created_at'] = date_i18n("Y-m-d H:i:s", time());
            $table_name = $this->table['multi_price'];
            $result = $wpdb->insert($table_name, $data);
            /* insert price option data for child events  start */
            $option_id = $wpdb->insert_id;
            $child_events_data = array( 'data' => $data, 'option_id' => $option_id );
            do_action( 'insert_child_events_price_option_data', $child_events_data );
            /* insert price option data for child events  end */
            $get_price_data = $wpdb->get_results( "SELECT * FROM $table_name WHERE event_id = $event_id" );
        }
        $datepicker_format_js = em_global_settings('datepicker_format');
        $datepicker_format = (!empty($datepicker_format_js)) ? explode('&', em_global_settings('datepicker_format'))[0] : 'mm/dd/yy';
        wp_send_json_success( $get_price_data );
    }
    /**
     * Price Manager Add
     */
    private function admin_event_price_manager_add(){
        $em_price_manager_nonce = event_m_get_param('em_price_manager_nonce');
        if( empty( $em_price_manager_nonce ) || !wp_verify_nonce( $em_price_manager_nonce, 'em_price_manager_cap_object_nonce' ) || empty( em_check_context_user_capabilities( array( 'edit_events', 'edit_others_events' ) ) ) ) {
            $error_msg = esc_html__( 'Unauthorized Access', 'eventprime-event-calendar-management' );
            wp_send_json_error( array( 'errors' => array( $error_msg ) ) );
        }
        $response = array();
        $get_price_data = '';
        $option_id = event_m_get_param('option_id');
        $response['option_data'] = array();
        if(!empty($option_id)){
            global $wpdb;
            $table_name = $this->table['multi_price'];
            $get_price_data = $wpdb->get_row( "SELECT * FROM $table_name WHERE id = $option_id" );
            if(!empty($get_price_data)){
                $event_id = $get_price_data->event_id;
                $response['option_data'] = $get_price_data;
                $response['option_data']->price = floatval($get_price_data->price);
                $response['option_data']->special_price = floatval($get_price_data->special_price);
                $response['option_data']->capacity = absint($get_price_data->capacity);
                $response['option_data']->is_default = absint($get_price_data->is_default);
                $response['option_data']->capacity_progress_bar = absint($get_price_data->capacity_progress_bar);
                $response['option_data']->priority = absint($get_price_data->priority);
                if(!empty($get_price_data->icon)){
                    $icon_image = $this->services['event_type']->get_image($get_price_data->icon);
                    $response['option_data']->icon_image = $icon_image;
                }
                $response['option_data']->seat_data = (!empty($get_price_data->seat_data) ? unserialize($get_price_data->seat_data) : array());
            }
        }
        else{
            $event_id = event_m_get_param('post_id');
        }
        $event = $this->services['event']->load_model_from_db($event_id);
        $datepicker_format = em_global_settings('datepicker_format');
        $datepicker_format_js = (!empty($datepicker_format)) ? explode('&', em_global_settings('datepicker_format'))[0] : 'mm/dd/yy';
        $datepicker_format_php = (!empty($datepicker_format)) ? explode('&', em_global_settings('datepicker_format'))[1] : 'm/d/Y';
        //$datepicker_format .= ' H:i';
        $response['datepicker_format'] = $datepicker_format_js;
        $response['start_booking_date'] = (!empty($event->start_booking_date) ? date_i18n($datepicker_format_php, $event->start_booking_date) : date_i18n($datepicker_format_php, $event->start_date) );
        $response['last_booking_date'] = (!empty($event->last_booking_date) ? date_i18n($datepicker_format_php, $event->last_booking_date) : date_i18n($datepicker_format_php, $event->end_date) );
        $venue = $this->services['venue']->load_model_from_db($event->venue);
        $response['venue'] = $venue;
        $response['event'] = $event;

        wp_send_json_success( $response );
    }
    /**
     * save price option
     */
    public function save_event_price_option() {
        $em_price_manager_nonce = event_m_get_param('em_price_manager_nonce');
        if( empty( $em_price_manager_nonce ) || !wp_verify_nonce( $em_price_manager_nonce, 'em_price_manager_cap_object_nonce' ) || empty( em_check_context_user_capabilities( array( 'edit_events', 'edit_others_events' ) ) ) ) {
            $error_msg = esc_html__( 'Unauthorized Access', 'eventprime-event-calendar-management' );
            wp_send_json_error( array( 'errors' => array( $error_msg ) ) );
        }
        $event_id = absint(event_m_get_param('event_id'));
        $response = $this->services['event']->save_event_price_option();
        if( isset( $response['error'] ) && !empty( $response['error'] ) ) {
            $error_msg = $response['message'];
            wp_send_json_error( array( 'errors' => array( $error_msg ) ) );
        }
        else{
            $redirect = html_entity_decode(esc_url(admin_url('admin.php/?page=em_dashboard&tab=price_manager&post_id='.$event_id)));
            wp_send_json_success(array('redirect' => $redirect));
        }
    }
    /**
     * delete price option
     */
    public function delete_event_price_option() {
        $em_price_manager_nonce = event_m_get_param('em_price_manager_nonce');
        if( empty( $em_price_manager_nonce ) || !wp_verify_nonce( $em_price_manager_nonce, 'em_price_manager_cap_object_nonce' ) || empty( em_check_context_user_capabilities( array( 'delete_events', 'delete_others_events' ) ) ) ) {
            $error_msg = esc_html__( 'Unauthorized Access', 'eventprime-event-calendar-management' );
            wp_send_json_error( array( 'errors' => array( $error_msg ) ) );
        }
        $option_id = event_m_get_param('option_id');
        $event_id = event_m_get_param('event_id');
        if(!empty($option_id) && !empty($event_id)){
            global $wpdb;
            $table_name = $this->table['multi_price'];
            foreach($option_id as $id){
                $get_price_data = $wpdb->get_row( "SELECT * FROM $table_name WHERE id = $id" );
                if(!empty($get_price_data)){
                    $wpdb->delete( $table_name, array( 'ID' => $id ) );
                    $child_events_data = array( 'event_id' => $event_id, 'option_id' => $id );
                    do_action( 'delete_child_events_price_option_data', $child_events_data );
                }
            }
            $redirect = html_entity_decode(esc_url(admin_url('admin.php/?page=em_dashboard&tab=price_manager&post_id='.$event_id)));
            wp_send_json_success( array( 'message' => esc_html__( "Options Deleted Successfully!", 'eventprime-event-calendar-management' ), 'redirect' => $redirect ) );
        }
        wp_send_json_error( array( 'errors' => esc_html__( "Something went wrong. Please try again", 'eventprime-event-calendar-management' ) ) );
    }
    /**
     * get price option data
     */
    public function get_event_price_opion_data($event_id) {
        global $wpdb;
        $response = [];
        $table_name = $this->table['multi_price'];
        $get_price_data = $wpdb->get_results( "SELECT * FROM $table_name WHERE event_id = $event_id AND status = 1 ORDER BY priority" );
        if(!empty($get_price_data)){
            foreach($get_price_data as $price_data){
                $pdata = $price_data;
                $pdata->icon_image = '';
                if(!empty($pdata->icon)){
                    $pdata->icon_image = $this->services['event_type']->get_image($pdata->icon);
                }
                $pdata->option_has_timeout = $pdata->option_disabled = $pdata->option_has_capacityout = 0;
                if(!empty($pdata->start_date)){
                    if(em_time($pdata->start_date) > em_current_time_by_timezone()){
                        $pdata->option_has_timeout = 1;
                        $pdata->option_disabled = 1;
                    }
                }
                if(!empty($pdata->end_date)){
                    if(em_time($pdata->end_date) < em_current_time_by_timezone()){
                        $pdata->option_has_timeout = 1;
                        $pdata->option_disabled = 1;
                    }
                }
                if(!empty($pdata->description)){
                    $pdata->description = html_entity_decode($pdata->description);
                }

                $pdata->total_booking = $this->services['booking']->get_multi_price_booking_count($pdata->id, $event_id);
                $capacity = $pdata->capacity;
                $pdata->width = 0;
                if($pdata->total_booking > 0 && $capacity > 0){
                    $pdata->width = ($pdata->total_booking / $capacity) * 100;
                }
                if($pdata->total_booking >= $capacity){
                    $pdata->option_has_capacityout = 1;
                    $pdata->option_disabled = 1;
                }
                
                $response[] = $pdata;
            }
        }
        return $response;
    }
    /**
     * sorting of price option list
     */
    public function multi_price_list_sorting() {
        $em_price_manager_nonce = event_m_get_param('em_price_manager_nonce');
        if( empty( $em_price_manager_nonce ) || !wp_verify_nonce( $em_price_manager_nonce, 'em_price_manager_cap_object_nonce' ) || empty( em_check_context_user_capabilities( array( 'edit_events', 'edit_others_events' ) ) ) ) {
            $error_msg = esc_html__( 'Unauthorized Access', 'eventprime-event-calendar-management' );
            wp_send_json_error( array( 'errors' => array( $error_msg ) ) );
        }
        $option_id = event_m_get_param('option_id');
        $event_id = event_m_get_param('event_id');
        if(!empty($option_id) && !empty($event_id)){
            $response = $this->services['event']->save_event_price_option_sorting();
        }
        if( isset( $response['error'] ) && !empty( $response['error'] ) ) {
            $error_msg = $response['message'];
            wp_send_json_error( array( 'errors' => array( $error_msg ) ) );
        }
        else{
            wp_send_json_success( array( 'success' => 1 ) );
        }
    }

    public function load_list_events_data(){
        $the_query = $this->services['event']->get_mesonry_events_query();
        $html = '';$recurring = 0;
        $posts= $the_query->posts;
        $posts = array_filter($posts, function($post){ return $post->post_status !== 'draft'; });
        if(!empty($posts)){
            $last_month_id = event_m_get_param('last_month_id');
            $gs_service      = EventM_Factory::get_service( 'EventM_Setting_Service' );
            $global_settings = $gs_service->load_model_from_db();
            $currency_symbol = em_currency_symbol();
            if(event_m_get_param('recurring')){
                $recurring = event_m_get_param('recurring');
            }
            foreach ($posts as $post){
                $event = $this->services['event']->load_model_from_db($post->ID);
                if($recurring == 0 && isset($event->parent) && !empty($event->parent)){
                    continue;
                }
                $month_id = date('Ym', $event->start_date);
                if(empty($last_month_id) || $last_month_id != $month_id){
                    $last_month_id = $month_id;
                    $html .= '<div class="ep-month-divider"><span class="ep-listed-event-month">'.date_i18n('F Y', $event->start_date).'<span class="ep-listed-event-month-tag"></span></span></div>';
                }
                $booking_allowed = 1;
                if((isset($event->parent) && !empty($event->parent)) && (isset($event->enable_recurrence_automatic_booking) && !empty($event->enable_recurrence_automatic_booking))){
                    // if event is recurring and parent has automatic booking enable than not allowed
                    $booking_allowed = 0;
                }
                $event->url = em_get_single_event_page_url($event, $global_settings);
                $emexpired = '';
                if(em_is_event_expired($event->id)){
                    $emexpired = 'emlist-expired';
                }
                if(empty($event->enable_booking)){
                    $emexpired .= 'em_event_disabled';
                }
                $html .= '<div  id="em-event-'.$event->id.'" class="ep-event-article '.$emexpired.'">';
                    $html .= '<div class="ep-topsec">
                        <div class="em-col-3 difl ep-event-image-wrap ep-col-table-c">
                            <div class="em_event_cover_list dbfl">';
                                $thumbImage = esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png');
                                if (!empty($event->cover_image_id)):
                                    $thumbImageData = wp_get_attachment_image_src($event->cover_image_id, 'large');
                                    if(!empty($thumbImageData) && isset($thumbImageData[0])){
                                        $thumbImage = $thumbImageData[0];
                                    }
                                    if(empty($thumbImage)){
                                        $thumbImage = get_the_post_thumbnail($event->id,'large');
                                        if(isset($event->parent) && !empty($event->parent) && empty($thumbImage)){
                                            $thumbImage = get_the_post_thumbnail($event->parent,'large');
                                        }
                                    }
                                    $html .= '<a href="'.$event->url.'">
                                        <img src="'.$thumbImage.'" alt="'.__('Event Cover Image', 'eventprime-event-calendar-management').'">
                                    </a>';
                                else:
                                    $html .= '<a href="'.$event->url.'"><img src="'.esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png').'" alt="'.__('Dummy Image','eventprime-event-calendar-management').'" class="em-no-image" ></a>';
                                endif;
                            $html .= '</div>
                        </div>';

                        $html .= '<div class="em-col-5 difl ep-col-table-c ep-event-content-wrap">
                            <div class="ep-event-content">';
                                $html .= '<h3 class="ep-event-title"><a class="ep-color-hover" data-event-id="'.$event->id.'" href="'.$event->url.'" target="_self">'.$event->name.'</a>
                                </h3>';
                                if(is_user_logged_in()):
                                    ob_start();
                                        do_action('event_magic_wishlist_link',$event);
                                        $wishlist = ob_get_contents();
                                    ob_end_clean();
                                    $html .= $wishlist;
                                endif;
                                if(!empty($event->description)) {
                                    $html .=  '<div class="ep-event-description">'.$event->description.'</div>';
                                }
                            $html .= '</div>';
                            $html .= do_action('event_magic_card_view_after_price', $event);
                        $html .= '</div>';

                        $html .='<div class="em-col-4 difl ep-col-table-c ep-event-meta-wrap">
                            <div class="ep-event-meta ep-color-before">'; 
                                $start_date = null; $end_date = null; $start_time = null; $end_time = null; $day = null;
                                if (em_compare_event_dates($event->id)){
                                    $day = date_i18n(get_option('date_format'),$event->start_date);
                                    $start_time = date_i18n(get_option('time_format'),$event->start_date);
                                    $end_time = date_i18n(get_option('time_format'),$event->end_date);
                                }
                                else{
                                    $start_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->start_date);
                                    $end_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->end_date);
                                }
                                if($event->all_day){
                                    $html .= '<div class="ep-list-event-date-row">
                                        <span class="material-icons em_color">date_range</span> 
                                        <div class="ep-list-event-date">'.
                                            date_i18n(get_option('date_format'),$event->start_date).'
                                            <span class="em-all-day"> - '. __('ALL DAY','eventprime-event-calendar-management') . '</span>
                                        </div>
                                    </div>';
                                } elseif(!empty($day)){
                                    $html .= '<div class="ep-list-event-date-row"><span class="material-icons em_color">date_range</span> <div class="ep-list-event-date">'.$day.' - '.$start_time;
                                    if(empty($event->hide_end_date)) {
                                        $html .= '  to  '.$end_time;
                                    }
                                    $html .= '</div></div>';
                                }
                                else{
                                    $html .= '<div class="ep-list-event-date-row"><span class="material-icons em_color">date_range</span> <div class="ep-list-event-date">'.$start_date;
                                    if(empty($event->hide_end_date)) {
                                        $html .= ' - '.$end_date;
                                    }
                                    $html .= '</div></div>';
                                }
                                if(!empty($event->venue)){  
                                    $venue_service= EventM_Factory::get_service('EventM_Venue_Service');
                                    $venue= $venue_service->load_model_from_db($event->venue);
                                    if(!empty($venue->id) && !empty($venue->address)){
                                        $html .= '<div class="em-list-view-venue-details" title="'.$venue->address.'"><span class="ep-list-event-location"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zM7 9c0-2.76 2.24-5 5-5s5 2.24 5 5c0 2.88-2.88 7.19-5 9.88C9.92 16.21 7 11.85 7 9z"/><circle cx="12" cy="9" r="2.5"/></svg></span><address class="em-list-event-address"><span>'.$venue->address.'</span></address>
                                                </div>';
                                    }
                                }
                                if(!empty($event->enable_booking) && empty($event->hide_booking_status)):
                                    $sum = $this->services['event']->booked_seats($event->id);
                                    $capacity = em_event_seating_capcity($event->id);
                                    $html .= '<div class="ep-list-booking-status dbfl dbfl">
                                        <div class="kf-event-attr-value dbfl">';
                                            if ($capacity > 0):
                                                $html .= '<div class="dbfl">'.$sum .'/'. $capacity.'</div>';
                                                $width = ($sum / $capacity) * 100;
                                                $html .= '<div class="dbfl"><div id="progressbar" class="em_progressbar dbfl"><div style="width:'.$width .'%'.'" class="em_progressbar_fill em_bg" ></div></div></div>';
                                            else:
                                                if($sum > 0){
                                                    $html .= '<div class="dbfl">' . $sum . ' '.__('Attending','eventprime-event-calendar-management').'</div>';
                                                }
                                            endif;
                                        $html .= '</div>
                                    </div>';
                                endif;

                                $custom_data_before_footer = '';
                                ob_start();
                                    do_action('event_magic_popup_custom_data_before_footer',$event);
                                    $custom_data_before_footer = ob_get_contents();
                                ob_end_clean();
                                $html .= $custom_data_before_footer;

                                $html .= ' <div class="ep-list-view-footer">
                                    <div class="em_event_price difl">';
                                        $ticket_price = $event->ticket_price;
                                        $ticket_price = apply_filters('event_magic_load_calender_ticket_price', $ticket_price, $event);
                                        if($event->show_fixed_event_price){
                                            if($event->fixed_event_price > 0){
                                                $ticket_price = $event->fixed_event_price;
                                            }
                                        }
                                        if(!is_numeric($ticket_price)){
                                            $html .= $ticket_price;
                                        }
                                        else{
                                            $html .= !empty($ticket_price) ? em_price_with_position($ticket_price) : '';
                                        }
                                    $html .= '</div>';
                                    $html .= do_action('event_magic_card_view_after_price',$event);
                                    $html .= '<div class="kf-tickets-button difr">
                                        <div class="em_event_attr_box em_eventpage_register difl">';
                                            if(absint($event->custom_link_enabled) == 1):
                                                $html .= '<div class="em_header_button em_event_custom_link kf-tickets">
                                                    <a class="em_header_button kf-tickets" target="_blank" href="'.$event->url.'">';
                                                        if(!empty(em_global_settings('hide_event_custom_link')) && !is_user_logged_in()){
                                                            $html .= em_global_settings_button_title('Login to View');
                                                        }
                                                        else{
                                                            $html .= em_global_settings_button_title('Click for Details');
                                                        }
                                                    $html .= '</a>
                                                </div>';
                                            elseif($this->services['event']->is_bookable($event)): 
                                                $current_ts = em_current_time_by_timezone();
                                                if($event->status=='expired'):
                                                    $html .= '<div class="em_header_button em_event_expired kf-tickets">'.
                                                    em_global_settings_button_title('Bookings Expired').'</div>';
                                                elseif($current_ts>$event->last_booking_date):
                                                    $html .= '<div class="em_header_button em_booking-closed kf-tickets">'.
                                                    em_global_settings_button_title('Bookings Closed').'</div>';
                                                elseif($current_ts<$event->start_booking_date):
                                                    $html .= '<div class="em_header_button em_not_started kf-tickets">'.
                                                    em_global_settings_button_title('Bookings not started yet').'</div>';
                                                else:
                                                    if(!empty($booking_allowed)):
                                                        if(is_user_logged_in() || $showBookNowForGuestUsers):
                                                            $html .= '<form action="'.get_permalink($global_settings->booking_page).'" method="post" name="em_booking">';
                                                                $html .= '<button class="em_header_button em_event-booking kf-tickets em_color" name="tickets" onclick="em_event_booking('.$event->id.')" id="em_booking">'.
                                                                em_global_settings_button_title('Book Now').'</button>';
                                                                $html .= '<input type="hidden" name="event_id" value="'.$event->id.'" />';
                                                                $html .= '<input type="hidden" name="venue_id" value="'.$event->venue.'" />';
                                                            $html .= '</form>';
                                                        else:
                                                            $html .= '<a class="em_header_button em_event-booking kf-tickets em_color" target="_blank" href="'.add_query_arg('event_id',$event->id,get_permalink($global_settings->profile_page)).'">'.
                                                            em_global_settings_button_title('Book Now').'</a>';
                                                        endif;
                                                    endif;
                                                endif;
                                            elseif($event->status == 'publish' && $event->enable_booking == 1):
                                                if(isset($event->standing_capacity) && !empty($event->standing_capacity)):
                                                    $html .= '<div class="em_event_attr_box em_eventpage_register difl"><div class="em_header_button em_not_bookable kf-tickets">'.em_global_settings_button_title('All Seats Booked').'</div>
                                                    </div>';
                                                else:
                                                    $html .= '<div class="em_event_attr_box em_eventpage_register difl">
                                                        <div class="em_header_button em_not_bookable kf-tickets">'.em_global_settings_button_title('Bookings Closed').'</div>
                                                    </div>';
                                                endif;
                                            endif;
                                        $html .= '</div>';
                                    $html .= '</div>';
                                $html .= '</div>';
                                $html .= do_action('event_magic_card_view_after_footer',$event);
                            $html .= '</div>';
                        $html .= '</div>';
                    $html .= '</div>';
                $html .= '</div>';
            }
                
        }
        wp_send_json_success(array('html' => $html, 'last_month_id' => $last_month_id));
    }

    /**
     * Event Orgainzer List Manager
     */  
    private function admin_event_organizer(){
        $em_organizer_nonce = event_m_get_param('em_organizer_nonce');
        if( empty( $em_organizer_nonce ) || !wp_verify_nonce( $em_organizer_nonce, 'em_organizer_object_nonce' ) || empty( em_check_context_user_capabilities( array( 'create_event_organizers' ) ) ) ) {
            $error_msg = esc_html__( 'Unauthorized Access', 'eventprime-event-calendar-management' );
            wp_send_json_error( array( 'errors' => array( $error_msg ) ) );
        }
        $response = $this->services['event_organizer']->load_edit_page();
        if( isset( $response->error ) ) {
            wp_send_json_error( array( 'errors' => array( $response->message ) ) );
        }
        wp_send_json_success( array( 'post' => $response ) );
    }

    private function admin_event_organizers(){
        $em_organizer_nonce = event_m_get_param('em_organizer_nonce');
        if( empty( $em_organizer_nonce ) || !wp_verify_nonce( $em_organizer_nonce, 'em_organizer_object_nonce' ) || empty( em_check_context_user_capabilities( array( 'view_event_organizers' ) ) ) ) {
            $error_msg = esc_html__( 'Unauthorized Access', 'eventprime-event-calendar-management' );
            wp_send_json_error( array( 'errors' => array( $error_msg ) ) );
        }
        $response = $this->services['event_organizer']->load_list_page();
        wp_send_json_success($response);
    }

    public function save_event_organizer(){
        $em_organizer_nonce = event_m_get_param('em_organizer_nonce');
        if( empty( $em_organizer_nonce ) || !wp_verify_nonce( $em_organizer_nonce, 'em_organizer_object_nonce' ) ) {
            $error_msg = esc_html__( 'Unauthorized Access', 'eventprime-event-calendar-management' );
            wp_send_json_error( array( 'errors' => array( $error_msg ) ) );
        }
        $model = $this->request->map_request_to_model('EventM_Event_Organizer_Model');
        $model->id = absint(event_m_get_param('id'));

        $user_allow_save = 1;
        if( empty( $id ) ) {
            if( empty( em_check_context_user_capabilities( array( 'create_event_organizers' ) ) ) ) {
                $user_allow_save = 0;
            }
        } else{
            if( empty( em_check_context_user_capabilities( array( 'edit_event_organizers' ) ) ) ) {
                $user_allow_save = 0;
            }
            if( empty( em_check_context_user_capabilities( array( 'edit_others_event_organizers' ) ) ) ) {
                if( $model->created_by != get_current_user_id() ) {
                    $user_allow_save = 0;
                }
            }
        }
        // if user not allow to save then through error
        if( $user_allow_save == 0 ) {
            $error_msg = esc_html__( 'You have no permission.', 'eventprime-event-calendar-management' );
            wp_send_json_error( array( 'errors' => array( $error_msg ) ) );
        }

        // Validate data
        $errors = $this->services['event_organizer']->validate($model);
        if (!empty($errors)) {
            wp_send_json_error(array('errors'=>$errors));
        }
        $model->organizer_websites = event_m_get_param('organizer_websites');
        foreach($model->organizer_websites as $key => $val) { $model->organizer_websites[$key] = esc_url($val); }
        foreach($model->social_links as $key => $val) { $model->social_links->$key = esc_url($val); }
        // set user id for term 
        if( empty( $model->id ) ) {
            $model->created_by = get_current_user_id();
        } else{
            $model->last_updated_by = get_current_user_id();
        }

        $event_organizer = $this->services['event_organizer']->save($model);
        if ($event_organizer instanceof WP_Error) {
            $error_msg= $event_organizer->get_error_message(); 
            wp_send_json_error(array('errors'=>array($error_msg)));
        }
        $redirect = admin_url('admin.php/?page=em_event_organizers');
        wp_send_json_success(array('redirect'=>$redirect));
    }

    public function get_organizer_data() {
        $response = new stdClass();
        $term_id = event_m_get_param('term_id');
        $event_id = event_m_get_param('event_id');
        $response->post = $this->services['event_organizer']->get_organizer($term_id);
        echo json_encode($response);
        die;
    }

    private function admin_event_organizers_search(){
        $em_organizer_nonce = event_m_get_param('em_organizer_nonce');
        if( empty( $em_organizer_nonce ) || !wp_verify_nonce( $em_organizer_nonce, 'em_organizer_object_nonce' ) || empty( em_check_context_user_capabilities( array( 'view_event_organizers' ) ) ) ) {
            $error_msg = esc_html__( 'Unauthorized Access', 'eventprime-event-calendar-management' );
            wp_send_json_error( array( 'errors' => array( $error_msg ) ) );
        }
        $response = $this->services['event_organizer']->load_list_page_with_search();
        wp_send_json_success($response);
    }

    private function admin_performers_search(){
        $em_performer_nonce = event_m_get_param('em_performer_nonce');
        if( empty( $em_performer_nonce ) || !wp_verify_nonce( $em_performer_nonce, 'em_performer_object_nonce' ) || empty( em_check_context_user_capabilities( array( 'view_event_performers' ) ) ) ) {
            $error_msg = esc_html__( 'Unauthorized Access', 'eventprime-event-calendar-management' );
            wp_send_json_error( array( 'errors' => array( $error_msg ) ) );
        }
        $response=$this->services['performer']->load_list_page_with_search();
        wp_send_json_success($response);
    }

    private function admin_event_types_search(){
        $em_event_type_nonce = event_m_get_param('em_event_type_nonce');
        if( empty( $em_event_type_nonce ) || !wp_verify_nonce( $em_event_type_nonce, 'em_event_type_object_nonce' ) || empty( em_check_context_user_capabilities( array( 'view_event_types' ) ) ) ) {
            $error_msg = esc_html__( 'Unauthorized Access', 'eventprime-event-calendar-management' );
            wp_send_json_error( array( 'errors' => array( $error_msg ) ) );
        }
        $response=$this->services['event_type']->load_list_page_with_search();
        wp_send_json_success($response);
    }

    private function admin_venues_search(){
        $em_event_site_nonce = event_m_get_param('em_event_site_nonce');
        if( empty( $em_event_site_nonce ) || !wp_verify_nonce( $em_event_site_nonce, 'em_venue_object_nonce' ) || empty( em_check_context_user_capabilities( array( 'view_event_sites' ) ) ) ) {
            $error_msg = esc_html__( 'Unauthorized Access', 'eventprime-event-calendar-management' );
            wp_send_json_error( array( 'errors' => array( $error_msg ) ) );
        }
        $response= $this->services['venue']->load_list_page_with_search();
        wp_send_json_success($response);
    }

    private function admin_custom_user_caps(){
        $em_user_cap_nonce = event_m_get_param('em_user_cap_nonce');
        $user_allow = 1;
        if( empty( $em_user_cap_nonce ) || !wp_verify_nonce( $em_user_cap_nonce, 'em_user_cap_object_nonce' ) || empty( em_check_context_user_capabilities( array( 'manage_options' ) ) ) ) {
            $user_allow = 0;
        }
        if( empty( $user_allow ) ) {
            $error_msg = esc_html__( 'Unauthorized Access', 'eventprime-event-calendar-management' );
            wp_send_json_error( array( 'errors' => array( $error_msg ) ) );
        }
        $user_service = EventM_Factory::get_service('EventM_User_Service');
        $response = $user_service->load_user_cap_edit_page();
        if( isset( $response->error ) && ! empty( $response->error ) ) {
            $error_msg = esc_html__( 'Unauthorized Access', 'eventprime-event-calendar-management' );
            wp_send_json_error( array( 'errors' => array( $error_msg ) ) );
        }
        wp_send_json_success($response);
    }

    public function save_user_custom_caps() {
        $em_user_cap_nonce = event_m_get_param('em_user_cap_nonce');
        $user_allow = 1;
        if( empty( $em_user_cap_nonce ) || !wp_verify_nonce( $em_user_cap_nonce, 'em_user_cap_object_nonce' ) || empty( em_check_context_user_capabilities( array( 'manage_options' ) ) ) ) {
            $user_allow = 0;
        }
        if( empty( $user_allow ) ) {
            $error_msg = esc_html__( 'Unauthorized Access', 'eventprime-event-calendar-management' );
            wp_send_json_error( array( 'errors' => array( $error_msg ) ) );
        }

        $em_user_capabilities = $_POST['em_user_capabilities'];
        if( !empty( $em_user_capabilities ) ) {
            $user_service = EventM_Factory::get_service('EventM_User_Service');
            $response = $user_service->update_user_custom_caps( $em_user_capabilities );
        }
        wp_send_json_success(array('message' => esc_html__('Capabilities updated successfully', 'eventprime-event-calendar-management')));
    }

    public function load_performers_card_data(){
        $paged = event_m_get_param('page');
        $performer_limit = event_m_get_param('show');
        $featured = event_m_get_param('featured');
        $em_search = event_m_get_param('em_search');
        $em_search = ($em_search != 'false') ? $em_search : '';
        $performer_cols = absint(event_m_get_param('cols'));
        $performer_cols = ($performer_cols == 0 || $performer_cols > 12) ? 4 : $performer_cols;
        $args = array(
            'orderby' => 'date',
            'posts_per_page' => $performer_limit,
            'offset'=> (int) ($paged-1) * $performer_limit,
            'paged' => $paged,
                's' => $em_search
        );
        if($featured == 1){ 
            $args['meta_query'] = array(
                array(	
                    'relation' => 'OR',
                    array(
                        'key' => em_append_meta_key('display_front'),
                        'value' => 1,
                        'compare' => '='
                    ),
                    array(
                        'key'     => em_append_meta_key('display_front'),
                        'value'   => 'true',
                        'compare' => '='
                    )
                ),
                array(
                    'key'     => em_append_meta_key('is_featured'),
                    'value'   => 1
                )
            );
        }
        $the_query= $this->services['performer']->get_all_performers_query($args);
        $html = '';
        $performers = $the_query->posts;
        $global_settings = $this->services['setting']->load_model_from_db();
        $performers_page_url= get_permalink(em_global_settings("performers_page"));
        if(!empty($performers)){
            foreach($performers as $performer){
                $performers_page_url= get_permalink( em_global_settings("performers_page") );
                $performer_url = add_query_arg("performer", $performer->id, $performers_page_url);
                $enable_seo_urls = em_global_settings('enable_seo_urls');
                if(!empty($enable_seo_urls)){
                    $performer_url = get_permalink($performer->id);
                }
                $html .= '<div class="ep-box-col-'.$performer_cols.' ep-col-md-6">
                    <div class="ep-box-card-item">
                        <div class="ep-box-card-thumb">';
                            if (!empty($performer->feature_image_id)){
                                $html .= '<a href="'.$performer_url.'" class="ep-img-link">'.get_the_post_thumbnail($performer->id, 'large').'</a>';
                            }else{
                                $html .= '<a href="'.$performer_url.'" class="ep-img-link"><img src="'.esc_url(plugins_url('templates/images/dummy-performer.png', __FILE__)).'" alt="'.__('Dummy Image','eventprime-event-calendar-management').'"></a>'; 
                            }
                            if (!empty($performer->social_links)){    
                            $html .= '<div class="ep-box-card-social ep-performers-social">';
                                if(isset($performer->social_links->facebook))
                                    $html .= '<a href="'.$performer->social_links->facebook.'" target="_blank" title="Facebook"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M279.14 288l14.22-92.66h-88.91v-60.13c0-25.35 12.42-50.06 52.24-50.06h40.42V6.26S260.43 0 225.36 0c-73.22 0-121.08 44.38-121.08 124.72v70.62H22.89V288h81.39v224h100.17V288z"/></svg></a>';
                                if(isset($performer->social_links->instagram))
                                    $html .= '<a href="'.$performer->social_links->instagram.'" target="_blank" title="Instagram"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"/></svg></a>';
                                if(isset($performer->social_links->linkedin))
                                    $html .= '<a href="'.$performer->social_links->linkedin.'" target="_blank" title="Linkedin"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M100.28 448H7.4V148.9h92.88zM53.79 108.1C24.09 108.1 0 83.5 0 53.8a53.79 53.79 0 0 1 107.58 0c0 29.7-24.1 54.3-53.79 54.3zM447.9 448h-92.68V302.4c0-34.7-.7-79.2-48.29-79.2-48.29 0-55.69 37.7-55.69 76.7V448h-92.78V148.9h89.08v40.8h1.3c12.4-23.5 42.69-48.3 87.88-48.3 94 0 111.28 61.9 111.28 142.3V448z"/></svg></a>';
                                if(isset($performer->social_links->twitter))
                                    $html .= '<a href="'.$performer->social_links->twitter.'" target="_blank" title="Twitter"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M459.37 151.716c.325 4.548.325 9.097.325 13.645 0 138.72-105.583 298.558-298.558 298.558-59.452 0-114.68-17.219-161.137-47.106 8.447.974 16.568 1.299 25.34 1.299 49.055 0 94.213-16.568 130.274-44.832-46.132-.975-84.792-31.188-98.112-72.772 6.498.974 12.995 1.624 19.818 1.624 9.421 0 18.843-1.3 27.614-3.573-48.081-9.747-84.143-51.98-84.143-102.985v-1.299c13.969 7.797 30.214 12.67 47.431 13.319-28.264-18.843-46.781-51.005-46.781-87.391 0-19.492 5.197-37.36 14.294-52.954 51.655 63.675 129.3 105.258 216.365 109.807-1.624-7.797-2.599-15.918-2.599-24.04 0-57.828 46.782-104.934 104.934-104.934 30.213 0 57.502 12.67 76.67 33.137 23.715-4.548 46.456-13.32 66.599-25.34-7.798 24.366-24.366 44.833-46.132 57.827 21.117-2.273 41.584-8.122 60.426-16.243-14.292 20.791-32.161 39.308-52.628 54.253z"/></svg></a>';
                            $html .= '</div>';
                            }
              $html .= '</div>
                        <div class="ep-box-card-content">
                            <div class="ep-box-title ep-box-card-title"><a
                            href="'.$performer_url.'">
                            '.$performer->name.'</a> </div>';

                            if(!empty($performer->role)){ 
                                $html .= '<div class="ep-box-card-role ep-performer-role">'.$performer->role.'</div>'; 
                            }
              $html .= '</div>
                    </div>
                </div>';
            }
        }
        wp_send_json_success($html);
    }

    public function load_performers_box_data(){
        $paged = event_m_get_param('page');
        $performer_limit = event_m_get_param('show');
        $featured = event_m_get_param('featured');
        $em_search = event_m_get_param('em_search');
        $em_search = ($em_search != 'false') ? $em_search : '';
        $performer_cols = absint(event_m_get_param('cols'));
        $performer_cols = ($performer_cols == 0 || $performer_cols > 12) ? 4 : $performer_cols;
        $args = array(
            'orderby' => 'date',
            'posts_per_page' => $performer_limit,
            'offset'=> (int) ($paged-1) * $performer_limit,
            'paged' => $paged,
            's' => $em_search, 
        );
        if($featured == 1){ 
            $args['meta_query'] = array(
                array(	
                    'relation' => 'OR',
                    array(
                        'key' => em_append_meta_key('display_front'),
                        'value' => 1,
                        'compare' => '='
                    ),
                    array(
                        'key'     => em_append_meta_key('display_front'),
                        'value'   => 'true',
                        'compare' => '='
                    )
                ),
                array(
                    'key'     => em_append_meta_key('is_featured'),
                    'value'   => 1
                )
            );
        }
        $the_query= $this->services['performer']->get_all_performers_query($args);
        $html = '';
        $performers = $the_query->posts;
        $global_settings = $this->services['setting']->load_model_from_db();
        $performers_page_url= get_permalink(em_global_settings("performers_page"));
        if(!empty($performers)){
            $b = event_m_get_param('bnum');
            $performer_box_color = em_global_settings('performer_box_color');
            foreach($performers as $performer){
                $performer_url = add_query_arg("performer", $performer->id, $performers_page_url);
                $enable_seo_urls = em_global_settings('enable_seo_urls');
                if(!empty($enable_seo_urls)){
                    $performer_url = get_permalink($performer->id);
                }
                if($b > 4) { $b = 1;}
                switch ($b) {
                    case 1 :
                        $bg_color = (!empty($performer_box_color) && isset($performer_box_color[0])) ? '#'.$performer_box_color[0] : '#A6E7CF';
                        break;
                    case 2 :
                        $bg_color = (!empty($performer_box_color) && isset($performer_box_color[1])) ? '#'.$performer_box_color[1] : '#DBEEC1';
                        break;
                    case 3 :
                        $bg_color = (!empty($performer_box_color) && isset($performer_box_color[2])) ? '#'.$performer_box_color[2] : '#FFD3B6';
                        break;
                    case 4 :
                        $bg_color = (!empty($performer_box_color) && isset($performer_box_color[3])) ? '#'.$performer_box_color[3] : '#FFA9A5';
                        break;
                    default:
                        $bg_color = '#A6E7CF';
                }
                $light_bg_color = ep_hex2rgba($bg_color, .5);
                $bg_color = ep_hex2rgba($bg_color, 1);

                $html .= '<div class="ep-box-col-'.$performer_cols.' ep-box-column ep-box-px-0" data-id="'.$performer->id.'" data-element_type="column">
                    <div class="ep-column-wrap ep-column-populated" style="background-image: linear-gradient(190deg,'.$bg_color.','.$light_bg_color.'); background-color: transparent;">
                        <div class="ep-box-widget-wrap">
                            <div class="ep-element ep-element-c95b9de ep-widget ep-widget-ep-pro-performer"
                             data-id="'.$performer->id.'" data-element_type="widget" data-widget_type="ep-pro-performer.default">
                                <div class="ep-widget-container">
                                    <div class="ep-performer-wrapper performer-style1">
                                        <div class="ep-box-box-item">
                                            <div class="ep-box-box-thumb">';
                                                if (!empty($performer->feature_image_id)){
                                                    $html .= '<a href="'.$performer_url.'" class="img-fluid">'.get_the_post_thumbnail($performer->id, 'large').'</a>';
                                                }else{ 
                                                    $html .= '<img src="'.esc_url(plugins_url('templates/images/dummy-performer.png', __FILE__)).'" class="img-fluid" alt="'.__('Dummy Image','eventprime-event-calendar-management').'">'; 
                                                }         
                                  $html .= '</div>
                                            <div class="ep-performer-content">
                                                <div class="ep-box-title ep-box-box-title"><a href="'.$performer_url.'">'.$performer->name.'</a> </div>';
                                                if(!empty($performer->role)){ 
                                                    $html .= '<div class="ep-box-card-role ep-performer-role">'.$performer->role.'</div>';
                                                }
                                                if (!empty($performer->social_links)){    
                                                $html .= '<div class="ep-performers-social">';
                                                    if(isset($performer->social_links->facebook))
                                                        $html .= '<a href="'.$performer->social_links->facebook.'" target="_blank" title="Facebook"> <i class="fab fa-facebook-f"></i></a>';
                                                    if(isset($performer->social_links->instagram))
                                                        $html .= '<a href="'.$performer->social_links->instagram.'" target="_blank" title="Instagram"><i class="fab fa-instagram"></i></a>';
                                                    if(isset($performer->social_links->linkedin))
                                                        $html .= '<a href="'.$performer->social_links->linkedin.'" target="_blank" title="Linkedin"><i class="fab fa-linkedin"></i></a>';
                                                    if(isset($performer->social_links->twitter))
                                                        $html .= '<a href="'.$performer->social_links->twitter.'" target="_blank" title="Twitter"><i class="fab fa-twitter"></i></a>';
                                                $html .= '</div>';
                                                }
                                  $html .= '</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>';
                $b++;
            }
        }
        wp_send_json_success($html);
    }

    public function load_performers_list_data(){
        $paged = event_m_get_param('page');
        $performer_limit = event_m_get_param('show');
        $featured = event_m_get_param('featured');
        $em_search = event_m_get_param('em_search');
        $em_search = ($em_search != 'false') ? $em_search : '';
        $args = array(
            'orderby' => 'date',
            'posts_per_page' => $performer_limit,
            'offset'=> (int) ($paged-1) * $performer_limit,
            'paged' => $paged,
            's' => $em_search
        );
        if($featured == 1){ 
            $args['meta_query'] = array(
                array(	
                    'relation' => 'OR',
                    array(
                        'key' => em_append_meta_key('display_front'),
                        'value' => 1,
                        'compare' => '='
                    ),
                    array(
                        'key'     => em_append_meta_key('display_front'),
                        'value'   => 'true',
                        'compare' => '='
                    )
                ),
                array(
                    'key'     => em_append_meta_key('is_featured'),
                    'value'   => 1
                )
            );
        }
        $the_query= $this->services['performer']->get_all_performers_query($args);
        $html = '';
        $performers = $the_query->posts;
        $performers_page_url= get_permalink(em_global_settings("performers_page"));
        if(!empty($performers)){
            foreach($performers as $performer){
                $performers_page_url= get_permalink( em_global_settings("performers_page") );
                $performer_url = add_query_arg("performer", $performer->id, $performers_page_url);
                $enable_seo_urls = em_global_settings('enable_seo_urls');
                if(!empty($enable_seo_urls)){
                    $performer_url = get_permalink($performer->id);
                }
            $html .= '<div class="ep-box-list-wrap">
                    <div class="ep-box-row">
                        <div class="ep-box-col-4 ep-list-box-table ep-box-profile-image">';
                            if (!empty($performer->feature_image_id)){
                            $html .= '<a href="'.$performer_url.'" >'.get_the_post_thumbnail($performer->id, 'large').'</a>';
                            }else{
                            $html .= '<img src="'.esc_url(plugins_url('templates/images/dummy-performer.png', __FILE__)).'" alt="'.__('Dummy Image','eventprime-event-calendar-management').'">';
                            } 
              $html .= '</div>
                        <div class="ep-box-col-6 ep-list-box-table">
                            <div class="ep-box-list-items">
                                <div class="ep-box-title ep-box-list-title">
                                    <a class="ep-color-hover" data-performer-id="'.$performer->id.'" href="'.add_query_arg("performer",$performer->id, $performers_page_url).'" target="_self" rel="noopener">
                                        '.$performer->name.'
                                    </a>
                                </div>';
                                if(!empty($performer->role)){ 
                                    $html .= '<div class="ep-box-card-role ep-performer-role">'.$performer->role.'</div>';
                                }
                  $html .= '<div class="ep-event-description">
                                <div class="ep-event-meta ep-color-before">
                                    <div class="ep-time-details">
                                        <span class="ep-box-phone">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="18px" viewBox="0 0 24 24" width="18px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M6.54 5c.06.89.21 1.76.45 2.59l-1.2 1.2c-.41-1.2-.67-2.47-.76-3.79h1.51m9.86 12.02c.85.24 1.72.39 2.6.45v1.49c-1.32-.09-2.59-.35-3.8-.75l1.2-1.19M7.5 3H4c-.55 0-1 .45-1 1 0 9.39 7.61 17 17 17 .55 0 1-.45 1-1v-3.49c0-.55-.45-1-1-1-1.24 0-2.45-.2-3.57-.57-.1-.04-.21-.05-.31-.05-.26 0-.51.1-.71.29l-2.2 2.2c-2.83-1.45-5.15-3.76-6.59-6.59l2.2-2.2c.28-.28.36-.67.25-1.02C8.7 6.45 8.5 5.25 8.5 4c0-.55-.45-1-1-1z"/></svg></span>
                                        <span class="ep-start-time">';
                                            if (!empty($performer->performer_phones)) 
                                                $html .= implode(', ',$performer->performer_phones); 
                                            else 
                                                $html .= '--';
                             $html .=  '</span>
                                    </div>
                                    <div class="ep-time-details">
                                        <span class="ep-box-email">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="18px" viewBox="0 0 24 24" width="18px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M22 6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6zm-2 0l-8 5-8-5h16zm0 12H4V8l8 5 8-5v10z"/></svg></span>
                                        <span class="ep-start-time">'; 
                                            if (!empty($performer->performer_emails)) 
                                                {foreach($performer->performer_emails as $key => $val) {
                                                $performer->performer_emails[$key] = '<a href="mailto:'.$val.'">'.htmlentities($val).'</a>';
                                                }
                                                $html .= implode(', ',$performer->performer_emails);
                                            } 
                                            else 
                                                $html .= '--';
                            $html .= '</span>
                                    </div>
                                    <div class="ep-time-details">
                                        <span class="ep-box-website">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0z" fill="none"/><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zm6.93 6h-2.95c-.32-1.25-.78-2.45-1.38-3.56 1.84.63 3.37 1.91 4.33 3.56zM12 4.04c.83 1.2 1.48 2.53 1.91 3.96h-3.82c.43-1.43 1.08-2.76 1.91-3.96zM4.26 14C4.1 13.36 4 12.69 4 12s.1-1.36.26-2h3.38c-.08.66-.14 1.32-.14 2 0 .68.06 1.34.14 2H4.26zm.82 2h2.95c.32 1.25.78 2.45 1.38 3.56-1.84-.63-3.37-1.9-4.33-3.56zm2.95-8H5.08c.96-1.66 2.49-2.93 4.33-3.56C8.81 5.55 8.35 6.75 8.03 8zM12 19.96c-.83-1.2-1.48-2.53-1.91-3.96h3.82c-.43 1.43-1.08 2.76-1.91 3.96zM14.34 14H9.66c-.09-.66-.16-1.32-.16-2 0-.68.07-1.35.16-2h4.68c.09.65.16 1.32.16 2 0 .68-.07 1.34-.16 2zm.25 5.56c.6-1.11 1.06-2.31 1.38-3.56h2.95c-.96 1.65-2.49 2.93-4.33 3.56zM16.36 14c.08-.66.14-1.32.14-2 0-.68-.06-1.34-.14-2h3.38c.16.64.26 1.31.26 2s-.1 1.36-.26 2h-3.38z"/></svg></span>
                                        <span class="ep-start-time">';
                                            if (!empty($performer->performer_websites)) { 
                                                foreach($performer->performer_websites as $key => $val) {
                                                    if(!empty($val)){
                                                        $performer->performer_websites[$key] = '<a href="'.$val.'" target="_blank">'.htmlentities($val).'</a>';
                                                    }
                                                }
                                                $html .=  implode(', ',$performer->performer_websites);
                                            } 
                                            else 
                                                $html .= '--';
                            $html .=  '</span>
                                    </div>
                                    <div class="ep-view-details"><a class="ep-view-details-button" data-event-id="'.$performer->id.'" href="'.$performer_url.'">View Detail</a></div>
                                </div>
                            </div>
                        </div>
                    </div>';
            $html .= '<div class="ep-box-col-2 ep-list-box-table box-boder-l">
                        <ul class="ep-box-social-links">';
                        if (!empty($performer->social_links)){
                            if(isset($performer->social_links->facebook))
                                $html .= '<li class="ep-event-social-icon"><a class="facebook" href="'.$performer->social_links->facebook.'" target="_blank" title="Facebook"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"> <path d="M504 256C504 119 393 8 256 8S8 119 8 256c0 123.78 90.69 226.38 209.25 245V327.69h-63V256h63v-54.64c0-62.15 37-96.48 93.67-96.48 27.14 0 55.52 4.84 55.52 4.84v61h-31.28c-30.8 0-40.41 19.12-40.41 38.73V256h68.78l-11 71.69h-57.78V501C413.31 482.38 504 379.78 504 256z"/></svg></a></li>';
                            if(isset($performer->social_links->instagram))
                                $html .= '<li class="ep-event-social-icon"><a class="whatsapp" href="'.$performer->social_links->instagram.'" target="_blank" title="Instagram"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"/></svg></a></li>';
                            if(isset($performer->social_links->linkedin))
                                $html .= '<li class="ep-event-social-icon"><a class="linkedin" href="'.$performer->social_links->linkedin.'" target="_blank" title="Linkedin"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M100.28 448H7.4V148.9h92.88zM53.79 108.1C24.09 108.1 0 83.5 0 53.8a53.79 53.79 0 0 1 107.58 0c0 29.7-24.1 54.3-53.79 54.3zM447.9 448h-92.68V302.4c0-34.7-.7-79.2-48.29-79.2-48.29 0-55.69 37.7-55.69 76.7V448h-92.78V148.9h89.08v40.8h1.3c12.4-23.5 42.69-48.3 87.88-48.3 94 0 111.28 61.9 111.28 142.3V448z"/></svg></a></li>';
                            if(isset($performer->social_links->twitter))
                                $html .= '<li class="ep-event-social-icon"><a class="twitter" href="'.$performer->social_links->twitter.'" target="_blank" title="Twitter"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M459.37 151.716c.325 4.548.325 9.097.325 13.645 0 138.72-105.583 298.558-298.558 298.558-59.452 0-114.68-17.219-161.137-47.106 8.447.974 16.568 1.299 25.34 1.299 49.055 0 94.213-16.568 130.274-44.832-46.132-.975-84.792-31.188-98.112-72.772 6.498.974 12.995 1.624 19.818 1.624 9.421 0 18.843-1.3 27.614-3.573-48.081-9.747-84.143-51.98-84.143-102.985v-1.299c13.969 7.797 30.214 12.67 47.431 13.319-28.264-18.843-46.781-51.005-46.781-87.391 0-19.492 5.197-37.36 14.294-52.954 51.655 63.675 129.3 105.258 216.365 109.807-1.624-7.797-2.599-15.918-2.599-24.04 0-57.828 46.782-104.934 104.934-104.934 30.213 0 57.502 12.67 76.67 33.137 23.715-4.548 46.456-13.32 66.599-25.34-7.798 24.366-24.366 44.833-46.132 57.827 21.117-2.273 41.584-8.122 60.426-16.243-14.292 20.791-32.161 39.308-52.628 54.253z"/></svg></a></li>';
                        }   
            $html .= '</ul>
                    </div>
                </div>
            </div>';
            }
        }
        wp_send_json_success( array( 'html' => $html ) );
    }

    public function load_performer_events_card_block(){
        $paged = event_m_get_param('page');
        $performer_id = event_m_get_param('p_id');
        $single_performer_event_limit = event_m_get_param("show");
        $event_cols = event_m_get_param("cols");
        $event_cols = ($event_cols == '' || $event_cols == 0 || $event_cols > 12) ? 4 : $event_cols;
        $args = array(
            'orderby' => em_append_meta_key('start_date'),
            'posts_per_page' => $single_performer_event_limit,
            'offset'=> (int) ($paged-1) * $single_performer_event_limit,
            'paged' => $paged,
        );
        $upcoming_events = $this->services['event']->upcoming_events_for_performer( $performer_id, $args );
        $posts = $upcoming_events->posts;
        $posts = apply_filters('ep_filter_front_events', $posts, $atts = array() );
        $posts = array_filter($posts, function($post){ return $post->post_status !== 'draft'; });
        $timestamp = time();
        $i = 0;
        $gs_service = EventM_Factory::get_service('EventM_Setting_Service');
        $global_settings = $gs_service->load_model_from_db();
        $showBookNowForGuestUsers = em_show_book_now_for_guest_users();
        $html = '';$recurring = 1; $column_class = '';
        if(event_m_get_param('recurring')){
            $recurring = event_m_get_param('recurring');
        }
        $section_id = '';
        if(empty($section_id)){ $ep_card_cls = 'ep-event-box-card'; } else{ $ep_card_cls = 'em_card_edt';}
        foreach ($posts as $post) :
            $event = $this->services['event']->load_model_from_db($post->ID);
            if(empty($recurring) && isset($event->parent) && !empty($event->parent)){
                continue;
            }
            $currency_symbol = em_currency_symbol();
            $booking_allowed = 1;
            if((isset($event->parent) && !empty($event->parent)) && (isset($event->enable_recurrence_automatic_booking) && !empty($event->enable_recurrence_automatic_booking))){
                // if event is recurring and parent has automatic booking enable than not allowed
                $booking_allowed = 0;
            }
            $event->url = em_get_single_event_page_url( $event, $global_settings );
            $emcardEpired ='';
            if (em_is_event_expired($event->id)) {
                $emcardEpired ='emcard-expired';
            }
            $emcardDisable = '';
            if((empty($event->enable_booking) && absint($event->custom_link_enabled) == 0)){
                $emcardDisable = 'em_event_disabled';
            }
            $html .= '<div class="'.$column_class.' ep-box-col-'.$event_cols.'">
                    <div class="'.$ep_card_cls.' '.$emcardEpired.''.$column_class.''.$emcardDisable.'" id="em-event-'.$event->id.'">';
                $html .= '<div class="em_event_cover dbfl">';
                    $thumbImage = esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png');
                    if (!empty($event->cover_image_id)): ?>
                        <?php 
                        $thumbImage = wp_get_attachment_image_src($event->cover_image_id, 'large')[0];
                        if(empty($thumbImage)){
                            $thumbImage = get_the_post_thumbnail($event->id,'large');
                            if(isset($event->parent) && !empty($event->parent) && empty($thumbImage)){
                                $thumbImage = get_the_post_thumbnail($event->parent,'large');
                            }
                        }
                        $html .='<a href="'.$event->url.'"><img src="'.$thumbImage.'"></a>';
                        else:
                        $html .='<a href="'.$event->url.'"><img src="'.esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png').'" class="em-no-image" ></a>';
                    endif;
                $html .='</div>';
                
                $html .='<div class="dbfl em-card-description">';
                    $html .='<div class="em_event_title"  title="'.$event->name.'">';
                        $html .='<a href="'.$event->url.'">'.$event->name.'</a>';
                        if(is_user_logged_in()):
                            ob_start();
                                do_action('event_magic_wishlist_link',$event);
                                $custom_data_wishlist_link = ob_get_contents();
                            ob_end_clean();
                            $html .= $custom_data_wishlist_link;
                        endif;
                    $html .='</div>';
                    $start_date = null; $end_date = null; $start_time = null; $end_time = null; $day = null;
                        if (em_compare_event_dates($event->id)){
                            $day = date_i18n(get_option('date_format'),$event->start_date);
                            $start_time = date_i18n(get_option('time_format'),$event->start_date);
                            $end_time = date_i18n(get_option('time_format'),$event->end_date);
                        }
                        else
                        {
                            $start_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->start_date);
                            $end_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->end_date);
                        }
                    if($event->all_day):
                        $html.='<div class="ep-card-event-date-wrap ep-box-row ep-box-center">'
                            .'<span class="ep-box-col-2"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg></span>'
                            . '<div class="ep-card-event-date ep-box-col-10 em_event_start difl em_wrap">'.date_i18n(get_option('date_format'),$event->start_date).'<span class="em-all-day"> - '.__('ALL DAY','eventprime-event-calendar-management').'</span>
                        </div></div>';
                    elseif(!empty($day)):
                        $html .= '<div class="em_event_start difl em_wrap">'.$day.'</div>';
                        $html .= '<div class="em_event_start difl em_wrap">'.$start_time.'  to  '.$end_time.'</div>';
                    else:
                        $html .= '<div class="em_event_start difl em_wrap">'.$start_date.' -    
                        </div>
                        <div class="em_event_start difl em_wrap">'.$end_date.' 
                        </div>';
                    endif;
                $html .='</div>';
                $html .='<div class="ep-single-box-footer dbfl">
                    <div class="em_event_price  difl">';
                        $ticket_price = $event->ticket_price;
                        if($event->show_fixed_event_price){
                            if($event->fixed_event_price > 0){
                                $ticket_price = $event->fixed_event_price;
                            }
                        }
                        if(!is_numeric($ticket_price)){
                            $html .= $ticket_price;
                        }
                        else{
                            $html .= !empty($ticket_price) ? em_price_with_position($ticket_price) : '';
                        }
                    $html .='</div>';
                    $html .=do_action('event_magic_card_view_after_price',$event);
                    $html .='<div class="ep-single-box-tickets-button difr">
                        <div class="em_event_attr_box em_eventpage_register difl">';
                            
                            if(absint($event->custom_link_enabled) == 1):
                                $html .='<div class="em_header_button em_event_custom_link kf-tickets">
                                    <a class="ep-event-custom-link" target="_blank" href="'.$event->url.'">';
                                            
                                        if(!empty($global_settings->hide_event_custom_link) && !is_user_logged_in()){
                                            $html .= em_global_settings_button_title('Login to View');
                                        }
                                        else{
                                            $html .= em_global_settings_button_title('Click for Details');
                                        }
                                    $html .='</a>';
                                $html ='</div>';
                            elseif($this->services['event']->is_bookable($event)): $current_ts = em_current_time_by_timezone();
                                if($event->status=='expired'):
                                        $html .= '<div class="em_header_button em_event_expired kf-tickets">'.em_global_settings_button_title('Bookings Expired').'</div>';
                                elseif($current_ts>$event->last_booking_date):
                                        $html .='<div class="em_header_button em_booking-closed kf-tickets">'.em_global_settings_button_title('Bookings Closed').'</div>';
                                elseif($current_ts < $event->start_booking_date): 
                                        $html .='<div class="em_header_button em_not_started kf-tickets">'.em_global_settings_button_title('Bookings not started yet').'</div>';
                                else: 
                                    if(!empty($booking_allowed)):
                                        if(is_user_logged_in() || $showBookNowForGuestUsers):
                                            $html .='<form action="'.get_permalink($global_settings->booking_page).'" method="post" name="em_booking">
                                                <a class="em_header_button em_event-booking kf-tickets" name="tickets" onclick="em_event_booking('.$event->id.')" id="em_booking">'.em_global_settings_button_title('Book Now').'</a>
                                                <input type="hidden" name="event_id" value="'.$event->id.'" />
                                                <input type="hidden" name="venue_id" value="'.$event->venue.'" />
                                            </form>';
                                        else: 
                                            $html .='<a class="em_header_button kf-tickets" target="_blank" href="'. add_query_arg('event_id',$event->id,get_permalink($global_settings->profile_page)).'">'.em_global_settings_button_title('Book Now').'</a>';
                                        endif;
                                    endif;
                                endif;
                            elseif($event->status == 'publish'):
                                if(isset($event->standing_capacity) && !empty($event->standing_capacity)):
                                    $html .='<div class="em_event_attr_box em_eventpage_register difl">
                                        <div class="em_header_button em_not_bookable kf-tickets">'.em_global_settings_button_title('All Seats Booked').'</div>
                                    </div>';
                                else:
                                    $html .='<div class="em_event_attr_box em_eventpage_register difl">
                                        <div class="em_header_button em_not_bookable kf-tickets">'. em_global_settings_button_title('Bookings Closed').'</div>
                                    </div>';
                                endif;
                            endif;
                        $html.='</div>';
                    $html.='</div>';
                $html.='</div>';
                $html.=do_action('event_magic_card_view_after_footer',$event);
            $html.='</div></div>';
        
            $i++;
        endforeach; 
        wp_send_json_success($html);
    }

    public function load_performer_events_list_block(){
        $paged = event_m_get_param('page');
        $performer_id = event_m_get_param('p_id');
        $single_performer_event_limit = event_m_get_param("show");
        $args = array(
            'orderby' => em_append_meta_key('start_date'),
            'posts_per_page' => $single_performer_event_limit,
            'offset'=> (int) ($paged-1) * $single_performer_event_limit,
            'paged' => $paged,
        );
        $upcoming_events = $this->services['event']->upcoming_events_for_performer( $performer_id, $args );
        $html = $last_month_id = ''; $recurring = 1;
        if(event_m_get_param('recurring')){
            $recurring = event_m_get_param('recurring');
        }
        $showBookNowForGuestUsers = em_show_book_now_for_guest_users();
        $posts= $upcoming_events->posts;
        $posts = array_filter($posts, function($post){ return $post->post_status !== 'draft'; });
        if(!empty($posts)){
            $last_month_id = event_m_get_param('last_month_id');
            $gs_service = EventM_Factory::get_service('EventM_Setting_Service');
            $global_settings = $gs_service->load_model_from_db();
            $currency_symbol = em_currency_symbol();
            foreach ($posts as $post){
                $event = $this->services['event']->load_model_from_db($post->ID);
                $month_id = date('Ym', $event->start_date);
                if(empty($last_month_id) || $last_month_id != $month_id){
                    $last_month_id = $month_id;
                    $html .= '<div class="ep-month-divider"><span class="ep-listed-event-month">'.date_i18n('F Y', $event->start_date).'<span class="ep-listed-event-month-tag"></span></span></div>';
                }
                $booking_allowed = 1;
                if((isset($event->parent) && !empty($event->parent)) && (isset($event->enable_recurrence_automatic_booking) && !empty($event->enable_recurrence_automatic_booking))){
                    // if event is recurring and parent has automatic booking enable than not allowed
                    $booking_allowed = 0;
                }
                $event_page = get_permalink(em_global_settings("events_page"));
                $event->url = em_get_single_event_page_url($event, $global_settings);
                $emexpired = '';
                if(em_is_event_expired($event->id)){
                    $emexpired = 'emlist-expired';
                }
                if(empty($event->enable_booking)){
                    $emexpired .= 'em_event_disabled';
                }
                $html .= '<div  id="em-event-'.$event->id.'" class="ep-event-article '.$emexpired.'">';
                    $html .= '<div class="ep-topsec">
                        <div class="em-col-3 difl ep-event-image-wrap ep-col-table-c">
                            <div class="em_event_cover_list dbfl">';
                                $thumbImage = esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png');
                                if (!empty($event->cover_image_id)):
                                    $thumbImage = wp_get_attachment_image_src($event->cover_image_id, 'large')[0];
                                    if(empty($thumbImage)){
                                        $thumbImage = get_the_post_thumbnail($event->id,'large');
                                        if(isset($event->parent) && !empty($event->parent) && empty($thumbImage)){
                                            $thumbImage = get_the_post_thumbnail($event->parent,'large');
                                        }
                                    }
                                    $html .= '<a href="'.$event->url.'">
                                        <img src="'.$thumbImage.'" alt="'.__('Event Cover Image', 'eventprime-event-calendar-management').'">
                                    </a>';
                                else:
                                    $html .= '<a href="'.$event->url.'"><img src="'.esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png').'" alt="'.__('Dummy Image','eventprime-event-calendar-management').'" class="em-no-image" ></a>';
                                endif;
                            $html .= '</div>
                        </div>';

                        $html .= '<div class="em-col-5 difl ep-col-table-c ep-event-content-wrap">
                            <div class="ep-event-content">';
                                $html .= '<h3 class="ep-event-title"><a class="ep-color-hover" data-event-id="'.$event->id.'" href="'.$event->url.'" target="_self">'.$event->name.'</a>
                                </h3>';
                                if(is_user_logged_in()):
                                    ob_start();
                                        do_action('event_magic_wishlist_link',$event);
                                        $wishlist = ob_get_contents();
                                    ob_end_clean();
                                    $html .= $wishlist;
                                endif;
                                if(!empty($event->description)) {
                                    $html .=  '<div class="ep-event-description">'.$event->description.'</div>';
                                }
                            $html .= '</div>';
                            $html .= do_action('event_magic_card_view_after_price', $event);
                        $html .= '</div>';

                        $html .='<div class="em-col-4 difl ep-col-table-c ep-event-meta-wrap">
                            <div class="ep-event-meta ep-color-before">'; 
                                $start_date = null; $end_date = null; $start_time = null; $end_time = null; $day = null;
                                if (em_compare_event_dates($event->id)){
                                    $day = date_i18n(get_option('date_format'),$event->start_date);
                                    $start_time = date_i18n(get_option('time_format'),$event->start_date);
                                    $end_time = date_i18n(get_option('time_format'),$event->end_date);
                                }
                                else{
                                    $start_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->start_date);
                                    $end_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->end_date);
                                }
                                if($event->all_day){
                                    $html .= '<div class="ep-list-event-date-row">
                                        <span class="material-icons em_color">date_range</span> 
                                        <div class="ep-list-event-date">'.
                                            date_i18n(get_option('date_format'),$event->start_date).'
                                            <span class="em-all-day"> - '. __('ALL DAY','eventprime-event-calendar-management') . '</span>
                                        </div>
                                    </div>';
                                } elseif(!empty($day)){
                                    $html .= '<div class="ep-list-event-date-row"><span class="material-icons em_color">date_range</span> <div class="ep-list-event-date">'.$day.' - '.$start_time.'  to  '.$end_time.'</div></div>';
                                }
                                else{
                                    $html .= '<div class="ep-list-event-date-row"><span class="material-icons em_color">date_range</span> <div class="ep-list-event-date">'.$start_date.' - '.$end_date.'</div></div>';
                                }
                                if(!empty($event->venue)){  
                                    $venue_service= EventM_Factory::get_service('EventM_Venue_Service');
                                    $venue= $venue_service->load_model_from_db($event->venue);
                                    if(!empty($venue->id) && !empty($venue->address)){
                                        $html .= '<div class="em-list-view-venue-details" title="'.$venue->address.'"><span class="ep-list-event-location"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zM7 9c0-2.76 2.24-5 5-5s5 2.24 5 5c0 2.88-2.88 7.19-5 9.88C9.92 16.21 7 11.85 7 9z"/><circle cx="12" cy="9" r="2.5"/></svg></span><address class="em-list-event-address"><span>'.$venue->address.'</span></address>
                                                </div>';
                                    }
                                }
                                if(!empty($event->enable_booking) && empty($event->hide_booking_status)):
                                    $sum = $this->services['event']->booked_seats($event->id);
                                    $capacity = em_event_seating_capcity($event->id);
                                    $html .= '<div class="ep-list-booking-status dbfl dbfl">
                                        <div class="kf-event-attr-value dbfl">';
                                            if ($capacity > 0):
                                                $html .= '<div class="dbfl">'.$sum .'/'. $capacity.'</div>';
                                                $width = ($sum / $capacity) * 100;
                                                $html .= '<div class="dbfl"><div id="progressbar" class="em_progressbar dbfl"><div style="width:'.$width .'%'.'" class="em_progressbar_fill em_bg" ></div></div></div>';
                                            else:
                                                if($sum > 0){
                                                    $html .= '<div class="dbfl">' . $sum . ' '.__('Attending','eventprime-event-calendar-management').'</div>';
                                                }
                                            endif;
                                        $html .= '</div>
                                    </div>';
                                endif;

                                $custom_data_before_footer = '';
                                ob_start();
                                    do_action('event_magic_popup_custom_data_before_footer',$event);
                                    $custom_data_before_footer = ob_get_contents();
                                ob_end_clean();
                                $html .= $custom_data_before_footer;

                                $html .= ' <div class="ep-list-view-footer">
                                    <div class="em_event_price difl">';
                                        $ticket_price = $event->ticket_price;
                                        // check if show one time event fees at front enable
                                        if($event->show_fixed_event_price){
                                            if($event->fixed_event_price > 0){
                                                $ticket_price = $event->fixed_event_price;
                                            }
                                        }
                                        $html .= !empty($ticket_price) ? $currency_symbol.$ticket_price : '';
                                    $html .= '</div>';
                                    $html .= do_action('event_magic_card_view_after_price',$event);
                                    $html .= '<div class="kf-tickets-button difr">
                                        <div class="em_event_attr_box em_eventpage_register difl">';
                                            if(absint($event->custom_link_enabled) == 1):
                                                $html .= '<div class="em_header_button em_event_custom_link kf-tickets">
                                                    <a class="em_header_button kf-tickets" target="_blank" href="'.$event->url.'">';
                                                        if(!empty(em_global_settings('hide_event_custom_link')) && !is_user_logged_in()){
                                                            $html .= em_global_settings_button_title('Login to View');
                                                        }
                                                        else{
                                                            $html .= em_global_settings_button_title('Click for Details');
                                                        }
                                                    $html .= '</a>
                                                </div>';
                                            elseif($this->services['event']->is_bookable($event)): 
                                                $current_ts = em_current_time_by_timezone();
                                                if($event->status=='expired'):
                                                    $html .= '<div class="em_header_button em_event_expired kf-tickets">'.
                                                    em_global_settings_button_title('Bookings Expired').'</div>';
                                                elseif($current_ts>$event->last_booking_date):
                                                    $html .= '<div class="em_header_button em_booking-closed kf-tickets">'.
                                                    em_global_settings_button_title('Bookings Closed').'</div>';
                                                elseif($current_ts<$event->start_booking_date):
                                                    $html .= '<div class="em_header_button em_not_started kf-tickets">'.
                                                    em_global_settings_button_title('Bookings not started yet').'</div>';
                                                else:
                                                    if(!empty($booking_allowed)):
                                                        if(is_user_logged_in() || $showBookNowForGuestUsers):
                                                            $html .= '<form action="'.get_permalink($global_settings->booking_page).'" method="post" name="em_booking">';
                                                                $html .= '<button class="em_header_button em_event-booking kf-tickets em_color" name="tickets" onclick="em_event_booking('.$event->id.')" id="em_booking">'.
                                                                em_global_settings_button_title('Book Now').'</button>';
                                                                $html .= '<input type="hidden" name="event_id" value="'.$event->id.'" />';
                                                                $html .= '<input type="hidden" name="venue_id" value="'.$event->venue.'" />';
                                                            $html .= '</form>';
                                                        else:
                                                            $html .= '<a class="em_header_button em_event-booking kf-tickets em_color" target="_blank" href="'.add_query_arg('event_id',$event->id,get_permalink($global_settings->profile_page)).'">'.
                                                            em_global_settings_button_title('Book Now').'</a>';
                                                        endif;
                                                    endif;
                                                endif;
                                            elseif($event->status == 'publish'):
                                                if(isset($event->standing_capacity) && !empty($event->standing_capacity)):
                                                    $html .= '<div class="em_event_attr_box em_eventpage_register difl"><div class="em_header_button em_not_bookable kf-tickets">'.em_global_settings_button_title('All Seats Booked').'</div>
                                                    </div>';
                                                else:
                                                    $html .= '<div class="em_event_attr_box em_eventpage_register difl">
                                                        <div class="em_header_button em_not_bookable kf-tickets">'.em_global_settings_button_title('Bookings Closed').'</div>
                                                    </div>';
                                                endif;
                                            endif;
                                        $html .= '</div>';
                                    $html .= '</div>';
                                $html .= '</div>';
                                $html .= do_action('event_magic_card_view_after_footer',$event);
                            $html .= '</div>';
                        $html .= '</div>';
                    $html .= '</div>';
                $html .= '</div>';
            }
                
        }
        wp_send_json_success(array('html' => $html, 'last_month_id' => $last_month_id));
    }

    public function load_performer_events_mini_list_block(){
        $paged = event_m_get_param('page');
        $performer_id = event_m_get_param('p_id');
        $single_performer_event_limit = event_m_get_param("show");
        $event_cols = event_m_get_param("cols");
        $event_cols = ($event_cols == '' || $event_cols == 0 || $event_cols > 12) ? 4 : $event_cols;
        $args = array(
            'orderby' => em_append_meta_key('start_date'),
            'posts_per_page' => $single_performer_event_limit,
            'offset'=> (int) ($paged-1) * $single_performer_event_limit,
            'paged' => $paged,
        );
        $upcoming_events = $this->services['event']->upcoming_events_for_performer( $performer_id, $args );
        $posts = $upcoming_events->posts;
        $posts = apply_filters('ep_filter_front_events', $posts, $atts = array() );
        $posts = array_filter($posts, function($post){ return $post->post_status !== 'draft'; });
        $timestamp = time();
        $i = 0;
        $gs_service = EventM_Factory::get_service('EventM_Setting_Service');
        $global_settings = $gs_service->load_model_from_db();
        $showBookNowForGuestUsers = em_show_book_now_for_guest_users();
        $html = '';$recurring = 1; $column_class = '';
        if(event_m_get_param('recurring')){
            $recurring = event_m_get_param('recurring');
        }
        $section_id = '';
        if(empty($section_id)){ $ep_card_cls = 'ep-event-box-card'; } else{ $ep_card_cls = 'em_card_edt';}
        $today = em_current_time_by_timezone();
        foreach ($posts as $post) :
            $event = $this->services['event']->load_model_from_db($post->ID);
            if(empty($recurring) && isset($event->parent) && !empty($event->parent)){
                continue;
            }
            $currency_symbol = em_currency_symbol();
            $booking_allowed = 1;
            if((isset($event->parent) && !empty($event->parent)) && (isset($event->enable_recurrence_automatic_booking) && !empty($event->enable_recurrence_automatic_booking))){
                // if event is recurring and parent has automatic booking enable than not allowed
                $booking_allowed = 0;
            }
           
            $event->url = em_get_single_event_page_url( $event, $global_settings );
            $emcardEpired ='';
            if (em_is_event_expired($event->id)) {
                $emcardEpired ='emcard-expired';
            }
            $emcardDisable = '';
            if((empty($event->enable_booking) && absint($event->custom_link_enabled) == 0)){
                $emcardDisable = 'em_event_disabled';
            }
            $html .= '<div class="kf-upcoming-event-row em_block dbfl '.$emcardEpired.' '.$emcardDisable.'">
                <div class="kf-upcoming-event-thumb em-col-2 difl">';
                $html .='<a href="'.$event->url.'">';
                $thumbImage = esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png');
                if (!empty($event->cover_image_id)): ?>
                    <?php 
                    $thumbImage = wp_get_attachment_image_src($event->cover_image_id, 'large')[0];
                    if(empty($thumbImage)){
                        $thumbImage = get_the_post_thumbnail($event->id,'large');
                        if(isset($event->parent) && !empty($event->parent) && empty($thumbImage)){
                            $thumbImage = get_the_post_thumbnail($event->parent,'large');
                        }
                    }
                    $html .= '<img src="'.$thumbImage.'" alt="'.__('Event Cover Image', 'eventprime-event-calendar-management').'">';
                    else:
                    $html .= '<img src="'.esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png').'" alt="'.__('Dummy Image','eventprime-event-calendar-management').'" class="em-no-image" >';
                    endif;
                $html .= '</a>
                    </div>';
                
                $html .='<div class="kf-upcoming-event-title em-col-5 em-col-pad20 difl">';
                        $html .='<a href="'.$event->url.'">'.$event->name.'</a>';
                        if(is_user_logged_in()):
                            ob_start();
                                do_action('event_magic_wishlist_link',$event);
                                $custom_data_wishlist_link = ob_get_contents();
                            ob_end_clean();
                            $html .= $custom_data_wishlist_link;
                        endif;
                    if ($today>$event->start_date && $today<$event->end_date) {
                    $html .= '<span class="kf-live">'.__('Live','eventprime-event-calendar-management').'</span>';
                            } 
                            $html .= '<div class="kf-upcoming-event-post-date">
                            <div class="em_event_start difl em_wrap">
                                '.date_i18n(get_option('date_format').' '.get_option('time_format'), $event->start_date).'
                                <span> - </span>
                                '.date_i18n(get_option('date_format').' '.get_option('time_format'), $event->end_date).'
                            </div>
                        </div>';    
                    $html .='</div>';
                    $html .='<div class="kf-upcoming-event-booking em-col-5 em-col-pad20 difr">
                    <div class="em_header_button kf-button">';
                        if ($this->services['event']->is_bookable($event) && absint($event->custom_link_enabled) != 1): $current_ts = em_current_time_by_timezone();
                            if ($event->status=='expired'):
                            $html .='<div class="em_header_button em_not_bookable kf-tickets">'.em_global_settings_button_title('Bookings Expired').'</div>';
                            elseif ($current_ts>$event->last_booking_date):
                            $html .='<div class="em_header_button em_not_bookable kf-button">'.em_global_settings_button_title('Bookings Closed').'</div>';
                            elseif($current_ts<$event->start_booking_date): 
                                $html .='<div class="em_header_button em_not_bookable kf-button">'.em_global_settings_button_title('Bookings not started yet').'</div>';
                            else:
                                if(is_user_logged_in() || $showBookNowForGuestUsers):
                                    $html .='<form action="'.get_permalink($global_settings->booking_page).'" method="post" name="em_booking">
                                        <button class="em_header_button kf-button em_color" name="tickets" onclick="em_event_booking('.$event->id.')" class="em_header_button" id="em_booking">
                                            <i class="fa fa-ticket" aria-hidden="true"></i>
                                            '.em_global_settings_button_title('Book Now');
                                            if ($event->ticket_price > 0){
                                                $ticketPrice = $event->ticket_price;
                                                // check if show one time event fees at front enable
                                                if($event->show_fixed_event_price){
                                                    if($event->fixed_event_price > 0){
                                                        $ticketPrice = $event->fixed_event_price;
                                                    }
                                                }
                                                if ($ticketPrice > 0){
                                                    $html .= " - " . '<span class="em_event_price">' . em_price_with_position($ticketPrice, $currency_symbol) . '</span>';
                                                }
                                                $html .=  do_action('event_magic_single_event_ticket_price_after', $event, $ticketPrice);
                                            }
                                    $html .='</button>
                                        <input type="hidden" name="event_id" value="'.$event->id.'" />
                                        <input type="hidden" name="venue_id" value="'.$event->venue.'" />
                                    </form>';
                                    else:
                                        $html .='<a class="em_header_button kf-button em_color" target="_blank" href="'.add_query_arg('event_id',$event->id, get_permalink($global_settings->profile_page)).'">'.em_global_settings_button_title('Book Now').'</a>';
                                    endif;
                                endif;
                            elseif(absint($event->custom_link_enabled) != 1):
                            $html .='<div class="em_event_attr_box em_eventpage_register difl">
                                <div class="em_header_button em_not_bookable kf-button">
                                    '.em_global_settings_button_title('Bookings Closed').'
                                </div>
                            </div>';
                            endif; 
                $html .='</div>
                </div>
            </div>';       
            $i++;
        endforeach;
        wp_send_json_success($html);
    }


    /**
     * Advanced Event Type Section
     */

    public function load_types_card_data(){
        $paged = event_m_get_param( 'page' );
        $type_limit = event_m_get_param( 'show' );
        $featured = event_m_get_param( 'featured' );
        $em_search = event_m_get_param( 'em_search' );
        $em_search = ( $em_search != 'false' ) ? $em_search : '';
        $type_cols = absint( event_m_get_param( 'cols' ) );
        $type_cols = ( $type_cols == 0 || $type_cols > 12 ) ? 4 : $type_cols;
        $meta_query = array();
        if( $featured == 1 ){ 
            array_push( $meta_query, array(
                array(
                    'key'     => em_append_meta_key('is_featured'),
                    'value'   => 1
                )
            ));
        }
        $args = array(
            'orderby' => 'date',
            'number' => $type_limit,
            'offset' => (int) ( $paged - 1 ) * $type_limit,
             'paged' => $paged,
        'name__like' => $em_search,
        'meta_query' => $meta_query
        );

        $the_query = $this->services['event_type']->get_all_types_query( $args );
        $html = '';
        $types = is_object( $the_query ) ? $the_query->terms : '';
        $types_page_url = get_permalink( em_global_settings( "event_types" ) );
        $global_settings = $this->services['setting']->load_model_from_db();
        if( ! empty( $types ) ){
            foreach( $types as $type ){
                $html .= '<div class="ep-box-col-'.$type_cols.' ep-col-md-6">
                    <div class="ep-box-card-item">
                        <div class="ep-box-card-thumb">';
                            if ( ! empty( $type->image_id ) ){
                                $html .= '<a href="'.add_query_arg( "type", $type->id, $types_page_url ).'" class="ep-img-link"><img src="'.wp_get_attachment_image_src( $type->image_id, 'large' )[0].'" alt="'.__( 'Event Type Image', 'eventprime-event-calendar-management' ).'"></a>';
                            }else{
                                $html .= '<a href="'.add_query_arg( "type", $type->id, $types_page_url ).'" class="ep-img-link"><img src="'.esc_url( plugins_url( 'templates/images/dummy_image.png', __FILE__ ) ).'" alt="'.__( 'Dummy Image','eventprime-event-calendar-management' ).'"></a>'; 
                            }
                        $html .= '</div>
                        <div class="ep-box-card-content">
                            <div class="ep-box-title ep-box-card-title">
                                <a href="'.add_query_arg( "type", $type->id, $types_page_url ).'">'.$type->name.'</a> 
                            </div>';
                        $html .= '<div class="ep-box-card-role ep-event-type-age">
                            '.__( 'Age Group', 'eventprime-event-calendar-management' );
                                if ( $type->age_group !== 'custom_group' ) 
                                $html .= em_code_to_display_string( $type->age_group ); 
                                else 
                                $html .= __( $type->custom_group, 'eventprime-event-calendar-management' );
                        $html .= '</div>
                        </div>
                    </div>
                </div>';
            }
        }
        wp_send_json_success( $html );
    }

    public function load_types_box_data(){
        $paged = event_m_get_param( 'page' );
        $type_limit = event_m_get_param( 'show' );
        $featured = event_m_get_param( 'featured' );
        $em_search = event_m_get_param( 'em_search');
        $em_search = ( $em_search != 'false' ) ? $em_search : '';
        $type_cols = absint( event_m_get_param( 'cols' ) );
        $type_cols = ( $type_cols == 0 || $type_cols > 12 ) ? 4 : $type_cols;
        $meta_query = array();
        if( $featured == 1 ){ 
            array_push( $meta_query, array(
                array(
                    'key'     => em_append_meta_key('is_featured'),
                    'value'   => 1
                )
            ));
        }
        $args = array(
            'orderby' => 'date',
            'number' => $type_limit,
            'offset' => (int) ( $paged - 1 ) * $type_limit,
            'paged' => $paged,
            'name__like' => $em_search,
            'meta_query' => $meta_query
        );
        $the_query = $this->services['event_type']->get_all_types_query( $args );
        $html = '';
        $types = $the_query->terms;
        $types_page_url = get_permalink( em_global_settings( "event_types" ) );
        $global_settings = $this->services['setting']->load_model_from_db();
        if( ! empty( $types ) ){
            $b = event_m_get_param('bnum');
            $type_box_color = em_global_settings('type_box_color');
            foreach( $types as $type ){
                if( $b > 4 ) { $b = 1;}
                switch($b){
                    case 1 :
                        $bg_color = (!empty($type_box_color) && isset($type_box_color[0])) ? '#'.$type_box_color[0] : '#A6E7CF';
                        break;
                    case 2 :
                        $bg_color = (!empty($type_box_color) && isset($type_box_color[1])) ? '#'.$type_box_color[1] : '#DBEEC1';
                        break;
                    case 3 :
                        $bg_color = (!empty($type_box_color) && isset($type_box_color[2])) ? '#'.$type_box_color[2] : '#FFD3B6';
                        break;
                    case 4 :
                        $bg_color = (!empty($type_box_color) && isset($type_box_color[3])) ? '#'.$type_box_color[3] : '#FFA9A5';
                        break;
                    default:
                        $bg_color = '#A6E7CF';
                }
                $light_bg_color = ep_hex2rgba($bg_color, .5);
                $bg_color = ep_hex2rgba($bg_color, 1);

                $html .= '<div class="ep-box-col-'.$type_cols.' ep-box-column ep-box-px-0" data-id="'.$type->id.'" data-element_type="column">
                    <div class="ep-column-wrap ep-column-populated" style="background-image: linear-gradient(190deg,'.$bg_color.','.$light_bg_color.'); background-color: transparent;">
                        <div class="ep-box-widget-wrap">
                            <div class="ep-element ep-element-c95b9de ep-widget ep-widget-ep-pro-type"
                            data-id="'.$type->id.'" data-element_type="widget" data-widget_type="ep-pro-type.default">
                                <div class="ep-widget-container">
                                    <div class="ep-type-wrapper type-style1">
                                        <div class="ep-box-box-item">
                                            <div class="ep-box-box-thumb">';
                                                if ( ! empty( $type->image_id ) ){
                                                    $html .= '<a href="'.add_query_arg( "type", $type->id, $types_page_url ).'" class="img-fluid"><img src="'.wp_get_attachment_image_src( $type->image_id, 'large' )[0].'" alt="'.__( 'Event Type Image', 'eventprime-event-calendar-management' ).'"></a>';
                                                }else{ 
                                                    $html .= '<img src="'.esc_url( plugins_url('templates/images/dummy_image.png', __FILE__ ) ).'" class="img-fluid" alt="'.__('Dummy Image','eventprime-event-calendar-management').'">'; 
                                                }         
                                  $html .= '</div>';
                                  $html .= '<div class="ep-type-content">
                                                <div class="ep-box-title ep-box-box-title"><a href="'.add_query_arg( "type", $type->id, $types_page_url ).'">'.$type->name.'</a> </div>
                                                <div class="ep-box-card-role ep-type-role">'. __('Age Group ', 'eventprime-event-calendar-management');
                                                if ($type->age_group !== 'custom_group') 
                                                $html .=  em_code_to_display_string($type->age_group); 
                                                else 
                                                $html .=  __($type->custom_group, 'eventprime-event-calendar-management');
                                      $html .= '</div>
                                            </div>';
                                  
                                $html .= '</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>';
                $b++;
                }
        }
        wp_send_json_success( $html );
    }

    public function load_types_list_data(){
        $paged = event_m_get_param( 'page' );
        $type_limit = event_m_get_param( 'show' );
        $featured = event_m_get_param( 'featured' );
        $em_search = event_m_get_param( 'em_search' );
        $em_search = ( $em_search != 'false' ) ? $em_search : '';
        $meta_query = array();
        if( $featured == 1 ){ 
            array_push( $meta_query, array(
                array(
                    'key'     => em_append_meta_key('is_featured'),
                    'value'   => 1
                )
            ));
        }
        $args = array(
            'orderby' => 'date',
            'number' => $type_limit,
            'offset' => (int) ( $paged - 1 ) * $type_limit,
             'paged' => $paged,
        'name__like' => $em_search,
        'meta_query' => $meta_query
        );
        $the_query= $this->services['event_type']->get_all_types_query( $args );
        $html = '';
        $types = $the_query->terms;
        $types_page_url = get_permalink( em_global_settings( "event_types" ) );
        if( ! empty( $types ) ){
            foreach( $types as $type ){
          $html .= '<div class="ep-box-list-wrap">
                        <div class="ep-box-row">
                            <div class="ep-box-col-4 ep-list-box-table ep-box-profile-image">';
                            if ( ! empty( $type->image_id ) ){
                            $html .= '<a href="'.add_query_arg( "type", $type->id, $types_page_url ).'" ><img src="'.wp_get_attachment_image_src( $type->image_id, 'large' )[0].'" alt="'.__( 'Event Type Image', 'eventprime-event-calendar-management' ).'"></a>';
                            }else{
                            $html .= '<img src="'.esc_url( plugins_url( 'templates/images/dummy_image.png', __FILE__ ) ).'" alt="'.__( 'Dummy Image', 'eventprime-event-calendar-management' ).'">';
                            } 
              $html .= '</div>
                        <div class="ep-box-col-6 ep-list-box-table">
                            <div class="ep-box-list-items">
                                <div class="ep-box-title ep-box-list-title">
                                    <a class="ep-color-hover" data-type-id="'.$type->id.'" href="'.add_query_arg( "type", $type->id, $types_page_url ).'" target="_self" rel="noopener">
                                        '.$type->name.'
                                    </a>
                                </div>';
                            
                      $html .= '<div class="ep-box-card-role ep-type-age">'. __('Age Group ', 'eventprime-event-calendar-management');
                                    if ($type->age_group !== 'custom_group') 
                                    $html .=  em_code_to_display_string($type->age_group); 
                                    else 
                                    $html .=  __($type->custom_group, 'eventprime-event-calendar-management');
                      $html .= '</div>';
                            
                      $html .= '<div class="ep-event-description">
                                    <div class="ep-event-meta ep-color-before">
                                    </div>
                                    <div class="ep-view-details"><a class="ep-view-details-button" data-event-id="'.$type->id.'" href="'.add_query_arg( "type", $type->id, $types_page_url ).'">View Detail</a></div>
                                </div>
                            </div>
                        </div>
                    </div>';
            $html .= '<div class="ep-box-col-2 ep-list-box-table box-boder-l">
                        <ul class="ep-box-social-links">';
                       
            $html .= '</ul>
                    </div>
                </div>
            </div>';
            }
        }
        wp_send_json_success( array( 'html' => $html ) );
    }

    public function load_type_events_card_block(){
        $paged = event_m_get_param('page');
        $type_id = event_m_get_param('p_id');
        $single_type_event_limit = event_m_get_param("show");
        $event_cols = event_m_get_param("cols");
        $event_cols = ($event_cols == '' || $event_cols == 0 || $event_cols > 12) ? 4 : $event_cols;
        $args = array(
            'orderby' => em_append_meta_key('start_date'),
            'posts_per_page' => $single_type_event_limit,
            'offset'=> (int) ($paged-1) * $single_type_event_limit,
            'paged' => $paged,
        );
        $upcoming_events = $this->services['event']->upcoming_events_for_type( $type_id, $args );
        $posts = $upcoming_events->posts;
        $posts = apply_filters('ep_filter_front_events', $posts, $atts = array() );
        $posts = array_filter($posts, function($post){ return $post->post_status !== 'draft'; });
        $timestamp = time();
        $i = 0;
        $gs_service = EventM_Factory::get_service('EventM_Setting_Service');
        $global_settings = $gs_service->load_model_from_db();
        $showBookNowForGuestUsers = em_show_book_now_for_guest_users();
        $html = ''; $recurring = 1; $column_class = '';
        if(event_m_get_param('recurring')){
            $recurring = event_m_get_param('recurring');
        }
        if(empty($section_id)){ $ep_card_cls = 'ep-event-box-card'; } else{ $ep_card_cls = 'em_card_edt';}
        foreach ($posts as $post) :
            $event = $this->services['event']->load_model_from_db($post->ID);
            if(empty($recurring) && isset($event->parent) && !empty($event->parent)){
                continue;
            }
            $currency_symbol = em_currency_symbol();
            $booking_allowed = 1;
            if((isset($event->parent) && !empty($event->parent)) && (isset($event->enable_recurrence_automatic_booking) && !empty($event->enable_recurrence_automatic_booking))){
                // if event is recurring and parent has automatic booking enable than not allowed
                $booking_allowed = 0;
            }
            $event->url = em_get_single_event_page_url( $event, $global_settings );
            $emcardEpired ='';
            if (em_is_event_expired($event->id)) {
                $emcardEpired ='emcard-expired';
            }
            $emcardDisable = '';
            if((empty($event->enable_booking) && absint($event->custom_link_enabled) == 0)){
                $emcardDisable = 'em_event_disabled';
            }
            $html .= '<div class="'.$column_class.' ep-box-col-'.$event_cols.'">
                    <div class="'.$ep_card_cls.' '.$emcardEpired.''.$column_class.''.$emcardDisable.'" id="em-event-'.$event->id.'">';
                $html .= '<div class="em_event_cover dbfl">';
                    $thumbImage = esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png');
                    if (!empty($event->cover_image_id)): ?>
                        <?php 
                        $thumbImage = wp_get_attachment_image_src($event->cover_image_id, 'large')[0];
                        if(empty($thumbImage)){
                            $thumbImage = get_the_post_thumbnail($event->id,'large');
                            if(isset($event->parent) && !empty($event->parent) && empty($thumbImage)){
                                $thumbImage = get_the_post_thumbnail($event->parent,'large');
                            }
                        }
                        $html .='<a href="'.$event->url.'"><img src="'.$thumbImage.'"></a>';
                        else:
                        $html .='<a href="'.$event->url.'"><img src="'.esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png').'" class="em-no-image" ></a>';
                    endif;
                $html .='</div>';
                
                $html .='<div class="dbfl em-card-description">';
                    $html .='<div class="em_event_title"  title="'.$event->name.'">';
                        $html .='<a href="'.$event->url.'">'.$event->name.'</a>';
                        if(is_user_logged_in()):
                            ob_start();
                                do_action('event_magic_wishlist_link',$event);
                                $custom_data_wishlist_link = ob_get_contents();
                            ob_end_clean();
                            $html .= $custom_data_wishlist_link;
                        endif;
                    $html .='</div>';
                    $start_date = null; $end_date = null; $start_time = null; $end_time = null; $day = null;
                        if (em_compare_event_dates($event->id)){
                            $day = date_i18n(get_option('date_format'),$event->start_date);
                            $start_time = date_i18n(get_option('time_format'),$event->start_date);
                            $end_time = date_i18n(get_option('time_format'),$event->end_date);
                        }
                        else
                        {
                            $start_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->start_date);
                            $end_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->end_date);
                        }
                    if($event->all_day):
                        $html.='<div class="ep-card-event-date-wrap ep-box-row ep-box-center">'
                            .'<span class="ep-box-col-2"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg></span>'
                            . '<div class="ep-card-event-date ep-box-col-10 em_event_start difl em_wrap">'.date_i18n(get_option('date_format'),$event->start_date).'<span class="em-all-day"> - '.__('ALL DAY','eventprime-event-calendar-management').'</span>
                        </div></div>';
                    elseif(!empty($day)):
                        $html .= '<div class="em_event_start difl em_wrap">'.$day.'</div>';
                        $html .= '<div class="em_event_start difl em_wrap">'.$start_time.'  to  '.$end_time.'</div>';
                    else:
                        $html .= '<div class="em_event_start difl em_wrap">'.$start_date.' -    
                        </div>
                        <div class="em_event_start difl em_wrap">'.$end_date.' 
                        </div>';
                    endif;
                $html .='</div>';
                $html .='<div class="ep-single-box-footer dbfl">
                    <div class="em_event_price  difl">';
                        $ticket_price = $event->ticket_price;
                        if($event->show_fixed_event_price){
                            if($event->fixed_event_price > 0){
                                $ticket_price = $event->fixed_event_price;
                            }
                        }
                        if(!is_numeric($ticket_price)){
                            $html .= $ticket_price;
                        }
                        else{
                            $html .= !empty($ticket_price) ? em_price_with_position($ticket_price) : '';
                        }
                    $html .='</div>';
                    $html .=do_action('event_magic_card_view_after_price',$event);
                    $html .='<div class="ep-single-box-tickets-button difr">
                        <div class="em_event_attr_box em_eventpage_register difl">';
                            
                            if(absint($event->custom_link_enabled) == 1):
                                $html .='<div class="em_header_button em_event_custom_link kf-tickets">
                                    <a class="ep-event-custom-link" target="_blank" href="'.$event->url.'">';
                                            
                                        if(!empty($global_settings->hide_event_custom_link) && !is_user_logged_in()){
                                            $html .= em_global_settings_button_title('Login to View');
                                        }
                                        else{
                                            $html .= em_global_settings_button_title('Click for Details');
                                        }
                                    $html .='</a>';
                                $html ='</div>';
                            elseif($this->services['event']->is_bookable($event)): $current_ts = em_current_time_by_timezone();
                                if($event->status=='expired'):
                                        $html .= '<div class="em_header_button em_event_expired kf-tickets">'.em_global_settings_button_title('Bookings Expired').'</div>';
                                elseif($current_ts>$event->last_booking_date):
                                        $html .='<div class="em_header_button em_booking-closed kf-tickets">'.em_global_settings_button_title('Bookings Closed').'</div>';
                                elseif($current_ts < $event->start_booking_date): 
                                        $html .='<div class="em_header_button em_not_started kf-tickets">'.em_global_settings_button_title('Bookings not started yet').'</div>';
                                else: 
                                    if(!empty($booking_allowed)):
                                        if(is_user_logged_in() || $showBookNowForGuestUsers):
                                            $html .='<form action="'.get_permalink($global_settings->booking_page).'" method="post" name="em_booking">
                                                <a class="em_header_button em_event-booking kf-tickets" name="tickets" onclick="em_event_booking('.$event->id.')" id="em_booking">'.em_global_settings_button_title('Book Now').'</a>
                                                <input type="hidden" name="event_id" value="'.$event->id.'" />
                                                <input type="hidden" name="venue_id" value="'.$event->venue.'" />
                                            </form>';
                                        else: 
                                            $html .='<a class="em_header_button kf-tickets" target="_blank" href="'. add_query_arg('event_id',$event->id,get_permalink($global_settings->profile_page)).'">'.em_global_settings_button_title('Book Now').'</a>';
                                        endif;
                                    endif;
                                endif;
                            elseif($event->status == 'publish'):
                                if(isset($event->standing_capacity) && !empty($event->standing_capacity)):
                                    $html .='<div class="em_event_attr_box em_eventpage_register difl">
                                        <div class="em_header_button em_not_bookable kf-tickets">'.em_global_settings_button_title('All Seats Booked').'</div>
                                    </div>';
                                else:
                                    $html .='<div class="em_event_attr_box em_eventpage_register difl">
                                        <div class="em_header_button em_not_bookable kf-tickets">'. em_global_settings_button_title('Bookings Closed').'</div>
                                    </div>';
                                endif;
                            endif;
                        $html.='</div>';
                    $html.='</div>';
                $html.='</div>';
                $html.=do_action('event_magic_card_view_after_footer',$event);
            $html.='</div></div>';
        
            $i++;
        endforeach;  
        wp_send_json_success($html);
    }

    public function load_type_events_list_block(){
        $paged = event_m_get_param('page');
        $type_id = event_m_get_param('p_id');
        $single_type_event_limit = event_m_get_param("show");
        $args = array(
            'orderby' => em_append_meta_key('start_date'),
            'posts_per_page' => $single_type_event_limit,
            'offset'=> (int) ($paged-1) * $single_type_event_limit,
            'paged' => $paged,
        );
        $upcoming_events = $this->services['event']->upcoming_events_for_type( $type_id, $args );
        $html = $last_month_id = '';$recurring = 1;
        $showBookNowForGuestUsers = em_show_book_now_for_guest_users();
        if(event_m_get_param('recurring')){
            $recurring = event_m_get_param('recurring');
        }
        $posts= $upcoming_events->posts;
        $posts = array_filter($posts, function($post){ return $post->post_status !== 'draft'; });
        if(!empty($posts)){
            $last_month_id = event_m_get_param('last_month_id');
            $gs_service = EventM_Factory::get_service('EventM_Setting_Service');
            $global_settings = $gs_service->load_model_from_db();
            $currency_symbol = em_currency_symbol();
            foreach ($posts as $post){
                $event = $this->services['event']->load_model_from_db($post->ID);
                $month_id = date('Ym', $event->start_date);
                if(empty($last_month_id) || $last_month_id != $month_id){
                    $last_month_id = $month_id;
                    $html .= '<div class="ep-month-divider"><span class="ep-listed-event-month">'.date_i18n('F Y', $event->start_date).'<span class="ep-listed-event-month-tag"></span></span></div>';
                }
                $booking_allowed = 1;
                if((isset($event->parent) && !empty($event->parent)) && (isset($event->enable_recurrence_automatic_booking) && !empty($event->enable_recurrence_automatic_booking))){
                    // if event is recurring and parent has automatic booking enable than not allowed
                    $booking_allowed = 0;
                }
                $event_page = get_permalink(em_global_settings("events_page"));
                $event->url = em_get_single_event_page_url($event, $global_settings);
                $emexpired = '';
                if(em_is_event_expired($event->id)){
                    $emexpired = 'emlist-expired';
                }
                if(empty($event->enable_booking)){
                    $emexpired .= 'em_event_disabled';
                }
                $html .= '<div  id="em-event-'.$event->id.'" class="ep-event-article '.$emexpired.'">';
                    $html .= '<div class="ep-topsec">
                        <div class="em-col-3 difl ep-event-image-wrap ep-col-table-c">
                            <div class="em_event_cover_list dbfl">';
                                $thumbImage = esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png');
                                if (!empty($event->cover_image_id)):
                                    $thumbImage = wp_get_attachment_image_src($event->cover_image_id, 'large')[0];
                                    if(empty($thumbImage)){
                                        $thumbImage = get_the_post_thumbnail($event->id,'large');
                                        if(isset($event->parent) && !empty($event->parent) && empty($thumbImage)){
                                            $thumbImage = get_the_post_thumbnail($event->parent,'large');
                                        }
                                    }
                                    $html .= '<a href="'.$event->url.'">
                                        <img src="'.$thumbImage.'" alt="'.__('Event Cover Image', 'eventprime-event-calendar-management').'">
                                    </a>';
                                else:
                                    $html .= '<a href="'.$event->url.'"><img src="'.esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png').'" alt="'.__('Dummy Image','eventprime-event-calendar-management').'" class="em-no-image" ></a>';
                                endif;
                            $html .= '</div>
                        </div>';

                        $html .= '<div class="em-col-5 difl ep-col-table-c ep-event-content-wrap">
                            <div class="ep-event-content">';
                                $html .= '<h3 class="ep-event-title"><a class="ep-color-hover" data-event-id="'.$event->id.'" href="'.$event->url.'" target="_self">'.$event->name.'</a>
                                </h3>';
                                if(is_user_logged_in()):
                                    ob_start();
                                        do_action('event_magic_wishlist_link',$event);
                                        $wishlist = ob_get_contents();
                                    ob_end_clean();
                                    $html .= $wishlist;
                                endif;
                                if(!empty($event->description)) {
                                    $html .=  '<div class="ep-event-description">'.$event->description.'</div>';
                                }
                            $html .= '</div>';
                            $html .= do_action('event_magic_card_view_after_price', $event);
                        $html .= '</div>';

                        $html .='<div class="em-col-4 difl ep-col-table-c ep-event-meta-wrap">
                            <div class="ep-event-meta ep-color-before">'; 
                                $start_date = null; $end_date = null; $start_time = null; $end_time = null; $day = null;
                                if (em_compare_event_dates($event->id)){
                                    $day = date_i18n(get_option('date_format'),$event->start_date);
                                    $start_time = date_i18n(get_option('time_format'),$event->start_date);
                                    $end_time = date_i18n(get_option('time_format'),$event->end_date);
                                }
                                else{
                                    $start_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->start_date);
                                    $end_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->end_date);
                                }
                                if($event->all_day){
                                    $html .= '<div class="ep-list-event-date-row">
                                        <span class="material-icons em_color">date_range</span> 
                                        <div class="ep-list-event-date">'.
                                            date_i18n(get_option('date_format'),$event->start_date).'
                                            <span class="em-all-day"> - '. __('ALL DAY','eventprime-event-calendar-management') . '</span>
                                        </div>
                                    </div>';
                                } elseif(!empty($day)){
                                    $html .= '<div class="ep-list-event-date-row"><span class="material-icons em_color">date_range</span> <div class="ep-list-event-date">'.$day.' - '.$start_time.'  to  '.$end_time.'</div></div>';
                                }
                                else{
                                    $html .= '<div class="ep-list-event-date-row"><span class="material-icons em_color">date_range</span> <div class="ep-list-event-date">'.$start_date.' - '.$end_date.'</div></div>';
                                }
                                if(!empty($event->venue)){  
                                    $venue_service= EventM_Factory::get_service('EventM_Venue_Service');
                                    $venue= $venue_service->load_model_from_db($event->venue);
                                    if(!empty($venue->id) && !empty($venue->address)){
                                        $html .= '<div class="em-list-view-venue-details" title="'.$venue->address.'"><span class="ep-list-event-location"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zM7 9c0-2.76 2.24-5 5-5s5 2.24 5 5c0 2.88-2.88 7.19-5 9.88C9.92 16.21 7 11.85 7 9z"/><circle cx="12" cy="9" r="2.5"/></svg></span><address class="em-list-event-address"><span>'.$venue->address.'</span></address>
                                                </div>';
                                    }
                                }
                                if(!empty($event->enable_booking) && empty($event->hide_booking_status)):
                                    $sum = $this->services['event']->booked_seats($event->id);
                                    $capacity = em_event_seating_capcity($event->id);
                                    $html .= '<div class="ep-list-booking-status dbfl dbfl">
                                        <div class="kf-event-attr-value dbfl">';
                                            if ($capacity > 0):
                                                $html .= '<div class="dbfl">'.$sum .'/'. $capacity.'</div>';
                                                $width = ($sum / $capacity) * 100;
                                                $html .= '<div class="dbfl"><div id="progressbar" class="em_progressbar dbfl"><div style="width:'.$width .'%'.'" class="em_progressbar_fill em_bg" ></div></div></div>';
                                            else:
                                                if($sum > 0){
                                                    $html .= '<div class="dbfl">' . $sum . ' '.__('Attending','eventprime-event-calendar-management').'</div>';
                                                }
                                            endif;
                                        $html .= '</div>
                                    </div>';
                                endif;

                                $custom_data_before_footer = '';
                                ob_start();
                                    do_action('event_magic_popup_custom_data_before_footer',$event);
                                    $custom_data_before_footer = ob_get_contents();
                                ob_end_clean();
                                $html .= $custom_data_before_footer;

                                $html .= ' <div class="ep-list-view-footer">
                                    <div class="em_event_price difl">';
                                        $ticket_price = $event->ticket_price;
                                        // check if show one time event fees at front enable
                                        if($event->show_fixed_event_price){
                                            if($event->fixed_event_price > 0){
                                                $ticket_price = $event->fixed_event_price;
                                            }
                                        }
                                        $html .= !empty($ticket_price) ? $currency_symbol.$ticket_price : '';
                                    $html .= '</div>';
                                    $html .= do_action('event_magic_card_view_after_price',$event);
                                    $html .= '<div class="kf-tickets-button difr">
                                        <div class="em_event_attr_box em_eventpage_register difl">';
                                            if(absint($event->custom_link_enabled) == 1):
                                                $html .= '<div class="em_header_button em_event_custom_link kf-tickets">
                                                    <a class="em_header_button kf-tickets" target="_blank" href="'.$event->url.'">';
                                                        if(!empty(em_global_settings('hide_event_custom_link')) && !is_user_logged_in()){
                                                            $html .= em_global_settings_button_title('Login to View');
                                                        }
                                                        else{
                                                            $html .= em_global_settings_button_title('Click for Details');
                                                        }
                                                    $html .= '</a>
                                                </div>';
                                            elseif($this->services['event']->is_bookable($event)): 
                                                $current_ts = em_current_time_by_timezone();
                                                if($event->status=='expired'):
                                                    $html .= '<div class="em_header_button em_event_expired kf-tickets">'.
                                                    em_global_settings_button_title('Bookings Expired').'</div>';
                                                elseif($current_ts>$event->last_booking_date):
                                                    $html .= '<div class="em_header_button em_booking-closed kf-tickets">'.
                                                    em_global_settings_button_title('Bookings Closed').'</div>';
                                                elseif($current_ts<$event->start_booking_date):
                                                    $html .= '<div class="em_header_button em_not_started kf-tickets">'.
                                                    em_global_settings_button_title('Bookings not started yet').'</div>';
                                                else:
                                                    if(!empty($booking_allowed)):
                                                        if(is_user_logged_in() || $showBookNowForGuestUsers):
                                                            $html .= '<form action="'.get_permalink($global_settings->booking_page).'" method="post" name="em_booking">';
                                                                $html .= '<button class="em_header_button em_event-booking kf-tickets em_color" name="tickets" onclick="em_event_booking('.$event->id.')" id="em_booking">'.
                                                                em_global_settings_button_title('Book Now').'</button>';
                                                                $html .= '<input type="hidden" name="event_id" value="'.$event->id.'" />';
                                                                $html .= '<input type="hidden" name="venue_id" value="'.$event->venue.'" />';
                                                            $html .= '</form>';
                                                        else:
                                                            $html .= '<a class="em_header_button em_event-booking kf-tickets em_color" target="_blank" href="'.add_query_arg('event_id',$event->id,get_permalink($global_settings->profile_page)).'">'.
                                                            em_global_settings_button_title('Book Now').'</a>';
                                                        endif;
                                                    endif;
                                                endif;
                                            elseif($event->status == 'publish'):
                                                if(isset($event->standing_capacity) && !empty($event->standing_capacity)):
                                                    $html .= '<div class="em_event_attr_box em_eventpage_register difl"><div class="em_header_button em_not_bookable kf-tickets">'.em_global_settings_button_title('All Seats Booked').'</div>
                                                    </div>';
                                                else:
                                                    $html .= '<div class="em_event_attr_box em_eventpage_register difl">
                                                        <div class="em_header_button em_not_bookable kf-tickets">'.em_global_settings_button_title('Bookings Closed').'</div>
                                                    </div>';
                                                endif;
                                            endif;
                                        $html .= '</div>';
                                    $html .= '</div>';
                                $html .= '</div>';
                                $html .= do_action('event_magic_card_view_after_footer',$event);
                            $html .= '</div>';
                        $html .= '</div>';
                    $html .= '</div>';
                $html .= '</div>';
            }
        }
        wp_send_json_success(array('html' => $html, 'last_month_id' => $last_month_id));
    }

    public function load_type_events_mini_list_block(){
        $paged = event_m_get_param('page');
        $type_id = event_m_get_param('p_id');
        $single_type_event_limit = event_m_get_param("show");
        $args = array(
            'orderby' => em_append_meta_key('start_date'),
            'posts_per_page' => $single_type_event_limit,
            'offset'=> (int) ($paged-1) * $single_type_event_limit,
            'paged' => $paged,
        );
        $upcoming_events = $this->services['event']->upcoming_events_for_type( $type_id, $args );
        $posts = $upcoming_events->posts;
        $posts = apply_filters('ep_filter_front_events', $posts, $atts = array() );
        $posts = array_filter($posts, function($post){ return $post->post_status !== 'draft'; });
        $timestamp = time();
        $i = 0;
        $gs_service = EventM_Factory::get_service('EventM_Setting_Service');
        $global_settings = $gs_service->load_model_from_db();
        $showBookNowForGuestUsers = em_show_book_now_for_guest_users();
        $html = '';$recurring = 1; $column_class = '';
        if(event_m_get_param('recurring')){
            $recurring = event_m_get_param('recurring');
        }
        $section_id = '';
        if(empty($section_id)){ $ep_card_cls = 'ep-event-box-card'; } else{ $ep_card_cls = 'em_card_edt';}
        $today = em_current_time_by_timezone();
        foreach ($posts as $post) :
            $event = $this->services['event']->load_model_from_db($post->ID);
            if(empty($recurring) && isset($event->parent) && !empty($event->parent)){
                continue;
            }
            $currency_symbol = em_currency_symbol();
            $booking_allowed = 1;
            if((isset($event->parent) && !empty($event->parent)) && (isset($event->enable_recurrence_automatic_booking) && !empty($event->enable_recurrence_automatic_booking))){
                // if event is recurring and parent has automatic booking enable than not allowed
                $booking_allowed = 0;
            }
           
            $event->url = em_get_single_event_page_url( $event, $global_settings );
            $emcardEpired ='';
            if (em_is_event_expired($event->id)) {
                $emcardEpired ='emcard-expired';
            }
            $emcardDisable = '';
            if((empty($event->enable_booking) && absint($event->custom_link_enabled) == 0)){
                $emcardDisable = 'em_event_disabled';
            }
            $html .= '<div class="kf-upcoming-event-row em_block dbfl '.$emcardEpired.' '.$emcardDisable.'">
                <div class="kf-upcoming-event-thumb em-col-2 difl">';
                $html .='<a href="'.$event->url.'">';
                $thumbImage = esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png');
                if (!empty($event->cover_image_id)): ?>
                    <?php 
                    $thumbImage = wp_get_attachment_image_src($event->cover_image_id, 'large')[0];
                    if(empty($thumbImage)){
                        $thumbImage = get_the_post_thumbnail($event->id,'large');
                        if(isset($event->parent) && !empty($event->parent) && empty($thumbImage)){
                            $thumbImage = get_the_post_thumbnail($event->parent,'large');
                        }
                    }
                    $html .= '<img src="'.$thumbImage.'" alt="'.__('Event Cover Image', 'eventprime-event-calendar-management').'">';
                    else:
                    $html .= '<img src="'.esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png').'" alt="'.__('Dummy Image','eventprime-event-calendar-management').'" class="em-no-image" >';
                    endif;
                $html .= '</a>
                    </div>';
                
                $html .='<div class="kf-upcoming-event-title em-col-5 em-col-pad20 difl">';
                        $html .='<a href="'.$event->url.'">'.$event->name.'</a>';
                        if(is_user_logged_in()):
                            ob_start();
                                do_action('event_magic_wishlist_link',$event);
                                $custom_data_wishlist_link = ob_get_contents();
                            ob_end_clean();
                            $html .= $custom_data_wishlist_link;
                        endif;
                    if ($today>$event->start_date && $today<$event->end_date) {
                    $html .= '<span class="kf-live">'.__('Live','eventprime-event-calendar-management').'</span>';
                            } 
                            $html .= '<div class="kf-upcoming-event-post-date">
                            <div class="em_event_start difl em_wrap">
                                '.date_i18n(get_option('date_format').' '.get_option('time_format'), $event->start_date).'
                                <span> - </span>
                                '.date_i18n(get_option('date_format').' '.get_option('time_format'), $event->end_date).'
                            </div>
                        </div>';    
                    $html .='</div>';
                    $html .='<div class="kf-upcoming-event-booking em-col-5 em-col-pad20 difr">
                    <div class="em_header_button kf-button">';
                        if ($this->services['event']->is_bookable($event) && absint($event->custom_link_enabled) != 1): $current_ts = em_current_time_by_timezone();
                            if ($event->status=='expired'):
                            $html .='<div class="em_header_button em_not_bookable kf-tickets">'.em_global_settings_button_title('Bookings Expired').'</div>';
                            elseif ($current_ts>$event->last_booking_date):
                            $html .='<div class="em_header_button em_not_bookable kf-button">'.em_global_settings_button_title('Bookings Closed').'</div>';
                            elseif($current_ts<$event->start_booking_date): 
                                $html .='<div class="em_header_button em_not_bookable kf-button">'.em_global_settings_button_title('Bookings not started yet').'</div>';
                            else:
                                if(is_user_logged_in() || $showBookNowForGuestUsers):
                                    $html .='<form action="'.get_permalink($global_settings->booking_page).'" method="post" name="em_booking">
                                        <button class="em_header_button kf-button em_color" name="tickets" onclick="em_event_booking('.$event->id.')" class="em_header_button" id="em_booking">
                                            <i class="fa fa-ticket" aria-hidden="true"></i>
                                            '.em_global_settings_button_title('Book Now');
                                            if ($event->ticket_price > 0){
                                                $ticketPrice = $event->ticket_price;
                                                // check if show one time event fees at front enable
                                                if($event->show_fixed_event_price){
                                                    if($event->fixed_event_price > 0){
                                                        $ticketPrice = $event->fixed_event_price;
                                                    }
                                                }
                                                if ($ticketPrice > 0){
                                                    $html .= " - " . '<span class="em_event_price">' . em_price_with_position($ticketPrice, $currency_symbol) . '</span>';
                                                }
                                                $html .=  do_action('event_magic_single_event_ticket_price_after', $event, $ticketPrice);
                                            }
                                    $html .='</button>
                                        <input type="hidden" name="event_id" value="'.$event->id.'" />
                                        <input type="hidden" name="venue_id" value="'.$event->venue.'" />
                                    </form>';
                                    else:
                                        $html .='<a class="em_header_button kf-button em_color" target="_blank" href="'.add_query_arg('event_id',$event->id, get_permalink($global_settings->profile_page)).'">'.em_global_settings_button_title('Book Now').'</a>';
                                    endif;
                                endif;
                            elseif(absint($event->custom_link_enabled) != 1):
                            $html .='<div class="em_event_attr_box em_eventpage_register difl">
                                <div class="em_header_button em_not_bookable kf-button">
                                    '.em_global_settings_button_title('Bookings Closed').'
                                </div>
                            </div>';
                            endif; 
                $html .='</div>
                </div>
            </div>';       
            $i++;
        endforeach;
        wp_send_json_success($html);
    }

    /** Advanced Organizers **/

    public function load_organizers_card_data(){
        $paged = event_m_get_param( 'page' );
        $organizer_limit = event_m_get_param( 'show' );
        $featured = event_m_get_param( 'featured' );
        $em_search = event_m_get_param( 'em_search' );
        $em_search = ( $em_search != 'false' ) ? $em_search : '';
        $organizer_cols = absint( event_m_get_param( 'cols' ) );
        $organizer_cols = ( $organizer_cols == 0 || $organizer_cols > 12 ) ? 4 : $organizer_cols;
        $meta_query = [];
        if( $featured == 1 ){ 
            array_push( $meta_query, array(
                array(
                    'key'     => em_append_meta_key('is_featured'),
                    'value'   => 1
                )
            ));
        }
        $args = array(
            'orderby' => 'date',
            'number' => $organizer_limit,
            'offset' => (int) ( $paged - 1 ) * $organizer_limit,
            'paged' => $paged,
            'name__like' => $em_search,
            'meta_query' => $meta_query
        );

        $the_query = $this->services['event_organizer']->get_all_organizers_query( $args );
        $html = '';
        $organizers = is_object( $the_query ) ? $the_query->terms : '';
        $organizers_page_url = get_permalink( em_global_settings( "event_organizers" ) );
        $global_settings = $this->services['setting']->load_model_from_db();
        if( ! empty( $organizers ) ){
            foreach($organizers as $organizer){
                $html .= '<div class="ep-box-col-'.$organizer_cols.' ep-col-md-6">
                        <div class="ep-box-card-item">
                            <div class="ep-box-card-thumb" >';
                            if (!empty( $organizer->image_id)){
                                $html .= '<a href="'.add_query_arg( "organizer", $organizer->id, $organizers_page_url ).'" class="ep-img-link"><img src="'.wp_get_attachment_image_src( $organizer->image_id, 'large' )[0].'" alt="'.__( 'Event Organizer Image', 'eventprime-event-calendar-management' ).'"></a>';
                            }else{
                                $html .= '<a href="'.add_query_arg( "organizer", $organizer->id, $organizers_page_url ).'" class="ep-img-link"><img src="'.esc_url( plugins_url('templates/images/dummy-organizer.png', __FILE__ ) ).'" alt="'.__( 'Dummy Image','eventprime-event-calendar-management' ).'"></a>'; 
                            }
                            if (!empty( $organizer->social_links )){    
                            $html .= '<div class="ep-box-card-social ep-organizers-social">';
                                if( isset( $organizer->social_links->facebook ) )
                                    $html .= '<a href="'.$organizer->social_links->facebook.'" target="_blank" title="Facebook"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M279.14 288l14.22-92.66h-88.91v-60.13c0-25.35 12.42-50.06 52.24-50.06h40.42V6.26S260.43 0 225.36 0c-73.22 0-121.08 44.38-121.08 124.72v70.62H22.89V288h81.39v224h100.17V288z"/></svg></a>';
                                if( isset( $organizer->social_links->instagram ) )
                                    $html .= '<a href="'.$organizer->social_links->instagram.'" target="_blank" title="Instagram"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"/></svg></a>';
                                if( isset( $organizer->social_links->linkedin) )
                                    $html .= '<a href="'.$organizer->social_links->linkedin.'" target="_blank" title="Linkedin"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M100.28 448H7.4V148.9h92.88zM53.79 108.1C24.09 108.1 0 83.5 0 53.8a53.79 53.79 0 0 1 107.58 0c0 29.7-24.1 54.3-53.79 54.3zM447.9 448h-92.68V302.4c0-34.7-.7-79.2-48.29-79.2-48.29 0-55.69 37.7-55.69 76.7V448h-92.78V148.9h89.08v40.8h1.3c12.4-23.5 42.69-48.3 87.88-48.3 94 0 111.28 61.9 111.28 142.3V448z"/></svg></a>';
                                if( isset( $organizer->social_links->twitter ) )
                                    $html .= '<a href="'.$organizer->social_links->twitter.'" target="_blank" title="Twitter"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M459.37 151.716c.325 4.548.325 9.097.325 13.645 0 138.72-105.583 298.558-298.558 298.558-59.452 0-114.68-17.219-161.137-47.106 8.447.974 16.568 1.299 25.34 1.299 49.055 0 94.213-16.568 130.274-44.832-46.132-.975-84.792-31.188-98.112-72.772 6.498.974 12.995 1.624 19.818 1.624 9.421 0 18.843-1.3 27.614-3.573-48.081-9.747-84.143-51.98-84.143-102.985v-1.299c13.969 7.797 30.214 12.67 47.431 13.319-28.264-18.843-46.781-51.005-46.781-87.391 0-19.492 5.197-37.36 14.294-52.954 51.655 63.675 129.3 105.258 216.365 109.807-1.624-7.797-2.599-15.918-2.599-24.04 0-57.828 46.782-104.934 104.934-104.934 30.213 0 57.502 12.67 76.67 33.137 23.715-4.548 46.456-13.32 66.599-25.34-7.798 24.366-24.366 44.833-46.132 57.827 21.117-2.273 41.584-8.122 60.426-16.243-14.292 20.791-32.161 39.308-52.628 54.253z"/></svg></a>';
                            $html .= '</div>';
                            }
              $html .= '</div>
                        <div class="ep-box-card-content">
                            <div class="ep-box-title ep-box-card-title"><a
                            href="'.add_query_arg( "organizer", $organizer->id, $organizers_page_url ).'">
                            '.$organizer->name.'</a> </div>';

              $html .= '</div>
                    </div>
                </div>';
            }
        }
        wp_send_json_success( $html );
    }

    public function load_organizers_box_data(){
        $paged = event_m_get_param( 'page' );
        $organizer_limit = event_m_get_param( 'show' );
        $featured = event_m_get_param( 'featured' );
        $em_search = event_m_get_param( 'em_search');
        $em_search = ( $em_search != 'false' ) ? $em_search : '';
        $organizer_cols = absint( event_m_get_param( 'cols' ) );
        $organizer_cols = ( $organizer_cols == 0 || $organizer_cols > 12 ) ? 4 : $organizer_cols;
        $meta_query = [];
        if( $featured == 1 ){ 
            array_push( $meta_query, array(
                array(
                    'key'     => em_append_meta_key('is_featured'),
                    'value'   => 1
                )
            ));
        }
        $args = array(
            'orderby' => 'date',
            'number' => $organizer_limit,
            'offset' => (int) ( $paged - 1 ) * $organizer_limit,
            'paged' => $paged,
            'name__like' => $em_search,
            'meta_query' => $meta_query
        );
        $the_query = $this->services['event_organizer']->get_all_organizers_query( $args );
        $html = '';
        $organizers = $the_query->terms;
        $organizers_page_url = get_permalink( em_global_settings( "event_organizers" ) );
        $global_settings = $this->services['setting']->load_model_from_db();
        if( ! empty( $organizers ) ){
            $b = event_m_get_param('bnum');
            $organizer_box_color = em_global_settings('organizer_box_color');
            foreach( $organizers as $organizer ){
                if( $b > 4 ) { $b = 1;}
                switch($b){
                    case 1 :
                        $bg_color = (!empty($organizer_box_color) && isset($organizer_box_color[0])) ? '#'.$organizer_box_color[0] : '#A6E7CF';
                        break;
                    case 2 :
                        $bg_color = (!empty($organizer_box_color) && isset($organizer_box_color[1])) ? '#'.$organizer_box_color[1] : '#DBEEC1';
                        break;
                    case 3 :
                        $bg_color = (!empty($organizer_box_color) && isset($organizer_box_color[2])) ? '#'.$organizer_box_color[2] : '#FFD3B6';
                        break;
                    case 4 :
                        $bg_color = (!empty($organizer_box_color) && isset($organizer_box_color[3])) ? '#'.$organizer_box_color[3] : '#FFA9A5';
                        break;
                    default:
                        $bg_color = '#A6E7CF';
                }
                $light_bg_color = ep_hex2rgba($bg_color, .5);
                $bg_color = ep_hex2rgba($bg_color, 1);

                $html .= '<div class="ep-box-col-'.$organizer_cols.' ep-box-column ep-box-px-0" data-id="'.$organizer->id.'" data-element_type="column">
                    <div class="ep-column-wrap ep-column-populated" style="background-image: linear-gradient(190deg,'.$bg_color.','.$light_bg_color.'); background-color: transparent;">
                        <div class="ep-box-widget-wrap">
                            <div class="ep-element ep-element-c95b9de ep-widget ep-widget-ep-pro-organizer"
                                data-id="'.$organizer->id.'" data-element_type="widget" data-widget_type="ep-pro-organizer.default">
                                <div class="ep-widget-container">
                                    <div class="ep-organizer-wrapper organizer-style1">
                                        <div class="ep-box-box-item">
                                            <div class="ep-box-box-thumb">';
                                                if ( ! empty( $organizer->image_id ) ){
                                                    $html .= '<a href="'.add_query_arg( "organizer", $organizer->id, $organizers_page_url ).'" class="img-fluid"><img src="'.wp_get_attachment_image_src( $organizer->image_id, 'large' )[0].'" alt="'.__( 'Event Organizer Image', 'eventprime-event-calendar-management' ).'"></a>';
                                                }else{ 
                                                    $html .= '<img src="'.esc_url( plugins_url('templates/images/dummy-organizer.png', __FILE__ ) ).'" class="img-fluid" alt="'.__('Dummy Image','eventprime-event-calendar-management').'">'; 
                                                }         
                                            $html .= '</div>
                                            <div class="ep-organizer-content">
                                                <div class="ep-box-title ep-box-box-title"><a href="'.add_query_arg( "organizer", $organizer->id, $organizers_page_url ).'">'.$organizer->name.'</a> </div>';
                                              
                                                if ( ! empty( $organizer->social_links ) ){    
                                                $html .= '<div class="ep-organizers-social">';
                                                    if( isset( $organizer->social_links->facebook ) )
                                                        $html .= '<a href="'.$organizer->social_links->facebook.'" target="_blank" title="Facebook"> <i class="fab fa-facebook-f"></i></a>';
                                                    if( isset( $organizer->social_links->instagram ) )
                                                        $html .= '<a href="'.$organizer->social_links->instagram.'" target="_blank" title="Instagram"><i class="fab fa-instagram"></i></a>';
                                                    if( isset( $organizer->social_links->linkedin ) )
                                                        $html .= '<a href="'.$organizer->social_links->linkedin.'" target="_blank" title="Linkedin"><i class="fab fa-linkedin"></i></a>';
                                                    if( isset( $organizer->social_links->twitter ) )
                                                        $html .= '<a href="'.$organizer->social_links->twitter.'" target="_blank" title="Twitter"><i class="fab fa-twitter"></i></a>';
                                                $html .= '</div>';
                                                }
                                            $html .= '</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>';
                $b++;
            }
        }
        wp_send_json_success( $html );
    }

    public function load_organizers_list_data(){
        $paged = absint(event_m_get_param( 'page' ));
        $organizer_limit = absint(event_m_get_param( 'show' ));
        $featured = absint(event_m_get_param( 'featured' ));
        $em_search = absint(event_m_get_param( 'em_search' ));
        $em_search = ( $em_search != 'false' ) ? $em_search : '';
        $meta_query = [];
        if( $featured == 1 ){ 
            array_push( $meta_query, array(
                array(
                    'key'     => em_append_meta_key('is_featured'),
                    'value'   => 1
                )
            ));
        }
        $args = array(
            'orderby' => 'date',
            'number' => $organizer_limit,
            'offset' => (int) ( $paged - 1 ) * $organizer_limit,
            'paged' => $paged,
            'name__like' => $em_search,
            'meta_query' => $meta_query
        );
        $the_query= $this->services['event_organizer']->get_all_organizers_query( $args );
        $html = '';
        $organizers = $the_query->terms;
        $organizers_page_url = get_permalink( em_global_settings( "event_organizers" ) );
        if( ! empty( $organizers ) ){
            foreach( $organizers as $organizer ){
        $html .= '<div class="ep-box-list-wrap">
                    <div class="ep-box-row">
                         <div class="ep-box-col-4 ep-list-box-table ep-box-profile-image">';
                            if ( ! empty( $organizer->image_id ) ){
                            $html .= '<a href="'.add_query_arg( "organizer", $organizer->id, $organizers_page_url ).'" ><img src="'.wp_get_attachment_image_src( $organizer->image_id, 'large' )[0].'" alt="'.__( 'Event Organizer Image', 'eventprime-event-calendar-management' ).'"></a>';
                            }else{
                            $html .= '<img src="'.esc_url( plugins_url( 'templates/images/dummy-organizer.png', __FILE__ ) ).'" alt="'.__( 'Dummy Image', 'eventprime-event-calendar-management' ).'">';
                            } 
            $html .= '</div>
                    <div class="ep-box-col-6 ep-list-box-table">
                        <div class="ep-box-list-items">
                            <div class="ep-box-title ep-box-list-title">
                                <a class="ep-color-hover" data-performer-id="'.$organizer->id.'" href="'.add_query_arg( "organizer", $organizer->id, $organizers_page_url ).'" target="_self" rel="noopener">
                                '.$organizer->name.'
                                </a>
                            </div>';
                  $html .= '<div class="ep-event-description">
                                <div class="ep-event-meta ep-color-before">
                                    <div class="ep-time-details">
                                        <span class="ep-box-phone"><svg xmlns="http://www.w3.org/2000/svg" height="18px" viewBox="0 0 24 24" width="18px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M6.54 5c.06.89.21 1.76.45 2.59l-1.2 1.2c-.41-1.2-.67-2.47-.76-3.79h1.51m9.86 12.02c.85.24 1.72.39 2.6.45v1.49c-1.32-.09-2.59-.35-3.8-.75l1.2-1.19M7.5 3H4c-.55 0-1 .45-1 1 0 9.39 7.61 17 17 17 .55 0 1-.45 1-1v-3.49c0-.55-.45-1-1-1-1.24 0-2.45-.2-3.57-.57-.1-.04-.21-.05-.31-.05-.26 0-.51.1-.71.29l-2.2 2.2c-2.83-1.45-5.15-3.76-6.59-6.59l2.2-2.2c.28-.28.36-.67.25-1.02C8.7 6.45 8.5 5.25 8.5 4c0-.55-.45-1-1-1z"/></svg></span>
                                        <span class="ep-start-time">';
                                            if ( ! empty( $organizer->organizer_phones ) ) 
                                                $html .= implode( ', ', (array) $organizer->organizer_phones ); 
                                            else 
                                                $html .= '--';
                             $html .=  '</span>
                                    </div>
                                    <div class="ep-time-details">
                                        <span class="ep-box-email"><svg xmlns="http://www.w3.org/2000/svg" height="18px" viewBox="0 0 24 24" width="18px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M22 6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6zm-2 0l-8 5-8-5h16zm0 12H4V8l8 5 8-5v10z"/></svg></span>
                                        <span class="ep-start-time">'; 
                                            if ( ! empty( $organizer->organizer_emails ) ) 
                                                {foreach( $organizer->organizer_emails as $key => $val ) {
                                                $organizer->organizer_emails[ $key ] = '<a href="mailto:'.$val.'">'.htmlentities( $val ).'</a>';
                                                }
                                                $html .= implode( ', ', (array) $organizer->organizer_emails );
                                            } 
                                            else 
                                                $html .= '--';
                            $html .= '</span>
                                    </div>
                                    <div class="ep-time-details">
                                        <span class="ep-box-website"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0z" fill="none"/><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zm6.93 6h-2.95c-.32-1.25-.78-2.45-1.38-3.56 1.84.63 3.37 1.91 4.33 3.56zM12 4.04c.83 1.2 1.48 2.53 1.91 3.96h-3.82c.43-1.43 1.08-2.76 1.91-3.96zM4.26 14C4.1 13.36 4 12.69 4 12s.1-1.36.26-2h3.38c-.08.66-.14 1.32-.14 2 0 .68.06 1.34.14 2H4.26zm.82 2h2.95c.32 1.25.78 2.45 1.38 3.56-1.84-.63-3.37-1.9-4.33-3.56zm2.95-8H5.08c.96-1.66 2.49-2.93 4.33-3.56C8.81 5.55 8.35 6.75 8.03 8zM12 19.96c-.83-1.2-1.48-2.53-1.91-3.96h3.82c-.43 1.43-1.08 2.76-1.91 3.96zM14.34 14H9.66c-.09-.66-.16-1.32-.16-2 0-.68.07-1.35.16-2h4.68c.09.65.16 1.32.16 2 0 .68-.07 1.34-.16 2zm.25 5.56c.6-1.11 1.06-2.31 1.38-3.56h2.95c-.96 1.65-2.49 2.93-4.33 3.56zM16.36 14c.08-.66.14-1.32.14-2 0-.68-.06-1.34-.14-2h3.38c.16.64.26 1.31.26 2s-.1 1.36-.26 2h-3.38z"/></svg></span>
                                        <span class="ep-start-time">';
                                            if ( ! empty( $organizer->organizer_websites ) ) { 
                                                foreach( $organizer->organizer_websites as $key => $val ) {
                                                    if( ! empty( $val ) ){
                                                        $organizer->organizer_websites[ $key ] = '<a href="'.$val.'" target="_blank">'.htmlentities( $val ).'</a>';
                                                    }
                                                }
                                                $html .=  implode(', ', (array) $organizer->organizer_websites );
                                            } 
                                            else 
                                                $html .= '--';
                            $html .=  '</span>
                                    </div>
                                    <div class="ep-view-details"><a class="ep-view-details-button" data-event-id="'.$organizer->id.'" href="'.add_query_arg( "organizer", $organizer->id, $organizers_page_url ).'">'.esc_html__('View Detail', 'eventprime-event-calendar-management').'</a></div>
                                </div>
                            </div>
                        </div>
                    </div>';
            $html .= '<div class="ep-box-col-2 ep-list-box-table box-boder-l">
                        <ul class="ep-box-social-links">';
                        if ( ! empty( $organizer->social_links ) ){
                            if( isset( $organizer->social_links->facebook ) )
                                $html .= '<li class="ep-event-social-icon"><a class="facebook" href="'.$organizer->social_links->facebook.'" target="_blank" title="Facebook"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"> <path d="M504 256C504 119 393 8 256 8S8 119 8 256c0 123.78 90.69 226.38 209.25 245V327.69h-63V256h63v-54.64c0-62.15 37-96.48 93.67-96.48 27.14 0 55.52 4.84 55.52 4.84v61h-31.28c-30.8 0-40.41 19.12-40.41 38.73V256h68.78l-11 71.69h-57.78V501C413.31 482.38 504 379.78 504 256z"/></svg></a></li>';
                            if( isset( $organizer->social_links->instagram ) )
                                $html .= '<li class="ep-event-social-icon"><a class="whatsapp" href="'.$organizer->social_links->instagram.'" target="_blank" title="Instagram"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"/></svg></a></li>';
                            if( isset( $organizer->social_links->linkedin ) )
                                $html .= '<li class="ep-event-social-icon"><a class="linkedin" href="'.$organizer->social_links->linkedin.'" target="_blank" title="Linkedin"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M100.28 448H7.4V148.9h92.88zM53.79 108.1C24.09 108.1 0 83.5 0 53.8a53.79 53.79 0 0 1 107.58 0c0 29.7-24.1 54.3-53.79 54.3zM447.9 448h-92.68V302.4c0-34.7-.7-79.2-48.29-79.2-48.29 0-55.69 37.7-55.69 76.7V448h-92.78V148.9h89.08v40.8h1.3c12.4-23.5 42.69-48.3 87.88-48.3 94 0 111.28 61.9 111.28 142.3V448z"/></svg></a></li>';
                            if( isset( $organizer->social_links->twitter ) )
                                $html .= '<li class="ep-event-social-icon"><a class="twitter" href="'.$organizer->social_links->twitter.'" target="_blank" title="Twitter"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M459.37 151.716c.325 4.548.325 9.097.325 13.645 0 138.72-105.583 298.558-298.558 298.558-59.452 0-114.68-17.219-161.137-47.106 8.447.974 16.568 1.299 25.34 1.299 49.055 0 94.213-16.568 130.274-44.832-46.132-.975-84.792-31.188-98.112-72.772 6.498.974 12.995 1.624 19.818 1.624 9.421 0 18.843-1.3 27.614-3.573-48.081-9.747-84.143-51.98-84.143-102.985v-1.299c13.969 7.797 30.214 12.67 47.431 13.319-28.264-18.843-46.781-51.005-46.781-87.391 0-19.492 5.197-37.36 14.294-52.954 51.655 63.675 129.3 105.258 216.365 109.807-1.624-7.797-2.599-15.918-2.599-24.04 0-57.828 46.782-104.934 104.934-104.934 30.213 0 57.502 12.67 76.67 33.137 23.715-4.548 46.456-13.32 66.599-25.34-7.798 24.366-24.366 44.833-46.132 57.827 21.117-2.273 41.584-8.122 60.426-16.243-14.292 20.791-32.161 39.308-52.628 54.253z"/></svg></a></li>';
                        }   
            $html .= '</ul>
                    </div>
                </div>
            </div>';
            }
        }
        wp_send_json_success( array( 'html' => $html ) );
    }

    public function load_organizer_events_card_block(){
        $paged = event_m_get_param('page');
        $organizer_id = event_m_get_param('o_id');
        $single_organizer_event_limit = event_m_get_param("show");
        $event_cols = event_m_get_param("cols");
        $event_cols = ($event_cols == '' || $event_cols == 0 || $event_cols > 12) ? 4 : $event_cols;
        $args = array(
            'orderby' => em_append_meta_key('start_date'),
            'posts_per_page' => $single_organizer_event_limit,
            'offset'=> (int) ($paged-1) * $single_organizer_event_limit,
            'paged' => $paged,
        );
        $upcoming_events = $this->services['event']->upcoming_events_for_organizer( $organizer_id, $args );
        $posts = $upcoming_events->posts;
        $posts = apply_filters('ep_filter_front_events', $posts, $atts = array() );
        $posts = array_filter($posts, function($post){ return $post->post_status !== 'draft'; });
        $timestamp = time();
        $i = 0;
        $gs_service = EventM_Factory::get_service('EventM_Setting_Service');
        $global_settings = $gs_service->load_model_from_db();
        $showBookNowForGuestUsers = em_show_book_now_for_guest_users();
        $html = ''; $recurring = 1; $column_class = '';
        if(event_m_get_param('recurring')){
            $recurring = event_m_get_param('recurring');
        }
        if(empty($section_id)){ $ep_card_cls = 'ep-event-box-card'; } else{ $ep_card_cls = 'em_card_edt';}
        foreach ($posts as $post) :
            $event = $this->services['event']->load_model_from_db($post->ID);
            if(empty($recurring) && isset($event->parent) && !empty($event->parent)){
                continue;
            }
            $currency_symbol = em_currency_symbol();
            $booking_allowed = 1;
            if((isset($event->parent) && !empty($event->parent)) && (isset($event->enable_recurrence_automatic_booking) && !empty($event->enable_recurrence_automatic_booking))){
                // if event is recurring and parent has automatic booking enable than not allowed
                $booking_allowed = 0;
            }
            $event->url = em_get_single_event_page_url( $event, $global_settings );
            $emcardEpired ='';
            if (em_is_event_expired($event->id)) {
                $emcardEpired ='emcard-expired';
            }
            $emcardDisable = '';
            if((empty($event->enable_booking) && absint($event->custom_link_enabled) == 0)){
                $emcardDisable = 'em_event_disabled';
            }
            $html .= '<div class="'.$column_class.' ep-box-col-'.$event_cols.'">
                    <div class="'.$ep_card_cls.' '.$emcardEpired.''.$column_class.''.$emcardDisable.'" id="em-event-'.$event->id.'">';
                $html .= '<div class="em_event_cover dbfl">';
                    $thumbImage = esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png');
                    if (!empty($event->cover_image_id)): ?>
                        <?php 
                        $thumbImage = wp_get_attachment_image_src($event->cover_image_id, 'large')[0];
                        if(empty($thumbImage)){
                            $thumbImage = get_the_post_thumbnail($event->id,'large');
                            if(isset($event->parent) && !empty($event->parent) && empty($thumbImage)){
                                $thumbImage = get_the_post_thumbnail($event->parent,'large');
                            }
                        }
                        $html .='<a href="'.$event->url.'"><img src="'.$thumbImage.'"></a>';
                        else:
                        $html .='<a href="'.$event->url.'"><img src="'.esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png').'" class="em-no-image" ></a>';
                    endif;
                $html .='</div>';
                
                $html .='<div class="dbfl em-card-description">';
                    $html .='<div class="em_event_title"  title="'.$event->name.'">';
                        $html .='<a href="'.$event->url.'">'.$event->name.'</a>';
                        if(is_user_logged_in()):
                            ob_start();
                                do_action('event_magic_wishlist_link',$event);
                                $custom_data_wishlist_link = ob_get_contents();
                            ob_end_clean();
                            $html .= $custom_data_wishlist_link;
                        endif;
                    $html .='</div>';
                    $start_date = null; $end_date = null; $start_time = null; $end_time = null; $day = null;
                        if (em_compare_event_dates($event->id)){
                            $day = date_i18n(get_option('date_format'),$event->start_date);
                            $start_time = date_i18n(get_option('time_format'),$event->start_date);
                            $end_time = date_i18n(get_option('time_format'),$event->end_date);
                        }
                        else
                        {
                            $start_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->start_date);
                            $end_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->end_date);
                        }
                    if($event->all_day):
                        $html.='<div class="ep-card-event-date-wrap ep-box-row ep-box-center">'
                            .'<span class="ep-box-col-2"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg></span>'
                            . '<div class="ep-card-event-date ep-box-col-10 em_event_start difl em_wrap">'.date_i18n(get_option('date_format'),$event->start_date).'<span class="em-all-day"> - '.__('ALL DAY','eventprime-event-calendar-management').'</span>
                        </div></div>';
                    elseif(!empty($day)):
                        $html .= '<div class="em_event_start difl em_wrap">'.$day.'</div>';
                        $html .= '<div class="em_event_start difl em_wrap">'.$start_time.'  to  '.$end_time.'</div>';
                    else:
                        $html .= '<div class="em_event_start difl em_wrap">'.$start_date.' -    
                        </div>
                        <div class="em_event_start difl em_wrap">'.$end_date.' 
                        </div>';
                    endif;
                $html .='</div>';
                $html .='<div class="ep-single-box-footer dbfl">
                    <div class="em_event_price  difl">';
                        $ticket_price = $event->ticket_price;
                        if($event->show_fixed_event_price){
                            if($event->fixed_event_price > 0){
                                $ticket_price = $event->fixed_event_price;
                            }
                        }
                        if(!is_numeric($ticket_price)){
                            $html .= $ticket_price;
                        }
                        else{
                            $html .= !empty($ticket_price) ? em_price_with_position($ticket_price) : '';
                        }
                    $html .='</div>';
                    $html .=do_action('event_magic_card_view_after_price',$event);
                    $html .='<div class="ep-single-box-tickets-button difr">
                        <div class="em_event_attr_box em_eventpage_register difl">';
                            
                            if(absint($event->custom_link_enabled) == 1):
                                $html .='<div class="em_header_button em_event_custom_link kf-tickets">
                                    <a class="ep-event-custom-link" target="_blank" href="'.$event->url.'">';
                                            
                                        if(!empty($global_settings->hide_event_custom_link) && !is_user_logged_in()){
                                            $html .= em_global_settings_button_title('Login to View');
                                        }
                                        else{
                                            $html .= em_global_settings_button_title('Click for Details');
                                        }
                                    $html .='</a>';
                                $html ='</div>';
                            elseif($this->services['event']->is_bookable($event)): $current_ts = em_current_time_by_timezone();
                                if($event->status=='expired'):
                                        $html .= '<div class="em_header_button em_event_expired kf-tickets">'.em_global_settings_button_title('Bookings Expired').'</div>';
                                elseif($current_ts>$event->last_booking_date):
                                        $html .='<div class="em_header_button em_booking-closed kf-tickets">'.em_global_settings_button_title('Bookings Closed').'</div>';
                                elseif($current_ts < $event->start_booking_date): 
                                        $html .='<div class="em_header_button em_not_started kf-tickets">'.em_global_settings_button_title('Bookings not started yet').'</div>';
                                else: 
                                    if(!empty($booking_allowed)):
                                        if(is_user_logged_in() || $showBookNowForGuestUsers):
                                            $html .='<form action="'.get_permalink($global_settings->booking_page).'" method="post" name="em_booking">
                                                <a class="em_header_button em_event-booking kf-tickets" name="tickets" onclick="em_event_booking('.$event->id.')" id="em_booking">'.em_global_settings_button_title('Book Now').'</a>
                                                <input type="hidden" name="event_id" value="'.$event->id.'" />
                                                <input type="hidden" name="venue_id" value="'.$event->venue.'" />
                                            </form>';
                                        else: 
                                            $html .='<a class="em_header_button kf-tickets" target="_blank" href="'. add_query_arg('event_id',$event->id,get_permalink($global_settings->profile_page)).'">'.em_global_settings_button_title('Book Now').'</a>';
                                        endif;
                                    endif;
                                endif;
                            elseif($event->status == 'publish'):
                                if(isset($event->standing_capacity) && !empty($event->standing_capacity)):
                                    $html .='<div class="em_event_attr_box em_eventpage_register difl">
                                        <div class="em_header_button em_not_bookable kf-tickets">'.em_global_settings_button_title('All Seats Booked').'</div>
                                    </div>';
                                else:
                                    $html .='<div class="em_event_attr_box em_eventpage_register difl">
                                        <div class="em_header_button em_not_bookable kf-tickets">'. em_global_settings_button_title('Bookings Closed').'</div>
                                    </div>';
                                endif;
                            endif;
                        $html.='</div>';
                    $html.='</div>';
                $html.='</div>';
                $html.=do_action('event_magic_card_view_after_footer',$event);
            $html.='</div></div>';
        
            $i++;
        endforeach;
        wp_send_json_success($html);
    }

    public function load_organizer_events_list_block(){
        $paged = event_m_get_param('page');
        $organizer_id = event_m_get_param('o_id');
        $single_organizer_event_limit = event_m_get_param("show");
        $args = array(
            'orderby' => em_append_meta_key('start_date'),
            'posts_per_page' => $single_organizer_event_limit,
            'offset'=> (int) ($paged-1) * $single_organizer_event_limit,
            'paged' => $paged,
        );
        $upcoming_events = $this->services['event']->upcoming_events_for_organizer( $organizer_id, $args );
        $html = '';$recurring = 1;
        $showBookNowForGuestUsers = em_show_book_now_for_guest_users();
        if(event_m_get_param('recurring')){
            $recurring = event_m_get_param('recurring');
        }
        $posts= $upcoming_events->posts;
        $posts = array_filter($posts, function($post){ return $post->post_status !== 'draft'; });
        if(!empty($posts)){
            $last_month_id = event_m_get_param('last_month_id');
            $global_settings = $this->services['setting']->load_model_from_db();
            $currency_symbol = em_currency_symbol();
            foreach ($posts as $post){
                $event = $this->services['event']->load_model_from_db($post->ID);
                $month_id = date('Ym', $event->start_date);
                if(empty($last_month_id) || $last_month_id != $month_id){
                    $last_month_id = $month_id;
                    $html .= '<div class="ep-month-divider"><span class="ep-listed-event-month">'.date_i18n('F Y', $event->start_date).'<span class="ep-listed-event-month-tag"></span></span></div>';
                }
                if(empty($recurring) && isset($event->parent) && !empty($event->parent)){
                    continue;
                }
                $booking_allowed = 1;
                if((isset($event->parent) && !empty($event->parent)) && (isset($event->enable_recurrence_automatic_booking) && !empty($event->enable_recurrence_automatic_booking))){
                    // if event is recurring and parent has automatic booking enable than not allowed
                    $booking_allowed = 0;
                }
                $event_page = get_permalink(em_global_settings("events_page"));
                $event->url = em_get_single_event_page_url($event, $global_settings);
                $emexpired = '';
                if(em_is_event_expired($event->id)){
                    $emexpired = 'emlist-expired';
                }
                if(empty($event->enable_booking)){
                    $emexpired .= 'em_event_disabled';
                }
                $html .= '<div  id="em-event-'.$event->id.'" class="ep-event-article '.$emexpired.'">';
                    $html .= '<div class="ep-topsec">
                        <div class="em-col-3 difl ep-event-image-wrap ep-col-table-c">
                            <div class="em_event_cover_list dbfl">';
                                $thumbImage = esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png');
                                if (!empty($event->cover_image_id)):
                                    $thumbImage = wp_get_attachment_image_src($event->cover_image_id, 'large')[0];
                                    if(empty($thumbImage)){
                                        $thumbImage = get_the_post_thumbnail($event->id,'large');
                                        if(isset($event->parent) && !empty($event->parent) && empty($thumbImage)){
                                            $thumbImage = get_the_post_thumbnail($event->parent,'large');
                                        }
                                    }
                                    $html .= '<a href="'.$event->url.'">
                                        <img src="'.$thumbImage.'" alt="'.__('Event Cover Image', 'eventprime-event-calendar-management').'">
                                    </a>';
                                else:
                                    $html .= '<a href="'.$event->url.'"><img src="'.esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png').'" alt="'.__('Dummy Image','eventprime-event-calendar-management').'" class="em-no-image" ></a>';
                                endif;
                            $html .= '</div>
                        </div>';

                        $html .= '<div class="em-col-5 difl ep-col-table-c ep-event-content-wrap">
                            <div class="ep-event-content">';
                                $html .= '<h3 class="ep-event-title"><a class="ep-color-hover" data-event-id="'.$event->id.'" href="'.$event->url.'" target="_self">'.$event->name.'</a>
                                </h3>';
                                if(is_user_logged_in()):
                                    ob_start();
                                        do_action('event_magic_wishlist_link',$event);
                                        $wishlist = ob_get_contents();
                                    ob_end_clean();
                                    $html .= $wishlist;
                                endif;
                                if(!empty($event->description)) {
                                    $html .=  '<div class="ep-event-description">'.$event->description.'</div>';
                                }
                            $html .= '</div>';
                            $html .= do_action('event_magic_card_view_after_price', $event);
                        $html .= '</div>';

                        $html .='<div class="em-col-4 difl ep-col-table-c ep-event-meta-wrap">
                            <div class="ep-event-meta ep-color-before">'; 
                                $start_date = null; $end_date = null; $start_time = null; $end_time = null; $day = null;
                                if (em_compare_event_dates($event->id)){
                                    $day = date_i18n(get_option('date_format'),$event->start_date);
                                    $start_time = date_i18n(get_option('time_format'),$event->start_date);
                                    $end_time = date_i18n(get_option('time_format'),$event->end_date);
                                }
                                else{
                                    $start_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->start_date);
                                    $end_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->end_date);
                                }
                                if($event->all_day){
                                    $html .= '<div class="ep-list-event-date-row">
                                        <span class="material-icons em_color">date_range</span> 
                                        <div class="ep-list-event-date">'.
                                            date_i18n(get_option('date_format'),$event->start_date).'
                                            <span class="em-all-day"> - '. __('ALL DAY','eventprime-event-calendar-management') . '</span>
                                        </div>
                                    </div>';
                                } elseif(!empty($day)){
                                    $html .= '<div class="ep-list-event-date-row"><span class="material-icons em_color">date_range</span> <div class="ep-list-event-date">'.$day.' - '.$start_time.'  to  '.$end_time.'</div></div>';
                                }
                                else{
                                    $html .= '<div class="ep-list-event-date-row"><span class="material-icons em_color">date_range</span> <div class="ep-list-event-date">'.$start_date.' - '.$end_date.'</div></div>';
                                }
                                if(!empty($event->venue)){  
                                    $venue_service= EventM_Factory::get_service('EventM_Venue_Service');
                                    $venue= $venue_service->load_model_from_db($event->venue);
                                    if(!empty($venue->id) && !empty($venue->address)){
                                        $html .= '<div class="em-list-view-venue-details" title="'.$venue->address.'"><span class="ep-list-event-location"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zM7 9c0-2.76 2.24-5 5-5s5 2.24 5 5c0 2.88-2.88 7.19-5 9.88C9.92 16.21 7 11.85 7 9z"/><circle cx="12" cy="9" r="2.5"/></svg></span><address class="em-list-event-address"><span>'.$venue->address.'</span></address>
                                                </div>';
                                    }
                                }
                                if(!empty($event->enable_booking) && empty($event->hide_booking_status)):
                                    $sum = $this->services['event']->booked_seats($event->id);
                                    $capacity = em_event_seating_capcity($event->id);
                                    $html .= '<div class="ep-list-booking-status dbfl dbfl">
                                        <div class="kf-event-attr-value dbfl">';
                                            if ($capacity > 0):
                                                $html .= '<div class="dbfl">'.$sum .'/'. $capacity.'</div>';
                                                $width = ($sum / $capacity) * 100;
                                                $html .= '<div class="dbfl"><div id="progressbar" class="em_progressbar dbfl"><div style="width:'.$width .'%'.'" class="em_progressbar_fill em_bg" ></div></div></div>';
                                            else:
                                                if($sum > 0){
                                                    $html .= '<div class="dbfl">' . $sum . ' '.__('Attending','eventprime-event-calendar-management').'</div>';
                                                }
                                            endif;
                                        $html .= '</div>
                                    </div>';
                                endif;

                                $custom_data_before_footer = '';
                                ob_start();
                                    do_action('event_magic_popup_custom_data_before_footer',$event);
                                    $custom_data_before_footer = ob_get_contents();
                                ob_end_clean();
                                $html .= $custom_data_before_footer;

                                $html .= ' <div class="ep-list-view-footer">
                                    <div class="em_event_price difl">';
                                        $ticket_price = $event->ticket_price;
                                        // check if show one time event fees at front enable
                                        if($event->show_fixed_event_price){
                                            if($event->fixed_event_price > 0){
                                                $ticket_price = $event->fixed_event_price;
                                            }
                                        }
                                        $html .= !empty($ticket_price) ? $currency_symbol.$ticket_price : '';
                                    $html .= '</div>';
                                    $html .= do_action('event_magic_card_view_after_price',$event);
                                    $html .= '<div class="kf-tickets-button difr">
                                        <div class="em_event_attr_box em_eventpage_register difl">';
                                            if(absint($event->custom_link_enabled) == 1):
                                                $html .= '<div class="em_header_button em_event_custom_link kf-tickets">
                                                    <a class="em_header_button kf-tickets" target="_blank" href="'.$event->url.'">';
                                                        if(!empty(em_global_settings('hide_event_custom_link')) && !is_user_logged_in()){
                                                            $html .= em_global_settings_button_title('Login to View');
                                                        }
                                                        else{
                                                            $html .= em_global_settings_button_title('Click for Details');
                                                        }
                                                    $html .= '</a>
                                                </div>';
                                            elseif($this->services['event']->is_bookable($event)): 
                                                $current_ts = em_current_time_by_timezone();
                                                if($event->status=='expired'):
                                                    $html .= '<div class="em_header_button em_event_expired kf-tickets">'.
                                                    em_global_settings_button_title('Bookings Expired').'</div>';
                                                elseif($current_ts>$event->last_booking_date):
                                                    $html .= '<div class="em_header_button em_booking-closed kf-tickets">'.
                                                    em_global_settings_button_title('Bookings Closed').'</div>';
                                                elseif($current_ts<$event->start_booking_date):
                                                    $html .= '<div class="em_header_button em_not_started kf-tickets">'.
                                                    em_global_settings_button_title('Bookings not started yet').'</div>';
                                                else:
                                                    if(!empty($booking_allowed)):
                                                        if(is_user_logged_in() || $showBookNowForGuestUsers):
                                                            $html .= '<form action="'.get_permalink($global_settings->booking_page).'" method="post" name="em_booking">';
                                                                $html .= '<button class="em_header_button em_event-booking kf-tickets em_color" name="tickets" onclick="em_event_booking('.$event->id.')" id="em_booking">'.
                                                                em_global_settings_button_title('Book Now').'</button>';
                                                                $html .= '<input type="hidden" name="event_id" value="'.$event->id.'" />';
                                                                $html .= '<input type="hidden" name="venue_id" value="'.$event->venue.'" />';
                                                            $html .= '</form>';
                                                        else:
                                                            $html .= '<a class="em_header_button em_event-booking kf-tickets em_color" target="_blank" href="'.add_query_arg('event_id',$event->id,get_permalink($global_settings->profile_page)).'">'.
                                                            em_global_settings_button_title('Book Now').'</a>';
                                                        endif;
                                                    endif;
                                                endif;
                                            elseif($event->status == 'publish'):
                                                if(isset($event->standing_capacity) && !empty($event->standing_capacity)):
                                                    $html .= '<div class="em_event_attr_box em_eventpage_register difl"><div class="em_header_button em_not_bookable kf-tickets">'.em_global_settings_button_title('All Seats Booked').'</div>
                                                    </div>';
                                                else:
                                                    $html .= '<div class="em_event_attr_box em_eventpage_register difl">
                                                        <div class="em_header_button em_not_bookable kf-tickets">'.em_global_settings_button_title('Bookings Closed').'</div>
                                                    </div>';
                                                endif;
                                            endif;
                                        $html .= '</div>';
                                    $html .= '</div>';
                                $html .= '</div>';
                                $html .= do_action('event_magic_card_view_after_footer',$event);
                            $html .= '</div>';
                        $html .= '</div>';
                    $html .= '</div>';
                $html .= '</div>';
            }
                
        }
        wp_send_json_success(array('html' => $html, 'last_month_id' => $last_month_id));
    }

    public function load_organizer_events_mini_list_block(){
        $paged = event_m_get_param('page');
        $organizer_id = event_m_get_param('p_id');
        $single_type_event_limit = event_m_get_param("show");
        $args = array(
            'orderby' => em_append_meta_key('start_date'),
            'posts_per_page' => $single_type_event_limit,
            'offset'=> (int) ($paged-1) * $single_type_event_limit,
            'paged' => $paged,
        );
        $upcoming_events = $this->services['event']->upcoming_events_for_organizer( $organizer_id, $args );
        $posts = $upcoming_events->posts;
        $posts = apply_filters('ep_filter_front_events', $posts, $atts = array() );
        $posts = array_filter($posts, function($post){ return $post->post_status !== 'draft'; });
        $timestamp = time();
        $i = 0;
        $gs_service = EventM_Factory::get_service('EventM_Setting_Service');
        $global_settings = $gs_service->load_model_from_db();
        $showBookNowForGuestUsers = em_show_book_now_for_guest_users();
        $html = '';$recurring = 1; $column_class = '';
        if(event_m_get_param('recurring')){
            $recurring = event_m_get_param('recurring');
        }
        $section_id = '';
        if(empty($section_id)){ $ep_card_cls = 'ep-event-box-card'; } else{ $ep_card_cls = 'em_card_edt';}
        $today = em_current_time_by_timezone();
        foreach ($posts as $post) :
            $event = $this->services['event']->load_model_from_db($post->ID);
            if(empty($recurring) && isset($event->parent) && !empty($event->parent)){
                continue;
            }
            $currency_symbol = em_currency_symbol();
            $booking_allowed = 1;
            if((isset($event->parent) && !empty($event->parent)) && (isset($event->enable_recurrence_automatic_booking) && !empty($event->enable_recurrence_automatic_booking))){
                // if event is recurring and parent has automatic booking enable than not allowed
                $booking_allowed = 0;
            }
           
            $event->url = em_get_single_event_page_url( $event, $global_settings );
            $emcardEpired ='';
            if (em_is_event_expired($event->id)) {
                $emcardEpired ='emcard-expired';
            }
            $emcardDisable = '';
            if((empty($event->enable_booking) && absint($event->custom_link_enabled) == 0)){
                $emcardDisable = 'em_event_disabled';
            }
            $html .= '<div class="kf-upcoming-event-row em_block dbfl '.$emcardEpired.' '.$emcardDisable.'">
                <div class="kf-upcoming-event-thumb em-col-2 difl">';
                $html .='<a href="'.$event->url.'">';
                $thumbImage = esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png');
                if (!empty($event->cover_image_id)): ?>
                    <?php 
                    $thumbImage = wp_get_attachment_image_src($event->cover_image_id, 'large')[0];
                    if(empty($thumbImage)){
                        $thumbImage = get_the_post_thumbnail($event->id,'large');
                        if(isset($event->parent) && !empty($event->parent) && empty($thumbImage)){
                            $thumbImage = get_the_post_thumbnail($event->parent,'large');
                        }
                    }
                    $html .= '<img src="'.$thumbImage.'" alt="'.__('Event Cover Image', 'eventprime-event-calendar-management').'">';
                    else:
                    $html .= '<img src="'.esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png').'" alt="'.__('Dummy Image','eventprime-event-calendar-management').'" class="em-no-image" >';
                    endif;
                $html .= '</a>
                    </div>';
                
                $html .='<div class="kf-upcoming-event-title em-col-5 em-col-pad20 difl">';
                        $html .='<a href="'.$event->url.'">'.$event->name.'</a>';
                        if(is_user_logged_in()):
                            ob_start();
                                do_action('event_magic_wishlist_link',$event);
                                $custom_data_wishlist_link = ob_get_contents();
                            ob_end_clean();
                            $html .= $custom_data_wishlist_link;
                        endif;
                    if ($today>$event->start_date && $today<$event->end_date) {
                    $html .= '<span class="kf-live">'.__('Live','eventprime-event-calendar-management').'</span>';
                            } 
                            $html .= '<div class="kf-upcoming-event-post-date">
                            <div class="em_event_start difl em_wrap">
                                '.date_i18n(get_option('date_format').' '.get_option('time_format'), $event->start_date).'
                                <span> - </span>
                                '.date_i18n(get_option('date_format').' '.get_option('time_format'), $event->end_date).'
                            </div>
                        </div>';    
                    $html .='</div>';
                    $html .='<div class="kf-upcoming-event-booking em-col-5 em-col-pad20 difr">
                    <div class="em_header_button kf-button">';
                        if ($this->services['event']->is_bookable($event) && absint($event->custom_link_enabled) != 1): $current_ts = em_current_time_by_timezone();
                            if ($event->status=='expired'):
                            $html .='<div class="em_header_button em_not_bookable kf-tickets">'.em_global_settings_button_title('Bookings Expired').'</div>';
                            elseif ($current_ts>$event->last_booking_date):
                            $html .='<div class="em_header_button em_not_bookable kf-button">'.em_global_settings_button_title('Bookings Closed').'</div>';
                            elseif($current_ts<$event->start_booking_date): 
                                $html .='<div class="em_header_button em_not_bookable kf-button">'.em_global_settings_button_title('Bookings not started yet').'</div>';
                            else:
                                if(is_user_logged_in() || $showBookNowForGuestUsers):
                                    $html .='<form action="'.get_permalink($global_settings->booking_page).'" method="post" name="em_booking">
                                        <button class="em_header_button kf-button em_color" name="tickets" onclick="em_event_booking('.$event->id.')" class="em_header_button" id="em_booking">
                                            <i class="fa fa-ticket" aria-hidden="true"></i>
                                            '.em_global_settings_button_title('Book Now');
                                            if ($event->ticket_price > 0){
                                                $ticketPrice = $event->ticket_price;
                                                // check if show one time event fees at front enable
                                                if($event->show_fixed_event_price){
                                                    if($event->fixed_event_price > 0){
                                                        $ticketPrice = $event->fixed_event_price;
                                                    }
                                                }
                                                if ($ticketPrice > 0){
                                                    $html .= " - " . '<span class="em_event_price">' . em_price_with_position($ticketPrice, $currency_symbol) . '</span>';
                                                }
                                                $html .=  do_action('event_magic_single_event_ticket_price_after', $event, $ticketPrice);
                                            }
                                    $html .='</button>
                                        <input type="hidden" name="event_id" value="'.$event->id.'" />
                                        <input type="hidden" name="venue_id" value="'.$event->venue.'" />
                                    </form>';
                                    else:
                                        $html .='<a class="em_header_button kf-button em_color" target="_blank" href="'.add_query_arg('event_id',$event->id, get_permalink($global_settings->profile_page)).'">'.em_global_settings_button_title('Book Now').'</a>';
                                    endif;
                                endif;
                            elseif(absint($event->custom_link_enabled) != 1):
                            $html .='<div class="em_event_attr_box em_eventpage_register difl">
                                <div class="em_header_button em_not_bookable kf-button">
                                    '.em_global_settings_button_title('Bookings Closed').'
                                </div>
                            </div>';
                            endif; 
                $html .='</div>
                </div>
            </div>';       
            $i++;
        endforeach;
        wp_send_json_success($html);
    }
    
    public function load_venues_card_data(){
        $paged = event_m_get_param( 'page' );
        $venue_limit = event_m_get_param( 'show' );
        $featured = event_m_get_param( 'featured' );
        $em_search = event_m_get_param( 'em_search' );
        $em_search = ( $em_search != 'false' ) ? $em_search : '';
        $venue_cols = absint( event_m_get_param( 'cols' ) );
        $venue_cols = ( $venue_cols == 0 || $venue_cols > 12 ) ? 4 : $venue_cols;
        $meta_query = array();
        if( $featured == 1 ){ 
            array_push( $meta_query, array(
                array(
                    'key'     => em_append_meta_key('is_featured'),
                    'value'   => 1
                )
            ));
        }
        $args = array(
            'orderby' => 'date',
            'number' => $venue_limit,
            'offset' => (int) ( $paged - 1 ) * $venue_limit,
             'paged' => $paged,
        'name__like' => $em_search,
        'meta_query' => $meta_query
        );

        $the_query = $this->services['venue']->get_all_venues_query( $args );
        $html = '';
        $venues = is_object( $the_query ) ? $the_query->terms : '';
        $global_settings = $this->services['setting']->load_model_from_db();
        if( ! empty( $venues ) ){
            foreach( $venues as $venue ){
                $venues_page_url = get_permalink( em_global_settings( "venues_page" ) );
                $venue_url = add_query_arg('venue', $venue->id, $venues_page_url);
                $enable_seo_urls = em_global_settings('enable_seo_urls');
                if(!empty($enable_seo_urls)){
                    $venue_url = get_term_link($venue->id);
                }
                $html .= '<div class="ep-box-col-'.$venue_cols.' ep-col-md-6">
                        <div class="ep-box-card-item">
                            <div class="ep-box-card-thumb" >';
                            if ( ! empty( $venue->gallery_images ) ){
                                $html .= '<a href="'.$venue_url.'" class="ep-img-link"><img src="'.wp_get_attachment_image_src( $venue->gallery_images[0], 'full'  )[0].'" alt="'.__( 'Event Site/Location Image', 'eventprime-event-calendar-management' ).'"></a>';
                            }else{
                                $html .= '<a href="'.$venue_url.'" class="ep-img-link"><img src="'.esc_url( plugins_url( 'templates/images/dummy_image.png', __FILE__ ) ).'" alt="'.__( 'Dummy Image','eventprime-event-calendar-management' ).'"></a>'; 
                            }
              $html .= '</div>
                        <div class="ep-box-card-content">
                            <div class="ep-box-title ep-box-card-title">
                                <a href="'.$venue_url.'">'.$venue->name.'</a>
                            </div>';
                    $html .= '<div class="kf-venue-seating-capacity dbfl em_color">';
                                if (!empty($venue->type)) : // here we are checking about the type first because in Standing we dont have capacity and in Seat we have capacity
                                    if ($venue->type == 'standings'):
                                        $html .= '<div class="kf-event-attr-name em_color dbfl">
                                            '.__("Type",'eventprime-event-calendar-management').'
                                        </div>
                                        <div class="kf-event-attr-value dbfl">
                                            '.__("Standing",'eventprime-event-calendar-management').'
                                        </div>';

                                    else:
                                        $html .= '<div class="kf-event-attr-name em_color dbfl">
                                                 '.__("Capacity",'eventprime-event-calendar-management').'
                                        </div>
                                        <div class="kf-event-attr-value dbfl"> 
                                             '.$venue->seating_capacity.' '.__('People','eventprime-event-calendar-management').'
                                        </div>';
                                    endif;
                                endif;
                    $html .= '</div>
                            <div class="em_venue_add dbfl">';
                                if (!empty($venue->address)){
                                    $html .= wp_trim_words($venue->address, 10);
                                }
                    $html .= '</div>';
              $html .= '</div>
                    </div>
                </div>';
            }
        }
        wp_send_json_success( $html );
    }

    public function load_venues_box_data(){
        $paged = event_m_get_param( 'page' );
        $venue_limit = event_m_get_param( 'show' );
        $featured = event_m_get_param( 'featured' );
        $em_search = event_m_get_param( 'em_search');
        $em_search = ( $em_search != 'false' ) ? $em_search : '';
        $venue_cols = absint( event_m_get_param( 'cols' ) );
        $venue_cols = ( $venue_cols == 0 || $venue_cols > 12 ) ? 4 : $venue_cols;
        $meta_query = array();
        if( $featured == 1 ){ 
            array_push( $meta_query, array(
                array(
                    'key'     => em_append_meta_key('is_featured'),
                    'value'   => 1
                )
            ));
        }
        $args = array(
            'orderby' => 'date',
            'number' => $venue_limit,
            'offset' => (int) ( $paged - 1 ) * $venue_limit,
             'paged' => $paged,
        'name__like' => $em_search,
        'meta_query' => $meta_query
        );
        $the_query = $this->services['venue']->get_all_venues_query( $args );
        $html = '';
        $venues = $the_query->terms;
        $global_settings = $this->services['setting']->load_model_from_db();
        if( ! empty( $venues ) ){
            $b = event_m_get_param('bnum');
            $venue_box_color = em_global_settings('venue_box_color');
            foreach( $venues as $venue ){
                $venues_page_url = get_permalink( em_global_settings( "venues_page" ) );
                $venue_url = add_query_arg('venue', $venue->id, $venues_page_url);
                $enable_seo_urls = em_global_settings('enable_seo_urls');
                if(!empty($enable_seo_urls)){
                    $venue_url = get_term_link($venue->id);
                }
                if( $b > 4 ) { $b = 1;}
                switch ($b) {
                    case 1 :
                        $bg_color = (!empty($venue_box_color) && isset($venue_box_color[0])) ? '#'.$venue_box_color[0] : '#A6E7CF';
                        break;
                    case 2 :
                        $bg_color = (!empty($venue_box_color) && isset($venue_box_color[1])) ? '#'.$venue_box_color[1] : '#DBEEC1';
                        break;
                    case 3 :
                        $bg_color = (!empty($performer_box_color) && isset($venue_box_color[2])) ? '#'.$venue_box_color[2] : '#FFD3B6';
                        break;
                    case 4 :
                        $bg_color = (!empty($venue_box_color) && isset($venue_box_color[3])) ? '#'.$venue_box_color[3] : '#FFA9A5';
                        break;
                    default:
                        $bg_color = '#A6E7CF';
                }
                $light_bg_color = ep_hex2rgba($bg_color, .5);
                $bg_color = ep_hex2rgba($bg_color, 1);

                $html .= '<div class="ep-box-col-'.$venue_cols.' ep-box-column ep-box-px-0" data-id="'.$venue->id.'" data-element_type="column">
                            <div class="ep-column-wrap ep-column-populated" style="background-image: linear-gradient(190deg,'.$bg_color.','.$light_bg_color.'); background-color: transparent;">
                                <div class="ep-box-widget-wrap" data-id="'.$venue->id.'">
                                    <div class="ep-box-box-item">
                                        <div class="ep-box-box-thumb">';
                                            if ( ! empty( $venue->gallery_images ) ){
                                                $html .= '<a href="'.$venue_url.'" class="img-fluid"><img src="'.wp_get_attachment_image_src( $venue->gallery_images[0], 'full' )[0].'" alt="'.__( 'Event Site/Location Image', 'eventprime-event-calendar-management' ).'"></a>';
                                            }else{ 
                                                $html .= '<a href="'.$venue_url.'" class="img-fluid"><img src="'.esc_url( plugins_url('templates/images/dummy_image.png', __FILE__ ) ).'" class="img-fluid" alt="'.__('Dummy Image','eventprime-event-calendar-management').'"></a>'; 
                                            }         
                                $html .= '</div>
                                        <div class="ep-venue-content">
                                            <div class="ep-box-title ep-box-box-title">
                                                <a href="'.$venue_url.'">'.$venue->name.'</a>
                                            </div>'; 
                                    $html .= '<div class="kf-venue-seating-capacity dbfl em_color">';
                                                if (!empty($venue->type)) : // here we are checking about the type first because in Standing we dont have capacity and in Seat we have capacity
                                                    if ($venue->type == 'standings'):
                                                        $html .= '<div class="kf-event-attr-name em_color dbfl">
                                                            '.__("Type",'eventprime-event-calendar-management').'
                                                        </div>
                                                        <div class="kf-event-attr-value dbfl">
                                                            '.__("Standing",'eventprime-event-calendar-management').'
                                                        </div>';
                
                                                    else:
                                                        $html .= '<div class="kf-event-attr-name em_color dbfl">
                                                                '.__("Capacity",'eventprime-event-calendar-management').'
                                                        </div>
                                                        <div class="kf-event-attr-value dbfl"> 
                                                            '.$venue->seating_capacity.' '.__('People','eventprime-event-calendar-management').'
                                                        </div>';
                                                    endif;
                                                endif;
                                    $html .= '</div>
                                            <div class="em_venue_add dbfl">';
                                                
                                                if (!empty($venue->address)){
                                                    $html .= wp_trim_words($venue->address, 10);
                                                }
                                            
                                    $html .= '</div>
                                        </div>
                                </div>
                            </div>
                        </div>
                    </div>';
            $b++;
            }
        }
        wp_send_json_success( $html );
    }

    public function load_venues_list_data(){
        $paged = event_m_get_param( 'page' );
        $venue_limit = event_m_get_param( 'show' );
        $featured = event_m_get_param( 'featured' );
        $em_search = event_m_get_param( 'em_search' );
        $em_search = ( $em_search != 'false' ) ? $em_search : '';
        $meta_query = array();
        if( $featured == 1 ){ 
            array_push( $meta_query, array(
                array(
                    'key'     => em_append_meta_key('is_featured'),
                    'value'   => 1
                )
            ));
        }
        $args = array(
            'orderby' => 'date',
            'number' => $venue_limit,
            'offset' => (int) ( $paged - 1 ) * $venue_limit,
             'paged' => $paged,
        'name__like' => $em_search,
        'meta_query' => $meta_query
        );
        $the_query= $this->services['venue']->get_all_venues_query( $args );
        $html = '';
        $venues = $the_query->terms;
        if( ! empty( $venues ) ){
            foreach( $venues as $venue ){
                $venues_page_url = get_permalink( em_global_settings( "venues_page" ) );
                $venue_url = add_query_arg('venue', $venue->id, $venues_page_url);
                $enable_seo_urls = em_global_settings('enable_seo_urls');
                if(!empty($enable_seo_urls)){
                    $venue_url = get_term_link($venue->id);
                }
        $html .= '<div class="ep-box-list-wrap">
                    <div class="ep-box-row">
                        <div class="ep-box-col-4 ep-list-box-table ep-box-profile-image">';
                            if ( ! empty( $venue->gallery_images ) ){
                            $html .= '<a href="'.$venue_url.'" ><img src="'.wp_get_attachment_image_src( $venue->gallery_images[0], 'full' )[0].'" alt="'.__( 'Event Site/Location Image', 'eventprime-event-calendar-management' ).'"></a>';
                            }else{
                            $html .= '<a href="'.$venue_url.'" ><img src="'.esc_url( plugins_url( 'templates/images/dummy_image.png', __FILE__ ) ).'" alt="'.__( 'Dummy Image', 'eventprime-event-calendar-management' ).'"></a>';
                            } 
              $html .= '</div>
                    <div class="ep-box-col-6 ep-list-box-table">
                        <div class="ep-box-list-items">
                            <div class="ep-box-title ep-box-list-title">
                                <a class="ep-color-hover" data-venue-id="'.$venue->id.'" href="'.$venue_url.'" target="_self" rel="noopener">
                                    '.$venue->name.'
                                </a>
                            </div>';

                  $html .= '<div class="kf-venue-seating-capacity dbfl em_color">';
                            if (!empty($venue->type)) : // here we are checking about the type first because in Standing we dont have capacity and in Seat we have capacity
                                if ($venue->type == 'standings'):
                                    $html .= '<div class="kf-event-attr-name em_color dbfl">
                                        '.__("Type",'eventprime-event-calendar-management').'
                                    </div>
                                    <div class="kf-event-attr-value dbfl">
                                        '.__("Standing",'eventprime-event-calendar-management').'
                                    </div>';

                                else:
                                    $html .= '<div class="kf-event-attr-name em_color dbfl">
                                            '.__("Capacity",'eventprime-event-calendar-management').'
                                    </div>
                                    <div class="kf-event-attr-value dbfl"> 
                                        '.$venue->seating_capacity.' '.__('People','eventprime-event-calendar-management').'
                                    </div>';
                                endif;
                            endif;
                    $html .= '</div>';
                            
                  $html .= '<div class="ep-event-description">
                                <div class="ep-event-meta ep-color-before ">
                                <div class="em_venue_add dbfl">';
                                    if (!empty($venue->address)){
                                        $html .= wp_trim_words($venue->address, 10);
                                    }
                        $html .= '</div>';
                        $html .= '<a class="ep-booking-button" data-venue-id="'.$venue->id.'" href="'.add_query_arg( "venue", $venue->id, $venues_page_url ).'">View Detail</a>
                                </div>
                            </div>
                        </div>
                    </div>';
          $html .= '<div class="ep-box-col-2 ep-list-box-table box-boder-l">
                        <ul class="ep-box-social-links"></ul>
                    </div>
                </div>
            </div>';
            }
        }
        wp_send_json_success( array( 'html' => $html ) );
    }

    public function load_venue_events_card_block(){
        $paged = event_m_get_param('page');
        $venue_id = event_m_get_param('venue_id');
        $single_venue_event_limit = event_m_get_param("show");
        $event_cols = event_m_get_param("cols");
        $event_cols = ($event_cols == '' || $event_cols == 0 || $event_cols > 12) ? 4 : $event_cols;
        $args = array(
            'orderby' => em_append_meta_key('start_date'),
            'posts_per_page' => $single_venue_event_limit,
            'offset'=> (int) ($paged-1) * $single_venue_event_limit,
            'paged' => $paged,
        );
        $upcoming_events = $this->services['event']->upcoming_events_for_venue( $venue_id, $args );
        $posts = $upcoming_events->posts;
        $posts = apply_filters('ep_filter_front_events', $posts, $atts = array() );
        $posts = array_filter($posts, function($post){ return $post->post_status !== 'draft'; });
        $timestamp = time();
        $i = 0;
        $gs_service = EventM_Factory::get_service('EventM_Setting_Service');
        $global_settings = $gs_service->load_model_from_db();
        $showBookNowForGuestUsers = em_show_book_now_for_guest_users();
        $html = ''; $recurring = 1; $column_class = '';
        if(event_m_get_param('recurring')){
            $recurring = event_m_get_param('recurring');
        }
        foreach ($posts as $post) :
            $event = $this->services['event']->load_model_from_db($post->ID);
            if(empty($recurring) && isset($event->parent) && !empty($event->parent)){
                continue;
            }
            $currency_symbol = em_currency_symbol();
            $booking_allowed = 1;
            if((isset($event->parent) && !empty($event->parent)) && (isset($event->enable_recurrence_automatic_booking) && !empty($event->enable_recurrence_automatic_booking))){
                // if event is recurring and parent has automatic booking enable than not allowed
                $booking_allowed = 0;
            }
            $event->url = em_get_single_event_page_url($event, $global_settings);
            $emcardEpired ='';
            if (em_is_event_expired($event->id)) {
                $emcardEpired ='emcard-expired';
            }
            $emcardDisable = '';
            if((empty($event->enable_booking) && absint($event->custom_link_enabled) == 0)){
                $emcardDisable = 'em_event_disabled';
            }
            $html .='<div class="col-md-'.$event_cols.' em_card difl '.$emcardEpired.''.$column_class.''.$emcardDisable.'" id="em-event-'.$event->id.'">';
                $html .= '<div class="em_event_cover dbfl">';
                    $thumbImage = esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png');
                    if (!empty($event->cover_image_id)): ?>
                        <?php 
                        $thumbImage = wp_get_attachment_image_src($event->cover_image_id, 'large')[0];
                        if(empty($thumbImage)){
                            $thumbImage = get_the_post_thumbnail($event->id,'large');
                            if(isset($event->parent) && !empty($event->parent) && empty($thumbImage)){
                                $thumbImage = get_the_post_thumbnail($event->parent,'large');
                            }
                        }
                        $html .='<a href="'.$event->url.'"><img src="'.$thumbImage.'"></a>';
                        else:
                        $html .='<a href="'.$event->url.'"><img src="'.esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png').'" class="em-no-image" ></a>';
                    endif;
                $html .='</div>';
                
                $html .='<div class="dbfl em-card-description">';
                    $html .='<div class="em_event_title em_block dbfl"  title="'.$event->name.'">';
                        $html .='<a href="'.$event->url.'">'.$event->name.'</a>';
                        if(is_user_logged_in()):
                            ob_start();
                                do_action('event_magic_wishlist_link',$event);
                                $custom_data_wishlist_link = ob_get_contents();
                            ob_end_clean();
                            $html .= $custom_data_wishlist_link;
                        endif;
                    $html .='</div>';
                    $start_date = null; $end_date = null; $start_time = null; $end_time = null; $day = null;
                        if (em_compare_event_dates($event->id)){
                            $day = date_i18n(get_option('date_format'),$event->start_date);
                            $start_time = date_i18n(get_option('time_format'),$event->start_date);
                            $end_time = date_i18n(get_option('time_format'),$event->end_date);
                        }
                        else
                        {
                            $start_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->start_date);
                            $end_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->end_date);
                        }
                    if($event->all_day):
                        $html.='<div class="em_event_start difl em_color em_wrap">'.date_i18n(get_option('date_format'),$event->start_date).'<span class="em-all-day"> - '.__('ALL DAY','eventprime-event-calendar-management').'</span>
                        </div>';
                    elseif(!empty($day)):
                        $html .= '<div class="em_event_start difl em_color em_wrap">'.$day.'</div>';
                        $html .= '<div class="em_event_start difl em_color em_wrap">'.$start_time.'  to  '.$end_time.'</div>';
                    else:
                        $html .= '<div class="em_event_start difl em_color em_wrap">'.$start_date.' -    
                        </div>
                        <div class="em_event_start difl em_color em_wrap">'.$end_date.' 
                        </div>';
                    endif;
                        
                    if(!empty($event->venue)){  
                        $venue_service= EventM_Factory::get_service('EventM_Venue_Service');
                        $venue= $venue_service->load_model_from_db($event->venue);
                        if(!empty($venue->id)){ 
                            $html .='<div class="em_event_address dbfl" title="'.$venue->address.'">'. $venue->address.'</div>';
                        }
                    }
                    if(!empty($event->description)) {
                        $html .= '<div class="em_event_description dbfl">'.$event->description.'</div>';
                    }
                    if(!empty($event->enable_booking) && empty($event->hide_booking_status)):
                        $sum = $this->services['event']->booked_seats($event->id);
                        $capacity = em_event_seating_capcity($event->id);
                        $html .='<div class="dbfl">
                            <div class="kf-event-attr-value dbfl">';  
                                if ($capacity > 0):
                                    $html .='<div class="dbfl">
                                        '.$sum.' / '.$capacity.' 
                                    </div>';
                                $width = ($sum / $capacity) * 100;
                                    $html .='<div class="dbfl">
                                        <div id="progressbar" class="em_progressbar dbfl">
                                            <div style="width:'. $width . '%" class="em_progressbar_fill em_bg" ></div>
                                        </div>';
                                    $html.='</div>';
                                
                                    else:
                                        $html .='<div class="dbfl">' . $sum . ' '.__('Attending','eventprime-event-calendar-management').'</div>';
                                ?>
                                <?php endif;
                            $html .='</div>';
                        $html .='</div>'; 
                    endif;
                    $custom_data_before_footer = '';
                    ob_start();
                        do_action('event_magic_popup_custom_data_before_footer',$event);
                        $custom_data_before_footer = ob_get_contents();
                    ob_end_clean();
                    $html .= $custom_data_before_footer;
                $html .='</div>';
                $html .='<div class="em-cards-footer dbfl">
                    <div class="em_event_price  difl">';
                        $ticket_price = $event->ticket_price;
                        if($event->show_fixed_event_price){
                            if($event->fixed_event_price > 0){
                                $ticket_price = $event->fixed_event_price;
                            }
                        }
                        if(!is_numeric($ticket_price)){
                            $html .= $ticket_price;
                        }
                        else{
                            $html .= !empty($ticket_price) ? em_price_with_position($ticket_price) : '';
                        }
                    $html .='</div>';
                    $html .=do_action('event_magic_card_view_after_price',$event);
                    $html .='<div class="kf-tickets-button difr">
                        <div class="em_event_attr_box em_eventpage_register difl">';
                            
                            if(absint($event->custom_link_enabled) == 1):
                                $html .='<div class="em_header_button em_event_custom_link kf-tickets">
                                    <a class="ep-event-custom-link" target="_blank" href="'.$event->url.'">';
                                            
                                        if(!empty($global_settings->hide_event_custom_link) && !is_user_logged_in()){
                                            $html .= em_global_settings_button_title('Login to View');
                                        }
                                        else{
                                            $html .= em_global_settings_button_title('Click for Details');
                                        }
                                    $html .='</a>';
                                $html ='</div>';
                            elseif($this->services['event']->is_bookable($event)): $current_ts = em_current_time_by_timezone();
                                if($event->status=='expired'):
                                        $html .= '<div class="em_header_button em_event_expired kf-tickets">'.em_global_settings_button_title('Bookings Expired').'</div>';
                                elseif($current_ts>$event->last_booking_date):
                                        $html .='<div class="em_header_button em_booking-closed kf-tickets">'.em_global_settings_button_title('Bookings Closed').'</div>';
                                elseif($current_ts < $event->start_booking_date): 
                                        $html .='<div class="em_header_button em_not_started kf-tickets">'.em_global_settings_button_title('Bookings not started yet').'</div>';
                                else: 
                                    if(!empty($booking_allowed)):
                                        if(is_user_logged_in() || $showBookNowForGuestUsers):
                                            $html .= '<form action="'.get_permalink($global_settings->booking_page).'" method="post" name="em_booking">
                                                <button class="em_header_button em_event-booking kf-tickets" name="tickets" onclick="em_event_booking('.$event->id.')" id="em_booking">'.em_global_settings_button_title('Book Now').'</button>
                                                <input type="hidden" name="event_id" value="'.$event->id.'" />
                                                <input type="hidden" name="venue_id" value="'.$event->venue.'" />
                                            </form>';
                                        else: 
                                            $html .= '<a class="em_header_button kf-tickets" target="_blank" href="'. add_query_arg('event_id',$event->id,get_permalink($global_settings->profile_page)).'">'.em_global_settings_button_title('Book Now').'</a>';
                                        endif;
                                    endif;
                                endif;
                            elseif($event->status == 'publish'):
                                if(isset($event->standing_capacity) && !empty($event->standing_capacity)):
                                    $html .='<div class="em_event_attr_box em_eventpage_register difl">
                                        <div class="em_header_button em_not_bookable kf-tickets">'.em_global_settings_button_title('All Seats Booked').'</div>
                                    </div>';
                                else:
                                    $html .='<div class="em_event_attr_box em_eventpage_register difl">
                                        <div class="em_header_button em_not_bookable kf-tickets">'. em_global_settings_button_title('Bookings Closed').'</div>
                                    </div>';
                                endif;
                            endif;
                        $html.='</div>';
                    $html.='</div>';
                $html.='</div>';
                $html.=do_action('event_magic_card_view_after_footer',$event);
            $html.='</div>';
        
            $i++;
        endforeach; 
        wp_send_json_success($html);
    }

    public function load_venue_events_list_block(){
        $paged = event_m_get_param('page');
        $venue_id = event_m_get_param('venue_id');
        $single_type_event_limit = event_m_get_param("show");
        $args = array(
            'orderby' => em_append_meta_key('start_date'),
            'posts_per_page' => $single_type_event_limit,
            'offset'=> (int) ($paged-1) * $single_type_event_limit,
            'paged' => $paged,
        );
        $upcoming_events = $this->services['event']->upcoming_events_for_venue( $venue_id, $args );
        $html = '';$recurring = 1;
        $showBookNowForGuestUsers = em_show_book_now_for_guest_users();
        if(event_m_get_param('recurring')){
            $recurring = event_m_get_param('recurring');
        }
        $posts= $upcoming_events->posts;
        $posts = array_filter($posts, function($post){ return $post->post_status !== 'draft'; });
        if(!empty($posts)){
            $last_month_id = event_m_get_param('last_month_id');
            $global_settings =  $this->services['setting']->load_model_from_db();
            $currency_symbol = em_currency_symbol();
            foreach ($posts as $post){
                $event = $this->services['event']->load_model_from_db($post->ID);
                $month_id = date('Ym', $event->start_date);
                if(empty($last_month_id) || $last_month_id != $month_id){
                    $last_month_id = $month_id;
                    $html .= '<div class="ep-month-divider"><span class="ep-listed-event-month">'.date_i18n('F Y', $event->start_date).'<span class="ep-listed-event-month-tag"></span></span></div>';
                }
                if(empty($recurring) && isset($event->parent) && !empty($event->parent)){
                    continue;
                }
                $booking_allowed = 1;
                if((isset($event->parent) && !empty($event->parent)) && (isset($event->enable_recurrence_automatic_booking) && !empty($event->enable_recurrence_automatic_booking))){
                    // if event is recurring and parent has automatic booking enable than not allowed
                    $booking_allowed = 0;
                }
                $event_page = get_permalink(em_global_settings("events_page"));
                $event->url = em_get_single_event_page_url($event, $global_settings);
                $emexpired = '';
                if(em_is_event_expired($event->id)){
                    $emexpired = 'emlist-expired';
                }
                if(empty($event->enable_booking)){
                    $emexpired .= 'em_event_disabled';
                }
                $html .= '<div  id="em-event-'.$event->id.'" class="ep-event-article '.$emexpired.'">';
                    $html .= '<div class="ep-topsec">
                        <div class="em-col-3 difl ep-event-image-wrap ep-col-table-c">
                            <div class="em_event_cover_list dbfl">';
                                $thumbImage = esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png');
                                if (!empty($event->cover_image_id)):
                                    $thumbImage = wp_get_attachment_image_src($event->cover_image_id, 'large')[0];
                                    if(empty($thumbImage)){
                                        $thumbImage = get_the_post_thumbnail($event->id,'large');
                                        if(isset($event->parent) && !empty($event->parent) && empty($thumbImage)){
                                            $thumbImage = get_the_post_thumbnail($event->parent,'large');
                                        }
                                    }
                                    $html .= '<a href="'.$event->url.'">
                                        <img src="'.$thumbImage.'" alt="'.__('Event Cover Image', 'eventprime-event-calendar-management').'">
                                    </a>';
                                else:
                                    $html .= '<a href="'.$event->url.'"><img src="'.esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png').'" alt="'.__('Dummy Image','eventprime-event-calendar-management').'" class="em-no-image" ></a>';
                                endif;
                            $html .= '</div>
                        </div>';

                        $html .= '<div class="em-col-5 difl ep-col-table-c ep-event-content-wrap">
                            <div class="ep-event-content">';
                                $html .= '<h3 class="ep-event-title"><a class="ep-color-hover" data-event-id="'.$event->id.'" href="'.$event->url.'" target="_self">'.$event->name.'</a>
                                </h3>';
                                if(is_user_logged_in()):
                                    ob_start();
                                        do_action('event_magic_wishlist_link',$event);
                                        $wishlist = ob_get_contents();
                                    ob_end_clean();
                                    $html .= $wishlist;
                                endif;
                                if(!empty($event->description)) {
                                    $html .=  '<div class="ep-event-description">'.$event->description.'</div>';
                                }
                            $html .= '</div>';
                            $html .= do_action('event_magic_card_view_after_price', $event);
                        $html .= '</div>';

                        $html .='<div class="em-col-4 difl ep-col-table-c ep-event-meta-wrap">
                            <div class="ep-event-meta ep-color-before">'; 
                                $start_date = null; $end_date = null; $start_time = null; $end_time = null; $day = null;
                                if (em_compare_event_dates($event->id)){
                                    $day = date_i18n(get_option('date_format'),$event->start_date);
                                    $start_time = date_i18n(get_option('time_format'),$event->start_date);
                                    $end_time = date_i18n(get_option('time_format'),$event->end_date);
                                }
                                else{
                                    $start_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->start_date);
                                    $end_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->end_date);
                                }
                                if($event->all_day){
                                    $html .= '<div class="ep-list-event-date-row">
                                        <span class="material-icons em_color">date_range</span> 
                                        <div class="ep-list-event-date">'.
                                            date_i18n(get_option('date_format'),$event->start_date).'
                                            <span class="em-all-day"> - '. __('ALL DAY','eventprime-event-calendar-management') . '</span>
                                        </div>
                                    </div>';
                                } elseif(!empty($day)){
                                    $html .= '<div class="ep-list-event-date-row"><span class="material-icons em_color">date_range</span> <div class="ep-list-event-date">'.$day.' - '.$start_time.'  to  '.$end_time.'</div></div>';
                                }
                                else{
                                    $html .= '<div class="ep-list-event-date-row"><span class="material-icons em_color">date_range</span> <div class="ep-list-event-date">'.$start_date.' - '.$end_date.'</div></div>';
                                }
                                if(!empty($event->venue)){  
                                    $venue_service= EventM_Factory::get_service('EventM_Venue_Service');
                                    $venue= $venue_service->load_model_from_db($event->venue);
                                    if(!empty($venue->id) && !empty($venue->address)){
                                        $html .= '<div class="em-list-view-venue-details" title="'.$venue->address.'"><span class="ep-list-event-location"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zM7 9c0-2.76 2.24-5 5-5s5 2.24 5 5c0 2.88-2.88 7.19-5 9.88C9.92 16.21 7 11.85 7 9z"/><circle cx="12" cy="9" r="2.5"/></svg></span><address class="em-list-event-address"><span>'.$venue->address.'</span></address>
                                                </div>';
                                    }
                                }
                                if(!empty($event->enable_booking) && empty($event->hide_booking_status)):
                                    $sum = $this->services['event']->booked_seats($event->id);
                                    $capacity = em_event_seating_capcity($event->id);
                                    $html .= '<div class="ep-list-booking-status dbfl dbfl">
                                        <div class="kf-event-attr-value dbfl">';
                                            if ($capacity > 0):
                                                $html .= '<div class="dbfl">'.$sum .'/'. $capacity.'</div>';
                                                $width = ($sum / $capacity) * 100;
                                                $html .= '<div class="dbfl"><div id="progressbar" class="em_progressbar dbfl"><div style="width:'.$width .'%'.'" class="em_progressbar_fill em_bg" ></div></div></div>';
                                            else:
                                                if($sum > 0){
                                                    $html .= '<div class="dbfl">' . $sum . ' '.__('Attending','eventprime-event-calendar-management').'</div>';
                                                }
                                            endif;
                                        $html .= '</div>
                                    </div>';
                                endif;

                                $custom_data_before_footer = '';
                                ob_start();
                                    do_action('event_magic_popup_custom_data_before_footer',$event);
                                    $custom_data_before_footer = ob_get_contents();
                                ob_end_clean();
                                $html .= $custom_data_before_footer;

                                $html .= ' <div class="ep-list-view-footer">
                                    <div class="em_event_price difl">';
                                        $ticket_price = $event->ticket_price;
                                        // check if show one time event fees at front enable
                                        if($event->show_fixed_event_price){
                                            if($event->fixed_event_price > 0){
                                                $ticket_price = $event->fixed_event_price;
                                            }
                                        }
                                        $html .= !empty($ticket_price) ? $currency_symbol.$ticket_price : '';
                                    $html .= '</div>';
                                    $html .= do_action('event_magic_card_view_after_price',$event);
                                    $html .= '<div class="kf-tickets-button difr">
                                        <div class="em_event_attr_box em_eventpage_register difl">';
                                            if(absint($event->custom_link_enabled) == 1):
                                                $html .= '<div class="em_header_button em_event_custom_link kf-tickets">
                                                    <a class="em_header_button kf-tickets" target="_blank" href="'.$event->url.'">';
                                                        if(!empty(em_global_settings('hide_event_custom_link')) && !is_user_logged_in()){
                                                            $html .= em_global_settings_button_title('Login to View');
                                                        }
                                                        else{
                                                            $html .= em_global_settings_button_title('Click for Details');
                                                        }
                                                    $html .= '</a>
                                                </div>';
                                            elseif($this->services['event']->is_bookable($event)): 
                                                $current_ts = em_current_time_by_timezone();
                                                if($event->status=='expired'):
                                                    $html .= '<div class="em_header_button em_event_expired kf-tickets">'.
                                                    em_global_settings_button_title('Bookings Expired').'</div>';
                                                elseif($current_ts>$event->last_booking_date):
                                                    $html .= '<div class="em_header_button em_booking-closed kf-tickets">'.
                                                    em_global_settings_button_title('Bookings Closed').'</div>';
                                                elseif($current_ts<$event->start_booking_date):
                                                    $html .= '<div class="em_header_button em_not_started kf-tickets">'.
                                                    em_global_settings_button_title('Bookings not started yet').'</div>';
                                                else:
                                                    if(!empty($booking_allowed)):
                                                        if(is_user_logged_in() || $showBookNowForGuestUsers):
                                                            $html .= '<form action="'.get_permalink($global_settings->booking_page).'" method="post" name="em_booking">';
                                                                $html .= '<button class="em_header_button em_event-booking kf-tickets em_color" name="tickets" onclick="em_event_booking('.$event->id.')" id="em_booking">'.
                                                                em_global_settings_button_title('Book Now').'</button>';
                                                                $html .= '<input type="hidden" name="event_id" value="'.$event->id.'" />';
                                                                $html .= '<input type="hidden" name="venue_id" value="'.$event->venue.'" />';
                                                            $html .= '</form>';
                                                        else:
                                                            $html .= '<a class="em_header_button em_event-booking kf-tickets em_color" target="_blank" href="'.add_query_arg('event_id',$event->id,get_permalink($global_settings->profile_page)).'">'.
                                                            em_global_settings_button_title('Book Now').'</a>';
                                                        endif;
                                                    endif;
                                                endif;
                                            elseif($event->status == 'publish'):
                                                if(isset($event->standing_capacity) && !empty($event->standing_capacity)):
                                                    $html .= '<div class="em_event_attr_box em_eventpage_register difl"><div class="em_header_button em_not_bookable kf-tickets">'.em_global_settings_button_title('All Seats Booked').'</div>
                                                    </div>';
                                                else:
                                                    $html .= '<div class="em_event_attr_box em_eventpage_register difl">
                                                        <div class="em_header_button em_not_bookable kf-tickets">'.em_global_settings_button_title('Bookings Closed').'</div>
                                                    </div>';
                                                endif;
                                            endif;
                                        $html .= '</div>';
                                    $html .= '</div>';
                                $html .= '</div>';
                                $html .= do_action('event_magic_card_view_after_footer',$event);
                            $html .= '</div>';
                        $html .= '</div>';
                    $html .= '</div>';
                $html .= '</div>';
            }
                
        }
        wp_send_json_success(array('html' => $html, 'last_month_id' => $last_month_id));
    }

    public function load_venue_events_mini_list_block(){
        $paged = event_m_get_param('page');
        $venue_id = event_m_get_param('venue_id');
        $single_type_event_limit = event_m_get_param("show");
        $args = array(
            'orderby' => em_append_meta_key('start_date'),
            'posts_per_page' => $single_type_event_limit,
            'offset'=> (int) ($paged-1) * $single_type_event_limit,
            'paged' => $paged,
        );
        $upcoming_events = $this->services['event']->upcoming_events_for_venue( $venue_id, $args );
        $posts = $upcoming_events->posts;
        $posts = apply_filters('ep_filter_front_events', $posts, $atts = array() );
        $posts = array_filter($posts, function($post){ return $post->post_status !== 'draft'; });
        $timestamp = time();
        $i = 0;
        $gs_service = EventM_Factory::get_service('EventM_Setting_Service');
        $global_settings = $gs_service->load_model_from_db();
        $showBookNowForGuestUsers = em_show_book_now_for_guest_users();
        $html = '';$recurring = 1; $column_class = '';
        if(event_m_get_param('recurring')){
            $recurring = event_m_get_param('recurring');
        }
        $section_id = '';
        if(empty($section_id)){ $ep_card_cls = 'ep-event-box-card'; } else{ $ep_card_cls = 'em_card_edt';}
        $today = em_current_time_by_timezone();
        foreach ($posts as $post) :
            $event = $this->services['event']->load_model_from_db($post->ID);
            if(empty($recurring) && isset($event->parent) && !empty($event->parent)){
                continue;
            }
            $currency_symbol = em_currency_symbol();
            $booking_allowed = 1;
            if((isset($event->parent) && !empty($event->parent)) && (isset($event->enable_recurrence_automatic_booking) && !empty($event->enable_recurrence_automatic_booking))){
                // if event is recurring and parent has automatic booking enable than not allowed
                $booking_allowed = 0;
            }
           
            $event->url = em_get_single_event_page_url( $event, $global_settings );
            $emcardEpired ='';
            if (em_is_event_expired($event->id)) {
                $emcardEpired ='emcard-expired';
            }
            $emcardDisable = '';
            if((empty($event->enable_booking) && absint($event->custom_link_enabled) == 0)){
                $emcardDisable = 'em_event_disabled';
            }
            $html .= '<div class="kf-upcoming-event-row em_block dbfl '.$emcardEpired.' '.$emcardDisable.'">
                <div class="kf-upcoming-event-thumb em-col-2 difl">';
                $html .='<a href="'.$event->url.'">';
                $thumbImage = esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png');
                if (!empty($event->cover_image_id)): ?>
                    <?php 
                    $thumbImage = wp_get_attachment_image_src($event->cover_image_id, 'large')[0];
                    if(empty($thumbImage)){
                        $thumbImage = get_the_post_thumbnail($event->id,'large');
                        if(isset($event->parent) && !empty($event->parent) && empty($thumbImage)){
                            $thumbImage = get_the_post_thumbnail($event->parent,'large');
                        }
                    }
                    $html .= '<img src="'.$thumbImage.'" alt="'.__('Event Cover Image', 'eventprime-event-calendar-management').'">';
                    else:
                    $html .= '<img src="'.esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png').'" alt="'.__('Dummy Image','eventprime-event-calendar-management').'" class="em-no-image" >';
                    endif;
                $html .= '</a>
                    </div>';
                
                $html .='<div class="kf-upcoming-event-title em-col-5 em-col-pad20 difl">';
                        $html .='<a href="'.$event->url.'">'.$event->name.'</a>';
                        if(is_user_logged_in()):
                            ob_start();
                                do_action('event_magic_wishlist_link',$event);
                                $custom_data_wishlist_link = ob_get_contents();
                            ob_end_clean();
                            $html .= $custom_data_wishlist_link;
                        endif;
                    if ($today>$event->start_date && $today<$event->end_date) {
                    $html .= '<span class="kf-live">'.__('Live','eventprime-event-calendar-management').'</span>';
                            } 
                            $html .= '<div class="kf-upcoming-event-post-date">
                            <div class="em_event_start difl em_wrap">
                                '.date_i18n(get_option('date_format').' '.get_option('time_format'), $event->start_date).'
                                <span> - </span>
                                '.date_i18n(get_option('date_format').' '.get_option('time_format'), $event->end_date).'
                            </div>
                        </div>';    
                    $html .='</div>';
                    $html .='<div class="kf-upcoming-event-booking em-col-5 em-col-pad20 difr">
                    <div class="em_header_button kf-button">';
                        if ($this->services['event']->is_bookable($event) && absint($event->custom_link_enabled) != 1): $current_ts = em_current_time_by_timezone();
                            if ($event->status=='expired'):
                            $html .='<div class="em_header_button em_not_bookable kf-tickets">'.em_global_settings_button_title('Bookings Expired').'</div>';
                            elseif ($current_ts>$event->last_booking_date):
                            $html .='<div class="em_header_button em_not_bookable kf-button">'.em_global_settings_button_title('Bookings Closed').'</div>';
                            elseif($current_ts<$event->start_booking_date): 
                                $html .='<div class="em_header_button em_not_bookable kf-button">'.em_global_settings_button_title('Bookings not started yet').'</div>';
                            else:
                                if(is_user_logged_in() || $showBookNowForGuestUsers):
                                    $html .='<form action="'.get_permalink($global_settings->booking_page).'" method="post" name="em_booking">
                                        <button class="em_header_button kf-button em_color" name="tickets" onclick="em_event_booking('.$event->id.')" class="em_header_button" id="em_booking">
                                            <i class="fa fa-ticket" aria-hidden="true"></i>
                                            '.em_global_settings_button_title('Book Now');
                                            if ($event->ticket_price > 0){
                                                $ticketPrice = $event->ticket_price;
                                                // check if show one time event fees at front enable
                                                if($event->show_fixed_event_price){
                                                    if($event->fixed_event_price > 0){
                                                        $ticketPrice = $event->fixed_event_price;
                                                    }
                                                }
                                                if ($ticketPrice > 0){
                                                    $html .= " - " . '<span class="em_event_price">' . em_price_with_position($ticketPrice, $currency_symbol) . '</span>';
                                                }
                                                $html .=  do_action('event_magic_single_event_ticket_price_after', $event, $ticketPrice);
                                            }
                                    $html .='</button>
                                        <input type="hidden" name="event_id" value="'.$event->id.'" />
                                        <input type="hidden" name="venue_id" value="'.$event->venue.'" />
                                    </form>';
                                    else:
                                        $html .='<a class="em_header_button kf-button em_color" target="_blank" href="'.add_query_arg('event_id',$event->id, get_permalink($global_settings->profile_page)).'">'.em_global_settings_button_title('Book Now').'</a>';
                                    endif;
                                endif;
                            elseif(absint($event->custom_link_enabled) != 1):
                            $html .='<div class="em_event_attr_box em_eventpage_register difl">
                                <div class="em_header_button em_not_bookable kf-button">
                                    '.em_global_settings_button_title('Bookings Closed').'
                                </div>
                            </div>';
                            endif; 
                $html .='</div>
                </div>
            </div>';       
            $i++;
        endforeach;
        wp_send_json_success($html);
    }

    public function remove_post_featured_image(){
        $event_id = event_m_get_param('event_id');
        if( !empty( $event_id ) ){
            delete_post_thumbnail( $event_id );
            update_post_meta( $event_id, 'em_cover_image_id', 0 );
            wp_send_json_success( array( 'success' => 1, "message" => esc_html__( 'Image Removed Successfully', 'eventprime-event-calendar-management' ) ) );
        }
        $error_msg = esc_html__( 'Featured Image not found.', 'eventprime-event-calendar-management' );
        wp_send_json_error( array( 'errors' => array( $error_msg ) ) );
    }

}
EventM_AJAX::get_instance();