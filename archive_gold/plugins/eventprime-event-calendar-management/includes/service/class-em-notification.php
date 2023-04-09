<?php

if (!defined( 'ABSPATH')){
    exit;
}

class EventM_Notification_Service {
    
    public static function booking_confirmed($booking){
        $setting_service = EventM_Factory::get_service('EventM_Setting_Service');
        $gs= $setting_service->load_model_from_db();
        $payment_note = isset($gs->offline_payment_note) ? $gs->offline_payment_note : '';
        if(empty($gs->send_booking_confirm_email))
           return;
        $booking_service = EventM_Factory::get_service('EventM_Booking_Service');
        if(!$booking instanceof EventM_Booking_Model){
            $booking= $booking_service->load_model_from_db($booking);
        }
        if(empty($booking->id))
            return;
        $event_service = EventM_Factory::get_service('EventM_Service');
        $event = $event_service->load_model_from_db($booking->event);
        $venue_service = EventM_Factory::get_service('EventM_Venue_Service');
        $venue = $venue_service->load_model_from_db($event->venue);
        if(empty($venue->id)){
            $venue= false;
        }
        if(empty($event->id)) {
            return;
        }
        // id with date and time
        $bookingIdDate = $booking->id . ' - ' .date_i18n(get_option('date_format').' '.get_option('time_format'), $booking->date);
        /* self::configure_mail(); */
        $user= get_user_by('ID',$booking->user);
        $booking_user_email = $user->user_email;
        $booking_user_name = ( ! empty( $user->display_name) ) ? $user->display_name : $user->user_nicename;
        $booking_user_phone = get_user_meta($user->ID, 'phone', true);
        if(empty($user) && em_show_book_now_for_guest_users()){
            $booking_user_email = $booking->order_info['user_email'];
            $booking_user_name = $booking->order_info['user_name'];
            $booking_user_phone = (isset($booking->order_info['user_phone']) && !empty($booking->order_info['user_phone']) ? $booking->order_info['user_phone'] : '');
        }
        // sub total
        $order_sub_total = isset($booking->order_info['subtotal']) ? $booking->order_info['subtotal'] : 0;
        // total payment
        $total_payment = $order_sub_total; 
        // fixed event price
        $fixed_event_price = 0;
        if(isset($booking->order_info['fixed_event_price']) && !empty($booking->order_info['fixed_event_price'])){
            $fixed_event_price = $booking->order_info['fixed_event_price'];
            $total_payment += $fixed_event_price;
        }
        // discounts
        $total_discount = 0;
        if(isset($booking->order_info['discount']) && !empty($booking->order_info['discount'])){
            $total_discount += $booking->order_info['discount'];
        }
        if(isset($booking->order_info['coupon_discount']) && !empty($booking->order_info['coupon_discount'])){
            $total_discount += $booking->order_info['coupon_discount'];
        }
        if(isset($booking->order_info['ebd_discount_amount']) && !empty($booking->order_info['ebd_discount_amount'])){
            $total_discount += $booking->order_info['ebd_discount_amount'];
        }
        // deduct total discount from total payment
        if(!empty($total_discount)){
            $total_payment -= $total_discount;
        }
        // final order total
        $order_total = (!empty($booking->payment_log) && isset($booking->payment_log['total_amount']) ? $booking->payment_log['total_amount'] : $total_payment );
        $booking_item_price = em_price_with_position($booking->order_info['item_price']);
        if(isset($booking->multi_price_id) && !empty($booking->multi_price_id) && isset($booking->payment_log['multi_price_option_data']) && !empty($booking->payment_log['multi_price_option_data']->name)){
            $booking_item_price .= ' (' . $booking->payment_log['multi_price_option_data']->name . ')';
        }
        $gcal_link = self::gcal_link($event, $venue);
        $iCal_link = self::iCal_link($event, $venue);
        // replace email variables
        if($event->enable_custom_booking_confirmation_email == 1){
            $event_url = get_permalink($event->id);
            $event_type_name = self::get_event_type_name($event);
            $organizer_data = self::get_event_organizer_data($event);
            $event_custom_link = $event->custom_link;
            $performer_data = self::get_event_performer_data($event);
            $attendee_name_html = self::get_attendee_name_variable_data($booking);
            $subject = __($event->custom_booking_confirmation_email_subject, 'eventprime-event-calendar-management');
            $mail_body =  $event->custom_booking_confirmation_email_body;
            $seat_no = isset($booking->order_info['seat_sequences']) ? implode(',', $booking->order_info['seat_sequences']) : "Standing Event";
            $mail_body = str_replace("{{seat_no}}", $seat_no, $mail_body);
            $mail_body = str_replace("{{attendee_names}}", $attendee_name_html, $mail_body);
            $mail_body = str_replace("{{booking_id}}", $bookingIdDate, $mail_body);
            $mail_body = str_replace("{{event_name}}", $event->name, $mail_body);
            $mail_body = str_replace("{{venue_name}}", empty($venue->id) ? '' : $venue->name, $mail_body);
            $mail_body = str_replace("{{venue_address}}",empty($venue->id) ? '' : $venue->address, $mail_body);
            $mail_body = str_replace("{{subtotal}}", em_price_with_position($order_sub_total), $mail_body);
            $mail_body = str_replace("{{quantity}}", $booking->order_info['quantity'], $mail_body);
            $mail_body = str_replace("{{price}}", $booking_item_price, $mail_body);
            $mail_body = str_replace("{{discount}}", em_price_with_position($total_discount), $mail_body);
            $mail_body = str_replace("{{fixed_fees}}", em_price_with_position($fixed_event_price), $mail_body);
            $mail_body = str_replace("{{event_url}}", $event_url, $mail_body);
            $mail_body = str_replace("{{event_type_name}}", $event_type_name, $mail_body);
            $mail_body = str_replace("{{gcal_link}}", $gcal_link, $mail_body);
            $mail_body = str_replace("{{iCal_link}}", $iCal_link, $mail_body);
            $mail_body = str_replace("{{organizer_name}}", $organizer_data['name'], $mail_body);
            $mail_body = str_replace("{{organizer_phone}}", $organizer_data['phone'], $mail_body);
            $mail_body = str_replace("{{organizer_email}}", $organizer_data['email'], $mail_body);
            $mail_body = str_replace("{{organizer_website}}", $organizer_data['website'], $mail_body);
            $mail_body = str_replace("{{performer_name}}", $performer_data['name'], $mail_body);
            $mail_body = str_replace("{{performer_role}}", $performer_data['role'], $mail_body);
            $mail_body = str_replace("{{event_custom_link}}", $event_custom_link, $mail_body);
        }
        else{
            $mail_body =  $gs->booking_confirmed_email;
            $mail_body = str_replace("#ID",$bookingIdDate, $mail_body);
            $mail_body = str_replace("Event Name", $event->name, $mail_body);
            $mail_body = str_replace("Venue Name",empty($venue->id) ? '' : $venue->name, $mail_body);
            $mail_body = str_replace("Event Venue",empty($venue->id) ? '' : $venue->address, $mail_body);
            /*$mail_body= isset($booking->order_info['seat_sequences']) ? str_replace("(Seat No.)",implode(',',$booking->order_info['seat_sequences']), $mail_body) : str_replace("(Seat No.)", "Standing Event", $mail_body);
            $mail_body = str_replace("$(Price)", $booking_item_price, $mail_body);
            $mail_body = str_replace("(Quantity)", $booking->order_info['quantity'], $mail_body);
            $mail_body = str_replace("$(Subtotal)", em_price_with_position($sub_total), $mail_body);*/
            // order item data
            $order_item_data = $booking->order_info['order_item_data'];
            $order_item_html = '';
            $order_item_style = "text-align:left;vertical-align:middle;border:1px solid #eee;font-family:'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif;word-wrap:break-word;color:#737373;padding:12px";
            foreach ($order_item_data as $okey => $ovalue) {
                $order_item_html .= '<tr>';
                $seat_price_var = $ovalue->seatNo;
                if(!empty($ovalue->variation_name)){
                    $seat_price_var .= ' ('.$ovalue->variation_name.')';
                }
                $order_item_html .= '<td style="'.$order_item_style.'"><span>'.$seat_price_var.'</span></td>';
                $order_item_html .= '<td style="'.$order_item_style.'"><span>'.em_price_with_position($ovalue->price, $booking->order_info['currency']).'</span></td>';
                $order_item_html .= '<td style="'.$order_item_style.'"><span>'.$ovalue->quantity.'</span></td>';
                $order_item_html .= '<td style="'.$order_item_style.'"><span>'.em_price_with_position($ovalue->sub_total, $booking->order_info['currency']).'</span></td>';
                $order_item_html .= '</tr>';
            }
            $mail_body = str_replace("<tr><td>(order_item_data)</td></tr>", $order_item_html, $mail_body);
            $mail_body = str_replace("$(Discount)", em_price_with_position($total_discount, $booking->order_info['currency']), $mail_body);
            $mail_body = str_replace("$(Fixed Event Fee)", em_price_with_position($fixed_event_price, $booking->order_info['currency']), $mail_body);
            $mail_body = str_replace("$(Order Total)", em_price_with_position($order_total, $booking->order_info['currency']), $mail_body);
            $payment_gateway = isset($booking->order_info['payment_gateway']) ? ucfirst($booking->order_info['payment_gateway']) : 'N/A';
            $mail_body = str_replace("$(Payment Gateway)", $payment_gateway, $mail_body);
            $mail_body = str_replace("$(Booking Status)", ucfirst($booking->status), $mail_body);
            $payment_note = ($payment_gateway == 'Offline') ? $payment_note : 'N/A';
            $mail_body = str_replace("$(Payment Note)", $payment_note, $mail_body);
            $mail_body = str_replace("(User Email)", $user->email, $mail_body);
            $mail_body = str_replace("{{gcal_link}}", $gcal_link, $mail_body);
            $mail_body = str_replace("{{iCal_link}}", $iCal_link, $mail_body);
            $subject = __('Booking Confirmation','eventprime-event-calendar-management');
        }
        $lastFoot = explode( '</tfoot>', $mail_body );
        $lastFootUpdate = $lastFoot[0];
        // add attendees name in email
        if( empty( $event->enable_custom_booking_confirmation_email ) ) {
            $attendee_name_html = self::get_booking_attendee_name_html($booking);
            $lastFootUpdate .= $attendee_name_html;
        }
        $lastFootUpdate = apply_filters( 'event_magic_booking_confirmed_footer_contnent', $lastFootUpdate, $event );
        $mail_body = $lastFootUpdate . '</tfoot>' . $lastFoot[1];
        // attachments
        $attachments = array();
        $em = event_magic_instance();
        if( in_array( 'offline_payments', $em->extensions ) && $booking->order_info['payment_gateway'] == "offline" && $gs->send_ticket_on_payment_received == 1 ){
            $attachments = array();
        }else{
            $attachments = apply_filters( 'event_magic_booking_confirmed_notification_attachments', $attachments, $booking );
        }
        /* send Mail to User */
        $to = $booking_user_email;
        $from = get_bloginfo('name') . '<' . get_bloginfo('admin_email') . '>';
        /* $headers = 'From: ' . $from . "\r\n"; */
        $headers = array('Content-Type: text/html; charset=UTF-8');
        if(empty($gs->disable_frontend_email)){
            wp_mail( $to, $subject, $mail_body, $headers, $attachments);
        }
        /* send Mail to Admin */   
        $mail_body = $gs->admin_booking_confirmed_email;
        $to = get_option('admin_email'); 
        $subject = __('Booking Confirmation','eventprime-event-calendar-management');
        $mail_body = str_replace("(user_email)", $booking_user_email, $mail_body);
        $mail_body = str_replace("(event_name)", $event->name, $mail_body);
        $mail_body = str_replace("(event_date)", em_get_event_date( $event ), $mail_body);
        $mail_body = str_replace("(booking_id)", $booking->id, $mail_body);
        $booking_url = admin_url( "admin.php?page=em_booking_add&post_id=".$booking->id );
        $view_order_url = '<a href="'.$booking_url.'" target="_blank">' . esc_html__('View Order', 'eventprime-event-calendar-management') . '</a>';
        $mail_body = str_replace("(view_order)", $view_order_url, $mail_body);
        $mail_body = str_replace("(booking_date)", em_showDateTime(em_get_post_meta($event->id,'start_date',true),true), $mail_body);
        $mail_body = str_replace("(subtotal)", em_price_with_position($order_sub_total), $mail_body);
        $mail_body = str_replace("(discount)", em_price_with_position($total_discount), $mail_body);
        $mail_body = str_replace("(order_total)", em_price_with_position($order_total), $mail_body);
        $payment_gateway = isset($booking->order_info['payment_gateway']) ? ucfirst($booking->order_info['payment_gateway']) : 'N/A';
        $mail_body = str_replace("(payment_method)", $payment_gateway, $mail_body);
        $mail_body = str_replace("(user_name)", $booking_user_name, $mail_body);
        $mail_body = str_replace("(user_phone)", $booking_user_phone, $mail_body);
        $headers = array('Content-Type: text/html; charset=UTF-8');
        if( empty( $gs->disable_admin_email ) && $gs->send_admin_booking_confirm_email == 1 ){
            wp_mail( $to, $subject, $mail_body, $headers);
        }
    }
    
