<?php
if (!defined( 'ABSPATH')){
    exit; // Exit if accessed directly
}

class EventM_Global_Settings_DAO 
{
    public function save($model)
    {   
        $options= get_option(EM_GLOBAL_SETTINGS);
        foreach($model as $key=>$val){
            $options[$key]= $val;
        }
        update_option(EM_GLOBAL_SETTINGS, $options);
    }
    
    public function get() { 
        $options= get_option(EM_GLOBAL_SETTINGS);
        $settings= new EventM_Global_Settings_Model();
        foreach ($options as $key=>$val) {
            $key= str_replace('em_','',$key);
            if (property_exists($settings, $key)) {
               $settings->{$key}= maybe_unserialize($val);
            }
        }
        $settings= apply_filters('event_magic_gs_get_model',$settings,$options);
        return $settings;
    }
}
