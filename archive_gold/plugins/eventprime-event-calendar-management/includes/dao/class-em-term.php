<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class EventM_Term_Dao
{
    public $tax_type;
    
    public function __construct($tax_type) {
        $this->tax_type= $tax_type;
    }
    public function save($model)
    {   
        $args['name']= $model->name;
        if(isset($model->slug)){
             $args['slug']= $model->slug;
        }

        if(!empty($model->id))
           $term= wp_update_term($model->id,$this->tax_type,$args);
        else
           $term= wp_insert_term(wp_strip_all_tags($model->name), $this->tax_type);
      
       
         // In case of any errors
        if ($term instanceof WP_Error) {
            return $term;
        }
        foreach ($model as $key=>$value) {
            if(in_array($key,array('id','name','slug','count'))){
                continue;
            }
            em_update_term_meta($term['term_id'], $key, $value); 
        }
        
        return $term;
    }
    
    public function get_all($args= array())
    {
        return get_terms($this->tax_type,$args);
    }
    
    public function get_single($term=0)
    {
        return get_term($term);
    }
    
    public function get_meta($term,$meta='',$single= true)
    {   
        if(is_object($term) && isset($term->term_id))
            return  em_get_term_meta($term->term_id, $meta, $single);
        else 
            return  em_get_term_meta($term, $meta, $single);  
    }
    
    public function insert_or_update($term, $type, $id = 0)
    {
        if ($id > 0)
            return wp_update_term($id, $type, $term);
        else
            return wp_insert_term($term, $type);
    }
}
