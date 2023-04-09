<?php

if (!defined('ABSPATH')) {
    exit;
}

class EventOrganizerM_Service {

    private $dao;
    private static $instance = null;
    
    private function __construct() {
        $this->dao = new EventM_Event_Organizer_DAO();
    }
    
    public static function get_instance()
    {   
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
        $id = event_m_get_param('term_id');
        $event_organizer = $this->load_model_from_db($id);
        if( !empty( $id ) ) {
            if( $event_organizer->id != $id ){
                $response->error = 1;
                $response->message = esc_html__('Organizer not found', 'eventprime-event-calendar-management');
                return $response;
            }
            if( !empty( em_check_context_user_capabilities( array( 'edit_event_organizers' ) ) ) ) {
                if( empty( em_check_context_user_capabilities( array( 'edit_others_event_organizers' ) ) ) ) {
                    if($event_organizer->created_by != get_current_user_id()) {
                        $response->error = 1;
                        $response->message = esc_html__( 'Unauthorized Access', 'eventprime-event-calendar-management' );
                        return $response;
                    }
                }
            } elseif( !empty( em_check_context_user_capabilities( array( 'edit_others_event_organizers' ) ) ) ) {
                if($event_organizer->created_by == get_current_user_id()) {
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
        $event_organizer->image = $this->get_image($event_organizer->image_id);
        $event_organizer->is_featured = absint($event_organizer->is_featured);
        $social_sharing_fields = ep_dynamic_social_sharing_fields();
        $event_organizer->social_fields = $social_sharing_fields;
        return $event_organizer;
    }

    /*
     * Load List page for REST 
     */

    public function load_list_page() {
        $response = new stdClass();
        $response->terms = array();
        $sort_option = event_m_get_param('sort_option');
        $args = array('hide_empty' => false,
            'offset' =>(absint(event_m_get_param('paged')) - 1) * EM_PAGINATION_LIMIT,
            'number' => EM_PAGINATION_LIMIT, 
            'orderby' => $sort_option, 
            'order' =>  event_m_get_param('order')
        );
        $response->terms = $this->get_organizers($args);
        $terms_count = wp_count_terms(EM_EVENT_ORGANIZER_TAX, array('hide_empty' => false));
        if ($terms_count > 0){
            $response->total_count = range(1, $terms_count);
        }
        $response->pagination_limit = EM_PAGINATION_LIMIT;
        $response->tax_type = EM_EVENT_ORGANIZER_TAX;
        // Loading default sorting options
        $response->sort_options = em_array_to_options(
            array(
                "term_id" => __('ID', 'eventprime-event-calendar-management'),
                "name"  => __('Alphabetically', 'eventprime-event-calendar-management')
            )
        );
        $response->sort_option = $sort_option;
        $response->current_user_id = get_current_user_id();
        return $response;
    }

    /*
     * Saving Event Type (Term) 
     */
    public function save($model) {

        // Check if user added any image
        $image_id = isset($model->image_id) ? $model->image_id : '';
        if (!empty($image_id)) {
            $model->image_id = $image_id;
        }
        $organizer = $this->dao->save($model);
        return $organizer;
    }

    public function load_model_from_db($id) {
        return $this->dao->get($id);
    }

    public function map_request_to_model($id, $model = null) {
        $type = new EventM_Event_Organizer_Model($id);
        $data = (array) $model;

        if (!empty($data) && is_array($data)) {
            foreach ($data as $key => $val) {
                if(property_exists($type, $key)){
                    $type->{$key}= $val;
                }
            }
        }
        return $type;
    }

    public function validate($model) {
        $errors= array();
        $term = term_exists($model->name, EM_EVENT_ORGANIZER_TAX);
        if (!empty($term) && isset($term['term_id']) && $term['term_id'] != $model->id) {
            $errors[]= __('Please use different name.','eventprime-event-calendar-management');
        }
        foreach($model->organizer_emails as $email) {
            if(!empty($email)){
                if (!preg_match('/^([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z]{2,5})$/',$email)) {
                    $errors[]=__('Incorrect email format.','eventprime-event-calendar-management');
                    break;
                }
            }
        }

        foreach($model->organizer_websites as $website) {
            if(!empty($website)){
                if (!preg_match('/^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/',$website)) {
                    $errors[]=__('Incorrect website URL format.','eventprime-event-calendar-management');
                    break;
                }
            }
        }
        return $errors;
    }

    public function count( $args, $em_search = '', $featured = '', $popular = '' ) {
        $meta_query = [];
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
        $types = $this->dao->get_all( $args );
        return count($types);
    }

    public function get_organizers($args = array()) {
        $types = $this->dao->get_all_back($args);
        return $types;
    }

    public function get_organizers_front($args = array()) {
        $types = $this->dao->get_all($args);
        return $types;
    }
    
    public function get_image($id, $size='thumbnail') {
       $img = wp_get_attachment_image_src($id, $size);
       if (!empty($img))
         return $img[0];
       return null;
    }

    public function get_organizer($term_id) {
        $term_id = absint($term_id);
        if (empty($term_id))
            return 0;

        return $this->dao->get($term_id);
    }

    /*
     * Load List page for Search result 
     */

    public function load_list_page_with_search() {
        $response = new stdClass();
        $response->terms = array();
        $sort_option = event_m_get_param('sort_option');
        $searchKeyword = !empty(event_m_get_param('searchKeyword')) ? event_m_get_param('searchKeyword') : '';
        $terms_count = wp_count_terms(EM_EVENT_ORGANIZER_TAX, array('hide_empty' => false, 'name__like' => $searchKeyword));
        if($terms_count <= 10){
           $pagedS = 1;
        }else{
            $pagedS = event_m_get_param('pagedS');
        }
        $args= array('hide_empty' => false,
            'offset'     =>(absint($pagedS) - 1) * EM_PAGINATION_LIMIT,
            'number'     => EM_PAGINATION_LIMIT, 
            //'orderby'    => 'term_id',
            'orderby'    =>  $sort_option,
            'order'      => 'DESC', 
            'name__like' => $searchKeyword);
        $response->terms = $this->get_organizers($args);
        if ($terms_count > 0){
            $response->total_count = range(1, $terms_count);
        }
        $response->pagination_limit = EM_PAGINATION_LIMIT;
        $response->tax_type = EM_EVENT_ORGANIZER_TAX;
        $response->sort_options = em_array_to_options(
            array(
                "term_id" => __('ID', 'eventprime-event-calendar-management'),
                "name" => __('Alphabetically', 'eventprime-event-calendar-management')
            )
        );
        $response->sort_option = $sort_option;
        $response->searchedKeyword = $searchKeyword;
        return $response;
    }

    public function  get_all_organizers_query( $args= array() ){
        $organizers =  $this->dao->get_all_organizers_query( $args );
        return $organizers;
    }

    public function get_featured_organizers( $num ) {
        $organizers =  $this->dao->get_featured_organizers( $num );
        return $organizers;
    }

    public function get_popular_organizers( $args = array() ) {
        $response = new stdClass();
        $response->terms = $p_organizers = array();
        $organizers = $this->dao->get_all( $args );

        // Counting number of events for the organizers
        $event_count= array();
        $event_service = EventM_Factory::get_service('EventM_Service');
        $events= $event_service->get_all();
        foreach($events as $event){
            if(!empty($event->organizer)){
                foreach($event->organizer as $organizer_id){
                    if(isset($event_count[$organizer_id])){
                        $event_count[$organizer_id] +=1;
                    }
                    else{
                        $event_count[$organizer_id]= 1;
                    }
                }
            }
        }
        
        foreach($organizers as $organizer){
           $organizer->cover_image_url= $this->get_image( $organizer->id, 'large' );
           if( isset( $event_count[ $organizer->id ] ) ){
               $organizer->events = $event_count[ $organizer->id ];
           } else{
               $organizer->events = 0;
           }
           $p_organizers[] = $organizer;
        }
       
        $response->terms[] = array_column( wp_list_sort( $p_organizers , $orderby = 'events', $order = 'DESC',  $posts_per_page = 5 ) , 'id' );
        return $response->terms;
    }

}?>