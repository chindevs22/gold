<?php do_action('event_magic_admin_promotion_banner'); ?>
<div class="kikfyre kf-container"  ng-controller="priceManagerCtrl" ng-app="eventMagicApp" ng-cloak ng-init="initialize('event_add_price_manager')">
    <div class="kf_progress_screen" ng-show="requestInProgress"></div>
    <div class="kf-db-content">
        <div class="kf-title" ng-if="price_option.option_id" ng-hide="requestInProgress">
            <?php esc_html_e('Edit Price Option', 'eventprime-event-calendar-management'); ?>
        </div>
        <div class="kf-title" ng-if="!price_option.option_id" ng-hide="requestInProgress">
            <?php esc_html_e('Add New Price Option', 'eventprime-event-calendar-management'); ?>
        </div>
        <div class="form_errors">
            <ul>
                <li class="emfield_error" ng-repeat="error in  formErrors">
                    <span>{{error}}</span>
                </li>
            </ul>  
        </div>
        <!-- FORM -->
        <form name="addPriceManagerForm" class="em-add-price-manager" ng-submit="savePriceOption(addPriceManagerForm.$valid)" novalidate>
            <input type="text" ng-model="price_option.event_id" style="display: none;">
            <input type="text" ng-model="price_option.option_id" style="display: none;">
            <div class="emrow">
                <div class="emfield"><?php esc_html_e('Name', 'eventprime-event-calendar-management'); ?><sup>*</sup></div>
                <div class="eminput">
                    <input type="text" ng-model="price_option.name" placeholder="<?php esc_html_e('Name', 'eventprime-event-calendar-management'); ?>" id="em-pm-name-input" ng-required="true">
                    <div class="emfield_error">
                        <span ng-show="addPriceManagerForm.price_option.name.$error.required && !addPriceManagerForm.name.$pristine"><?php esc_html_e('This is a required field.', 'eventprime-event-calendar-management'); ?></span>
                    </div>
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php esc_html_e('Name of this price tier. If your event will have a single tier, you can name it ‘default’ etc. Name will be visible to your users on booking selection page.', 'eventprime-event-calendar-management'); ?>
                </div>
            </div>
            <div class="emrow">
                <div class="emfield"><?php esc_html_e('Description', 'eventprime-event-calendar-management'); ?></div>
                <div class="eminput" ng-model="price_option.description">
                    <?php
                    $description = '';
                    $settings = array(
                        'wpautop' => true,
                        'media_buttons' => false,
                        'textarea_name' => 'description',
                        'textarea_rows' => 20,
                        'tabindex' => '',
                        'tabfocus_elements' => ':prev,:next', 
                        'editor_css' => '', 
                        'editor_class' => '',
                        'teeny' => false,
                        'dfw' => false,
                        'tinymce' => true,
                        'quicktags' => true
                    );
                    //$post_id = event_m_get_param('post_id');
                    if(!empty($option_id)){
                        global $wpdb;
                        $table_name = $wpdb->prefix.'em_price_options';
                        $get_price_data = $wpdb->get_row( "SELECT description FROM $table_name WHERE id = $option_id" );
                        if(!empty($get_price_data)){
                            $description = html_entity_decode($get_price_data->description);
                        }
                    }
                    wp_editor( $description, 'description',$settings); ?>
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php esc_html_e('Optional. Description of this price tier. Description will be visible to your users on booking selection page.', 'eventprime-event-calendar-management'); ?>
                </div>
            </div>
            <div class="emrow">
                <div class="emfield"><?php _e('Icon','eventprime-event-calendar-management'); ?></div>
                <div class="eminput">
                    <input class="kf-upload" type="button" ng-click="mediaUploader(false)" value="<?php _e('Upload','eventprime-event-calendar-management'); ?>" />
                    <div class="em_cover_image em_gallery_images">
                     <img ng-src="{{price_option.icon_image}}" />
                    </div>
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php _e('Optional. You can add an icon which will appear beside tier name and description.','eventprime-event-calendar-management'); ?>
                </div>
                <input type="text" class="hidden" ng-model="term.image_id" />
            </div>
            <div class="emrow">
                <div class="emfield"><?php esc_html_e('Available Starting', 'eventprime-event-calendar-management'); ?></div>
                <div class="eminput">
                    <input type="text" ng-model="price_option.start_date" placeholder="<?php esc_html_e('Available Starting', 'eventprime-event-calendar-management'); ?>" id="em-pm-start-date-input" autocomplete="off">
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php esc_html_e('Optional. This price tier will be available for users after this date. Leave it blank for it to be available as soon as event booking starts.', 'eventprime-event-calendar-management'); ?>
                </div>
            </div>
            <div class="emrow">
                <div class="emfield"><?php esc_html_e('Available Till', 'eventprime-event-calendar-management'); ?></div>
                <div class="eminput">
                    <input type="text" ng-model="price_option.end_date" placeholder="<?php esc_html_e('Available Till', 'eventprime-event-calendar-management'); ?>" id="em-pm-end-date-input"  autocomplete="off">
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php esc_html_e('Optional. This price option will no longer be available for users after this date. You can use this to offer limited time discounts etc. Leave it blank for it to be available until the event booking closes..', 'eventprime-event-calendar-management'); ?>
                </div>
            </div>
            <div class="emrow">
                <div class="emfield"><?php esc_html_e('Tier Price', 'eventprime-event-calendar-management'); ?><sup>*</sup></div>
                <div class="eminput">
                    <input type="number" min="0" ng-model="price_option.price" placeholder="<?php esc_html_e('Tier Price', 'eventprime-event-calendar-management'); ?>" id="em-pm-price-input" ng-required="true">
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php esc_html_e('Default booking price, per attendee.', 'eventprime-event-calendar-management'); ?>
                </div>
            </div>
            <div class="emrow">
                <div class="emfield"><?php esc_html_e('Discounted Price', 'eventprime-event-calendar-management'); ?></div>
                <div class="eminput">
                    <input type="number" min="0" ng-model="price_option.special_price" placeholder="<?php esc_html_e('Discounted Price', 'eventprime-event-calendar-management'); ?>" id="em-pm-special-price-input">
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php esc_html_e('If wish to offer discount on the default booking price set above, you can mention the final discounted price here. Discounted prices are highlighted on the frontend, to grab user attention.', 'eventprime-event-calendar-management'); ?>
                </div>
            </div>
            <div class="emrow">
                <div class="emfield"><?php esc_html_e('Capacity', 'eventprime-event-calendar-management'); ?><sup>*</sup></div>
                <div class="eminput">
                    <input type="number" min="1" ng-model="price_option.capacity" placeholder="<?php esc_html_e('Capacity', 'eventprime-event-calendar-management'); ?>" id="em-pm-capacity-input" ng-required="true">
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php esc_html_e('Limit the maximum number of bookings for this price option. Useful when you have limited capacity for specific price options like front-row seats etc.', 'eventprime-event-calendar-management'); ?>
                </div>
            </div>
            <div class="emrow" ng-show="data.venue.type == 'seats'">
                <div class="emfield">
                <?php esc_html_e('Included Seats', 'eventprime-event-calendar-management'); ?>
                </div>
                <div class="eminput">
                    <button  type="button" class="ep-seat-selector" ng-disabled="requestInProgress">
                        <?php esc_html_e('Select Seats', 'eventprime-event-calendar-management'); ?>
                    </button>
                    <div class="ep-seat-selected-show" ng-show="selectedSeats.length > 0">{{selectedSeats.length}} <?php esc_html_e(' Seats Selected', 'eventprime-event-calendar-management'); ?></div>
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php esc_html_e('Define the seats from your venue seating layout which are included in this price tier. For example, front row seats can have different pricing compared to back row seats.', 'eventprime-event-calendar-management'); ?>
                </div>
                
                <div class="em_modal_wrapper ep-seats-details-modal ep-modal-box-main {{em_model_length_class}}" id="em_seats_details_modal" style="display: none;">
                    <div class="ep-modal-box-overlay ep-modal-box-overlay-fade-in"></div>
                    <div class="ep-modal-box-wrap ep-modal-box-out">
                        <div class="ep-modal-box-header">
                            <div class="ep-popup-title"></div>
                            <span class="ep-modal-box-close">×</span>
                        </div>
                  
                        <div class="emrow">
                            <div class="emfield"><?php _e('Seat Icon Color','eventprime-event-calendar-management'); ?></div>
                            <div class="eminput">
                                <input class="jscolor" id="em_color_picker" type="text" name="variation_color" ng-model="price_option.variation_color" ng-change="variation_color_change()" >
                                <div class="emfield_error"></div>
                            </div>
                            <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                                <?php _e('Differently priced seats can be highlighted using different icon colors. On the frontend, users will be able to see the color coded seating plan along with color code legend, while they are asked to select their seats.','eventprime-event-calendar-management'); ?>
                            </div>
                        </div>
                        <div class="ep-seat-selector-layout-main" >
                            <div class="em-seat-selected" ng-show="selectedSeats.length > 0"><span><strong>{{selectedSeats.length}}</strong> <?php _e('seats selected.','eventprime-event-calendar-management'); ?></span></div>
                            <div class="ep-seat-layout-wrap">
                            <table class="em_venue_seating" ng-style="seat_container_width" id="seat_selectable">
                                <tr ng-repeat="row in data.event.seats" class="row isles_row_spacer" id="row{{$index}}" ng-style="{'margin-top':row[0].rowMargin}">
                                    <td class="row_selection_bar" ng-click="selectRow($index)">
                                        <div class="em_seat_row_number">{{getRowAlphabet($index)}}</div>
                                    </td>
                                    <td ng-repeat="seat in row" ng-init="adjustContainerWidth(seat.columnMargin, $parent.$index)" class="seat isles_col_spacer" ng-class="seat.type" id="ui{{$parent.$index}}-{{$index}}" ng-style="{'margin-left':seat.columnMargin, 'border':seat.seatBorderColor, 'border-bottom': 0}">
                                        <div ng-click="selectColumn($index)" ng-if="$parent.$index == 0" class="em_seat_col_number">{{$index + 1}}</div>
                                        <div class="seat_avail seat_avail_number seat_status">{{seat.col + 1}}</div>
                                        <div  id="pm_seat"  class="seat_avail seat_status" ng-click="selectSeat(seat, $parent.$index, $index, data.venue.seat_color, data.venue.selected_seat_color)" ng-click="showSeatOptions(seat)" ng-style="{'background-color': seat.seatColor}">{{seat.uniqueIndex}} </div>
                                    </td>
                                </tr>
                            </table>
                            </div>
                            <div class="em-seat-selected em-seat-selected-bottom" ng-show="selectedSeats.length > 0"><span><strong>{{selectedSeats.length}}</strong> <?php _e('seats selected.','eventprime-event-calendar-management'); ?></span></div>
                            <div class="ep-seat-selector-footer dbfl">
                                <a href=""class="btn btn-primary ep-seat-selection-save-btn"><?php _e('Done','eventprime-event-calendar-management'); ?></a>
                                <!-- <a href="#" class="btn btn-primary ep-seat-selection-reset-btn">Reset</a> -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="emrow">
                <div class="emfield"><?php esc_html_e('Selected by Default', 'eventprime-event-calendar-management'); ?></div>
                <div class="eminput">
                    <input type="checkbox" ng-model="price_option.is_default" placeholder="<?php esc_html_e('Selected by Default', 'eventprime-event-calendar-management'); ?>" id="em-pm-set-default-input" ng-true-value="1" ng-false-value="0">
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php esc_html_e('Enable to show this price option as selected when user reaches booking page.', 'eventprime-event-calendar-management'); ?>
                </div>
            </div>
            <div class="emrow">
                <div class="emfield"><?php esc_html_e('Priority', 'eventprime-event-calendar-management'); ?></div>
                <div class="eminput">
                    <input type="number" min="1" ng-model="price_option.priority" placeholder="<?php esc_html_e('Priority', 'eventprime-event-calendar-management'); ?>" id="em-pm-priority-input">
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php esc_html_e('Set the order of this price option on the booking page. Not required if the event will have only single price option.', 'eventprime-event-calendar-management'); ?>
                </div>
            </div>
            <div class="emrow">
                <div class="emfield"><?php esc_html_e('Show Capacity Progress', 'eventprime-event-calendar-management'); ?></div>
                <div class="eminput">
                    <input type="checkbox" ng-model="price_option.capacity_progress_bar" placeholder="<?php esc_html_e('Show Capacity Progress', 'eventprime-event-calendar-management'); ?>" id="em-pm-set-default-input" ng-true-value="1" ng-false-value="0">
                </div>
                <div class="emnote"><i class="fa fa-info-circle" aria-hidden="true"></i>
                    <?php esc_html_e('If you have set capacity limit above, enabling this option will show user how many seats are left.', 'eventprime-event-calendar-management'); ?>
                </div>
            </div>

            <div class="dbfl kf-buttonarea">
                <div class="em_cancel"><a class="kf-cancel" href="<?php echo admin_url('admin.php?page=em_dashboard&tab=price_manager&post_id='.$post_id); ?>"><?php _e('Cancel','eventprime-event-coupons'); ?></a></div>
                <button type="submit" class="btn btn-primary" ng-disabled="addPriceManagerForm.$invalid || requestInProgress"><?php esc_html_e('Save', 'eventprime-event-calendar-management'); ?></button>
                <span class="kf-error" ng-show="addPriceManagerForm.$invalid && addPriceManagerForm.$dirty"><?php esc_html_e('Please fill all required fields.', 'eventprime-event-calendar-management'); ?></span>
            </div>

            <div class="dbfl kf-required-errors" ng-show="addPriceManagerForm.$invalid && addPriceManagerForm.$dirty">
                <h3><?php esc_html_e("Looks like you missed out filling some required fields (*). You will not be able to save until all required fields are filled in. Here’s what's missing", 'eventprime-event-calendar-management'); ?> -
                    <span ng-show="addPriceManagerForm.price_option.name.$error.required"><?php esc_html_e('Name', 'eventprime-event-calendar-management'); ?></span>
                </h3>
            </div>
            <div class="form_errors">
                <ul>
                    <li class="emfield_error" ng-repeat="error in  formErrors">
                        <span>{{error}}</span>
                    </li>
                </ul>  
            </div>
        </form>
    </div>
</div>
<script type="text/javascript">
    /*jQuery(document).ready(function(){
        console.log(jQuery("#seat_selectable").length);
        if(jQuery("#seat_selectable").length > 0){
            jQuery("#seat_selectable").selectable({
                filter: "td",
                selecting: function(event, ui) {
                    console.log(event);   
                    console.log(ui);   
                }
            });
        }
    });*/
    
    
    jQuery(document).ready(function(){
        jQuery(".ep-seat-selector").click(function(){
            jQuery("#em_seats_details_modal").toggle();
        });
        jQuery('button.ep-seat-selector').click(function () {
        jQuery('.ep-modal-box-main').show();
        jQuery('.ep-modal-box-wrap').removeClass('ep-modal-box-out');
        jQuery('.ep-modal-box-wrap').addClass('ep-modal-box-in');
    });
    
     jQuery('a.ep-seat-selection-save-btn').click(function () {
        jQuery('.ep-modal-box-wrap').removeClass('ep-modal-box-in');
        jQuery('.ep-modal-box-wrap').addClass('ep-modal-box-out'); 
          jQuery("#em_seats_details_modal").toggle();
    });
    
    
       
    }); 
</script>
