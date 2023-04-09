<?php

if (!defined('ABSPATH')) {
    exit;
}

class EventM_Booking_Service {
    private $dao;
    private static $instance = null;
    
    public static function get_instance()
    {   
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
    
    private function __construct() {
        $this->dao = new EventM_Booking_DAO();
    }
    
    public function book_seat() {
        $booking= $this->dao->get(0);
        $response = new stdClass();
                
        $event_service = EventM_Factory::get_service('EventM_Service');
        $venue_service= EventM_Factory::get_service('EventM_Venue_Service');
        
        $event_id = event_m_get_param('event_id', true);
        //$booked_seats = absint(em_get_post_meta($event_id, 'booked_seats', true));
        $booked_seats = $event_service->booked_seats($event_id);
        
        $event = $event_service->load_model_from_db($event_id);
        $venue = $venue_service->load_model_from_db($event->venue);

        $showBookNowForGuestUsers = em_show_book_now_for_guest_users();
        $setting_service = EventM_Factory::get_service('EventM_Setting_Service');
        $global_settings = $setting_service->load_model_from_db();
        // check if seat is bookable
        if ($event->status=='expired') {
            $error = new WP_Error('em_error_booking_expired',__('Booking expired','eventprime-event-calendar-management'));
            return $error;
        }
        if((isset($event->parent) && !empty($event->parent)) && (isset($event->enable_recurrence_automatic_booking) && !empty($event->enable_recurrence_automatic_booking))){
            $error = new WP_Error('em_error_booking_not_allowed',__('Booking not allowed','eventprime-event-calendar-management'));
            return $error;
        }

        $available_seats = $event_service->available_seats($event_id);
        if ($available_seats<=0) {
            $error = new WP_Error('em_error_booking_finished',__('All the seats are booked','eventprime-event-calendar-management'));
            return $error;
        }
       
        $order_info = array('discount_per' => 0, 'discount' => 0);
        $order_info['quantity'] = absint(event_m_get_param('quantity', true));
        $order_info['item_price'] = (float) event_m_get_param('single_price', true);
        if (empty($order_info['item_price'])) {
            $order_info['item_price'] = 0;
        }

        $subtotal = event_m_get_param("subtotal", true);
        $order_info['subtotal'] = (!empty($subtotal) ? $subtotal : 0);
        
        $order_info['currency'] = em_currency_symbol();
        if ($order_info['quantity'] >= $event->discount_no_tickets){
            if (!empty($event->en_ticket) && !empty($event->allow_discount)){
                $order_info['discount_per'] = event_m_get_param('discount_per', true);
                //$total_price = $order_info['quantity'] * $order_info['item_price'];
                $discount = ($order_info['subtotal'] * $order_info['discount_per']) / 100;
                $order_info['discount'] = $discount;
            }
        }
        
        $response->discount = $order_info['discount'];

        // guest booking variables
        $username = $useremail = $userphone = '';$gb_per_info = array();
        if(!is_user_logged_in() && $showBookNowForGuestUsers){
            $guest_booking_personal_info = event_m_get_param("guest_booking_personal_info", true);
            if(empty($guest_booking_personal_info)){
                $username = event_m_get_param("username", true);
                $useremail = event_m_get_param("useremail", true);
                $userphone = event_m_get_param("userphone", true);
            } else{
                $username = $guest_booking_personal_info[1]->value;
                $useremail = $guest_booking_personal_info[2]->value;
                $userphone = isset($guest_booking_personal_info[3]) ? $guest_booking_personal_info[3]->value : '';
                if(isset($global_settings->custom_guest_booking_field_data) && count($global_settings->custom_guest_booking_field_data) > 4){
                    $cgbfd = $global_settings->custom_guest_booking_field_data;
                    foreach($cgbfd as $cgkey => $cgvalue){
                        if(!empty($cgvalue)){
                            $fl = (!empty($cgvalue->label)) ? $cgvalue->label : $cgvalue->type;
                            $gb_per_info[][$fl] = $guest_booking_personal_info[$cgkey];
                        }
                    }
                }
                $order_info['guest_booking_custom_data'] = $gb_per_info;
            }
            $order_info['guest_booking'] = 1;
        }
        if(is_user_logged_in()){
            $user = wp_get_current_user();    
        } else {
            if( $showBookNowForGuestUsers ) {
                $user = get_user_by( 'email', $useremail );
                if($global_settings->auto_create_guest_account){
                    if(!$user){
                        $password = wp_generate_password();
                        $userid = wp_create_user($username, $password, $useremail);
                        wp_new_user_notification($userid);
                        $user = get_user_by('id', $userid);
                        $order_info['guest_booking'] = 1;
                        if(isset($global_settings->custom_guest_booking_field_data) && count($global_settings->custom_guest_booking_field_data) > 2){
                            $cgbfd = $global_settings->custom_guest_booking_field_data;
                            $guest_booking_personal_info = event_m_get_param("guest_booking_personal_info", true);
                            foreach($cgbfd as $cgkey => $cgvalue){
                                if(!empty($cgvalue) && $cgkey > 2){
                                    $fl = (!empty($cgvalue->label)) ? $cgvalue->label : $cgvalue->type;
                                    if(isset($guest_booking_personal_info[$cgkey])){
                                        update_user_meta($userid, 'EP_GB_'.$fl, $guest_booking_personal_info[$cgkey]->value);
                                    }
                                }
                            }
                        }
                    }
                    else{
                        $userid = $user->ID;
                        $order_info['guest_booking'] = 1;
                        if(isset($global_settings->custom_guest_booking_field_data) && count($global_settings->custom_guest_booking_field_data) > 2){
                            $cgbfd = $global_settings->custom_guest_booking_field_data;
                            $guest_booking_personal_info = event_m_get_param("guest_booking_personal_info", true);
                            foreach($cgbfd as $cgkey => $cgvalue){
                                if(!empty($cgvalue) && $cgkey > 2){
                                    $fl = (!empty($cgvalue->label)) ? $cgvalue->label : $cgvalue->type;
                                    if(isset($guest_booking_personal_info[$cgkey])){
                                        update_user_meta($userid, 'EP_GB_'.$fl, $guest_booking_personal_info[$cgkey]->value);
                                    }
                                }
                            }
                        }
                    }
                }
                else{
                    if($user){
                        $userid = $user->ID;
                        $order_info['guest_booking'] = 1;
                        if(isset($global_settings->custom_guest_booking_field_data) && count($global_settings->custom_guest_booking_field_data) > 2){
                            $cgbfd = $global_settings->custom_guest_booking_field_data;
                            $guest_booking_personal_info = event_m_get_param("guest_booking_personal_info", true);
                            foreach($cgbfd as $cgkey => $cgvalue){
                                if(!empty($cgvalue) && $cgkey > 2){
                                    $fl = (!empty($cgvalue->label)) ? $cgvalue->label : $cgvalue->type;
                                    if(isset($guest_booking_personal_info[$cgkey])){
                                        update_user_meta($userid, 'EP_GB_'.$fl, $guest_booking_personal_info[$cgkey]->value);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        /*else {
            $user = get_user_by( 'email', $useremail );
            if(!$user){
                $user = new stdClass();
                $user->ID = null;
                $user->user_email = event_m_get_param("useremail", true);
                $order_info['guest_booking'] = 1;
            }
        }*/
        $order_info['user_email'] = (!empty($useremail) ? $useremail : (!empty($user) && !empty($user->user_email) ? $user->user_email : ''));
        $order_info['user_name'] = (!empty($username) ? $username : (!empty($user) && !empty($user->user_login) ? $user->user_login : ''));
        $order_info['user_phone'] = (!empty($userphone)) ? $userphone : '';

        // add coupon code elements
        $order_info['coupon_code'] = event_m_get_param("coupon_code", true);
        $order_info['coupon_discount'] = event_m_get_param("coupon_discount", true);
        $order_info['coupon_amount'] = event_m_get_param("coupon_amount", true);
        $order_info['coupon_type'] = event_m_get_param("coupon_type", true);
        if(!empty($venue->id) && $venue->type == "seats"){
            $order_info['seat_sequences'] = event_m_get_param("seat_sequences", true);
            $order_info['seat_pos'] = event_m_get_param("seat_pos", true);
            $order_info['seat_price_data'] = event_m_get_param("seat_price_data", true);

            if (!$this->check_booking_availability($event_id, $order_info)) {
                $error = new WP_Error('seat_conflict', __('Something went wrong. Please try again.', 'eventprime-event-calendar-management'));
                return $error;
            }

            $seats = event_m_get_param("seats", true);
            $order_other_info['seats'] = $seats;
            $booking->name= __('Order','eventprime-event-calendar-management').' '. date("Y-m-d H:i:s");
            $booking->status= 'pending';
            $booking->user= $user->ID;
            $booking->date= current_time('timestamp');
            $booking->event= $event->id;
            
            // check for custom booking fields
            $is_custom_booking_field = event_m_get_param("is_custom_booking_field", true);
            $order_info['is_custom_booking_field'] = $is_custom_booking_field;
            if(!empty($is_custom_booking_field)){
                $customAttendeenames = event_m_get_param("attendee_names", true);
                foreach($customAttendeenames as $key => $atnames){
                    foreach($atnames as $atk => $atypeInd){
                        if($atk !== '$$hashKey'){
                            foreach($atypeInd as $typekey => $typeData){
                                foreach($typeData as $labelkey => $labeldata){
                                    $cbf_label = $labelkey;
                                    if(empty($labelkey)){
                                        $cbf_label = $typekey;
                                    }
                                    $booking->attendee_names[$key][$cbf_label] = sanitize_text_field($labeldata->value);
                                    if(is_null($labeldata->value)){
                                        $booking->attendee_names[$key][$typekey][$cbf_label] = __('N/A', 'eventprime-event-calendar-management');
                                    }
                                }
                            }
                        }
                    }
                }
            }
            else{
                $booking->attendee_names= event_m_get_param("attendee_names", true);
                foreach($booking->attendee_names as $key => $val) {
                    $booking->attendee_names[$key] = sanitize_text_field($val);
                    if ($booking->attendee_names[$key] == '')
                        $booking->attendee_names[$key] = __('N/A', 'eventprime-event-calendar-management');
                }
            }
            // multi price option
            /*$multi_price_option_data = event_m_get_param("multi_price_option_data", true);
            if(!empty($multi_price_option_data)){
                $booking->multi_price_id = $multi_price_option_data[0]->id;
            }*/
            
            $order_item_data = event_m_get_param("order_item_data", true);
            $order_info['order_item_data'] = (!empty($order_item_data) ? array_filter($order_item_data) : array());
            // order info
            $booking->order_info= $order_info;
            $booking= $this->dao->save($booking);
            if (!empty($booking)) {
                $event_service->update_booked_seats($event_id,$booking->order_info['quantity']+$booked_seats);
                $this->dao->set_meta($event->id,'seats',$seats);
                $response->order_id = $booking->id;
                // action for automatic recurrence tmp booking
                do_action('event_magic_automatic_recurrence_tmp_booking', $booking);
                return $response;
            } 
            else
            {
                $error = new WP_Error('seat_conflict', __('Something went wrong. Please try again.', 'eventprime-event-calendar-management'));
                return $error;
            }
        } 
        else 
        {
            if (!$this->check_booking_availability($event->id,$order_info)) {
                $error = new WP_Error('error_capacity', __("Booking can't be done as no seats are available.", 'eventprime-event-calendar-management'));
                return $error;
            }
            $booking->name= __('Order','eventprime-event-calendar-management').' '. date("Y-m-d H:i:s");
            $booking->status= 'pending';
            $booking->user= $user->ID;
            $booking->date= current_time('timestamp');
            $booking->event= $event->id;
            
            // check for custom booking fields
            $is_custom_booking_field = event_m_get_param("is_custom_booking_field", true);
            $order_info['is_custom_booking_field'] = $is_custom_booking_field;
            if(!empty($is_custom_booking_field)){
                $customAttendeenames = event_m_get_param("attendee_names", true);
                foreach($customAttendeenames as $key => $atnames){
                    foreach($atnames as $atk => $atypeInd){
                        if($atk !== '$$hashKey'){
                            foreach($atypeInd as $typekey => $typeData){
                                foreach($typeData as $labelkey => $labeldata){
                                    $cbf_label = $labelkey;
                                    if(empty($labelkey)){
                                        $cbf_label = $typekey;
                                    }
                                    $booking->attendee_names[$key][$cbf_label] = sanitize_text_field($labeldata->value);
                                    if(is_null($labeldata->value)){
                                        $booking->attendee_names[$key][$typekey][$cbf_label] = __('N/A', 'eventprime-event-calendar-management');
                                    }
                                }
                            }
                        }
                    }
                }
            }
            else{
                $booking->attendee_names= event_m_get_param("attendee_names", true);
                foreach($booking->attendee_names as $key => $val) {
                    $booking->attendee_names[$key] = sanitize_text_field($val);
                    if ($booking->attendee_names[$key] == '')
                        $booking->attendee_names[$key] = __('N/A', 'eventprime-event-calendar-management');
                }
            }
            // multi price option
            /*$multi_price_option_data = event_m_get_param("multi_price_option_data", true);
            if(!empty($multi_price_option_data)){
                $booking->multi_price_id = $multi_price_option_data[0]->id;
            }*/

            $order_item_data = event_m_get_param("order_item_data", true);
            $order_info['order_item_data'] = (!empty($order_item_data) ? array_filter($order_item_data) : array());

            // order info
            $booking->order_info= $order_info;
            $booking= $this->dao->save($booking);
            if (!empty($booking)) {
                $event_service->update_booked_seats($event_id, $order_info['quantity'] + $booked_seats);
                $response->order_id = $booking->id;
                // action for automatic recurrence tmp booking
                do_action('event_magic_automatic_recurrence_tmp_booking', $booking);
                return $response;
            }
        }
    }

    public function check_booking_availability($event_id, $order_info) {
        $event_service = EventM_Factory::get_service('EventM_Service');
        if (!empty($order_info['seat_pos'])) {
            $seats = $this->dao->get_meta($event_id,'seats');

            // Venue does not have any seats
            if (empty($seats) || empty($seats[0])) {
                return false;
            }

            if (!empty($order_info['seat_pos'])) {
                foreach ($order_info['seat_pos'] as $pos) {
                    $positions = explode('-', $pos);
                    $row = $positions[0];
                    $col = $positions[1];

                    if (isset($seats[$row][$col])) {
                        $seat = $seats[$row][$col];
                        if ($seat->type != 'general') {
                            return false;
                        }
                    }
                }
            }
        } 
        else 
        {  
            $event= $event_service->load_model_from_db($event_id);
            // Check if capcity is not given
            if (empty($event->seating_capacity))
                return true;
            $available= $event->seating_capacity-$event_service->booked_seats($event_id);
            return $available>=absint($order_info['quantity']);
        }
        return true;
    }
    
    /* 
     * Removes temporary booking for a user
     */
    public function remove_tmp_bookings_for_user($user_id) {
        $event_service = EventM_Factory::get_service('EventM_Service');
        $bookings = $this->dao->get_tmp_bookings($user_id);
        
        foreach ($bookings as $booking) {
            if (!empty($booking->order_info['seat_pos'])) {
                $this->update_seat_status($booking->event, $booking->order_info['seat_pos'], 'tmp', 'general');
                $booked_seats = $event_service->booked_seats($booking->event);
                if ($booked_seats>0){
                    $event_service->update_booked_seats($booking->event, $booked_seats-$booking->order_info['quantity']);
                }
            }
            else
            {
                // Standing event order
                $booked_seats = $event_service->booked_seats($booking->event);
                if ($booked_seats>0){
                    $event_service->update_booked_seats($booking->event, $booked_seats-$booking->order_info['quantity']);
                }
            }
            //$this->dao->delete_post($booking->id);
            wp_update_post(
                array(
                    'ID' => $booking->id,
                    'post_status' => 'pending',
                )
            );
        }
    }

    private function update_seat_status($event_id, $seat_pos, $type_from, $type_to) {
        $seats = em_get_post_meta($event_id, 'seats', true);
        if (!empty($seats) && !empty($seats[0])) {
            if (!empty($seat_pos)) {
                foreach ($seat_pos as $pos) {
                    $positions = explode('-', $pos);
                    $row = $positions[0];
                    $col = $positions[1];

                    if (isset($seats[$row][$col])) {
                        $seat = &$seats[$row][$col];
                        if ($seat->type == $type_from) {
                            $seat->type = $type_to;
                        }
                    }
                }
                em_update_post_meta($event_id, 'seats', $seats);
            }
        }
    }

    /*
     * Remove temporary bookings for all. 
     */
    public function remove_all_tmp_bookings() {
        $event_service = EventM_Factory::get_service('EventM_Service');
        $bookings = $this->dao->get_all_tmp_bookings();
        if (!empty($bookings)) {
            foreach ($bookings as $booking) {
                if (isset($booking->order_info['seat_pos'])) {
                    $this->update_seat_status($booking->event,  $booking->order_info['seat_pos'], 'tmp', 'general'); // Reverting Seat status as it was before booking
                }
                // Updating booked seats
                $booked_seats= $event_service->booked_seats($booking->event);
                if($booked_seats>0){
                    $event_service->update_booked_seats($booking->event,$booked_seats - $booking->order_info['quantity']);
                }
                //$this->dao->delete_post($booking->id);
                // if booking in trash status then do not update
                $book_post = get_post($booking->id);
                if($book_post->post_status == 'trash') continue;
                wp_update_post(
                    array(
                        'ID' => $booking->id,
                        'post_status' => 'cancelled',
                    )
                );
            }
        }
    }

    /*
     * Called after booking confirmation from payment gateway.
     */
    public function confirm_booking($booking_id,$data = array()) {
        $booking = $this->load_model_from_db($booking_id);
        if (empty($booking->id))
            return;

        $booking->booking_tmp_status = 0;
        if (!empty($booking->order_info['seat_pos'])) {
            $this->update_seat_status($booking->event, $booking->order_info['seat_pos'], 'tmp', 'sold');
        }
        $booking->order_info['payment_gateway'] = $data['payment_gateway'];
        // coupon code section
        if(isset($booking->order_info['coupon_code']) && empty($booking->order_info['coupon_code'])){
            $booking->order_info['coupon_code'] = $data['coupon_code'];
            $booking->order_info['coupon_discount'] = $data['coupon_discount'];
            $booking->order_info['coupon_amount'] = $data['coupon_amount'];
            $booking->order_info['coupon_type'] = $data['coupon_type'];
        }
        $booking->order_info = apply_filters('event_magic_add_booking_order_info', $booking->order_info, $data);
        $booking->payment_log = $data;
        $booking->status = $data['payment_status'];
        
        $booking = $this->dao->save($booking);
        if (!empty($booking)) {
            // action for automatic recurrence booking
            do_action('event_magic_automatic_recurrence_booking', $booking);
            if (strtolower($data['payment_status']) == "completed") {
                EventM_Notification_Service::booking_confirmed($booking);
            } else if (strtolower($data['payment_status']) == "refunded") {
                EventM_Notification_Service::booking_refund($booking);
            } else {
                EventM_Notification_Service::booking_pending($booking);
            }
        }
    }

    public function refund_booking($booking_id) {
        $response = new stdClass();
        $response->msg = __("Something went wrong. Refund transaction was unsuccessful. Please try from merchant interface.", 'eventprime-event-calendar-management');
        
        $booking = $this->load_model_from_db($booking_id);
        if(empty($booking->id)){
            $response->msg = __("Refund process could not be completed.", 'eventprime-event-calendar-management');
            return $response;
        }
        $booking = apply_filters('event_magic_refund_booking',$booking);
        $this->save($booking);
        if($booking->status=='refunded'){
            // action for refund automatic recurrence booking
            do_action('event_magic_automatic_recurrence_booking_refund', $booking);
            $response->msg = __("Refund process completed.", 'eventprime-event-calendar-management');
            EventM_Notification_Service::booking_refund($booking->id);
        }
        else
        {
            $response->msg = __("Refund process could not be completed.", 'eventprime-event-calendar-management');
        }
        return $response;
    }

    public function revoke_seats($booking_id, $from = 'sold', $target = 'general') {
        $event_service = EventM_Factory::get_service('EventM_Service');
        $booking= $this->dao->get($booking_id);
        if(empty($booking->id))
            return false;
        $event= $event_service->load_model_from_db($booking->event);
        if(empty($event->id))
            return false;
        $event_service->update_booked_seats($event->id, $event->booked_seats - $booking->order_info['quantity']);

        if (!empty($booking->order_info['seat_pos'])) {
            $this->update_seat_status($event->id, $booking->order_info['seat_pos'], $from, $target);
        }
    }

    public function get_seats($event_ID) {
        $args = array(
            'post_type' => EM_BOOKING_POST_TYPE,
            'meta_query' => array(
                array(
                    'key' => 'em_event_id',
                    'value' => $event_ID,
                    'compare' => '='
                )
        ));
        $my_query = get_posts($args);
        return $my_query;
    }

    public function get_bookings_by_user($user_id) {
        $bookings = $this->dao->get_bookings_by_user($user_id);
        return $bookings;
    }

    public function get_event_by_booking($booking_id) {
        $events = $this->dao->get_event_booking($booking_id);
        return $events;
    }

    public function get_final_price($order_id) {
        $after_discount_price = 0;
        $order_info = em_get_post_meta($order_id,'order_info',true);
        $payment_log = em_get_post_meta($order_id,'payment_log',true);
        if(isset($order_info['item_price']) && !empty($order_info['item_price'])){
            $after_discount_price = ($order_info['item_price'] * $order_info['quantity']) - $order_info['discount'];
            if(isset($order_info['fixed_event_price']) && !empty($order_info['fixed_event_price'])){
                $after_discount_price += $order_info['fixed_event_price'];
            }
            $after_discount_price = apply_filters('event_magic_booking_get_final_price', $after_discount_price, $order_info);
            // coupon code section
            if(isset($order_info['coupon_discount']) && !empty($order_info['coupon_discount'])){
                $after_discount_price = $after_discount_price - $order_info['coupon_discount'];
            }
        }
        $total_amount = (!empty($payment_log) && isset($payment_log['total_amount']) ? $payment_log['total_amount'] : (isset($order_info['subtotal']) ? $order_info['subtotal'] : '') );
        if( !empty( $payment_log ) && isset( $payment_log['payment_gateway'] ) && $payment_log['payment_gateway'] == 'none' && !isset( $payment_log['total_amount'] ) ) {
            $total_amount = 0;
        }
        return (!empty($total_amount) ? $total_amount : $after_discount_price);
    }

    public function get_single_price($order_id, $seat_no = '') {
        $order_info = em_get_post_meta($order_id,'order_info',true);
        $price = $order_info['item_price'];
        if(isset($order_info['order_item_data'])){
            if(isset($order_info['seat_sequences']) && !empty($order_info['seat_sequences'])){
                foreach($order_info['order_item_data'] as $order_item_data){
                    if(isset($order_item_data->seatNo)){
                        $seatNo = explode(",", $order_item_data->seatNo);
                        if(in_array($seat_no, $seatNo)){
                            $price = $order_item_data->price;
                            break;                            
                        }
                    }
                }
            } else{
                foreach($order_info['order_item_data'] as $order_item_data){
                    $price = $order_item_data->sub_total;
                    break;
                }
            }
        }
        return $price;
    }

    public function get_price_for_print($order_id, $seat_no = '') {
        return $this->get_single_price($order_id, $seat_no);
    }
    
    public function load_booking() {
        $setting_service = EventM_Factory::get_service('EventM_Setting_Service');
        $options = $setting_service->load_model_from_db();
        
        $booking_id = absint(event_m_get_param('post_id'));
        $booking = $this->load_model_from_db($booking_id);
        if(empty($booking->id)){
            // Booking not available.
        }
        $event_service= EventM_Factory::get_service('EventM_Service');
        $venue_service= EventM_Factory::get_service('EventM_Venue_Service');
        
        $event= $event_service->load_model_from_db($booking->event);
        if(!empty($event->venue)){
            $venue= $venue_service->load_model_from_db($event->venue);
            if(!empty($venue->id)){
                $booking->edit= 1;
                $booking->event_name= $event->name;
                $booking->type= $venue->type;
            }
        }
        if(empty($booking->payment_log)){
            $booking->payment_log= __('No Transaction Log Available','eventprime-event-calendar-management');
        }

        if (!empty($booking->order_info['currency'])){
            $booking->currency_symbol = $booking->order_info['currency'];
        }
        elseif($booking->payment_log['payment_gateway'] == 'paypal'){
            $booking->currency_symbol = $booking->payment_log['mc_currency'];
        }
        $user= get_user_by('id',$booking->user);
        if(!empty($user)){
            $user_info= array('display_name'=>$user->display_name,'email'=>$user->user_email);
            if(isset($user->phone) && $user->phone != '') {
                $user_info['phone'] = $user->phone;
            }
            $booking->user = $user_info;
        }
        else
        {
            $booking->user_notice= __('User information is not available','eventprime-event-calendar-management');
        }
        $booking->booked_on= date_i18n(get_option('date_format').' '.get_option('time_format'),$booking->date);
        $booking->confirm_email= !empty($options->send_booking_confirm_email) ? true : false;
        $booking->cancel_email= !empty($options->send_booking_cancellation_email) ? true : false;
        $em= event_magic_instance(); 
        if(in_array('seating',$em->extensions) && !empty($events->en_ticket)){
            $booking->ticket=true;
        }
        else{
            $booking->ticket=true;
        }
        
        $booking->ticket= !empty($event->en_ticket)  ? 1 : 0;
        
        $ticket_price = (isset($booking->order_info['subtotal']) ? $booking->order_info['subtotal'] : $booking->order_info['item_price'] * $booking->order_info['quantity']);

        $booking->ticket_price = $ticket_price;
        
        $amount_received = $ticket_price;
        if(is_numeric($booking->order_info['discount'])){
            $amount_received -= $booking->order_info['discount'];
        }
        if(is_numeric($booking->order_info['coupon_discount'])){
            $amount_received -= $booking->order_info['coupon_discount'];
        }
        
        $amount_due = $ticket_price;
        if(is_numeric($booking->order_info['discount'])){
            $amount_due -= $booking->order_info['discount'];
        }
        if(is_numeric($booking->order_info['coupon_discount'])){
            $amount_due -= $booking->order_info['coupon_discount'];
        }
        if(isset($booking->order_info['fixed_event_price']) && !empty($booking->order_info['fixed_event_price'])){
            $amount_received += $booking->order_info['fixed_event_price'];
            $amount_due += $booking->order_info['fixed_event_price'];
        }
        $amount_received = apply_filters('event_magic_view_attendee_amount_received', $amount_received, $booking);
        $amount_due = apply_filters('event_magic_view_attendee_amount_due', $amount_due, $booking);
        
        $booking->amount_received = em_price_with_position($amount_received, $booking->currency_symbol);
        $booking->amount_due = em_price_with_position($amount_due, $booking->currency_symbol);
        $booking->final_price = $amount_received;
        $booking->currency_position = $options->currency_position;
        $booking->variation_name = '';
        if(isset($booking->order_info['order_item_data']) && !empty($booking->order_info['order_item_data'])){
            $oid = $booking->order_info['order_item_data'];
            foreach ($oid as $key => $value) {
                if(isset($value->variation_name) && !empty($value->variation_name)){
                    $booking->variation_name = $value->variation_name;
                }
            }
        }
        $booking->payment_gateway = isset($booking->order_info['payment_gateway']) ? $booking->order_info['payment_gateway'] : '';
        return $booking;
    }
    
    public function load_bookings(){
        $data= new stdClass();
        $hide_old_bookings = (em_global_settings('hide_old_bookings') == 1) ? true : false;
        $show_no_of_booking = EM_PAGINATION_LIMIT;
        if(!empty(event_m_get_param('show_no_of_booking'))){
            $show_no_of_booking = event_m_get_param('show_no_of_booking');
            if($show_no_of_booking == 'All'){
                $show_no_of_booking = -1;
            }
        }
        /* Getting only pending and completed bookings */
        $args = array(
            'posts_per_page' => $show_no_of_booking,
            'offset' => ((int)event_m_get_param('paged') - 1) * $show_no_of_booking,
            'numberposts' => -1,
            'post_type' => EM_BOOKING_POST_TYPE,
            'post_status' => array('completed', 'pending'),
            'order'=>'DESC'
        );
        $event_service= EventM_Factory::get_service('EventM_Service');
        if($hide_old_bookings) {
            $params = array(
                'numberposts' => -1,
                'post_status' => 'publish',
                'post_type' => EM_EVENT_POST_TYPE,
                'fields' => 'ids'
            );
            $active_event_ids = $event_service->get_events($params);
            $active_event_ids = array_map('intval', $active_event_ids);
            $args['meta_query'] = array(
                array(
                    'key' => em_append_meta_key('event'),
                    'value' => $active_event_ids,
                    'compare' => 'IN',
                    'type' => 'NUMERIC,'
                )
            );
        }
        $filter = array();
        $filter['event'] = event_m_get_param('event');
        $filter['status'] = event_m_get_param('filter_status');
        $filter['filter_between'] = event_m_get_param('filter_between');

        $data->paged = event_m_get_param('paged');
        $data->show_no_of_booking = event_m_get_param('show_no_of_booking');
        $data->date_from = event_m_get_param('date_from');
        $data->date_to = event_m_get_param('date_to');
        $data->filter_status = event_m_get_param('filter_status');
        $args = $this->apply_filter($filter, $args,$data);
        $data->selcted_bookings = event_m_get_param('selected_bookings');
        
        $bookings = $this->dao->get_all($args);
        $event_service = EventM_Factory::get_service('EventM_Service');
        
        foreach ($bookings as $booking) {
            $user = get_user_by('id', $booking->user);
            $booking->user= !empty($user) ? array('display_name'=>$user->display_name,'email'=>$user->user_email) : array('display_name'=>$booking->order_info['user_name'], 'email'=>$booking->order_info['user_email']);
            $booking->no_tickets= $booking->order_info['quantity'];
            $booking->event_name= html_entity_decode(get_the_title(absint($booking->event)));
            if(isset($booking->order_info['parent_booking_id']) && !empty($booking->order_info['parent_booking_id'])){
                $booking->parent_event_name = $this->get_parent_booking_event(absint($booking->order_info['parent_booking_id']));
            }
            $event = $event_service->load_model_from_db( $booking->event );
            $booking->event_date = em_get_event_date( $event );
            $booking->payment_gateway = isset($booking->order_info['payment_gateway']) ? $booking->order_info['payment_gateway'] : '';
        }
        $data->posts= $bookings;
        $filter = array(
            'numberposts' => -1,
            'post_status' => 'any',
            'order' => 'DESC',
            'post_type' => EM_EVENT_POST_TYPE,
            'meta_query' => array(
                array(
                    'key' => em_append_meta_key('enable_booking'),
                    'value' => 1,
                    'compare' => '=',
                    'type' => 'NUMERIC,'
                ),
            ),
        );
        
        if ($hide_old_bookings) {
            $filter['post_status'] = 'publish';
        }
        
        $events = $event_service->get_events($filter);
        $data->filter_between = event_m_get_param('filter_between');

        $tmp_status = EventM_Constants::$status;
        $data->status = array();
        $data->status[] = array("key" => "", "label" => __('All', 'eventprime-event-calendar-management'));

        foreach ($tmp_status as $key => $label) {
            $tmp = new stdClass();
            $tmp->key = $key;
            $tmp->label = $label;
            $data->status[] = $tmp;
        }

        $data->events = array();
        $data->events[] = array('id' => 0, 'title' => __('All Events', 'eventprime-event-calendar-management'));
        if (!empty($events)) {
            foreach ($events as $event) {
                $tmp = new stdClass();
                $tmp->id = $event->ID;
                if ($event->post_parent > 0) {
                    $date = em_showDateTime($event_service->get_meta($event->ID,'start_date'),false,"m/d/Y");
                    $tmp->title = $event->post_title . ' - ' . $date;
                } else {
                    $tmp->title = $event->post_title;
                }
                $data->events[] = $tmp;
            }
        }

        /**
         * Return post count 
         */
        // Calculating number of bookings
        $args['offset'] = 0;
        $args['posts_per_page'] = 99999;

        $tmp = get_posts($args);
        $data->total_bookings = range(1, count($tmp));
        $data->pagination_limit = $show_no_of_booking;
        $data->event = (int) event_m_get_param('event');
        return $data;
    }
    
    public function load_model_from_db($id)
    {   
        return $this->dao->get($id);
    }
    
    protected function apply_filter($filter, $args,$data) {
        $date_query = array();

        switch ($filter['filter_between']) {
            case 'today': $today = getdate();
                $date_query = array(array(
                        'year' => $today['year'],
                        'month' => $today['mon'],
                        'day' => $today['mday']
                ));
                break;
            case 'week': $date_query = array(
                    array(
                        'year' => date('Y'),
                        'week' => date( 'W' )
                    )
                );
                break;
            case 'month':
                $today = getdate();
                $date_query = array(
                    array(
                        'year' => $today['year'],
                        'month' => $today['mon'],
                    ),
                );
                break;
            case 'year': $date_query = array(
                    array(
                        'year' => date('Y'),
                    ),
                );
                break;
            case 'range': $date_query = array(
                    array(
                        'after' => $data->date_from,
                        'before' => date('y-m-d', strtotime('+1 day', strtotime($data->date_to))),
                        'inclusive' => true
                ));
                break;
            default: $date_query = array();
        }



        $args['date_query'] = $date_query;

        // Check for event type
        if (!empty($filter['event'])) {
            $args['meta_query'] = array(
                array(
                    'key' => em_append_meta_key('event'), // Check the start date field
                    'value' => $filter['event'], // Set today's date (note the similar format)
                    'compare' => '=', // Return the ones less than today's date
                    'type' => 'NUMERIC,'
                )
            );
        }

        if (!empty($filter['status'])) {
            $args['post_status'] = $filter['status'];
        }
        return $args;
    }
    
    public function save($model){
        return $this->dao->save($model);
    }  
    
    public function get_all($args=array()){
        return $this->dao->get_all($args);
    }
    
    public function get_by_event($event_id,$args= array()){
        $defaults= array('order'=>'DESC','numberposts'=>5);
        $args = wp_parse_args($args,$defaults );
        $args['meta_query'] = array(
                array(
                    'key' => em_append_meta_key('event'),
                    'value' => $event_id,
                    'compare' => '=',
                    'type' => 'NUMERIC,'
                )
        );
        $bookings= $this->get_all($args);
        return $bookings;
    }
    
    public function export_data(){
        $data= new stdClass();
        $args = array(
            'numberposts' => -1,
            'post_type' => EM_BOOKING_POST_TYPE,
            'post_status' => 'any',
        );
        $event_service= EventM_Factory::get_service('EventM_Service');
        $filter = array();
        $filter['event'] = event_m_get_param('event');
        $filter['status'] = event_m_get_param('filter_status');
        $filter['filter_between'] = event_m_get_param('filter_between');

        $data->date_from = event_m_get_param('date_from');
        $data->date_to = event_m_get_param('date_to');
        $data->filter_status = event_m_get_param('filter_status');
        $args = $this->apply_filter($filter, $args,$data);
        $data->selcted_bookings = event_m_get_param('selected_bookings');
        $bookings = $this->dao->get_all($args);
        return $bookings;
    }

    public function get_parent_booking_event($parent_booking_id){
        $booking = $this->load_model_from_db($parent_booking_id);
        $parent_event = get_the_title(absint($booking->event));
        return $parent_event;
    }

    public function cancel_bookings(){
        $this->check_permission();
        $ids = event_m_get_param('ids');
        $delete_booking = event_m_get_param('delete_booking');
        if(!empty($ids)){
            if(!is_array($ids)){
                $ids = explode(',', $ids);
            }
            foreach ($ids as $id) {
                $post = get_post($id);
                if($post->post_type == 'em_booking'){
                    $booking = $this->load_model_from_db($id);
                    $this->revoke_seats($id);
                    $booking->status = 'cancelled';
                    $this->save($booking);
                    // delete booking
                    if($delete_booking == 1){
                        $post = array(
                            'ID' => $id,
                            'post_status' => 'trash',
                        );
                        wp_update_post($post);
                    }
                }
            }
        }
        wp_send_json_success(array('success' => 1));
    }

    private function check_permission(){
        if(!em_is_user_admin()){
            $error_msg= __('User not allowed','eventprime-event-calendar-management');
            wp_send_json_error(array('errors'=>array($error_msg)));
        }
    }

    public function load_booking_by_user_event_id( $user_id, $event_id ) {
        if ( empty( $user_id ) || empty( $event_id ) ){
            return;
        }
        $bookings = $this->dao->get_bookings_by_user_id_event_id( $user_id, $event_id );
        return $bookings;
    }

    public function get_multi_price_booking_count($id, $event_id) {
        $booking = $this->dao->get_multi_price_booking_count($id, $event_id);
        return $booking;
    }
    
}