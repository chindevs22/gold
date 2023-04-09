<?php do_action('event_magic_admin_promotion_banner'); ?>
<div class="kikfyre" ng-app="eventMagicApp" ng-controller="bookingCtrl" ng-init="initialize('edit')" ng-cloak="">
    <div class="kf_progress_screen" ng-show="requestInProgress"></div>
    <div ng-hide="requestInProgress">
        <div class="kf-operationsbar dbfl">
            <div class="kf-title difl"> From: {{data.post.user.email}}</div> 
            <div class="kf-nav dbfl">
                <ul>
                    <li>    
                        <div class="ep-booking-print-ticket">
                            <?php do_action('event_magic_print_ticket_btn'); ?>
                            <span  ng-show="data.post.event_status != 'trash'">
                                <a href="admin-ajax.php?action=em_download_booking_details&booking_id={{data.post.id}}" class="btn-primary"> <?php _e('Download Booking Details', 'eventprime-event-calendar-management'); ?></a>
                            </span>
                       </div>
                    </li>       
                </ul>               
            </div>
        </div>
        <div class="em-booking-detail-wrap dbfl">
            <div class="em-booking-row" >
                <span class="em-booking-label"><?php _e('Booking ID', 'eventprime-event-calendar-management'); ?>:</span> <span class="em-booking-detail"> {{data.post.id}}</span>
            </div>

            <div class="em-booking-row" >
                <span class="em-booking-label"><?php _e('Email', 'eventprime-event-calendar-management'); ?>:</span> <span class="em-booking-detail">{{data.post.order_info.user_email}}</span>
            </div>

            <div ng-show="data.post.user.phone.length > 0" class="em-booking-row" >
                <span class="em-booking-label"><?php _e('Phone', 'eventprime-event-calendar-management'); ?>:</span> <span class="em-booking-detail">{{data.post.user.phone}}</span>
            </div>

            <div ng-show="data.post.event_name" class="em-booking-row" >
                <span class="em-booking-label"><?php _e('Event', 'eventprime-event-calendar-management'); ?>:</span> <span class="em-booking-detail">{{data.post.event_name}}</span>
            </div>

            <div ng-show="data.post.order_info.quantity > 0" class="em-booking-row" >
                <span class="em-booking-label"><?php _e('No. of Attendees', 'eventprime-event-calendar-management'); ?>:</span> <span class="em-booking-detail">{{data.post.order_info.quantity}}</span>
            </div>
            
            <div ng-show="data.post.attendee_names.length > 0" class="em-booking-row" >
                <span class="em-booking-label"><?php _e('Names of Attendees', 'eventprime-event-calendar-management'); ?>:</span> 
                <span class="em-booking-detail attendees-info">
                    <ol class="ep-attendee-display " ng-show="!data.post.order_info.is_custom_booking_field || data.post.order_info.is_custom_booking_field == 0">
                        <li ng-repeat="(i,v) in data.post.attendee_names track by $index">
                            {{data.post.attendee_names[i]}}
                        </li>   
                    </ol>
                    <div class="em-cbf-attendee-info em-booking-detail" ng-show="data.post.order_info.is_custom_booking_field && data.post.order_info.is_custom_booking_field == 1">
                        <div ng-repeat="namedata in data.post.attendee_names track by $index" class="em-custom-booking-fields-data">
                            <div class="em-cbf-label-info" ng-repeat="(key, value) in namedata">
                                <span class="em-cbf-label">{{key}}</span>: <span class="em-cbf-value">{{value}}</span>
                            </div>
                        </div>
                    </div>
                </span>
            </div>
            
            <div ng-show="data.post.order_info.seat_sequences" class="em-booking-row" >
                <span class="em-booking-label"><?php _e('Seat No.', 'eventprime-event-calendar-management'); ?>:</span> <span class="em-booking-detail">{{data.post.order_info.seat_sequences.join()}}</span>
            </div>

            <div ng-show="data.post.date" class="em-booking-row" >
                <span class="em-booking-label"><?php _e('Booked On', 'eventprime-event-calendar-management'); ?>:</span> <span class="em-booking-detail">{{data.post.booked_on}}</span>
            </div>
            
            <div class="em-booking-row" >
                <span class="em-booking-label"><?php _e('Total Price', 'eventprime-event-calendar-management'); ?>:</span>

                <span class="em-booking-detail">{{data.post.ticket_price | currencyPosition: data.post.currency_position : data.post.currency_symbol}}</span>
                
                <span class="em-booking-detail" ng-if="data.post.variation_name"> ({{data.post.variation_name}})</span>
            </div>

            <div class="em-booking-row" ng-show="data.post.order_info.fixed_event_price > 0">
                <span class="em-booking-label"><?php _e('One-Time event Fee', 'eventprime-event-calendar-management'); ?>:</span>
                <span class="em-booking-detail">{{data.post.order_info.fixed_event_price | currencyPosition: data.post.currency_position : data.post.currency_symbol}}</span>
            </div>

            <div ng-show="data.post.order_info.discount > 0" class="em-booking-row" >
                <span class="em-booking-label">Discount:</span>
                <span class="em-booking-detail">{{data.post.order_info.discount | currencyPosition: data.post.currency_position : data.post.currency_symbol}}</span>
            </div>
            
            <div ng-show="data.post.order_info.applied_ebd > 0 && data.post.order_info.ebd_discount_amount > 0" class="em-booking-row" >
                <span class="em-booking-label">Automatic Discount:</span>
                <span class="em-booking-detail">{{data.post.order_info.ebd_discount_amount | currencyPosition: data.post.currency_position : data.post.currency_symbol}}</span>
            </div>

            <div ng-if="data.post.order_info.coupon_code" class="em-booking-row" >
                <span class="em-booking-label"><?php _e('Coupon Amount', 'eventprime-event-calendar-management'); ?>:</span>
                <span class="em-booking-detail">{{data.post.order_info.coupon_discount | currencyPosition: data.post.currency_position : data.post.currency_symbol}}</span>
            </div>

            <div ng-show="data.post.status == 'completed'" class="em-booking-row" >
                <span class="em-booking-label"><?php _e('Amount Received', 'eventprime-event-calendar-management'); ?>:</span> <span class="em-booking-detail">{{data.post.amount_received}}</span>
            </div>

            <div ng-show="data.post.status != 'completed'" class="em-booking-row">
                <span class="em-booking-label"><?php _e('Amount Due', 'eventprime-event-calendar-management'); ?>:</span> <span class="em-booking-detail">{{data.post.amount_due}}</span>
            </div>

            <div ng-show="data.post.status != 'completed'" class="em-booking-row">
                <span class="em-booking-label"><?php _e('Amount Due', 'eventprime-event-calendar-management'); ?>:</span> <span class="em-booking-detail">{{data.post.amount_due}}</span>
            </div>

            <div class="em-booking-row" >
                <span class="em-booking-label"><?php _e('Booking Status', 'eventprime-event-calendar-management'); ?>:</span> <span class="em-booking-detail">{{data.post.status.toUpperCase()}}</span>
            </div>
            <div class="em-booking-row" >
                <span class="em-booking-label"><?php _e('Payment Gateway', 'eventprime-event-calendar-management'); ?>:</span> <span class="em-booking-detail">{{data.post.payment_gateway.toUpperCase()}}</span>
            </div>
            <?php do_action('event_magic_admin_offline_handle'); ?>

            <div class="em-booking-row kf-bg-light" >
                <span class="em-booking-label"><?php _e('Notifications', 'eventprime-event-calendar-management'); ?>:</span>
                <span class="em-booking-detail ep-booking-bg-light">
                    <ul>
                        <li ng-show="!data.post.order_info.guest_booking"><a href="javascript:void(0)" ng-click="reset_password_mail()">Reset User Password Mail</a></li>
                        <li ng-show="data.post.status == 'cancelled' && data.post.cancel_email"><a href="javascript:void(0)" ng-click="cancellation_mail()"><?php _e('Resend Cancellation Mail', 'eventprime-event-calendar-management'); ?></a></li>
                        <li ng-show="data.post.status == 'completed' && data.post.confirm_email"><a href="javascript:void(0)" ng-click="confirm_mail()"><?php _e('Resend Booking Confirm Mail', 'eventprime-event-calendar-management'); ?></a></li>
                        <li ng-show="data.post.status == 'refunded'"><a href="javascript:void(0)" ng-click="refund_mail()"><?php _e('Resend Booking Refund Mail', 'eventprime-event-calendar-management'); ?></a></li>
                        <li ng-show="data.post.status == 'pending'"><a href="javascript:void(0)" ng-click="pending_mail()"><?php _e('Resend Booking Pending Mail', 'eventprime-event-calendar-management'); ?></a></li>                                 
                    </ul>
                </span>
            </div>
            
            <div class="em-booking-row" ng-show="data.post.payment_log">
                <span class="em-booking-label"><input type="button" id="display_log" class="btn-primary kf-upload" value="Transaction Log" ng-click="showDiv = !showDiv" /></span>
                <span ng-show="showDiv" class="em-booking-detail" ><pre>{{data.post.payment_log| json}}</pre></span>
            </div>
            <div class="em-booking-row">
                {{refund_status}}
                <div ng-show="data.post.final_price > 0 && (data.post.status == 'cancelled')">
                  <span class="em-booking-label"><input type="button" value="Refund" ng-click="cancelBooking()" class="btn btn-primary kf-upload" /></span>
                </div>
            </div>
            
            <?php do_action('event_magic_admin_booking_show_custom_data', $post_id); ?>
        </div>
        

        <div class="em-sticky-note-wrap dbfl">
            <div class="emheader"><?php _e('New Note','eventprime-event-calendar-management'); ?></div>
            <div class="em-sticky-note dbfl">
                <div class="em-booking-row em-booking-textarea" >
                    <div class="emrow">
                        <textarea name="note" ng-model="data.post.note">
                    
                        </textarea>    
                        <div class="dbfl"> <input type="button" ng-click="savePost()" value="Add"  class="btn btn-primary kf-upload"/></div>
                    </div>
                    <div class="emrow">
                        <ul  class="em-notes-row" >
                            <li ng-repeat="note in data.post.notes track by $index">
                                {{note}}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div id="em_printable">

            </div>
        </div>
        <div class="ep-booking-footer dbfl kf-buttonarea">
            <a class="kf-cancel" href="<?php echo admin_url('/admin.php?page=em_bookings'); ?>">&#8592; &nbsp;<?php _e('Back', 'eventprime-event-calendar-management'); ?></a>
        </div>
    </div>
</div>