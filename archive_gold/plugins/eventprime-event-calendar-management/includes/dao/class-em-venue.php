<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class EventM_Venue_DAO extends EventM_Term_Dao {

    public function __construct() {
        parent::__construct(EM_EVENT_VENUE_TAX);
    }

    public function get_all($args= array()) {
       $defaults = array('taxonomy' => EM_EVENT_VENUE_TAX,'hide_empty' => false);
       $venues= array();
       $args= wp_parse_args($args,$defaults);
       $terms=parent::get_all($args);
       if(empty($terms) || is_wp_error($terms)){
           return $venues;
       }
       
       foreach($terms as $term){
           $venues[]= $this->get($term->term_id);
       }
       return $venues;
    }

    public function get_capacity($id) {
        $capacity= absint($this->get_meta($id,'seating_capacity'));
        return $capacity>0 ? $capacity : 0;
    }

    public function get($id) { 
        $term= $this->get_single($id);
        if (empty($term) || is_wp_error($term))
            return new EventM_Venue_Model();

        $venue = new EventM_Venue_Model();
        $meta= get_term_meta($id,'',true);
        foreach ($meta as $key=>$val) {
            $key= str_replace('em_','',$key);
            if (property_exists($venue, $key)) {
               $venue->{$key}= maybe_unserialize($val[0]);
            }
        }
        $venue->id= $term->term_id;
        $venue->name=$term->name;
        $venue->slug=$term->slug;
        return $venue;
    }

    public function create($venue, $id = 0) {
        return $this->insert_or_update($venue, EM_EVENT_VENUE_TAX, $id);
    }
    
    public function save($model){
        $term= parent::save($model);
        if ($term instanceof WP_Error) {
            return false;
        }
        return $this->get($term['term_id']);
    }

    /**
     * get venue standing capacity
     */
    public function get_stand_capacity($id) {
        $capacity = absint( $this->get_meta( $id, 'standing_capacity' ) );
        return $capacity > 0 ? $capacity : 0;
    }

    public function get_all_venues_query( $args = array() ) {
        $defaults= array( 
         'hide_empty' => false ,
        );
      
        $args = wp_parse_args( $args, $defaults );
        $terms = parent::get_all( $args );
        $venues = array();
        if( empty( $terms ) || is_wp_error( $terms ) ){
           return $venues;
        }
        foreach( $terms as $term ){
           $venues[] = $this->get( $term->term_id );
        }

        $wp_query = new WP_Term_Query( $args );
        $wp_query->terms = $venues;
        
        return $wp_query;
    }

     /**
     * get featured types list
     */
    public function get_featured_venues( $num ) { 
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