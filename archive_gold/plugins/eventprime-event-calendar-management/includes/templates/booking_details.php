<?php
$booking_service = EventM_Factory::get_service('EventM_Booking_Service');
$total_price = $booking_service->get_final_price($booking->id); 
$total_price = empty($total_price) ? __('Free','eventprime-event-calendar-management') : em_price_with_position($total_price, $booking->order_info['currency']);
$offline_status = isset($booking->payment_log['offline_status']) ? $booking->payment_log['offline_status'] : null;
?>
<div class="em_modal_wrapper" id="em_booking_details_modal">
    <div id="em_message_bar">
    <?php if($booking->status=='pending'){
            _e('**Ticket download link will be available after Confirmation.','eventprime-event-calendar-management');
        }
        else if($booking->status=='cancelled'){
            _e('**Booking cancelled successfully.','eventprime-event-calendar-management');
        }
        else if($booking->status=='completed'){
            _e('**Booking has been completed.','eventprime-event-calendar-management');
        }
        else if($booking->status=='refunded'){
           _e('**We have issued a refund for this booking.','eventprime-event-calendar-management');
    } ?>
    </div>
    <table class="em_modal_table">
        <tr>
            <td><?php _e('Booking ID','eventprime-event-calendar-management'); ?></td>
            <td><?php echo $booking->id; ?></td>
        </tr>
        <tr>
            <td><?php _e('Booked On','eventprime-event-calendar-management'); ?></td>
            <td><?php echo date_i18n(get_option('date_format').' '.get_option('time_format'), $booking->date); ?></td>
        </tr>
        <tr>
            <td><?php _e('Event Name','eventprime-event-calendar-management'); ?></td>
            <td><?php echo $event->name; ?></td>
        </tr>
        <tr> 
            <td><?php _e('No. of Attendees','eventprime-event-calendar-management'); ?></td>
            <td><?php echo $booking->order_info['quantity']; ?></td>
        </tr>
        <?php 
        if(isset($booking->order_info['fixed_event_price']) && $booking->order_info['fixed_event_price'] > 0){?>
            <tr>
                <td><?php _e('One-Time event Fee','eventprime-event-calendar-management'); ?></td>
                <td><?php  echo em_price_with_position($booking->order_info['fixed_event_price'], $booking->order_info['currency']); ?></td>
            </tr><?php
        }
        if(!isset($booking->order_info['order_item_data'])){
            if(!empty($venue) && $venue->type == 'seats' && $booking->order_info['seat_sequences'] > 0 ) { ?>
                <tr> 
                    <td><?php _e('Seats','eventprime-event-calendar-management'); ?></td>
                    <td><?php echo implode(',',$booking->order_info['seat_sequences']); ?></td>
                </tr><?php
            }?>
            <tr>
                <td><?php _e('Price','eventprime-event-calendar-management'); ?></td>
                <td>
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
            </tr><?php
        } else{?>
            <tr>
                <td>
                    <?php esc_html_e('Order Info','eventprime-event-calendar-management'); ?>
                </td>
                <td>
                    <table><?php
                        foreach ($booking->order_info['order_item_data'] as $ikey => $ivalue) {
                            if(!empty($venue) && $venue->type == 'seats' && $booking->order_info['seat_sequences'] > 0 ) {?>
                                <tr>
                                    <td><?php esc_html_e('Seat','eventprime-event-calendar-management'); ?></td>
                                    <td>
                                        <?php echo $ivalue->seatNo;
                                        if(!empty($ivalue->variation_name)){
                                            echo ' ('.$ivalue->variation_name.')';
                                        }?>
                                    </td>
                                </tr><?php
                            } else{?>
                                <tr>
                                    <td><?php esc_html_e('Variation','eventprime-event-calendar-management'); ?></td>
                                    <td><?php echo $ivalue->variation_name;?></td>
                                </tr><?php
                            }?>
                            <tr>
                                <td><?php esc_html_e('Price','eventprime-event-calendar-management'); ?></td>
                                <td>
                                    <?php echo em_price_with_position($ivalue->price, $booking->order_info['currency']);?>
                                </td>
                            </tr>
                            <tr>
                                <td><?php esc_html_e('Quantity','eventprime-event-calendar-management'); ?></td>
                                <td>
                                    <?php echo $ivalue->quantity;?>
                                </td>
                            </tr>
                            <tr>
                                <td><?php esc_html_e('Sub Total','eventprime-event-calendar-management'); ?></td>
                                <td>
                                    <?php echo em_price_with_position($ivalue->sub_total, $booking->order_info['currency']);?>
                                </td>
                            </tr><?php
                        }?>
                    </table>
                </td>
            </tr><?php
        }
        if(isset($booking->order_info['coupon_code']) && !empty($booking->order_info['coupon_code']) && isset($booking->order_info['coupon_discount']) && !empty($booking->order_info['coupon_discount'])){?>            
            <tr>
                <td><?php _e('Coupon Amount','eventprime-event-calendar-management'); ?></td>
                <td><?php  echo em_price_with_position($booking->order_info['coupon_discount'], $booking->order_info['currency']); ?></td>
            </tr>
            <?php
        }?>
        <?php do_action('event_magic_front_user_booking_before_total_price', $booking); ?>
        <tr>
            <td><?php _e('Total Price','eventprime-event-calendar-management'); ?></td>
            <td><?php  echo $total_price; ?></td>
        </tr>
        <tr> 
            <td><?php _e('Booking Status','eventprime-event-calendar-management'); ?></td>
            <td id="em_booking_status"><?php echo EventM_Constants::$status[$booking->status]; ?></td>
        </tr>

        <tr> 
            <td><?php _e('Attendees','eventprime-event-calendar-management'); ?></td>
            <td><?php 
                if(!isset($booking->order_info['is_custom_booking_field']) || empty($booking->order_info['is_custom_booking_field'])){
                    echo implode(', ', $booking->attendee_names);
                }
                else{
                    foreach($booking->attendee_names as $attendees){
                        echo '<table>';
                            foreach($attendees as $label => $value){
                                echo '<tr>';
                                    echo '<td>'.$label.'</td>';
                                    echo '<td>'.$value.'</td>';
                                echo '</tr>';
                            }
                        echo '</table>';
                    }
                } ?>
            </td>
        </tr>
        
        <?php do_action('event_magic_front_user_booking_details', $offline_status); ?>

        <?php if(($booking->status=="completed") && !empty($event->allow_cancellations)): ?>
            <tr id="em_action_bar">
                <td><?php _e('Action','eventprime-event-calendar-management'); ?></td>
                <td><input type="button" id="em_cancelled_btn" value="<?php _e('Cancellation Request','eventprime-event-calendar-management');?>" onclick="em_cancel_booking(<?php echo $booking->id; ?>)" /></td>
            </tr>
        <?php endif; ?>
        
        <?php do_action('event_magic_print_ticket',$booking); ?>
    </table>     
</div>