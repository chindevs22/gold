<?php

if (!defined('ABSPATH')) {
    exit;
}

class EventTypeM_Service {

    private $dao;
    private static $instance = null;
    
    private function __construct() {
        $this->dao = new EventM_Event_Type_DAO();
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
        $event_type = $this->load_model_from_db($id);

        if( !empty( $id ) ) {
            if( $event_type->id != $id ){
                $response->error = 1;
                $response->message = esc_html__('Event type not found', 'eventprime-event-calendar-management');
                return $response;
            }

            if( !empty( em_check_context_user_capabilities( array( 'edit_event_types' ) ) ) ) {
                if( empty( em_check_context_user_capabilities( array( 'edit_others_event_types' ) ) ) ) {
                    if($event_type->created_by != get_current_user_id()) {
                        $response->error = 1;
                        $response->message = esc_html__( 'Unauthorized Access', 'eventprime-event-calendar-management' );
                        return $response;
                    }
                }
            } elseif( !empty( em_check_context_user_capabilities( array( 'edit_others_event_types' ) ) ) ) {
                if($event_type->created_by == get_current_user_id()) {
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

        $event_type->age_groups = $this->age_dropdown($event_type->age_group);
        $event_type->image = $this->get_image($event_type->image_id);
        $event_type->is_featured = absint($event_type->is_featured);
        return $event_type;
    }

    /*
     * Load List page for REST 
     */

    public function load_list_page() {
        $response = new stdClass();
        $response->terms = array();
        $sort_option = event_m_get_param('sort_option');
        $args = array(
            'hide_empty' => false,
            'offset' =>(absint(event_m_get_param('paged')) - 1) * EM_PAGINATION_LIMIT,
            'number' => EM_PAGINATION_LIMIT, 'orderby' => $sort_option, 'order' => event_m_get_param('order')
        );
        $response->terms = $this->get_types($args);
        $terms_count = wp_count_terms(EM_EVENT_TYPE_TAX, array('hide_empty' => false));
        if ($terms_count>0)
            $response->total_count = range(1, $terms_count);
        $response->pagination_limit = EM_PAGINATION_LIMIT;
        $response->tax_type = EM_EVENT_TYPE_TAX;
        $response->sort_options = em_array_to_options(array("term_id" => __('ID', 'eventprime-event-calendar-management'),
         "name" => __('Alphabetically', 'eventprime-event-calendar-management')));
        $response->sort_option = $sort_option;
        $response->current_user_id = get_current_user_id();
        return $response;
    }

    /*
     * Saving Event Type (Term) 
     */
    public function save($model) {
        // Check if user added any custom age group 
        $custom_age_group = isset($model->custom_group) ? $model->custom_group : '';
        if (!empty($custom_age_group)) {
            $model->custom_group = $custom_age_group;
        }
        // Check if user added any image
        $image_id = isset($model->image_id) ? $model->image_id : '';
        if (!empty($image_id)) {
            $model->image_id = $image_id;
        }
        $type = $this->dao->save($model);
        return $type;
    }

    public function age_dropdown($age_group) {
        $age_groups = array(
            "all" => __('All', 'eventprime-event-calendar-management'),
            "parental_guidance" => __('All ages but parental guidance', 'eventprime-event-calendar-management'),
            "custom_group" => __('Custom Age', 'eventprime-event-calendar-management'),
        );
        if (!empty( $age_group ) && !in_array($age_group, array("all", "parental_guidance", "custom_group"))){
            $age_groups[$age_group] = $age_group;
        }
        return em_array_to_options($age_groups);
    }

    public function load_model_from_db($id) {
        return $this->dao->get($id);
    }

    public function map_request_to_model($id, $model = null) {
        $type = new EventM_Event_Type_Model($id);
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
        $term = term_exists($model->name, EM_EVENT_TYPE_TAX);
        if (!empty($term) && isset($term['term_id']) && $term['term_id'] != $model->id) {
            $errors[]= __('Please use different name.','eventprime-event-calendar-management');
        }
        return $errors;
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

    public function get_types($args = array()) {
        $types = $this->dao->get_all($args);
        return $types;
    }
    
    public function get_image($id, $size='thumbnail') {
       $img = wp_get_attachment_image_src($id, $size);
       if (!empty($img))
         return $img[0];
       return null;
    }

    /*
     * Load List page with searched keyword results 
     */
    public function load_list_page_with_search() {
        $response = new stdClass();
        $response->terms = array();
        $sort_option = event_m_get_param('sort_option');
        $searchKeyword = !empty(event_m_get_param('searchKeyword')) ? event_m_get_param('searchKeyword') : '';
        $terms_count = wp_count_terms(EM_EVENT_TYPE_TAX, array('hide_empty' => false, 'name__like' => $searchKeyword));
        if($terms_count <= EM_PAGINATION_LIMIT){
           $pagedS = 1;
        }else{
            $pagedS = event_m_get_param('pagedS');
        }
        $args= array('hide_empty' => false,
                     'offset' =>(absint($pagedS) - 1) * EM_PAGINATION_LIMIT,
                     'number' => EM_PAGINATION_LIMIT,
                     'orderby' => $sort_option, 
                     'order' => event_m_get_param('order'),
                     'name__like' => $searchKeyword);
        $response->terms = $this->get_types($args);

        if ($terms_count>0)
            $response->total_count = range(1, $terms_count);
        $response->pagination_limit = EM_PAGINATION_LIMIT;
        $response->tax_type = EM_EVENT_TYPE_TAX;
        $response->sort_options = em_array_to_options(array("term_id" => __('ID', 'eventprime-event-calendar-management'),
        "name" => __('Alphabetically', 'eventprime-event-calendar-management')));
        $response->sort_option = $sort_option;
        $response->searchedKeyword = $searchKeyword;
        return $response;
    }

    public function get_front_types($args = array()) {
        $types = $this->dao->get_front_all($args);
        return $types;
    }

    public function  get_all_types_query( $args= array() ){
        $types =  $this->dao->get_all_types_query( $args );
        return $types;
    }

    public function get_featured_types( $num ) {
        $types =  $this->dao->get_featured_types( $num );
        return $types;
    }

    public function get_popular_types( $args = array() )
    {
        $response= new stdClass();
        $response->terms= array();
        $p_types = array();
        $types = $this->dao->get_all( $args );

        // Counting number of events for the event type
        $event_count= array();
        $event_service = EventM_Factory::get_service('EventM_Service');
        $events= $event_service->get_all();
        foreach($events as $event){
            if(!empty($event->event_type)){
                if(isset($event_count[$event->event_type])){
                    $event_count[$event->event_type] += 1;
                }
                else{
                    $event_count[$event->event_type] = 1;
                } 
            }
        }

        foreach($types as $type){
           $type->cover_image_url= $this->get_image( $type->id, 'large' );
           if( isset( $event_count[ $type->id ] ) ){
               $type->events = $event_count[ $type->id ];
           } else{
               $type->events = 0;
           }
           $p_types[] = $type;
        }

        $response->terms[] = array_column( wp_list_sort( $p_types , $orderby = 'events', $order = 'DESC',  $posts_per_page = 5 ) , 'id' );
        return $response->terms;
    }
}
?>