    public static function booking_pending($booking){
        $setting_service = EventM_Factory::get_service('EventM_Setting_Service');
        $gs = $setting_service->load_model_from_db();
        if(empty($gs->send_booking_pending_email))
           return;
        $booking_service = EventM_Factory::get_service('EventM_Booking_Service');
        if(!$booking instanceof EventM_Booking_Model){
            $booking= $booking_service->load_model_from_db($booking);
        }
        if(empty($booking->id))
            return;
        $event_service = EventM_Factory::get_service('EventM_Service');
        $event= $event_service->load_model_from_db($booking->event);
        $venue_service= EventM_Factory::get_service('EventM_Venue_Service');
        $venue= $venue_service->load_model_from_db($event->venue);
        if(empty($venue->id)){
            $venue= false;
        }
        /* self::configure_mail(); */
        $user= get_user_by('ID', $booking->user);
        $booking_user_email = $user->user_email;
        if(empty($user)){
            if(em_show_book_now_for_guest_users()){
                $booking_user_email = $booking->order_info['user_email'];
            }
            else{
                return false;
            }
        }
        $mail_body =  $gs->booking_pending_email;
        $mail_body= isset($booking->order_info['seat_sequences']) ? str_replace("(Seat No.)",implode(',',$booking->order_info['seat_sequences']), $mail_body) : str_replace("(Seat No.)", "Standing Event", $mail_body);
        $mail_body = str_replace("ID",$booking->id, $mail_body);
        $mail_body = str_replace("Event Name", $event->name, $mail_body);
        $mail_body = str_replace("Venue Name",empty($venue->id) ? '' : $venue->name, $mail_body);
        $mail_body = str_replace("Event Venue",empty($venue->id) ? '' : $venue->address, $mail_body);
        //$mail_body = str_replace("$(Subtotal)", ($booking->order_info['quantity']*$booking->order_info['item_price'])-$booking->order_info['discount']-$booking->order_info['coupon_discount'], $mail_body);
        $sub_total = $booking->order_info['quantity'] * $booking->order_info['item_price'];
        $fixed_event_price = 0;
        if(isset($booking->order_info['fixed_event_price']) && !empty($booking->order_info['fixed_event_price'])){
            $fixed_event_price = $booking->order_info['fixed_event_price'];
            $sub_total += $fixed_event_price;
        }
        $total_discount = 0;
        if(isset($booking->order_info['discount']) && !empty($booking->order_info['discount'])){
            $sub_total -= $booking->order_info['discount'];
            $total_discount += $booking->order_info['discount'];
        }
        if(isset($booking->order_info['coupon_discount']) && !empty($booking->order_info['coupon_discount'])){
            $sub_total -= $booking->order_info['coupon_discount'];
            $total_discount += $booking->order_info['coupon_discount'];
        }
        if(isset($booking->order_info['ebd_discount_amount']) && !empty($booking->order_info['ebd_discount_amount'])){
            $sub_total -= $booking->order_info['ebd_discount_amount'];
            $total_discount += $booking->order_info['ebd_discount_amount'];
        }
        $booking_item_price = em_price_with_position($booking->order_info['item_price']);
        if(isset($booking->multi_price_id) && !empty($booking->multi_price_id) && isset($booking->payment_log['multi_price_option_data']) && !empty($booking->payment_log['multi_price_option_data']->name)){
            $booking_item_price .= ' (' . $booking->payment_log['multi_price_option_data']->name . ')';
        }
        $mail_body = str_replace("$(Subtotal)", em_price_with_position($sub_total), $mail_body);
        $mail_body = str_replace("(Quantity)", $booking->order_info['quantity'], $mail_body);
        $mail_body = str_replace("$(Price)", $booking_item_price, $mail_body);
        $mail_body = str_replace("$(Discount)", em_price_with_position($total_discount), $mail_body);
        $mail_body = str_replace("$(Fixed Event Fee)", em_price_with_position($fixed_event_price), $mail_body);
        $lastFoot = explode( '</tfoot>', $mail_body );
        $lastFootUpdate = $lastFoot[0];
        // add attendees name in email
        if( empty( $event->enable_custom_booking_confirmation_email ) ) {
            if(!isset($booking->order_info['is_custom_booking_field']) || empty($booking->order_info['is_custom_booking_field'])){
                $attendee_names = isset($booking->attendee_names) ? implode(',', $booking->attendee_names) : "";
                $attendee_name_html = '<tr><th colspan="2" style="text-align:left;border-top-width:4px;color:#737373;border:1px solid #e4e4e4;padding:12px">'.esc_html__( 'Attendees', 'eventprime-event-calendar-management' ).' </th>';
                    $attendee_name_html .= '<td style="text-align:left;border-top-width:4px;color:#737373;border:1px solid #e4e4e4;padding:12px"><span>'.esc_attr($attendee_names).'</span></td>';
                $attendee_name_html .= '</tr>';
                $lastFootUpdate .= $attendee_name_html;
            }
            else{
                $attendee_names = $booking->attendee_names;
                $attendee_name_html = '';$i = 1;
                foreach($booking->attendee_names as $attendees){
                    $attData = array();
                    $attendee_name_html .= '<tr><th colspan="2" style="text-align:left;border-top-width:4px;color:#737373;border:1px solid #e4e4e4;padding:12px">'.esc_html__( 'Attendees '.$i, 'eventprime-event-calendar-management' ).' </th>';
                        $attendee_name_html .= '<td style="text-align:left;border-top-width:4px;color:#737373;border:1px solid #e4e4e4;padding:12px">';
                            foreach($attendees as $label => $value){
                               $attendee_name_html .= '<span>'. $label .' : '. $value .' </span><br/>';
                            }
                        $attendee_name_html .= '</td>';
                    $attendee_name_html .= '</tr>';
                    $i++;
                }
                $lastFootUpdate .= $attendee_name_html;
            }
        }
        $lastFootUpdate = apply_filters( 'event_magic_booking_confirmed_footer_contnent', $lastFootUpdate, $event );
        $mail_body = $lastFootUpdate . '</tfoot>' . $lastFoot[1];
        /* send Mail to User */
        $to = $booking_user_email;
        $subject = __('Booking Pending','eventprime-event-calendar-management');
        $headers = array('Content-Type: text/html; charset=UTF-8');
        if(empty($gs->disable_frontend_email)){
            wp_mail( $to, $subject, $mail_body,$headers);
        }
        // Admin email
        $to = get_option('admin_email');
        $subject = __('Booking Pending','eventprime-event-calendar-management');        
        $body = sprintf(__('User %s has Booking Pending with Booking ID #%d.','eventprime-event-calendar-management'),$booking_user_email,$booking->id);
        $headers = array('Content-Type: text/html; charset=UTF-8');
        if(empty($gs->disable_admin_email)){
            wp_mail( $to, $subject, $body,$headers);
        }
    }

    
    public static function booking_cancel($booking)
    {   
        $setting_service = EventM_Factory::get_service('EventM_Setting_Service');
        $gs= $setting_service->load_model_from_db();
        if(empty($gs->send_booking_cancellation_email))
           return; 
        $booking_service = EventM_Factory::get_service('EventM_Booking_Service');
        if(!$booking instanceof EventM_Booking_Model){
            $booking= $booking_service->load_model_from_db($booking);
        }
        if(empty($booking->id))
            return;
       
        $event_service = EventM_Factory::get_service('EventM_Service');
        $event= $event_service->load_model_from_db($booking->event);
        
        /* self::configure_mail(); */
        $user= get_user_by('ID', $booking->user);
        $booking_user_email = $user->user_email;
        if(empty($user)){
            if(em_show_book_now_for_guest_users()){
                $booking_user_email = $booking->order_info['user_email'];
            }
            else{
                return false;
            }
        }
            
        $mail_body =  $gs->booking_cancelation_email;
        $mail_body= isset($booking->order_info['seat_sequences']) ? str_replace("(Seat No.)",implode(',',$booking->order_info['seat_sequences']), $mail_body) : str_replace("(Seat No.)", "Standing Event", $mail_body);
        $mail_body = str_replace("#ID",$booking->id, $mail_body);
        $mail_body = str_replace("Event Name", $event->name, $mail_body);
        $mail_body = str_replace("Event Venue",empty($venue->id) ? '' : $venue->address, $mail_body);
        //$mail_body = str_replace("$(Subtotal)", ($booking->order_info['quantity']*$booking->order_info['item_price'])-$booking->order_info['discount']-$booking->order_info['coupon_discount'], $mail_body);
        $sub_total = $booking->order_info['quantity'] * $booking->order_info['item_price'];
        $fixed_event_price = 0;
        if(isset($booking->order_info['fixed_event_price']) && !empty($booking->order_info['fixed_event_price'])){
            $fixed_event_price = $booking->order_info['fixed_event_price'];
            $sub_total += $fixed_event_price;
        }
        $total_discount = 0;
        if(isset($booking->order_info['discount']) && !empty($booking->order_info['discount'])){
            $sub_total -= $booking->order_info['discount'];
            $total_discount += $booking->order_info['discount'];
        }
        if(isset($booking->order_info['coupon_discount']) && !empty($booking->order_info['coupon_discount'])){
            $sub_total -= $booking->order_info['coupon_discount'];
            $total_discount += $booking->order_info['coupon_discount'];
        }
        if(isset($booking->order_info['ebd_discount_amount']) && !empty($booking->order_info['ebd_discount_amount'])){
            $sub_total -= $booking->order_info['ebd_discount_amount'];
            $total_discount += $booking->order_info['ebd_discount_amount'];
        }
        $booking_item_price = em_price_with_position($booking->order_info['item_price']);
        if(isset($booking->multi_price_id) && !empty($booking->multi_price_id) && isset($booking->payment_log['multi_price_option_data']) && !empty($booking->payment_log['multi_price_option_data']->name)){
            $booking_item_price .= ' (' . $booking->payment_log['multi_price_option_data']->name . ')';
        }
        $mail_body = str_replace("$(Subtotal)", em_price_with_position($sub_total), $mail_body);
        $mail_body = str_replace("(Quantity)", $booking->order_info['quantity'], $mail_body);
        $mail_body = str_replace("$(Price)", $booking_item_price, $mail_body);
        $mail_body = str_replace("$(Discount)", em_price_with_position($total_discount), $mail_body);
        $mail_body = str_replace("$(Fixed Event Fee)", em_price_with_position($fixed_event_price), $mail_body);

        $lastFoot = explode( '</tfoot>', $mail_body );
        $lastFootUpdate = $lastFoot[0];
        // add attendees name in email
        if( empty( $event->enable_custom_booking_confirmation_email ) ) {
            if(!isset($booking->order_info['is_custom_booking_field']) || empty($booking->order_info['is_custom_booking_field'])){
                $attendee_names = isset($booking->attendee_names) ? implode(',', $booking->attendee_names) : "";
                $attendee_name_html = '<tr><th colspan="2" style="text-align:left;border-top-width:4px;color:#737373;border:1px solid #e4e4e4;padding:12px">'.esc_html__( 'Attendees', 'eventprime-event-calendar-management' ).' </th>';
                    $attendee_name_html .= '<td style="text-align:left;border-top-width:4px;color:#737373;border:1px solid #e4e4e4;padding:12px"><span>'.esc_attr($attendee_names).'</span></td>';
                $attendee_name_html .= '</tr>';
                $lastFootUpdate .= $attendee_name_html;
            }
            else{
                $attendee_names = $booking->attendee_names;
                $attendee_name_html = '';$i = 1;
                foreach($booking->attendee_names as $attendees){
                    $attData = array();
                    $attendee_name_html .= '<tr><th colspan="2" style="text-align:left;border-top-width:4px;color:#737373;border:1px solid #e4e4e4;padding:12px">'.esc_html__( 'Attendees '.$i, 'eventprime-event-calendar-management' ).' </th>';
                        $attendee_name_html .= '<td style="text-align:left;border-top-width:4px;color:#737373;border:1px solid #e4e4e4;padding:12px">';
                            foreach($attendees as $label => $value){
                               $attendee_name_html .= '<span>'. $label .' : '. $value .' </span><br/>';
                            }
                        $attendee_name_html .= '</td>';
                    $attendee_name_html .= '</tr>';
                    $i++;
                }
                $lastFootUpdate .= $attendee_name_html;
            }
        }
        $lastFootUpdate = apply_filters( 'event_magic_booking_confirmed_footer_contnent', $lastFootUpdate, $event );
        $mail_body = $lastFootUpdate . '</tfoot>' . $lastFoot[1];

        /* send Mail to User */
        $to = $booking_user_email;
        $subject = __('Booking Cancellation','eventprime-event-calendar-management');
        $headers = array('Content-Type: text/html; charset=UTF-8');
        if(empty($gs->disable_frontend_email)){
            wp_mail( $to, $subject, $mail_body,$headers); 
        }
        
        // Admin email
        $mail_body = file_get_contents(EM_BASE_URL.'includes/mail/admin_cancellation.html');  
        $mail_body = str_replace("Event Name", $event->name, $mail_body);
        
        $admin_email = get_option('admin_email'); 
        $mail_body = str_replace("#ID",$booking->id, $mail_body);
        $mail_body = str_replace("(User Email)",$booking_user_email, $mail_body);
        $to = $admin_email;
        $subject = __('Booking Cancellation','eventprime-event-calendar-management');
        $headers = array('Content-Type: text/html; charset=UTF-8');
        if(empty($gs->disable_admin_email)){
            wp_mail( $to, $subject, $mail_body,$headers); 
        }
    }
    
    
    public static function reset_password_mail($booking,$new_user_password)
    {   
        $setting_service = EventM_Factory::get_service('EventM_Setting_Service');
        $gs= $setting_service->load_model_from_db();
        $booking_service = EventM_Factory::get_service('EventM_Booking_Service');
        if(!$booking instanceof EventM_Booking_Model){
            $booking= $booking_service->load_model_from_db($booking);
        }
                
        if(empty($booking->id))
            return;
        
        /* self::configure_mail(); */
        $user= get_user_by('ID', $booking->user);
        if(empty($user))
            return false;

        $mail_body= $gs->reset_password_mail;
        $mail_body = str_replace("@username",$user->user_email,$mail_body);
        $mail_body = str_replace("@password",$new_user_password,$mail_body);   
         
        
        $to = $user->user_email;
        $subject = __('New Password','eventprime-event-calendar-management');
        $headers = array('Content-Type: text/html; charset=UTF-8');
        $body = $mail_body;
        wp_mail( $to, $subject, $body, $headers );
        
        $admin_email = get_option('admin_email'); 
        $to = $admin_email;
        $subject = __('Reset User Password','eventprime-event-calendar-management');        
        $body = sprintf(__('Password of user %s is Reset.','eventprime-event-calendar-management'),$user->user_email);
        wp_mail( $to, $subject, $body, $headers);
    }
    
