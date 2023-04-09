<?php
    wp_enqueue_script('em-public');
    em_localize_map_info('em-google-map');
    wp_enqueue_script('em-booking-controller');
    wp_enqueue_style('em-public-css');
    add_thickbox();

    $event_id = absint(event_m_get_param('event_id'));
    $event_service = EventM_Factory::get_service('EventM_Service');
    $event = $event_service->load_model_from_db($event_id);
    if (empty($event->id)) {
        return;
    }
    if(empty($event->enable_booking)){
        _e('Booking is closed','eventprime-event-calendar-management');
        return;
    }
    $extensions= event_magic_instance()->extensions;
    $venue= null;
    if(!empty($event->venue)){
        $venue_service= EventM_Factory::get_service('EventM_Venue_Service');
        $venue= $venue_service->load_model_from_db($event->venue);
        if($venue->type=='seats' && !in_array('seating',$extensions))
        {
            _e('This event was created using Seating extension which is not active/installed. Please change venue type to standing and try again.','eventprime-event-calendar-management');
            return;
        }
    }
    $setting_service = EventM_Factory::get_service('EventM_Setting_Service');
    $global_settings = $setting_service->load_model_from_db();
?>
<div id="booking_dialog" class="emagic" ng-app="eventMagicApp" ng-controller="emBookingCtrl" ng-init="initialize(<?php echo $event->venue, ',' . $event->id; ?>)" ng-cloak>
    <div class="em_progress_screen" ng-show="requestInProgress"></div>
    <div class="emagic">
        <div ng-hide="show_cart">
            <div class="kf-seat-table-popup kf_config_pop_wrap" ng-show="event.venue.type == 'seats'">
                <!-- Event Seat structure -->
                <div id="kf-seat-table-parent" class="em_block dbfl kf_config_pop " >
                    <div class="kf-seat-table-popup-head dbfl">
                        <div class="proceed_button_container em_event_row difr">
                            <button  id="kf_seat_update" class="em_header_button" ng-hide="update_cart" ng-disabled="requestInProgress"  ng-click="orderSeats()" data-no_seat="<?php _e('No seats are selected', 'eventprime-event-calendar-management');?>"><?php echo em_global_settings_button_title('Proceed'); ?></button>
                            <button ng-disabled="requestInProgress" id="kf_update_cart" class="em_header_button kf-button em_color" ng-show="update_cart" ng-click="updateOrder()"><?php _e("Update Cart", 'eventprime-event-calendar-management'); ?></button>
                            <button class="em_header_button kf-button em_color" ng-click="display_cart(true)" ng-disabled="!(orders.length &gt; 0)" ng-show="(orders.length &gt; 0)"><?php _e("Show Cart", 'eventprime-event-calendar-management'); ?></button>
                        </div>

                        <div class="kf-popup-title"><?php _e('Please Select your seat(s) below', 'eventprime-event-calendar-management'); ?></div>
                        <div  class="kf-popup-sub-title"><?php _e('You can only select from available seats. Once you are done, click proceed to checkout.', 'eventprime-event-calendar-management'); ?></div>
                    </div>
                    <div class="kf-booking-seat-wrap dbfl">
                        <table class="em_venue_seating" ng-style="seat_container_width">
                            <tr>
                                <td class="kf-booking-head">
                                    <div class="kf-seat-selector"><?php _e('Selected Seats', 'eventprime-event-calendar-management'); ?>: {{selectedSeats.length}} </div>
                                    <div class="kf-single-ticket-price" ng-show="event.hide_0_price_from_frontend == 0 || event.ticket_price &gt; 0">
                                        <?php _e('Price', 'eventprime-event-calendar-management'); ?>
                                        <span class="em-booking-page-price">{{em_selected_seat_price | currencyPosition: event.currency_position : currency_symbol}}</span>
                                    </div>
                                    <div class="kf-max-ticket-booking-note"ng-show="event.max_tickets_per_person &gt; 0"><?php _e("Note : Maximum seats allowed per booking - ", 'eventprime-event-calendar-management'); ?>{{event.max_tickets_per_person}}</div>
                                    <div class="kf-legends dbfl em_block ">
                                        <div class="kf-legend difl"><span class="kf-available kf-legend-box" ng-style="{'background': '#'+event.venue.seat_color}"></span><?php _e("Available", 'eventprime-event-calendar-management'); ?></div>
                                        <div class="kf-legend difl"><span class="kf-booked kf-legend-box" ng-style="{'background': '#'+event.venue.booked_seat_color}"></span><?php _e("Booked", 'eventprime-event-calendar-management'); ?></div>   
                                        <div class="kf-legend difl"><span class="kf-reserved kf-legend-box" ng-style="{'background': '#'+event.venue.reserved_seat_color}"></span><?php _e("Reserved", 'eventprime-event-calendar-management'); ?></div>  
                                        <div class="kf-legend difl"><span class="kf-selected kf-legend-box" ng-style="{'background': '#'+event.venue.selected_seat_color}"></span><?php _e("Selected", 'eventprime-event-calendar-management'); ?></div>
                                    </div>
                                </td>
                            </tr>
                            <tr ng-repeat="row in event.seats" class="row isles_row_spacer" id="row{{$index}}" ng-style="{'margin-top':row[0].rowMargin}" ng-init="trIndex=$index">
                                <td class="row_selection_bar" ng-click="selectRow($index)">
                                    <div class="em_seat_row_number">{{getRowAlphabet($index)}}</div>
                                </td>

                                <td ng-repeat="seat in row" ng-init="adjustContainerWidth(seat.columnMargin, $parent.$index)" class="seat isles_col_spacer {{seat.type}}" ng-class="{'em-seat-column-even':$index%2==0, 'em-seat-column-odd':$index%2==1}" id="ui{{$parent.$index}}-{{$index}}" ng-style="{'margin-left':seat.columnMargin, 'border':seat.seatBorderColor, 'border-bottom': 0}">
                                    
                                    <div ng-if="$index == 0 && event.row_wise_tier && event.row_wise_tier[trIndex]">
                                        {{event.row_wise_tier[trIndex]}}
                                    </div>

                                    <div ng-dblclick="selectColumn($index)" ng-if="$parent.$index == 0" class="em_seat_col_number">{{$index + 1}}</div>

                                    <div class="seat_avail seat_avail_number seat_status">{{seat.col + 1}}</div>
                                    <div id="pm_seat" class="ep-seat-booking-popover seat_avail seat_status em-seat-tooltip" ng-class="{'em-seat-last-seat':$last}" ng-click="selectSeat(seat, $parent.$index, $index, event.venue.seat_color, event.venue.selected_seat_color)" ng-style="{'background-color': seat.seatColor}">
                                        <div class="ep-seat-info-popover" ng-class="{'em-seat-last-popover':$last}">   
                                            <div class="ep-seat-booking-cat ep-seat-info-popover-arrow"></div>
                                            <div class="ep-seat-info-popover-row ep-seat-booking-cat" ng-if="seat.variation_id">{{em_all_variation_name[seat.variation_id]}}</div>
                                            <div class="ep-seat-info-popover-row">
                                                <?php esc_html_e('Seat index', 'eventprime-event-calendar-management');?> - 
                                                <strong> {{seat.seatSequence}}</strong>
                                            </div>
                                            <div style="display: none;">{{seatPrice = seat.price}}</div>
                                            <div class="ep-seat-info-popover-row" ng-if="seatPrice.indexOf('-') &gt; -1">
                                                <?php esc_html_e('Seat Price', 'eventprime-event-calendar-management');?> - 
                                                <strong> {{epStringSplit(seatPrice,0)}}<!-- {{seatPrice.split('-')[0]}} --></strong>
                                            </div>
                                            <div class="ep-seat-info-popover-row" ng-if="seatPrice.indexOf('-') &gt; -1">
                                                <span>
                                                    <?php esc_html_e('Special Price', 'eventprime-event-calendar-management');?> -  
                                                    <strong>{{epStringSplit(seatPrice,1)}}<!-- {{seatPrice.split('-')[1]}} --></strong>
                                                </span>
                                            </div>  
                                            <div class="ep-seat-info-popover-row" ng-if="seatPrice.indexOf('-') === -1">
                                                <?php esc_html_e('Seat Price', 'eventprime-event-calendar-management');?> - 
                                                <strong> {{seatPrice}}</strong>
                                            </div>
                                            <div class="ep-seat-info-popover-row" ng-if="!seatPrice.indexOf('-')">
                                                <?php esc_html_e('Seat Price', 'eventprime-event-calendar-management');?> - 
                                                <strong> {{seatPrice}}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                        <div class="em_booking_screen dbfl">
                            <?php echo em_global_settings_button_title('ALL EYES THIS WAY'); ?>
                        </div>
                    </div>
                    
                    <?php do_action('event_magic_guest_booking_user_info_block', 'seats'); ?>

                    <div class="ep-attendee-info-wrap difl" ng-if="attendee_names.length &gt; 0 && event.enable_attendees == 1">
                        <div class="ep-attendee-heading-text"><?php _e('Attendee Information','eventprime-event-calendar-management'); ?></div>                          
                        <ul class="ep-attendee-input" ng-if="attendee_names.length &gt; 0 && (!event.custom_booking_field_data || event.custom_booking_field_data.length == 0)">
                            <div class="ep-attendee-sub-heading dbfl"><?php _e('You can choose to proceed without adding Attendee names','eventprime-event-calendar-management'); ?></div>
                            <li ng-repeat="(i,v) in attendee_names track by $index">
                                <input type="text" id="em-attendee-name-input-{{i}}" ng-model="attendee_names[i]" placeholder="<?php _e('Attendee Name','eventprime-event-calendar-management'); ?>">
                                <span class="perror em-booking-seat-data" id="em-attendee-name-{{i}}"></span>
                            </li>
                        </ul>
                        <div class="ep-attendee-input" ng-if="attendee_names.length &gt; 0 && event.custom_booking_field_data.length &gt; 0">
                            <ul ng-repeat="(i,v) in attendee_names track by $index" class="em-custom-booking-fields-input">
                                <div class="em-booking-attendee-info-section" id="em_attendee_info_{{i+1}}">{{seatAttendeeInfo[i+1]}}</div>
                                <li ng-repeat="field in event.custom_booking_field_data track by $index" ng-if="field.type">
                                    <div ng-if="field.type == 'text'">
                                        <label for="ep-input-{{i}}-{{field.type}}">{{field.label}}</label>
                                        <input type="text" ng-model="attendee_names[i][$index][field.type][field.label].value" placeholder="{{field.label}}" id="ep-input-{{i}}-{{field.type}}">
                                        <span class="perror em-booking-seating-data" id="em-attendee-name-seating-{{i}}-{{field.type}}"></span>
                                    </div>
                                    <div ng-if="field.type == 'email'">
                                        <label for="ep-input-{{i}}-{{field.type}}">{{field.label}}</label>
                                        <input type="email" ng-model="attendee_names[i][$index][field.type][field.label].value" placeholder="{{field.label}}" id="ep-input-{{i}}-{{field.type}}">
                                        <span class="perror em-booking-seating-data" id="em-attendee-name-seating-{{i}}-{{field.type}}"></span>
                                    </div>
                                    <div ng-if="field.type == 'tel'">
                                        <label for="ep-input-{{i}}-{{field.type}}">{{field.label}}</label>
                                        <input type="tel" ng-model="attendee_names[i][$index][field.type][field.label].value" placeholder="{{field.label}}" id="ep-input-{{i}}-{{field.type}}">
                                        <span class="perror em-booking-seating-data" id="em-attendee-name-seating-{{i}}-{{field.type}}"></span>
                                    </div>
                                    <div ng-if="field.type == 'date'">
                                        <label for="ep-input-{{i}}-{{field.type}}">{{field.label}}</label>
                                        <input type="text" class="em-cbf-datepicker" ng-model="attendee_names[i][$index][field.type][field.label].value" placeholder="{{field.label}}" id="ep-input-{{i}}-{{field.type}}">
                                        <span class="perror em-booking-seating-data" id="em-attendee-name-seating-{{i}}-{{field.type}}"></span>
                                    </div>
                                </li>
                            </ul>   
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Standing-->
            <div class="kf-standing-type-popup kf-standing-type-popup-wrap" ng-show="event.venue.type == 'standings' || event.venue.type == ''">
                <div id="kf-seat-table-parent" class="em_block dbfl kf_config_pop " >
                    <div class="kf-seat-table-popup-head dbfl">
                        <div class="proceed_button_container em_event_row difr">
                            <button class="bg_gradient kf-button em_color" id="kf_update_cart" ng-show="update_cart" ng-disabled="requestInProgress" ng-click="updateOrder()"><?php _e("Update Cart", 'eventprime-event-calendar-management'); ?></button>
                            <button class="bg_gradient kf-button em_color" ng-click="display_cart(true)" ng-disabled="!(orders.length &gt; 0)" ng-show="(orders.length &gt; 0)"><?php _e("Show Cart", 'eventprime-event-calendar-management'); ?></button>
                        </div>

                        <div ng-show="event.venue.type == 'seats'" class="kf-popup-title"><?php _e('Please Select your seat(s) below', 'eventprime-event-calendar-management'); ?></div>
                        <div ng-show="event.venue.type == 'seats'" class="kf-popup-sub-title"><?php _e('You can only select from available seats. Once you are done, click proceed to checkout.', 'eventprime-event-calendar-management'); ?></div>
                        <div ng-show="event.venue.type == 'standings'" class="kf-popup-title">
                            <?php echo em_global_settings_button_title('Please enter number of tickets you wish to book'); ?>
                        </div>
                        <div ng-show="event.venue.type == 'standings'" class="kf-popup-sub-title"><?php _e('Once you are done, click proceed to checkout.', 'eventprime-event-calendar-management'); ?></div>
                    </div>

                    <div class="kf-standing-order ep-booking-row difl" >
                        <div class="ep-booking-quantity">
                            <input type="number" name="quantity" ng-model="booking_quantity" id="standing_order_quantity" />
                        </div>
                        <div  class="ep-booking-event-name">{{event.name}}</div>
                        <div class="ep-booking-event-price" ng-show="event.hide_0_price_from_frontend == 0 || event.ticket_price &gt; 0">
                            <span class="em-booking-page-price" ng-if="event.ticket_price_has_string == 1" ng-bind-html="event.ticket_price | unsafe"></span>

                            <span class="em-booking-page-price" ng-if="event.ticket_price_has_string == 0" ng-bind-html="event.ticket_price | currencyPositionWithHtml: event.currency_position : currency_symbol"></span>
                        </div>
                        <div class="ep-booking-event-button difr"> 
                            <button class="em_header_button" id="kf_standing_update" ng-if="event.available_standings == 0" ng-hide="update_cart" ng-disabled="requestInProgress">
                                <?php echo em_global_settings_button_title('All Seats Booked'); ?>
                            </button>
                        </div>
                        <div class="ep-booking-event-button difr">
                            <button class="em_header_button" id="kf_standing_update" ng-if="event.available_standings != 0" ng-hide="update_cart" 
                            ng-disabled="requestInProgress" ng-click="orderStandings()" 
                            data-no_seat="<?php _e('Please enter at least 1 booking to proceed ahead', 'eventprime-event-calendar-management');?>" 
                            data-available_standing="{{event.available_standings}}" data-max_booking_qty="<?php _e('Booking quantity is greater than available bookings', 'eventprime-event-calendar-management');?>">
                                <?php echo em_global_settings_button_title('Proceed'); ?>
                            </button>
                        </div>
                    </div>

                    <div class="ep-event-pricing-info-wrap dbfl" ng-if="event.price_option_data.length &gt; 1 && (event.venue.type == 'standings' || event.venue.type == '') && event.ticket_price &gt; 0">
                        <div class="ep-attendee-heading-text"><?php _e('Available Options (Select one)', 'eventprime-event-calendar-management'); ?></div>
                        <div class="ep-event-pricing-info dbfl">
                            <div class="ep-event-price-selector" ng-class="{'event-price-disabled':option.option_disabled &gt; 0}" ng-repeat="option in event.price_option_data track by $index">
                                <label class="ep-event-pricing-label" for="multi-price-option-{{$index}}">
                                    <input type="radio" name="event_multi_price" id="multi-price-option-{{$index}}" ng-value="option" ng-model="event_multi_price_option.val"  ng-click="updateMultiPriceOption(option)" ng-disabled="option.option_disabled" />
                                    <div class="ep-event-pricing-box">
                                        <div class="ep-event-pricing-box-wrap">
                                            <div class="ep-event-price-swatch-icon" ng-if="option.icon_image"><img loading="lazy" src="{{option.icon_image}}" alt="{{option.name}}" /></div>
                                            <div class="ep-event-pricing-details">
                                                <span class="ep-event-pricing-name" >{{option.name}}</span>
                                                <p class="ep-event-pricing-description" ng-bind-html="option.description | unsafe"></p>
                                                <div class="ep-event-booking-status" ng-if="option.capacity_progress_bar == 1">
                                                    <div>{{option.total_booking}} <?php _e('out of', 'eventprime-event-calendar-management'); ?> {{option.capacity}} <?php _e('sold', 'eventprime-event-calendar-management'); ?></div> 
                                                </div>
                                            </div>
                                            <div class="ep-event-price-wrap">
                                                <div class="em-price-option-price" ng-class="{'no-event-price':option.special_price &gt; 0}">
                                                    <div ng-if="option.special_price &gt; 0" class="ep-event-price-discount"><span class="ep-event-sale"><?php _e('Sale!', 'eventprime-event-calendar-management'); ?></span><div class="ep-event-sale-price">{{option.price | currencyPosition: event.currency_position : currency_symbol}}</div></div>
                                                    <div class="ep-event-price-spacial">{{option.price | currencyPosition: event.currency_position : currency_symbol}}</div>
                                                </div>
                                                <div class="em-price-option-special-price" ng-if="option.special_price &gt; 0">{{option.special_price | currencyPosition: event.currency_position : currency_symbol}}</div>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                    <?php do_action('event_magic_guest_booking_user_info_block', 'standings'); ?>
                    
                    <!-- Attendee Form -->
                    
                    <div class="ep-attendee-info-wrap difl" ng-if="attendee_names.length &gt; 0 && event.enable_attendees == 1">
                        <div class="ep-attendee-heading-text"><?php _e('Attendee Information','eventprime-event-calendar-management'); ?></div>
                        <ul class="ep-attendee-input" ng-if="attendee_names.length &gt; 0 && (!event.custom_booking_field_data || event.custom_booking_field_data.length == 0)">
                            <div class="ep-attendee-sub-heading dbfl"><?php _e('You can choose to proceed without adding Attendee names','eventprime-event-calendar-management'); ?></div>
                            <li ng-repeat="(i,v) in attendee_names track by $index">
                                <!-- <input type="text" ng-model="attendee_names[i]" placeholder="<?php _e('Attendee Name','eventprime-event-calendar-management'); ?>"> -->

                                <input type="text" id="em-attendee-name-input-standing-{{i}}" ng-model="attendee_names[i]" placeholder="<?php _e('Attendee Name','eventprime-event-calendar-management'); ?>">
                                <span class="perror em-booking-standing-data" id="em-attendee-name-standing-{{i}}"></span>
                            </li>   
                        </ul>
                        <div class="ep-attendee-input" ng-if="attendee_names.length &gt; 0 && event.custom_booking_field_data.length &gt; 0">
                            <ul ng-repeat="(i,v) in attendee_names track by $index" class="em-custom-booking-fields-input em-custom-booking-fields-input-standing">
                                <li ng-repeat="field in event.custom_booking_field_data track by $index" ng-if="field.type">
                                    <div ng-if="field.type == 'text'">
                                        <label for="ep-input-{{i}}-{{field.type}}" ng-show="field.label">{{field.label}}</label>
                                        <label for="ep-input-{{i}}-{{field.type}}" ng-show="!field.label">{{field.type}}</label>
                                        <span class="perror" ng-show="field.required == 1">*</span>
                                        <input type="text" ng-model="attendee_names[i][$index][field.type][field.label].value" placeholder="{{field.label}}" id="ep-standings-input-{{i}}-{{field.type}}">
                                        <span class="perror em-booking-standing-data" id="em-attendee-name-standing-{{i}}-{{field.type}}"></span>
                                    </div>
                                    <div ng-if="field.type == 'email'">
                                        <label for="ep-input-{{i}}-{{field.type}}" ng-show="field.label">{{field.label}}</label>
                                        <label for="ep-input-{{i}}-{{field.type}}" ng-show="!field.label">{{field.type}}</label>
                                        <span class="perror" ng-show="field.required == 1">*</span>
                                        <input type="email" ng-model="attendee_names[i][$index][field.type][field.label].value" placeholder="{{field.label}}" id="ep-standings-input-{{i}}-{{field.type}}">
                                        <span class="perror em-booking-standing-data" id="em-attendee-name-standing-{{i}}-{{field.type}}"></span>
                                    </div>
                                    <div ng-if="field.type == 'tel'">
                                        <label for="ep-input-{{i}}-{{field.type}}" ng-show="field.label">{{field.label}}</label>
                                        <label for="ep-input-{{i}}-{{field.type}}" ng-show="!field.label">{{field.type}}</label>
                                        <span class="perror" ng-show="field.required == 1">*</span>
                                        <input type="tel" ng-model="attendee_names[i][$index][field.type][field.label].value" placeholder="{{field.label}}" id="ep-standings-input-{{i}}-{{field.type}}">
                                        <span class="perror em-booking-standing-data" id="em-attendee-name-standing-{{i}}-{{field.type}}"></span>
                                    </div>
                                    <div ng-if="field.type == 'date'">
                                        <label for="ep-input-{{i}}-{{field.type}}" ng-show="field.label">{{field.label}}</label>
                                        <label for="ep-input-{{i}}-{{field.type}}" ng-show="!field.label">{{field.type}}</label>
                                        <span class="perror" ng-show="field.required == 1">*</span>
                                        <input type="text" class="em-cbf-datepicker" ng-model="attendee_names[i][$index][field.type][field.label].value" placeholder="{{field.label}}" id="ep-standings-input-{{i}}-{{field.type}}">
                                        <span class="perror em-booking-standing-data" id="em-attendee-name-standing-{{i}}-{{field.type}}"></span>
                                    </div>
                                </li>
                            </ul>   
                        </div>
                    </div>
                    
                    <!-- Attendee Form -->
                    <div id="show_popup_loader"></div>
                </div>
            </div>
            <div ng-show="event.venue.type == 'standings' || event.venue.type == 'seats' || event.venue.type == ''">
                <?php do_action('event_magic_additional_fields', $event); ?>
            </div>

            <!-- Proceed buttons at bottom -->
            <div class="ep-booking-summary-wrap dbfl" ng-show="event.venue.type == 'standings' || event.venue.type == ''">
                <div class="ep-booking-summary-head">
                    <div class="ep-booking-summary-title em_align_center">
                        <?php esc_html_e('Booking Summary', 'eventprime-event-calendar-management');?>
                    </div>
                </div>

                <div class="ep-booking-summary-row">
                    <div class="ep-seat-selector"><?php esc_html_e('Selected Tickets', 'eventprime-event-calendar-management'); ?>: {{booking_quantity}} </div>
                    <div class="ep-booked-ticket-price" ng-show="event.hide_0_price_from_frontend == 0 || event.ticket_price &gt; 0">
                        <?php esc_html_e('Price', 'eventprime-event-calendar-management'); ?>:
                        <span class="em-booking-page-price" ng-if="event.ticket_price_has_string == 1" ng-bind-html="event.ticket_price*booking_quantity | unsafe"></span>

                        <span class="em-booking-page-price" ng-if="event.ticket_price_has_string == 0" ng-bind-html="event.ticket_price*booking_quantity | currencyPositionWithHtml: event.currency_position : currency_symbol"></span>
                    </div>
                </div>

                <div class="ep-booking-summary-row ep-booking-summary-footer em_bg_lt dbfl">
                    <div></div>
                    <!-- Proceed buttons at bottom -->
                    <div class="proceed_button_container em_event_row difr">
                        <button class="bg_gradient kf-button em_color" id="kf_update_cart" ng-show="update_cart" ng-disabled="requestInProgress" ng-click="updateOrder()"><?php _e("Update Cart", 'eventprime-event-calendar-management'); ?></button>
                        <button class="bg_gradient kf-button em_color" ng-click="display_cart(true)" ng-disabled="!(orders.length &gt; 0)" ng-show="(orders.length &gt; 0)"><?php _e("Show Cart", 'eventprime-event-calendar-management'); ?></button>

                        <button class="em_header_button" id="kf_standing_update" ng-if="event.available_standings != 0" ng-hide="update_cart"
                        ng-disabled="requestInProgress" ng-click="orderStandings()" 
                        data-no_seat="<?php _e('Please enter at least 1 booking to proceed ahead', 'eventprime-event-calendar-management');?>" 
                        data-available_standing="{{event.available_standings}}" data-max_booking_qty="<?php _e('Booking quantity is greater than available bookings', 'eventprime-event-calendar-management');?>">
                            <?php echo em_global_settings_button_title('Proceed'); ?>
                        </button>
                    </div>
                </div>
            </div>

            <!--Booking Summary-->
            <div class="ep-booking-summary-wrap dbfl" ng-show="event.venue.type == 'seats'">
                <div class="ep-booking-summary-head">
                    <div class="ep-booking-summary-title em_align_center">
                        <?php esc_html_e('Booking Summary', 'eventprime-event-calendar-management');?>
                    </div>
                </div>
                <div class="ep-booking-summary-row">
                    <div class="ep-seat-selector"><?php esc_html_e('Selected Seats', 'eventprime-event-calendar-management'); ?>: {{selectedSeats.length}} </div>
                    <div class="ep-booked-ticket-price" ng-show="event.hide_0_price_from_frontend == 0 || event.ticket_price &gt; 0">
                        <?php esc_html_e('Price', 'eventprime-event-calendar-management'); ?>
                        <span class="em-booking-page-price">{{em_selected_seat_price | currencyPosition: event.currency_position : currency_symbol}}</span>
                    </div>
                </div>
                
                <div class="ep-booking-summary-row ep-booking-summary-footer em_bg_lt dbfl">
                    <div></div>
                    <!-- Proceed buttons at bottom -->
                    <div ng-show="event.venue.type == 'seats'">                    
                        <div class="proceed_button_container em_event_row difr">
                            <button  id="kf_seat_update" class="em_header_button" ng-hide="update_cart" ng-disabled="requestInProgress"  ng-click="orderSeats()" data-no_seat="<?php esc_html_e('No seats are selected', 'eventprime-event-calendar-management'); ?>"><?php echo em_global_settings_button_title('Proceed'); ?></button>
                            <button ng-disabled="requestInProgress" id="kf_update_cart" class="em_header_button kf-button em_color" ng-show="update_cart" ng-click="updateOrder()"><?php esc_html_e("Update Cart", 'eventprime-event-calendar-management'); ?></button>
                            <button class="em_header_button kf-button em_color" ng-click="display_cart(true)" ng-disabled="!(orders.length &gt; 0)" ng-show="(orders.length &gt; 0)"><?php esc_html_e("Show Cart", 'eventprime-event-calendar-management'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
            <!--Ends: Booking Summary-->
        </div> 

        {{pos}}  

        <div id="booking_summary" class="em-before-payment dbfl" ng-show="show_cart && orders.length &gt; 0"> 
            <div class="kf-before-payment-wrap dbfl">
                <div ng-if="event.venue.type == 'seats'">
                    <div id="div_id" class="em_booking_heading em_event_attr_box em_align_center"><?php _e("Confirm Booking Detail and Checkout", 'eventprime-event-calendar-management'); ?></div>
                    <div class="kf-payment-details em_floatfix dbfl">
                        <div class="ep-cart__item-list">
                            <div class="ep-cart-item ep-cart-item-head">
                                <div class="event-name"><?php esc_html_e('Seats', 'eventprime-event-calendar-management');?></div>
                                <div class="event-name"><?php esc_html_e('Price', 'eventprime-event-calendar-management');?></div>
                                <div class="event-name"><?php esc_html_e('Quantity', 'eventprime-event-calendar-management');?></div>
                                <div class="event-name"><?php esc_html_e('Subtotal', 'eventprime-event-calendar-management');?></div>
                            </div>
                            <div ng-repeat="tmp in order.order_item_data track by $index"  class="ep-cart-item" ng-if="tmp.event_id">
                                <div class="ep-event-name-details">
                                    <div class="ep-cart-item-name"> 
                                        <strong>{{tmp.variation_name}}</strong>
                                        <span class="em-booking-seat-no">
                                            <?php _e('Seat Nos.', 'eventprime-event-calendar-management'); ?>- {{tmp.seatNo}}
                                            <a ng-click="loadEventForBooking(tmp.event_id)" > 
                                                <?php _e('Change', 'eventprime-event-calendar-management'); ?>
                                            </a>
                                        </span>
                                    </div>
                                </div>
                                <div class="ep-cart-item-price em_single_price" ng-show="event.hide_0_price_from_frontend == 0 || event.ticket_price &gt; 0">
                                    <span class="em-booking-page-price">{{tmp.price | currencyPosition: event.currency_position : currency_symbol}}</span>
                                </div>
                                <div class="kf-booked-qty">
                                    {{tmp.quantity}}
                                </div>
                                <div class="ep-cart-item-price em_sub_total_price" ng-show="event.hide_0_price_from_frontend == 0 || event.ticket_price &gt; 0">
                                    <span class="em-booking-page-price">{{tmp.sub_total | currencyPosition: event.currency_position : currency_symbol}}</span>
                                </div>
                            </div>
                            <?php do_action('event_magic_front_checkout_data_view'); ?>
                        </div>
                    </div>
                </div>
                <div ng-if="event.venue.id == 0 || event.venue.id == null || event.venue.type == 'standings'">
                    <div class="em_booking_heading em_event_attr_box em_align_center"><?php _e("Confirm Booking Detail and Checkout", 'eventprime-event-calendar-management'); ?></div>
                    <div class="kf-payment-details em_floatfix dbfl">
                        <div class="ep-cart__item-list">
                            <div class="ep-cart-item ep-cart-item-head">
                                <div class="event-name"><?php esc_html_e('Event', 'eventprime-event-calendar-management');?></div>
                                <div class="event-name"><?php esc_html_e('Price', 'eventprime-event-calendar-management');?></div>
                                <div class="event-name"><?php esc_html_e('Quantity', 'eventprime-event-calendar-management');?></div>
                                <div class="event-name"><?php esc_html_e('Subtotal', 'eventprime-event-calendar-management');?></div>
                            </div> 
                            <div ng-repeat="tmp in order.order_item_data track by $index" class="ep-cart-item" ng-if="tmp.event_id">
                                <div class="ep-event-name-details">
                                    <div class="ep-cart-item-name"><strong>{{event.name}}</strong></div>
                                    <div class="ep-cart-item-vanue"> <?php _e('On', 'eventprime-event-calendar-management'); ?> {{event.start_date}} <?php _e('at', 'eventprime-event-calendar-management'); ?> <span class="kf-booking-event-vanue">{{event.venue.name}}</span></div>
                                </div>

                                <div class="ep-cart-item-price em_total_price">
                                    <span class="em-booking-page-price">{{tmp.price | currencyPosition: event.currency_position : currency_symbol}}</span>
                                </div>
                                
                                <div class="kf-booked-qty">{{tmp.quantity}} <?php _e('Tickets', 'eventprime-event-calendar-management'); ?> <a ng-click="loadEventForBooking(tmp.event_id)" > <?php _e('Change', 'eventprime-event-calendar-management'); ?> </a></div>

                                <div class="ep-cart-item-price em_sub_total_price">
                                    <span class="em-booking-page-price">{{tmp.sub_total | currencyPosition: event.currency_position : currency_symbol}}</span>
                                    <span ng-if="tmp.variation_name"> ({{tmp.variation_name}})</span>
                                </div>
                            </div>
                            <?php do_action('event_magic_front_checkout_data_view'); ?>
                        </div>
                    </div>
                </div>  
                <!--  0 Payment Proceed Button -->
                <div class="payment_notice dbfl" ng-show="price &gt; 0 && bookable">
                    <div class="kf-payment-info dbfl"><?php _e("Your seats are on hold. You will need to checkout within <a><span id='em_payment_timer'></span></a> minutes to reserve them. Otherwise they will be released for booking.", 'eventprime-event-calendar-management'); ?></div>
                    <div class="em_payment_progress_wrap dbfl">
                        <div class="em_payment_progress em_bg" id="em_payment_progress"></div>
                    </div>
                </div>  
                <?php //do_action('event_magic_front_checkout_data_view', $event_id); ?>
                
                <div class="kf-checkout-footer dbfl">
                    <div class="ep-billing-details-wrap">
                        <div class="ep-billing-coupons">
                            <!-- coupon code section -->
                            <?php
                            if(in_array('coupons', $extensions)){ 
                                do_action( 'event_magic_front_coupon_section' );
                            }?>
                        
                        </div>
                        <div class="ep-billing-details">
                            <div class="ep-ep-billing-amount-info"></div>
                            <div class="em-checkout-booking-title">
                                <?php esc_html_e( 'Booking Details', 'eventprime-event-calendar-management' );?>
                            </div>
                            <div class="em-checkout-subtotal ep-billing-row">
                                <div class="em_subtotal ep-billing-info-col">
                                    <?php _e("Subtotal", 'eventprime-event-calendar-management'); ?> -&nbsp;
                                </div>
                                <div class="ep-billing-info-col">
                                    <strong>
                                        <span>{{orders[0].subtotal | currencyPosition: event.currency_position : currency_symbol}}</span>
                                    </strong>
                                </div>
                            </div>
                            
                            <div class="ep-billing-coupons ep-billing-row" ng-show="discount &gt; 0">
                        
                                <div class="ep-billing-info-col"><?php _e("Discount", 'eventprime-event-calendar-management'); ?></span> -&nbsp;</div>
                                <div class="ep-billing-info-col">{{discount | currencyPosition: event.currency_position : currency_symbol}}</div>
                                
                            </div>
                            
                            <div class="em_one_time_fee_row ep-billing-row " ng-show="fixed_event_price &gt; 0">
                                <div class="em_one_time_fee-label ep-billing-info-col">
                                    <?php _e("One-Time event Fee", 'eventprime-event-calendar-management'); ?> -&nbsp;
                                </div>
                                <div class="em_one_time_fee-amount ep-billing-info-col">
                                    <strong>
                                        <span>{{fixed_event_price | currencyPosition: event.currency_position : currency_symbol}}</span>
                                    </strong>
                                </div>
                            </div>

                            <!-- Early bird discount section starts -->
                            <?php
                            if(in_array('em_automatic_discounts', $extensions)){ 
                                do_action( 'event_magic_front_ebd_section' );
                            }?>

                            <!-- if coupon code applied -->
                            <div class="ep-coupon-code-payment-row ep-billing-row" ng-if="couponCodeApplied &gt; 0">
                                <div class="ep-coupon-code-payment-label ep-billing-info-col"><?php echo __("Coupon Code Discount", 'eventprime-event-coupons'); ?> -&nbsp;</div> 
                                <div class="ep-coupon-code-amount ep-billing-info-col">
                                    <strong>
                                        <span ng-if="event.currency_position == 'before'"><?php echo em_currency_symbol();?>{{coupon_code_discount_amount}}</span>
                                        <span ng-if="event.currency_position == 'before_space'"><?php echo em_currency_symbol();?> {{coupon_code_discount_amount}}</span>
                                        <span ng-if="event.currency_position == 'after'">{{coupon_code_discount_amount}}<?php echo em_currency_symbol();?></span>
                                        <span ng-if="event.currency_position == 'after_space'">{{coupon_code_discount_amount}} <?php echo em_currency_symbol();?></span>
                                    </strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ep-final-due-amount" ng-show="event.hide_0_price_from_frontend == 0 || event.ticket_price &gt; 0">
                        <div class="em_subtotal "><?php _e("Total Due", 'eventprime-event-calendar-management'); ?> -&nbsp;
                            <strong>
                                <span>{{price | currencyPosition: event.currency_position : currency_symbol}}</span>
                            </strong>
                        </div>
                    </div>
                    
                    <!-- Paypal Proceed Button -->
                    <div class="ep-payment-integration dbfl">
                        <div class="ep-payment_prcoessors dbfl">
                            <div class="ep-payment-checkout-wrap">
                           
                                <?php do_action('event_magic_front_payment_processors'); ?>
                             
                            </div>
                            <div class="ep-payment-checkout-btn em_bg_lt dbfl" ng-show="price == 0 || data.payment_processor == 'paypal'">
                                <div class="em_checkout_btn difr" ng-show="price == 0 && bookable">
                                    <button  class="ep-payment-button" ng-click="proceedWithoutPayment()"><?php echo em_global_settings_button_title('Checkout'); ?></button>
                                </div>
                                <div  class="em_checkout_btn difr " ng-show="data.enable_modern_paypal==0 && data.payment_processor == 'paypal' && price &gt; 0 && bookable">
                                    <button ng-disabled="requestInProgress" class="ep-payment-button" ng-click="proceedToPaypal()"><?php echo em_global_settings_button_title('Checkout'); ?></button>
                                </div>
                                <div  class="em_checkout_btn difr " ng-show="data.enable_modern_paypal==1 && data.payment_processor == 'paypal' && price &gt; 0 && bookable">
                                    <div id="paypal-button-container" class="ep-paypal-wrap"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            
            <?php do_action('event_magic_guest_booking_user_info_block_view'); ?>
            <div class="dbfl ep-attendees-info" ng-if="attendee_names.length &gt; 0 && event.enable_attendees == 1">
                <div class="ep-attendees-heading-text dbfl"><?php _e('Attendee Information', 'eventprime-event-calendar-management'); ?></div>
                <div class="dbfl ep-attendees-list" ng-if="is_custom_booking_field == 0">
                    <ol class="ep-attendee-display">
                        <li ng-repeat="(i,v) in attendee_names track by $index">
                            {{attendee_names[i]}}
                        </li>   
                    </ol>
                </div>
                <div class="dbfl ep-cbf-attendee-wrap" ng-if="is_custom_booking_field == 1">
                    <div class="ep-cbf-attendee-display" ng-repeat="attendees in attendee_names">
                        <div class="ep-attendee-info-head" ng-click="showAttendeeInfoBlock($index)">
                            <span class="ep-attendee-title">Attendee {{$index + 1}}</span>
                            <span class="material-icons ep-attendee-toggle-{{$index}}"> add </span>
                        </div>
                        <ul class="ep-attendee-info-content" id="attendee-info-block-{{$index}}" style="display: none">
                            <li ng-repeat="(key, value) in attendees">
                                <span ng-repeat="(akey, avalue) in value">
                                    <div ng-repeat="(ind, label) in avalue">
                                        <label ng-show="ind">{{ind}}</label>
                                        <label ng-show="!ind">{{key}}</label>
                                        <span class="ep-cbf-attendee-value">{{label.value}}</span>
                                    </div>
                                </span>
                            </li> 
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="dbfl em_block kf-notice-print-tickets"><?php //_e("Note: You will be able to print your tickets after the checkout.", 'eventprime-event-calendar-management'); ?></div>
            <div class="dbfl em_block">     
                <a href="<?php echo em_get_single_event_page_url($event, $global_settings);?>"><?php _e("Go to Event", 'eventprime-event-calendar-management'); ?></a>
            </div>

            <?php do_action('event_magic_front_payment_forms',$event); ?> 
        </div>
        <div id="kf-reconfirm-popup" class="dbfl" ng-show="!bookable" ng-cloak>
            <div class="dbfl em_block kf-reconfirm-popup-content">
                <p><?php _e('Sorry, the time window for checkout has expired. Selected seats have been released for bookings. You can click <a href="'.em_get_single_event_page_url($event, $global_settings).'">here</a> to go back to the Event page and try booking again.', 'eventprime-event-calendar-management'); ?></p>
                <a href="<?php echo em_get_single_event_page_url($event, $global_settings); ?>"><?php _e('OK', 'eventprime-event-calendar-management'); ?></a>
            </div>
        </div>
        <div id="kf-reconfirm-popup" class="dbfl" ng-show="errorMsg" ng-cloak>
            <div class="dbfl em_block kf-reconfirm-popup-content">
                <span  class="kf-reconfirm-popup-close" ng-click="errorMsg=''">&times;</span>
                <div>{{errorMsg}}</div>
            </div>
        </div>
    </div>
</div>    
<script>
    document.addEventListener("DOMContentLoaded", function(event) { 
        $ = jQuery;
        $(".kf-reconfirm-pc").click(function(){
            $("#kf-reconfirm-popup").hide();
        });
        
        var radioColor =  $('.emagic, #primary.content-area .entry-content').find('a').css('color');
        ///var bgg = $(background: linear-gradient(90deg, rgba(2,0,36,1) 0%, rgba(255,255,255,1) 100%));
        var newColor = radioColor.replace('rgb', 'rgba').replace(')', ',.2)'); //rgba(100,100,100,.8)
        var boxBorderColor = radioColor.replace('rgb', 'rgba').replace(')', ',.5)');
        var rgbaTwo = "rgba(255,255,255,1)";
           
        var rgbaCol = 'rgba(' + parseInt(newColor.slice(-6,-4),16)
        + ',' + parseInt(newColor.slice(-4,-2),16)
        + ',' + parseInt(newColor.slice(-2),16)
        ')';
    
        $('.ep-event-price-selector label').css({
          'background' : 'linear-gradient(to right,' + newColor + ', ' + rgbaTwo + ')'
        });
        $('.ep-event-price-selector label').css({
          'border-color' : boxBorderColor
        });
        
        $( ".kf-payment-mode-select" ).wrapAll( "<div class='ep-payment-selector'></div>" );
    });
    
 
</script>