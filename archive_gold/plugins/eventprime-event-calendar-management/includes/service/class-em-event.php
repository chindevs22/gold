<?php

if (!defined('ABSPATH')) {
    exit;
}

class EventM_Service {

    private $dao;
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->dao = new EventM_Event_DAO();
    }

    public function load_edit_page() {
        $response = new stdClass();
        $id = event_m_get_param('post_id');
        $event = $this->load_model_from_db($id);
        // datepicker format from global settings. index 1 for php
        $datepicker_format = 'm/d/Y';
        $global_datepicker_format = em_global_settings('datepicker_format');
        if(!empty($global_datepicker_format)){
            $datepicker_format_arr = explode('&', em_global_settings('datepicker_format'));
            $datepicker_format = $datepicker_format_arr[1];            
        }
        $event->start_date= !empty($event->start_date) ? em_showDateTime($event->start_date, true, $datepicker_format) : '';
        $event->end_date= !empty($event->end_date) ? em_showDateTime($event->end_date, true, $datepicker_format) : '';
        $event->start_booking_date= !empty($event->start_booking_date) ? em_showDateTime($event->start_booking_date, true, $datepicker_format) : '';
        $event->last_booking_date= !empty($event->last_booking_date) ? em_showDateTime($event->last_booking_date, true, $datepicker_format) : '';
        
        $response->post = $event->to_array();
        $response->post['venues'] = $this->get_venues_dropdown();
        $response->post['date_format'] = "mm/dd/yy";
        $response->post['cover_image_id'] = get_post_thumbnail_id($id);
        $response->post['cover_image_url'] = $this->get_event_cover_image($id);
        $response->post['images'] = $this->get_event_images($response->post['gallery_image_ids']);
        $response->post['sponser_images'] = $this->get_event_images($response->post['sponser_image_ids']);

        // Loading Currency symbol
        $global_settings = new EventM_Global_Settings_Model();
        $response->post['currency'] = em_currency_symbol();
        $response->post['status_list'] = em_array_to_options(array(
            "publish" => __('Active','eventprime-event-calendar-management'),
            "expired" => __('Unpublished','eventprime-event-calendar-management'),
            "draft" => __('Draft','eventprime-event-calendar-management')
        ));
        $response->post['performers'] = $this->get_performers_dropdown();
        $response->post['event_types'] = $this->get_types_dropdown();
        $response->post['ticket_templates'] = $this->get_ticket_dropdown();
        $response->rm_forms = $this->get_rm_forms();
        // Venue's data
        $venue_service = EventM_Factory::get_service('EventM_Venue_Service');
        $venue = $venue_service->load_model_from_db($event->venue);
        $response->post['seat_color'] = $venue->seat_color;
        $response->post['booked_seat_color'] = $venue->booked_seat_color;
        $response->post['reserved_seat_color'] = $venue->reserved_seat_color;
        $response->post['selected_seat_color'] = $venue->selected_seat_color;
        // update seat color data
        // check if event's seats object has seat color option
        $event_seat = $response->post['seats'];
        if(!empty($event_seat)){
            $updated_event_seat = array();
            foreach ($event_seat as $row_key => $row_value) {
                foreach ($row_value as $col_key => $col_value) {
                    if(is_object($col_value) && !isset($col_value->seatColor)){
                        $col_value->seatColor = '';
                        $col_value->seatBorderColor = '';
                    }
                    $term_seat_color = $venue->seat_color;
                    $term_booked_seat_color = $venue->booked_seat_color;
                    $term_reserved_seat_color = $venue->reserved_seat_color;
                    if($col_value->type == 'general' && !empty($term_seat_color)){
                        $col_value->seatColor = '#'.$term_seat_color;
                        $col_value->seatBorderColor = '3px solid #'.$term_seat_color;
                    }
                    if($col_value->type == 'sold' && !empty($term_booked_seat_color)){
                        $col_value->seatColor = '#'.$term_booked_seat_color;
                        $col_value->seatBorderColor = '3px solid #'.$term_booked_seat_color;
                    }
                    if($col_value->type == 'reserved' && !empty($term_reserved_seat_color)){
                        $col_value->seatColor = '#'.$term_reserved_seat_color;
                        $col_value->seatBorderColor = '3px solid #'.$term_reserved_seat_color;
                    }
                    $updated_event_seat[$row_key][$col_key] = $col_value;
                }
            }
            if(!empty($updated_event_seat)){
                $response->post['seats'] = $updated_event_seat;
            }
        }
        // datepicker format from global settings. index 0 for js
        $datepicker_format_js = em_global_settings('datepicker_format');
        $response->post['datepicker_format'] = (!empty($datepicker_format_js)) ? explode('&', em_global_settings('datepicker_format'))[0] : 'mm/dd/yy';
        $response->post['venue_type'] = $venue->type;
        /*getting organizers dropdown*/
        $response->post['organizers'] = $this->get_organizers_dropdown();
        if($event->organizer && is_serialized($event->organizer)){
            $response->post['organizer'] = unserialize($event->organizer);
        }

        $response = apply_filters('eventprime_load_post_response', $response);
                
        return $response;
    }
    
    public function load_frontend_event_submit_page() {
        $edit_event_id = event_m_get_param('event_id');
        $global_settings = new EventM_Global_Settings_Model();
        $type_service = EventM_Factory::get_service('EventTypeM_Service');
        $performer_service = EventM_Factory::get_service('EventM_Performer_Service');
        $venue_service = EventM_Factory::get_service('EventM_Venue_Service');
        $event_organizers = new EventM_Event_Organizer_Model();
        $response = array();
        $response['date_format'] = "mm/dd/yy";
        $response['currency'] = em_currency_symbol();
        $response['venues'] = $this->get_venues_dropdown();
        $response['performers'] = $this->get_performers_dropdown();
        foreach($response['performers'] as $id => $performer){
            if(is_string($performer->id)){
                unset($response['performers'][$id]);
            }
        }
        $response['event_types'] = $this->get_types_dropdown('front');
        $response['event_organizers'] = $this->get_organizers_dropdown();
        $response['user_submitted'] = 1;
        $response['user'] = get_current_user_id();
        $response['rm_forms'] = $this->get_rm_forms();
        $response['frontend_submission_sections'] = em_global_settings('frontend_submission_sections');
        if(empty($response['frontend_submission_sections'])){
            $response['frontend_submission_sections'] = $global_settings->frontend_submission_sections;
        }
        $response['frontend_submission_required'] = em_global_settings('frontend_submission_required');
        if(empty($response['frontend_submission_required'])){
            $response['frontend_submission_required'] = $global_settings->frontend_submission_required;
        }
        $response['age_groups_dropdown'] = $type_service->age_dropdown($age_group = array());
        $response['performer_types'] = $performer_service->get_types();
        // datepicker format from global settings. index 0 for js
        $response['datepicker_format'] = (!empty(em_global_settings('datepicker_format'))) ? explode('&', em_global_settings('datepicker_format'))[0] : "mm/dd/yy";
        //remove add new type and venue if not selected
        if(isset($response['frontend_submission_sections']) && !empty($response['frontend_submission_sections'])){
            if(!isset($response['frontend_submission_sections']->fes_new_event_type) || empty($response['frontend_submission_sections']->fes_new_event_type)){
                array_pop($response['event_types']);
            }
            if(!isset($response['frontend_submission_sections']->fes_new_event_location) || empty($response['frontend_submission_sections']->fes_new_event_location)){
                array_pop($response['venues']);
            }
        }
        $response = apply_filters('eventprime_frontend_event_submit_response', $response);
        if(!empty($edit_event_id)){
            $event_service = EventM_Factory::get_service('EventM_Service');
            $event_data = $event_service->load_model_from_db($edit_event_id);
            if(!empty($event_data)){
                $newEventData = (array) $event_data;
                $newEventData['cover_image_data'] = '';
                if($newEventData['cover_image_id']){
                    $imgUrl = wp_get_attachment_url($newEventData['cover_image_id']);
                    if(!empty($imgUrl)){
                        $newEventData['cover_image_data'] = $imgUrl;
                    }
                }
                $php_datepicker_format = (!empty(em_global_settings('datepicker_format'))) ? explode('&', em_global_settings('datepicker_format'))[1].' H:i' : "m/d/Y H:i";
                if(!empty($newEventData['start_date'])){
                    $newEventData['start_date'] = date($php_datepicker_format, $newEventData['start_date']);
                }
                if(!empty($newEventData['end_date'])){
                    $newEventData['end_date'] = date($php_datepicker_format, $newEventData['end_date']);
                }
                if(!empty($newEventData['start_booking_date'])){
                    $newEventData['start_booking_date'] = date($php_datepicker_format, $newEventData['start_booking_date']);
                }
                if(!empty($newEventData['last_booking_date'])){
                    $newEventData['last_booking_date'] = date($php_datepicker_format, $newEventData['last_booking_date']);
                }
                $response = $response + $newEventData;
            }
        }
        else{
            $response['enable_booking'] = 0;
            $response['ticket_price'] = 0.00;
            $response['all_day'] = 0;
            $response['performer'] = array();
        }
        $response['organizer_phones'] = $event_organizers->organizer_phones;
        $response['organizer_emails'] = $event_organizers->organizer_emails;
        $response['organizer_websites'] = $event_organizers->organizer_websites;
        $response['venue_types'] = $venue_service->seating_options();
        unset($response['venue_types'][0]);
        return $response;
    }

    /*
    * Load List page for REST 
    */
    public function load_list_page() {
        $gs = EventM_Factory::get_service('EventM_Setting_Service');
        $gs_model = $gs->load_model_from_db();
        $type_service = EventM_Factory::get_service('EventTypeM_Service');
        
        $response = new stdClass();                  
        $response->posts = array(); 
        $hideExpired = event_m_get_param('hideExpired');
        if ($hideExpired == true) { 
            $gs_model->hide_expired_from_admin=1;                    
        } 
        else        
        {       
            $full_load = event_m_get_param('full_load');        
            if (!$full_load) {      
                $gs_model->hide_expired_from_admin=0;       
            }       
        }
        $gs->save($gs_model);   
        $response->hideExpired = $gs_model->hide_expired_from_admin;
        $post_status = 'any';       
        if ($gs_model->hide_expired_from_admin == 1) {      
            $post_status = array('publish','draft');        
        }
        
        $args = array(      
              'posts_per_page' => EM_PAGINATION_LIMIT,      
              'offset' => ((int) event_m_get_param('paged') - 1) * EM_PAGINATION_LIMIT, 
              'orderby'=>'date', 
              'order' => 'DESC',        
              'post_type' => 'em_event',        
              'post_status' => $post_status
            );      
        
        $events = $this->dao->get_events($args);
        foreach ($events as $p) {       
            $post = new stdClass();     
            $event = $this->load_model_from_db($p->ID);     
            $post->sum = $this->booked_seats($event->id);       
            $post->id = $event->id;     
            $post->name = $event->name;
            $post->is_expired = em_is_event_expired($event->id);        
            $venue_terms = wp_get_object_terms($event->id, EM_EVENT_VENUE_TAX);     

            if (!empty($venue_terms))       
                $post->venue_name = $venue_terms[0]->name;      

            $post->capacity = em_event_seating_capcity($event->id);     
            $post->between = em_showDateTime($event->start_date,true,"m/d/Y") . " to " . em_showDateTime($event->end_date,true,"m/d/Y");        
            // Cover Image      
            $cover_image_id = get_post_thumbnail_id($event->id);        
            if (!empty($cover_image_id) && $cover_image_id > 0) {       
                $cover_image = wp_get_attachment_image_src($cover_image_id, 'large');       
                $post->cover_image_url = $cover_image[0];       
            }
            $post->user = $event->user;
            $show_edit = 0;
            // check if user have the edit permission
            if( !empty( em_check_context_user_capabilities( array( 'edit_events' ) ) ) ) {
                $show_edit = 1;
                if( empty( em_check_context_user_capabilities( array( 'edit_others_events' ) ) ) ) {
                    if( $post->user != get_current_user_id() ) {
                        $show_edit = 0;
                    }
                }
            }
            $post->show_edit = $show_edit;
            $response->posts[] = $post;     
        }
        
        $response->trans = new stdClass();              
        $response->total_posts = range(1, $this->get_total_events($args));      
        $response->event_ids = array();     
        $events = $this->dao->get_events($args);
        foreach ($events as $p) {       
            $response->event_ids[] = $p->ID;
        }

        $response->trans->manager_navs = em_manager_navs();
        $response->manager_nav = 'event';       
        $response->pagination_limit = EM_PAGINATION_LIMIT;
        
        $response->performers = EventM_Factory::get_service('EventM_Performer_Service')->get_all();
        $response->organizers = EventM_Factory::get_service('EventOrganizerM_Service')->get_organizers();
        $temp_venues= EventM_Factory::get_service('EventM_Venue_Service')->get_venues();
        $venues= array();
        foreach($temp_venues as $index=>$v){
            array_push($venues,array('id'=>$v->id,'name'=>$v->name));
        }
        $response->venues[]= array('id'=>0,'name'=>__('Select Event Site'));
        $response->venues= array_merge($response->venues,$venues);
        if(empty($venues)){
            $response->venues= array();
        }
        $types = $type_service->get_types();
        $event_types = array();
        $status_list = em_array_to_options(array(
            "publish" => __('Active','eventprime-event-calendar-management'),
            "expired" => __('Unpublished','eventprime-event-calendar-management'),
            "draft" => __('Draft','eventprime-event-calendar-management')
        ));
        $colors= array();
        if(!empty($types)){
            // Insert default value 
            $tmp = new stdClass();
            $tmp->id = 0;
            $tmp->name = __('Select Event Type', 'eventprime-event-calendar-management');
            $event_types[] = $tmp;
            foreach ($types as $type) {
                $tmp = new stdClass();
                $tmp->name = $type->name;
                $tmp->id = $type->id;
                $colors[$type->id]= $type->color;
                $event_types[] = $tmp;
            }
        }
        $response->event_types=$event_types;
        $response->colors=$colors;
        $response->status_list=$status_list;
        
        return $response;
    }
    
    public function admin_calendar_view() {
        $data = array('events' => array());
        $events = $this->get_all();
        $type_service = EventM_Factory::get_service('EventTypeM_Service');
        $curr_user = get_current_user_id();
        foreach($events as $ev){
            if( empty( em_check_context_user_capabilities( array( 'view_others_events' ) ) ) ) {
                // other user's events not load if no permission
                if( $ev->user !== $curr_user ) {
                    continue;
                }
            }
            $event = array();
            $event['id']                 = $ev->id;
            $event['title']              = htmlspecialchars_decode($ev->name);
            $event['start']              = date('c',$ev->start_date);
            $event['end']                = date('c',$ev->end_date);
            $event['enable_booking']     = (isset($ev->enable_booking) && !empty($ev->enable_booking)) ? absint($ev->enable_booking) : 0;
            $event['ticket_price']       = (isset($ev->ticket_price) && !empty($ev->ticket_price)) ? abs(floatval($ev->ticket_price)) : 0.00;
            $event['start_booking_date'] = (isset($ev->start_booking_date) && !empty($ev->start_booking_date)) ? $ev->start_booking_date : '';
            $event['last_booking_date']  = (isset($ev->last_booking_date) && !empty($ev->last_booking_date)) ? $ev->last_booking_date : '';
            $event['performer']          = $ev->performer;
            $event['venue']              = $ev->venue;
            $event['event_type']         = $ev->event_type;
            $event['all_day']            = $ev->all_day;
            $event['allDay']             = $ev->all_day;
            $event['status']             = $ev->status;
            $event['organizer']          = $ev->organizer;
            $event['cover_image_id']     = get_post_thumbnail_id( $ev->id );
            $event['cover_image_url']    = $this->get_event_cover_image( $ev->id );
            $event['popup']              = $this->admin_event_hover_popup($ev);
            if (!empty($ev->event_type)) {
                $type_model = $type_service->load_model_from_db($ev->event_type);
                $event['bg_color'] = '#' . $type_model->color;
                $event['type_text_color'] = '#' . $type_model->type_text_color;
            }
            if(!empty($ev->event_text_color)){
                $event['event_text_color'] = '#' . $ev->event_text_color;
            }
            $event = apply_filters('ep_admin_calendar_event',$event,$ev);
            array_push($data['events'],$event);
        }

        // if add or edit event permission
        if( !empty( em_check_context_user_capabilities( array( 'create_events', 'edit_events', 'edit_others_events' ) ) ) ) {
            $data['performers'] = EventM_Factory::get_service('EventM_Performer_Service')->get_all();
            $data['organizers'] = EventM_Factory::get_service('EventOrganizerM_Service')->get_organizers();
            $temp_venues = EventM_Factory::get_service('EventM_Venue_Service')->get_venues();
            $venues = array();
            foreach( $temp_venues as $index => $v ){
                array_push($venues, array('id' => $v->id, 'name' => $v->name));
            }
            $data['venues'][] = array('id' => 0,'name' => esc_html__('Select Event Site', 'eventprime-event-calendar-management'));
            $data['venues'] = array_merge($data['venues'],$venues);
            if(empty($venues)){
                $data['venues'] = array();
            }
            $types = $type_service->get_types();
            $event_types = array();
            $status_list = em_array_to_options(
                array(
                    "publish" => esc_html__('Active','eventprime-event-calendar-management'),
                    "expired" => esc_html__('Unpublished','eventprime-event-calendar-management'),
                    "draft" => esc_html__('Draft','eventprime-event-calendar-management')
                )
            );
            $colors = array();
            if(!empty($types)){
                // Insert default value 
                $tmp = new stdClass();
                $tmp->id = 0;
                $tmp->name = esc_html__('Select Event Type', 'eventprime-event-calendar-management');
                $event_types[] = $tmp;
                foreach ($types as $type) {
                    $tmp = new stdClass();
                    $tmp->name = $type->name;
                    $tmp->id = $type->id;
                    $colors[$type->id]= $type->color;
                    $event_types[] = $tmp;
                }
            }
            $data['event_types'] = $event_types;
            $data['status_list'] = $status_list;
            $data['colors'] = $colors;
        }
        wp_send_json_success($data);
    }

    public function get_total_events($args = array()) {
        if (isset($args['posts_per_page']))
            unset($args['posts_per_page']);
        $args['numberposts'] = -1;
        $posts = get_posts($args);
        $total_count = count($posts);
        return $total_count;
    }
    
    public function get_all($args= array()) {
        $posts = $this->dao->get_events($args);
        $events= array();
        foreach($posts as $post){
            $event = $this->load_model_from_db($post->ID);
            array_push($events,$event);
        }
        return $events;
    }

    public function get_event_cover_image($id, $size = 'thumbnail') {
        $cover_image_url = get_the_post_thumbnail_url($id, $size);
        if ($cover_image_url === false)
            return "";
        else
            return $cover_image_url;
    }

    public function get_event_images($image_ids) {
        //  Gallery Image Ids
        $gallery_image_ids = maybe_unserialize($image_ids);
        $images = array();

        if (!empty($gallery_image_ids) && $gallery_image_ids != "") {
            $image_ids = array_unique($gallery_image_ids);
            foreach ($image_ids as $image_id) {
                $tmp = new stdClass();
                $tmp->src = wp_get_attachment_image_src($image_id);
                $tmp->id = $image_id;
                $images[] = $tmp;
            }
        }

        return $images;
    }

    public function get_rm_forms() {
        $rm_forms = array();
        // Registration Magic Integration
        if (is_registration_magic_active()) {
            $where = array("form_type" => 1);
            $data_specifier = array('%d');
            $forms = RM_DBManager::get('FORMS', $where, $data_specifier, 'results', 0, 99999, '*', $sort_by = 'created_on', $descending = true);
            $form_dropdown_array = array();
            $form_dropdown_array[0] = __('Default EventPrime Form','eventprime-event-calendar-management');
            if ($forms)
                foreach ($forms as $form)
                    $form_dropdown_array[$form->form_id] = $form->form_name;
            $rm_forms = $form_dropdown_array;
        }
        return $rm_forms;
    }

    public function save_settings($event){
        $event->name= sanitize_text_field(event_m_get_param('name'));
        $event->description= wp_kses_post(stripslashes(event_m_get_param('description')));
        
        // Event Type handling
        $event_type = sanitize_text_field(event_m_get_param('event_type'));
        if($event_type=='new_event_type'){ // If admin chosen to add new Event Type
            $new_event_type_name = sanitize_text_field(event_m_get_param('new_event_type'));
            $new_event_type_color = sanitize_text_field(event_m_get_param('new_event_type_color'));
            $new_event_type_text_color = sanitize_text_field(event_m_get_param('new_event_type_text_color'));
            $type_service = EventM_Factory::get_service('EventTypeM_Service');
            //Creating Type model
            $type= new EventM_Event_Type_Model();
            $type->name=$new_event_type_name;
            $type->color=$new_event_type_color;
            $type->type_text_color=$new_event_type_text_color;
            $type= $type_service->save($type);
            $this->dao->set_type($event->id,$type->id);
            $event->event_type=$type->id;
        }
        else{
            $event->event_type= absint($event_type);
        }
        
        // Feature image handling
        $cover_image_id = absint(event_m_get_param('cover_image_id'));
        if (!empty($cover_image_id)) {
            $this->dao->set_thumbnail($event->id,$cover_image_id);
            $event->cover_image_id=$cover_image_id;
        }
        else{ // If cover image not uploaded then set first image from gallery
            $gallery_image_ids=event_m_get_param('gallery_image_ids');
            if (is_array($event->gallery_image_ids)) {
                if(!empty($gallery_image_ids[0])){
                    $img_id= absint($gallery_image_ids[0]);
                    $this->dao->set_thumbnail($event->id,$img_id);
                    $event->cover_image_id=$img_id;
                }
            }
        }
        
        // Gallery images handling
        $gallery_image_ids=event_m_get_param('gallery_image_ids');
        if(is_array($gallery_image_ids)){
            $event->gallery_image_ids=$gallery_image_ids;
        }
        // datepicker format from global settings. index 1 for php and 0 for js
        $datepicker_format = '';
        $datepicker_format_arr = explode('&', em_global_settings('datepicker_format'));
        if(!empty($datepicker_format_arr) && isset($datepicker_format_arr[1])){
            $datepicker_format = $datepicker_format_arr[1] . ' H:i';
        }
        // Event Dates
        $start_date= sanitize_text_field(event_m_get_param('start_date'));
        $event->start_date= !empty($start_date) ? em_timestamp($start_date) : '';
        $end_date= sanitize_text_field(event_m_get_param('end_date'));
        $event->end_date= !empty($end_date) ? em_timestamp($end_date) : '';
        $start_booking_date= sanitize_text_field(event_m_get_param('start_booking_date'));
        $event->start_booking_date= !empty($start_booking_date) ? em_timestamp($start_booking_date) : $event->start_date;
        $last_booking_date= sanitize_text_field(event_m_get_param('last_booking_date'));
        $event->last_booking_date= !empty($last_booking_date) ? em_timestamp($last_booking_date) : $event->end_date;
        
        $event->enable_booking= absint(event_m_get_param('enable_booking'));
        $event->hide_booking_status=absint(event_m_get_param('hide_booking_status'));
        $event->allow_cancellations=absint(event_m_get_param('allow_cancellations'));
        $event->enable_attendees=absint(event_m_get_param('enable_attendees'));
        $event->show_attendees=absint(event_m_get_param('show_attendees'));
        $event->rm_form= absint(event_m_get_param('rm_form'));
        $event->all_day= absint(event_m_get_param('all_day'));
        $event->hide_event_from_calendar= absint(event_m_get_param('hide_event_from_calendar'));
        $event->hide_event_from_events= absint(event_m_get_param('hide_event_from_events'));
        $event->audience_notice= wp_kses_post(stripslashes(event_m_get_param('audience_notice')));
        $event->status = event_m_get_param('status');
        $ticket_price = (float) event_m_get_param('ticket_price');
        $event->ticket_price = number_format($ticket_price, 2, '.', '');
        $event->custom_link_enabled= absint(event_m_get_param('custom_link_enabled'));
        $event->custom_link= event_m_get_param('custom_link');
        $event->event_text_color = sanitize_text_field(event_m_get_param('event_text_color'));
        $event->fixed_event_price = (float) event_m_get_param('fixed_event_price');
        $event->show_fixed_event_price = absint(event_m_get_param('show_fixed_event_price'));
        $event->show_tier_name_on_booking = absint(event_m_get_param('show_tier_name_on_booking'));
        $event->hide_end_date = absint(event_m_get_param('hide_end_date'));
        $event->slug = sanitize_text_field(event_m_get_param('slug'));
        // update event seats price if updated
        if(isset($event->seats) && !empty($event->seats)){
            foreach($event->seats as $row => $seat_data){
                foreach($seat_data as $col => $seats){
                    if($event->seats[$row][$col]->price != $ticket_price){
                        $event->seats[$row][$col]->price = $ticket_price;
                    }
                }
            }
        }
        $event_id = $this->dao->save($event);
        return $event_id;
    }
    
    public function save_performers($event){
        $performer = event_m_get_param('performer');
        if ($performer[0]=='new_performer') { // Admin chosen to add new performer
            $performer_service = EventM_Factory::get_service('EventM_Performer_Service');
            // Check if multiple performers given seperated by comma
            $performer_name= sanitize_text_field(event_m_get_param('custom_performer_name'));
            $performers = explode(',',$performer_name);
            $performer_ids = array();
            foreach ($performers as $performer_name){
                if (!empty($performer_name)){
                    $model = new EventM_Performer_Model();
                    $model->name = $performer_name;
                    $type = sanitize_text_field(event_m_get_param('custom_performer_type'));
                    $model->type= empty($type) ? 'person' : $type;
                    $performer= $performer_service->save($model);
                    // In case of any errors
                    if (!empty($performer)) {
                        $performer_ids[] = $performer->id;
                    }
                }
            }
            $event->performer=$performer_ids;
        }
        else{
            $event->performer= $performer;
        }
        $event->enable_performer= absint(event_m_get_param('enable_performer'));
        $event->match= absint(event_m_get_param('match'));
        $event_id = $this->dao->save($event);
        return $event_id;
    }
    
    public function save_social($event){
        $event->facebook_page=esc_url(event_m_get_param('facebook_page'));
        $event_id = $this->dao->save($event);
        return $event_id;
    }
    
    public function save_organizer($event){
        $event->organizer_name       = sanitize_text_field(event_m_get_param('organizer_name'));
        $event->organizer_phones     = event_m_get_param('organizer_phones');
        foreach($event->organizer_phones as $key => $val) {
            $event->organizer_phones[$key] = sanitize_text_field($val);
        }
        $event->organizer_emails     = event_m_get_param('organizer_emails');
        foreach($event->organizer_emails as $key => $val) {
            $event->organizer_emails[$key] = sanitize_text_field($val);
        }
        $event->organizer_websites   = event_m_get_param('organizer_websites');
        foreach($event->organizer_websites as $key => $val) {
            $event->organizer_websites[$key] = esc_url($val);
        }
        $event->hide_organizer       = absint(event_m_get_param('hide_organizer'));
        $event->organizer            = event_m_get_param('organizer');
        $event_id                    = $this->dao->save($event);
        return $event_id;
    }
    
    public function save_venue($event){
        $venue = sanitize_text_field(event_m_get_param('venue'));
        $venue_service = EventM_Factory::get_service('EventM_Venue_Service');
        $event->seating_capacity = absint(event_m_get_param('seating_capacity'));
        if ($venue == 'new_venue') { // If admin chosen to add new Venue
            $model = new EventM_Venue_Model();
            $model->name = sanitize_text_field(event_m_get_param('new_venue'));;
            $model->type = 'standings';
            $model->address = sanitize_text_field(event_m_get_param('new_venue_address'));
            $model->seating_capacity = 0;
            $model->standing_capacity = absint( event_m_get_param( 'standing_capacity' ) );;
            $venue = $venue_service->save($model);
            if (!empty($event->venue))
               $this->dao->remove_venue($event->id, $event->venue);
            $this->dao->set_venue($event->id, $venue->id);
            $event->venue = $venue->id;
        }else {
            // Save Venue info
            $venue = absint($venue);
            if (empty($venue)) {
                // Getting old venue
                if(!empty($event->venue)){
                    $this->dao->remove_venue($event->id,$event->venue);
                }
            }
            else {
                
                if($event->venue!=$venue){ // Venue changed.
                    $this->dao->set_venue($event->id, $venue);
                    $venue_model= $venue_service->load_model_from_db($venue);
                    if($venue_model->type!='standings'){
                        $event->seating_capacity=$venue_model->seating_capacity;
                        $event->seats=$venue_model->seats;
                    }
                    $event->venue=$venue;
                }
            }
        }
        $event->standing_capacity = absint( event_m_get_param( 'standing_capacity' ) );
        $event_id = $this->dao->save($event);
        return $event_id;
    }
    
    public function save_user_submitted_event(){
        // first check if edit event
        $event_id = sanitize_text_field(event_m_get_param('event_id'));
        if(!empty($event_id)){
            $event_service = EventM_Factory::get_service('EventM_Service');
            $event = $event_service->load_model_from_db($event_id);
        }
        else{
            // Loading model
            $event = new EventM_Event_Model(0);
        }
        
        $setting_service = EventM_Factory::get_service('EventM_Setting_Service');
        $gs = $setting_service->load_model_from_db();
        $event->name = sanitize_text_field(event_m_get_param('name'));
        $event->event_text_color = sanitize_text_field(event_m_get_param('event_text_color'));
        $event->description = wp_kses_post(stripslashes(event_m_get_param('description')));
        $event->event_type = absint(event_m_get_param('event_type'));
        // datepicker format from global settings
        $datepicker_format = '';
        $datepicker_format_arr = explode('&', em_global_settings('datepicker_format'));
        if(!empty($datepicker_format_arr)){
            $datepicker_format = $datepicker_format_arr[1] . ' H:i';
        }
        // Event Dates
        $start_date = sanitize_text_field(event_m_get_param('start_date'));
        $event->start_date = !empty($start_date) ? em_timestamp($start_date) : '';
        $end_date = sanitize_text_field(event_m_get_param('end_date'));
        $event->end_date = !empty($end_date) ? em_timestamp($end_date) : '';
        $start_booking_date = sanitize_text_field(event_m_get_param('start_booking_date'));
        $event->start_booking_date = !empty($start_booking_date) ? em_timestamp($start_booking_date) : $event->start_date;
        $last_booking_date = sanitize_text_field(event_m_get_param('last_booking_date'));
        $event->last_booking_date = !empty($last_booking_date) ? em_timestamp($last_booking_date) : $event->end_date;
        $event->enable_booking = absint(event_m_get_param('enable_booking'));
        //$event->rm_form = absint(event_m_get_param('rm_form'));
        $event->all_day = absint(event_m_get_param('all_day'));
        $event->audience_notice = wp_kses_post(stripslashes(event_m_get_param('audience_notice')));
        $event->status = $gs->ues_default_status;
        $event->ticket_price = (float) event_m_get_param('ticket_price');
        $event->fixed_event_price = (float) event_m_get_param('fixed_event_price');
        $event->show_fixed_event_price = absint(event_m_get_param('show_fixed_event_price'));
        $event->custom_link_enabled = absint(event_m_get_param('custom_link_enabled'));
        $event->custom_link = '';
        if($event->custom_link_enabled == 1){
            $event->custom_link = esc_url(event_m_get_param('custom_link'));
        }
        $event->user_submitted = absint(event_m_get_param('user_submitted'));
        $event->facebook_page = esc_url(event_m_get_param('facebook_page'));
        $event->hide_event_from_calendar = absint(event_m_get_param('hide_event_from_calendar'));
        $event->hide_event_from_events = absint(event_m_get_param('hide_event_from_events'));
        $event->hide_organizer = absint(event_m_get_param('new_organizer_hide_organizer'));
        $event->event_type = event_m_get_param('event_type');
        if($event->event_type == 'new_event_type'){
            $type_dao = new EventM_Event_Type_DAO();
            $new_event_type_name = sanitize_text_field(event_m_get_param('new_event_type_name'));
            $new_event_type_background_color = sanitize_text_field(event_m_get_param('new_event_type_background_color'));
            $new_event_type_text_color = sanitize_text_field(event_m_get_param('new_event_type_text_color'));
            $new_event_type_age_group = event_m_get_param('new_event_type_age_group');
            if($new_event_type_age_group == "custom_group"){
                $new_event_type_custom_group = event_m_get_param('new_event_type_custom_group');
            }
            $new_event_type_description = wp_kses_post(stripslashes(event_m_get_param('new_event_type_description')));
            if (!empty($new_event_type_name)) {
                $event_type_service = EventM_Factory::get_service('EventTypeM_Service');
                $event_type = $event_type_service->map_request_to_model(0, array(
                    'name' => $new_event_type_name,
                    'color' => $new_event_type_background_color,
                    'type_text_color' => $new_event_type_text_color,
                    'age_group' => $new_event_type_age_group,
                    'custom_group' => $new_event_type_custom_group,
                    'description' => $new_event_type_description
                ));
                $new_event_type_data = $type_dao->save($event_type);
                $event->event_type = $new_event_type_data->id;
            }
        }
        $event->performer = event_m_get_param('performer');
        if(!empty($event->performer)){
            $event->enable_performer = 1;
        }
        else{
            $performer_dao = new EventM_Performer_DAO();
            $new_performer_name = sanitize_text_field(event_m_get_param('new_performer_name'));
            if (!empty($new_performer_name)) {
                // Check if multiple performers given seperated by comma
                $performers = explode(',', $new_performer_name);
                $performer_ids = array();
                foreach ($performers as $performer_name){
                    if (!empty($performer_name)){
                        $performer = new EventM_Performer_Model();
                        $performer->name = $performer_name;
                        $type = event_m_get_param('new_performer_type');
                        $performer->type = empty($type) ? 'person': $type;
                        $role = sanitize_text_field(event_m_get_param('new_performer_role'));
                        $performer->role = empty($role) ? 'person': $role;
                        $performer->feature_image_id = !empty( event_m_get_param('performer_image_id') ) ? event_m_get_param('performer_image_id') : null;
                        $performer->description = wp_kses_post(stripslashes(event_m_get_param('new_performer_description')));
                        $performer->slug = '';
                        $performer= $performer_dao->save($performer);
                        // In case of any errors
                        if (!empty($performer)) {
                            $performer_ids[] = $performer->id;
                            $performer_image_id = !empty( event_m_get_param('performer_image_id') ) ? event_m_get_param('performer_image_id') : null;
                            
                            if( ! empty( $performer_image_id ) ){
                                $performer_service = EventM_Factory::get_service('EventM_Performer_Service');
                                $performer_service->set_image($performer->id,$performer_image_id);
                            }
                        }
                    }
                }
                $event->performer = $performer_ids;
                $event->enable_performer = 1;
            }
        }
        // If admin chosen to add new Venue
        $new_venue = sanitize_text_field(event_m_get_param('new_venue'));
        $venue_service = EventM_Factory::get_service('EventM_Venue_Service');
        if (!empty($new_venue)) {
            $address = sanitize_text_field(event_m_get_param('new_venue_address'));
            $lat = sanitize_text_field(event_m_get_param('lat'));
            $lng = sanitize_text_field(event_m_get_param('lng'));
            $zoom_level = sanitize_text_field(event_m_get_param('zoom_level'));
            $seating_type = event_m_get_param('seating_type');
            if($seating_type == 'standings'){
                $standing_capacity = event_m_get_param('standing_capacity');
                $venue = $venue_service->map_request_to_model(0, array('name' => $new_venue, 'type' => $seating_type, 'address' => $address, 'standing_capacity' => $standing_capacity, 'lat' => $lat, 'lng' => $lng, 'zoom_level' => $zoom_level));
            }
            else{
                $seating_capacity = event_m_get_param('seating_capacity');
                $seat_color = event_m_get_param('seat_color');
                $booked_seat_color = event_m_get_param('booked_seat_color');
                $reserved_seat_color = event_m_get_param('reserved_seat_color');
                $selected_seat_color = event_m_get_param('selected_seat_color');
                $seats = event_m_get_param('seats');
                $venue = $venue_service->map_request_to_model(0, array('name' => $new_venue, 'type' => $seating_type, 'address' => $address, 'seating_capacity' => $seating_capacity, 'seat_color' => $seat_color, 'booked_seat_color' => $booked_seat_color, 'reserved_seat_color' => $reserved_seat_color, 'selected_seat_color' => $selected_seat_color, 'seats' => $seats, 'lat' => $lat, 'lng' => $lng, 'zoom_level' => $zoom_level));
            }
            $venue_dao = new EventM_Venue_DAO();
            $venue = $venue_dao->save($venue);

            // updating event standings from new_venue 
            $venue_model= $venue_service->load_model_from_db($venue->id);


            $event->standing_capacity = $venue_model->standing_capacity;
        } else {
            $event->venue = absint(event_m_get_param('venue'));
            $venue_model= $venue_service->load_model_from_db($event->venue);
            if($venue_model->type != 'standings' && (empty($event->seating_capacity) ||$event->seating_capacity > $venue_model->seating_capacity)){
                $event->seating_capacity = $venue_model->seating_capacity;
                $event->seats = $venue_model->seats;
            }
            if($venue_model->type !='seats' && (empty($event->standing_capacity) || $event->standing_capacity > $venue_model->standing_capacity)){
                $event->standing_capacity = $venue_model->standing_capacity;
            }
        }
        // set feature image
        $cover_image_id = absint(event_m_get_param('attachment_id'));
        if (!empty($cover_image_id)) {
            $event->cover_image_id = $cover_image_id;
        }else{
            $event->cover_image_id = '';
        }

        /* Organizer Details */
        $event->organizer = event_m_get_param('organizer');
        $new_organizer_name = event_m_get_param('new_organizer_name');
        if(!empty($new_organizer_name)){
            $organizer_dao = new EventM_Event_Organizer_DAO();
            $new_organizer_name = sanitize_text_field(event_m_get_param('new_organizer_name'));
            $organizer_phones = event_m_get_param('organizer_phones');
            $organizer_emails = event_m_get_param('organizer_emails');
            $organizer_websites = event_m_get_param('organizer_websites');
            foreach($organizer_websites as $key => $val) { $organizer_websites[$key] = esc_url($val); }
            // set organizer image
            $organizer_image_id = event_m_get_param('organizer_image_id');
            if (!empty($organizer_image_id)) { $organizer_image_id = $organizer_image_id;
            }else{ 
                $organizer_image_id = null;
            }
            $new_event_organizer_description = wp_kses_post(stripslashes(event_m_get_param('new_event_organizer_description')));
            if (!empty($new_organizer_name)) {
                $event_organizer_service = EventM_Factory::get_service('EventOrganizerM_Service');
                $event_organizer = $event_organizer_service->map_request_to_model(0, array(
                    'name' => $new_organizer_name,
                    'organizer_phones' => array_filter($organizer_phones),
                    'organizer_emails' => array_filter($organizer_emails),
                    'organizer_websites' => array_filter($organizer_websites),
                    'image_id' => $organizer_image_id,
                    'description' => $new_event_organizer_description
                ));
                $new_event_organizer_data = $organizer_dao->save($event_organizer);
                if (!empty($new_event_organizer_data)) {
                    $organizer_ids[] = $new_event_organizer_data->id;
                }
                $event->organizer = $organizer_ids;
                $event->hide_organizer = absint(event_m_get_param('new_organizer_hide_organizer'));
            }
        }
        $event_id = $this->dao->save($event);
        // set vanue to event
        if(!empty($venue->id)){
            $this->dao->set_venue($event_id, $venue->id);
            $this->dao->set_meta($event_id, 'venue', $venue->id);
        }
        // cover image
        if (!empty($cover_image_id)) {
            $this->dao->set_thumbnail($event_id, $cover_image_id);
        }
        if(!empty($new_event_type_data)){
            $this->dao->set_type($events_id, $new_event_type_data->id);
            $this->dao->set_meta($events_id, 'event_type', $new_event_type_data->id);
        }
        if( empty( $event_id ) ){
            do_action( 'event_magic_send_fes_sms', $event_id );
        }
        return $event_id;
    }
    
    public function save_popup_event($event){
        $old_event= clone $event;
        $event->all_day=absint(event_m_get_param('all_day'));
        $event->enable_booking=absint(event_m_get_param('enable_booking'));
        // datepicker format from global settings
        $datepicker_format = '';
        $datepicker_format_arr = explode('&', em_global_settings('datepicker_format'));
        if(!empty($datepicker_format_arr) && isset($datepicker_format_arr[1])){
            $datepicker_format = $datepicker_format_arr[1] . ' H:i';
        }
        $start_date= sanitize_text_field(event_m_get_param('start_date'));
        $event->start_date= !empty($start_date) ? em_timestamp($start_date) : '';
        $end_date= sanitize_text_field(event_m_get_param('end_date'));
        $event->end_date= !empty($end_date) ? em_timestamp($end_date) : '';
        // Setting booking dates automatically if bookings are enabled
        if ($event->enable_booking === 1) {
            if (!isset($event->start_booking_date) || $event->start_booking_date == '') {
                $event->start_booking_date= em_get_local_timestamp();
                $event->last_booking_date= $event->start_date;
            } else {
                $event->start_booking_date= sanitize_text_field(event_m_get_param('start_booking_date'));
                $event->last_booking_date= sanitize_text_field(event_m_get_param('last_booking_date'));
            }
        } else {
            $event->start_booking_date= '';
            $event->last_booking_date= '';
        }

        $ticket_price = event_m_get_param('ticket_price');
        $ticket_price = (empty($ticket_price)) ? 0 : number_format($ticket_price, 2, '.', '');
        $event->event_type = absint(event_m_get_param('event_type'));
        $venue = absint(event_m_get_param('venue'));
         // update event seat price data
        if(!empty($event->seats) && $event->venue == $venue){
            foreach($event->seats as $row => $seat_data){
                foreach($seat_data as $col => $seats){
                    if($event->seats[$row][$col]->price != $ticket_price){
                        $event->seats[$row][$col]->price = $ticket_price;
                    }
                }
            }
        }
        // Save Venue info
        if (empty($venue)) {
            // Getting old venue
            if(!empty($event->venue)){
                $this->dao->remove_venue($event->id,$event->venue);
            }
        }
        else {
            if($event->venue != $venue){ // Venue changed.
                $venue_service = EventM_Factory::get_service('EventM_Venue_Service');
                $venue_model= $venue_service->load_model_from_db($venue);
                $this->dao->set_venue($event->id, $venue);
                if($venue_model->type != 'standings' && (empty($event->seating_capacity) || $event->seating_capacity > $venue_model->seating_capacity)){
                    $event->seating_capacity = $venue_model->seating_capacity;
                    $venue_seats = $venue_model->seats;
                    if(!empty($venue_seats)){
                        foreach($venue_seats as $row => $seat_data){
                            foreach($seat_data as $col => $seats){
                                if($venue_seats[$row][$col]->price != $ticket_price){
                                    $venue_seats[$row][$col]->price = $ticket_price;
                                }
                            }
                        }
                    }
                    $event->seats = $venue_seats;
                }
                if($venue_model->type !='seats' && (empty($event->standing_capacity) || $event->standing_capacity > $venue_model->standing_capacity)){
                    $event->standing_capacity = $venue_model->standing_capacity;
                    //$event->standings = $venue_model->standings;
                }
            }
        }
        $event->ticket_price = $ticket_price;
        $event->venue = $venue;
        $event->performer = event_m_get_param('performer');     
        $event->name = htmlspecialchars_decode(sanitize_text_field(strip_tags(event_m_get_param('title'))));
        $event->enable_performer = absint(event_m_get_param('performer'));
        $event->status = event_m_get_param('status');
        $event->organizer = event_m_get_param('organizer');
        // Feature image handling
        $cover_image_id = absint(event_m_get_param('cover_image_id'));
        if ( ! empty( $cover_image_id ) ) {
            $event->cover_image_id = $cover_image_id;
        }
        $event = apply_filters('ep_before_saving_popup_event', $event);
        $event_id = $this->dao->save($event);
        // Thumbnail image handling
        if ( ! empty( $cover_image_id ) && ! empty( $event_id ) ) {
            $this->dao->set_thumbnail( $event_id, $cover_image_id );
        }
        // add price in price option table
        $this->set_multi_price_option($event, $event_id);
        $event->old_data = $old_event;
        do_action('ep_popup_event_saved',$event,$event_id);
        return $event_id;
    }
   
    public function save($model) {
        $id = isset($model->id) ? $model->id : 0;
        $model = $this->map_request_to_model($id, $model);
        // If admin chosen to add new Performer(s)
        $performer_dao = new EventM_Performer_DAO();
        $custom_performer_name = event_m_get_param('custom_performer_name');
        if (!empty($custom_performer_name)) {
            // Check if multiple performers given seperated by comma
            $performers = explode(',', $custom_performer_name);
            $performer_ids = array();
            foreach ($performers as $performer_name){
                if (!empty($performer_name)){
                    $performer = new EventM_Performer_Model();
                    $performer->name = $performer_name;
                    $type = event_m_get_param('custom_performer_type');
                    $performer->type= empty($type) ? 'person': $type;
                    $slug = event_m_get_param('custom_performer_slug');
                    $performer->slug= empty($slug) ? '': $slug;
                    $performer= $performer_dao->save($performer);
                    // In case of any errors
                    if (!empty($performer)) {
                        $performer_ids[] = $performer->id;
                    }
                }
            }
            $model->performer=$performer_ids;
        }
        //Booking dates
        $model->start_booking_date= empty($model->start_booking_date) ? $model->start_date : $model->start_booking_date;
        $model->last_booking_date= empty($model->last_booking_date) ? $model->end_date : $model->last_booking_date;
        
        $event_id = $this->dao->save($model);
        // In case of any errors
        if ($event_id instanceof WP_Error) {
            return $event_id;
        }
        $event = $this->load_model_from_db($event_id);
        
        // If admin chosen to add new Event Type
        $type_dao = new EventM_Event_Type_DAO();
        $new_event_type = event_m_get_param('new_event_type');
        $new_event_type_color = event_m_get_param('new_event_type_color');
        $new_event_type_text_color = event_m_get_param('new_event_type_text_color');
        if (!empty($new_event_type)) {
            $event_type_service = EventM_Factory::get_service('EventTypeM_Service');
            $event_type = $event_type_service->map_request_to_model(0, array('name' => $new_event_type, 'color' => $new_event_type_color, "type_text_color" => $new_event_type_text_color));
            $type = $type_dao->save($event_type);
            $this->dao->set_type($event->id, $type->id);
            $this->dao->set_meta($event->id, 'event_type', $type->id);
        } else {
            $type = $model->event_type;
            if ($type>0){
                $this->dao->set_type($event->id, $type);
            }
                
        }

        // If admin chosen to add new Venue
        $new_venue = event_m_get_param('new_venue');
        $venue_service = EventM_Factory::get_service('EventM_Venue_Service');
        if (!empty($new_venue)) {
            $address = event_m_get_param('new_venue_address');
            $seating_capacity = event_m_get_param('new_venue_capacity');
            $venue = $venue_service->map_request_to_model(0, array('name' => $new_venue, 'type' => 'standings', 'address' => $address, 'seating_capacity' => $seating_capacity));
            $venue_dao = new EventM_Venue_DAO();
            $venue = $venue_dao->save($venue);
            if (!empty($model->venue))
               $this->dao->remove_venue($event->id, $model->venue);
            $this->dao->set_venue($event->id, $venue->id);
            $this->dao->set_meta($event->id, 'venue', $venue->id);
        } else {
            // Save Venue info
            $venue = absint(event_m_get_param('venue'));
            if (empty($venue)) {
                    // Getting old venue
                    $old_venue= $this->dao->get_venue($event->id);
                    if(!empty($old_venue)){
                        $this->dao->remove_venue($event->id,$old_venue->term_id);
                    }
            }
            else {
                $this->dao->set_venue($event->id, $venue);
                $venue_model= $venue_service->load_model_from_db($venue);
                if($venue_model->type!='standings' && $event->seating_capacity>$venue_model->seating_capacity){
                    $event->seating_capacity=$venue_model->seating_capacity;
                    $event->seats=$venue_model->seats;
                }
                
                $this->update_model($event);
            }
        }

        // Set Feature image
        $cover_image_id = $event->cover_image_id;
        if ($cover_image_id != null && (int) $cover_image_id > 0) {
            $this->dao->set_thumbnail($event->id, $cover_image_id);
        }

        // If cover image not uploaded then set first image from gallery
        if ($cover_image_id == null || (int) $cover_image_id == 0) {
            if (is_array($event->gallery_image_ids) && !empty($event->gallery_image_ids)) {
                $this->dao->set_thumbnail($event->id, $gallery_image_ids[0]);
            }
        }

        do_action('event_magic_event_saved', $event);
        return $event;
    }
    
    private function update_meta_before_save($meta, $model) {

        if (is_array($meta)) {
            foreach ($meta as $value) {
                if ($value == "allow_discount" && 0 == (int) $model->$value) {
                    $index = array_search('discount_no_tickets', $meta);
                    unset($meta[$index]);

                    $index = array_search('discount_per', $meta);
                    unset($meta[$index]);
                }
            }
        }

        return $meta;
    }

    public function get_venue_capcity($venue_id, $event_id) {
        $response = array();
        $service = EventM_Factory::get_service('EventM_Venue_Service');
        $response['capacity'] = (int) $service->capacity($venue_id);
        $response['seats'] = $service->get_seats($venue_id, $event_id);

        return $response;
    }

    public function validate($model) {
        $errors= array();
        if ($model->venue == 'new_venue')
        {
            $new_venue= event_m_get_param('new_venue',true);
            if(term_exists($new_venue, EM_EVENT_VENUE_TAX)){
                $errors[]= __('Please use different Venue name','eventprime-event-calendar-management');
            }
        }
        
        if ($model->event_type == 'new_event_type')
        {
            $new_event_type= event_m_get_param('new_event_type',true);
            if(term_exists($new_event_type, EM_EVENT_TYPE_TAX)){
                $errors[]= __('Please use different Event Type','eventprime-event-calendar-management');
            }
 
        }
        
        if(!empty($model->enable_booking)){
            $start_date = em_timestamp($model->start_date);
            $end_date = em_timestamp($model->end_date);
            $last_booking_date = em_timestamp($model->last_booking_datee);
            $start_booking_date =  em_timestamp($model->start_booking_date);
            
            if ($start_date > $end_date) {
                $errors[]= __('Event Start date should be prior to Event End date.','eventprime-event-calendar-management');
            }

            if ($last_booking_date> $end_date) {
                $errors[]=__('Last booking date can not be greater than End date.','eventprime-event-calendar-management');
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
        $errors= apply_filters('event_magic_event_save_validation',$errors,$model);
        return $errors;
    }

    public function get_events_the_query($custom_args=array(),$shortcode_params=array()) {
        $setting_service = EventM_Factory::get_service('EventM_Setting_Service');
        $gs = $setting_service->load_model_from_db();
        $hide_past_events = $gs->hide_past_events;
        $type_ids = $shortcode_params['types'];
        $venue_ids = $shortcode_params['sites'];
        $viewtype = $shortcode_params['view'];
        $upcoming = (isset($shortcode_params['upcoming']) && $shortcode_params['upcoming'] != '') ? $shortcode_params['upcoming'] : '';
        $posts_per_page = 10;
        if($viewtype == 'card' || (isset($_GET['events_view']) && $_GET['events_view'] == 'card')){
            $show_no_of_events_card = em_global_settings('show_no_of_events_card');
            $posts_per_page = $show_no_of_events_card;
            if($show_no_of_events_card == 'custom'){
                $posts_per_page = em_global_settings('card_view_custom_value');
            }
            if($show_no_of_events_card == 'all'){
                $posts_per_page = -1;
            }
            if(isset($shortcode_params['show']) && !empty($shortcode_params['show'])){
                $posts_per_page = $shortcode_params['show'];
            }
        }
        $paged = (get_query_var('paged') ) ? get_query_var('paged') : 1;

        $meta_query= array(
            'relation' => 'AND',
            array(
                'key' => em_append_meta_key('hide_event_from_events'),
                'value' => '1',
                'compare' => '!='
            )
        );
        $search = event_m_get_param('em_s');
        $args = array(
            'meta_key' => em_append_meta_key('start_date'),
            'orderby' => 'meta_value',
            'order' => 'ASC',
            'posts_per_page' => $posts_per_page,
            'paged' => $paged,
            'meta_query' => $meta_query,
            'post_type' => EM_EVENT_POST_TYPE
        );
        $args['post_status'] = !empty($hide_past_events) == 1 ? 'publish' : 'any';
        
        if ($search == "1"){
            $sd = event_m_get_param('em_sd');
            // datepicker format from global settings
            $datepicker_format_arr = em_global_settings('datepicker_format');
            $date_format = (!empty($datepicker_format_arr)) ? explode('&', em_global_settings('datepicker_format'))[1] : '!Y-m-d';
            $start_date= DateTime::createFromFormat($date_format, $sd);
            if(!empty($start_date)){
                $start_date->setTime(23,59,59);
                $start_ts= $start_date->getTimestamp();
                $start_date->setTime(0,0);
                $end_ts=$start_date->getTimestamp();
                array_push($meta_query,array(
                    'key' => em_append_meta_key('start_date'),
                    'value' => $start_ts,
                    'compare' => '<=',
                    'type'=>'NUMERIC'
                ),
                array(
                    'key' => em_append_meta_key('end_date'),
                    'value' => $end_ts,
                    'compare' => '>=',
                    'type'=>'NUMERIC'
                ));
            }
            
            $keyword = event_m_get_param('em_search');
            if (!empty($keyword)){
                $args['s']= $keyword;
            }
            
            $types = event_m_get_param('em_types');
            if (!empty($types)) {
                $types = is_array($types) ? $types : array($types);
                if(!empty($type_ids))
                    $type_ids = array_intersect($types,$type_ids);
                else
                    $type_ids = $types;
            }
            
            $venue = event_m_get_param('em_venue');
            if (!empty($venue)) {
                $venue = is_array($venue) ? $venue : array($venue);
                if(!empty($venue_ids))
                    $venue_ids = array_intersect($venue,$venue_ids);
                else
                    $venue_ids = $venue;
            }
        }
        
        if (!empty($type_ids)) {
            array_push($meta_query,array(
                'key' => em_append_meta_key('event_type'),
                'value' => $type_ids,
                'compare' => 'IN',
                'type'=>'NUMERIC'
            ));
        }
        
        if (!empty($venue_ids)) {
            array_push($meta_query,array(
                'key' => em_append_meta_key('venue'),
                'value' => $venue_ids,
                'compare' => 'IN',
                'type'=>'NUMERIC'
            ));
        }

        /* if(isset($shortcode_params['upcoming']) && $shortcode_params['upcoming'] != '' && (!isset($shortcode_params['individual_events']) || empty($shortcode_params['individual_events']))){ */
        if(isset($upcoming) && $upcoming != '' && (!isset($shortcode_params['individual_events']) || empty($shortcode_params['individual_events']))){
            if( $upcoming == 1 ) {
                array_push($meta_query,array(
                    'key' => em_append_meta_key( 'start_date' ),
                    'value' => current_time( 'timestamp' ),
                    'compare' => '>='
                ));
            }
            if( $upcoming == 0 ) {
                array_push($meta_query,array(
                    'key' => em_append_meta_key( 'end_date' ),
                    'value' => current_time( 'timestamp' ),
                    'compare' => '<='
                ));
            }
        }
        /* individual events shortcode arguements - yesterday, today, tomorrow, month */
        if( isset( $shortcode_params['individual_events'] ) && $shortcode_params['individual_events'] != '' ){
        $meta_query = $this->individual_events_shortcode_argument( $meta_query, $shortcode_params['individual_events'] );
        }

        if(!empty($meta_query)){
            $args['meta_query']= $meta_query;
        }
        
        $args = wp_parse_args($custom_args, $args);
        // if card view then shows only publish posts
        if(($viewtype == 'card' || (isset($_GET['events_view']) && $_GET['events_view'] == 'card')) && $posts_per_page != -1){
            $args['post_status'] = 'publish';
        }
        // post filter
        add_filter('posts_orderby', 'em_posts_order_by');

        // check for future entries
        $sort_type = isset($_REQUEST['sort_type']) ? sanitize_text_field($_REQUEST['sort_type']) : '';
        if(!empty($sort_type) && $sort_type == 'newer'){
            $args['post_status'] = 'publish';
        }
        // check for past entries
        if(!empty($sort_type) && $sort_type == 'older'){
            $args['post_status'] = 'expired';
            $args['paged'] = $paged - 1;
            $args['order'] = 'DESC';
            add_filter('posts_orderby', 'em_older_posts_order_by');
        }

        $wp_query = new WP_Query($args);
        remove_filter('posts_orderby', 'em_posts_order_by');

        if(!empty($sort_type) && $sort_type == 'older'){
            remove_filter('posts_orderby', 'em_older_posts_order_by');
        }
        // on card view if no current and future post then show past post on first page
        if(($viewtype == 'card' || (isset($_GET['events_view']) && $_GET['events_view'] == 'card')) && $posts_per_page != -1){
            if(count($wp_query->posts) == 0){
                $args['post_status'] = !empty($hide_past_events) == 1 ? 'publish' : 'any';
                $wp_query = new WP_Query($args);
            }
        }
        // check for newer entries exists
        $newer_args = $args;
        $newer_args['paged'] = $paged + 1;
        $newer_args['post_status'] = 'publish';
        add_filter('posts_orderby', 'em_posts_order_by');
        $next_page_query = new WP_Query($newer_args);
        remove_filter('posts_orderby', 'em_posts_order_by');
        if(isset($next_page_query->posts) && count($next_page_query->posts) > 0 && $posts_per_page != -1){
            $wp_query->next_page_exist = 1;
        }

        // check for older entries exists
        $older_args = $args;
        $older_args['paged'] = $paged;
        $older_args['post_status'] = 'expired';
        add_filter('posts_orderby', 'em_older_posts_order_by');
        $previous_page_query = new WP_Query($older_args);
        remove_filter('posts_orderby', 'em_older_posts_order_by');
        if(isset($previous_page_query->posts) && count($previous_page_query->posts) > 0 && $posts_per_page != -1 && empty($hide_past_events)){
            $wp_query->previous_page_exist = 1;
        }
        if(!empty($sort_type) && $sort_type == 'older' && $posts_per_page != -1){
            if($paged > 1){
                $wp_query->older_page_exist = 1;       
            }
        }

        return $wp_query;
    }

    public function get_upcoming_events($exclude = array()) {
        $events = $this->dao->get_upcoming_events();
        if (empty($events))
            return array();
        $temp = array();
        foreach ($events as $event) {
            $model = $this->load_model_from_db($event->ID);
            array_push($temp, $model);
        }
        return $temp;
    }

    private function get_upcoming_date($dates = array()) {
        $upcoming_date = null;

        if (!empty($dates)):
            foreach ($dates as $date):
                if (strtotime($date) >= strtotime(date('Y-m-d'))):
                    $upcoming_date = $date;
                    break;
                endif;
            endforeach;
        endif;

        return $upcoming_date;
    }

    public function get_data_for_slider() {
        $setting_service = EventM_Factory::get_service('EventM_Setting_Service');
        $global_settings= $setting_service->load_model_from_db();
        $data = new stdClass();
        $data->image_ids = array();
        $data->links = array();
        $data->ids = array();
        $args = array(
            'numberposts' => -1,
            'order' => 'ASC',
            'post_status' => 'publish',
            'meta_query' => array('relation' => 'AND', // WordPress has all the results, now, return only the events after today's date
                array(
                    'relation' => 'AND',
                    array(
                        'key' => em_append_meta_key('hide_event_from_events'),
                        'value' => '1', //
                        'compare' => '!='
                    ),
                    array(
                        'relation' => 'OR',
                        array(
                            'key' => em_append_meta_key('start_date'), 
                            'value' => current_time('timestamp'), 
                            'compare' => '>=', 
                        ),
                        array(
                            'key' => em_append_meta_key('end_date'),
                            'value' => current_time('timestamp'),
                            'compare' => '>=', 
                        )))
            ),
            'post_type' => EM_EVENT_POST_TYPE);

        $events = $this->dao->get_events($args);
        foreach ($events as $event){
            $event = $this->load_model_from_db($event->ID);
            $image_id = get_post_thumbnail_id($event->id);
            if (!empty($image_id)) {
                $data->image_ids[] = $image_id;
            } else {
                $data->image_ids[] = '';
            }
            $data->links[] = (absint($event->custom_link_enabled) == 1) ? $event->custom_link : add_query_arg('event', $event->id, get_page_link($global_settings->events_page));

            $data->ids[] = $event->id;
        }     
        return $data;
    }

    public function searchByText($text, $exclude = array()) {
        $search_query = new WP_Query();
        $events = $search_query->query('s=' . $text);
        $event_ids = array();
        // echo'<pre>'; var_dump($events); 

        foreach ($events as $key => $event):
            if (!in_array($event->ID, $exclude)):
                $event_ids[] = $event->ID;
            endif;
        endforeach;

        return $event_ids;
    }

    public function searchByType($type, $exclude = array()) {
        $event_ids = array();
        $event_service= EventM_Factory::get_service('EventM_Service');
        $events = $event_service->events_by_type($type);

        foreach ($events as $key => $event){
            if (!in_array($event->ID, $exclude)){
                $event_ids[] = $event->ID;
            }
        }


        return ($event_ids);
    }


    public function update_past_event_status() {
        $event_dao = new EventM_Event_DAO();
        $events = $event_dao->get_past_events();

        if (!empty($events)):
            foreach ($events as $event):
                wp_update_post(array('ID' => $event->ID, 'post_status' => 'expired'));
            endforeach;
        endif;
    }

    public function get_all_events() {
        $event_dao = new EventM_Event_DAO();
        return $event_dao->get_events();
    }

    public function get_post($ID) {
        $args = array(
            'author' => $ID,
            'orderby' => 'post_date',
            'order' => 'ASC',
            'post_type' => EM_EVENT_POST_TYPE
        );
        $details = get_posts($args);
        return $details;
    }

    public function get_venue($event_id) {
        $venue = $this->dao->get_venue($event_id);
        if (!empty($venue)) {
            return $venue->term_id;
        }
        return null;
    }

    public function get_performer($event_id) {
        $performer = em_get_post_meta($event_id, 'performer', true);
        return $performer;
    }

    public function get_type($event_id) {
        $type = $this->dao->get_type($event_id);
        if (!empty($type))
            return $type->term_id;
        return null;
    }

    public function get_events($filter) {
        return $this->dao->get_events($filter);
    }

    public function available_seats($event_id) {
        return $this->dao->available_seats($event_id);
    }

    public function booked_seats($event_id) {
        return (int) $this->dao->booked_seats($event_id);
    }

    public function get_header($event) {
        $local_objects = array();
        $setting_service= EventM_Factory::get_service('EventM_Setting_Service');
        $options= $setting_service->load_model_from_db();
        wp_enqueue_script('jquery-ui-tabs', array('jquery'));
        //get_header();
        wp_enqueue_script('em-single-event');
        wp_enqueue_script('jquery-colorbox');
        if (!empty($options->gcal_sharing)){
            wp_enqueue_script("em-gcal", plugin_dir_url(__DIR__) . 'templates/js/em-gcal.js', array(), EVENTPRIME_VERSION);
            wp_enqueue_script("em-google-client", "https://apis.google.com/js/client.js?onload=em_gcal_handle", array(), EVENTPRIME_VERSION);
            wp_localize_script("em-gcal", "em_local_gcal_objects", array("gc_id" => $options->google_cal_client_id, "g_api_key" => $options->google_cal_api_key));
        }

        if ($options->social_sharing && !empty($options->fb_api_key)){
            $local_objects["social_sharing"] = 1;
            $local_objects["fb_api"] = $options->fb_api_key;
        }
        
        $event_url = add_query_arg(array('event'=>$event->id),get_permalink($options->events_page));
        $local_objects["fb_event_href"] = $event_url;
        if ( !wp_script_is( 'em-single-event', 'registered' ) ) {
            wp_register_script( 'em-single-event', EM_BASE_URL . 'includes/templates/js/em-single-event.js', array( 'jquery' ), EVENTPRIME_VERSION, false );
        }
        wp_localize_script("em-single-event", "em_local_event_objects", $local_objects);
        if ( !wp_script_is( 'em-google-map', 'registered' ) ) {
            wp_register_script( 'em-google-map', EM_BASE_URL . 'includes/js/em-map.js', false, EVENTPRIME_VERSION, false );
        }
        em_localize_map_info("em-google-map");
    }

    public function load_model_from_db($id) {
        $event= $this->dao->get($id);
        $event= $this->format_model_from_db($event);
        return apply_filters('loading_event_from_db',$event);
    }

    public function get_venues_dropdown() {
        $venue_service= EventM_Factory::get_service('EventM_Venue_Service');
        $dropdown = array();
        $venues = $venue_service->get_venues();
        // Insert default value 
        $tmp = new stdClass();
        $tmp->id = '';
        $tmp->name = __('Select Site', 'eventprime-event-calendar-management');
        $dropdown[] = $tmp;

        foreach ($venues as $venue) {
            $tmp = new stdClass();
            $tmp->id = $venue->id;
            $tmp->name = $venue->name;
            $tmp->type = $venue->type;
            $tmp->seating_capacity = $venue->seating_capacity;
            $dropdown[] = $tmp;
        }

        $tmp = new stdClass();
        $tmp->name = __('Add New Site', 'eventprime-event-calendar-management');
        $tmp->id = "new_venue";
        $dropdown[] = $tmp;

        return $dropdown;
    }

    public function get_performers_dropdown() {
        $performer_dao = new EventM_Performer_DAO();
        $dropdown = array();
        $performers = $performer_dao->get_all();
        $performer_text = em_global_settings_button_title('Performer');
        $tmp = new stdClass();
        $tmp->id = '';
        $tmp->name = __("Select " . $performer_text, 'eventprime-event-calendar-management');
        //$dropdown[] = $tmp;

        if ($performers != null) {
            foreach ($performers as $performer) {
                $tmp = new stdClass();
                $tmp->name = $performer->name;
                $tmp->id = $performer->id;
                $dropdown[] = $tmp;
            }
        }

        $tmp = new stdClass();
        $tmp->name = __('New ' . $performer_text, 'eventprime-event-calendar-management');
        $tmp->id = "new_performer";
        $dropdown[] = $tmp;

        return $dropdown;
    }

    public function get_types_dropdown($path = null) {
        $type_dao = new EventM_Event_Type_DAO();
        $event_types = array();
        if(!empty($path) && $path == 'front' ){
            $types = $type_dao->get_front_all();    
        } else{
            $types = $type_dao->get_all();
        }
        

        // Insert default value 
        $tmp = new stdClass();
        $tmp->id = '';
        $tmp->name = __('Select Event Type', 'eventprime-event-calendar-management');
        $event_types[] = $tmp;

        if ($types != null) {
            foreach ($types as $type) {
                $tmp = new stdClass();
                $tmp->name = $type->name;
                $tmp->id = $type->id;
                $event_types[] = $tmp;
            }
        }

        if( !empty( em_check_context_user_capabilities( array( 'create_event_types' ) ) ) || (!empty($path) && $path == 'front' ) ) {
            $tmp = new stdClass();
            $tmp->name = __('Add New Event Type', 'eventprime-event-calendar-management');
            $tmp->id = "new_event_type";
            $event_types[] = $tmp;
        }

        return $event_types;
    }

    public function get_ticket_dropdown() {
        $dropdown = array();
        $templates = apply_filters('event_magic_ticket_templates', array());

        $tmp = new stdClass();
        $tmp->id = 0;
        $tmp->name = __('Select', 'eventprime-event-calendar-management');
        $dropdown[] = $tmp;

        if ($templates != null) {
            foreach ($templates as $template) {
                $tmp = new stdClass();
                $tmp->name = $template->post_title;
                $tmp->id = $template->ID;
                $dropdown[] = $tmp;
            }
        }

        return $dropdown;
    }

    public function map_request_to_model($id, $model = null) {
        $event = new EventM_Event_Model($id);
        $data = (array) $model;

        if (!empty($data) && is_array($data)) {
            foreach ($data as $key => $val) {
                if(property_exists($event,$key)){
                    $event->{$key}= $val;
                }
            }
        }
        return $this->format_model_to_save($event);
    }
    
    private function format_model_to_save($model){
        $model->allow_cancellations= absint($model->allow_cancellations);
        $model->allow_discount= absint($model->allow_discount);
        $model->booked_seats= absint($model->booked_seats);
        $model->cover_image_id= absint($model->cover_image_id);
        $model->discount_no_tickets= absint($model->discount_no_tickets);
        $model->discount_per= (float) $model->discount_per;
        $model->enable_booking= absint($model->enable_booking);
        $model->enable_performer= absint($model->enable_performer);
        $model->start_date= !empty($model->start_date) ? em_timestamp($model->start_date) : '';
        $model->end_date= !empty($model->end_date) ? em_timestamp($model->end_date) : '';
        $model->start_booking_date= !empty($model->start_booking_date) ? em_timestamp($model->start_booking_date) : '';
        $model->last_booking_date= !empty($model->last_booking_date) ? em_timestamp($model->last_booking_date) : '';
        $model->event_type= absint($model->event_type);
        $model->hide_booking_status= absint($model->hide_booking_status);
        $model->hide_event_from_calendar= absint($model->hide_event_from_calendar);
        $model->hide_event_from_events= absint($model->hide_event_from_events);
        $model->match= absint($model->match);
        $model->max_tickets_per_person= absint($model->max_tickets_per_person);
        $model->ticket_template= absint($model->ticket_template);
        $model->venue= absint($model->venue);
        $model->rm_form= absint($model->rm_form);
        $model->en_ticket= absint($model->en_ticket);
        $model->seating_capacity= absint($model->seating_capacity);
        $model->ticket_price= (float) $model->ticket_price;
        $model->performer= is_array($model->performer) ? $model->performer : array();
        $model->description= wp_kses_post(stripslashes($model->description));
        $model->hide_organizer= absint($model->hide_organizer);
        $model->enable_attendees= absint($model->enable_attendees);
        $model->show_attendees= absint($model->show_attendees);
        $model->fixed_event_price= (float) $model->fixed_event_price;
        $model->show_fixed_event_price = absint($model->show_fixed_event_price);
        $model->show_tier_name_on_booking = absint($model->show_tier_name_on_booking);
        $model->hide_end_date = absint($model->hide_end_date);
        $model= apply_filters('event_magic_format_model_to_save',$model);
        return $model;
    }
    
    private function format_model_from_db($model){
        $model->allow_cancellations= absint($model->allow_cancellations);
        $model->allow_discount= absint($model->allow_discount);
        $model->booked_seats= $this->booked_seats($model->id);
        $model->cover_image_id= absint($model->cover_image_id);
        $model->discount_no_tickets= absint($model->discount_no_tickets);
        $model->discount_per= (float) $model->discount_per;
        $model->enable_booking= absint($model->enable_booking);
        $model->enable_performer= absint($model->enable_performer);
        $model->event_type= absint($model->event_type);
        $model->hide_booking_status= absint($model->hide_booking_status);
        $model->hide_event_from_calendar= absint($model->hide_event_from_calendar);
        $model->hide_event_from_events= absint($model->hide_event_from_events);
        $model->hide_organizer= absint($model->hide_organizer);
        $model->match= absint($model->match);
        $model->max_tickets_per_person= absint($model->max_tickets_per_person);
        $model->ticket_template= absint($model->ticket_template);
        $model->venue= absint($model->venue);
        $model->rm_form= absint($model->rm_form);
        $model->en_ticket= absint($model->en_ticket);
        $model->seating_capacity= absint($model->seating_capacity);
        $model->ticket_price= (float) $model->ticket_price;
        $model->all_day= absint($model->all_day);
        $model->custom_link_enabled= absint($model->custom_link_enabled);
        $model->user_submitted= absint($model->user_submitted);
        $model->user= absint($model->user);
        $model->enable_attendees= absint($model->enable_attendees);
        $model->show_attendees= absint($model->show_attendees);
        $model->performer= !is_array($model->performer) ? array() : $model->performer;
        $model->fixed_event_price= (float) $model->fixed_event_price;
        $model->show_fixed_event_price = absint($model->show_fixed_event_price);
        $model->standing_capacity = absint($model->standing_capacity);
        $model->show_tier_name_on_booking = absint($model->show_tier_name_on_booking);
        $model->hide_end_date = absint($model->hide_end_date);
        // Checking for Seating extension for ticket prices
        $em= event_magic_instance(); 
        if(!in_array('seating',$em->extensions) || empty($model->en_ticket)){
            $model->max_tickets_per_person=0;
        }
        $model= apply_filters('event_magic_format_model_from_db',$model);
        return $model;
    }
    
    public function update_booked_seats($event, $no_seats) {
        // Get parent event
        $event_status = get_post_status($event);
        if ($event_status == 'publish') {
            $parent = $this->dao->get_meta($event, 'parent_event');
            if (!empty($parent)) {
                //$prev_parent_booked_seats = (int) $this->dao->get_meta($parent, 'booked_seats');
                $prev_parent_booked_seats = (int) $this->dao->booked_seats($parent);
                //$prev_child_booked_seats = (int) $this->dao->get_meta($event, 'booked_seats');
                $prev_child_booked_seats = (int) $this->dao->booked_seats($event);
                if ($prev_child_booked_seats > $no_seats) {
                    $diff_seats = $prev_child_booked_seats - $no_seats;
                    $this->dao->set_meta($parent, 'booked_seats', $prev_parent_booked_seats - $diff_seats);
                } else if ($no_seats > $prev_child_booked_seats) {
                    $diff_seats = $no_seats - $prev_child_booked_seats;
                    $this->dao->set_meta($parent, 'booked_seats', $prev_parent_booked_seats + $diff_seats);
                }
            }
            $this->dao->set_meta($event, 'booked_seats', $no_seats);
        }
    }

    /*
     * Event Settings Page 
     */

    public function load_settings_page() {
        $id = event_m_get_param('post_id');
        $event = $this->load_model_from_db($id);
        if (empty($event)) {
            $error = new WP_Error('NON_EXIST_EVENT', "Event does not exists");
            echo wp_json_encode($error);
            wp_die();
        }
        $event->event_types = $this->get_types_dropdown();
        $event->cover_image_id = get_post_thumbnail_id($id);
        $event->cover_image_url= $this->get_event_cover_image($id);
        $event->images = $this->get_event_images($response->post['gallery_image_ids']);
        $event->rm_forms = $this->get_rm_forms();
        $event->status_list = em_array_to_options(array(
            "publish" =>__('Active','eventprime-event-calendar-management'),
            "expired" => __('Unpublished','eventprime-event-calendar-management'),
            "draft" =>__('Draft','eventprime-event-calendar-management')
        ));
        return $event;
    }

    public function get_events_for_calendar_view($params) {
        $setting_service = EventM_Factory::get_service('EventM_Setting_Service');
        $global_settings = $setting_service->load_model_from_db();
        $currency_symbol = "";
        $em = event_magic_instance();
        // datepicker format from global settings
        $datepicker_format_arr = explode('&', em_global_settings('datepicker_format'));
        $date_format = (!empty($datepicker_format_arr) && isset($datepicker_format_arr[1])) ? $datepicker_format_arr[1] : get_option('date_format');
        $time_format = get_option('time_format');
        $date_time_format = $date_format . ' ' . $time_format;
        if ($global_settings->currency) {
            $all_currency_symbols = EventM_Constants::get_currency_symbol();
            $currency_symbol = $all_currency_symbols[$global_settings->currency];
        } else {
            $currency_symbol = EM_DEFAULT_CURRENCY;
        }
        $the_query = $this->get_events_the_query(array('nopaging'=>true),$params);
        $posts = $the_query->posts;
        $events = array();
        $performer_service = EventM_Factory::get_service('EventM_Performer_Service');
        $type_service = EventM_Factory::get_service('EventTypeM_Service');
        $event_organizer_service = EventM_Factory::get_service('EventOrganizerM_Service');
        if (empty($posts))
            return $events;
        
        $posts = apply_filters('ep_filter_front_events',$posts,$params);
        $posts = array_filter($posts, function($post){ return $post->post_status !== 'draft'; });
       /*  $recurring = ((!isset($params['recurring']) || $params['recurring'] === 0) ? 0 : 1); */
        $recurring = ( isset( $params['recurring'] ) && $params['recurring'] != '' ) ? $params['recurring'] : 1;
        foreach ($posts as $post) {
            $event = $this->load_model_from_db($post->ID);
            if($recurring == 0 && isset($event->parent) && !empty($event->parent)){
                continue;
            }
            $time_str = '';
            //$start_date = em_showDateTime($event->start_date, true, "m/d/Y");
            //$end_date = em_showDateTime($event->end_date, true, "m/d/Y");
            $start_date = ( !empty( $event->start_date ) ? date( 'c',$event->start_date ) : '' );
            $end_date = ( !empty( $event->end_date ) ? date( 'c',$event->end_date ) : '' );
            if (em_compare_event_dates($post->ID)) {
                //$day = date('D, M d, Y',$event->start_date);
                $day = date_i18n($date_format,$event->start_date);
                $start_time = date_i18n($time_format,$event->start_date);
                $end_time = date_i18n($time_format,$event->end_date);
                $time_str .= $day . ' ' . $start_time;
                if(empty($event->hide_end_date)) {
                    $time_str .= ' ' .__("to", 'eventprime-event-calendar-management') . ' ' . $end_time;
                }
            } 
            else {
                $time_str .= date_i18n($date_time_format,$event->start_date);
                if(empty($event->hide_end_date)) {
                    $time_str .= ' - ' . date_i18n($date_time_format,$event->end_date);
                }
            }
            if($event->all_day){
                if(is_multidate_event($event)){
                    $time_str = date_i18n($date_format, $event->start_date);
                    if(empty($event->hide_end_date)) {
                        $time_str .= ' - ' . date_i18n($date_format, $event->end_date);
                    }
                }
                else{
                    $time_str = date_i18n($date_format,$event->start_date) . ' - ' . __("ALL DAY",'eventprime-event-calendar-management');
                }
            }
            $link_href = add_query_arg("event", $event->id, get_permalink($global_settings->events_page));
            $event_arr = array('title' => $event->name, 'start' => $start_date, 'end' => $end_date, 'id' => $post->ID, 'time_str' => $time_str);
            if (!empty($event->event_type)) {
                $type_model = $type_service->load_model_from_db($event->event_type);
                if($type_model){
                    $event_arr['bg_color'] = '#' . $type_model->color;
                    $event_arr['type_text_color'] = '#' . $type_model->type_text_color;
                }
            }
            if(!empty($event->event_text_color)){
                $event_arr['event_text_color'] = '#' . $event->event_text_color;
            }
            $venue_address = em_get_term_meta($event->venue, 'address', true);
            $event_arr['address'] = !empty($venue_address) ? $venue_address : '';
            $event_arr['ticket_price'] = (abs(floatval($event->ticket_price)) <= 0) ? '' : $event->ticket_price;
            // check if show one time event fees at front enable
            if($event->show_fixed_event_price){
                if($event->fixed_event_price > 0){
                    $event_arr['ticket_price'] = $event->fixed_event_price;
                }
            }
            $event_arr['ticket_price'] = apply_filters('event_magic_load_calender_ticket_price', $event_arr['ticket_price'], $event);
            if ( is_numeric( $event_arr['ticket_price'] ) ) {
                $event_arr['ticket_price'] = em_price_with_position( $event_arr['ticket_price'] );
            }
            $event_arr['image_url'] = '';
            if (!empty($event->cover_image_id)) {
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
                $event_arr['image_url'] = $thumbImage;
            }
            $bookable = $this->is_bookable($event);
            if($bookable){
                $showBookNowForGuestUsers = em_show_book_now_for_guest_users();
                $current_ts = em_current_time_by_timezone();
                if($event->status=='expired'){
                    $event_arr['link']='<div class="em_header_button em_event_expired kf-tickets">'.em_global_settings_button_title('Bookings Expired').'</div>';
                }
                elseif($current_ts>$event->last_booking_date){
                    $event_arr['link']='<div class="em_header_button em_booking-closed kf-tickets">'.em_global_settings_button_title('Bookings Closed').'</div>';
                }
                elseif($current_ts<$event->start_booking_date){
                    $event_arr['link']='<div class="em_header_button em_not_started kf-tickets">'.em_global_settings_button_title('Bookings not started yet').'</div>';
                }
                else {
                    if (is_user_logged_in() || $showBookNowForGuestUsers) {
                        $event_arr['link'] = '<form action="'.get_permalink($global_settings->booking_page).'" method="post" name="em_booking"><input type="hidden" name="event_id" value="'.$event->id.'" /><input type="hidden" name="venue_id" value="'.$event->venue.'" /><button name="tickets" onclick="em_event_booking('.$event->id.')" class="em_color">'.em_global_settings_button_title('Book Now').'</button>';
                    }
                    else {
                        $event_arr['link'] = '<a class="em_header_button kf-tickets" href="'.add_query_arg('event_id',$event->id,get_permalink($global_settings->profile_page)).'">'.em_global_settings_button_title('Book Now').'</a>';
                    }
                }
            }
            else {
                if((isset($event->parent) && !empty($event->parent)) && (isset($event->enable_recurrence_automatic_booking) && !empty($event->enable_recurrence_automatic_booking))){
                    $event_arr['link']='<div class="em_header_button em_booking-closed kf-tickets">'.em_global_settings_button_title('Bookings not allowed').'</div>';
                }
                elseif(isset($event->standing_capacity) && !empty($event->standing_capacity)){
                    $event_arr['link']='<div class="em_header_button em_booking-closed kf-tickets">'.em_global_settings_button_title('All Seats Booked').'</div>';
                }
                else{
                    $event_arr['link']='<div class="em_header_button em_booking-closed kf-tickets">'.em_global_settings_button_title('Bookings Closed').'</div>';
                }  
            }
            
            $capacity = em_event_seating_capcity($event->id);
            $event_arr['booking_status'] = array('status' => '' ,'width' => 0);
            $sum = 0;
            if(!empty($event->enable_booking) && empty($event->hide_booking_status)){
                $sum = $this->booked_seats($event->id);
                if($capacity > 0){
                    $width = ($sum / $capacity) * 100;
                    $event_arr['booking_status'] = array('status' => $sum.' / '.$capacity,'width' => $width);
                }
            }
            if (!empty($event->enable_performer) && !empty($event->performer)) {
                $performers = array();
                foreach ($event->performer as $p_id) {
                    $performer = $performer_service->load_model_from_db($p_id);
                    $cover_image = $performer_service->get_image($performer->id);
                    if (!empty($cover_image)) {
                        array_push($performers, $cover_image);
                    }
                }
                if (!empty($performers)) {
                    $event_arr['performers'] = '<ul>';
                    foreach ($performers as $img_url) {
                        $event_arr['performers'] .= "<li><img src='$img_url' /></li>";
                    }
                    $event_arr['performers'] .= '</ul>';
                }
            }
            $event_arr['url'] = em_get_single_event_page_url($event, $global_settings);

            $popup_html = '<div class="em_event_detail_popup" style="display:none">
                            <a href="'.$event_arr['url'].'" class="ep-event-modal-head" target="_blank">
                                <div class="ep_event_detail_popup_head em_bg dbfl">
                                    <div class="ep_event_title difl">'.$event_arr['title'].'</div>
                                    <div class="ep_event_price difr">'.$event_arr['ticket_price'].'</div>
                                </div>
                            </a>';
                            // before popup details
                            $popup_html .=  '<div class="ep-event-detail-wrap">';
                                ob_start();
                                    do_action('event_magic_popup_custom_data_before_details', $event);
                                    $popup_custom_data_before_details = ob_get_contents();
                                ob_end_clean();
                                $popup_html .= $popup_custom_data_before_details;
                            $popup_html .=  '<div class="ep-event-detail-row dbfl"><i class="material-icons">access_time</i></span><span class="ep_event_time">'.$event_arr['time_str'].'</span></div>';
            if(!empty($event_arr['address'])){
                $popup_html .= '<div class="ep-event-location ep-event-detail-row dbfl"><i class="material-icons">location_on</i><span class="ep_event_venue">'.$event_arr['address'].'</span></div>';
            }
            
            if(!empty($event_arr['performers'])){
                $performers_text = em_global_settings_button_title('Performers');
                $popup_html .= '<div class="ep-event-performers ep-event-detail-row dbfl"><span class="ep-event-performers-title">'.__( $performers_text,'eventprime-event-calendar-management').'</span><div class="ep_event_performers">'.$event_arr['performers'].'</div></div>';
            }
            
            if(empty($event->hide_booking_status) && absint($event->custom_link_enabled) != 1 && $event->enable_booking == 1 ){
                $popup_html .= '<div class="event_booking_details ep-event-detail-row">'.__("Booking Status", 'eventprime-event-calendar-management');
                    if($capacity > 0){
                        $popup_html .= '<div class="status">'. $event_arr['booking_status']['status'].'</div><div class="dbfl"><div id="progressbar" class="em_progressbar dbfl"><div class="em_progressbar_fill em_bg" style="width:'.$event_arr['booking_status']['width'].'%"></div></div>';
                    } else{
                        if($sum > 0){
                            $popup_html .= '<div class="status"><div class="ep-event-attenders-wrap"><span class="ep-event-attenders">' . $sum . ' </span>'.__('Attending','eventprime-event-calendar-management').'</div></div>';
                        }
                    }
                $popup_html .= '</div>'; 
            }
            if(!empty($event->enable_booking) && absint($event->custom_link_enabled) != 1){
                $popup_html .= '<div class="ep-booking-footer"><div class="ep-book-now dbfl">'.$event_arr['link'].'</div>';
            }
            // code for wishlist extension
            if(in_array('wishlist',$em->extensions)){
                if(is_user_logged_in()):
                    ob_start();
                    $event->has_popup = 1;
                    do_action('event_magic_wishlist_link',$event);
                    $wishlist = ob_get_contents();
                    ob_end_clean();
                    $popup_html .= $wishlist;
                endif;
            }
            // for custom data on calendar from extensions
            ob_start();
            do_action('event_magic_popup_custom_data',$event);
            $popup_custom_data = ob_get_contents();
            ob_end_clean();
            $popup_html .= $popup_custom_data;
            $popup_html .= '</div></div>';
            $event_arr['popup_html']=  $popup_html;
            array_push($events, $event_arr); 
        }
        return $events;
    }
    
    public function create_seats_from_venue($event_id, $venue_id) {
        $data = new stdClass();
        $event_seats = array();
        $venue_seats = array();
        $event_seats = em_get_post_meta($event_id, 'seats', true);
        $venue_seats = em_get_term_meta($venue_id, 'seats', true);
     
        $data->event_id = $event_id;
        if (empty($venue_seats)) { 
            $data->seats = array();
            em_update_post_meta($event_id, 'seats', array());
            return $data;
        }
        // Copy seat structure in case it not copied already
        if (empty($event_seats)) {  
            em_update_post_meta($event_id, 'seats', $venue_seats);
          
            $data->seats = $venue_seats;
            return $data;
        } else {
            $event_seats = em_get_post_meta($event_id, 'seats', true);
            $data->seats = $event_seats;
            return $data;
        }
    }
    
    public function events_by_type($type_id){
        return $this->dao->events_by_type($type_id);
    }
    
    public function event_count_by_type($type_id){
        return $this->dao->event_count_by_type($type_id);
    }
    
    public function get_upcoming_events_by_venue($venue_id){
        return $this->dao->get_upcoming_events_by_venue($venue_id);
    }

    public function upcoming_events_for_performer($performer_id, $args){
        $result = $this->dao->get_upcoming_events_for_performer($performer_id, $args);
        return $result;
    }
    
    public function admin_event_hover_popup($event){
        $setting_service = EventM_Factory::get_service('EventM_Setting_Service');
        $global_settings= $setting_service->load_model_from_db();
        $curr_user = get_current_user_id();
        $date_format = get_option('date_format');
        $time_format = get_option('time_format');
        $date_time_format = $date_format . ' ' . $time_format;
        $price= (abs(floatval($event->ticket_price))<=0) ? __('Free', 'eventprime-event-calendar-management') : em_currency_symbol().$event->ticket_price;
        $time_str = $performer_str = $organizer_str = '';
        if (em_compare_event_dates($event->id)) {
            //$day = date('D, M d, Y',$event->start_date);
            $day = date_i18n($date_format,$event->start_date);
            $start_time = date_i18n($time_format,$event->start_date);
            $end_time = date_i18n($time_format,$event->end_date);

            $time_str .= $day . ' ' . $start_time . ' to ' . $end_time;
        } else {
            $time_str .= date_i18n($date_time_format,$event->start_date) . ' - ' . date_i18n($date_time_format,$event->end_date);
        }
        $venue_address = em_get_term_meta($event->venue,'address',true);
        $address = !empty($venue_address) ? $venue_address : '';
        $performer_service = EventM_Factory::get_service('EventM_Performer_Service');
        $event_organizer_service = EventM_Factory::get_service('EventOrganizerM_Service');
        $type_service = EventM_Factory::get_service('EventTypeM_Service');
        if(!empty($event->enable_performer) && !empty($event->performer)) {
            $performers = array();
            foreach ($event->performer as $p_id) {
                $performer = $performer_service->load_model_from_db($p_id);
                $cover_image = $performer_service->get_image($performer->id);
                if (!empty($cover_image)) {
                    array_push($performers, $cover_image);
                }
            }
            if (!empty($performers)) {
                $performer_str = '<ul>';
                foreach ($performers as $img_url) {
                    $performer_str .= "<li><img src='$img_url' /></li>";
                }
                $performer_str .= '</ul>';
            }
        }
        $capacity = em_event_seating_capcity($event->id);
        $booking_status = array('status' => '', 'width' => 0);
        $sum = 0;
        if($event->enable_booking == 1 && empty($event->hide_booking_status)){
            $sum = $this->booked_seats($event->id);
            if($capacity > 0){
                $width = ($sum / $capacity) * 100;
                $booking_status = array('status' => $sum.' / '.$capacity, 'width' => $width);
            }
        }
        // organizer data
        if(!empty($event->organizer)) {
            $organizers = array();
            $event->organizer = is_array( $event->organizer ) ? $event->organizer : (array)$event->organizer;
            foreach ($event->organizer as $o_id) {
                $organizer = $event_organizer_service->load_model_from_db($o_id);
                $organizer_cover_image = $event_organizer_service->get_image($organizer->image_id);
                if (!empty($organizer_cover_image)) {
                    array_push($organizers, $organizer_cover_image);
                }
            }
            if (!empty($organizers)) {
                $organizer_str = '<ul>';
                foreach ($organizers as $org_img_url) {
                    $organizer_str .= "<li><img src='$org_img_url' /></li>";
                }
                $organizer_str .= '</ul>';
            }
        }
        // event feature image
        $cover_image_id = get_post_thumbnail_id( $event->id );
        if ( ! empty( $cover_image_id ) && $cover_image_id > 0 ) {
            $cover_image_url = $this->get_event_cover_image( $event->id );
            $cover_image_url_str = '<ul><li><img src='.$cover_image_url.' /></li></ul>';
        }
        $popup_html = '<div class="em_event_detail_popup" style="display:none">
            <div class="ep_event_detail_popup_head em_bg dbfl">
                <div class="ep_event_title difl">'.$event->name.'</div>
                <div class="ep_event_price difr">'.$price.'</div>
            </div>
            <div class="ep-event-detail-wrap">
                <div class="ep-event-detail-row dbfl">
                    <i class="material-icons" title="'.esc_html__('Event Time','eventprime-event-calendar-management').'">access_time</i>
                    <span class="ep_event_time">'.$time_str.'</span>
                </div>';
                if(!empty($address)){
                    $popup_html .= '<div class="ep-event-location ep-event-detail-row dbfl"><i class="material-icons" title="'.esc_html__('Event Address','eventprime-event-calendar-management').'">location_on</i><span class="ep_event_venue">'.$address.'</span></div>';
                }   
                /*if(!empty($performer_str)){
                    $popup_html .= '<div class="ep-event-performers ep-event-detail-row dbfl">
                        <span class="ep-event-performers-title">'.esc_html__('Performers','eventprime-event-calendar-management').'</span>
                        <div class="ep-event-list-images ep_event_performers">'.$performer_str.'</div>
                    </div>';
                }
                if(!empty($organizer_str)){
                    $popup_html .= '<div class="ep-event-performers ep-event-detail-row dbfl">
                        <span class="ep-event-performers-title">'.esc_html__('Organizers','eventprime-event-calendar-management').'</span>
                        <div class="ep-event-list-images ep_event_organizers">'.$organizer_str.'</div>
                    </div>';
                }
                if(!empty($cover_image_url)){
                    $popup_html .= '<div class="ep-event-performers ep-event-detail-row dbfl">
                        <span class="ep-event-performers-title">'.esc_html__('Featured Image','eventprime-event-calendar-management').'</span>
                        <div class="ep-event-list-images ep_event_cover_image">'.$cover_image_url_str.'</div>
                    </div>';
                }*/
                if( $event->enable_booking == 1 && $sum > 0 ) {
                    $popup_html .= '<div class="event_booking_details ep-event-detail-row"><i class="dashicons dashicons-hourglass"></i><div class="status">'.esc_html__("Booking Status", 'eventprime-event-calendar-management').'</div>';
                        if ($capacity > 0){
                            $popup_html .= ' '.$booking_status['status'].'<div class="dbfl"><div id="progressbar" class="em_progressbar dbfl"><div class="em_progressbar_fill em_bg" style="width:'.$booking_status['width'].'%"></div></div></div>';
                        }else{
                            if($sum > 0){
                                $popup_html .= ' <span class="ep-event-attenders"><strong>' . $sum . ' </strong></span>'.__('Attending','eventprime-event-calendar-management');
                            }
                        }
                    $popup_html .= '</div>'; 
                }
        
                $popup_html .= '<div class="ep-shortcode dbfl"><i class="material-icons" title="'.esc_html__('Event Shortcode','eventprime-event-calendar-management').'">code</i>[em_event id="'.$event->id.'"]</div>';
                $link = $delete_link = $settings_link = '';
                if( !empty( em_check_context_user_capabilities( array( 'edit_events', 'edit_others_events' ) ) ) ) {
                    $link = '<div class="ep-event-link ep-event-dash"><a href="'.admin_url('admin.php?page=em_dashboard&post_id='.$event->id).'" title="'.esc_html__('Go to the Dashboard','eventprime-event-calendar-management').'">'.esc_html__('Dashboard','eventprime-event-calendar-management').'</a></div>';
                    $settings_link = '<div class="ep-event-link ep-event-setting"><a style="float:right;" href="'.admin_url('admin.php?page=em_dashboard&tab=setting&post_id='.$event->id).'" title="'.esc_html__('Go to the Settings','eventprime-event-calendar-management').'">'.esc_html__('Settings','eventprime-event-calendar-management').'</a></div>';
                    // other user event setting not visible if no permission
                    if($curr_user != $event->user){
                        if( empty( em_check_context_user_capabilities( array( 'edit_others_events' ) ) ) ) {
                            $link = $settings_link = '';
                        }
                    }
                }
                if( !empty( em_check_context_user_capabilities( array( 'delete_events', 'delete_others_events' ) ) ) ) {
                    $delete_link = '<div class="ep-event-link ep-event-delete"><a href="#" data-id="'.$event->id.'" title="'.esc_html__('Delete Event','eventprime-event-calendar-management').'"><i class="material-icons">delete_forever</i></a></div>';
                    // other user event delete setting not visible if no permission
                    if($curr_user != $event->user){
                        if( empty( em_check_context_user_capabilities( array( 'delete_others_events' ) ) ) ) {
                            $delete_link = '';
                        }
                    }
                }
                $popup_html .= '<div class="ep-dashboard-link dbfl">'.$link.$delete_link.$settings_link.'</div>';
            $popup_html .= '</div></div>';
        return $popup_html;
    }
    
    public function get_events_by_venue($venue_id){
        return $this->dao->get_events_by_venue($venue_id);
    }
    
    public function get_events_by_user($user_id = 0){
        $events = array();
        $post_args = array(
            'author' => $user_id,
            'order' => 'DESC'
        );
        $event_posts = $this->dao->get_events($post_args);
        foreach($event_posts as $event_post) {
            $events[] = $this->load_model_from_db($event_post->ID);
        }
        return $events;
    }
    
    // Check if booking is enabled and seating capacity allows for new booking
    public function is_bookable($event){
        if(!empty($event->enable_booking)){
            // first check for recurring event and recurrence automatic booking
            if((isset($event->parent) && !empty($event->parent)) && (isset($event->enable_recurrence_automatic_booking) && !empty($event->enable_recurrence_automatic_booking))){
                return false;
            }
            // Now check for capacity
            if(!empty($event->seating_capacity)){
                $total_booked_seats= $this->booked_seats($event->id);
                if($total_booked_seats<$event->seating_capacity){
                    return true;
                }
            }
            elseif (!empty($event->standing_capacity)) {
                // capacity check for standing
                $total_booked_seats = $this->booked_seats($event->id);
                if($total_booked_seats < $event->standing_capacity){
                    return true;
                }
            }
            else {
                return true;
            }
        }
        return false;
    }
    
    public function get_ical_file() {
        if (!is_admin()) {
            $event_id = absint(event_m_get_param('event'));
            if ($event_id == 0) {
                // Do nothing
            } else {
                $download_format = sanitize_text_field(event_m_get_param('download'));
                if ($download_format === 'ical') {
                    $event = $this->load_model_from_db($event_id);
                    $setting_service = EventM_Factory::get_service('EventM_Setting_Service');
                    $options = $setting_service->load_model_from_db();
                    $event_url = add_query_arg(array('event'=>$event->id),get_permalink($options->events_page));
                    $event_content = preg_replace('#<a[^>]*href="((?!/)[^"]+)">[^<]+</a>#', '$0 ( $1 )', $event->description);
                    $event_content = str_replace("<p>", "\\n", $event_content);
                    $event_content = strip_shortcodes(strip_tags($event_content));
                    $event_content = str_replace("\r\n", "\\n", $event_content);
                    $event_content = str_replace("\n", "\\n", $event_content);
                    $event_content = preg_replace('/(<script[^>]*>.+?<\/script>|<style[^>]*>.+?<\/style>)/s', '', $event_content);
                    $timezone = em_get_user_timezone();
                    $gmt_offset_seconds = em_gmt_offset_seconds($event->start_date);
                    $time_format = ($event->all_day == 1) ? 'Ymd' : 'Ymd\\THi00\\Z';

                    $crlf = "\r\n";

                    $ical  = "BEGIN:VCALENDAR".$crlf;
                    $ical .= "VERSION:2.0".$crlf;
                    $ical .= "METHOD:PUBLISH".$crlf;
                    $ical .= "CALSCALE:GREGORIAN".$crlf;
                    $ical .= "PRODID:-//WordPress - EPv".EVENTPRIME_VERSION."//EN".$crlf;
                    $ical .= "X-ORIGINAL-URL:".home_url().'/'.$crlf;
                    $ical .= "X-WR-CALNAME:".get_bloginfo('name').$crlf;
                    $ical .= "X-WR-CALDESC:".get_bloginfo('description').$crlf;
                    $ical .= "REFRESH-INTERVAL;VALUE=DURATION:PT1H".$crlf;
                    $ical .= "X-PUBLISHED-TTL:PT1H".$crlf;
                    $ical .= "X-MS-OLK-FORCEINSPECTOROPEN:TRUE".$crlf;

                    $ical .= "BEGIN:VEVENT".$crlf;
                    $ical .= "CLASS:PUBLIC".$crlf;
                    $ical .= "UID:EP-".md5(strval($event->id))."@".em_get_site_domain().$crlf;
                    $ical .= "DTSTART:".gmdate($time_format, ($event->start_date - $gmt_offset_seconds)).$crlf;
                    $ical .= "DTEND:".gmdate($time_format, ($event->end_date - $gmt_offset_seconds)).$crlf;
                    $ical .= "DTSTAMP:".get_the_date($time_format, $event->id).$crlf;
                    $ical .= "CREATED:".get_the_date('Ymd', $event->id).$crlf;
                    $ical .= "LAST-MODIFIED:".get_the_modified_date('Ymd', $event->id).$crlf;
                    $ical .= "SUMMARY:".html_entity_decode($event->name, ENT_NOQUOTES, 'UTF-8').$crlf;
                    $ical .= "DESCRIPTION:".html_entity_decode($event_content, ENT_NOQUOTES, 'UTF-8').$crlf;
                    $ical .= "X-ALT-DESC;FMTTYPE=text/html:".html_entity_decode($event_content, ENT_NOQUOTES, 'UTF-8').$crlf;
                    $ical .= "URL:".$event_url.$crlf;

                    if ($event->venue != 0) {
                        $venue_serv = EventM_Factory::get_service('EventM_Venue_Service');
                        $venue = $venue_serv->load_model_from_db($event->venue);
                        $ical .= "LOCATION:".trim(strip_tags($venue->address)).$crlf;
                    }

                    $cover_image_id = get_post_thumbnail_id($event->id);
                    if (!empty($cover_image_id) && $cover_image_id > 0) {
                        $cover_image_url = $this->get_event_cover_image($event->id,'full');
                        $ical .= "ATTACH;FMTTYPE=".get_post_mime_type($cover_image_id).":".$cover_image_url.$crlf;
                    }

                    $ical .= "END:VEVENT".$crlf;
                    $ical .= "END:VCALENDAR";

                    header('Content-type: application/force-download; charset=utf-8');
                    header('Content-Disposition: attachment; filename="ep-event-'.$event->id.'.ics"');

                    echo $ical;
                    exit;
                }
            }
        }
    }
    
    public function update_model($model){
        $this->dao->save($model);
    }
    
    public function set_meta($id,$key,$value){
         $this->dao->set_meta($id,$key,$value);
    }
    
    public function get_meta($id,$key){
        return $this->dao->get_meta($id,$key);
    }

    public function get_wishlist_events_by_user($user_id = 0){
        $events = array();
        $event_posts = get_user_meta($user_id, 'em_wishlist', true);
        if(!empty($event_posts)){
            foreach($event_posts as $event_post_key => $event_post_value) {
                $events[] = $this->load_model_from_db($event_post_key);
            }
        }
        return $events;
    }

    public function get_book_now_button_for_event($event_model){
        $book_now_btn = '<div>';
        if($this->is_bookable($event_model)){
            $current_ts = em_current_time_by_timezone();
            if($event_model->status=='expired'){
                $book_now_btn .= em_global_settings_button_title('Bookings Expired');
            } elseif($current_ts>$event_model->last_booking_date){
                $book_now_btn .= em_global_settings_button_title('Bookings Closed');
            } elseif($current_ts<$event_model->start_booking_date){
                $book_now_btn .= em_global_settings_button_title('Bookings not started yet');
            } else {
                $setting_service = EventM_Factory::get_service('EventM_Setting_Service');
                $global_settings= $setting_service->load_model_from_db();

                if(is_user_logged_in()){
                    $currency_symbol= em_currency_symbol();
                    if ($event_model->ticket_price > 0){
                        $currencyHtml = ' - ' . $currency_symbol.$event_model->ticket_price;;
                    } else {
                        $currencyHtml = '';
                    }
                    $book_now_btn .= '<form action="'.get_permalink($global_settings->booking_page).'" method="post" name="em_booking">
                                            <button class="em-wishlist-book-now-btn " name="tickets" onclick="em_event_booking('.$event_model->id.')" class="em_header_button" id="em_booking">
                                                <i class="fa fa-ticket" aria-hidden="true"></i>'
                                                .em_global_settings_button_title('Book Now').$currencyHtml.'
                                            </button>
                                            <input type="hidden" name="event_id" value="'.$event_model->id.'" />
                                            <input type="hidden" name="venue_id" value="'.$event_model->venue.'" />
                                        </form>';
                } else {
                    $book_now_btn .= '<button class="em_profile_wishlist_btn em_header_button kf-tickets" target="_blank" href="'.add_query_arg("event_id",$event_model->id,get_permalink($global_settings->profile_page)).'">'.em_global_settings_button_title('Book Now').'</button>';
                }
            }
        } else {
            if((isset($event_model->parent) && !empty($event_model->parent)) && (isset($event_model->enable_recurrence_automatic_booking) && !empty($event_model->enable_recurrence_automatic_booking))){
                $book_now_btn .= em_global_settings_button_title('Bookings not allowed');
            }
            elseif(isset($event_model->standing_capacity) && !empty($event_model->standing_capacity)){
                $book_now_btn .= em_global_settings_button_title('All Seats Booked');
            }
            else{
                $book_now_btn .= em_global_settings_button_title('Bookings closed');
            }
        }
        $book_now_btn .= '</div>';
        return $book_now_btn;
    }
    
    // get events list for masonry view
    public function get_mesonry_events_query($custom_args=array(),$shortcode_params=array()) {
        $setting_service = EventM_Factory::get_service('EventM_Setting_Service');
        $gs = $setting_service->load_model_from_db();
        $hide_past_events = $gs->hide_past_events;
        $upcoming = '';
        if(!empty($shortcode_params)){
            $type_ids = isset($shortcode_params['types']) ? $shortcode_params['types'] : array();
            $venue_ids = isset($shortcode_params['sites']) ? $shortcode_params['sites'] : array();
            $viewtype = isset($shortcode_params['view']) ? $shortcode_params['view'] : 'mesonry';
            $upcoming = (isset($shortcode_params['upcoming']) && $shortcode_params['upcoming'] != '') ? $shortcode_params['upcoming'] : '';
            $recurring = isset($shortcode_params['recurring']) ? $shortcode_params['recurring'] : 1;
            $individual_events = isset($shortcode_params['individual_events']) ? $shortcode_params['individual_events'] : '';
        }
        if(event_m_get_param('upcoming')){
            $upcoming = event_m_get_param('upcoming');
        }
        $ind_events = event_m_get_param('i_events');
        if( !empty( $ind_events ) && $ind_events != '' ){    
            $individual_events = event_m_get_param('i_events');
        }
        $posts_per_page = 10;
        $posts_per_page = $this->get_posts_per_page_card();
        if(isset($shortcode_params['show']) && !empty($shortcode_params['show'])){
            $posts_per_page = $shortcode_params['show'];
        }
        if(event_m_get_param('show')){
           $posts_per_page = event_m_get_param('show');
        }
        $paged = (get_query_var('paged') ) ? get_query_var('paged') : 1;
        if(event_m_get_param('page')){
            $paged = event_m_get_param('page');
        }
        $meta_query= array(
            'relation' => 'AND',
            array(
                'key' => em_append_meta_key('hide_event_from_events'),
                'value' => '1',
                'compare' => '!='
            )
        );
        $search = event_m_get_param('em_s');
        $args = array(
            'meta_key' => em_append_meta_key('start_date'),
            'orderby' => 'meta_value',
            'order' => 'ASC',
            'posts_per_page' => $posts_per_page,
            'paged' => $paged,
            'meta_query' => $meta_query,
            'post_type' => EM_EVENT_POST_TYPE
        );
        $args['post_status'] = !empty($hide_past_events) == 1 ? 'publish' : 'any';
        
        if ($search == "1"){
            $sd = event_m_get_param('em_sd');
            // datepicker format from global settings
            $datepicker_format_arr = explode('&', em_global_settings('datepicker_format'));
            $date_format = (!empty($datepicker_format_arr) && isset($datepicker_format_arr[1])) ? $datepicker_format_arr[1] : '!Y-m-d';
            $start_date= DateTime::createFromFormat($date_format, $sd);
            if(!empty($start_date)){
                $start_date->setTime(23,59,59);
                $start_ts= $start_date->getTimestamp();
                $start_date->setTime(0,0);
                $end_ts=$start_date->getTimestamp();
                array_push($meta_query,array(
                    'key' => em_append_meta_key('start_date'),
                    'value' => $start_ts,
                    'compare' => '<=',
                    'type'=>'NUMERIC'
                ),
                array(
                    'key' => em_append_meta_key('end_date'),
                    'value' => $end_ts,
                    'compare' => '>=',
                    'type'=>'NUMERIC'
                ));
            }
            
            $keyword = event_m_get_param('em_search');
            if (!empty($keyword)){
                $args['s']= $keyword;
            }
        }

        $types = event_m_get_param('em_types');
        if (!empty($types)) {
            /* $types = is_array($types) ? $types : array($types); */
            $types = is_array($types) ? $types : explode(',', $types);
            if(!empty($type_ids))
                $type_ids = array_intersect($types,$type_ids);
            else
                $type_ids = $types;
        }
        
        $venue = event_m_get_param('em_venue');
        if (!empty($venue)) {
            /* $venue = is_array($venue) ? $venue : array($venue); */
            $venue = is_array($venue) ? $venue : explode(',', $venue);
            if(!empty($venue_ids))
                $venue_ids = array_intersect($venue,$venue_ids);
            else
                $venue_ids = $venue;
        }
        
        if (!empty($type_ids)) {
            array_push($meta_query,array(
                'key' => em_append_meta_key('event_type'),
                'value' => $type_ids,
                'compare' => 'IN',
                'type'=>'NUMERIC'
            ));
        }
        
        if (!empty($venue_ids)) {
            array_push($meta_query,array(
                'key' => em_append_meta_key('venue'),
                'value' => $venue_ids,
                'compare' => 'IN',
                'type'=>'NUMERIC'
            ));
        }

        /* if(isset($shortcode_params['upcoming']) && $shortcode_params['upcoming'] != '' && (!isset($shortcode_params['individual_events']) || empty($shortcode_params['individual_events']))){ */
        if(isset($upcoming) && $upcoming != '' && (!isset($individual_events) || empty($individual_events))){
            if( $upcoming == 1 ) {
                array_push($meta_query,array(
                    'key' => em_append_meta_key( 'start_date' ),
                    'value' => current_time( 'timestamp' ),
                    'compare' => '>='
                ));
            }
            if( $upcoming == 0 ) {
                array_push($meta_query,array(
                    'key' => em_append_meta_key( 'end_date' ),
                    'value' => current_time( 'timestamp' ),
                    'compare' => '<='
                ));
            }
        }
        
        /* individual events shortcode arguements - yesterday, today, tomorrow, month */
        if( isset( $shortcode_params['individual_events'] ) && $shortcode_params['individual_events'] != '' ){
            $meta_query = $this->individual_events_shortcode_argument( $meta_query, $individual_events );
        }

        $i_events = event_m_get_param('i_events');
        if( !empty( $i_events ) && $i_events != '' ){
            $meta_query = $this->individual_events_shortcode_argument( $meta_query, $i_events );
        }

        if(!empty($meta_query)){
            $args['meta_query']= $meta_query;
        }
        
        $args = wp_parse_args($custom_args, $args);
        // post filter
        add_filter('posts_orderby', 'em_posts_order_by');
        $wp_query = new WP_Query($args);
        remove_filter('posts_orderby', 'em_posts_order_by');
        return $wp_query;
    }

    public function save_email($event){
        $event->enable_custom_booking_confirmation_email = absint(event_m_get_param('enable_custom_booking_confirmation_email'));
        $event->custom_booking_confirmation_email_subject = sanitize_text_field(event_m_get_param('custom_booking_confirmation_email_subject'));
        $event->custom_booking_confirmation_email_body = wp_kses_post(stripslashes(event_m_get_param('custom_booking_confirmation_email_body')));
        $event_id = $this->dao->save($event);
        return $event_id;
    }

    public function get_cards_events_query( $custom_args=array(), $shortcode_params = array() ) {
        $setting_service = EventM_Factory::get_service('EventM_Setting_Service');
        $gs = $setting_service->load_model_from_db();
        $hide_past_events = $gs->hide_past_events;
        $viewtype = $upcoming = '';
        if(!empty($shortcode_params)){
            $type_ids = isset($shortcode_params['types']) ? $shortcode_params['types'] : array();
            $venue_ids = isset($shortcode_params['sites']) ? $shortcode_params['sites'] : array();
            $viewtype = isset($shortcode_params['view']) ? $shortcode_params['view'] : 'card';
            $upcoming = (isset($shortcode_params['upcoming']) && $shortcode_params['upcoming'] != '') ? $shortcode_params['upcoming'] : '';
            $individual_events = isset($shortcode_params['individual_events']) ? $shortcode_params['individual_events'] : '';
            $id = isset($shortcode_params['id']) ? $shortcode_params['id'] : '';
        }
        if(event_m_get_param('upcoming')){
            $upcoming = event_m_get_param('upcoming');
        }
        $ind_events = event_m_get_param('i_events');
        if( !empty( $ind_events ) && $ind_events != '' ){    
            $individual_events = event_m_get_param('i_events');
        }
        $posts_per_page = 10;
        if($viewtype == 'card' || (isset($_GET['events_view']) && $_GET['events_view'] == 'card')){
            $posts_per_page = $this->get_posts_per_page_card();
            if(isset($shortcode_params['show']) && !empty($shortcode_params['show'])){
                $posts_per_page = $shortcode_params['show'];
            }
        }
        if(event_m_get_param('show')){
            $posts_per_page = event_m_get_param('show');
        }
        $paged = (get_query_var('paged') ) ? get_query_var('paged') : 1;
        if(event_m_get_param('page')){
            $paged = event_m_get_param('page');
        }
        $meta_query= array(
            'relation' => 'AND',
            array(
                'key' => em_append_meta_key('hide_event_from_events'),
                'value' => '1',
                'compare' => '!='
            )
        );
        
        if(isset($upcoming) && $upcoming != '' && (!isset($individual_events) || empty($individual_events))){
            if( $upcoming == 1 ) {
                array_push($meta_query,array(
                    'key' => em_append_meta_key( 'start_date' ),
                    'value' => current_time( 'timestamp' ),
                    'compare' => '>='
                ));
            }
            if( $upcoming == 0 ) {
                array_push($meta_query,array(
                    'key' => em_append_meta_key( 'end_date' ),
                    'value' => current_time( 'timestamp' ),
                    'compare' => '<='
                ));
            }
        }
        $search = event_m_get_param('em_s');
        $args = array(
            'meta_key' => em_append_meta_key('start_date'),
            'orderby' => 'meta_value',
            'order' => 'ASC',
            'posts_per_page' => $posts_per_page,
            'paged' => $paged,
            'meta_query' => $meta_query,
            'post_type' => EM_EVENT_POST_TYPE
        );
        $args['post_status'] = !empty($hide_past_events) == 1 ? 'publish' : 'any';
    
        if( isset( $id ) && ! empty( $id ) ){
            $args['p'] = $id;
        }

        if ($search == "1"){
            $sd = event_m_get_param('em_sd');
            // datepicker format from global settings
            $datepicker_format_arr = explode('&', em_global_settings('datepicker_format'));
            $date_format = (!empty($datepicker_format_arr) && isset($datepicker_format_arr[1])) ? $datepicker_format_arr[1] : '!Y-m-d';
            $start_date= DateTime::createFromFormat($date_format, $sd);
            if(!empty($start_date)){
                $start_date->setTime(23,59,59);
                $start_ts= $start_date->getTimestamp();
                $start_date->setTime(0,0);
                $end_ts=$start_date->getTimestamp();
                array_push($meta_query,array(
                    'key' => em_append_meta_key('start_date'),
                    'value' => $start_ts,
                    'compare' => '<=',
                    'type'=>'NUMERIC'
                ),
                array(
                    'key' => em_append_meta_key('end_date'),
                    'value' => $end_ts,
                    'compare' => '>=',
                    'type'=>'NUMERIC'
                ));
            }
            
            $keyword = event_m_get_param('em_search');
            if (!empty($keyword)){
                $args['s']= $keyword;
            }
        }

        $types = event_m_get_param('em_types');
        if (!empty($types)) {
            $types = is_array($types) ? $types : explode(',', $types);
            if(!empty($type_ids))
                $type_ids = array_intersect($types,$type_ids);
            else
                $type_ids = $types;
        }
        $venue = event_m_get_param('em_venue');
        if (!empty($venue)) {
            $venue = is_array($venue) ? $venue : explode(',',$venue);
            if(!empty($venue_ids))
            $venue_ids = array_intersect($venue,$venue_ids);
                else
            $venue_ids = $venue;
        }
    
        if (!empty($type_ids)) {
            array_push($meta_query,array(
                'key' => em_append_meta_key('event_type'),
                'value' => $type_ids,
                'compare' => 'IN',
                'type'=>'NUMERIC'
            ));
        }
        
        if (!empty($venue_ids)) {
            array_push($meta_query,array(
                'key' => em_append_meta_key('venue'),
                'value' => $venue_ids,
                'compare' => 'IN',
                'type'=>'NUMERIC'
            ));
        }
        /* individual events shortcode arguements - yesterday, today, tomorrow, month */
        if( isset( $shortcode_params['individual_events'] ) && $shortcode_params['individual_events'] != '' ){
            $meta_query = $this->individual_events_shortcode_argument( $meta_query, $individual_events );
        }
        
        $i_events = event_m_get_param('i_events');
        if( !empty( $i_events ) && $i_events != '' ){
            $meta_query = $this->individual_events_shortcode_argument( $meta_query, $i_events );
        }
        
        if(!empty($meta_query)){
            $args['meta_query']= $meta_query;
        }
        
        $args = wp_parse_args($custom_args, $args);
        // post filter
        add_filter('posts_orderby', 'em_posts_order_by');
        $wp_query = new WP_Query($args);
        remove_filter('posts_orderby', 'em_posts_order_by');
        return $wp_query;
    }

    public function get_posts_per_page_card(){
        $posts_per_page = 10;
        $show_no_of_events_card = em_global_settings('show_no_of_events_card');
        $posts_per_page = $show_no_of_events_card;
        if($show_no_of_events_card == 'custom'){
            $posts_per_page = em_global_settings('card_view_custom_value');
        }
        if($show_no_of_events_card == 'all'){
            $posts_per_page = -1;
        }
        return $posts_per_page;
    }

    /**
     * set price option
     */
    private function set_multi_price_option($event, $event_id){
        if(empty($event->id)){
            $this->add_multi_price_option($event, $event_id);
        }
        else{
            $this->update_multi_price_option($event, $event_id);
        }
    }
    /** 
     * add event price in table
     */
    public function add_multi_price_option($event, $event_id){
        global $wpdb;
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
        $table_name = $wpdb->prefix.'em_price_options';
        $result = $wpdb->insert($table_name, $data);
    }

    /**
     * update event price from table
     */
    public function update_multi_price_option($event, $event_id) {
        global $wpdb;
        $table_name = $wpdb->prefix.'em_price_options';
        $get_price_data = $wpdb->get_row( "SELECT * FROM $table_name WHERE event_id = $event_id AND is_event_price = 1" );
        $event_post_details = get_post( $event_id );
        if(empty($get_price_data)){
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
            if ( isset( $event_post_details->post_parent ) && $event_post_details->post_parent != 0 ) {
                /* parent price option id for child events  */
                $table_name = $wpdb->prefix.'em_price_options';
                $get_parent_price_data = $wpdb->get_row( "SELECT * FROM $table_name WHERE event_id = $event_post_details->post_parent AND is_event_price = 1" );
                if(!empty($get_parent_price_data->id)){
                    $data['parent_price_option_id'] = $get_parent_price_data->id;
                }   
            }
            $table_name = $wpdb->prefix.'em_price_options';
            $result = $wpdb->insert($table_name, $data);
        }
        else{
            $price_id = $get_price_data->id;
            if ( isset( $event_post_details->post_parent ) && $event_post_details->post_parent != 0 ) {
                /* parent price option id for child events  */
                $table_name = $wpdb->prefix.'em_price_options';
                $get_parent_price_data = $wpdb->get_row( "SELECT * FROM $table_name WHERE event_id = $event_post_details->post_parent AND is_event_price = 1" );
                $wpdb->update( $table_name, 
                    array( 
                        'price' => $event->ticket_price,
                        'capacity' => (!empty($event->standing_capacity)) ? $event->standing_capacity : $event->seating_capacity,
                        'updated_at' => date_i18n("Y-m-d H:i:s", time())
                    ), 
                    array( 'id' => $price_id, 'parent_price_option_id' => $get_parent_price_data->id )
                );
            }else{
                $wpdb->update( $table_name, 
                    array( 
                        'price' => $event->ticket_price,
                        'capacity' => (!empty($event->standing_capacity)) ? $event->standing_capacity : $event->seating_capacity,
                        'updated_at' => date_i18n("Y-m-d H:i:s", time())
                    ), 
                    array( 'id' => $price_id )
                );
            }
        }
    }

    public function save_event_price_option() {
        $event_id = absint(event_m_get_param('event_id'));
        $event = $this->load_model_from_db($event_id);
        $response = array();
        if(!empty($event_id) && !empty($event)){
            global $wpdb;
            $event_capacity = $event->seating_capacity;
            $venue_service = EventM_Factory::get_service('EventM_Venue_Service');
            $venue = $venue_service->load_model_from_db($event->venue);
            $venue_type = $venue->type;
            if($venue_type == 'standings'){
                $event_capacity = $event->standing_capacity;
            }
            $option_id = absint(event_m_get_param('option_id'));
            $data = array();
            $data['name'] = sanitize_text_field(event_m_get_param('name'));
            $allowed_html = wp_kses_allowed_html( 'post' );
            $data['description'] = wp_kses(htmlentities(event_m_get_param('description')), $allowed_html);
            
            $start_date = sanitize_text_field(event_m_get_param('start_date'));
            $end_date = sanitize_text_field(event_m_get_param('end_date'));
            $data['start_date'] = (!empty($start_date) ? date_i18n("Y-m-d H:i:s", strtotime($start_date)) : NULL);
            $data['end_date'] = (!empty($end_date) ? date_i18n("Y-m-d H:i:s", strtotime($end_date)) : NULL);
            $price = sanitize_text_field(event_m_get_param('price'));
            $special_price = sanitize_text_field(event_m_get_param('special_price'));
            $data['price'] = number_format(floatval($price), 2, '.', '');
            $data['special_price'] = number_format(floatval($special_price), 2, '.', '');
            $data['capacity'] = absint(sanitize_text_field(event_m_get_param('capacity')));
            $data['is_default'] = absint(sanitize_text_field(event_m_get_param('is_default')));
            $data['capacity_progress_bar'] = absint(sanitize_text_field(event_m_get_param('capacity_progress_bar')));
            $data['variation_color'] = sanitize_text_field(event_m_get_param('variation_color'));
            $table_name = $wpdb->prefix.'em_price_options';
            if(empty($option_id)){
                // check event capacity first
                $getOptionCapacity = $wpdb->get_results(" SELECT sum(capacity) as existing_capacity FROM $table_name WHERE event_id = $event_id");
                $totalCapacity = $getOptionCapacity[0]->existing_capacity + $data['capacity'];
                if($totalCapacity > $event_capacity){
                    $response['error'] = true;
                    $response['message'] = esc_html__('The sum of all options capacity should not be greater then Event capacity.', 'eventprime-event-calendar-management');
                    return $response;
                }

                $data['event_id'] = $event_id;
                $data['is_event_price'] = 0;
                $data['icon'] = sanitize_text_field(event_m_get_param('icon'));
                $data['priority'] = absint(sanitize_text_field(event_m_get_param('priority')));
                if(empty($data['priority'])){
                    $data['priority'] = 1;
                }
                $data['status'] = 1;
                $data['created_at'] = date_i18n("Y-m-d H:i:s", time());
                $result = $wpdb->insert($table_name, $data);
                $option_id = $wpdb->insert_id;
                /* insert price option data for child events  */
                $child_events_data = array( 'data' => $data, 'option_id' => $option_id );
                do_action( 'insert_child_events_price_option_data', $child_events_data );
            }
            else{
                // check event capacity first
                $getOptionCapacity = $wpdb->get_results(" SELECT sum(capacity) as existing_capacity FROM $table_name WHERE event_id = $event_id AND id != $option_id ");
                $totalCapacity = $getOptionCapacity[0]->existing_capacity + $data['capacity'];
                if($totalCapacity > $event_capacity){
                    $response['error'] = true;
                    $response['message'] = esc_html__('The sum of all options capacity should not be greater then Event capacity.', 'eventprime-event-calendar-management');
                    return $response;
                }
                $icon = sanitize_text_field(event_m_get_param('icon'));
                if(!empty($icon)){
                    $data['icon'] = sanitize_text_field(event_m_get_param('icon'));
                }
                $data['updated_at'] = date_i18n("Y-m-d H:i:s", time());
                $result = $wpdb->update($table_name, $data, array('id' => $option_id));
                /* update price option data for child events  */
                $child_events_data = array( 'data' => $data, 'event_id' => $event_id, 'option_id' => $option_id );
                do_action( 'update_child_events_price_option_data', $child_events_data );   
            }
            if(!empty($data['is_default'])){
                $get_price_data = $wpdb->get_results( "SELECT * FROM $table_name WHERE id != $option_id AND event_id = $event_id" );
                if(!empty($get_price_data)){
                    foreach($get_price_data as $price_data){
                        $updated_data['is_default'] = 0;
                        $result = $wpdb->update($table_name, $updated_data, array('id' => $price_data->id));
                    }
                }
            }
            // update seat data
            $selectedSeats = event_m_get_param('selectedSeats');
            $is_seat_update = sanitize_text_field(event_m_get_param('is_seat_update'));
            $updated_seat_data = array();
            if(!empty($selectedSeats) && count($selectedSeats) > 0){
                if(!empty($option_id)){ //remove old data
                    if(!empty($is_seat_update)){
                        $get_seat_data = $wpdb->get_row( "SELECT seat_data FROM $table_name WHERE id = $option_id" );
                        if(!empty($get_seat_data->seat_data)){
                            $old_seat_data = unserialize($get_seat_data->seat_data);
                            if(!empty($old_seat_data) && count($old_seat_data) > 0){
                                // remove variation from seats
                                $eventSeats = $event->seats;
                                foreach ($old_seat_data as $okey => $ovalue) {
                                    $valIndex = $ovalue->uniqueIndex;
                                    if(!empty($valIndex)){
                                        $splInd = explode('-', $valIndex);
                                        if(count($splInd) == 2){
                                            $one = $splInd[0];
                                            $two = $splInd[1];
                                            if(isset($eventSeats[$one]) && isset($eventSeats[$one][$two])){
                                                $seatVar = &$eventSeats[$one][$two];
                                                $genColor = sanitize_text_field(event_m_get_param('genColor'));
                                                $seatVar->price = $event->ticket_price;
                                                $seatVar->seatColor = $genColor;
                                                $seatVar->seatBorderColor = '3px solid ' . $genColor;
                                                unset($seatVar->variation_id);
                                                unset($seatVar->mainSeatColor);
                                                unset($seatVar->mainSeatBorderColor);
                                                $eventSeats[$one][$two] = $seatVar;
                                            }
                                        }
                                    }
                                }
                                $event->seats = $eventSeats;
                                $event_id = $this->dao->save($event);
                            }
                        }
                    }
                }
                $price = sanitize_text_field(event_m_get_param('price'));
                $special_price = sanitize_text_field(event_m_get_param('special_price'));
                if(!empty($special_price)){
                    $price .= '-'.$special_price;
                }
                $eventSeats = $event->seats;
                foreach($selectedSeats as $key => $seats){
                    $valIndex = $seats->uniqueIndex;
                    if(!empty($valIndex)){
                        $splInd = explode('-', $valIndex);
                        if(count($splInd) == 2){
                            $one = $splInd[0];
                            $two = $splInd[1];
                            if(isset($eventSeats[$one]) && isset($eventSeats[$one][$two])){
                                $seatVar = &$eventSeats[$one][$two];
                                $variation_color = sanitize_text_field(event_m_get_param('variation_color'));
                                $seatVar->price = $price;
                                $seatVar->seatColor = '#'.$variation_color;
                                $seatVar->seatBorderColor = '3px solid #'.$variation_color;
                                $seatVar->mainSeatColor = '#'.$variation_color;
                                $seatVar->mainSeatBorderColor = '3px solid #'.$variation_color;
                                $seatVar->variation_id = $option_id;
                                $updated_seat_data[] = $seatVar;
                                $eventSeats[$one][$two] = $seatVar;
                            }
                        }
                    }
                }
                $event->seats = $eventSeats;
                $event_id = $this->dao->save($event);
                //$updated_event = $this->load_model_from_db($event_id);
            }
            if(!empty($is_seat_update)){
                $updated_data = array();
                $updated_data['seat_data'] = (!empty($updated_seat_data) && count($updated_seat_data) > 0) ? serialize($updated_seat_data) : '';
                $result = $wpdb->update($table_name, $updated_data, array('id' => $option_id));
                /* update price option data for child events if seat update */
                $child_events_data = array( 'data' => $updated_data, 'event_id' => $event_id, 'option_id' => $option_id );
                do_action( 'update_child_events_price_option_data', $child_events_data );
            }
            $response['error'] = false;
        }
        else{
            $response['error'] = true;
            $response['message'] = esc_html__('Event id should not be empty', 'eventprime-event-calendar-management');
        }
        return $response;
    }

    public function save_event_price_option_sorting() {
        $response = array();
        $option_id = event_m_get_param('option_id');
        $event_id = event_m_get_param('event_id');
        $i = 1;
        global $wpdb;
        $table_name = $wpdb->prefix.'em_price_options';
        foreach($option_id as $id){
            $get_price_data = $wpdb->get_row( "SELECT id FROM $table_name WHERE id = $id" );
            $data['priority'] = $i;
            if(!empty($get_price_data)){
                $wpdb->update($table_name, $data, array('id' => $id));
                $i++;
            }
        }
        return true;
    }

    public function print_upcoming_event_block($events) {?>
        <div class="ep-event-type-events em_block dbfl">
            <div class="kf-row-heading">
                <span class="kf-row-title"><?php echo __('Upcoming Events', 'eventprime-event-calendar-management'); ?>
                    <span class="em_events_count-wrap em_bg">
                        <span class="em_events_count_no em_color"></span>
                    </span>
                </span>
            </div>  
            <div class="em_event_list">
                <?php
                if (!empty($events)) {
                    $showBookNowForGuestUsers = em_show_book_now_for_guest_users();
                    $i = 1;$countNo = 0;
                    $setting_service = EventM_Factory::get_service('EventM_Setting_Service');
                    $global_settings = $setting_service->load_model_from_db();
                    $today = em_current_time_by_timezone();
                    $currency_symbol = em_currency_symbol();
                    foreach ($events as $event) {
                        $eventId = isset($event->id) ? $event->id : $event->ID;
                        $event_model = $this->load_model_from_db($eventId);
                        if($event_model->start_date < $today) continue;
                        $event_url = (absint($event_model->custom_link_enabled) == 1) ? $event_model->custom_link : add_query_arg('event', $event_model->id, get_page_link($global_settings->events_page));
                        $emstyle = '';
                        if($i > 5){
                            $emstyle = 'style="display:none;"';
                        }?>
                        <div class="kf-upcoming-event-row em_block dbfl <?php echo empty($event_model->enable_booking) ? 'em_event_disabled' : ''; ?>" id="em-upcoming-<?php echo $i;?>" <?php echo $emstyle;?>>
                            <div class="kf-upcoming-event-thumb em-col-2 difl">
                                <a href="<?php echo $event_url; ?>">
                                    <?php 
                                    $thumbImage = esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png');
                                    if (!empty($event_model->cover_image_id)): ?>
                                        <?php 
                                        $thumbImage = wp_get_attachment_image_src($event_model->cover_image_id, 'large')[0];
                                        if(empty($thumbImage)){
                                            $thumbImage = get_the_post_thumbnail($event_model->id,'large');
                                            if(isset($event_model->parent) && !empty($event_model->parent) && empty($thumbImage)){
                                                $thumbImage = get_the_post_thumbnail($event_model->parent,'large');
                                            }
                                        }?>
                                        <img src="<?php echo $thumbImage; ?>" alt="<?php _e('Event Cover Image', 'eventprime-event-calendar-management');?>">
                                    <?php else: ?>
                                        <img src="<?php echo esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png'); ?>" alt="<?php _e('Dummy Image','eventprime-event-calendar-management'); ?>" class="em-no-image" >
                                    <?php endif; ?>
                                </a>
                            </div>
                            <div class="kf-upcoming-event-title em-col-5 em-col-pad20 difl">
                                <a href="<?php echo $event_url; ?>">
                                    <?php echo $event_model->name; ?>
                                </a>
                                <?php if ($today>$event_model->start_date && $today<$event_model->end_date) { ?>
                                    <span class="kf-live"><?php _e('Live','eventprime-event-calendar-management'); ?></span>
                                <?php } ?>
                                <div class="kf-upcoming-event-post-date">
                                    <div class="em_event_start difl em_wrap">
                                    <?php echo date_i18n(get_option('date_format').' '.get_option('time_format'), $event_model->start_date); ?>
                                    <span> - </span>
                                    <?php echo date_i18n(get_option('date_format').' '.get_option('time_format'), $event_model->end_date); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="kf-upcoming-event-booking em-col-5 em-col-pad20 difr">
                                <div class="em_header_button kf-button">
                                    <?php if ($this->is_bookable($event_model) && absint($event_model->custom_link_enabled) != 1): $current_ts = em_current_time_by_timezone(); ?>
                                        <?php if ($event_model->status=='expired'): ?>
                                           <div class="em_header_button em_not_bookable kf-tickets"><?php echo em_global_settings_button_title('Bookings Expired'); ?></div>
                                        <?php elseif ($current_ts>$event_model->last_booking_date): ?>
                                           <div class="em_header_button em_not_bookable kf-button"><?php echo em_global_settings_button_title('Bookings Closed'); ?></div>
                                        <?php elseif($current_ts<$event_model->start_booking_date): ?>  
                                           <div class="em_header_button em_not_bookable kf-button"><?php echo em_global_settings_button_title('Bookings not started yet'); ?></div>
                                        <?php else: ?>
                                            <?php if(is_user_logged_in() || $showBookNowForGuestUsers): ?>
                                                <form action="<?php echo get_permalink($global_settings->booking_page); ?>" method="post" name="em_booking">
                                                    <button class="em_header_button kf-button em_color" name="tickets" onclick="em_event_booking(<?php echo $event_model->id ?>)" class="em_header_button" id="em_booking">
                                                        <i class="fa fa-ticket" aria-hidden="true"></i>
                                                        <?php
                                                        echo em_global_settings_button_title('Book Now');
                                                        if ($event_model->ticket_price > 0){
                                                            $ticketPrice = $event_model->ticket_price;
                                                            // check if show one time event fees at front enable
                                                            if($event_model->show_fixed_event_price){
                                                                if($event_model->fixed_event_price > 0){
                                                                    $ticketPrice = $event_model->fixed_event_price;
                                                                }
                                                            }
                                                            if ($ticketPrice > 0){
                                                                echo " - " . '<span class="em_event_price">' . em_price_with_position($ticketPrice, $currency_symbol) . '</span>';
                                                            }
                                                            do_action('event_magic_single_event_ticket_price_after', $event_model, $ticketPrice);
                                                        }
                                                        ?>
                                                    </button>
                                                    <input type="hidden" name="event_id" value="<?php echo $event_model->id; ?>" />
                                                    <input type="hidden" name="venue_id" value="<?php echo $event_model->venue; ?>" />
                                                </form>
                                                <?php else: ?>
                                                    <a class="em_header_button kf-button em_color" target="_blank" href="<?php echo add_query_arg('event_id',$event_model->id, get_permalink($global_settings->profile_page)); ?>"><?php echo em_global_settings_button_title('Book Now'); ?></a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        <?php elseif(absint($event_model->custom_link_enabled) != 1): ?>
                                        <div class="em_event_attr_box em_eventpage_register difl">
                                            <div class="em_header_button em_not_bookable kf-button">
                                                <?php echo em_global_settings_button_title('Bookings Closed'); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?> 
                                </div>
                            </div>
                        </div><?php
                        $i++;$countNo++;
                    }?>
                    <script type="text/javascript">
                        var countNum = <?php echo $countNo?>;
                        if(countNum > 0){
                            document.addEventListener("DOMContentLoaded", function(event) { 
                                jQuery(".em_events_count_no").html(countNum);
                            });
                        }
                    </script>
                    <?php if(count($events) > 5){?>
                        <div class="em-upcoming-event-load-more">
                            <button type="button" class="ep-load-more" id="em-upcoming-event-load-more" data-total_count="<?php echo count($events);?>" data-current_count="5"><?php echo __('Load More', 'eventprime-event-calendar-management');?></button>
                        </div><?php
                    }
                }?>
            </div>
        </div><?php
    }

    public function get_organizers_dropdown() {
        $organizer_dao = new EventM_Event_Organizer_DAO();
        $event_organizers = array();
        $organizers = $organizer_dao->get_all();
        if ($organizers != null) {
            foreach ($organizers as $organizer) {
                $tmp = new stdClass();
                $tmp->name = $organizer->name;
                $tmp->id = $organizer->id;
                $event_organizers[] = $tmp;
            }
        }

        return $event_organizers;
    }

    public function event_count_by_organizer($organizer_id){
        return $this->dao->event_count_by_organizer($organizer_id);
    }

    public function events_by_organizer($organizer_id){
        return $this->dao->events_by_organizer($organizer_id);
    }

    public function upcoming_events_for_organizer( $organizer_id, $args ){
        $results = $this->dao->get_upcoming_events_for_organizer( $organizer_id, $args );
        return $results;
    }

    public function delete_event_price_option() {
        $option_id = event_m_get_param('option_id');
        $event_id = event_m_get_param('event_id');
        $response = array();
        if(!empty($option_id) && !empty($event_id)){
            global $wpdb;
            $table_name = $wpdb->prefix.'em_price_options';
            foreach($option_id as $id){
                $get_price_data = $wpdb->get_row( "SELECT * FROM $table_name WHERE id = $id" );
                if(!empty($get_price_data)){
                    if(!empty($get_price_data->seat_data) && is_serialized($get_price_data->seat_data)){
                        $price_seat_data = unserialize($get_price_data->seat_data);
                        if(!empty($price_seat_data) && count($price_seat_data) > 0){
                            $event = $this->load_model_from_db($event_id);
                            if(!empty($event->seats)){
                                $eventSeats = $event->seats;
                                foreach ($price_seat_data as $pkey => $pvalue) {
                                    $valIndex = $pvalue->uniqueIndex;
                                    if(!empty($valIndex)){
                                        $splInd = explode('-', $valIndex);
                                        if(count($splInd) == 2){
                                            $one = $splInd[0];
                                            $two = $splInd[1];
                                            if(isset($eventSeats[$one]) && isset($eventSeats[$one][$two])){
                                                $seatVar = &$eventSeats[$one][$two];
                                                if(isset($seatVar->variation_id) && $seatVar->variation_id == $id){
                                                    $seatVar->price = $event->ticket_price;
                                                    $seatVar->seatColor = '#null';
                                                    $seatVar->seatBorderColor = '3px solid #null';
                                                    unset($seatVar->variation_id);
                                                    unset($seatVar->mainSeatColor);
                                                    unset($seatVar->mainSeatBorderColor);
                                                    $eventSeats[$one][$two] = $seatVar;
                                                }
                                            }
                                        }
                                    }
                                }
                                $event->seats = $eventSeats;
                                $event_id = $this->dao->save($event);
                            }
                        }
                    }
                    $wpdb->delete( $table_name, array( 'ID' => $id ) );
                }
            }
            $response['error'] = false;
        }
        else{
            $response['error'] = true;
            $response['message'] = esc_html__('Event id should not be empty', 'eventprime-event-calendar-management');
        }
        return $response;
    }

    public function individual_events_shortcode_argument( $meta_query, $individual_events = '' ){
        if( $individual_events == 'yesterday' ){
            
            $yesterday_dt = new DateTime('yesterday');
            $yesterday_ts = strtotime( $yesterday_dt->format('Y-m-d H:i:s') );
            array_push($meta_query,array(
                'key' => em_append_meta_key( 'start_date' ),
                'value' => $yesterday_ts,
                'compare' => '>=',
                'type'=>'NUMERIC'
            ));

            $today_dt = new DateTime('today');
            $today_ts = strtotime( $today_dt->format('Y-m-d H:i:s') );
            array_push($meta_query,array(
                'key' => em_append_meta_key( 'start_date' ),
                'value' => $today_ts,
                'compare' => '<',
                'type'=>'NUMERIC'
            ));

        }
        if( $individual_events == 'today' ){

            $today_dt = new DateTime('today');
            $today_ts = strtotime( $today_dt->format('Y-m-d H:i:s') );
            array_push($meta_query,array(
                'key' => em_append_meta_key( 'start_date' ),
                'value' => $today_ts,
                'compare' => '>=',
                'type'=>'NUMERIC'
            ));

            $tomorrow_dt = new DateTime('tomorrow');
            $tomorrow_ts = strtotime( $tomorrow_dt->format('Y-m-d H:i:s') );
            array_push($meta_query,array(
                'key' => em_append_meta_key( 'start_date' ),
                'value' => $tomorrow_ts,
                'compare' => '<',
                'type'=>'NUMERIC'
            ));
        }
        if( $individual_events == 'tomorrow' ){

            $tomorrow_dt = new DateTime('tomorrow');
            $tomorrow_ts = strtotime( $tomorrow_dt->format('Y-m-d H:i:s') );
            array_push($meta_query,array(
                'key' => em_append_meta_key( 'start_date' ),
                'value' => $tomorrow_ts,
                'compare' => '>=',
                'type'=>'NUMERIC'
            ));

            $tda_tomorrow_dt = new DateTime('tomorrow');
            $tda_tomorrow_dt->modify('+1 day');
            $tda_tomorrow_ts = strtotime( $tda_tomorrow_dt->format('Y-m-d H:i:s') );
            array_push($meta_query,array(
                'key' => em_append_meta_key( 'start_date' ),
                'value' => $tda_tomorrow_ts,
                'compare' => '<',
                'type'=>'NUMERIC'
            ));
        }

        if( $individual_events == 'this month' ){

            $this_month_dt = new DateTime('first day of this month');
            $this_month_ts = strtotime( $this_month_dt->format('Y-m-d 00:00:00') );
            array_push($meta_query,array(
                'key' => em_append_meta_key( 'start_date' ),
                'value' => $this_month_ts,
                'compare' => '>=',
                'type'=>'NUMERIC'
            ));

            $next_month_dt = new DateTime('first day of next month');
            $next_month_ts = strtotime( $next_month_dt->format('Y-m-d 00:00:00') );
            array_push($meta_query,array(
                'key' => em_append_meta_key( 'start_date' ),
                'value' => $next_month_ts,
                'compare' => '<',
                'type'=>'NUMERIC'
            ));
        }
       
        return $meta_query;
        
    }

    public function print_upcoming_event_block_for_performers($upcoming_events, $event_args) {
        $setting_service = EventM_Factory::get_service('EventM_Setting_Service');
        $global_settings = $setting_service->load_model_from_db();
        $today = em_current_time_by_timezone();
        $currency_symbol = em_currency_symbol();
        $display_view = $event_args->event_style;
        $posts_per_page = $event_args->event_limit;
        $event_cols = $event_args->event_cols;
        $load_more = $event_args->load_more;
        $hide_past_events = $event_args->hide_past_events;
        $events = $upcoming_events->posts; 
        $showBookNowForGuestUsers = em_show_book_now_for_guest_users();
        $recurring = 1; $column_class = ''; 
        ?>
        <div class="ep-event-type-events em_block dbfl">
            <div class="kf-row-heading">
                <span class="kf-row-title"><?php echo __('Upcoming Events', 'eventprime-event-calendar-management'); ?>
                    <span class="em_events_count-wrap em_bg"></span>
                </span>
            </div>
            <?php
            if (!empty($events)){
                $i = 1;
                if($display_view == 'card'){ ?>
                    <div class="ep-event-box-cards ep-box-row em_performer_event_cards">
                        <!-- the loop -->
                        <?php foreach ($events as $event) :
                            $eventId = isset($event->id) ? $event->id : $event->ID;
                            $event= $this->load_model_from_db($eventId);
                            if(empty($recurring) && isset($event->parent) && !empty($event->parent)){
                                continue;
                            }
                            // check for booking allowed
                            $booking_allowed = 1;
                            if((isset($event->parent) && !empty($event->parent)) && (isset($event->enable_recurrence_automatic_booking) && !empty($event->enable_recurrence_automatic_booking))){
                                // if event is recurring and parent has automatic booking enable than not allowed
                                $booking_allowed = 0;
                            }
                            $event->url = em_get_single_event_page_url($event, $global_settings);
                            ?>
                            <div class="<?php echo $column_class;?> ep-box-col-<?php echo $event_cols;?>">
                            <div class="<?php if(empty($section_id)){ echo 'ep-event-box-card'; } else{ echo 'em_card_edt';}?><?php if (em_is_event_expired($event->id)) echo 'emcard-expired'; ?> <?php echo (empty($event->enable_booking) && absint($event->custom_link_enabled) == 0) ? 'em_event_disabled' : ''; ?>">
                               
                                <div class="em_event_cover dbfl">
                                    <?php 
                                    $thumbImage = esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png');
                                    if (!empty($event->cover_image_id)): ?>
                                        <?php 
                                        $thumbImage = wp_get_attachment_image_src($event->cover_image_id, 'large')[0];
                                        if(empty($thumbImage)){
                                            $thumbImage = get_the_post_thumbnail($event->id,'large');
                                            if(isset($event->parent) && !empty($event->parent) && empty($thumbImage)){
                                                $thumbImage = get_the_post_thumbnail($event->parent,'large');
                                            }
                                        }?>
                                        <a href="<?php echo $event->url; ?>">
                                            <img src="<?php echo $thumbImage; ?>" alt="<?php _e('Event Cover Image', 'eventprime-event-calendar-management');?>">
                                        </a>
                                    <?php else: ?>
                                        <a href="<?php echo $event->url; ?>"><img src="<?php echo esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png'); ?>" alt="<?php _e('Dummy Image','eventprime-event-calendar-management'); ?>" class="em-no-image" ></a>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="dbfl em-card-description">
                                    <div class="em_event_title"  title="<?php  echo $event->name; ?>">
                                        <a href="<?php echo $event->url; ?>"><?php echo $event->name; ?></a>
                                        <?php if(is_user_logged_in()): ?>
                                            <?php do_action('event_magic_wishlist_link',$event); ?>
                                        <?php endif; ?>
                                    </div>
                                    <?php do_action('event_magic_popup_custom_data_before_details',$event);?>
                                    <?php $start_date = null; $end_date = null; $start_time = null; $end_time = null; $day = null;
                                        if (em_compare_event_dates($event->id)){
                                            $day = date_i18n(get_option('date_format'),$event->start_date);
                                            $start_time = date_i18n(get_option('time_format'),$event->start_date);
                                            $end_time = date_i18n(get_option('time_format'),$event->end_date);
                                        } else {
                                            $start_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->start_date);
                                            $end_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->end_date);
                                        }
                                    if($event->all_day):?>
                                        <div class="ep-card-event-date-wrap ep-box-row ep-box-center">
                                            <span class="ep-box-col-2"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg></span> 
                                            <div class="ep-card-event-date ep-box-col-10"><?php echo date_i18n(get_option('date_format'),$event->start_date); ?><span class="em-all-day"> - <?php _e('ALL DAY','eventprime-event-calendar-management');?></span></div>
                                        </div>
                                    <?php elseif(!empty($day)): ?>
                                        <div class="ep-card-event-date-wrap ep-box-row ep-box-center">
                                            <span class="ep-box-col-2"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg></span> 
                                            <div class="ep-card-event-date ep-box-col-10"><?php echo $day; ?></div>
                                        </div>
                                        <div class="ep-card-event-date-wrap ep-box-row ep-box-center">
                                            <span class="ep-box-col-2"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg></span>
                                            <div class="ep-card-event-date ep-box-col-10"><?php echo $start_time.'  to  '.$end_time; ?></div> 
                                        </div>
                                    <?php else: ?>
                                        <div class="ep-card-event-date-wrap ep-box-row ep-box-center">
                                           <span class="ep-box-col-2"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg></span>
                                           <div class="ep-card-event-date ep-box-col-10"><?php echo $start_date; ?> - <?php echo $end_date; ?> </div>   
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="ep-single-box-footer dbfl">
                                    <div class="em_event_price  difl">
                                        <?php 
                                        $ticket_price = $event->ticket_price;
                                        $ticket_price = apply_filters('event_magic_load_calender_ticket_price', $ticket_price, $event);
                                        // check if show one time event fees at front enable
                                        if($event->show_fixed_event_price){
                                            if($event->fixed_event_price > 0){
                                                $ticket_price = $event->fixed_event_price;
                                            }
                                        }
                                        if(!is_numeric($ticket_price)){
                                            echo $ticket_price;
                                        }
                                        else{
                                            echo !empty($ticket_price) ? em_price_with_position($ticket_price) : '';
                                        } ?>
                                    </div>
                                    <?php do_action('event_magic_card_view_after_price',$event); ?>
                                    <div class="ep-single-box-tickets-button difr">
                                        <div class="em_event_attr_box em_eventpage_register difl">
                                            <?php 
                                            if(absint($event->custom_link_enabled) == 1):?>
                                                <div class="em_header_button em_event_custom_link kf-tickets">
                                                    <a class="ep-event-custom-link" target="_blank" href="<?php echo $event->url; ?>">
                                                        <?php 
                                                        if(!empty($global_settings->hide_event_custom_link) && !is_user_logged_in()){
                                                            echo em_global_settings_button_title('Login to View');
                                                        }
                                                        else{
                                                            echo em_global_settings_button_title('Click for Details');
                                                        }?>
                                                    </a>
                                                </div>
                                            <?php
                                            elseif($this->is_bookable($event)): $current_ts = em_current_time_by_timezone();?>
                                                <?php if($event->status=='expired'):?>
                                                    <div class="em_header_button em_event_expired kf-tickets">
                                                        <?php echo em_global_settings_button_title('Bookings Expired'); ?>
                                                    </div>
                                                <?php elseif($current_ts>$event->last_booking_date): ?>
                                                    <div class="em_header_button em_booking-closed kf-tickets"><?php echo em_global_settings_button_title('Bookings Closed'); ?></div>
                                                <?php elseif($current_ts<$event->start_booking_date): ?>  
                                                    <div class="em_header_button em_not_started kf-tickets"><?php echo em_global_settings_button_title('Bookings not started yet'); ?></div>
                                                <?php else: ?>
                                                    <?php 
                                                    if(!empty($booking_allowed)):
                                                        if(is_user_logged_in() || $showBookNowForGuestUsers): ?>
                                                            <form action="<?php echo get_permalink($global_settings->booking_page); ?>" method="post" name="em_booking">
                                                                <a class="em_header_button em_event-booking kf-tickets" name="tickets" onclick="em_event_booking(<?php echo $event->id ?>)" id="em_booking"><?php echo em_global_settings_button_title('Book Now'); ?></a>
                                                                <input type="hidden" name="event_id" value="<?php echo $event->id; ?>" />
                                                                <input type="hidden" name="venue_id" value="<?php echo $event->venue; ?>" />
                                                            </form>
                                                        <?php else: ?> 
                                                            <a class="em_header_button kf-tickets" target="_blank" href="<?php echo add_query_arg('event_id',$event->id,get_permalink($global_settings->profile_page)); ?>"><?php echo em_global_settings_button_title('Book Now'); ?></a>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            <?php elseif($event->status == 'publish'): ?>
                                                <?php  if(isset($event->standing_capacity) && !empty($event->standing_capacity)):?>
                                                    <div class="em_event_attr_box em_eventpage_register difl">
                                                        <div class="em_header_button em_not_bookable kf-tickets"><?php echo em_global_settings_button_title('All Seats Booked'); ?></div>
                                                    </div>
                                                <?php else:?>
                                                    <div class="em_event_attr_box em_eventpage_register difl">
                                                        <div class="em_header_button em_not_bookable kf-tickets"><?php echo em_global_settings_button_title('Bookings Closed'); ?></div>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php do_action('event_magic_card_view_after_footer',$event); ?>
                                
                            </div></div>
                        <?php $i++; endforeach; ?>
                    </div> 
                    <?php
                    if($upcoming_events->max_num_pages > 1 && $load_more == 1):?>
                        <?php $curr_page = $upcoming_events->query_vars['paged'];?>
                        <div class="ep-view-load-more ep-view-load-more-wrap dbfl" onclick="em_load_more_performer_events_card_block('.ep-view-load-more','.ep-loading-view-btn','.em_performer_event_cards')" data-curr_page="<?php echo $curr_page?>" data-p_id="<?php echo $upcoming_events->performer_id; ?>" data-loading="<?php _e('Loading...');?>" data-loaded="<?php _e('Load More');?>" data-max_page="<?php echo $upcoming_events->max_num_pages;?>" data-show="<?php echo $posts_per_page;?>" data-cols = "<?php echo $event_cols;?>" data-recurring="<?php echo $recurring;?>">
                            <div class="ep-loading-view-btn em_color"><?php _e('Load More');?></div>
                        </div>
                    <?php endif;?>
                <?php }elseif( $display_view == 'list' ){ ?>
                    <div class="em_list_view ep-events-list-wrap em_cards" id="ms-container">
                        <div class="ep-wrap">
                            <div class="ep-event-list-standard ep-performer-event-list-standard">
                                <!-- the loop -->
                                <?php foreach ($events as $event) :
                                    $eventId = isset($event->id) ? $event->id : $event->ID;
                                    $event = $this->load_model_from_db($eventId);
                                    if(empty($recurring) && isset($event->parent) && !empty($event->parent)){
                                        continue;
                                    }
                                    $month_id = date('Ym', $event->start_date);
                                    if(empty($last_month_id) || $last_month_id != $month_id){
                                        $last_month_id = $month_id;?>
                                        <div class="ep-month-divider"><span class="ep-listed-event-month"><?php echo date_i18n('F Y', $event->start_date); ?><span class="ep-listed-event-month-tag"></span></span></div><?php
                                    }
                                    // check for booking allowed
                                    $booking_allowed = 1;
                                    if((isset($event->parent) && !empty($event->parent)) && (isset($event->enable_recurrence_automatic_booking) && !empty($event->enable_recurrence_automatic_booking))){
                                        // if event is recurring and parent has automatic booking enable than not allowed
                                        $booking_allowed = 0;
                                    }
                                    $event->url = em_get_single_event_page_url($event, $global_settings);
                                    ?>
                                    <div class="ep-event-article <?php if (em_is_event_expired($event->id)) echo 'emlist-expired'; ?> <?php echo empty($event->enable_booking) ? 'em_event_disabled' : ''; ?>">
                                        <div class="ep-topsec">
                                            <div class="em-col-3 difl ep-event-image-wrap ep-col-table-c">
                                                <div class="em_event_cover_list dbfl">
                                                    <?php 
                                                    $thumbImage = esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png');
                                                    if (!empty($event->cover_image_id)): ?>
                                                        <?php 
                                                        $thumbImage = wp_get_attachment_image_src($event->cover_image_id, 'large')[0];
                                                        if(empty($thumbImage)){
                                                            $thumbImage = get_the_post_thumbnail($event->id,'large');
                                                            if(isset($event->parent) && !empty($event->parent) && empty($thumbImage)){
                                                                $thumbImage = get_the_post_thumbnail($event->parent,'large');
                                                            }
                                                        }?>
                                                        <a href="<?php echo $event->url; ?>">
                                                            <img src="<?php echo $thumbImage; ?>" alt="<?php _e('Event Cover Image', 'eventprime-event-calendar-management');?>">
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="<?php echo $event->url; ?>"><img src="<?php echo esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png'); ?>" alt="<?php _e('Dummy Image','eventprime-event-calendar-management'); ?>" class="em-no-image" ></a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <div class="em-col-5 difl ep-col-table-c ep-event-content-wrap">
                                                <div class="ep-event-content">
                                                    <h3 class="ep-event-title"><a class="ep-color-hover" data-event-id="<?php echo $event->id;?>" href="<?php echo $event->url; ?>" target="_self"><?php  echo $event->name; ?></a>
                                                    </h3>
                                                    <?php if(is_user_logged_in()): ?>
                                                        <?php do_action('event_magic_wishlist_link',$event); ?>
                                                    <?php endif; ?>
                                                    <?php if(!empty($event->description)) { ?>
                                                        <div class="ep-event-description"><?php echo $event->description; ?></div>
                                                    <?php } ?>
                                                </div>
                                            </div>

                                            <div class="em-col-4 difl ep-col-table-c ep-event-meta-wrap">
                                                <div class="ep-event-meta ep-color-before">
                                                    <?php $start_date = null; $end_date = null; $start_time = null; $end_time = null; $day = null;
                                                    if (em_compare_event_dates($event->id)){
                                                        $day = date_i18n(get_option('date_format'),$event->start_date);
                                                        $start_time = date_i18n(get_option('time_format'),$event->start_date);
                                                        $end_time = date_i18n(get_option('time_format'),$event->end_date);
                                                    } else {
                                                        $start_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->start_date);
                                                        $end_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->end_date);
                                                    }
                                                    if($event->all_day):?>
                                                        <div class="ep-list-event-date-row">
                                                            <span class="material-icons em_color">date_range</span> 
                                                            <div class="ep-list-event-date">
                                                                <?php echo date_i18n(get_option('date_format'),$event->start_date); ?>
                                                                <span class="em-all-day"> - <?php _e('ALL DAY','eventprime-event-calendar-management');?></span>
                                                            </div>
                                                        </div>
                                                    <?php elseif(!empty($day)): ?>
                                                        <div class="ep-list-event-date-row">
                                                            <span class="material-icons em_color">date_range</span> <div class="ep-list-event-date"><?php echo $day; ?> - <?php echo $start_time.'  to  '.$end_time; ?></div>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="ep-list-event-date-row">
                                                            <span class="material-icons em_color">date_range</span> <div class="ep-list-event-date"><?php echo $start_date; ?> - <?php echo $end_date; ?> </div>   
                                                        </div>
                                                    <?php endif; ?> 
                                                    <?php 
                                                    if(!empty($event->venue)){
                                                        $venue_service= EventM_Factory::get_service('EventM_Venue_Service');
                                                        $venue= $venue_service->load_model_from_db($event->venue);
                                                        if(!empty($venue->id) && !empty($venue->address)){ ?>
                                                            <div class="em-list-view-venue-details" title="<?php echo $venue->address; ?>"><span class="ep-list-event-location"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zM7 9c0-2.76 2.24-5 5-5s5 2.24 5 5c0 2.88-2.88 7.19-5 9.88C9.92 16.21 7 11.85 7 9z"/><circle cx="12" cy="9" r="2.5"/></svg></span><div class="em-list-event-address"><span><?php echo $venue->address; ?></span></div>
                                                            </div><?php 
                                                        }
                                                    } ?> 

                                                    <?php if(!empty($event->enable_booking) && empty($event->hide_booking_status)):
                                                        $sum = $this->booked_seats($event->id);
                                                        $capacity = em_event_seating_capcity($event->id);?>  
                                                        <div class="ep-list-booking-status ep-event-attenders-main">
                                                            <div class="kf-event-attr-value dbfl"> 
                                                                <?php if ($capacity > 0): ?>
                                                                    <div class="dbfl">
                                                                        <?php echo $sum; ?> / <?php echo $capacity; ?> 
                                                                    </div>
                                                                    <?php $width = ($sum / $capacity) * 100; ?>
                                                                    <div class="dbfl ">
                                                                        <div id="progressbar" class="em_progressbar dbfl">
                                                                            <div style="width:<?php echo $width . '%'; ?>" class="em_progressbar_fill em_bg" ></div>
                                                                        </div>
                                                                    </div>
                                                                    <?php
                                                                else:
                                                                    if($sum > 0){
                                                                        echo '<div class="ep-event-attenders-wrap"><span class="material-icons em_color">person</span><span class="ep-event-attenders">' . $sum . ' </span>'.__('Attending','eventprime-event-calendar-management').'</div>';
                                                                    }?>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    <?php endif;?>
                                                    <?php do_action('event_magic_popup_custom_data_before_footer',$event);?>
                                                    <div class="ep-list-view-footer">
                                                        <div class="em_event_price difl">
                                                            <?php 
                                                            $ticket_price = $event->ticket_price;
                                                            // check if show one time event fees at front enable
                                                            if($event->show_fixed_event_price){
                                                                if($event->fixed_event_price > 0){
                                                                    $ticket_price = $event->fixed_event_price;
                                                                }
                                                            }
                                                            echo !empty($ticket_price) ? $currency_symbol.$ticket_price : ''; ?>
                                                        </div>
                                                        <?php do_action('event_magic_card_view_after_price',$event); ?>
                                                        <div class="kf-tickets-button difr">
                                                            <div class="em_event_attr_box em_eventpage_register difl">
                                                                <?php 
                                                                if(absint($event->custom_link_enabled) == 1):?>
                                                                    <div class="em_header_button em_event_custom_link kf-tickets">
                                                                        <a class="ep-event-custom-link" target="_blank" href="<?php echo $event->url; ?>">
                                                                            <?php 
                                                                            if(!empty($global_settings->hide_event_custom_link) && !is_user_logged_in()){
                                                                                echo em_global_settings_button_title('Login to View');
                                                                            }
                                                                            else{
                                                                                echo em_global_settings_button_title('Click for Details');
                                                                            }?>
                                                                        </a>
                                                                    </div>
                                                                <?php
                                                                elseif($this->is_bookable($event)): $current_ts = em_current_time_by_timezone();?>
                                                                    <?php if($event->status=='expired'):?>
                                                                        <div class="em_header_button em_event_expired kf-tickets">
                                                                            <?php echo em_global_settings_button_title('Bookings Expired'); ?>
                                                                        </div>
                                                                    <?php elseif($current_ts>$event->last_booking_date): ?>
                                                                        <div class="em_header_button em_booking-closed kf-tickets">
                                                                            <?php echo em_global_settings_button_title('Bookings Closed'); ?>
                                                                        </div>
                                                                    <?php elseif($current_ts<$event->start_booking_date): ?>  
                                                                        <div class="em_header_button em_not_started kf-tickets">
                                                                            <?php echo em_global_settings_button_title('Bookings not started yet'); ?>
                                                                        </div>
                                                                    <?php else: ?>
                                                                        <?php 
                                                                        if(!empty($booking_allowed)):
                                                                            if(is_user_logged_in() || $showBookNowForGuestUsers): ?>
                                                                                <form action="<?php echo get_permalink($global_settings->booking_page); ?>" method="post" name="em_booking">
                                                                                    <button class="em_header_button em_event-booking kf-tickets em_color" name="tickets" onclick="em_event_booking(<?php echo $event->id ?>)" id="em_booking">
                                                                                        <?php echo em_global_settings_button_title('Book Now'); ?>
                                                                                    </button>
                                                                                    <input type="hidden" name="event_id" value="<?php echo $event->id; ?>" />
                                                                                    <input type="hidden" name="venue_id" value="<?php echo $event->venue; ?>" />
                                                                                </form>
                                                                            <?php else: ?> 
                                                                                <a class="em_header_button em_event-booking kf-tickets em_color" target="_blank" href="<?php echo add_query_arg('event_id',$event->id,get_permalink($global_settings->profile_page)); ?>">
                                                                                    <?php echo em_global_settings_button_title('Book Now'); ?>
                                                                                </a>
                                                                            <?php endif; ?>
                                                                        <?php endif; ?>
                                                                    <?php endif; ?>
                                                                <?php elseif($event->status == 'publish'):?>
                                                                    <?php  if(isset($event->standing_capacity) && !empty($event->standing_capacity)):?>
                                                                        <div class="em_event_attr_box em_eventpage_register difl">
                                                                            <div class="em_header_button em_not_bookable kf-tickets">
                                                                                <?php echo em_global_settings_button_title('All Seats Booked'); ?>
                                                                            </div>
                                                                        </div>
                                                                    <?php else:?>
                                                                        <div class="em_event_attr_box em_eventpage_register difl">
                                                                            <div class="em_header_button em_not_bookable kf-tickets">
                                                                                <?php echo em_global_settings_button_title('Bookings Closed'); ?>
                                                                            </div>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php do_action('event_magic_card_view_after_footer',$event); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php  $i++; endforeach; ?>
                            </div>
                            <?php
                            if($upcoming_events->max_num_pages > 1 && $load_more == 1){
                                $curr_page = $upcoming_events->query_vars['paged'];?>
                                <div class="ep-masonry-load-more ep-masonry-load-more-wrap" onclick="em_load_more_performer_events_list_block()" data-curr_page="<?php echo $curr_page?>" data-p_id="<?php echo $upcoming_events->performer_id; ?>" data-loading="<?php _e('Loading...');?>" data-loaded="<?php _e('Load More');?>" data-max_page="<?php echo $upcoming_events->max_num_pages;?>"  data-show="<?php echo $posts_per_page;?>"  data-month_id="<?php echo $last_month_id;?>" data-recurring="<?php echo $recurring;?>"><div class="ep-load-more-button em_color"><?php _e('Load More');?></div></div><?php
                            }?>
                        </div>
                    </div>
                <?php
                }else{ ?>
                    <div class="em_event_list em_performer_event_mini_list">
                        <?php
                        foreach ( $events as $event ) {
                            $eventId = isset($event->id) ? $event->id : $event->ID;
                            $event_model = $this->load_model_from_db($eventId);
                            if(empty($recurring) && isset($event->parent) && !empty($event->parent)){
                                continue;
                            }
                            // check for booking allowed
                            $booking_allowed = 1;
                            if((isset($event->parent) && !empty($event->parent)) && (isset($event->enable_recurrence_automatic_booking) && !empty($event->enable_recurrence_automatic_booking))){
                                // if event is recurring and parent has automatic booking enable than not allowed
                                $booking_allowed = 0;
                            }
                            $event_model->url = em_get_single_event_page_url( $event_model, $global_settings );
                            $emcardEpired ='';
                            if (em_is_event_expired( $eventId )) {
                                $emcardEpired ='emcard-expired';
                            }
                            ?>
                            <div class="kf-upcoming-event-row em_block dbfl <?php echo $emcardEpired;?> <?php echo empty($event_model->enable_booking) ? 'em_event_disabled' : ''; ?>">
                                <div class="kf-upcoming-event-thumb em-col-2 difl">
                                    <a href="<?php echo $event_model->url; ?>">
                                        <?php 
                                        $thumbImage = esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png');
                                        if (!empty($event_model->cover_image_id)): ?>
                                            <?php 
                                            $thumbImage = wp_get_attachment_image_src($event_model->cover_image_id, 'large')[0];
                                            if(empty($thumbImage)){
                                                $thumbImage = get_the_post_thumbnail($event_model->id,'large');
                                                if(isset($event_model->parent) && !empty($event_model->parent) && empty($thumbImage)){
                                                    $thumbImage = get_the_post_thumbnail($event_model->parent,'large');
                                                }
                                            }?>
                                            <img src="<?php echo $thumbImage; ?>" alt="<?php _e('Event Cover Image', 'eventprime-event-calendar-management');?>">
                                        <?php else: ?>
                                            <img src="<?php echo esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png'); ?>" alt="<?php _e('Dummy Image','eventprime-event-calendar-management'); ?>" class="em-no-image" >
                                        <?php endif; ?>
                                    </a>
                                </div>
                                <div class="kf-upcoming-event-title em-col-5 em-col-pad20 difl">
                                    <a href="<?php echo $event_model->url; ?>">
                                        <?php echo $event_model->name; ?>
                                    </a>
                                    <?php if ($today>$event_model->start_date && $today<$event_model->end_date) { ?>
                                        <span class="kf-live"><?php _e('Live','eventprime-event-calendar-management'); ?></span>
                                    <?php } ?>
                                    <div class="kf-upcoming-event-post-date">
                                        <div class="em_event_start difl em_wrap">
                                        <?php echo date_i18n(get_option('date_format').' '.get_option('time_format'), $event_model->start_date); ?>
                                        <span> - </span>
                                        <?php echo date_i18n(get_option('date_format').' '.get_option('time_format'), $event_model->end_date); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="kf-upcoming-event-booking em-col-5 em-col-pad20 difr">
                                    <div class="em_header_button kf-button">
                                        <?php if ($this->is_bookable($event_model) && absint($event_model->custom_link_enabled) != 1): $current_ts = em_current_time_by_timezone(); ?>
                                            <?php if ($event_model->status=='expired'): ?>
                                            <div class="em_header_button em_not_bookable kf-tickets"><?php echo em_global_settings_button_title('Bookings Expired'); ?></div>
                                            <?php elseif ($current_ts>$event_model->last_booking_date): ?>
                                            <div class="em_header_button em_not_bookable kf-button"><?php echo em_global_settings_button_title('Bookings Closed'); ?></div>
                                            <?php elseif($current_ts<$event_model->start_booking_date): ?>  
                                            <div class="em_header_button em_not_bookable kf-button"><?php echo em_global_settings_button_title('Bookings not started yet'); ?></div>
                                            <?php else: ?>
                                                <?php if(is_user_logged_in() || $showBookNowForGuestUsers): ?>
                                                    <form action="<?php echo get_permalink($global_settings->booking_page); ?>" method="post" name="em_booking">
                                                        <button class="em_header_button kf-button em_color" name="tickets" onclick="em_event_booking(<?php echo $event_model->id ?>)" class="em_header_button" id="em_booking">
                                                            <i class="fa fa-ticket" aria-hidden="true"></i>
                                                            <?php
                                                            echo em_global_settings_button_title('Book Now');
                                                            if ($event_model->ticket_price > 0){
                                                                $ticketPrice = $event_model->ticket_price;
                                                                // check if show one time event fees at front enable
                                                                if($event_model->show_fixed_event_price){
                                                                    if($event_model->fixed_event_price > 0){
                                                                        $ticketPrice = $event_model->fixed_event_price;
                                                                    }
                                                                }
                                                                if ($ticketPrice > 0){
                                                                    echo " - " . '<span class="em_event_price">' . em_price_with_position($ticketPrice, $currency_symbol) . '</span>';
                                                                }
                                                                do_action('event_magic_single_event_ticket_price_after', $event_model, $ticketPrice);
                                                            }
                                                            ?>
                                                        </button>
                                                        <input type="hidden" name="event_id" value="<?php echo $event_model->id; ?>" />
                                                        <input type="hidden" name="venue_id" value="<?php echo $event_model->venue; ?>" />
                                                    </form>
                                                    <?php else: ?>
                                                        <a class="em_header_button kf-button em_color" target="_blank" href="<?php echo add_query_arg('event_id',$event_model->id, get_permalink($global_settings->profile_page)); ?>"><?php echo em_global_settings_button_title('Book Now'); ?></a>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            <?php elseif(absint($event_model->custom_link_enabled) != 1): ?>
                                            <div class="em_event_attr_box em_eventpage_register difl">
                                                <div class="em_header_button em_not_bookable kf-button">
                                                    <?php echo em_global_settings_button_title('Bookings Closed'); ?>
                                                </div>
                                            </div>
                                        <?php endif; ?> 
                                    </div>
                                </div>
                            </div>
                        <?php
                        $i++;  }
                        ?>
                    </div>
                    <?php if($upcoming_events->max_num_pages > 1 && $load_more == 1){
                        $curr_page = $upcoming_events->query_vars['paged']; ?>
                        <div class="ep-view-load-more ep-view-load-more-wrap dbfl" onclick="em_load_more_performer_events_mini_list_block('.ep-view-load-more','.ep-loading-view-btn','.em_performer_event_mini_list')" data-curr_page="<?php echo $curr_page?>" data-p_id="<?php echo $upcoming_events->performer_id; ?>" data-loading="<?php _e('Loading...');?>" data-loaded="<?php _e('Load More');?>" data-max_page="<?php echo $upcoming_events->max_num_pages;?>" data-show="<?php echo $posts_per_page;?>" data-cols = "<?php echo $event_cols;?>" data-recurring="<?php echo $recurring;?>">
                            <div class="ep-loading-view-btn em_color"><?php _e('Load More');?></div>
                        </div>
                      <?php
                    }
                }
            }else {
                if($_POST){ ?>
                    <article>
                        <p><?php _e('No events match your criterion.','eventprime-event-calendar-management'); ?></p>
                    </article>
                <?php }else{ ?>
                    <article>
                        <p><?php _e('There are no Events available right now.','eventprime-event-calendar-management'); ?></p>
                    </article>
                <?php } 
            } ?>
        </div>
        <?php 
    }

    public function upcoming_events_for_type( $type_id, $args ){
        $results = $this->dao->get_upcoming_events_for_type( $type_id, $args );
        return $results;
    }

    public function print_upcoming_event_block_for_types( $upcoming_events, $event_args ) {
        $setting_service = EventM_Factory::get_service( 'EventM_Setting_Service' );
        $global_settings = $setting_service->load_model_from_db();
        $today = em_current_time_by_timezone();
        $currency_symbol = em_currency_symbol();
        $display_view = $event_args->event_style;
        $posts_per_page = $event_args->event_limit;
        $event_cols = $event_args->event_cols;
        $load_more = $event_args->load_more;
        $hide_past_events = $event_args->hide_past_events;
        $events = $upcoming_events->posts; 
        $showBookNowForGuestUsers = em_show_book_now_for_guest_users();
        $recurring = 1; $column_class = ''; 
        ?>
        <div class="ep-event-type-events em_block dbfl">
            <div class="kf-row-heading">
                <span class="kf-row-title"><?php echo __( 'Upcoming Events', 'eventprime-event-calendar-management' ); ?>
                    <span class="em_events_count-wrap em_bg">
                        <?php /* echo '<span class="em_events_count_no em_color">' . count($events) . '</span>'; */ ?>
                    </span>
                </span>
            </div>
            <?php
            if (!empty($events)){
                $i = 1;
                if($display_view == 'card'){ ?>
                    <div class="ep-event-box-cards ep-box-row em_performer_event_cards">
                        <!-- the loop -->
                        <?php foreach ($events as $event) :
                            $eventId = isset($event->id) ? $event->id : $event->ID;
                            $event= $this->load_model_from_db($eventId);
                            if(empty($recurring) && isset($event->parent) && !empty($event->parent)){
                                continue;
                            }
                            // check for booking allowed
                            $booking_allowed = 1;
                            if((isset($event->parent) && !empty($event->parent)) && (isset($event->enable_recurrence_automatic_booking) && !empty($event->enable_recurrence_automatic_booking))){
                                // if event is recurring and parent has automatic booking enable than not allowed
                                $booking_allowed = 0;
                            }
                            $event->url = em_get_single_event_page_url($event, $global_settings);
                            ?>
                        <div class="<?php echo $column_class;?> ep-box-col-<?php echo $event_cols;?>">
                            <div class="<?php if(empty($section_id)){ echo 'ep-event-box-card'; } else{ echo 'em_card_edt';}?><?php if (em_is_event_expired($event->id)) echo 'emcard-expired'; ?> <?php echo (empty($event->enable_booking) && absint($event->custom_link_enabled) == 0) ? 'em_event_disabled' : ''; ?>">
                               
                                <div class="em_event_cover dbfl">
                                    <?php 
                                    $thumbImage = esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png');
                                    if (!empty($event->cover_image_id)): ?>
                                        <?php 
                                        $thumbImage = wp_get_attachment_image_src($event->cover_image_id, 'large')[0];
                                        if(empty($thumbImage)){
                                            $thumbImage = get_the_post_thumbnail($event->id,'large');
                                            if(isset($event->parent) && !empty($event->parent) && empty($thumbImage)){
                                                $thumbImage = get_the_post_thumbnail($event->parent,'large');
                                            }
                                        }?>
                                        <a href="<?php echo $event->url; ?>">
                                            <img src="<?php echo $thumbImage; ?>" alt="<?php _e('Event Cover Image', 'eventprime-event-calendar-management');?>">
                                        </a>
                                    <?php else: ?>
                                        <a href="<?php echo $event->url; ?>"><img src="<?php echo esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png'); ?>" alt="<?php _e('Dummy Image','eventprime-event-calendar-management'); ?>" class="em-no-image" ></a>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="dbfl em-card-description">
                                    <div class="em_event_title"  title="<?php  echo $event->name; ?>">
                                        <a href="<?php echo $event->url; ?>"><?php echo $event->name; ?></a>
                                        <?php if(is_user_logged_in()): ?>
                                            <?php do_action('event_magic_wishlist_link',$event); ?>
                                        <?php endif; ?>
                                    </div>
                                    <?php do_action('event_magic_popup_custom_data_before_details',$event);?>
                                    <?php $start_date = null; $end_date = null; $start_time = null; $end_time = null; $day = null;
                                        if (em_compare_event_dates($event->id)){
                                            $day = date_i18n(get_option('date_format'),$event->start_date);
                                            $start_time = date_i18n(get_option('time_format'),$event->start_date);
                                            $end_time = date_i18n(get_option('time_format'),$event->end_date);
                                        } else {
                                            $start_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->start_date);
                                            $end_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->end_date);
                                        }
                                    if($event->all_day):?>
                                        <div class="ep-card-event-date-wrap ep-box-row ep-box-center">
                                            <span class="ep-box-col-2"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg></span> 
                                            <div class="ep-card-event-date ep-box-col-10"><?php echo date_i18n(get_option('date_format'),$event->start_date); ?><span class="em-all-day"> - <?php _e('ALL DAY','eventprime-event-calendar-management');?></span></div>
                                        </div>
                                    <?php elseif(!empty($day)): ?>
                                        <div class="ep-card-event-date-wrap ep-box-row ep-box-center">
                                            <span class="ep-box-col-2"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg></span> 
                                            <div class="ep-card-event-date ep-box-col-10"><?php echo $day; ?></div>
                                        </div>
                                        <div class="ep-card-event-date-wrap ep-box-row ep-box-center">
                                            <span class="ep-box-col-2"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg></span>
                                            <div class="ep-card-event-date ep-box-col-10"><?php echo $start_time.'  to  '.$end_time; ?></div> 
                                        </div>
                                    <?php else: ?>
                                        <div class="ep-card-event-date-wrap ep-box-row ep-box-center">
                                           <span class="ep-box-col-2"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg></span>
                                           <div class="ep-card-event-date ep-box-col-10"><?php echo $start_date; ?> - <?php echo $end_date; ?> </div>   
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="ep-single-box-footer dbfl">
                                    <div class="em_event_price  difl">
                                        <?php 
                                        $ticket_price = $event->ticket_price;
                                        $ticket_price = apply_filters('event_magic_load_calender_ticket_price', $ticket_price, $event);
                                        // check if show one time event fees at front enable
                                        if($event->show_fixed_event_price){
                                            if($event->fixed_event_price > 0){
                                                $ticket_price = $event->fixed_event_price;
                                            }
                                        }
                                        if(!is_numeric($ticket_price)){
                                            echo $ticket_price;
                                        }
                                        else{
                                            echo !empty($ticket_price) ? em_price_with_position($ticket_price) : '';
                                        } ?>
                                    </div>
                                    <?php do_action('event_magic_card_view_after_price',$event); ?>
                                    <div class="ep-single-box-tickets-button difr">
                                        <div class="em_event_attr_box em_eventpage_register difl">
                                            <?php 
                                            if(absint($event->custom_link_enabled) == 1):?>
                                                <div class="em_header_button em_event_custom_link kf-tickets">
                                                    <a class="ep-event-custom-link" target="_blank" href="<?php echo $event->url; ?>">
                                                        <?php 
                                                        if(!empty($global_settings->hide_event_custom_link) && !is_user_logged_in()){
                                                            echo em_global_settings_button_title('Login to View');
                                                        }
                                                        else{
                                                            echo em_global_settings_button_title('Click for Details');
                                                        }?>
                                                    </a>
                                                </div>
                                            <?php
                                            elseif($this->is_bookable($event)): $current_ts = em_current_time_by_timezone();?>
                                                <?php if($event->status=='expired'):?>
                                                    <div class="em_header_button em_event_expired kf-tickets">
                                                        <?php echo em_global_settings_button_title('Bookings Expired'); ?>
                                                    </div>
                                                <?php elseif($current_ts>$event->last_booking_date): ?>
                                                    <div class="em_header_button em_booking-closed kf-tickets"><?php echo em_global_settings_button_title('Bookings Closed'); ?></div>
                                                <?php elseif($current_ts<$event->start_booking_date): ?>  
                                                    <div class="em_header_button em_not_started kf-tickets"><?php echo em_global_settings_button_title('Bookings not started yet'); ?></div>
                                                <?php else: ?>
                                                    <?php 
                                                    if(!empty($booking_allowed)):
                                                        if(is_user_logged_in() || $showBookNowForGuestUsers): ?>
                                                            <form action="<?php echo get_permalink($global_settings->booking_page); ?>" method="post" name="em_booking">
                                                                <a class="em_header_button em_event-booking kf-tickets" name="tickets" onclick="em_event_booking(<?php echo $event->id ?>)" id="em_booking"><?php echo em_global_settings_button_title('Book Now'); ?></a>
                                                                <input type="hidden" name="event_id" value="<?php echo $event->id; ?>" />
                                                                <input type="hidden" name="venue_id" value="<?php echo $event->venue; ?>" />
                                                            </form>
                                                        <?php else: ?> 
                                                            <a class="em_header_button kf-tickets" target="_blank" href="<?php echo add_query_arg('event_id',$event->id,get_permalink($global_settings->profile_page)); ?>"><?php echo em_global_settings_button_title('Book Now'); ?></a>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            <?php elseif($event->status == 'publish'): ?>
                                                <?php  if(isset($event->standing_capacity) && !empty($event->standing_capacity)):?>
                                                    <div class="em_event_attr_box em_eventpage_register difl">
                                                        <div class="em_header_button em_not_bookable kf-tickets"><?php echo em_global_settings_button_title('All Seats Booked'); ?></div>
                                                    </div>
                                                <?php else:?>
                                                    <div class="em_event_attr_box em_eventpage_register difl">
                                                        <div class="em_header_button em_not_bookable kf-tickets"><?php echo em_global_settings_button_title('Bookings Closed'); ?></div>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php do_action('event_magic_card_view_after_footer',$event); ?>
                                
                            </div></div>
                        <?php $i++; endforeach; ?>
                        </div> 
                        <?php
                        if($upcoming_events->max_num_pages > 1 && $load_more == 1):?>
                        <?php $curr_page = $upcoming_events->query_vars['paged'];?>
                            <div class="ep-view-load-more ep-view-load-more-wrap dbfl" onclick="em_load_more_type_events_card_block('.ep-view-load-more','.ep-loading-view-btn','.em_performer_event_cards')" data-curr_page="<?php echo $curr_page?>" data-p_id="<?php echo $upcoming_events->type_id; ?>" data-loading="<?php _e('Loading...');?>" data-loaded="<?php _e('Load More');?>" data-max_page="<?php echo $upcoming_events->max_num_pages;?>" data-show="<?php echo $posts_per_page;?>" data-cols = "<?php echo $event_cols;?>" data-recurring="<?php echo $recurring;?>">
                                <div class="ep-loading-view-btn em_color"><?php _e('Load More');?></div>
                            </div>
                        <?php endif;?>
                <?php }elseif( $display_view == 'list' ){ ?>
                    <div class="em_list_view ep-events-list-wrap em_cards" id="ms-container">
                        <div class="ep-wrap">
                            <div class="ep-event-list-standard ep-performer-event-list-standard">
                                <!-- the loop -->
                                <?php foreach ($events as $event) :
                                    $eventId = isset($event->id) ? $event->id : $event->ID;
                                    $event = $this->load_model_from_db($eventId);
                                    if(empty($recurring) && isset($event->parent) && !empty($event->parent)){
                                        continue;
                                    }
                                    $month_id = date('Ym', $event->start_date);
                                    if(empty($last_month_id) || $last_month_id != $month_id){
                                        $last_month_id = $month_id;?>
                                        <div class="ep-month-divider"><span class="ep-listed-event-month"><?php echo date_i18n('F Y', $event->start_date); ?><span class="ep-listed-event-month-tag"></span></span></div><?php
                                    }
                                    // check for booking allowed
                                    $booking_allowed = 1;
                                    if((isset($event->parent) && !empty($event->parent)) && (isset($event->enable_recurrence_automatic_booking) && !empty($event->enable_recurrence_automatic_booking))){
                                        // if event is recurring and parent has automatic booking enable than not allowed
                                        $booking_allowed = 0;
                                    }
                                    $event->url = em_get_single_event_page_url($event, $global_settings);
                                    ?>
                                    <div class="ep-event-article <?php if (em_is_event_expired($event->id)) echo 'emlist-expired'; ?> <?php echo empty($event->enable_booking) ? 'em_event_disabled' : ''; ?>">
                                        <div class="ep-topsec">
                                            <div class="em-col-3 difl ep-event-image-wrap ep-col-table-c">
                                                <div class="em_event_cover_list dbfl">
                                                    <?php 
                                                    $thumbImage = esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png');
                                                    if (!empty($event->cover_image_id)): ?>
                                                        <?php 
                                                        $thumbImage = wp_get_attachment_image_src($event->cover_image_id, 'large')[0];
                                                        if(empty($thumbImage)){
                                                            $thumbImage = get_the_post_thumbnail($event->id,'large');
                                                            if(isset($event->parent) && !empty($event->parent) && empty($thumbImage)){
                                                                $thumbImage = get_the_post_thumbnail($event->parent,'large');
                                                            }
                                                        }?>
                                                        <a href="<?php echo $event->url; ?>">
                                                            <img src="<?php echo $thumbImage; ?>" alt="<?php _e('Event Cover Image', 'eventprime-event-calendar-management');?>">
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="<?php echo $event->url; ?>"><img src="<?php echo esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png'); ?>" alt="<?php _e('Dummy Image','eventprime-event-calendar-management'); ?>" class="em-no-image" ></a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <div class="em-col-5 difl ep-col-table-c ep-event-content-wrap">
                                                <div class="ep-event-content">
                                                    <h3 class="ep-event-title"><a class="ep-color-hover" data-event-id="<?php echo $event->id;?>" href="<?php echo $event->url; ?>" target="_self"><?php  echo $event->name; ?></a>
                                                    </h3>
                                                    <?php if(is_user_logged_in()): ?>
                                                        <?php do_action('event_magic_wishlist_link',$event); ?>
                                                    <?php endif; ?>
                                                    <?php if(!empty($event->description)) { ?>
                                                        <div class="ep-event-description"><?php echo $event->description; ?></div>
                                                    <?php } ?>
                                                </div>
                                            </div>

                                            <div class="em-col-4 difl ep-col-table-c ep-event-meta-wrap">
                                                <div class="ep-event-meta ep-color-before">
                                                    <?php $start_date = null; $end_date = null; $start_time = null; $end_time = null; $day = null;
                                                    if (em_compare_event_dates($event->id)){
                                                        $day = date_i18n(get_option('date_format'),$event->start_date);
                                                        $start_time = date_i18n(get_option('time_format'),$event->start_date);
                                                        $end_time = date_i18n(get_option('time_format'),$event->end_date);
                                                    } else {
                                                        $start_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->start_date);
                                                        $end_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->end_date);
                                                    }
                                                    if($event->all_day):?>
                                                        <div class="ep-list-event-date-row">
                                                            <span class="material-icons em_color">date_range</span> 
                                                            <div class="ep-list-event-date">
                                                                <?php echo date_i18n(get_option('date_format'),$event->start_date); ?>
                                                                <span class="em-all-day"> - <?php _e('ALL DAY','eventprime-event-calendar-management');?></span>
                                                            </div>
                                                        </div>
                                                    <?php elseif(!empty($day)): ?>
                                                        <div class="ep-list-event-date-row">
                                                            <span class="material-icons em_color">date_range</span> <div class="ep-list-event-date"><?php echo $day; ?> - <?php echo $start_time.'  to  '.$end_time; ?></div>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="ep-list-event-date-row">
                                                            <span class="material-icons em_color">date_range</span> <div class="ep-list-event-date"><?php echo $start_date; ?> - <?php echo $end_date; ?> </div>   
                                                        </div>
                                                    <?php endif; ?> 
                                                    <?php 
                                                    if(!empty($event->venue)){
                                                        $venue_service= EventM_Factory::get_service('EventM_Venue_Service');
                                                        $venue= $venue_service->load_model_from_db($event->venue);
                                                        if(!empty($venue->id) && !empty($venue->address)){ ?>
                                                            <div class="em-list-view-venue-details" title="<?php echo $venue->address; ?>"><span class="ep-list-event-location"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zM7 9c0-2.76 2.24-5 5-5s5 2.24 5 5c0 2.88-2.88 7.19-5 9.88C9.92 16.21 7 11.85 7 9z"/><circle cx="12" cy="9" r="2.5"/></svg></span><div class="em-list-event-address"><span><?php echo $venue->address; ?></span></div>
                                                            </div><?php 
                                                        }
                                                    } ?> 

                                                    <?php if(!empty($event->enable_booking) && empty($event->hide_booking_status)):
                                                        $sum = $this->booked_seats($event->id);
                                                        $capacity = em_event_seating_capcity($event->id);?>  
                                                        <div class="ep-list-booking-status ep-event-attenders-main">
                                                            <div class="kf-event-attr-value dbfl"> 
                                                                <?php if ($capacity > 0): ?>
                                                                    <div class="dbfl">
                                                                        <?php echo $sum; ?> / <?php echo $capacity; ?> 
                                                                    </div>
                                                                    <?php $width = ($sum / $capacity) * 100; ?>
                                                                    <div class="dbfl ">
                                                                        <div id="progressbar" class="em_progressbar dbfl">
                                                                            <div style="width:<?php echo $width . '%'; ?>" class="em_progressbar_fill em_bg" ></div>
                                                                        </div>
                                                                    </div>
                                                                    <?php
                                                                else:
                                                                    if($sum > 0){
                                                                        echo '<div class="ep-event-attenders-wrap"><span class="material-icons em_color">person</span><span class="ep-event-attenders">' . $sum . ' </span>'.__('Attending','eventprime-event-calendar-management').'</div>';
                                                                    }?>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    <?php endif;?>
                                                    <?php do_action('event_magic_popup_custom_data_before_footer',$event);?>
                                                    <div class="ep-list-view-footer">
                                                        <div class="em_event_price difl">
                                                            <?php 
                                                            $ticket_price = $event->ticket_price;
                                                            // check if show one time event fees at front enable
                                                            if($event->show_fixed_event_price){
                                                                if($event->fixed_event_price > 0){
                                                                    $ticket_price = $event->fixed_event_price;
                                                                }
                                                            }
                                                            echo !empty($ticket_price) ? $currency_symbol.$ticket_price : ''; ?>
                                                        </div>
                                                        <?php do_action('event_magic_card_view_after_price',$event); ?>
                                                        <div class="kf-tickets-button difr">
                                                            <div class="em_event_attr_box em_eventpage_register difl">
                                                                <?php 
                                                                if(absint($event->custom_link_enabled) == 1):?>
                                                                    <div class="em_header_button em_event_custom_link kf-tickets">
                                                                        <a class="ep-event-custom-link" target="_blank" href="<?php echo $event->url; ?>">
                                                                            <?php 
                                                                            if(!empty($global_settings->hide_event_custom_link) && !is_user_logged_in()){
                                                                                echo em_global_settings_button_title('Login to View');
                                                                            }
                                                                            else{
                                                                                echo em_global_settings_button_title('Click for Details');
                                                                            }?>
                                                                        </a>
                                                                    </div>
                                                                <?php
                                                                elseif($this->is_bookable($event)): $current_ts = em_current_time_by_timezone();?>
                                                                    <?php if($event->status=='expired'):?>
                                                                        <div class="em_header_button em_event_expired kf-tickets">
                                                                            <?php echo em_global_settings_button_title('Bookings Expired'); ?>
                                                                        </div>
                                                                    <?php elseif($current_ts>$event->last_booking_date): ?>
                                                                        <div class="em_header_button em_booking-closed kf-tickets">
                                                                            <?php echo em_global_settings_button_title('Bookings Closed'); ?>
                                                                        </div>
                                                                    <?php elseif($current_ts<$event->start_booking_date): ?>  
                                                                        <div class="em_header_button em_not_started kf-tickets">
                                                                            <?php echo em_global_settings_button_title('Bookings not started yet'); ?>
                                                                        </div>
                                                                    <?php else: ?>
                                                                        <?php 
                                                                        if(!empty($booking_allowed)):
                                                                            if(is_user_logged_in() || $showBookNowForGuestUsers): ?>
                                                                                <form action="<?php echo get_permalink($global_settings->booking_page); ?>" method="post" name="em_booking">
                                                                                    <button class="em_header_button em_event-booking kf-tickets em_color" name="tickets" onclick="em_event_booking(<?php echo $event->id ?>)" id="em_booking">
                                                                                        <?php echo em_global_settings_button_title('Book Now'); ?>
                                                                                    </button>
                                                                                    <input type="hidden" name="event_id" value="<?php echo $event->id; ?>" />
                                                                                    <input type="hidden" name="venue_id" value="<?php echo $event->venue; ?>" />
                                                                                </form>
                                                                            <?php else: ?> 
                                                                                <a class="em_header_button em_event-booking kf-tickets em_color" target="_blank" href="<?php echo add_query_arg('event_id',$event->id,get_permalink($global_settings->profile_page)); ?>">
                                                                                    <?php echo em_global_settings_button_title('Book Now'); ?>
                                                                                </a>
                                                                            <?php endif; ?>
                                                                        <?php endif; ?>
                                                                    <?php endif; ?>
                                                                <?php elseif($event->status == 'publish'):?>
                                                                    <?php  if(isset($event->standing_capacity) && !empty($event->standing_capacity)):?>
                                                                        <div class="em_event_attr_box em_eventpage_register difl">
                                                                            <div class="em_header_button em_not_bookable kf-tickets">
                                                                                <?php echo em_global_settings_button_title('All Seats Booked'); ?>
                                                                            </div>
                                                                        </div>
                                                                    <?php else:?>
                                                                        <div class="em_event_attr_box em_eventpage_register difl">
                                                                            <div class="em_header_button em_not_bookable kf-tickets">
                                                                                <?php echo em_global_settings_button_title('Bookings Closed'); ?>
                                                                            </div>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php do_action('event_magic_card_view_after_footer',$event); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php  $i++; endforeach; ?>
                            </div>
                            <?php
                            if($upcoming_events->max_num_pages > 1 && $load_more == 1){
                                $curr_page = $upcoming_events->query_vars['paged'];?>
                                <div class="ep-masonry-load-more ep-masonry-load-more-wrap" onclick="em_load_more_type_events_list_block()" data-curr_page="<?php echo $curr_page?>" data-p_id="<?php echo $upcoming_events->type_id; ?>" data-loading="<?php _e('Loading...');?>" data-loaded="<?php _e('Load More');?>" data-max_page="<?php echo $upcoming_events->max_num_pages;?>"  data-show="<?php echo $posts_per_page;?>"  data-month_id="<?php echo $last_month_id;?>" data-recurring="<?php echo $recurring;?>"><div class="ep-load-more-button em_color"><?php _e('Load More');?></div></div><?php
                            }?>
                        </div>
                    </div>
                <?php
                }else{ ?>
                    <div class="em_event_list em_performer_event_mini_list">
                        <?php
                            foreach ( $events as $event ) {
                                $eventId = isset($event->id) ? $event->id : $event->ID;
                                $event_model = $this->load_model_from_db($eventId);
                                if(empty($recurring) && isset($event->parent) && !empty($event->parent)){
                                    continue;
                                }
                                // check for booking allowed
                                $booking_allowed = 1;
                                if((isset($event->parent) && !empty($event->parent)) && (isset($event->enable_recurrence_automatic_booking) && !empty($event->enable_recurrence_automatic_booking))){
                                    // if event is recurring and parent has automatic booking enable than not allowed
                                    $booking_allowed = 0;
                                }
                                $event_model->url = em_get_single_event_page_url( $event_model, $global_settings );
                                $emcardEpired ='';
                                if (em_is_event_expired($eventId)) {
                                    $emcardEpired ='emcard-expired';
                                }
                                ?>
                                <div class="kf-upcoming-event-row em_block dbfl <?php echo $emcardEpired;?> <?php echo empty($event_model->enable_booking) ? 'em_event_disabled' : ''; ?>">
                                    <div class="kf-upcoming-event-thumb em-col-2 difl">
                                        <a href="<?php echo $event_model->url; ?>">
                                            <?php 
                                            $thumbImage = esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png');
                                            if (!empty($event_model->cover_image_id)): ?>
                                                <?php 
                                                $thumbImage = wp_get_attachment_image_src($event_model->cover_image_id, 'large')[0];
                                                if(empty($thumbImage)){
                                                    $thumbImage = get_the_post_thumbnail($event_model->id,'large');
                                                    if(isset($event_model->parent) && !empty($event_model->parent) && empty($thumbImage)){
                                                        $thumbImage = get_the_post_thumbnail($event_model->parent,'large');
                                                    }
                                                }?>
                                                <img src="<?php echo $thumbImage; ?>" alt="<?php _e('Event Cover Image', 'eventprime-event-calendar-management');?>">
                                            <?php else: ?>
                                                <img src="<?php echo esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png'); ?>" alt="<?php _e('Dummy Image','eventprime-event-calendar-management'); ?>" class="em-no-image" >
                                            <?php endif; ?>
                                        </a>
                                    </div>
                                    <div class="kf-upcoming-event-title em-col-5 em-col-pad20 difl">
                                        <a href="<?php echo $event_model->url; ?>">
                                            <?php echo $event_model->name; ?>
                                        </a>
                                        <?php if ($today>$event_model->start_date && $today<$event_model->end_date) { ?>
                                            <span class="kf-live"><?php _e('Live','eventprime-event-calendar-management'); ?></span>
                                        <?php } ?>
                                        <div class="kf-upcoming-event-post-date">
                                            <div class="em_event_start difl em_wrap">
                                            <?php echo date_i18n(get_option('date_format').' '.get_option('time_format'), $event_model->start_date); ?>
                                            <span> - </span>
                                            <?php echo date_i18n(get_option('date_format').' '.get_option('time_format'), $event_model->end_date); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="kf-upcoming-event-booking em-col-5 em-col-pad20 difr">
                                        <div class="em_header_button kf-button">
                                            <?php if ($this->is_bookable($event_model) && absint($event_model->custom_link_enabled) != 1): $current_ts = em_current_time_by_timezone(); ?>
                                                <?php if ($event_model->status=='expired'): ?>
                                                <div class="em_header_button em_not_bookable kf-tickets"><?php echo em_global_settings_button_title('Bookings Expired'); ?></div>
                                                <?php elseif ($current_ts>$event_model->last_booking_date): ?>
                                                <div class="em_header_button em_not_bookable kf-button"><?php echo em_global_settings_button_title('Bookings Closed'); ?></div>
                                                <?php elseif($current_ts<$event_model->start_booking_date): ?>  
                                                <div class="em_header_button em_not_bookable kf-button"><?php echo em_global_settings_button_title('Bookings not started yet'); ?></div>
                                                <?php else: ?>
                                                    <?php if(is_user_logged_in() || $showBookNowForGuestUsers): ?>
                                                        <form action="<?php echo get_permalink($global_settings->booking_page); ?>" method="post" name="em_booking">
                                                            <button class="em_header_button kf-button em_color" name="tickets" onclick="em_event_booking(<?php echo $event_model->id ?>)" class="em_header_button" id="em_booking">
                                                                <i class="fa fa-ticket" aria-hidden="true"></i>
                                                                <?php
                                                                echo em_global_settings_button_title('Book Now');
                                                                if ($event_model->ticket_price > 0){
                                                                    $ticketPrice = $event_model->ticket_price;
                                                                    // check if show one time event fees at front enable
                                                                    if($event_model->show_fixed_event_price){
                                                                        if($event_model->fixed_event_price > 0){
                                                                            $ticketPrice = $event_model->fixed_event_price;
                                                                        }
                                                                    }
                                                                    if ($ticketPrice > 0){
                                                                        echo " - " . '<span class="em_event_price">' . em_price_with_position($ticketPrice, $currency_symbol) . '</span>';
                                                                    }
                                                                    do_action('event_magic_single_event_ticket_price_after', $event_model, $ticketPrice);
                                                                }
                                                                ?>
                                                            </button>
                                                            <input type="hidden" name="event_id" value="<?php echo $event_model->id; ?>" />
                                                            <input type="hidden" name="venue_id" value="<?php echo $event_model->venue; ?>" />
                                                        </form>
                                                        <?php else: ?>
                                                            <a class="em_header_button kf-button em_color" target="_blank" href="<?php echo add_query_arg('event_id',$event_model->id, get_permalink($global_settings->profile_page)); ?>"><?php echo em_global_settings_button_title('Book Now'); ?></a>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                <?php elseif(absint($event_model->custom_link_enabled) != 1): ?>
                                                <div class="em_event_attr_box em_eventpage_register difl">
                                                    <div class="em_header_button em_not_bookable kf-button">
                                                        <?php echo em_global_settings_button_title('Bookings Closed'); ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?> 
                                        </div>
                                    </div>
                                </div>
                            <?php
                            $i++;  }
                      ?>
                    </div>
                    <?php if($upcoming_events->max_num_pages > 1 && $load_more == 1){
                        $curr_page = $upcoming_events->query_vars['paged']; ?>
                        <div class="ep-view-load-more ep-view-load-more-wrap dbfl" onclick="em_load_more_type_events_mini_list_block('.ep-view-load-more','.ep-loading-view-btn','.em_performer_event_mini_list')" data-curr_page="<?php echo $curr_page?>" data-p_id="<?php echo $upcoming_events->type_id; ?>" data-loading="<?php _e('Loading...');?>" data-loaded="<?php _e('Load More');?>" data-max_page="<?php echo $upcoming_events->max_num_pages;?>" data-show="<?php echo $posts_per_page;?>" data-cols = "<?php echo $event_cols;?>" data-recurring="<?php echo $recurring;?>">
                            <div class="ep-loading-view-btn em_color"><?php _e('Load More');?></div>
                        </div>
                      <?php
                    }
                }
            }else {
                if($_POST){ ?>
                    <article>
                        <p><?php _e('No events match your criterion.','eventprime-event-calendar-management'); ?></p>
                    </article>
                <?php }else{ ?>
                    <article>
                        <p><?php _e('There are no Events available right now.','eventprime-event-calendar-management'); ?></p>
                    </article>
                <?php } 
            } ?>
        </div>
        <?php 
    }

    public function print_upcoming_event_block_for_organizers( $upcoming_events, $event_args ) {
        $setting_service = EventM_Factory::get_service( 'EventM_Setting_Service' );
        $global_settings = $setting_service->load_model_from_db();
        $today = em_current_time_by_timezone();
        $currency_symbol = em_currency_symbol();
        $display_view = $event_args->event_style;
        $posts_per_page = $event_args->event_limit;
        $event_cols = $event_args->event_cols;
        $load_more = $event_args->load_more;
        $hide_past_events = $event_args->hide_past_events;
        $events = $upcoming_events->posts; 
        $showBookNowForGuestUsers = em_show_book_now_for_guest_users();
        $recurring = 1; $column_class = ''; 
        ?>
        <div class="ep-event-type-events em_block dbfl">
            <div class="kf-row-heading">
                <span class="kf-row-title"><?php echo __( 'Upcoming Events', 'eventprime-event-calendar-management' ); ?>
                    <span class="em_events_count-wrap em_bg">
                        <?php /* echo '<span class="em_events_count_no em_color">' . count($events) . '</span>'; */ ?>
                    </span>
                </span>
            </div>
        <?php  
        if ( ! empty( $events ) ){
            $i = 1;
            if( $display_view == 'card' ){ ?>
                <div class="ep-event-box-cards ep-box-row em_organizer_event_cards">
                    <?php foreach ($events as $event) :
                        $eventId = isset($event->id) ? $event->id : $event->ID;
                        $event= $this->load_model_from_db($eventId);
                        if(empty($recurring) && isset($event->parent) && !empty($event->parent)){
                            continue;
                        }
                        // check for booking allowed
                        $booking_allowed = 1;
                        if((isset($event->parent) && !empty($event->parent)) && (isset($event->enable_recurrence_automatic_booking) && !empty($event->enable_recurrence_automatic_booking))){
                            // if event is recurring and parent has automatic booking enable than not allowed
                            $booking_allowed = 0;
                        }
                        $event->url = em_get_single_event_page_url($event, $global_settings);
                        ?>
                        <div class="<?php echo $column_class;?> ep-box-col-<?php echo $event_cols;?>">
                        <div class="<?php if(empty($section_id)){ echo 'ep-event-box-card'; } else{ echo 'em_card_edt';}?><?php if (em_is_event_expired($event->id)) echo 'emcard-expired'; ?> <?php echo (empty($event->enable_booking) && absint($event->custom_link_enabled) == 0) ? 'em_event_disabled' : ''; ?>">
                           
                            <div class="em_event_cover dbfl">
                                <?php 
                                $thumbImage = esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png');
                                if (!empty($event->cover_image_id)): ?>
                                    <?php 
                                    $thumbImage = wp_get_attachment_image_src($event->cover_image_id, 'large')[0];
                                    if(empty($thumbImage)){
                                        $thumbImage = get_the_post_thumbnail($event->id,'large');
                                        if(isset($event->parent) && !empty($event->parent) && empty($thumbImage)){
                                            $thumbImage = get_the_post_thumbnail($event->parent,'large');
                                        }
                                    }?>
                                    <a href="<?php echo $event->url; ?>">
                                        <img src="<?php echo $thumbImage; ?>" alt="<?php _e('Event Cover Image', 'eventprime-event-calendar-management');?>">
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo $event->url; ?>"><img src="<?php echo esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png'); ?>" alt="<?php _e('Dummy Image','eventprime-event-calendar-management'); ?>" class="em-no-image" ></a>
                                <?php endif; ?>
                            </div>
                            
                            <div class="dbfl em-card-description">
                                <div class="em_event_title"  title="<?php  echo $event->name; ?>">
                                    <a href="<?php echo $event->url; ?>"><?php echo $event->name; ?></a>
                                    <?php if(is_user_logged_in()): ?>
                                        <?php do_action('event_magic_wishlist_link',$event); ?>
                                    <?php endif; ?>
                                </div>
                                <?php do_action('event_magic_popup_custom_data_before_details',$event);?>
                                <?php $start_date = null; $end_date = null; $start_time = null; $end_time = null; $day = null;
                                    if (em_compare_event_dates($event->id)){
                                        $day = date_i18n(get_option('date_format'),$event->start_date);
                                        $start_time = date_i18n(get_option('time_format'),$event->start_date);
                                        $end_time = date_i18n(get_option('time_format'),$event->end_date);
                                    } else {
                                        $start_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->start_date);
                                        $end_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->end_date);
                                    }
                                if($event->all_day):?>
                                    <div class="ep-card-event-date-wrap ep-box-row ep-box-center">
                                        <span class="ep-box-col-2"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg></span> 
                                        <div class="ep-card-event-date ep-box-col-10"><?php echo date_i18n(get_option('date_format'),$event->start_date); ?><span class="em-all-day"> - <?php _e('ALL DAY','eventprime-event-calendar-management');?></span></div>
                                    </div>
                                <?php elseif(!empty($day)): ?>
                                    <div class="ep-card-event-date-wrap ep-box-row ep-box-center">
                                        <span class="ep-box-col-2"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg></span> 
                                        <div class="ep-card-event-date ep-box-col-10"><?php echo $day; ?></div>
                                    </div>
                                    <div class="ep-card-event-date-wrap ep-box-row ep-box-center">
                                        <span class="ep-box-col-2"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg></span>
                                        <div class="ep-card-event-date ep-box-col-10"><?php echo $start_time.'  to  '.$end_time; ?></div> 
                                    </div>
                                <?php else: ?>
                                    <div class="ep-card-event-date-wrap ep-box-row ep-box-center">
                                       <span class="ep-box-col-2"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg></span>
                                       <div class="ep-card-event-date ep-box-col-10"><?php echo $start_date; ?> - <?php echo $end_date; ?> </div>   
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="ep-single-box-footer dbfl">
                                <div class="em_event_price  difl">
                                    <?php 
                                    $ticket_price = $event->ticket_price;
                                    $ticket_price = apply_filters('event_magic_load_calender_ticket_price', $ticket_price, $event);
                                    // check if show one time event fees at front enable
                                    if($event->show_fixed_event_price){
                                        if($event->fixed_event_price > 0){
                                            $ticket_price = $event->fixed_event_price;
                                        }
                                    }
                                    if(!is_numeric($ticket_price)){
                                        echo $ticket_price;
                                    }
                                    else{
                                        echo !empty($ticket_price) ? em_price_with_position($ticket_price) : '';
                                    } ?>
                                </div>
                                <?php do_action('event_magic_card_view_after_price',$event); ?>
                                <div class="ep-single-box-tickets-button difr">
                                    <div class="em_event_attr_box em_eventpage_register difl">
                                        <?php 
                                        if(absint($event->custom_link_enabled) == 1):?>
                                            <div class="em_header_button em_event_custom_link kf-tickets">
                                                <a class="ep-event-custom-link" target="_blank" href="<?php echo $event->url; ?>">
                                                    <?php 
                                                    if(!empty($global_settings->hide_event_custom_link) && !is_user_logged_in()){
                                                        echo em_global_settings_button_title('Login to View');
                                                    }
                                                    else{
                                                        echo em_global_settings_button_title('Click for Details');
                                                    }?>
                                                </a>
                                            </div>
                                        <?php
                                        elseif($this->is_bookable($event)): $current_ts = em_current_time_by_timezone();?>
                                            <?php if($event->status=='expired'):?>
                                                <div class="em_header_button em_event_expired kf-tickets">
                                                    <?php echo em_global_settings_button_title('Bookings Expired'); ?>
                                                </div>
                                            <?php elseif($current_ts>$event->last_booking_date): ?>
                                                <div class="em_header_button em_booking-closed kf-tickets"><?php echo em_global_settings_button_title('Bookings Closed'); ?></div>
                                            <?php elseif($current_ts<$event->start_booking_date): ?>  
                                                <div class="em_header_button em_not_started kf-tickets"><?php echo em_global_settings_button_title('Bookings not started yet'); ?></div>
                                            <?php else: ?>
                                                <?php 
                                                if(!empty($booking_allowed)):
                                                    if(is_user_logged_in() || $showBookNowForGuestUsers): ?>
                                                        <form action="<?php echo get_permalink($global_settings->booking_page); ?>" method="post" name="em_booking">
                                                            <a class="em_header_button em_event-booking kf-tickets" name="tickets" onclick="em_event_booking(<?php echo $event->id ?>)" id="em_booking"><?php echo em_global_settings_button_title('Book Now'); ?></a>
                                                            <input type="hidden" name="event_id" value="<?php echo $event->id; ?>" />
                                                            <input type="hidden" name="venue_id" value="<?php echo $event->venue; ?>" />
                                                        </form>
                                                    <?php else: ?> 
                                                        <a class="em_header_button kf-tickets" target="_blank" href="<?php echo add_query_arg('event_id',$event->id,get_permalink($global_settings->profile_page)); ?>"><?php echo em_global_settings_button_title('Book Now'); ?></a>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        <?php elseif($event->status == 'publish'): ?>
                                            <?php  if(isset($event->standing_capacity) && !empty($event->standing_capacity)):?>
                                                <div class="em_event_attr_box em_eventpage_register difl">
                                                    <div class="em_header_button em_not_bookable kf-tickets"><?php echo em_global_settings_button_title('All Seats Booked'); ?></div>
                                                </div>
                                            <?php else:?>
                                                <div class="em_event_attr_box em_eventpage_register difl">
                                                    <div class="em_header_button em_not_bookable kf-tickets"><?php echo em_global_settings_button_title('Bookings Closed'); ?></div>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php do_action('event_magic_card_view_after_footer',$event); ?>
                            
                        </div></div>
                    <?php $i++; endforeach; ?>
                </div>
                <?php
                if($upcoming_events->max_num_pages > 1 && $load_more == 1):?>
                    <?php $curr_page = $upcoming_events->query_vars['paged'];?>
                    <div class="ep-view-load-more ep-view-load-more-wrap dbfl" onclick="em_load_more_organizer_events_card_block('.ep-view-load-more','.ep-loading-view-btn','.em_organizer_event_cards')" data-curr_page="<?php echo $curr_page?>" data-o_id="<?php echo $upcoming_events->organizer_id; ?>" data-loading="<?php _e('Loading...');?>" data-loaded="<?php _e('Load More');?>" data-max_page="<?php echo $upcoming_events->max_num_pages;?>" data-show="<?php echo $posts_per_page;?>" data-cols = "<?php echo $event_cols;?>" data-recurring="<?php echo $recurring;?>">
                        <div class="ep-loading-view-btn em_color"><?php _e('Load More');?></div>
                    </div>
                <?php endif;?>
                 
            
            <?php }elseif($display_view == 'list'){ ?>
                
                    <div class="em_list_view ep-events-list-wrap em_cards" id="ms-container">
                        <div class="ep-wrap">
                            <div class="ep-event-list-standard ep-organizer-event-list-standard">
                                <!-- the loop -->
                                <?php foreach ($events as $event) :
                                    $eventId = isset($event->id) ? $event->id : $event->ID;
                                    $event = $this->load_model_from_db($eventId);
                                    if(empty($recurring) && isset($event->parent) && !empty($event->parent)){
                                        continue;
                                    }
                                    $month_id = date('Ym', $event->start_date);
                                    if(empty($last_month_id) || $last_month_id != $month_id){
                                        $last_month_id = $month_id;?>
                                        <div class="ep-month-divider"><span class="ep-listed-event-month"><?php echo date_i18n('F Y', $event->start_date); ?><span class="ep-listed-event-month-tag"></span></span></div><?php
                                    }
                                    // check for booking allowed
                                    $booking_allowed = 1;
                                    if((isset($event->parent) && !empty($event->parent)) && (isset($event->enable_recurrence_automatic_booking) && !empty($event->enable_recurrence_automatic_booking))){
                                        // if event is recurring and parent has automatic booking enable than not allowed
                                        $booking_allowed = 0;
                                    }
                                    $event->url = em_get_single_event_page_url($event, $global_settings);
                                    ?>
                                    <div class="ep-event-article <?php if (em_is_event_expired($event->id)) echo 'emlist-expired'; ?> <?php echo empty($event->enable_booking) ? 'em_event_disabled' : ''; ?>">
                                        <div class="ep-topsec">
                                            <div class="em-col-3 difl ep-event-image-wrap ep-col-table-c">
                                                <div class="em_event_cover_list dbfl">
                                                    <?php 
                                                    $thumbImage = esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png');
                                                    if (!empty($event->cover_image_id)): ?>
                                                        <?php 
                                                        $thumbImage = wp_get_attachment_image_src($event->cover_image_id, 'large')[0];
                                                        if(empty($thumbImage)){
                                                            $thumbImage = get_the_post_thumbnail($event->id,'large');
                                                            if(isset($event->parent) && !empty($event->parent) && empty($thumbImage)){
                                                                $thumbImage = get_the_post_thumbnail($event->parent,'large');
                                                            }
                                                        }?>
                                                        <a href="<?php echo $event->url; ?>">
                                                            <img src="<?php echo $thumbImage; ?>" alt="<?php _e('Event Cover Image', 'eventprime-event-calendar-management');?>">
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="<?php echo $event->url; ?>"><img src="<?php echo esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png'); ?>" alt="<?php _e('Dummy Image','eventprime-event-calendar-management'); ?>" class="em-no-image" ></a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <div class="em-col-5 difl ep-col-table-c ep-event-content-wrap">
                                                <div class="ep-event-content">
                                                    <h3 class="ep-event-title"><a class="ep-color-hover" data-event-id="<?php echo $event->id;?>" href="<?php echo $event->url; ?>" target="_self"><?php  echo $event->name; ?></a>
                                                    </h3>
                                                    <?php if(is_user_logged_in()): ?>
                                                        <?php do_action('event_magic_wishlist_link',$event); ?>
                                                    <?php endif; ?>
                                                    <?php if(!empty($event->description)) { ?>
                                                        <div class="ep-event-description"><?php echo $event->description; ?></div>
                                                    <?php } ?>
                                                </div>
                                            </div>

                                            <div class="em-col-4 difl ep-col-table-c ep-event-meta-wrap">
                                                <div class="ep-event-meta ep-color-before">
                                                    <?php $start_date = null; $end_date = null; $start_time = null; $end_time = null; $day = null;
                                                    if (em_compare_event_dates($event->id)){
                                                        $day = date_i18n(get_option('date_format'),$event->start_date);
                                                        $start_time = date_i18n(get_option('time_format'),$event->start_date);
                                                        $end_time = date_i18n(get_option('time_format'),$event->end_date);
                                                    } else {
                                                        $start_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->start_date);
                                                        $end_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->end_date);
                                                    }
                                                    if($event->all_day):?>
                                                        <div class="ep-list-event-date-row">
                                                            <span class="material-icons em_color">date_range</span> 
                                                            <div class="ep-list-event-date">
                                                                <?php echo date_i18n(get_option('date_format'),$event->start_date); ?>
                                                                <span class="em-all-day"> - <?php _e('ALL DAY','eventprime-event-calendar-management');?></span>
                                                            </div>
                                                        </div>
                                                    <?php elseif(!empty($day)): ?>
                                                        <div class="ep-list-event-date-row">
                                                            <span class="material-icons em_color">date_range</span> <div class="ep-list-event-date"><?php echo $day; ?> - <?php echo $start_time.'  to  '.$end_time; ?></div>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="ep-list-event-date-row">
                                                            <span class="material-icons em_color">date_range</span> <div class="ep-list-event-date"><?php echo $start_date; ?> - <?php echo $end_date; ?> </div>   
                                                        </div>
                                                    <?php endif; ?> 
                                                    <?php 
                                                    if(!empty($event->venue)){
                                                        $venue_service= EventM_Factory::get_service('EventM_Venue_Service');
                                                        $venue= $venue_service->load_model_from_db($event->venue);
                                                        if(!empty($venue->id) && !empty($venue->address)){ ?>
                                                            <div class="em-list-view-venue-details" title="<?php echo $venue->address; ?>"><span class="ep-list-event-location"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zM7 9c0-2.76 2.24-5 5-5s5 2.24 5 5c0 2.88-2.88 7.19-5 9.88C9.92 16.21 7 11.85 7 9z"/><circle cx="12" cy="9" r="2.5"/></svg></span><div class="em-list-event-address"><span><?php echo $venue->address; ?></span></div>
                                                            </div><?php 
                                                        }
                                                    } ?> 

                                                    <?php if(!empty($event->enable_booking) && empty($event->hide_booking_status)):
                                                        $sum = $this->booked_seats($event->id);
                                                        $capacity = em_event_seating_capcity($event->id);?>  
                                                        <div class="ep-list-booking-status ep-event-attenders-main">
                                                            <div class="kf-event-attr-value dbfl"> 
                                                                <?php if ($capacity > 0): ?>
                                                                    <div class="dbfl">
                                                                        <?php echo $sum; ?> / <?php echo $capacity; ?> 
                                                                    </div>
                                                                    <?php $width = ($sum / $capacity) * 100; ?>
                                                                    <div class="dbfl ">
                                                                        <div id="progressbar" class="em_progressbar dbfl">
                                                                            <div style="width:<?php echo $width . '%'; ?>" class="em_progressbar_fill em_bg" ></div>
                                                                        </div>
                                                                    </div>
                                                                    <?php
                                                                else:
                                                                    if($sum > 0){
                                                                        echo '<div class="ep-event-attenders-wrap"><span class="material-icons em_color">person</span><span class="ep-event-attenders">' . $sum . ' </span>'.__('Attending','eventprime-event-calendar-management').'</div>';
                                                                    }?>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    <?php endif;?>
                                                    <?php do_action('event_magic_popup_custom_data_before_footer',$event);?>
                                                    <div class="ep-list-view-footer">
                                                        <div class="em_event_price difl">
                                                            <?php 
                                                            $ticket_price = $event->ticket_price;
                                                            // check if show one time event fees at front enable
                                                            if($event->show_fixed_event_price){
                                                                if($event->fixed_event_price > 0){
                                                                    $ticket_price = $event->fixed_event_price;
                                                                }
                                                            }
                                                            echo !empty($ticket_price) ? $currency_symbol.$ticket_price : ''; ?>
                                                        </div>
                                                        <?php do_action('event_magic_card_view_after_price',$event); ?>
                                                        <div class="kf-tickets-button difr">
                                                            <div class="em_event_attr_box em_eventpage_register difl">
                                                                <?php 
                                                                if(absint($event->custom_link_enabled) == 1):?>
                                                                    <div class="em_header_button em_event_custom_link kf-tickets">
                                                                        <a class="ep-event-custom-link" target="_blank" href="<?php echo $event->url; ?>">
                                                                            <?php 
                                                                            if(!empty($global_settings->hide_event_custom_link) && !is_user_logged_in()){
                                                                                echo em_global_settings_button_title('Login to View');
                                                                            }
                                                                            else{
                                                                                echo em_global_settings_button_title('Click for Details');
                                                                            }?>
                                                                        </a>
                                                                    </div>
                                                                <?php
                                                                elseif($this->is_bookable($event)): $current_ts = em_current_time_by_timezone();?>
                                                                    <?php if($event->status=='expired'):?>
                                                                        <div class="em_header_button em_event_expired kf-tickets">
                                                                            <?php echo em_global_settings_button_title('Bookings Expired'); ?>
                                                                        </div>
                                                                    <?php elseif($current_ts>$event->last_booking_date): ?>
                                                                        <div class="em_header_button em_booking-closed kf-tickets">
                                                                            <?php echo em_global_settings_button_title('Bookings Closed'); ?>
                                                                        </div>
                                                                    <?php elseif($current_ts<$event->start_booking_date): ?>  
                                                                        <div class="em_header_button em_not_started kf-tickets">
                                                                            <?php echo em_global_settings_button_title('Bookings not started yet'); ?>
                                                                        </div>
                                                                    <?php else: ?>
                                                                        <?php 
                                                                        if(!empty($booking_allowed)):
                                                                            if(is_user_logged_in() || $showBookNowForGuestUsers): ?>
                                                                                <form action="<?php echo get_permalink($global_settings->booking_page); ?>" method="post" name="em_booking">
                                                                                    <button class="em_header_button em_event-booking kf-tickets em_color" name="tickets" onclick="em_event_booking(<?php echo $event->id ?>)" id="em_booking">
                                                                                        <?php echo em_global_settings_button_title('Book Now'); ?>
                                                                                    </button>
                                                                                    <input type="hidden" name="event_id" value="<?php echo $event->id; ?>" />
                                                                                    <input type="hidden" name="venue_id" value="<?php echo $event->venue; ?>" />
                                                                                </form>
                                                                            <?php else: ?> 
                                                                                <a class="em_header_button em_event-booking kf-tickets em_color" target="_blank" href="<?php echo add_query_arg('event_id',$event->id,get_permalink($global_settings->profile_page)); ?>">
                                                                                    <?php echo em_global_settings_button_title('Book Now'); ?>
                                                                                </a>
                                                                            <?php endif; ?>
                                                                        <?php endif; ?>
                                                                    <?php endif; ?>
                                                                <?php elseif($event->status == 'publish'):?>
                                                                    <?php  if(isset($event->standing_capacity) && !empty($event->standing_capacity)):?>
                                                                        <div class="em_event_attr_box em_eventpage_register difl">
                                                                            <div class="em_header_button em_not_bookable kf-tickets">
                                                                                <?php echo em_global_settings_button_title('All Seats Booked'); ?>
                                                                            </div>
                                                                        </div>
                                                                    <?php else:?>
                                                                        <div class="em_event_attr_box em_eventpage_register difl">
                                                                            <div class="em_header_button em_not_bookable kf-tickets">
                                                                                <?php echo em_global_settings_button_title('Bookings Closed'); ?>
                                                                            </div>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php do_action('event_magic_card_view_after_footer',$event); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php  $i++; endforeach; ?>
                            </div>
                            <?php
                            if($upcoming_events->max_num_pages > 1 && $load_more == 1){
                                $curr_page = $upcoming_events->query_vars['paged'];?>
                                <div class="ep-masonry-load-more ep-masonry-load-more-wrap" onclick="em_load_more_organizer_events_list_block()" data-curr_page="<?php echo $curr_page?>" data-o_id="<?php echo $upcoming_events->organizer_id; ?>" data-loading="<?php _e('Loading...');?>" data-loaded="<?php _e('Load More');?>" data-max_page="<?php echo $upcoming_events->max_num_pages;?>"  data-show="<?php echo $posts_per_page;?>"  data-month_id="<?php echo $last_month_id;?>" data-recurring="<?php echo $recurring;?>"><div class="ep-load-more-button em_color"><?php _e('Load More');?></div></div><?php
                            }?>
                        </div>
                    </div>
                
            <?php }else { ?>
                <div class="em_event_list em_performer_event_mini_list">
                    <?php
                    foreach ( $events as $event ) {
                        $eventId = isset($event->id) ? $event->id : $event->ID;
                        $event_model = $this->load_model_from_db($eventId);
                        if(empty($recurring) && isset($event->parent) && !empty($event->parent)){
                            continue;
                        }
                        // check for booking allowed
                        $booking_allowed = 1;
                        if((isset($event->parent) && !empty($event->parent)) && (isset($event->enable_recurrence_automatic_booking) && !empty($event->enable_recurrence_automatic_booking))){
                            // if event is recurring and parent has automatic booking enable than not allowed
                            $booking_allowed = 0;
                        }
                        $event_model->url = em_get_single_event_page_url( $event_model, $global_settings );
                        $emcardEpired ='';
                        if (em_is_event_expired( $eventId )) {
                            $emcardEpired ='emcard-expired';
                        }
                        ?>
                        <div class="kf-upcoming-event-row em_block dbfl <?php echo $emcardEpired;?> <?php echo empty($event_model->enable_booking) ? 'em_event_disabled' : ''; ?>">
                            <div class="kf-upcoming-event-thumb em-col-2 difl">
                                <a href="<?php echo $event_model->url; ?>">
                                    <?php 
                                    $thumbImage = esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png');
                                    if (!empty($event_model->cover_image_id)): ?>
                                        <?php 
                                        $thumbImage = wp_get_attachment_image_src($event_model->cover_image_id, 'large')[0];
                                        if(empty($thumbImage)){
                                            $thumbImage = get_the_post_thumbnail($event_model->id,'large');
                                            if(isset($event_model->parent) && !empty($event_model->parent) && empty($thumbImage)){
                                                $thumbImage = get_the_post_thumbnail($event_model->parent,'large');
                                            }
                                        }?>
                                        <img src="<?php echo $thumbImage; ?>" alt="<?php _e('Event Cover Image', 'eventprime-event-calendar-management');?>">
                                    <?php else: ?>
                                        <img src="<?php echo esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png'); ?>" alt="<?php _e('Dummy Image','eventprime-event-calendar-management'); ?>" class="em-no-image" >
                                    <?php endif; ?>
                                </a>
                            </div>
                            <div class="kf-upcoming-event-title em-col-5 em-col-pad20 difl">
                                <a href="<?php echo $event_model->url; ?>">
                                    <?php echo $event_model->name; ?>
                                </a>
                                <?php if ($today>$event_model->start_date && $today<$event_model->end_date) { ?>
                                    <span class="kf-live"><?php _e('Live','eventprime-event-calendar-management'); ?></span>
                                <?php } ?>
                                <div class="kf-upcoming-event-post-date">
                                    <div class="em_event_start difl em_wrap">
                                    <?php echo date_i18n(get_option('date_format').' '.get_option('time_format'), $event_model->start_date); ?>
                                    <span> - </span>
                                    <?php echo date_i18n(get_option('date_format').' '.get_option('time_format'), $event_model->end_date); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="kf-upcoming-event-booking em-col-5 em-col-pad20 difr">
                                <div class="em_header_button kf-button">
                                    <?php if ($this->is_bookable($event_model) && absint($event_model->custom_link_enabled) != 1): $current_ts = em_current_time_by_timezone(); ?>
                                        <?php if ($event_model->status=='expired'): ?>
                                        <div class="em_header_button em_not_bookable kf-tickets"><?php echo em_global_settings_button_title('Bookings Expired'); ?></div>
                                        <?php elseif ($current_ts>$event_model->last_booking_date): ?>
                                        <div class="em_header_button em_not_bookable kf-button"><?php echo em_global_settings_button_title('Bookings Closed'); ?></div>
                                        <?php elseif($current_ts<$event_model->start_booking_date): ?>  
                                        <div class="em_header_button em_not_bookable kf-button"><?php echo em_global_settings_button_title('Bookings not started yet'); ?></div>
                                        <?php else: ?>
                                            <?php if(is_user_logged_in() || $showBookNowForGuestUsers): ?>
                                                <form action="<?php echo get_permalink($global_settings->booking_page); ?>" method="post" name="em_booking">
                                                    <button class="em_header_button kf-button em_color" name="tickets" onclick="em_event_booking(<?php echo $event_model->id ?>)" class="em_header_button" id="em_booking">
                                                        <i class="fa fa-ticket" aria-hidden="true"></i>
                                                        <?php
                                                        echo em_global_settings_button_title('Book Now');
                                                        if ($event_model->ticket_price > 0){
                                                            $ticketPrice = $event_model->ticket_price;
                                                            // check if show one time event fees at front enable
                                                            if($event_model->show_fixed_event_price){
                                                                if($event_model->fixed_event_price > 0){
                                                                    $ticketPrice = $event_model->fixed_event_price;
                                                                }
                                                            }
                                                            if ($ticketPrice > 0){
                                                                echo " - " . '<span class="em_event_price">' . em_price_with_position($ticketPrice, $currency_symbol) . '</span>';
                                                            }
                                                            do_action('event_magic_single_event_ticket_price_after', $event_model, $ticketPrice);
                                                        }
                                                        ?>
                                                    </button>
                                                    <input type="hidden" name="event_id" value="<?php echo $event_model->id; ?>" />
                                                    <input type="hidden" name="venue_id" value="<?php echo $event_model->venue; ?>" />
                                                </form>
                                                <?php else: ?>
                                                    <a class="em_header_button kf-button em_color" target="_blank" href="<?php echo add_query_arg('event_id',$event_model->id, get_permalink($global_settings->profile_page)); ?>"><?php echo em_global_settings_button_title('Book Now'); ?></a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        <?php elseif(absint($event_model->custom_link_enabled) != 1): ?>
                                        <div class="em_event_attr_box em_eventpage_register difl">
                                            <div class="em_header_button em_not_bookable kf-button">
                                                <?php echo em_global_settings_button_title('Bookings Closed'); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?> 
                                </div>
                            </div>
                        </div>
                    <?php
                    $i++;  }
                    ?>
                </div>
                <?php if($upcoming_events->max_num_pages > 1 && $load_more == 1){
                    $curr_page = $upcoming_events->query_vars['paged']; ?>
                    <div class="ep-view-load-more ep-view-load-more-wrap dbfl" onclick="em_load_more_organizer_events_mini_list_block('.ep-view-load-more','.ep-loading-view-btn','.em_performer_event_mini_list')" data-curr_page="<?php echo $curr_page?>" data-p_id="<?php echo $upcoming_events->organizer_id; ?>" data-loading="<?php _e('Loading...');?>" data-loaded="<?php _e('Load More');?>" data-max_page="<?php echo $upcoming_events->max_num_pages;?>" data-show="<?php echo $posts_per_page;?>" data-cols = "<?php echo $event_cols;?>" data-recurring="<?php echo $recurring;?>">
                        <div class="ep-loading-view-btn em_color"><?php _e('Load More');?></div>
                    </div>
                  <?php
                }
            }
        }else {
            if($_POST){ ?>
                <article>
                    <p><?php _e('No events match your criterion.','eventprime-event-calendar-management'); ?></p>
                </article>
            <?php }else{ ?>
                <article>
                    <p><?php _e('There are no Events available right now.','eventprime-event-calendar-management'); ?></p>
                </article>
            <?php }
        } ?>
        </div>
        <?php 
    }

    public function upcoming_events_for_venue( $venue_id, $args ){

        $results = $this->dao->get_upcoming_events_for_venue( $venue_id, $args );
        return $results;
    }

    public function print_upcoming_event_block_for_venues( $upcoming_events, $event_args ) {
        $setting_service = EventM_Factory::get_service( 'EventM_Setting_Service' );
        $global_settings = $setting_service->load_model_from_db();
        $today = em_current_time_by_timezone();
        $currency_symbol = em_currency_symbol();
        $display_view = $event_args->event_style;
        $posts_per_page = $event_args->event_limit;
        $event_cols = $event_args->event_cols;
        $load_more = $event_args->load_more;
        $hide_past_events = $event_args->hide_past_events;
        $events = $upcoming_events->posts; 
        $showBookNowForGuestUsers = em_show_book_now_for_guest_users();
        $recurring = 1; $column_class = ''; 
        ?>
        <div class="ep-event-type-events em_block dbfl">
            <div class="kf-row-heading">
                <span class="kf-row-title"><?php echo __( 'Upcoming Events', 'eventprime-event-calendar-management' ); ?>
                    <span class="em_events_count-wrap em_bg">
                    </span>
                </span>
            </div>
        <?php  
        if ( ! empty( $events ) ){
            $i = 1;
            if( $display_view == 'card' ){ ?>
                <div class="em_cards em_venue_event_cards">
                    <!-- the loop -->
                    <?php foreach ( $events as $event ) :
                        $eventId = isset( $event->id ) ? $event->id : $event->ID;
                        $event= $this->load_model_from_db( $eventId );
                        if( empty( $recurring ) && isset( $event->parent ) && !empty( $event->parent ) ){
                            continue;
                        }
                        // check for booking allowed
                        $booking_allowed = 1;
                        if((isset( $event->parent ) && ! empty( $event->parent )) && ( isset( $event->enable_recurrence_automatic_booking ) && ! empty( $event->enable_recurrence_automatic_booking ) ) ){
                            // if event is recurring and parent has automatic booking enable than not allowed
                            $booking_allowed = 0;
                        }
                        $event->url = em_get_single_event_page_url($event, $global_settings);
                        ?>
                        <div class="<?php if(empty($section_id)){ echo 'em_card'; } else{ echo 'em_card_edt';}?> difl <?php if (em_is_event_expired($event->id)) echo 'emcard-expired'; ?> <?php echo (empty($event->enable_booking) && absint($event->custom_link_enabled) == 0) ? 'em_event_disabled' : ''; ?> <?php echo $column_class;?> col-md-<?php echo $event_cols;?>">
                            <div class="em_event_cover dbfl">
                                <?php 
                                $thumbImage = esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png');
                                if (!empty($event->cover_image_id)): ?>
                                    <?php 
                                    $thumbImage = wp_get_attachment_image_src($event->cover_image_id, 'large')[0];
                                    if(empty($thumbImage)){
                                        $thumbImage = get_the_post_thumbnail($event->id,'large');
                                        if(isset($event->parent) && !empty($event->parent) && empty($thumbImage)){
                                            $thumbImage = get_the_post_thumbnail($event->parent,'large');
                                        }
                                    }?>
                                    <a href="<?php echo $event->url; ?>">
                                        <img src="<?php echo $thumbImage; ?>" alt="<?php _e('Event Cover Image', 'eventprime-event-calendar-management');?>">
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo $event->url; ?>"><img src="<?php echo esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png'); ?>" alt="<?php _e('Dummy Image','eventprime-event-calendar-management'); ?>" class="em-no-image" ></a>
                                <?php endif; ?>
                            </div>
                            
                            <div class="dbfl em-card-description">
                                <div class="em_event_title em_block dbfl"  title="<?php  echo $event->name; ?>">
                                    <a href="<?php echo $event->url; ?>"><?php echo $event->name; ?></a>
                                    <?php if(is_user_logged_in()): ?>
                                        <?php do_action('event_magic_wishlist_link',$event); ?>
                                    <?php endif; ?>
                                </div>
                                <?php do_action('event_magic_popup_custom_data_before_details',$event);?>
                                <?php $start_date = null; $end_date = null; $start_time = null; $end_time = null; $day = null;
                                    if (em_compare_event_dates($event->id)){
                                        $day = date_i18n(get_option('date_format'),$event->start_date);
                                        $start_time = date_i18n(get_option('time_format'),$event->start_date);
                                        $end_time = date_i18n(get_option('time_format'),$event->end_date);
                                    } else {
                                        $start_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->start_date);
                                        $end_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->end_date);
                                    }
                                if($event->all_day):?>
                                    <div class="em_event_start difl em_color em_wrap">
                                        <?php echo date_i18n(get_option('date_format'),$event->start_date); ?><span class="em-all-day"> - <?php _e('ALL DAY','eventprime-event-calendar-management');?></span>
                                    </div>
                                <?php elseif(!empty($day)): ?>
                                    <div class="em_event_start difl em_color em_wrap">
                                        <?php echo $day; ?>
                                    </div>
                                    <div class="em_event_start difl em_color em_wrap"><?php echo $start_time.'  to  '.$end_time; ?></div>
                                <?php else: ?>
                                    <div class="em_event_start difl em_color em_wrap">
                                        <?php echo $start_date; ?> -    
                                    </div>
                                    <div class="em_event_start difl em_color em_wrap">
                                        <?php echo $end_date; ?>  
                                    </div>
                                <?php endif; ?>
                                <?php 
                                if(!empty($event->venue)){  
                                    $venue_service= EventM_Factory::get_service('EventM_Venue_Service');
                                    $venue= $venue_service->load_model_from_db($event->venue);
                                    if(!empty($venue->id)){  ?>
                                        <div class="em_event_address dbfl" title="<?php echo $venue->address; ?>"><?php echo $venue->address; ?></div>
                                        <?php 
                                    }
                                }?>
                                <?php if(!empty($event->description)) { ?>
                                    <div class="em_event_description dbfl"><?php echo $event->description; ?></div>
                                <?php } ?>

                                <?php if(!empty($event->enable_booking) && empty($event->hide_booking_status)):
                                    $sum = $this->booked_seats($event->id);
                                    $capacity = em_event_seating_capcity($event->id);?>  
                                    <div class="dbfl">
                                        <div class="kf-event-attr-value dbfl">  
                                            <?php if ($capacity > 0): ?>
                                                <div class="dbfl">
                                                    <?php echo $sum; ?> / <?php echo $capacity; ?> 
                                                </div>
                                            <?php $width = ($sum / $capacity) * 100; ?>
                                                <div class="dbfl">
                                                    <div id="progressbar" class="em_progressbar dbfl">
                                                        <div style="width:<?php echo $width . '%'; ?>" class="em_progressbar_fill em_bg" ></div>
                                                    </div>
                                                </div>
                                            <?php
                                                else:
                                                    echo '<div class="dbfl">' . $sum . ' '.__('Attending','eventprime-event-calendar-management').'</div>';
                                            ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>  
                                    <?php
                                endif;?>
                                <?php do_action('event_magic_popup_custom_data_before_footer',$event);?>
                            </div>
                            <div class="em-cards-footer dbfl">
                                <div class="em_event_price  difl">
                                    <?php 
                                    $ticket_price = $event->ticket_price;
                                    $ticket_price = apply_filters('event_magic_load_calender_ticket_price', $ticket_price, $event);
                                    // check if show one time event fees at front enable
                                    if($event->show_fixed_event_price){
                                        if($event->fixed_event_price > 0){
                                            $ticket_price = $event->fixed_event_price;
                                        }
                                    }
                                    if(!is_numeric($ticket_price)){
                                        echo $ticket_price;
                                    }
                                    else{
                                        echo !empty($ticket_price) ? em_price_with_position($ticket_price) : '';
                                    } ?>
                                </div>
                                <?php do_action('event_magic_card_view_after_price',$event); ?>
                                <div class="kf-tickets-button difr">
                                    <div class="em_event_attr_box em_eventpage_register difl">
                                        <?php 
                                        if(absint($event->custom_link_enabled) == 1):?>
                                            <div class="em_header_button em_event_custom_link kf-tickets">
                                                <a class="ep-event-custom-link" target="_blank" href="<?php echo $event->url; ?>">
                                                    <?php 
                                                    if(!empty($global_settings->hide_event_custom_link) && !is_user_logged_in()){
                                                        echo em_global_settings_button_title('Login to View');
                                                    }
                                                    else{
                                                        echo em_global_settings_button_title('Click for Details');
                                                    }?>
                                                </a>
                                            </div>
                                        <?php
                                        elseif($this->is_bookable($event)): $current_ts = em_current_time_by_timezone();?>
                                            <?php if($event->status=='expired'):?>
                                                <div class="em_header_button em_event_expired kf-tickets">
                                                    <?php echo em_global_settings_button_title('Bookings Expired'); ?>
                                                </div>
                                            <?php elseif($current_ts>$event->last_booking_date): ?>
                                                <div class="em_header_button em_booking-closed kf-tickets"><?php echo em_global_settings_button_title('Bookings Closed'); ?></div>
                                            <?php elseif($current_ts<$event->start_booking_date): ?>  
                                                <div class="em_header_button em_not_started kf-tickets"><?php echo em_global_settings_button_title('Bookings not started yet'); ?></div>
                                            <?php else: ?>
                                                <?php 
                                                if(!empty($booking_allowed)):
                                                    if(is_user_logged_in() || $showBookNowForGuestUsers): ?>
                                                        <form action="<?php echo get_permalink($global_settings->booking_page); ?>" method="post" name="em_booking">
                                                            <button class="em_header_button em_event-booking kf-tickets" name="tickets" onclick="em_event_booking(<?php echo $event->id ?>)" id="em_booking"><?php echo em_global_settings_button_title('Book Now'); ?></button>
                                                            <input type="hidden" name="event_id" value="<?php echo $event->id; ?>" />
                                                            <input type="hidden" name="venue_id" value="<?php echo $event->venue; ?>" />
                                                        </form>
                                                    <?php else: ?> 
                                                        <a class="em_header_button kf-tickets" target="_blank" href="<?php echo add_query_arg('event_id',$event->id,get_permalink($global_settings->profile_page)); ?>"><?php echo em_global_settings_button_title('Book Now'); ?></a>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        <?php elseif($event->status == 'publish'): ?>
                                            <?php  if(isset($event->standing_capacity) && !empty($event->standing_capacity)):?>
                                                <div class="em_event_attr_box em_eventpage_register difl">
                                                    <div class="em_header_button em_not_bookable kf-tickets"><?php echo em_global_settings_button_title('All Seats Booked'); ?></div>
                                                </div>
                                            <?php else:?>
                                                <div class="em_event_attr_box em_eventpage_register difl">
                                                    <div class="em_header_button em_not_bookable kf-tickets"><?php echo em_global_settings_button_title('Bookings Closed'); ?></div>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php do_action('event_magic_card_view_after_footer',$event); ?>
                        </div>
                    <?php $i++; endforeach; ?>
                    </div> 
                    <?php
                    if($upcoming_events->max_num_pages > 1 && $load_more == 1):?>
                    <?php $curr_page = $upcoming_events->query_vars['paged'];?>
                        <div class="ep-view-load-more ep-view-load-more-wrap dbfl" onclick="em_load_more_venue_events_card_block('.ep-view-load-more','.ep-loading-view-btn','.em_venue_event_cards')" data-curr_page="<?php echo $curr_page?>" data-venue_id="<?php echo $upcoming_events->venue_id; ?>" data-loading="<?php _e('Loading...');?>" data-loaded="<?php _e('Load More');?>" data-max_page="<?php echo $upcoming_events->max_num_pages;?>" data-show="<?php echo $posts_per_page;?>" data-cols = "<?php echo $event_cols;?>" data-recurring="<?php echo $recurring;?>">
                            <div class="ep-loading-view-btn em_color"><?php _e('Load More');?></div>
                        </div>
                    <?php endif;?>
                 
            
            <?php }elseif($display_view == 'list'){ ?>
                
                    <div class="em_list_view ep-events-list-wrap em_cards" id="ms-container">
                        <div class="ep-wrap">
                            <div class="ep-event-list-standard ep-venue-event-list-standard">
                                <!-- the loop -->
                                <?php foreach ($events as $event) :
                                    $eventId = isset($event->id) ? $event->id : $event->ID;
                                    $event = $this->load_model_from_db($eventId);
                                    if(empty($recurring) && isset($event->parent) && !empty($event->parent)){
                                        continue;
                                    }
                                    $month_id = date('Ym', $event->start_date);
                                    if(empty($last_month_id) || $last_month_id != $month_id){
                                        $last_month_id = $month_id;?>
                                        <div class="ep-month-divider"><span class="ep-listed-event-month"><?php echo date_i18n('F Y', $event->start_date); ?><span class="ep-listed-event-month-tag"></span></span></div><?php
                                    }
                                    // check for booking allowed
                                    $booking_allowed = 1;
                                    if((isset($event->parent) && !empty($event->parent)) && (isset($event->enable_recurrence_automatic_booking) && !empty($event->enable_recurrence_automatic_booking))){
                                        // if event is recurring and parent has automatic booking enable than not allowed
                                        $booking_allowed = 0;
                                    }
                                    $event->url = em_get_single_event_page_url($event, $global_settings);
                                    ?>
                                    <div class="ep-event-article <?php if (em_is_event_expired($event->id)) echo 'emlist-expired'; ?> <?php echo empty($event->enable_booking) ? 'em_event_disabled' : ''; ?>">
                                        <div class="ep-topsec">
                                            <div class="em-col-3 difl ep-event-image-wrap ep-col-table-c">
                                                <div class="em_event_cover_list dbfl">
                                                    <?php 
                                                    $thumbImage = esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png');
                                                    if (!empty($event->cover_image_id)): ?>
                                                        <?php 
                                                        $thumbImage = wp_get_attachment_image_src($event->cover_image_id, 'large')[0];
                                                        if(empty($thumbImage)){
                                                            $thumbImage = get_the_post_thumbnail($event->id,'large');
                                                            if(isset($event->parent) && !empty($event->parent) && empty($thumbImage)){
                                                                $thumbImage = get_the_post_thumbnail($event->parent,'large');
                                                            }
                                                        }?>
                                                        <a href="<?php echo $event->url; ?>">
                                                            <img src="<?php echo $thumbImage; ?>" alt="<?php _e('Event Cover Image', 'eventprime-event-calendar-management');?>">
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="<?php echo $event->url; ?>"><img src="<?php echo esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png'); ?>" alt="<?php _e('Dummy Image','eventprime-event-calendar-management'); ?>" class="em-no-image" ></a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <div class="em-col-5 difl ep-col-table-c ep-event-content-wrap">
                                                <div class="ep-event-content">
                                                    <h3 class="ep-event-title"><a class="ep-color-hover" data-event-id="<?php echo $event->id;?>" href="<?php echo $event->url; ?>" target="_self"><?php  echo $event->name; ?></a>
                                                    </h3>
                                                    <?php if(is_user_logged_in()): ?>
                                                        <?php do_action('event_magic_wishlist_link',$event); ?>
                                                    <?php endif; ?>
                                                    <?php if(!empty($event->description)) { ?>
                                                        <div class="ep-event-description"><?php echo $event->description; ?></div>
                                                    <?php } ?>
                                                </div>
                                            </div>

                                            <div class="em-col-4 difl ep-col-table-c ep-event-meta-wrap">
                                                <div class="ep-event-meta ep-color-before">
                                                    <?php $start_date = null; $end_date = null; $start_time = null; $end_time = null; $day = null;
                                                    if (em_compare_event_dates($event->id)){
                                                        $day = date_i18n(get_option('date_format'),$event->start_date);
                                                        $start_time = date_i18n(get_option('time_format'),$event->start_date);
                                                        $end_time = date_i18n(get_option('time_format'),$event->end_date);
                                                    } else {
                                                        $start_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->start_date);
                                                        $end_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->end_date);
                                                    }
                                                    if($event->all_day):?>
                                                        <div class="ep-list-event-date-row">
                                                            <span class="material-icons em_color">date_range</span> 
                                                            <div class="ep-list-event-date">
                                                                <?php echo date_i18n(get_option('date_format'),$event->start_date); ?>
                                                                <span class="em-all-day"> - <?php _e('ALL DAY','eventprime-event-calendar-management');?></span>
                                                            </div>
                                                        </div>
                                                    <?php elseif(!empty($day)): ?>
                                                        <div class="ep-list-event-date-row">
                                                            <span class="material-icons em_color">date_range</span> <div class="ep-list-event-date"><?php echo $day; ?> - <?php echo $start_time.'  to  '.$end_time; ?></div>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="ep-list-event-date-row">
                                                            <span class="material-icons em_color">date_range</span> <div class="ep-list-event-date"><?php echo $start_date; ?> - <?php echo $end_date; ?> </div>   
                                                        </div>
                                                    <?php endif; ?> 
                                                    <?php 
                                                    if(!empty($event->venue)){
                                                        $venue_service= EventM_Factory::get_service('EventM_Venue_Service');
                                                        $venue= $venue_service->load_model_from_db($event->venue);
                                                        if(!empty($venue->id) && !empty($venue->address)){ ?>
                                                            <div class="em-list-view-venue-details" title="<?php echo $venue->address; ?>"><span class="ep-list-event-location"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zM7 9c0-2.76 2.24-5 5-5s5 2.24 5 5c0 2.88-2.88 7.19-5 9.88C9.92 16.21 7 11.85 7 9z"/><circle cx="12" cy="9" r="2.5"/></svg></span><div class="em-list-event-address"><span><?php echo $venue->address; ?></span></div>
                                                            </div><?php 
                                                        }
                                                    } ?> 

                                                    <?php if(!empty($event->enable_booking) && empty($event->hide_booking_status)):
                                                        $sum = $this->booked_seats($event->id);
                                                        $capacity = em_event_seating_capcity($event->id);?>  
                                                        <div class="ep-list-booking-status ep-event-attenders-main">
                                                            <div class="kf-event-attr-value dbfl"> 
                                                                <?php if ($capacity > 0): ?>
                                                                    <div class="dbfl">
                                                                        <?php echo $sum; ?> / <?php echo $capacity; ?> 
                                                                    </div>
                                                                    <?php $width = ($sum / $capacity) * 100; ?>
                                                                    <div class="dbfl ">
                                                                        <div id="progressbar" class="em_progressbar dbfl">
                                                                            <div style="width:<?php echo $width . '%'; ?>" class="em_progressbar_fill em_bg" ></div>
                                                                        </div>
                                                                    </div>
                                                                    <?php
                                                                else:
                                                                    if($sum > 0){
                                                                        echo '<div class="ep-event-attenders-wrap"><span class="material-icons em_color">person</span><span class="ep-event-attenders">' . $sum . ' </span>'.__('Attending','eventprime-event-calendar-management').'</div>';
                                                                    }?>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    <?php endif;?>
                                                    <?php do_action('event_magic_popup_custom_data_before_footer',$event);?>
                                                    <div class="ep-list-view-footer">
                                                        <div class="em_event_price difl">
                                                            <?php 
                                                            $ticket_price = $event->ticket_price;
                                                            // check if show one time event fees at front enable
                                                            if($event->show_fixed_event_price){
                                                                if($event->fixed_event_price > 0){
                                                                    $ticket_price = $event->fixed_event_price;
                                                                }
                                                            }
                                                            echo !empty($ticket_price) ? $currency_symbol.$ticket_price : ''; ?>
                                                        </div>
                                                        <?php do_action('event_magic_card_view_after_price',$event); ?>
                                                        <div class="kf-tickets-button difr">
                                                            <div class="em_event_attr_box em_eventpage_register difl">
                                                                <?php 
                                                                if(absint($event->custom_link_enabled) == 1):?>
                                                                    <div class="em_header_button em_event_custom_link kf-tickets">
                                                                        <a class="ep-event-custom-link" target="_blank" href="<?php echo $event->url; ?>">
                                                                            <?php 
                                                                            if(!empty($global_settings->hide_event_custom_link) && !is_user_logged_in()){
                                                                                echo em_global_settings_button_title('Login to View');
                                                                            }
                                                                            else{
                                                                                echo em_global_settings_button_title('Click for Details');
                                                                            }?>
                                                                        </a>
                                                                    </div>
                                                                <?php
                                                                elseif($this->is_bookable($event)): $current_ts = em_current_time_by_timezone();?>
                                                                    <?php if($event->status=='expired'):?>
                                                                        <div class="em_header_button em_event_expired kf-tickets">
                                                                            <?php echo em_global_settings_button_title('Bookings Expired'); ?>
                                                                        </div>
                                                                    <?php elseif($current_ts>$event->last_booking_date): ?>
                                                                        <div class="em_header_button em_booking-closed kf-tickets">
                                                                            <?php echo em_global_settings_button_title('Bookings Closed'); ?>
                                                                        </div>
                                                                    <?php elseif($current_ts<$event->start_booking_date): ?>  
                                                                        <div class="em_header_button em_not_started kf-tickets">
                                                                            <?php echo em_global_settings_button_title('Bookings not started yet'); ?>
                                                                        </div>
                                                                    <?php else: ?>
                                                                        <?php 
                                                                        if(!empty($booking_allowed)):
                                                                            if(is_user_logged_in() || $showBookNowForGuestUsers): ?>
                                                                                <form action="<?php echo get_permalink($global_settings->booking_page); ?>" method="post" name="em_booking">
                                                                                    <button class="em_header_button em_event-booking kf-tickets em_color" name="tickets" onclick="em_event_booking(<?php echo $event->id ?>)" id="em_booking">
                                                                                        <?php echo em_global_settings_button_title('Book Now'); ?>
                                                                                    </button>
                                                                                    <input type="hidden" name="event_id" value="<?php echo $event->id; ?>" />
                                                                                    <input type="hidden" name="venue_id" value="<?php echo $event->venue; ?>" />
                                                                                </form>
                                                                            <?php else: ?> 
                                                                                <a class="em_header_button em_event-booking kf-tickets em_color" target="_blank" href="<?php echo add_query_arg('event_id',$event->id,get_permalink($global_settings->profile_page)); ?>">
                                                                                    <?php echo em_global_settings_button_title('Book Now'); ?>
                                                                                </a>
                                                                            <?php endif; ?>
                                                                        <?php endif; ?>
                                                                    <?php endif; ?>
                                                                <?php elseif($event->status == 'publish'):?>
                                                                    <?php  if(isset($event->standing_capacity) && !empty($event->standing_capacity)):?>
                                                                        <div class="em_event_attr_box em_eventpage_register difl">
                                                                            <div class="em_header_button em_not_bookable kf-tickets">
                                                                                <?php echo em_global_settings_button_title('All Seats Booked'); ?>
                                                                            </div>
                                                                        </div>
                                                                    <?php else:?>
                                                                        <div class="em_event_attr_box em_eventpage_register difl">
                                                                            <div class="em_header_button em_not_bookable kf-tickets">
                                                                                <?php echo em_global_settings_button_title('Bookings Closed'); ?>
                                                                            </div>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php do_action('event_magic_card_view_after_footer',$event); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php  $i++; endforeach; ?>
                            </div>
                            <?php
                            if($upcoming_events->max_num_pages > 1 && $load_more == 1){
                                $curr_page = $upcoming_events->query_vars['paged'];?>
                                <div class="ep-masonry-load-more ep-masonry-load-more-wrap" onclick="em_load_more_venue_events_list_block()" data-curr_page="<?php echo $curr_page?>" data-venue_id="<?php echo $upcoming_events->venue_id; ?>" data-loading="<?php _e('Loading...');?>" data-loaded="<?php _e('Load More');?>" data-max_page="<?php echo $upcoming_events->max_num_pages;?>"  data-show="<?php echo $posts_per_page;?>"  data-month_id="<?php echo $last_month_id;?>" data-recurring="<?php echo $recurring;?>"><div class="ep-load-more-button em_color"><?php _e('Load More');?></div></div><?php
                            }?>
                        </div>
                    </div>
                
            <?php }else { ?>
                <div class="em_event_list em_venue_event_mini_list">
                    <?php
                    foreach ( $events as $event ) {
                        $eventId = isset($event->id) ? $event->id : $event->ID;
                        $event_model = $this->load_model_from_db($eventId);
                        if(empty($recurring) && isset($event->parent) && !empty($event->parent)){
                            continue;
                        }
                        // check for booking allowed
                        $booking_allowed = 1;
                        if((isset($event->parent) && !empty($event->parent)) && (isset($event->enable_recurrence_automatic_booking) && !empty($event->enable_recurrence_automatic_booking))){
                            // if event is recurring and parent has automatic booking enable than not allowed
                            $booking_allowed = 0;
                        }
                        $event_model->url = em_get_single_event_page_url( $event_model, $global_settings );
                        $emcardEpired ='';
                        if (em_is_event_expired($eventId)) {
                            $emcardEpired ='emcard-expired';
                        }
                        ?>
                        <div class="kf-upcoming-event-row em_block dbfl <?php echo $emcardEpired;?> <?php echo empty($event_model->enable_booking) ? 'em_event_disabled' : ''; ?>">
                            <div class="kf-upcoming-event-thumb em-col-2 difl">
                                <a href="<?php echo $event_model->url; ?>">
                                    <?php 
                                    $thumbImage = esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png');
                                    if (!empty($event_model->cover_image_id)): ?>
                                        <?php 
                                        $thumbImage = wp_get_attachment_image_src($event_model->cover_image_id, 'large')[0];
                                        if(empty($thumbImage)){
                                            $thumbImage = get_the_post_thumbnail($event_model->id,'large');
                                            if(isset($event_model->parent) && !empty($event_model->parent) && empty($thumbImage)){
                                                $thumbImage = get_the_post_thumbnail($event_model->parent,'large');
                                            }
                                        }?>
                                        <img src="<?php echo $thumbImage; ?>" alt="<?php _e('Event Cover Image', 'eventprime-event-calendar-management');?>">
                                    <?php else: ?>
                                        <img src="<?php echo esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png'); ?>" alt="<?php _e('Dummy Image','eventprime-event-calendar-management'); ?>" class="em-no-image" >
                                    <?php endif; ?>
                                </a>
                            </div>
                            <div class="kf-upcoming-event-title em-col-5 em-col-pad20 difl">
                                <a href="<?php echo $event_model->url; ?>">
                                    <?php echo $event_model->name; ?>
                                </a>
                                <?php if ($today>$event_model->start_date && $today<$event_model->end_date) { ?>
                                    <span class="kf-live"><?php _e('Live','eventprime-event-calendar-management'); ?></span>
                                <?php } ?>
                                <div class="kf-upcoming-event-post-date">
                                    <div class="em_event_start difl em_wrap">
                                    <?php echo date_i18n(get_option('date_format').' '.get_option('time_format'), $event_model->start_date); ?>
                                    <span> - </span>
                                    <?php echo date_i18n(get_option('date_format').' '.get_option('time_format'), $event_model->end_date); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="kf-upcoming-event-booking em-col-5 em-col-pad20 difr">
                                <div class="em_header_button kf-button">
                                    <?php if ($this->is_bookable($event_model) && absint($event_model->custom_link_enabled) != 1): $current_ts = em_current_time_by_timezone(); ?>
                                        <?php if ($event_model->status=='expired'): ?>
                                        <div class="em_header_button em_not_bookable kf-tickets"><?php echo em_global_settings_button_title('Bookings Expired'); ?></div>
                                        <?php elseif ($current_ts>$event_model->last_booking_date): ?>
                                        <div class="em_header_button em_not_bookable kf-button"><?php echo em_global_settings_button_title('Bookings Closed'); ?></div>
                                        <?php elseif($current_ts<$event_model->start_booking_date): ?>  
                                        <div class="em_header_button em_not_bookable kf-button"><?php echo em_global_settings_button_title('Bookings not started yet'); ?></div>
                                        <?php else: ?>
                                            <?php if(is_user_logged_in() || $showBookNowForGuestUsers): ?>
                                                <form action="<?php echo get_permalink($global_settings->booking_page); ?>" method="post" name="em_booking">
                                                    <button class="em_header_button kf-button em_color" name="tickets" onclick="em_event_booking(<?php echo $event_model->id ?>)" class="em_header_button" id="em_booking">
                                                        <i class="fa fa-ticket" aria-hidden="true"></i>
                                                        <?php
                                                        echo em_global_settings_button_title('Book Now');
                                                        if ($event_model->ticket_price > 0){
                                                            $ticketPrice = $event_model->ticket_price;
                                                            // check if show one time event fees at front enable
                                                            if($event_model->show_fixed_event_price){
                                                                if($event_model->fixed_event_price > 0){
                                                                    $ticketPrice = $event_model->fixed_event_price;
                                                                }
                                                            }
                                                            if ($ticketPrice > 0){
                                                                echo " - " . '<span class="em_event_price">' . em_price_with_position($ticketPrice, $currency_symbol) . '</span>';
                                                            }
                                                            do_action('event_magic_single_event_ticket_price_after', $event_model, $ticketPrice);
                                                        }
                                                        ?>
                                                    </button>
                                                    <input type="hidden" name="event_id" value="<?php echo $event_model->id; ?>" />
                                                    <input type="hidden" name="venue_id" value="<?php echo $event_model->venue; ?>" />
                                                </form>
                                                <?php else: ?>
                                                    <a class="em_header_button kf-button em_color" target="_blank" href="<?php echo add_query_arg('event_id',$event_model->id, get_permalink($global_settings->profile_page)); ?>"><?php echo em_global_settings_button_title('Book Now'); ?></a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        <?php elseif(absint($event_model->custom_link_enabled) != 1): ?>
                                        <div class="em_event_attr_box em_eventpage_register difl">
                                            <div class="em_header_button em_not_bookable kf-button">
                                                <?php echo em_global_settings_button_title('Bookings Closed'); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?> 
                                </div>
                            </div>
                        </div>
                    <?php
                    $i++;  }
                    ?>
                </div>
                <?php if($upcoming_events->max_num_pages > 1 && $load_more == 1){
                    $curr_page = $upcoming_events->query_vars['paged']; ?>
                    <div class="ep-view-load-more ep-view-load-more-wrap dbfl" onclick="em_load_more_venue_events_mini_list_block('.ep-view-load-more','.ep-loading-view-btn','.em_venue_event_mini_list')" data-curr_page="<?php echo $curr_page?>" data-venue_id="<?php echo $upcoming_events->venue_id; ?>" data-loading="<?php _e('Loading...');?>" data-loaded="<?php _e('Load More');?>" data-max_page="<?php echo $upcoming_events->max_num_pages;?>" data-show="<?php echo $posts_per_page;?>" data-cols = "<?php echo $event_cols;?>" data-recurring="<?php echo $recurring;?>">
                        <div class="ep-loading-view-btn em_color"><?php _e('Load More');?></div>
                    </div>
                  <?php
                }
            }
        }else {
            if($_POST){ ?>
                    <article>
                        <p><?php _e('No events match your criterion.','eventprime-event-calendar-management'); ?></p>
                    </article>
            <?php }else{ ?>
                    <article>
                        <p><?php _e('There are no Events available right now.','eventprime-event-calendar-management'); ?></p>
                    </article>
            <?php } 
        } ?>
        </div>
        <?php 
    }
}