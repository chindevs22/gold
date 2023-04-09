<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class EventM_Venue_Service {

    private $dao;
    private static $instance = null;

    private function __construct() {
        $this->dao = new EventM_Venue_DAO();
    }

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /*
     * Load Add/Edit page for REST 
     */

    public function load_edit_page() {
        $response = new stdClass();
        $id = event_m_get_param('id');
        $venue = $this->load_model_from_db($id);
        if( !empty( $id ) ) {
            if( $venue->id != $id ){
                $response->error = 1;
                $response->message = esc_html__('Venue not found', 'eventprime-event-calendar-management');
                return $response;
            }

            if( !empty( em_check_context_user_capabilities( array( 'edit_event_sites' ) ) ) ) {
                if( empty( em_check_context_user_capabilities( array( 'edit_others_event_sites' ) ) ) ) {
                    if($venue->created_by != get_current_user_id()) {
                        $response->error = 1;
                        $response->message = esc_html__( 'Unauthorized Access', 'eventprime-event-calendar-management' );
                        return $response;
                    }
                }
            } elseif( !empty( em_check_context_user_capabilities( array( 'edit_others_event_sites' ) ) ) ) {
                if($venue->created_by == get_current_user_id()) {
                    $response->error = 1;
                    $response->message = esc_html__( 'Unauthorized Access', 'eventprime-event-calendar-management' );
                    return $response;
                }
            } else{
                $response->error = 1;
                $response->message = esc_html__( 'Unauthorized Access', 'eventprime-event-calendar-management' );
                return $response;
            }
        }

        if(!empty($venue->established)){
            $venue->established= em_showDateTime($venue->established, false, 'm/d/Y');
        }
        $gmap_api_key = em_global_settings('gmap_api_key');
        if (!empty($gmap_api_key)) {
            $venue->map_configured = true;
        } else {
            $venue->map_configured = false;
            $venue->map_notice = __("Location Map field is not active as Google Map API is not configured. You can confiure it from the Global Settings.", 'eventprime-event-calendar-management');
        }
        $venue->addresses= array();
        $venue->types = $this->seating_options();
        $venue->images = $this->get_gallery_images($venue->gallery_images);
        $venue->is_featured = absint( $venue->is_featured );
        return $venue;
    }

    /*
     * Load List page for REST 
     */

    public function load_list_page() {
        $response = new stdClass();
        $response->terms = array();
        $sort_option = event_m_get_param('sort_option');
        $args = array(
            'hide_empty' => 0,
            'orderby' => $sort_option,
            'order' => event_m_get_param('order'),
            'offset' => (int) (event_m_get_param('paged') - 1) * EM_PAGINATION_LIMIT,
            'number' => EM_PAGINATION_LIMIT
        );
        $venues = $this->get_venues($args);
        foreach ($venues as $venue) {
            if( !empty( em_check_context_user_capabilities( array( 'view_event_sites' ) ) ) ) {
                if( empty( em_check_context_user_capabilities( array( 'view_others_event_sites' ) ) ) ) {
                    if( empty( $venue->created_by ) || $venue->created_by != get_current_user_id() ) {
                        continue;
                    }
                }
            } else{
                continue;
            }

            if (!empty($venue->gallery_images)) {
                $feature_image = wp_get_attachment_image_src($venue->gallery_images[0], 'large');
                $venue->feature_image = $feature_image[0];
            }

            // Number of upcoming events
            $args = array('tax_query' => array(array('taxonomy' => 'em_venue', 'field' => 'term_id', 'terms' => $venue->id)));
            $tmp_posts = query_posts($args);
            $venue->event_count = count($tmp_posts);
            $response->terms[] = $venue;
        }

        // Loading default sorting options
        $response->sort_options = em_array_to_options(array(
            "count" => __('No. of events', 'eventprime-event-calendar-management'),
            "name" => __('Alphabetically', 'eventprime-event-calendar-management'))
        );
        $response->sort_option = $sort_option;
        $response->tax_type = EM_EVENT_VENUE_TAX;
        $terms_count = wp_count_terms($response->tax_type, array('hide_empty' => false));
        $response->total_count = range(1, $terms_count);
        $response->pagination_limit = EM_PAGINATION_LIMIT;
        $response->current_user_id = get_current_user_id();
        return $response;
    }

    public function save($model) {
        $model= apply_filters('event_magic_before_venue_save', $model);
        $venue = $this->dao->save($model);
        do_action('event_magic_venue_saved', $venue);
        return $venue;
    }

    // Updating capacity in associated events
    private function update_events_capacity($venue_id, $event_id) {
        $venue = $this->load_model_from_db($venue_id);
        $filter = array('post_type' => EM_EVENT_POST_TYPE,
            'post_status' => array('publish', 'expired'),
            'tax_query' => array(
                array(
                    'taxonomy' => EM_EVENT_VENUE_TAX,
                    'field' => 'term_id',
                    'terms' => $venue->id,
                ),
            ),
        );
        $event_service = EventM_Factory::get_service('EventM_Service');
        $events = $event_service->get_events($filter);
        if (!empty($events) && $venue->seating_capacity > 0 && $venue->type == "seats") {
            foreach ($events as $event) {
                if ($event_id == $event->ID) {
                    em_update_post_meta($event->ID, 'seating_capacity', $venue->seating_capacity);
                }
            }
        }
    }


    public function get_upcoming_events($venue_id) {
        $event_service= EventM_Factory::get_service('EventM_Service');
        $events = $event_service->get_upcoming_events_by_venue($venue_id);
        return $events;
    }

    public function get_venues($args = array()) {
        $venues = $this->dao->get_all($args);
        return $venues;
    }

    public function get_venue_addresses_by_events($events = array()) {
        $venues= array();
        foreach ($events as $event) {
            if(empty($event->venue))
                continue;
            $venue= $this->load_model_from_db($event->venue);
            $venues[]= $venue;
        }
        return $venues;
    }

    public function capacity($venue_id) {
        $venue_id = absint($venue_id);
        if (empty($venue_id))
            return 0;

        return $this->dao->get_capacity($venue_id);
    }

    public function get_seats($venue_id, $event_id) {
        return $this->dao->get_meta($venue_id,'seats');
    }

    public function seating_options() {
        $types = array(
            "" => __('Select', 'eventprime-event-calendar-management'),
            "standings" => __('Standing', 'eventprime-event-calendar-management')
        );
        $types = apply_filters('event_magic_seating_types', $types);
        $dd = em_array_to_options($types);
        return $dd;
    }

    public function get_gallery_images($ids) {
        $images = array();
        if (is_array($ids)) {
            $image_ids = array_unique($ids);
            foreach ($image_ids as $image_id) {
                $tmp = new stdClass();
                $tmp->src = wp_get_attachment_image_src($image_id);
                $tmp->id = $image_id;
                $images[] = $tmp;
            }
        }
        return $images;
    }

    public function load_model_from_db($id) {
        $venue= $this->dao->get($id);
        return $this->format_model_from_db($venue);
    }

    public function map_request_to_model($id, $model = null) {

        $venue = new EventM_Venue_Model($id);
        $data = (array) $model;

        if (!empty($data) && is_array($data)) {
            foreach ($data as $key => $val) {
                if (property_exists($venue,$key)) {
                    $venue->{$key}= $val;
                }
            }
        }
        return $this->format_model_to_save($venue);
    }

    public function validate($model) {
        $errors= array();
        $term = term_exists($model->name, EM_EVENT_VENUE_TAX);
        if (!empty($term) && isset($term['term_id']) && $term['term_id'] != $model->id) {
            $errors[]= __('Please use different Venue name', 'eventprime-event-calendar-management');
        }
        return $errors;
    }
    
    private function format_model_to_save($model){
        $model->seating_capacity= absint($model->seating_capacity);
        $model->display_address_on_frontend = absint($model->display_address_on_frontend);
        return $model;
    }
    
    private function format_model_from_db($model){
        $model->seating_capacity = absint($model->seating_capacity);
        $model->display_address_on_frontend = absint($model->display_address_on_frontend);
        $model->standing_capacity = absint($model->standing_capacity);
        return $model;
    }

    public function stand_capacity($venue_id) {
        $venue_id = absint($venue_id);
        if (empty($venue_id))
            return 0;

        return $this->dao->get_stand_capacity($venue_id);
    }

    /*
     * Load List page with search result 
     */
    public function load_list_page_with_search() {
        $response = new stdClass();
        $response->terms = array();
        $sort_option = event_m_get_param('sort_option');
        $searchKeyword = !empty(event_m_get_param('searchKeyword')) ? event_m_get_param('searchKeyword') : '';
        $response->tax_type = EM_EVENT_VENUE_TAX;
        $terms_count = wp_count_terms($response->tax_type, array('hide_empty' => false, 'name__like' => $searchKeyword));
        if($terms_count <= EM_PAGINATION_LIMIT){
            $pagedS = 1;
         }else{
             $pagedS = event_m_get_param('pagedS');
         }
        $response->total_count = range(1, $terms_count);
        $args =array('hide_empty' => 0,
                    'orderby' => $sort_option,
                    'order' => event_m_get_param('order'),
                    'offset' => (int) ($pagedS - 1) * EM_PAGINATION_LIMIT,
                    'number' => EM_PAGINATION_LIMIT,
                    'name__like' => $searchKeyword);
        $venues= $this->get_venues($args);
        foreach ($venues as $venue) {
            if (!empty($venue->gallery_images)) {
                $feature_image = wp_get_attachment_image_src($venue->gallery_images[0], 'large');
                $venue->feature_image = $feature_image[0];
            }

            // Number of upcoming events
            $args = array('tax_query' => array(array('taxonomy' => 'em_venue', 'field' => 'term_id', 'terms' => $venue->id)));
            $tmp_posts = query_posts($args);
            $venue->event_count = count($tmp_posts);
            $response->terms[] = $venue;
        }

        // Loading default sorting options
        $response->sort_options = em_array_to_options(array("count" => __('No. of events', 'eventprime-event-calendar-management'),
                                  "name" => __('Alphabetically', 'eventprime-event-calendar-management')));
        $response->sort_option = $sort_option;
       
        $response->pagination_limit = EM_PAGINATION_LIMIT;
        $response->searchedKeyword = $searchKeyword;
        return $response;
    }

    
    public function count( $args , $em_search = '', $featured = '', $popular = '' ) {
        $meta_query = array();
        if( $featured == 1 ){
            array_push( $meta_query, array(
                array(
                    'key'     => em_append_meta_key('is_featured'),
                    'value'   => 1
                )
            ));
        }
        if( $popular == 1 )
            return 1;

        $em_search = ( $em_search != 'false' ) ? $em_search : '';
        $args['name__like'] = $em_search;
        $args['meta_query'] = $meta_query;
        $types = $this->dao->get_all($args);
        return count($types);
    }

    public function  get_all_venues_query( $args= array() ){
        $venues =  $this->dao->get_all_venues_query( $args );
        return $venues;
    }

    public function get_featured_venues( $num ) {
        $venues =  $this->dao->get_featured_venues( $num );
        return $venues;
    }

    public function get_popular_venues( $args = array() )
    {
        $response= new stdClass();
        $response->terms= array();
        $p_venues = array();
        $venues = $this->dao->get_all( $args );

        // Counting number of events for the event sites
        $event_count= array();
        $event_service = EventM_Factory::get_service('EventM_Service');
        $events= $event_service->get_all();
        foreach($events as $event){
            if(!empty($event->venue)){
                if(isset($event_count[$event->venue])){
                    $event_count[$event->venue] += 1;
                }
                else{
                    $event_count[$event->venue] = 1;
                } 
            }
        }
        
        foreach($venues as $venue){
           /* $venue->cover_image_url= $this->get_image( $venue->id, 'large' ); */
           if( isset( $event_count[ $venue->id ] ) ){
               $venue->events = $event_count[ $venue->id ];
           } else{
               $venue->events = 0;
           }
           $p_venues[] = $venue;
        }
       
        $response->terms[] = array_column( wp_list_sort( $p_venues , $orderby = 'events', $order = 'DESC') , 'id' );
        return $response->terms;
    }
}