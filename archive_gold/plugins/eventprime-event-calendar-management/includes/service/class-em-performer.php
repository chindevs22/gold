<?php

if (!defined( 'ABSPATH')){
    exit;
}

class EventM_Performer_Service {
    
    private $dao;
    private static $instance = null;
    
    private function __construct() {
        $this->dao= new EventM_Performer_DAO();
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
        $id = event_m_get_param('post_id');
        $performer = $this->load_model_from_db($id);
        if( !empty( $id ) ) {
            if( $performer->id != $id ){
                $response->error = 1;
                $response->message = esc_html__('Performer not found', 'eventprime-event-calendar-management');
                return $response;
            }
            if( !empty( em_check_context_user_capabilities( array( 'edit_event_performers' ) ) ) ) {
                if( empty( em_check_context_user_capabilities( array( 'edit_others_event_performers' ) ) ) ) {
                    if($performer->created_by != get_current_user_id()) {
                        $response->error = 1;
                        $response->message = esc_html__( 'Unauthorized Access', 'eventprime-event-calendar-management' );
                        return $response;
                    }
                }
            } elseif( !empty( em_check_context_user_capabilities( array( 'edit_others_event_performers' ) ) ) ) {
                if($performer->created_by == get_current_user_id()) {
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
        $performer->types =  $this->get_types();
        $performer->feature_image = $this->get_image($id);
        $performer->is_featured = absint($performer->is_featured);
        $social_sharing_fields = ep_dynamic_social_sharing_fields();
        $performer->social_fields = $social_sharing_fields;
        return $performer;
    }
    
    /*
     * Load List page for REST 
     */
    public function load_list_page()
    {
        $response = new stdClass();
        $response->posts = array();
        $sort_option = event_m_get_param('sort_option');
        
        // Get all the performers (posts)
        $args = array(
            'post_type' => EM_PERFORMER_POST_TYPE,
            'post_status' => 'publish'
        );
        $performers = $this->get_all($args);
       
        // Counting number of events for the performer
        $event_count= array();
        $event_service = EventM_Factory::get_service('EventM_Service');
        $events = $event_service->get_all();
        foreach($events as $event){
            if(!empty($event->performer) && !empty($event->enable_performer)){
                foreach($event->performer as $performer_id){
                    if(isset($event_count[$performer_id])){
                        $event_count[$performer_id] +=1;
                    }
                    else{
                        $event_count[$performer_id]= 1;
                    }
                }
            }
        }
        
        foreach($performers as $performer){
            if( !empty( em_check_context_user_capabilities( array( 'view_event_performers' ) ) ) ) {
                if( empty( em_check_context_user_capabilities( array( 'view_others_event_performers' ) ) ) ) {
                    if( empty( $performer->created_by ) || $performer->created_by != get_current_user_id() ) {
                        continue;
                    }
                }
            } else{
                continue;
            }

            $performer->cover_image_url = $this->get_image($performer->id,'large');
            if(isset($event_count[$performer->id])){
                $performer->events = $event_count[$performer->id];
            } else{
               $performer->events = 0;
            }
            $response->posts[] = $performer;
        }
        
        // Loading default sorting options
        $response->sort_options = em_array_to_options(
            array(
                "name" => __('Alphabetically', 'eventprime-event-calendar-management'),
                "count" => __('No. of events', 'eventprime-event-calendar-management')
            )
        );
        
        $post_count_obj = wp_count_posts($this->dao->post_type);
        $post_count = $post_count_obj->publish;
        
        $response->sort_option = $sort_option;
        $response->total_posts = range(1,$post_count);
        $response->pagination_limit = EM_PAGINATION_LIMIT;
        $response->offset = ((int) event_m_get_param('paged')-1) * EM_PAGINATION_LIMIT;
        $response->current_user_id = get_current_user_id();
        return $response;
    }
    
    public function save($model)
    {
       $performer= $this->dao->save($model);
       if(!empty($performer->feature_image_id)){
           $this->set_image($performer->id,$performer->feature_image_id);
       }
       return $performer;
    }

    public function get_upcoming_events(){
        $event_service= EventM_Factory::get_service('EventM_Service');
        $events= $event_service->get_upcoming_events();
        return $events;
    }
    
    public function load_model_from_db($id)
    {   
        return $this->dao->get($id);
    }
    
    public function map_request_to_model($id,$model=null)
    {  
        $performer= new EventM_Performer_Model($id);
        $data= (array) $model;
        
        if(!empty($data) && is_array($data))
        {
            foreach($data as $key=>$val)
            {
                if (property_exists($performer,$key)) {
                    $performer->{$key}= $val;
                }
            }
        }
        return $performer;
    }
    
    public function get_types()
    {
        $types= array(
            'person'=>__('Person', 'eventprime-event-calendar-management'),
            'group'=>__('Group', 'eventprime-event-calendar-management')
        );
        return em_array_to_options($types);
    }
    
    public function get_image($id,$size='thumbnail')
    {
       $img= wp_get_attachment_image_src(get_post_thumbnail_id($id),$size);
       if(!empty($img))
         return $img[0];
       return null;
    }
    
    public function set_image($id,$attach_id)
    {  
        if(!empty($attach_id)){
            $this->dao->set_thumbnail($id, $attach_id);
        }
    }
    
    public function get_all($args= array()){
       $performers=  $this->dao->get_all($args);
       return $performers;
    }
    
    public function count($args= array()){
        $performers=  $this->get_all($args);
        return count($performers);
    }

      /*
     * Load List page with search 
     */
    public function load_list_page_with_search()
    {
        $response= new stdClass();
        $response->posts= array();
        $sort_option = event_m_get_param('sort_option');
        $searchKeyword = !empty(event_m_get_param('searchKeyword')) ? event_m_get_param('searchKeyword') : '';
        // Get all the performers (posts)
        $args = array(
            'post_type' => EM_PERFORMER_POST_TYPE,
            'post_status' => 'publish',
            's' => $searchKeyword );

        $performers = $this->get_all($args);
       
        // Counting number of events for the performer
        $event_count= array();
        $event_service = EventM_Factory::get_service('EventM_Service');
        $events= $event_service->get_all();
        foreach($events as $event){
            if(!empty($event->performer) && !empty($event->enable_performer)){
                foreach($event->performer as $performer_id){
                    if(isset($event_count[$performer_id])){
                        $event_count[$performer_id] +=1;
                    }
                    else{
                        $event_count[$performer_id]= 1;
                    }
                }
            }
        }
        
        foreach($performers as $performer){
           $performer->cover_image_url= $this->get_image($performer->id,'large');
           if(isset($event_count[$performer->id])){
               $performer->events= $event_count[$performer->id];
           } else{
               $performer->events=0;
           }
           $response->posts[]= $performer;
        }
        
        // Loading default sorting options
        $response->sort_options = em_array_to_options(
            array(
                "name" => __('Alphabetically', 'eventprime-event-calendar-management'),
                "count" => __('No. of events', 'eventprime-event-calendar-management')
            )
        );
        
        $post_count_obj = wp_count_posts($this->dao->post_type);
        $post_count= $post_count_obj->publish;
        $response->sort_option = $sort_option;
        $response->total_posts= range(1,count($performers));
        $response->pagination_limit= EM_PAGINATION_LIMIT;
        if(count($response->total_posts) <= EM_PAGINATION_LIMIT){
            $pagedS = 1;
         }else{
            $pagedS = event_m_get_param('pagedS');
         }

        $response->offset= ((int) $pagedS-1) * EM_PAGINATION_LIMIT;
        $response->searchedKeyword = $searchKeyword;
        return $response;
    }

    public function validate($model) {
        $errors= array();
        foreach($model->performer_emails as $email) {
            if(!empty($email)){
                if (!preg_match('/^([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z]{2,5})$/',$email)) {
                    $errors[]=__('Incorrect email format.','eventprime-event-calendar-management');
                    break;
                }
            }
            
        }

        foreach($model->performer_websites as $website) {
            if(!empty($website)){
                if (!preg_match('/^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,6}(:[0-9]{1,5})?(\/.*)?$/',$website)) {
                    $errors[]=__('Incorrect website URL format.','eventprime-event-calendar-management');
                    break;
                }
            }    
        }
        return $errors;
    }

    public function  get_all_performers_query($args= array()){
        $performers =  $this->dao->get_all_performers_query($args);
        return $performers;
    }

    public function get_featured_performers($num) {
        $performers =  $this->dao->get_featured_performers($num);
        return $performers;
    }

    public function get_popular_performers($args = array()) {
        $response= new stdClass();
        $response->posts= array();
        $p_performers = array();
        $performers = $this->get_all($args);

        // Counting number of events for the performer
        $event_count= array();
        $event_service = EventM_Factory::get_service('EventM_Service');
        $events= $event_service->get_all();
        foreach($events as $event){
            if(!empty($event->performer) && !empty($event->enable_performer)){
                foreach($event->performer as $performer_id){
                    if(isset($event_count[$performer_id])){
                        $event_count[$performer_id] +=1;
                    }
                    else{
                        $event_count[$performer_id]= 1;
                    }
                }
            }
        }
        
        foreach($performers as $performer){
            $performer->cover_image_url= $this->get_image($performer->id,'large');
            if(isset($event_count[$performer->id])){
                $performer->events= $event_count[$performer->id];
            } else{
                $performer->events=0;
            }
            $p_performers[] = $performer;
        }
        $response->posts[] = array_column(wp_list_sort( $p_performers , $orderby = 'events', $order = 'DESC',  $posts_per_page = 5 ) , 'id');
    
        return $response->posts;
    }
}
