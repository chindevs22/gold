<?php

abstract class EventM_Payment_Service 
{
    private $payment_processor;
    
    public function __construct($payment_processor) 
    {
        $this->payment_processor= $payment_processor;
    }
    
    abstract function charge($info= array());
    abstract function refund($order_id,$info= array());
    abstract function cancel($order_id);
}