    public static function booking_refund($booking)
    {
        $setting_service = EventM_Factory::get_service('EventM_Setting_Service');
        $gs= $setting_service->load_model_from_db();
        if(empty($gs->send_booking_refund_email))
           return;
        /* self::configure_mail(); */
        $booking_service = EventM_Factory::get_service('EventM_Booking_Service');
        if(!$booking instanceof EventM_Booking_Model){
            $booking= $booking_service->load_model_from_db($booking);
        }
        if(empty($booking->id))
            return false;
        $user= get_user_by('ID', $booking->user);
        if(empty($user))
            return false;

        $event_service = EventM_Factory::get_service('EventM_Service');
        $event= $event_service->load_model_from_db($booking->event);
        
        $venue_service= EventM_Factory::get_service('EventM_Venue_Service');
        $venue= $venue_service->load_model_from_db($event->venue);
        if(empty($venue->id)){
            $venue= false;
        }
        
        
        $mail_body =  $gs->booking_refund_email; 
        $mail_body= isset($booking->order_info['seat_sequences']) ? str_replace("(Seat No.)",implode(',',$booking->order_info['seat_sequences']), $mail_body) : str_replace("(Seat No.)", "Standing Event", $mail_body);
        $mail_body = str_replace("#ID",$booking->id, $mail_body);
        $mail_body = str_replace("Event Name", $event->name, $mail_body);
        $mail_body = str_replace("Event Venue",empty($venue->id) ? '' : $venue->address, $mail_body);
        $mail_body = str_replace("Venue Name",empty($venue->id) ? '' : $venue->name, $mail_body);
        //$mail_body = str_replace("$(Subtotal)",($booking->order_info['quantity']*$booking->order_info['item_price'])-$booking->order_info['discount']-$booking->order_info['coupon_discount'], $mail_body);
        $sub_total = $booking->order_info['quantity'] * $booking->order_info['item_price'];
        $fixed_event_price = 0;
        if(isset($booking->order_info['fixed_event_price']) && !empty($booking->order_info['fixed_event_price'])){
            $fixed_event_price = $booking->order_info['fixed_event_price'];
            $sub_total += $fixed_event_price;
        }
        $total_discount = 0;
        if(isset($booking->order_info['discount']) && !empty($booking->order_info['discount'])){
            $sub_total -= $booking->order_info['discount'];
            $total_discount += $booking->order_info['discount'];
        }
        if(isset($booking->order_info['coupon_discount']) && !empty($booking->order_info['coupon_discount'])){
            $sub_total -= $booking->order_info['coupon_discount'];
            $total_discount += $booking->order_info['coupon_discount'];
        }
        if(isset($booking->order_info['ebd_discount_amount']) && !empty($booking->order_info['ebd_discount_amount'])){
            $sub_total -= $booking->order_info['ebd_discount_amount'];
            $total_discount += $booking->order_info['ebd_discount_amount'];
        }
        $booking_item_price = em_price_with_position($booking->order_info['item_price']);
        if(isset($booking->multi_price_id) && !empty($booking->multi_price_id) && isset($booking->payment_log['multi_price_option_data']) && !empty($booking->payment_log['multi_price_option_data']->name)){
            $booking_item_price .= ' (' . $booking->payment_log['multi_price_option_data']->name . ')';
        }
        $mail_body = str_replace("$(Subtotal)", em_price_with_position($sub_total), $mail_body);
        $mail_body = str_replace("(Quantity)", $booking->order_info['quantity'], $mail_body);
        $mail_body = str_replace("$(Price)", $booking_item_price, $mail_body);
        $mail_body = str_replace("$(Discount)", em_price_with_position($total_discount), $mail_body);
        $mail_body = str_replace("$(Fixed Event Fee)", em_price_with_position($fixed_event_price), $mail_body);

        $lastFoot = explode( '</tfoot>', $mail_body );
        $lastFootUpdate = $lastFoot[0];
        // add attendees name in email
        if( empty( $event->enable_custom_booking_confirmation_email ) ) {
            if(!isset($booking->order_info['is_custom_booking_field']) || empty($booking->order_info['is_custom_booking_field'])){
                $attendee_names = isset($booking->attendee_names) ? implode(',', $booking->attendee_names) : "";
                $attendee_name_html = '<tr><th colspan="2" style="text-align:left;border-top-width:4px;color:#737373;border:1px solid #e4e4e4;padding:12px">'.esc_html__( 'Attendees', 'eventprime-event-calendar-management' ).' </th>';
                    $attendee_name_html .= '<td style="text-align:left;border-top-width:4px;color:#737373;border:1px solid #e4e4e4;padding:12px"><span>'.esc_attr($attendee_names).'</span></td>';
                $attendee_name_html .= '</tr>';
                $lastFootUpdate .= $attendee_name_html;
            }
            else{
                $attendee_names = $booking->attendee_names;
                $attendee_name_html = '';$i = 1;
                foreach($booking->attendee_names as $attendees){
                    $attData = array();
                    $attendee_name_html .= '<tr><th colspan="2" style="text-align:left;border-top-width:4px;color:#737373;border:1px solid #e4e4e4;padding:12px">'.esc_html__( 'Attendees '.$i, 'eventprime-event-calendar-management' ).' </th>';
                        $attendee_name_html .= '<td style="text-align:left;border-top-width:4px;color:#737373;border:1px solid #e4e4e4;padding:12px">';
                            foreach($attendees as $label => $value){
                               $attendee_name_html .= '<span>'. $label .' : '. $value .' </span><br/>';
                            }
                        $attendee_name_html .= '</td>';
                    $attendee_name_html .= '</tr>';
                    $i++;
                }
                $lastFootUpdate .= $attendee_name_html;
            }
        }
        $lastFootUpdate = apply_filters( 'event_magic_booking_confirmed_footer_contnent', $lastFootUpdate, $event );
        $mail_body = $lastFootUpdate . '</tfoot>' . $lastFoot[1];
        
        /* send Mail to User */
        $to = $user->user_email;
        $subject = __('Booking Refund','eventprime-event-calendar-management');
        $headers = array('Content-Type: text/html; charset=UTF-8');
        if(empty($gs->disable_frontend_email)){
            wp_mail($to, $subject, $mail_body, $headers);
        }
        
        //Admin notification
        $admin_email = get_option('admin_email');  
        $to = $admin_email; 
        $subject = sprintf(__('Booking Refund on Booking ID# %d','eventprime-event-calendar-management'),$booking->id);        
        $body = sprintf(__('A refund of %s%0.2f has been issued to booking #%d for %s','eventprime-event-calendar-management'),$booking->order_info['currency'],$sub_total,$booking->id,$event->name);
        $headers = array('Content-Type: text/html; charset=UTF-8');
        if(empty($gs->disable_admin_email)){
            wp_mail($to, $subject, $body, $headers);
        }
    }
    
