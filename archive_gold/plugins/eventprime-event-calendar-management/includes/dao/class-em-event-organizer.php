<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class EventM_Event_Organizer_DAO extends EventM_Term_Dao
{   
    public function __construct() {
        parent::__construct(EM_EVENT_ORGANIZER_TAX);
    }

    public function get($id) { 
       $term = $this->get_single($id);
        if (empty($term) || is_wp_error($term))
            return new EventM_Event_Organizer_Model(0);

        $type = new EventM_Event_Organizer_Model($id);
        $meta = $this->get_meta($id,'',true);
        if(is_array($meta)){
            foreach ($meta as $key => $val) {
                $key = str_replace('em_','',$key);
                if (property_exists($type, $key)) {
                   $type->{$key} = maybe_unserialize($val[0]);
                }
            }
        }
        
        $type->id = $term->term_id;
        $type->name = htmlspecialchars_decode($term->name);
        $type->count = $term->count;
        return $type;
    }
    
    public function get_all($args= array()) {
        $defaults= array('hide_empty' => false);
        $args = wp_parse_args($args,$defaults);
        $terms = parent::get_all($args);
        
        $types = array();
        if(empty($terms) || is_wp_error($terms)){
           return $types;
        }
        foreach($terms as $term){
           $types[] = $this->get($term->term_id);
        }
        return $types;
    }
    
    public function save($model){
        $term= parent::save($model);
        if ($term instanceof WP_Error) {
            return false;
        }
        return $this->get($term['term_id']);
    }
    
    public function get_all_back($args= array()) {
        $defaults= array( 'hide_empty' => false );
        $args = wp_parse_args($args,$defaults);
        $terms = parent::get_all($args);
        
        $types = array();
        if(empty($terms) || is_wp_error($terms)){
           return $types;
        }
        foreach($terms as $term){
            $term_data = $this->get_back($term->term_id);
            if(!empty($term_data)){
                $types[] = $term_data;
            }
        }
        return $types;
    }

    public function get_back($id) { 
        $term = $this->get_single($id);
        if (empty($term) || is_wp_error($term))
            return new EventM_Event_Organizer_Model(0);

        $type = new EventM_Event_Organizer_Model($id);
        $meta = $this->get_meta($id,'',true);
        if(is_array($meta)){
            foreach ($meta as $key => $val) {
                $key = str_replace('em_','',$key);
                if (property_exists($type, $key)) {
                   $type->{$key} = maybe_unserialize($val[0]);
                }
            }
        }
        
        $type->id = $term->term_id;
        $type->name = htmlspecialchars_decode($term->name);
        $type->count = $term->count;

        if( !empty( em_check_context_user_capabilities( array( 'view_event_organizers' ) ) ) ) {
            if( empty( em_check_context_user_capabilities( array( 'view_others_event_organizers' ) ) ) ) {
                if( !empty( $type->created_by ) && $type->created_by == get_current_user_id() ) {
                    return $type;
                }
            } else{
                return $type;
            }
        }

        if( !empty( em_check_context_user_capabilities( array( 'create_event_organizers' ) ) ) ) {
            if( empty( $id ) ) {
                return $type;
            }
        }
        return null;
    }

    public function get_all_organizers_query( $args = array() ) {
        $defaults= array( 
         'hide_empty' => false ,
        );
      
        $args = wp_parse_args( $args, $defaults );
        $terms = parent::get_all( $args );
        $types = array();
        if( empty( $terms ) || is_wp_error( $terms ) ){
           return $types;
        }
        foreach( $terms as $term ){
           $types[] = $this->get( $term->term_id );
        }

        $wp_query = new WP_Term_Query( $args );
        $wp_query->terms = $types;
        
        return $wp_query;
    }

     /**
     * get featured organizer list
     */
    public function get_featured_organizers( $num ) { 
        $args= array( 
            'hide_empty' => false ,
            'number' => $num,
            'meta_query' => array(
                'key' => em_append_meta_key('is_featured'),
                'value'   => 1
            )
        );

        $terms = parent::get_all( $args );
        $types = array();
        if( empty( $terms ) || is_wp_error( $terms ) ){
            return $types;
        }
        foreach( $terms as $term ){
            $types[] = $this->get( $term->term_id );
        }

        return $types;
    }
    
}