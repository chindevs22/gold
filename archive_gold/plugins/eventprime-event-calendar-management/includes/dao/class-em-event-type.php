<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class EventM_Event_Type_DAO extends EventM_Term_Dao
{   
    public function __construct() {
        parent::__construct(EM_EVENT_TYPE_TAX);
    }

    public function get($id) { 
        $term = $this->get_single($id);
        if (empty($term) || is_wp_error($term))
            return new EventM_Event_Type_Model(0);

        $type = new EventM_Event_Type_Model($id);
        $meta = $this->get_meta($id,'',true);
        if(is_array($meta)){
            foreach ($meta as $key=>$val) {
                $key = str_replace('em_','',$key);
                if (property_exists($type, $key)) {
                   $type->{$key}= maybe_unserialize($val[0]);
                }
            }
        }
        
        $type->id = $term->term_id;
        $type->name = htmlspecialchars_decode($term->name);
        $type->count = $term->count;

        if(is_admin()){
            if( !empty( em_check_context_user_capabilities( array( 'view_event_types' ) ) ) ) {
                if( empty( em_check_context_user_capabilities( array( 'view_others_event_types' ) ) ) ) {
                    if( !empty( $type->created_by ) && $type->created_by == get_current_user_id() ) {
                        return $type;
                    }
                } else{
                    return $type;
                }
            }

            if( !empty( em_check_context_user_capabilities( array( 'create_event_types' ) ) ) ) {
                if( empty( $id ) ) {
                    return $type;
                }
            }
            return null;
        }else{
            return $type;
        }
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
            $typeData = $this->get($term->term_id);
            if( !empty( $typeData ) ) {
                $types[] = $typeData;
            }
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
    
    public function get_front_all($args= array()) {
        $defaults= array('hide_empty' => false);
        $args = wp_parse_args($args,$defaults);
        $terms = parent::get_all($args);
        $types = array();
        if(empty($terms) || is_wp_error($terms)){
           return $types;
        }
        foreach($terms as $term){
            $typeData = $this->get_front($term->term_id);
            if( !empty( $typeData ) ) {
                $types[] = $typeData;
            }
        }
        return $types;
    }

    public function get_front($id) { 
        $term = $this->get_single($id);
        if (empty($term) || is_wp_error($term))
            return new EventM_Event_Type_Model(0);

        $type = new EventM_Event_Type_Model($id);
        $meta = $this->get_meta($id,'',true);
        if(is_array($meta)){
            foreach ($meta as $key=>$val) {
                $key = str_replace('em_','',$key);
                if (property_exists($type, $key)) {
                   $type->{$key}= maybe_unserialize($val[0]);
                }
            }
        }
        
        $type->id = $term->term_id;
        $type->name = htmlspecialchars_decode($term->name);
        $type->count = $term->count;

        return $type;
    }

    public function get_all_types_query( $args = array() ) {
        $defaults= array( 
         'hide_empty' => false ,
        );
      
        $args = wp_parse_args( $args, $defaults );
        $terms = parent::get_all( $args );
        $types = array();
        if( empty( $terms ) || is_wp_error( $terms ) ){
           return $types;
        }
        foreach($terms as $term){
            $typeData = $this->get_front($term->term_id);
            if( !empty( $typeData ) ) {
                $types[] = $typeData;
            }
        }

        $wp_query = new WP_Term_Query( $args );
        $wp_query->terms = $types;
        
        return $wp_query;
    }

    /**
     * get featured types list
     */
    public function get_featured_types( $num ) { 
        $args = array( 
            'hide_empty' => false ,
            'number' => $num,
            'meta_query' => array(  
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
                ),
            ),
            array(
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