    public static function user_registration($user_data=null ) { 
       /*  self::configure_mail(); */
        $setting_service = EventM_Factory::get_service('EventM_Setting_Service');
        $gs= $setting_service->load_model_from_db();
        $mail_body= $gs->registration_email_content;
        $mail_body = str_replace("@username",$user_data->email,$mail_body);
        $mail_body = str_replace("@first_name",get_user_meta($user_data->user_id, 'first_name', event_m_get_param('first_name', true)),$mail_body);
        $mail_body = str_replace("@last_name",get_user_meta($user_data->user_id, 'last_name', event_m_get_param('last_name', true)),$mail_body);
        $mail_body = str_replace("@phone",get_user_meta($user_data->user_id, 'phone', event_m_get_param('phone', true)),$mail_body);
        
        //$body_content .= "Your auto generated password is ".$user_data->password;
        $registration_email_subject= $gs->registration_email_subject;
        
        if(empty($registration_email_subject)){
            $registration_email_subject= get_bloginfo('name');
        }
        
        if(!empty($user_data)){
            $headers = array('Content-Type: text/html; charset=UTF-8');
            wp_mail( $user_data->email, $registration_email_subject, $mail_body, $headers );
            $admin_email = get_option('admin_email'); 
            $to = $admin_email;
            $subject = __('New User Registered','eventprime-event-calendar-management'); 
            $body = sprintf(__('New user %s has Registered','eventprime-event-calendar-management'),$user_data->email);
            wp_mail( $to, $subject, $body, $headers);
            return true;
        }
        return false;
    }
    
