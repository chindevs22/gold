<?php  
wp_enqueue_style('em-public-css');
wp_enqueue_script('em_responsive_slider_js');
if ( isset( $atts['ep_sin_events'] ) && !empty( $atts['ep_sin_events'] ) ) {
    $posts = $atts['ep_sin_events'];
}
else{
    $the_query = $event_service->get_mesonry_events_query(array("posts_per_page" => -1),$events_atts);
    $posts = $the_query->posts;
    $posts = apply_filters('ep_filter_front_events', $posts, $atts);
    $posts = array_filter($posts, function($post){ return $post->post_status !== 'draft'; });
}
$service= EventM_Factory::get_service('EventM_Service');
$timestamp = time();
$showBookNowForGuestUsers = em_show_book_now_for_guest_users();
/* $recurring = ((!isset($events_atts['recurring']) || $events_atts['recurring'] === 0) ? 0 : 1); */
$recurring = ( isset( $events_atts['recurring'] ) && $events_atts['recurring'] != '' ) ? $events_atts['recurring'] : 1;
$class = $sec_unique = '';
if($section_id){
    $sec_unique = 'section-slider-view-'.$section_id;
}
if(isset($atts['class'])){
    $class = $atts['class'];
}
?>  
<div class="emagic <?php echo $class;?>" id="<?php echo $sec_unique;?>">
    <?php if (!empty($posts)) : ?>
        <div class="em_slider_view ep-events-slider-wrap" id="ep-slider-container">
            <ul class="em_event_slides elm-slider<?php echo $section_id;?>"  id="event-slider<?php echo $section_id;?>">
                <!-- the loop -->
                <?php foreach ($posts as $post) :
                    $event= $service->load_model_from_db($post->ID);
                    if(empty($recurring) && isset($event->parent) && !empty($event->parent)){
                        continue;
                    }
                    // check for booking allowed
                    $booking_allowed = 1;
                    if((isset($event->parent) && !empty($event->parent)) && (isset($event->enable_recurrence_automatic_booking) && !empty($event->enable_recurrence_automatic_booking))){
                        // if event is recurring and parent has automatic booking enable than not allowed
                        $booking_allowed = 0;
                    }
                    $event->url = em_get_single_event_page_url($event, $global_settings);?>
                    <li>
                        <div class="em_slider difl grid-item ep-slider-item-wrap <?php if (em_is_event_expired($event->id)) echo 'emslider-expired'; ?> <?php echo empty($event->enable_booking) ? 'em_event_disabled' : ''; ?>" id="em-event-<?php echo $event->id;?>">
                            <div class="em_slider_content">
                                <div class="em_event_title em_block dbfl"  title="<?php  echo $event->name; ?>">
                                    <a href="<?php echo $event->url; ?>"><?php echo $event->name; ?></a>
                                    <?php if(is_user_logged_in()): ?>
                                        <?php do_action('event_magic_wishlist_link',$event); ?>
                                    <?php endif; ?>
                                </div>
                                <?php do_action('event_magic_popup_custom_data_before_details',$event);?>
                                <?php $start_date = null; $end_date = null; $start_time = null; $end_time = null; $day = null;
                                    if (em_compare_event_dates($event->id)){
                                        $day = date_i18n(get_option('date_format'),$event->start_date);
                                        $start_time = date_i18n(get_option('time_format'),$event->start_date);
                                        $end_time = date_i18n(get_option('time_format'),$event->end_date);
                                    }
                                    else
                                    {
                                        $start_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->start_date);
                                        $end_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->end_date);
                                    }
                                ?>
                                <?php if($event->all_day):?>
                                    <div class="ep-slider-event-date-row">
                                        <div class="ep-slider-event-date">
                                            <?php echo date_i18n(get_option('date_format'),$event->start_date); ?>
                                            <span class="em-all-day"> - <?php _e('ALL DAY','eventprime-event-calendar-management');?></span>
                                        </div>
                                    </div>
                                <?php elseif(!empty($day)): ?>
                                    <div class="ep-slider-event-date-row">
                                        <div class="ep-slider-event-date ">
                                            <?php echo $day; ?> - <?php echo $start_time;
                                            if(empty($event->hide_end_date)) {
                                                echo '  to  '.$end_time;
                                            }?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="ep-slider-event-date-row">
                                        <div class="ep-slider-event-date ">
                                            <?php echo $start_date; 
                                            if(empty($event->hide_end_date)) {
                                                echo ' - ' . $end_date;
                                            }?>
                                        </div>   
                                    </div>
                                <?php endif; ?>
                                <?php if(!empty($event->description)) { ?>
                                    <div class="ep-slider-event-description dbfl"><?php echo $event->description; ?></div>
                                <?php } ?>
                                <?php 
                                if(!empty($event->venue)){  
                                    $venue_service= EventM_Factory::get_service('EventM_Venue_Service');
                                    $venue= $venue_service->load_model_from_db($event->venue);
                                    if(!empty($venue->id) && !empty($venue->address)){ ?>
                                        <div class="ep-slider-event-address-wrap dbfl" title="<?php echo $venue->address; ?>">
                                            <span class="material-icons em_color">location_on_outline</span><div class="ep-slider-event-address"><?php echo $venue->address; ?></div></div>
                                        <?php 
                                    }
                                }
                                ?>
                                <?php if(!empty($event->enable_booking) && empty($event->hide_booking_status)):
                                    $sum = $event_service->booked_seats($event->id);
                                    $capacity = em_event_seating_capcity($event->id);?>  
                                    <div class="ep-slider-booking-row dbfl">
                                        <div class="kf-event-attr-value dbfl">  
                                            <?php if ($capacity > 0): ?>
                                                <div class="dbfl">
                                                    <?php echo $sum; ?> / <?php echo $capacity; ?> 
                                                </div>
                                            <?php $width = ($sum / $capacity) * 100; ?>
                                                <div class="dbfl ">
                                                    <div id="progressbar" class="em_progressbar dbfl">
                                                        <div style="width:<?php echo $width . '%'; ?>" class="em_progressbar_fill em_bg" ></div>
                                                    </div>
                                                </div>
                                            <?php
                                                else:
                                                    echo '<div class="dbfl">' . $sum . ' '.__('Sold','eventprime-event-calendar-management').'</div>';
                                            ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>  
                                    <?php
                                endif;?>
                                <?php //do_action('event_magic_popup_custom_data_before_footer',$event);?>
                                <div class="em-slider-footer dbfl">
                                    <div class="em_event_price  difl">
                                        <?php 
                                        $ticket_price = $event->ticket_price;
                                        $ticket_price = apply_filters('event_magic_load_calender_ticket_price', $ticket_price, $event);
                                        // check if show one time event fees at front enable
                                        if($event->show_fixed_event_price){
                                            if($event->fixed_event_price > 0){
                                                $ticket_price = $event->fixed_event_price;
                                            }
                                        }
                                        if(!is_numeric($ticket_price)){
                                            echo $ticket_price;
                                        }
                                        else{
                                            echo !empty($ticket_price) ? em_price_with_position($ticket_price) : '';
                                        } ?>
                                    </div>
                                    <?php do_action('event_magic_card_view_after_price',$event); ?>
                                    <div class="kf-tickets-button difr">
                                        <div class="em_event_attr_box em_eventpage_register difl">
                                            <?php 
                                            if(absint($event->custom_link_enabled) == 1):?>
                                                <div class="em_header_button em_event_custom_link kf-tickets">
                                                    <a class="em_header_button kf-tickets" target="_blank" href="<?php echo $event->url; ?>">
                                                        <?php echo em_global_settings_button_title('Click for Details'); ?>
                                                    </a>
                                                </div>
                                            <?php
                                            elseif($event_service->is_bookable($event)): $current_ts = em_current_time_by_timezone();?>
                                                <?php if($event->status=='expired'):?>
                                                    <div class="em_header_button em_event_expired kf-tickets">
                                                        <?php echo em_global_settings_button_title('Bookings Expired'); ?>
                                                    </div>
                                                <?php elseif($current_ts>$event->last_booking_date): ?>
                                                    <div class="em_header_button em_booking-closed kf-tickets">
                                                        <?php echo em_global_settings_button_title('Bookings Closed'); ?>
                                                    </div>
                                                <?php elseif($current_ts<$event->start_booking_date): ?>  
                                                        <div class="em_header_button em_not_started kf-tickets">
                                                           <?php echo em_global_settings_button_title('Bookings not started yet'); ?>
                                                        </div>
                                                <?php else: ?>
                                                    <?php 
                                                    if(!empty($booking_allowed)):
                                                        if(is_user_logged_in() || $showBookNowForGuestUsers): ?>
                                                            <form action="<?php echo get_permalink($global_settings->booking_page); ?>" method="post" name="em_booking">
                                                                <button class="em_header_button em_event-booking kf-tickets em_color" name="tickets" onclick="em_event_booking(<?php echo $event->id ?>)" id="em_booking">
                                                                    <?php echo em_global_settings_button_title('Book Now'); ?>
                                                                </button>
                                                                <input type="hidden" name="event_id" value="<?php echo $event->id; ?>" />
                                                                <input type="hidden" name="venue_id" value="<?php echo $event->venue; ?>" />
                                                            </form>
                                                        <?php else: ?> 
                                                            <a class="em_header_button kf-tickets" target="_blank" href="<?php echo add_query_arg('event_id',$event->id,get_permalink($global_settings->profile_page)); ?>">
                                                                <?php echo em_global_settings_button_title('Book Now'); ?>
                                                            </a>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            <?php elseif($event->status == 'publish' && $event->enable_booking == 1): ?>
                                                <?php  if(isset($event->standing_capacity) && !empty($event->standing_capacity)):?>
                                                    <div class="em_event_attr_box em_eventpage_register difl">
                                                        <div class="em_header_button em_not_bookable kf-tickets">
                                                            <?php echo em_global_settings_button_title('All Seats Booked'); ?>
                                                        </div>
                                                    </div>
                                                <?php else:?>
                                                    <div class="em_event_attr_box em_eventpage_register difl">
                                                        <div class="em_header_button em_not_bookable kf-tickets">
                                                            <?php echo em_global_settings_button_title('Bookings Closed'); ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php do_action('event_magic_card_view_after_footer',$event); ?>
                            </div>
                        
                            <div class="em_slider_img">
                                <?php if (!empty($event->cover_image_id)): ?>
                                    <a href="<?php echo $event->url; ?>"><?php //echo get_the_post_thumbnail($event->id,'large'); ?>
                                        <img src="<?php if(!empty($event->cover_image_id)): echo wp_get_attachment_image_src($event->cover_image_id, 'large')[0]; else: echo esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png'); ?>" alt="<?php _e('Event Cover Image', 'eventprime-event-calendar-management'); endif; ?>">
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo $event->url; ?>"><img src="<?php echo esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png'); ?>" alt="<?php _e('Dummy Image','eventprime-event-calendar-management'); ?>" class="em-no-image"></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php else: ?>
        <?php if($_POST): ?>
            <article>
                <p><?php _e('No events match your criterion.','eventprime-event-calendar-management'); ?></p>
            </article>
        <?php else: ?>
            <article>
                <p><?php _e('There are no Events available right now.','eventprime-event-calendar-management'); ?></p>
            </article>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function (event) {
        jQuery(document).ready(function () {
            jQuery(".elm-slider<?php echo $section_id;?>").responsiveSlides({
                auto: true,
                speed: 500,
                timeout: 4000,
                nav: true,
                pause: true,
                pauseControls: true,
                namespace: "centered-btns",
            });
        });
    });
</script>