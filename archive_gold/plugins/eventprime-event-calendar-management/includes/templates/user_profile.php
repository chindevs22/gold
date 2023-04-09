<?php
wp_enqueue_script('em-public');
wp_enqueue_script('em-ctabs');
wp_enqueue_style('em-public-css');
wp_enqueue_style('em-ctabs-css');
em_localize_map_info('em-google-map');

$event_service = EventM_Factory::get_service('EventM_Service');
$setting_service = EventM_Factory::get_service('EventM_Setting_Service');
$global_settings = $setting_service->load_model_from_db();
$class = $section_id = $sec_unique = '';
if(isset($atts['section_id'])){
    $section_id = $atts['section_id'];
    $sec_unique = 'section-performers-'.$section_id;
    $page = false;
}
if(isset($atts['class'])){
    $class = $atts['class'];
}
if (!is_user_logged_in()) {
    include_once('user_registration.php');
} else {
    // Get current user details
    $user = wp_get_current_user();

    // Get booking details
    $booking_service = EventM_Factory::get_service('EventM_Booking_Service');
    $bookings = $booking_service->get_bookings_by_user($user->ID);
    $venue_service = EventM_Factory::get_service('EventM_Venue_Service');
    $send_ticket_opr = em_global_settings('send_ticket_on_payment_received');
    $custom_note_tpa = em_global_settings('custom_note_ticket_print_area');
    // Add thickbox library for details pop up
    add_thickbox();

    $i = 0;
    $booking_ids = event_m_get_param('em_bookings');
    $booking_ids = empty($booking_ids) ? '' : explode(',', $booking_ids);
    if (!empty($booking_ids)):
        ?>
        <div class="emagic <?php echo $class;?>" id="<?php echo $sec_unique;?>">
            <div class="kf-go-to-profile-page difl"><a href="<?php echo get_permalink(em_global_settings('profile_page')); ?>"><?php _e('Go to User Profile Page', 'eventprime-event-calendar-management'); ?></a></div><br>
            <?php
            foreach ($booking_ids as $b_id):
                $booking = $booking_service->load_model_from_db($b_id);
                if (empty($booking->id)) {
                    printf(__('No such booking exists for Booking ID #%d', 'eventprime-event-calendar-management'),$b_id);
                    continue;
                }
                if ($booking->user != $user->ID) continue; ?>  
                <div class="kf-booking-confirmation dbfl">
                    <div class="kf-booking-confirmation-notice">
                        <?php
                        if ($booking->status == 'pending') {
                            _e('Booking is Pending', 'eventprime-event-calendar-management');
                        } elseif ($booking->status == 'completed') {
                            _e('Congratulations, your booking has been confirmed!', 'eventprime-event-calendar-management');
                        } elseif ($booking->status == 'cancelled') {
                            _e('Your booking has been cancelled!', 'eventprime-event-calendar-management');
                        }
                        ?>
                    </div>
                    <?php if( isset($booking->order_info['payment_gateway']) && $booking->order_info['payment_gateway'] == 'offline' && $send_ticket_opr == 1 && isset($custom_note_tpa) && !empty($custom_note_tpa)){?>
                    <div><?php printf( __( '%s', 'eventprime-event-calendar-management' ), $custom_note_tpa );?></div>
                    <?php } ?>    
                    <div class="kf-booked-event-details em_block dbfl">
                        <div class="kf-booked-event-cover difl">
                            <?php
                            $event = $event_service->load_model_from_db($booking->event);
                            if (!empty($event->cover_image_id)) {
                                echo get_the_post_thumbnail($event->id, 'full');
                            } else {
                                ?>
                                <img height="150" width="150" src="<?php echo esc_url(plugins_url('/images/dummy_image_thumbnail.png', __FILE__)) ?>" alt="<?php _e('Dummy Image', 'eventprime-event-calendar-management'); ?>" >
                            <?php }
                            $booking_page_url = get_permalink(em_global_settings('booking_details_page'));
                            $booking_page_url = add_query_arg('id', $booking->id, $booking_page_url);
                            ?>
                            <div class="kf-booked-event-print em_bg dbfl em_block">
                                <a href="<?php echo $booking_page_url; ?>" class="details bg_gradient bg_grad_button" target="__blank">
                                    <?php echo em_global_settings_button_title('View Details'); ?>
                                </a>
                            </div>
                        </div>
                        <div class="kf-booked-event-details-wrap difl">
                            <div class="kf-booked-event-name"> <?php echo $event->name; ?></div>
                            <div class="kf-booked-event-date em_color"> <?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $event->start_date); ?></div>

                            <!--   Add to Calendar-->
                            <?php if (!empty($global_settings->gcal_sharing)): ?>
                                <div id="add-to-google-calendar">
                                    <span><label><input type="text" id="event_<?php echo $event->id; ?>"  style="display: none" value="<?php echo $event->name; ?>"></label></span>
                                    <span><label><input type="text" id="s_date_<?php echo $event->id; ?>"  style="display: none"value="<?php echo em_showDateTime($event->start_date, true); ?>" ></label></span>
                                    <span><label><input type="text" id="e_date_<?php echo $event->id; ?>"  style="display: none" value="<?php echo em_showDateTime($event->end_date, true); ?>" ></label></span>
                                    <div onclick="em_gcal_handle_auth_click()" id="authorize-button" class="kf-event-add-calendar em_color dbfl" style="display: none;">
                                        <img class="kf-google-calendar-add" src="<?php echo esc_url(plugins_url('/images/gcal.png', __FILE__)); ?>"/>
                                        <a class="kf-add-calendar"><?php _e('Add To Calendar', 'eventprime-event-calendar-management'); ?></a>
                                    </div>
                                    <?php if(current_time('timestamp')<$event->start_date): ?>
                                        <div class="pm-edit-user pm-difl">
                                            <div onclick="em_add_to_calendar('<?php echo $event->id; ?>')" id="addToCalendar" style="display: none;" class="kf-event-add-calendar em_color dbfl">
                                                <img class="kf-google-calendar-add" src="<?php echo esc_url(plugins_url('/images/gcal.png', __FILE__)); ?>">
                                                <a class="kf-add-calendar"><?php _e('Add To Calendar', 'eventprime-event-calendar-management'); ?></a>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <div class="pm-popup-mask"></div>    
                                    <div id="pm-change-password-dialog" style="display:none">
                                        <div class="pm-popup-container">
                                            <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
                                                <div class="title"><?php _e('Event Added', 'eventprime-event-calendar-management'); ?>
                                                    <div class="pm-popup-close pm-difr">
                                                        <img src="<?php echo esc_url(plugins_url('/images/popup-close.png', __FILE__)); ?>"  height="24px" width="24px">
                                                    </div>
                                                </div>
                                                <div class="pm-popup-action pm-dbfl pm-pad10 pm-bg">
                                                    <div class="pm-login-box GCal-confirm-message">
                                                        <div class="pm-login-box-error pm-pad10" style="" id="pm_reset_passerror"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="kf-booked-event-description">
                                <?php echo wpautop(do_shortcode($event->description)); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php
            $gmap_api_key = em_global_settings('gmap_api_key');
            if (!empty($event->venue)):
                $venue = $venue_service->load_model_from_db($event->venue);
                if (!empty($venue->id) && !empty($gmap_api_key) && !empty($venue->address)):
                    ?>
                    <div class="kf-event-venue-info difr">
                        <div class="kf-booked-event-venue-name"><?php echo $venue->name ?></div>
                        <div class="kf-booked-event-venue-address"><?php echo $venue->address; ?></div>
                        <?php $direction_links = '<a target="blank" href="https://www.google.com/maps?saddr=My+Location&daddr=' . $venue->address . '">' . $venue->name . '</a> '; ?>
                        <div class="em_venue_dir"><?php _e('Directions', 'eventprime-event-calendar-management'); ?> : <?php echo $direction_links; ?></div>
                        <div data-venue-id="<?php echo $venue->id; ?>" id="em_booking_map_canvas" style="height: 400px;"></div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            <script type="text/javascript">
                document.addEventListener("DOMContentLoaded", function (event) {
                    jQuery(function () {
                        em_load_map('booking', 'em_booking_map_canvas');
                    });
                });
            </script>
        </div>
    <?php else: ?>
        <!-- <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"> -->
        <div class="emagic">
            <a id='em_dummy_link_for_primary_color_extraction' style='display:none' href='#'></a>
            <div class="em_block dbfl">
                <div class="em_username_profile em_block dbfl">
                    <?php 
                    $display_name = ucwords($user->display_name);
                    if(is_email($user->display_name)){
                        $display_name = $user->display_name;
                    }
                    echo __("Welcome", 'eventprime-event-calendar-management') . ', <span class="profile_title">' . $display_name . '</span>'; ?> &nbsp; 
                </div>  
            </div>

            <div class="em-tabmenu-container em_block dbfl" id="ep-tabmenu">
                <div class="ep-tabs">
                    <div class="tab-links em-tab-vertical-menu ">
                        <div class="em-tab-nav">
                            <div class="emtabs_head ep-my-bookings-tab" data-emt-tabcontent="#tab1"><i class="material-icons">receipt</i><?php echo __("My Bookings", 'eventprime-event-calendar-management'); ?></div>
                            <div class="emtabs_head ep-my-submitted-event-tab" data-emt-tabcontent="#tab2"><i class="material-icons">event</i><?php echo __("My Events", 'eventprime-event-calendar-management'); ?></div>
                            <div class="emtabs_head ep-directions-tab" data-emt-tabcontent="#tab3"><i class="material-icons">directions</i><?php echo __("Directions", 'eventprime-event-calendar-management'); ?></div>
                            <div class="emtabs_head ep-transactions-tab" data-emt-tabcontent="#tab4"><i class="material-icons">credit_card</i><?php echo __("Transactions", 'eventprime-event-calendar-management'); ?></div>
                            <div class="emtabs_head ep-account-tab" data-emt-tabcontent="#tab5"><i class="material-icons">account_box</i><?php echo __("Account", 'eventprime-event-calendar-management'); ?></div>
                            <?php do_action('em_wishlist_user_profile_tab');?>
                            <div class="emtabs_head ep-logout-tab" data-emt-tabcontent="#tab7"><i class="material-icons">launch</i><a href="<?php echo wp_logout_url(); ?>"><?php echo __("Logout", 'eventprime-event-calendar-management'); ?></a></div>
                        </div>
                    </div>

                    <div class="em-tab-content-main">
                        <div id="tab1" class="tab active em_block dbfl">
                            <?php
                            if ( count($bookings) > 0 ) {?>
                                <table class="em_profile_table em_block">
                                    <thead class="em_bg">
                                        <tr>
                                            <th class="em_profile_serial"><?php _e("S.No.", 'eventprime-event-calendar-management'); ?></th>                              
                                            <th><?php _e("Event Name", 'eventprime-event-calendar-management'); ?></th>                                
                                            <th><?php _e("Event Date", 'eventprime-event-calendar-management'); ?></th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $venues = array();
                                        foreach ($bookings as $index => $single_booking):
                                            $ev_model = $event_service->load_model_from_db($single_booking->event);
                                            if(!empty($ev_model->venue)){
                                                $venue_model= $venue_service->load_model_from_db($ev_model->venue);
                                                if(!empty($venue_model->id) && !isset($venues[$venue_model->id])){
                                                    $venues[$venue_model->id]=$venue_model;
                                                }
                                            }
                                            if (!empty($ev_model->id)):
                                                $order_info = em_get_post_meta($single_booking->id, 'order_info', true);
                                                if(!empty($order_info) && is_numeric($order_info['quantity'])){
                                                    $discount = ($order_info['quantity'] * $order_info['item_price'] * $order_info['discount']) / 100;
                                                    $total_price = ($order_info['quantity'] * $order_info['item_price']) - $discount;
                                                }?>
                                                <tr>
                                                    <td><?php echo $index + 1; ?></td>
                                                    <td>
                                                        <?php
                                                        if($ev_model->status != 'trash'){?>
                                                            <a href="<?php echo add_query_arg('event', $ev_model->id, get_page_link($global_settings->events_page)); ?>" target="_blank">
                                                                <?php echo $ev_model->name; ?>
                                                            </a><?php 
                                                        }
                                                        else{
                                                            echo $ev_model->name;
                                                        }?>
                                                    </td>                              
                                                    <td><?php echo em_showDateTime($ev_model->start_date, true); ?></td>
                                                    <td>
                                                        <?php
                                                        $booking_page_url = get_permalink(em_global_settings('booking_details_page'));
                                                        $booking_page_url = add_query_arg('id', $single_booking->id, $booking_page_url);?>
                                                        <a href="<?php echo esc_url($booking_page_url); ?>" class="details bg_gradient bg_grad_button" target="_blank"><?php _e('Details', 'eventprime-event-calendar-management'); ?></a></td>
                                                </tr>
                                                <?php
                                            endif;
                                        endforeach;?>
                                    </tbody>
                                </table><?php
                            } else {?>
                                <div class="ep-alert-warning ep-alert-info"> <?php esc_html_e('No Booking Found', 'eventprime-event-calendar-management');?></div><?php
                            }?>
                        </div>
                        
                        <div id="tab2" class="tab">
                            <?php
                            $my_events = $event_service->get_events_by_user($user->ID);
                            if (!empty($my_events)): ?>
                            <table class="em_profile_table">
                                <thead class="em_bg">
                                    <tr>
                                        <th><?php echo __("Event Name", 'eventprime-event-calendar-management'); ?></th>
                                        <th><?php echo __("Start Date", 'eventprime-event-calendar-management'); ?></th>
                                        <th><?php echo __("Submitted On", 'eventprime-event-calendar-management'); ?></th>
                                        <th><?php echo __("Status", 'eventprime-event-calendar-management'); ?></th>
                                        <th>&nbsp;</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($my_events as $my_event): ?>
                                        <tr id="em-fes-row-<?php echo $my_event->id;?>">
                                            <td class="ep-profile-events-title"><a href="<?php echo add_query_arg('event',$my_event->id,get_permalink($global_settings->events_page)); ?>" target="_blank" title="<?php echo $my_event->name; ?>"><?php echo $my_event->name; ?></a></td>
                                            <td><?php echo em_showDateTime($my_event->start_date, true); ?></td>
                                            <td><?php echo get_the_date('',$my_event->id); ?></td>
                                            <td><?php echo __(ucfirst($my_event->status), 'eventprime-event-calendar-management'); ?></td>
                                            <td class="ep-profile-events-action">
                                                <a href="javascript:void(0);" id="em-fes-delete-<?php echo $my_event->id;?>" onclick="em_fes_event_remove(<?php echo $my_event->id; ?>)" title="<?php echo __("Remove Event", 'eventprime-event-calendar-management'); ?>" data-delete_msg="<?php echo __("Are you sure you want to delete the event?", 'eventprime-event-calendar-management'); ?>">
                                                    <i class="material-icons" title="<?php echo __("Remove Event", 'eventprime-event-calendar-management'); ?>">delete</i>
                                                </a>
                                                <a href="<?php echo add_query_arg('event', $my_event->id, get_permalink($global_settings->event_submit_form)); ?>" title="<?php echo __("Edit Event", 'eventprime-event-calendar-management'); ?>">
                                                    <i class="material-icons" title="<?php echo __("Edit Event", 'eventprime-event-calendar-management'); ?>">edit</i>
                                                </a>
                                                <a href="javascript:void(0);" onclick="em_event_download_attendees('<?php echo $my_event->id?>')" title="<?php echo __("Download Attendees", 'eventprime-event-calendar-management'); ?>">
                                                    <i class="material-icons" title="<?php echo __("Download Attendees", 'eventprime-event-calendar-management'); ?>">cloud_download</i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php
                                    endforeach;
                                    ?>
                                </tbody>
                            </table>
                            <?php else: ?>
                            <div class="ep-alert-warning ep-alert-info"><?php _e("You have not submitted any events yet.", 'eventprime-event-calendar-management'); ?></div>
                            <?php endif; ?>
                        </div>

                        <div id="tab3" class="tab em_block dbfl">
                            <?php
                            $venue_ids = $venue_addresses = $datas = array();
                            $v_ids = $direction_links = '';
                            if(isset($venues) && count($venues) > 0){
                                foreach ($venues as $key => $venue) {
                                    if( isset( $venue ) && !empty( $venue )){
                                        array_push($venue_ids, $venue->id);
                                        $venue_addresses[$key]['address'] = $venue->address;
                                        $venue_addresses[$key]['name'] = $venue->name;
                                    }
                                }
                            }
                            
                            $address = array_unique($venue_addresses, SORT_REGULAR);
                            foreach ($address as $key => $values) {
                                $direction_links .= '<a target="blank" href="https://www.google.com/maps?saddr=My+Location&daddr=' . $values['address'] . '">' . $values['name'] . '</a> ';
                            }
                            if (count($venue_ids) > 0)
                                $v_ids = implode(',', $venue_ids);
                            if (!empty($venue_addresses)):
                                ?>
                                <div class="em_venue_dir"><?php _e('Directions', 'eventprime-event-calendar-management'); ?> : <?php echo $direction_links; ?></div>
                                <div data-venue-ids="<?php echo $v_ids; ?>" id="em_user_event_venue_canvas<?php echo $section_id;?>" style="height: 400px;"></div>
                            <?php else: ?>
                                <div class="em_venue_dir ep-alert-warning ep-alert-info"><?php _e('Locations not available.', 'eventprime-event-calendar-management'); ?></div>
                            <?php endif; ?>
                        </div>

                        <div id="tab4" class="tab">
                            <?php
                            if ( count( $bookings ) > 0 ) {?>
                                <table class="em_profile_table">
                                    <thead class="em_bg">
                                        <tr>
                                            <th class="em_profile_serial"></th>
                                            <th><?php echo __("Event Name", 'eventprime-event-calendar-management'); ?></th>
                                            <th><?php echo __("Amount", 'eventprime-event-calendar-management'); ?></th>
                                            <th><?php echo __("Status", 'eventprime-event-calendar-management'); ?></th>
                                            <th><?php echo __('Booked On', 'eventprime-event-calendar-management'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($bookings as $booking):
                                            $event = $event_service->load_model_from_db($booking->event);
                                            $currency_symbol = '';
                                            if (!empty($event->id)):
                                                $total_price = $booking_service->get_final_price($booking->id);
                                                if (!empty($booking->order_info['currency'])) {
                                                    $currency_symbol = $booking->order_info['currency'];
                                                } elseif (isset($booking->payment_log['payment_gateway']) && ($booking->payment_log['payment_gateway'] == 'paypal')) {
                                                    $currency_symbol = $payment_log['mc_currency'];
                                                }
                                                ?>
                                                <tr>
                                                    <td></td>
                                                    <td><?php echo $event->name; ?></td>
                                                    <td><?php echo em_price_with_position($total_price, $currency_symbol); ?></td>
                                                    <td><?php echo EventM_Constants::$status[$booking->status]; ?></td>
                                                    <td><?php echo em_showDateTime($booking->date, true); ?></td>
                                                </tr>
                                            <?php
                                            endif;
                                        endforeach;
                                        ?>
                                    </tbody>
                                </table><?php
                            } else {?>
                                <div class="ep-alert-warning ep-alert-info"> <?php esc_html_e('No Data Found', 'eventprime-event-calendar-management');?></div><?php
                            }?>
                        </div>

                        <div id="tab5" class="tab em_block dbfl">
                            <?php
                            $current_user = wp_get_current_user();
                            if (!$current_user instanceof WP_User)
                                return false;
                            if (is_registration_magic_active()) {
                                em_rm_custom_data($current_user->ID);
                            } else {
                                ?>
                                <table>
                                    <tr><th><?php _e('Name', 'eventprime-event-calendar-management'); ?>:</th><td><?php echo $current_user->display_name; ?></td></tr>
                                    <tr><th><?php _e('Email', 'eventprime-event-calendar-management'); ?>:</th><td><?php echo $current_user->user_email; ?></td></tr>
                                    <?php if(isset($current_user->phone) && !empty($current_user->phone)) { ?>
                                    <tr><th><?php _e('Phone', 'eventprime-event-calendar-management'); ?>:</th><td><?php echo $current_user->phone; ?></td></tr>
                                    <?php } ?>
                                    <tr><th><?php _e('Registered On', 'eventprime-event-calendar-management'); ?>:</th><td><?php echo $current_user->user_registered; ?></td></tr>
                                </table>
                            <?php }
                            if (current_user_can('edit_user', $current_user->ID)) { ?>
                                <a href="<?php echo admin_url().'profile.php'; ?>"><?php _e('Edit Profile', 'eventprime-event-calendar-management'); ?></a>
                            <?php } ?>
                        </div>
                        <?php do_action('em_wishlist_user_profile_tab_content');?>
                    </div>
                </div>
            </div>
            <script>

                function load_profile_tabs_panel<?php echo $section_id;?>(){
                    $ = jQuery;

                    //get accent color from theme
                    g_em_acc_color = $('#em_dummy_link_for_primary_color_extraction').css('color');
                    if (typeof g_em_acc_color == 'undefined'){
                        g_em_acc_color = '#000';
                    }
                    var emagic_jq = $(".emagic");
                    emagic_jq.find("[data-em_apply_acc_color='true']").css('color', g_em_acc_color);
                    emagic_jq.find("[data-em_apply_acc_bgcolor='true']").css('background-color', g_em_acc_color);
                    g_em_customtab = new EMCustomTabs({
                        container: '.ep-tabs',
                        animation: 'fade',
                        accentColor: g_em_acc_color,
                        activeTabIndex: 0,
                        onTabChange: function (i) {
                            if (i == 1) {
                                em_load_map_nws('user_profile', 'em_user_event_venue_canvas<?php echo $section_id;?>');
                            }
                        }
                    });
                }
                var g_em_customtab, g_em_acc_color;
                document.addEventListener("DOMContentLoaded", function (event) {
                    jQuery(document).ready(function () {
                        load_profile_tabs_panel<?php echo $section_id; ?>();
                    });
                });
                /*document.addEventListener("DOMContentLoaded", function (event) {
                    jQuery(document).ready(function () {
                        $ = jQuery;

                        //get accent color from theme
                        g_em_acc_color = $('#em_dummy_link_for_primary_color_extraction').css('color');
                        if (typeof g_em_acc_color == 'undefined')
                            g_em_acc_color = '#000';

                        var emagic_jq = $(".emagic");
                        emagic_jq.find("[data-em_apply_acc_color='true']").css('color', g_em_acc_color);
                        emagic_jq.find("[data-em_apply_acc_bgcolor='true']").css('background-color', g_em_acc_color);
                        g_em_customtab = new EMCustomTabs({
                            container: '.ep-tabs',
                            animation: 'fade',
                            accentColor: g_em_acc_color,
                            activeTabIndex: 0,
                            onTabChange: function (i) {
                                if (i == 1) {
                                    em_load_map('user_profile', 'em_user_event_venue_canvas');
                                }
                            }
                        });
                    });
                });*/

            </script>
        </div>
    <?php endif;
} ?>