    public static function event_submitted($event_id)
    {  
        $setting_service = EventM_Factory::get_service('EventM_Setting_Service');
        $event_service = EventM_Factory::get_service('EventM_Service');
        $gs = $setting_service->load_model_from_db();
        if(empty($gs->send_event_submitted_email))
           return;

        if(empty($gs->event_submitted_email) || is_null($gs->event_submitted_email)) {
            ob_start();
            include(EM_BASE_DIR . 'includes/mail/event_submitted.html');
            $gs->event_submitted_email = ob_get_clean();
        }
        $userEmail = wp_get_current_user()->user_email;
        if(empty($userEmail)){
            $userEmail = 'User';
        }
        /* self::configure_mail(); */
        $mail_body = $gs->event_submitted_email;
        $mail_body = str_replace("@UserEmail",$userEmail,$mail_body);
        $mail_body = str_replace("@EventName",get_the_title($event_id),$mail_body);
        $mail_body = str_replace("@EventStartDate",em_showDateTime(em_get_post_meta($event_id,'start_date',true),true),$mail_body);
        $mail_body = str_replace("@EventEndDate",em_showDateTime(em_get_post_meta($event_id,'end_date',true),true),$mail_body);
       
        /* Send Mail to Admin */    
        $to = get_option('admin_email');
        $subject = __('New Event Submitted','eventprime-event-calendar-management');
        $headers = array('Content-Type: text/html; charset=UTF-8');
        if(empty($gs->disable_admin_email)){
            wp_mail($to, $subject, $mail_body, $headers);
        }
    }
    
