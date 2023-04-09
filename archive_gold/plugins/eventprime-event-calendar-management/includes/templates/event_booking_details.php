<?php
wp_enqueue_style('em-public-css');
wp_enqueue_script('em-public');
em_localize_map_info('em-google-map');
$url_booking_id = sanitize_text_field(event_m_get_param('id'));
if(isset($atts) && isset($atts['id']) && !empty($atts['id']) && is_numeric($atts['id'])){
    $url_booking_id = sanitize_text_field($atts['id']);
}
if(!empty($url_booking_id)){
    $is_guest = (isset($_GET['is_guest']) ? sanitize_text_field(event_m_get_param('is_guest')) : 0);
    if(!is_user_logged_in() && empty($is_guest)){
        include_once('user_registration.php');
    }
    else{
        $user = wp_get_current_user();
        $roles = (array) $user->roles;
        // Get booking details
        $booking_service = EventM_Factory::get_service('EventM_Booking_Service');
        $booking = $booking_service->load_model_from_db($url_booking_id);
        $order_with_guest_booking = isset($booking->order_info['guest_booking']) ? $booking->order_info['guest_booking'] : 0;
        if($user->ID != $booking->user){
            if(!in_array('administrator', $roles, true) && (empty($is_guest))){
                echo '<div class="emagic"><div class="ep-login-form em_block em_bg_lt dbfl">';
                echo "<div class='ep-login-header dbfl'> <h3 class='em_form_heading'>".__("No booking found!", 'eventprime-event-calendar-management')."</h3></div>" ;
                echo '<div> <div>';
                exit();
            }
        }
        
        if(!empty($is_guest) && empty($order_with_guest_booking)){
            echo '<div class="emagic"><div class="ep-login-form em_block em_bg_lt dbfl">';
            echo "<div class='ep-login-header dbfl'> <h3 class='em_form_heading'>".__("No booking found!", 'eventprime-event-calendar-management')."</h3></div>" ;
            echo '<div> <div>';
            exit();
        }
        $total_price = $booking_service->get_final_price($booking->id); 
        $total_price = empty($total_price) ? __('Free','eventprime-event-calendar-management') : em_price_with_position($total_price, $booking->order_info['currency']);
        $offline_status = isset($booking->payment_log['offline_status']) ? $booking->payment_log['offline_status'] : null;
        $event_service = EventM_Factory::get_service('EventM_Service');
        $event = $event_service->load_model_from_db($booking->event);
        $setting_service = EventM_Factory::get_service('EventM_Setting_Service');
        $global_settings = $setting_service->load_model_from_db();
        $venue_service = EventM_Factory::get_service('EventM_Venue_Service');
        $venue = (isset($event->venue) && !empty($event->venue)) ? $venue_service->load_model_from_db($event->venue) : array();
        $eventUrl = add_query_arg( 'event', $event->id, get_page_link( $global_settings->events_page ) );?>

        <!--New Booking Table -->
        <div class="emagic">
            <div class="ep-booking-details-container">
                <div class="booking-details-head">
                    <div class="ep-booking-id em_color">
                        <?php echo esc_html__('Booking #', 'eventprime-event-calendar-management') . $booking->id; ?>
                    </div>
                    <div class="ep-booking-print"> 
                     
                        <?php do_action('event_magic_print_ticket', $booking); ?>
                              
                    </div>
                </div>
                
                <table align="center" border="0" cellspacing="0" cellpadding="0" class="ep-booking-table-main" style="border-collapse: collapse; table-layout: fixed;">
                    <tbody>
                        <tr>
                            <td class="ep-booking-body">
                                <table align="center" border="0" cellspacing="0" cellpadding="0" class="ep-booking-table" style="    border-collapse: collapse;table-layout: fixed;background: #f6f8fA;border-bottom: 1px solid #e7e8e9;">
                                    <tbody>
                                        <tr>
                                            <td class="ep-booking-info" style="min-height: 50px; padding: 40px 20px;">
                                                <table align="center" border="0" cellspacing="0" cellpadding="0" class="ep-booking-table-info" style="border-collapse: collapse; table-layout: fixed;">
                                                    <tbody>
                                                        <tr style="">
                                                            <td class="ep-booking-info-title" style="width: 130px; color: #000; font-size: 14px; font-weight: 500; line-height: 29px;">
                                                            <?php _e('Event Name','eventprime-event-calendar-management'); ?>
                                                            </td>
                                                            <td class="ep-booking-info-title" style=" font-size: 14px; font-weight: 500; line-height: 25px;">
                                                                <a href="<?php echo esc_url($eventUrl);?>" target="_blank"><?php echo $event->name; ?></a>
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <td class="ep-booking-info-title" style="width: 130px; color: #000; font-size: 14px; font-weight: 500; line-height: 29px;">
                                                                <?php esc_html_e('Booking Date','eventprime-event-calendar-management'); ?>
                                                            </td>
                                                            <td class="ep-booking-info-v" style="color: #75787b;  font-size: 14px; font-weight: 400; line-height: 25px;">
                                                                <?php echo date_i18n(get_option('date_format').' '.get_option('time_format'), $booking->date); ?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="ep-booking-info-title" style="width: 130px; color: #000; font-size: 14px; font-weight: 500; line-height: 29px;">
                                                                <?php esc_html_e('Event Date','eventprime-event-calendar-management'); ?>
                                                            </td>
                                                            <td class="ep-booking-info-v" style="color: #75787b;  font-size: 14px; font-weight: 400; line-height: 25px;">
                                                                <?php 
                                                                if(!empty($event->all_day)){
                                                                    if(is_multidate_event($event)){
                                                                        echo date_i18n(get_option('date_format'), $event->start_date) . ' - ' . date_i18n(get_option('date_format'), $event->end_date);
                                                                    }
                                                                    else{
                                                                        echo date_i18n(get_option('date_format'),$event->start_date).' - '.__('ALL DAY','eventprime-event-calendar-management');
                                                                    }
                                                                }
                                                                else{
                                                                    echo date_i18n(get_option('date_format').' '.get_option('time_format'),$event->start_date);
                                                                    echo ' - ';
                                                                    echo date_i18n(get_option('date_format').' '.get_option('time_format'),$event->end_date);
                                                                }?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="ep-booking-info-title" style="width: 130px; color: #000;  font-size: 14px; font-weight: 500; line-height: 29px;">
                                                                <?php esc_html_e('Booking Status', 'eventprime-event-calendar-management');?>
                                                            </td>
                                                            <td class="ep-booking-info-v" style="color: #75787b;  font-size: 14px; font-weight: 400; line-height: 25px;">
                                                               <?php echo EventM_Constants::$status[$booking->status]; ?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="ep-booking-info-title" style="width: 130px; color: #000;  font-size: 14px; font-weight: 500; line-height: 29px;">
                                                                <?php esc_html_e('Payment Method', 'eventprime-event-calendar-management');?>
                                                            </td>
                                                            <td class="ep-booking-info-v" style="color: #75787b;  font-size: 14px; font-weight: 400; line-height: 25px;">
                                                               <?php if(isset($booking->order_info['payment_gateway'])){ echo ucfirst($booking->order_info['payment_gateway']); } else{ echo 'N/A';} ?>
                                                            </td>
                                                        </tr>
                                                        <?php do_action('event_magic_front_user_booking_details', $offline_status); ?>
                                                    </tbody>
                                                </table>
                                            </td>
                                            <td class="ep-booking-body-qr-code" style="min-height: 50px; width: 164px; padding: 40px 20px 40px 1px;"><?php
                                                if($global_settings->show_qr_code_on_ticket == 1){
                                                    $url = get_permalink($global_settings->booking_details_page);
                                                    $url = add_query_arg('id', $url_booking_id, $url);
                                                    $file_name = 'ep_qr_'.md5($url).'.png';
                                                    $upload_dir = wp_upload_dir();
                                                    $file_path = $upload_dir['basedir'] . '/ep/' . $file_name;
                                                    if(!file_exists($file_path)){
                                                        if(!file_exists(dirname($file_path))){
                                                            mkdir(dirname($file_path), 0755);
                                                        }
                                                        require_once EM_BASE_DIR . 'includes/lib/qrcode.php';
                                                        $qrCode = new QRcode();
                                                        $qrCode->png($url, $file_path, 'M', 4, 2);
                                                    }
                                                    $image_url = $upload_dir['baseurl'].'/ep/'.$file_name;?>
                                                    <img src="<?php echo $image_url; ?>"  width="164" alt="<?php echo __('QR Code', 'eventprime-event-calendar-management'); ?>" style="border: 0; line-height: 100%; outline: 0;  display: block; font-size: 14px;" /><?php
                                                }?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <?php
                                if(isset($booking->order_info) && !empty($booking->order_info)){?>
                                    <table align="center" border="0" cellspacing="0" cellpadding="0" class="ep-booking-items-table-wrap" style="border-collapse: collapse; table-layout: fixed; background: #fff;">
                                        <tbody>
                                            <tr>
                                                <td class="ep-booking-items-w ep-booking-items-bottom" style="padding: 40px 20px 0px 20px;">
                                                    <div class="ep-booking-items-title">
                                                        <?php esc_html_e('ORDER DETAILS', 'eventprime-event-calendar-management');?>
                                                    </div>
                                                    <!-- Invoice Items -->
                                                    <table border="0" cellspacing="0" cellpadding="0" class="ep-booking-items-table" style="border-collapse: collapse;  table-layout: fixed;">
                                                        <thead>
                                                            <tr class="booking-details-border">
                                                                <th class="ep-booking-item" style="width: 55%; text-align:left;">
                                                                    <?php esc_html_e('Seat', 'eventprime-event-calendar-management');?>
                                                                </th>
                                                                <th class="ep-booking-item" style="width: 15%;text-align:center;">
                                                                    <?php esc_html_e('Price', 'eventprime-event-calendar-management');?>
                                                                </th>
                                                                <th class="ep-booking-item" style="width: 15%;">
                                                                    <?php esc_html_e('Quantity', 'eventprime-event-calendar-management');?>
                                                                </th>
                                                                <th class="ep-booking-item" style="width: 20%;">
                                                                    <?php esc_html_e('Total', 'eventprime-event-calendar-management');?>
                                                                </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            if(!isset($booking->order_info['order_item_data'])){
                                                                if(!empty($venue)) { 
                                                                    if($venue->type == 'seats' && count($booking->order_info['seat_sequences']) > 0 ){?>
                                                                        <tr style="border-bottom: 1px solid #e7e8e9;">
                                                                            <td class="ep-booking-item" style="text-align: left">
                                                                                <?php echo implode(',',$booking->order_info['seat_sequences']); ?>
                                                                            </td>
                                                                            <td class="ep-booking-item">
                                                                                <?php
                                                                                if(isset($booking->order_info['subtotal'])){
                                                                                    $em_price = em_price_with_position($booking->order_info['subtotal'], $booking->order_info['currency']);
                                                                                }
                                                                                else{
                                                                                    $em_price = em_price_with_position($booking->order_info['item_price'], $booking->order_info['currency']);   
                                                                                }
                                                                                echo $em_price;
                                                                                if(isset($booking->multi_price_id) && !empty($booking->multi_price_id) && isset($booking->payment_log['multi_price_option_data']) && !empty($booking->payment_log['multi_price_option_data']->name)){
                                                                                    echo ' (' . $booking->payment_log['multi_price_option_data']->name . ')';
                                                                                }?>
                                                                            </td>
                                                                            <td class="ep-booking-item">
                                                                                <?php echo $booking->order_info['quantity'];?>
                                                                            </td>
                                                                            <td class="ep-booking-item">
                                                                                <?php echo em_price_with_position($booking->order_info['item_price'] * $booking->order_info['quantity'], $booking->order_info['currency']);?>
                                                                            </td>
                                                                        </tr><?php
                                                                    } else{
                                                                        if(isset($booking->multi_price_id) && !empty($booking->multi_price_id)){
                                                                            if(isset($booking->payment_log['multi_price_option_data']) && !empty($booking->payment_log['multi_price_option_data'])){?>
                                                                                <tr style="border-bottom: 1px solid #e7e8e9;">
                                                                                    <td class="ep-booking-item" style="text-align: left">
                                                                                        <?php echo $booking->payment_log['multi_price_option_data']->name; ?>
                                                                                    </td>
                                                                                    <td class="ep-booking-item">
                                                                                        <?php echo em_price_with_position($booking->order_info['item_price'], $booking->order_info['currency']);?>
                                                                                    </td>
                                                                                    <td class="ep-booking-item">
                                                                                        <?php echo $booking->order_info['quantity'];?>
                                                                                    </td>
                                                                                    <td class="ep-booking-item">
                                                                                        <?php echo em_price_with_position($booking->order_info['item_price'] * $booking->order_info['quantity'], $booking->order_info['currency']);?>
                                                                                    </td>
                                                                                </tr><?php
                                                                            } else{?>
                                                                                <tr style="border-bottom: 1px solid #e7e8e9;">
                                                                                    <td class="ep-booking-item" style="text-align: left">
                                                                                        <?php echo $event->name; ?>
                                                                                    </td>
                                                                                    <td class="ep-booking-item">
                                                                                        <?php echo em_price_with_position($booking->order_info['item_price'], $booking->order_info['currency']);?>
                                                                                    </td>
                                                                                    <td class="ep-booking-item">
                                                                                        <?php echo $booking->order_info['quantity'];?>
                                                                                    </td>
                                                                                    <td class="ep-booking-item">
                                                                                        <?php echo em_price_with_position($booking->order_info['item_price'] * $booking->order_info['quantity'], $booking->order_info['currency']);?>
                                                                                    </td>
                                                                                </tr><?php
                                                                            }
                                                                        }else{?>
                                                                            <tr style="border-bottom: 1px solid #e7e8e9;">
                                                                                <td class="ep-booking-item" style="text-align: left">
                                                                                    <?php echo $event->name; ?>
                                                                                </td>
                                                                                <td class="ep-booking-item">
                                                                                    <?php echo em_price_with_position($booking->order_info['item_price'], $booking->order_info['currency']);?>
                                                                                </td>
                                                                                <td class="ep-booking-item">
                                                                                    <?php echo $booking->order_info['quantity'];?>
                                                                                </td>
                                                                                <td class="ep-booking-item">
                                                                                    <?php echo em_price_with_position($booking->order_info['item_price'] * $booking->order_info['quantity'], $booking->order_info['currency']);?>
                                                                                </td>
                                                                            </tr><?php
                                                                        }
                                                                    }
                                                                }
                                                            } else{
                                                                foreach ($booking->order_info['order_item_data'] as $ikey => $ivalue) {?>
                                                                    <tr style="border-bottom: 1px solid #e7e8e9;"><?php
                                                                        if(!empty($venue) && $venue->type == 'seats') {?>
                                                                            <td class="ep-booking-item em-with-seat-sequences" style="text-align: left">
                                                                                <?php echo $ivalue->seatNo;
                                                                                if(!empty($ivalue->variation_name)){
                                                                                    echo ' ('.$ivalue->variation_name.')';
                                                                                }?>
                                                                            </td><?php
                                                                        } else{?>
                                                                            <td class="ep-booking-item" style="text-align: left">
                                                                                <?php //echo $ivalue->seatNo;
                                                                                if(!empty($ivalue->variation_name)){
                                                                                    echo $ivalue->variation_name;
                                                                                }?>
                                                                            </td><?php
                                                                        }?>
                                                                        <td class="ep-booking-item">
                                                                            <?php echo em_price_with_position($ivalue->price, $booking->order_info['currency']);?>
                                                                        </td>
                                                                        <td class="ep-booking-item">
                                                                            <?php echo $ivalue->quantity;?>
                                                                        </td>
                                                                        <td class="ep-booking-item">
                                                                            <?php echo em_price_with_position($ivalue->sub_total, $booking->order_info['currency']);?>
                                                                        </td>
                                                                    </tr><?php
                                                                }
                                                            }?>
                                                            <?php //do_action('event_magic_front_user_booking_before_total_price', $booking); ?>
                                                        </tbody>
                                                        <tfoot>
                                                            <?php 
                                                            if(isset($booking->order_info['fixed_event_price']) && $booking->order_info['fixed_event_price'] > 0){?>
                                                                <tr> 
                                                                    <td></td>
                                                                    <td colspan="2" class="ep-booking-totsl-t"><?php _e('One-Time event Fee','eventprime-event-calendar-management'); ?></td>
                                                                    <td class="ep-booking-total-p"><?php  echo em_price_with_position($booking->order_info['fixed_event_price'], $booking->order_info['currency']); ?></td>
                                                                </tr><?php
                                                            }
                                                            if(isset($booking->order_info['discount']) && !empty($booking->order_info['discount'])){?>
                                                                <tr> 
                                                                    <td></td>
                                                                    <td colspan="2" class="ep-booking-totsl-t"><?php _e('Discount','eventprime-event-calendar-management'); ?></td>
                                                                    <td class="ep-booking-total-p"><?php  echo em_price_with_position($booking->order_info['discount'], $booking->order_info['currency']); ?></td>
                                                                </tr><?php
                                                            }
                                                            if(isset($booking->order_info['coupon_code']) && !empty($booking->order_info['coupon_code']) && isset($booking->order_info['coupon_discount']) && !empty($booking->order_info['coupon_discount'])){?>
                                                                <tr> 
                                                                    <td></td>
                                                                    <td colspan="2" class="ep-booking-totsl-t"><?php _e('Coupon Amount','eventprime-event-calendar-management'); ?></td>
                                                                    <td class="ep-booking-total-p"><?php  echo em_price_with_position($booking->order_info['coupon_discount'], $booking->order_info['currency']); ?></td>
                                                                </tr><?php
                                                            }?>
                                                            <?php do_action('event_magic_front_user_booking_before_total_price', $booking); ?>
                                                            <tr> 
                                                                <td></td>
                                                                <td colspan="2" class="ep-booking-totsl-t"><?php _e('Total Price','eventprime-event-calendar-management'); ?></td>
                                                                <td class="ep-booking-total-p"><?php  echo $total_price; ?></td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table><?php
                                }?>
                                
                                <table>
                                    <tbody>
                                        <tr>
                                            <td class="ep-booking-body-full ep-booking-attendees" style="padding: 15px 20px 15px 20px;">
                                                <div class="ep-booking-attendees-title">
                                                    Attendees
                                                </div>
                                                <div class="ep-booking-attendees-wrap" >      
                                                    <?php
                                                    if (!isset($booking->order_info['is_custom_booking_field']) || empty($booking->order_info['is_custom_booking_field'])) {
                                                        echo implode(', ', $booking->attendee_names);
                                                    } else {
                                                        foreach ($booking->attendee_names as $attendees) {
                                                            echo '<div class="ep-booking-attendee"> <div class="ep-booking-attendee-profile"><img class="ep-attendee-profile" src="https://secure.gravatar.com/avatar/39000aa1f1a266b2b15c734988cd7d2f?s=96&amp;d=mm&amp;r=g"></div> ';
                                                                echo '<div class="ep-booking-attendee-info-wrap">';
                                                            foreach ($attendees as $label => $value) {                                                    
                                                                echo '<div class="ep-booking-attendee-info">';
                                                                echo '<div class="ep-booking-attendee-label">' . $label . '</div>';
                                                                echo '<div class="ep-booking-attendee-value">' . $value . '</div>';
                                                                echo '</div>';
                                                                
                                                            }
                                                            echo '</div>';
                                                            echo '</div>';
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php if(($booking->status=="completed") && !empty($event->allow_cancellations)): ?>
                    <div id="em_action_bar">
                        <div><input type="button" id="em_cancelled_btn" value="<?php esc_html_e('Cancellation Request','eventprime-event-calendar-management');?>" onclick="em_cancel_booking(<?php echo $booking->id; ?>)" /></div>
                    </div>
                    <div id="em_booking_status_message"></div>
                <?php endif; ?>
                    <div class="ep-guest-booking-details">
                <?php
                    if( ! empty( $is_guest ) && ! empty( $order_with_guest_booking ) ){
                        do_action('event_magic_guest_booking_order_detail_redirect');
                        
                    } 
                ?>
                </div>
            </div>
        </div><?php
    }
}