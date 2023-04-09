<?php
if (!defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class EventM_Booking_DAO extends EventM_Post_Dao{
    
    public function __construct() {
        parent::__construct(EM_BOOKING_POST_TYPE);
    }
    
    public function get_tmp_bookings($user_id){ 
         $args = array(
            'orderby' => 'date',
            'order' => 'DESC',
            'post_status'=> 'pending',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => em_append_meta_key('user'), 
                    'value' => $user_id, 
                    'compare' => '=', 
                    'type' => 'NUMERIC,'
                ),
                array(
                    'key' => em_append_meta_key('booking_tmp_status'), 
                    'value' => 1, 
                    'compare' => '=', 
                    'type' => 'NUMERIC,' 
                ),
            ),
            'post_type' => $this->post_type);
         
        return $this->get_all($args);
    }
    
    public function get_all_tmp_bookings(){
         $args = array(
            'orderby' => 'date',
            'order' => 'DESC',
            'post_status'=> 'pending',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => em_append_meta_key('date'), 
                    'value' => current_time( 'timestamp' )-240, 
                    'compare' => '<=', 
                    'type' => 'NUMERIC,'
                ),
                array(
                    'key' => em_append_meta_key('booking_tmp_status'), 
                    'value' => 1, 
                    'compare' => '=', 
                    'type' => 'NUMERIC,' 
                )
            ),
            'post_type' => $this->post_type);
         
        return $this->get_all($args);
    }
    
    public function get_bookings_by_user($user_id)
    {
        $filter = array(
            'numberposts'=>-1,
            'orderby' => 'date',
            'order' => 'DESC',
            'post_status'=> 'any',
            'meta_query' => array(
                array(
                    'key' => em_append_meta_key('user'), 
                    'value' => $user_id, 
                    'compare' => '=', 
                    'type' => 'NUMERIC,'
                )
            ),
            'post_type' => $this->post_type
        );
        
        return $this->get_all($filter);
    }
    
    public function get_event_booking($booking_id){  
        $data = new stdClass(); 
        $venue_service= EventM_Factory::get_service('EventM_Venue_Service');
        $booking_service = EventM_Factory::get_service('EventM_Booking_Service');
        
        $booking= get_post($booking_id);
            if(empty($booking)){
                echo "No such booking exists for Order ID #".$booking_id;
             return;   
            }
   
        $event_id= em_get_post_meta($booking_id,'event_id',true);   
        $event_service= EventM_Factory::get_service('EventM_Service');
        $event= $event_service->load_model_from_db($event_id);
        if(!empty($event->id))
        {
            $data->ID = $booking_id;
            $data->event_id=$event->id;
            $data->event_date=em_showDateTime(strtotime($event->start_date), true);
            $data->event_name = $event->name;
            $data->description=$event->description;  
            $order_info= get_post_meta($booking_id,'em_order_info',true);  
            if(!empty($event->venue)){
                $venue= $venue_service->load_model_from_db($event->venue);
                $data->address = $venue->address;
                $data->venue_name=$venue->name;
                $data->type = $venue->type;
                $data->venue_id= $event->venue;
                if($venue->type=='seats')
                {            
                    if(($order_info['seat_sequences'])>0)
                    {                  
                        $data->seat_sequence = implode(',',$order_info['seat_sequences']); 
                    }
                } 
            }
             
            $currency_symbol= $order_info['currency'];
            $payment_log= maybe_unserialize(em_get_post_meta($booking_id, 'payment_log', true));   
            if(isset($currency_symbol) && !empty($currency_symbol))
            {
                      $currency_symbol= $currency_symbol;
            }
            elseif(isset($payment_log['payment_gateway']) && ($payment_log['payment_gateway'] == 'paypal' ))
            {
                $currency_symbol = $payment_log['mc_currency'];
            }

            $data->item_price = $order_info['item_price'];
            $data->order_info=  $order_info;
            $data->total_price=   $booking_service->get_final_price($booking->ID);  
            if(empty($data->item_price)){
                $data->item_price = __('Free','eventprime-event-calendar-management');
                $data->total_price= __('Free','eventprime-event-calendar-management');
            }
            else
            {
                $data->item_price = $order_info['item_price'].$currency_symbol;   
                $data->total_price= $data->total_price.$currency_symbol;

            }
        }
            
        $data->discount = $order_info['discount'].$currency_symbol;
     return $data;
    }
    
    public function get($id)
    {
        $post= empty($id) ? 0 : get_post($id);
        
        if(empty($post))
            return new EventM_Booking_Model();
            
        $booking= new EventM_Booking_Model($id);
        $meta= $this->get_meta($id,'',true);
        
        if(!empty($meta)){
            foreach ($meta as $key=>$val) {
                $key= str_replace('em_','',$key);
                if (property_exists($booking, $key)) {
                   $booking->{$key}= maybe_unserialize($val[0]);
                }
            }
        }

        $booking->id= $post->ID;
        $booking->status= $post->post_status;
        $booking= apply_filters('event_magic_booking_get_model',$booking);
        return $booking;
    }
    
    public function get_all($args= array()){
       $defaults= array('post_type' => $this->post_type,'numberposts'=>-1,'orderby'=>'date','order'=>'ASC','post_status'=>'any');
       $args = wp_parse_args($args,$defaults);
       $posts= $this->get_posts($args);
       if(empty($posts))
           return array();
       
       $bookings= array();
       foreach($posts as $post){
           $bookings[]= $this->get($post->ID);
       }
       return $bookings;
    }
    
    public function save($model){
        $post_id= parent::save($model);
        
        if ($post_id instanceof WP_Error) {
            return false;
        }
        return $this->get($post_id);
    }

    public function get_bookings_by_user_id_event_id($user_id, $event_id){ 
        $args = array(
           'orderby' => 'date',
           'order' => 'DESC',
           'post_status'=> 'completed',
           'meta_query' => array(
               'relation' => 'AND',
                array(
                   'key' => em_append_meta_key('user'), 
                   'value' => $user_id, 
                   'compare' => '=', 
                   'type' => 'NUMERIC,'
                ),
                array(
                   'key' => em_append_meta_key('event'), 
                   'value' => $event_id, 
                   'compare' => '=', 
                   'type' => 'NUMERIC,' 
                ),
            ),
            'post_type' => $this->post_type
        );
        
        return $this->get_all($args);
    }

    public function get_multi_price_booking_count($id, $event_id) {
        $count = 0;
        $args = array(
            'post_status' => 'completed',
            'posts_per_page' => -1,
            'post_type' => $this->post_type,
            'order' => 'ASC',
            'meta_query' => array(
                array(
                   'key' => em_append_meta_key('event'), 
                   'value' => $event_id, 
                   'compare' => '=', 
                   'type' => 'NUMERIC,' 
                )
            )
        );
        $bookings = get_posts( $args );
        if( !empty($bookings) && count($bookings) > 0 ){
            foreach ( $bookings as $booking ) {
                $order_info = get_post_meta($booking->ID, 'em_order_info', true);
                if(isset($order_info) && !empty($order_info)){
                    $order_item_data = $order_info['order_item_data'];
                    if(isset($order_item_data) && !empty($order_item_data)){
                        foreach ($order_item_data as $key => $data) {
                            if($key == $id){
                                $count += $data->quantity;
                            }
                        }
                    }
                }
            }
        }
        return $count;
    }
}