    public static function event_approved($event_id){  
        $setting_service = EventM_Factory::get_service('EventM_Setting_Service');
        $event_service = EventM_Factory::get_service('EventM_Service');
        $gs = $setting_service->load_model_from_db();
        if(empty($gs->send_event_approved_email))
           return;
        $event = $event_service->load_model_from_db($event_id);
        
        if(empty($gs->event_approved_email) || is_null($gs->event_approved_email)) {
            ob_start();
            include(EM_BASE_DIR . 'includes/mail/event_approved.html');
            $gs->event_submitted_email = ob_get_clean();
        }
        /* self::configure_mail(); */
        $mail_body = $gs->event_approved_email;
        $mail_body = str_replace("@UserName",get_the_author_meta('display_name',$event->user),$mail_body);
        $mail_body = str_replace("@EventName",$event->name,$mail_body);
        $mail_body = str_replace("@SiteURL",site_url(),$mail_body);
        $mail_body = str_replace("@EventLink",add_query_arg(array('event'=>$event->id),get_permalink($gs->events_page)),$mail_body);
        /* Send Mail to Event Author */
        $to = get_the_author_meta('user_email',$event->user);
        $subject = __('Event Published','eventprime-event-calendar-management');
        $headers = array('Content-Type: text/html; charset=UTF-8');
        if(empty($gs->disable_frontend_email)){
            wp_mail($to, $subject, $mail_body,$headers);
        }
    }
    
