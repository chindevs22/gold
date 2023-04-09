<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class EventM_Post_Dao
{
    public $post_type;
    
    public function __construct($post_type) {
        $this->post_type= $post_type;
    }

    public function save($model)
    {
        $args = array(
            'post_title' => wp_strip_all_tags($model->name),
            'post_content' => isset($model->description) ? $model->description : ' ' ,
            'post_status' => $model->status,
            'post_type' => $this->post_type
        );
        
        $args = apply_filters('eventprime_save_into_post_model',$args,$model);
        
        if(isset($model->slug))
        {
            $args['post_name']=$model->slug;
        }
        
        if($model->id>0)
        {
            $args['ID']= $model->id;
            $post= wp_update_post($args,true);
        } 
        else    
            $post= wp_insert_post($args);
        
        if ($post instanceof WP_Error) {
            return $post;
        }
        
        foreach($model as $key=>$val)
        {
            if(in_array($key,array('id','name','slug','description','status','parent'))){
                continue;
            }
           
            $this->set_meta($post,$key,$val);  
        }
        
        do_action('event_magic_update_child_events',$model->id);
        
        return $post;
    }
    
    public function get_meta($post,$meta,$single= true)
    {   
        if(is_object($post) && isset($post->ID))
            return em_get_post_meta($post->ID, $meta, $single);
        else 
            return  em_get_post_meta($post, $meta, $single);  
    }
    
    public function set_meta($post,$meta,$meta_value)
    {   
        if(is_object($post) && isset($post->ID))
            return em_update_post_meta($post->ID, $meta, $meta_value);
        else 
            return em_update_post_meta($post, $meta, $meta_value);  
    }
    
    public function get_posts($args){
        $default = array(
	'orderby'          => 'date',
        'numberposts'      => -1,
        'offset'           => 0,     
	'order'            => 'DESC',
	'post_type'        => $this->post_type,
	'post_status'      => 'publish');
         $args= wp_parse_args($args, $default);  
         $posts= get_posts($args);
         if(empty($posts))
            return array();
        
         return $posts;
    }
    
    public function delete_post($id){
        wp_delete_post($id);
    }
}
