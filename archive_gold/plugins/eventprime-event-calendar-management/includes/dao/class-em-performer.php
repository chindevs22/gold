<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class EventM_Performer_DAO extends EventM_Post_Dao
{
    public function __construct() {
        parent::__construct(EM_PERFORMER_POST_TYPE);
    }
    
    public function set_thumbnail($id,$img_id) { 
        set_post_thumbnail($id, $img_id);
    }
    
    public function get_upcoming_events($performer_id,$events= array()){
        $performer_events= array();
        if(is_array($events)){
            foreach($events as $event){
                if(!is_object($event))
                    $event= get_post ($event);
                $performers= em_get_post_meta($event->ID, 'performer', true);               
                if(!empty($performers) && is_array($performers)){
                    if(in_array($performer_id, $performers))
                        $performer_events[]= $event;
                }
                
            }
        }
        return $performer_events;
    }
    
    public function get($id)
    {
        $post= empty($id) ? 0 : get_post($id);
        if(empty($post))
            return new EventM_Performer_Model(0);
        
        $performer = new EventM_Performer_Model($id);
        $meta = $this->get_meta($id,'',true);
        foreach ($meta as $key=>$val) {
            $key = str_replace('em_','',$key);
            if (property_exists($performer, $key)) {
               $performer->{$key} = maybe_unserialize($val[0]);
            }
        }
        $performer->id = $post->ID;
        $performer->name = $post->post_title;
        $performer->slug = $post->post_name;
        $performer->description = $post->post_content;
        return $performer;
    }
    
    public function get_all($args= array()){
        $default = array(
            'orderby'          => 'title',
            'numberposts'      => -1,
            'offset'           => 0,     
            'order'            => 'ASC',
	        'post_type'        => $this->post_type,
	        'post_status'      => 'publish'
        );
        $args = wp_parse_args($args, $default);  
        $posts = $this->get_posts($args);
        if(empty($posts))
           return array();
       
        $performers = array();
        foreach($posts as $post){
            $performers[] = $this->get($post->ID);
        }
        return $performers;
    }
    
    public function save($model){
        $post_id= parent::save($model);
        
        if ($post_id instanceof WP_Error) {
            return false;
        }
        return $this->get($post_id);
    }
    
    public function get_all_performers_query($args= array()){
        $default = array(
            'orderby'          => 'title',
            'numberposts'      => -1,
            'offset'           => 0,     
        	'order'            => 'ASC',
        	'post_type'        => $this->post_type,
        	'post_status'      => 'publish',
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
            )
        );
        $args = wp_parse_args($args, $default);
  
        $posts = $this->get_posts($args);
        if(empty($posts))
           return array();
       
        $performers= array();
        foreach($posts as $post){
            $performers[]= $this->get($post->ID);
        }
        $wp_query = new WP_Query($args);

        $wp_query->posts = $performers;
        return $wp_query;
    }

    /* get featured performer list */
    public function get_featured_performers($num) { 
        $filter = array(
            'orderby' => 'title',
            'numberposts' => $num,
            'order' => 'ASC',
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key'   => em_append_meta_key('is_featured'),
                    'value' => 1,
                )
            ),
            'post_type' => EM_PERFORMER_POST_TYPE,
        );
        $posts = get_posts($filter);
        return $posts;
    }
}