    private static function configure_mail() {  
        add_filter('wp_mail_content_type', 'em_set_mail_content_type_html');
        add_filter('wp_mail_from', 'em_set_mail_from');
        add_filter('wp_mail_from_name', 'em_set_mail_from_name');
    }

    private static function get_event_type_name($event) {
        $event_type_service = EventM_Factory::get_service('EventTypeM_Service');
        $event_type = $event_type_service->load_model_from_db($event->event_type);
        if($event_type){
            return $event_type->name;
        }
        return;
    }

    private static function gcal_link($event, $venue) {
        $html = '<div id="authorize-button" class="kf-event-add-calendar em_color dbfl">
            <a class="em-events-gcal em-events-button em-color em-bg-color-hover em-border-color" href="https://www.google.com/calendar/event?action=TEMPLATE&text='.urlencode($event->name).'&dates='.gmdate('Ymd\\THi00', ($event->start_date)).'/'.gmdate('Ymd\\THi00', ($event->end_date)).'&details='.urlencode($event->description).$venue->name.'" target="_blank" title="'.esc_html__('Add To Google Calendar', 'eventprime-event-calendar-management').'">';
                $html .= esc_html__("Add To Google Calendar", 'eventprime-event-calendar-management');
            $html .= '</a>
        </div>';
        return $html;
    }

