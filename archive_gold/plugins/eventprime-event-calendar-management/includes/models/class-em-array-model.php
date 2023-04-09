<?php
abstract class EventM_Array_Model
{
    public function to_array($getters= true)
    {  
        $obj= array();
        $vars= get_object_vars($this);
        foreach($vars as $key=>$val)
        { 
           $method= "get_".$key; 
           if($getters && method_exists($this, $method)) 
               $obj[$key]=  $this->$method();
           else
               $obj[$key]= $val;
           
        }
        return $obj;
    }
}
