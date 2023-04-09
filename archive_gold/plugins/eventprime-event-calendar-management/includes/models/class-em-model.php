<?php

abstract class EventM_Model{
    
    protected $data;
    
    public function __construct($data_elements= array()) {
       $this->data= new stdClass();
       
       foreach($data_elements as $element){
           $this->data->$element= new stdClass();
       }
    }
    
    protected abstract function loadData();
    
    public function get_data(){
        return $this->data;
    }
}