    private static function iCal_link($event, $venue) {
        $setting_service = EventM_Factory::get_service('EventM_Setting_Service');
        $global_settings = $setting_service->load_model_from_db();
        $url = add_query_arg('event', $event->id, get_page_link($global_settings->events_page));
        $url .= '&download=ical';
        $html = '<div class="ep-ical-download em_color" title="'.esc_html__('+ iCal / Outlook export', 'eventprime-event-calendar-management').'"><a href="'.$url.'">'. __('+ iCal / Outlook export', 'eventprime-event-calendar-management').'</a></div>';
        return $html;
    }

    private static function get_event_organizer_data($event) {
        $response = $org_name = array();
        $event_organizers = $event->organizer;
        if(is_serialized($event_organizers)){
            $event_organizers = unserialize($event->organizer);
        }
        $response['name'] = $response['phone'] = $response['email'] = $response['website'] = '';
        if(!empty($event_organizers)){
            $organizer_service = EventM_Factory::get_service('EventOrganizerM_Service');
            $org_phone = $org_email = $org_site = '';
            foreach($event_organizers as $organizer_id){
                $organizer_detail = $organizer_service->get_organizer($organizer_id);
                $org_name[] = $organizer_detail->name;
                if(!empty($organizer_detail->organizer_phones)){
                    $phone_data = implode(',', $organizer_detail->organizer_phones);
                    if(!empty($org_phone)){
                        $phone_data = ' | '. implode(',', $organizer_detail->organizer_phones);
                    }
                    $org_phone .= $phone_data;
                }
                if(!empty($organizer_detail->organizer_emails)){
                    $email_data = implode(',', $organizer_detail->organizer_emails);
                    if(!empty($org_email)){
                        $email_data = ' | '. implode(',', $organizer_detail->organizer_emails);
                    }
                    $org_email .= $email_data;
                }
                if(!empty($organizer_detail->organizer_websites)){
                    $website_data = implode(',', $organizer_detail->organizer_websites);
                    if(!empty($org_site)){
                        $website_data = ' | '. implode(',', $organizer_detail->organizer_websites);
                    }
                    $org_site .= $website_data;
                }
            }
            $response['name'] = implode(',', $org_name);
            $response['phone'] = $org_phone;
            $response['email'] = $org_email;
            $response['website'] = $org_site;
        }
        return $response;
    }

    private static function get_event_performer_data($event) {
        $response = $name = $role = array();
        $response['name'] = $response['role'] = '';
        $performer_service = EventM_Factory::get_service('EventM_Performer_Service');
        if(!empty($event->performer)){
            foreach($event->performer as $id) {
                $performer = $performer_service->load_model_from_db($id);
                $name[] = $performer->name;
                $role[] = $performer->role;
            }
            $response['name'] = implode(',', $name);
            $response['role'] = implode(',', $role);
        }
        return $response;
    }

    private static function get_booking_attendee_name_html($booking) {
        $attendee_name_html = '';$i = 1;
        // add attendees name in email
        if(!isset($booking->order_info['is_custom_booking_field']) || empty($booking->order_info['is_custom_booking_field'])){
            $attendee_names = isset($booking->attendee_names) ? implode(',', $booking->attendee_names) : "";
            $attendee_name_html = '<tr><th colspan="2" style="text-align:left;border-top-width:4px;color:#737373;border:1px solid #e4e4e4;padding:12px">'.esc_html__( 'Attendees', 'eventprime-event-calendar-management' ).' </th>';
                $attendee_name_html .= '<td colspan="2" style="text-align:left;border-top-width:4px;color:#737373;border:1px solid #e4e4e4;padding:12px"><span>'.esc_attr($attendee_names).'</span></td>';
            $attendee_name_html .= '</tr>';
        }
        else{
            $attendee_names = $booking->attendee_names;
            foreach($booking->attendee_names as $attendees){
                $attData = array();
                $attendee_name_html .= '<tr><th colspan="2" style="text-align:left;border-top-width:4px;color:#737373;border:1px solid #e4e4e4;padding:12px">'.esc_html__( 'Attendees '.$i, 'eventprime-event-calendar-management' ).' </th>';
                    $attendee_name_html .= '<td colspan="2" style="text-align:left;border-top-width:4px;color:#737373;border:1px solid #e4e4e4;padding:12px">';
                        foreach($attendees as $label => $value){
                           $attendee_name_html .= '<span>'. $label .' : '. $value .' </span><br/>';
                        }
                    $attendee_name_html .= '</td>';
                $attendee_name_html .= '</tr>';
                $i++;
            }
        }
        return $attendee_name_html;
    }

    private static function get_attendee_name_variable_data($booking) {
        $attendee_name_html = '';$i = 1;
        // add attendees name in email
        if(!isset($booking->order_info['is_custom_booking_field']) || empty($booking->order_info['is_custom_booking_field'])){
            $attendee_name_html = isset($booking->attendee_names) ? implode(',', $booking->attendee_names) : "";
        }
        else{
            $attendee_names = $booking->attendee_names;
            foreach($booking->attendee_names as $attendees){
                $attData = array();
                $attendee_name_html .= '<table><tr><th colspan="2" style="text-align:left;border-top-width:4px;color:#737373;border:1px solid #e4e4e4;padding:12px">'.esc_html__( 'Attendees '.$i, 'eventprime-event-calendar-management' ).' </th>';
                    $attendee_name_html .= '<td style="text-align:left;border-top-width:4px;color:#737373;border:1px solid #e4e4e4;padding:12px">';
                        foreach($attendees as $label => $value){
                           $attendee_name_html .= '<span>'. $label .' : '. $value .' </span><br/>';
                        }
                    $attendee_name_html .= '</td>';
                $attendee_name_html .= '</tr></table>';
                $i++;
            }
        }
        return $attendee_name_html;
    }
}