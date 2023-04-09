<?php
wp_enqueue_style('em-public-css');
if ( isset( $atts['ep_sin_events'] ) ) {
    $posts = $atts['ep_sin_events'];
}
else{
    $the_query = $event_service->get_cards_events_query(array(),$events_atts);
    $posts = $the_query->posts;
    $posts = apply_filters('ep_filter_front_events',$posts,$atts);
    $posts = array_filter($posts, function($post){ return $post->post_status !== 'draft'; });
}
$service = EventM_Factory::get_service('EventM_Service');
$timestamp = time();
$showBookNowForGuestUsers = em_show_book_now_for_guest_users();
$posts_per_page = $event_service->get_posts_per_page_card();
$sites = $types = '';
if(isset($events_atts['show']) && !empty($events_atts['show'])){
    $posts_per_page = $events_atts['show'];
}
if($events_atts['sites'] && !empty($events_atts['sites'])){
    $sites = implode(',',$events_atts['sites']);
}
if($events_atts['types'] && !empty($events_atts['types'])){
    $types = implode(',',$events_atts['types']);
}
/* $upcoming = isset($events_atts['upcoming']) ? $events_atts['upcoming'] : ''; */
/* $recurring = ((!isset($events_atts['recurring']) || $events_atts['recurring'] === 0) ? 0 : 1); */
/* upcoming and recurring improved */
$upcoming = ( isset( $events_atts['upcoming'] ) && $events_atts['upcoming'] != '' ) ? $events_atts['upcoming'] : '';
$recurring = ( isset( $events_atts['recurring'] ) && $events_atts['recurring'] != '' ) ? $events_atts['recurring'] : 1;

$section_id = $class = $sec_unique = '';
if($section_id){
    $sec_unique = 'section-card-view-'.$section_id;
}
if(isset($atts['class'])){
    $class = $atts['class'];
}?>  
<div class="emagic <?php echo $class;?>" id="<?php echo $sec_unique;?>">
    <?php if (!empty($posts)) : ?>
        <div class="em_cards">
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
                <div class="<?php if(empty($section_id)){ echo 'em_card'; } else{ echo 'em_card_edt';}?> difl <?php if (em_is_event_expired($event->id)) echo 'emcard-expired'; ?> <?php echo (empty($event->enable_booking) && absint($event->custom_link_enabled) == 0) ? 'em_event_disabled' : ''; ?> <?php echo $column_class;?>" id="em-event-<?php echo $event->id;?>">
                    <div class="em_event_cover dbfl">
                        <?php 
                        $thumbImage = esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png');
                        if (!empty($event->cover_image_id)): ?>
                            <?php 
                            $thumbImageData = wp_get_attachment_image_src($event->cover_image_id, 'large');
                            if(!empty($thumbImageData) && isset($thumbImageData[0])){
                                $thumbImage = $thumbImageData[0];
                            }
                            if(empty($thumbImage)){
                                $thumbImage = get_the_post_thumbnail($event->id,'large');
                                if(isset($event->parent) && !empty($event->parent) && empty($thumbImage)){
                                    $thumbImage = get_the_post_thumbnail($event->parent,'large');
                                }
                            }?>
                            <a href="<?php echo $event->url; ?>">
                                <?php //echo $thumbImage; ?>
                                <img src="<?php echo $thumbImage; ?>" alt="<?php _e('Event Cover Image', 'eventprime-event-calendar-management');?>">
                            </a>
                        <?php else: ?>
                            <a href="<?php echo $event->url; ?>"><img src="<?php echo esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png'); ?>" alt="<?php _e('Dummy Image','eventprime-event-calendar-management'); ?>" class="em-no-image" ></a>
                        <?php endif; ?>
                    </div>
                    
                    <div class="dbfl em-card-description">
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
                            } else {
                                $start_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->start_date);
                                $end_date = date_i18n(get_option('date_format').' '.get_option('time_format'),$event->end_date);
                            }
                        if($event->all_day):?>
                            <div class="em_event_start difl em_color em_wrap">
                                <?php echo date_i18n(get_option('date_format'),$event->start_date); ?><span class="em-all-day"> - <?php _e('ALL DAY','eventprime-event-calendar-management');?></span>
                            </div>
                        <?php elseif(!empty($day)): ?>
                            <div class="em_event_start difl em_color em_wrap">
                                <?php echo $day; ?>
                            </div>
                            <div class="em_event_start difl em_color em_wrap">
                                <?php echo $start_time; ?>
                                <?php if(empty($event->hide_end_date)): 
                                    echo '  to  ' . $end_time;
                                endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="em_event_start difl em_color em_wrap">
                                <?php echo $start_date; ?>
                            </div>
                            <?php if(empty($event->hide_end_date)): ?>
                                <div class="em_event_start difl em_color em_wrap">
                                    <?php echo ' - ' . $end_date; ?>
                                </div><?php 
                            endif;
                        endif; ?>
                        <?php 
                        if(!empty($event->venue)){  
                            $venue_service= EventM_Factory::get_service('EventM_Venue_Service');
                            $venue= $venue_service->load_model_from_db($event->venue);
                            if(!empty($venue->id)){  ?>
                                <div class="em_event_address dbfl" title="<?php echo $venue->address; ?>"><?php echo $venue->address; ?></div>
                                <?php 
                            }
                        }?>
                        <?php if(!empty($event->description)) { ?>
                            <div class="em_event_description dbfl"><?php echo $event->description; ?></div>
                        <?php } ?>

                        <?php if(!empty($event->enable_booking) && empty($event->hide_booking_status)):
                            $sum = $event_service->booked_seats($event->id);
                            $capacity = em_event_seating_capcity($event->id);?>  
                            <div class="dbfl">
                                <div class="kf-event-attr-value dbfl">  
                                    <?php if ($capacity > 0): ?>
                                        <div class="dbfl">
                                            <?php echo $sum; ?> / <?php echo $capacity; ?> 
                                        </div>
                                    <?php $width = ($sum / $capacity) * 100; ?>
                                        <div class="dbfl">
                                            <div id="progressbar" class="em_progressbar dbfl">
                                                <div style="width:<?php echo $width . '%'; ?>" class="em_progressbar_fill em_bg" ></div>
                                            </div>
                                        </div>
                                    <?php
                                        else:
                                            echo '<div class="dbfl">' . $sum . ' '.__('Attending','eventprime-event-calendar-management').'</div>';
                                    ?>
                                    <?php endif; ?>
                                </div>
                            </div>  
                            <?php
                        endif;?>
                        <?php do_action('event_magic_popup_custom_data_before_footer',$event);?>
                    </div>
                    <div class="em-cards-footer dbfl">
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
                                        <a class="ep-event-custom-link" target="_blank" href="<?php echo $event->url; ?>">
                                            <?php 
                                            if(!empty($global_settings->hide_event_custom_link) && !is_user_logged_in()){
                                                echo em_global_settings_button_title('Login to View');
                                            }
                                            else{
                                                echo em_global_settings_button_title('Click for Details');
                                            }?>
                                        </a>
                                    </div>
                                <?php
                                elseif($event_service->is_bookable($event)): $current_ts = em_current_time_by_timezone();?>
                                    <?php if($event->status=='expired'):?>
                                        <div class="em_header_button em_event_expired kf-tickets">
                                            <?php echo em_global_settings_button_title('Bookings Expired'); ?>
                                        </div>
                                    <?php elseif($current_ts>$event->last_booking_date): ?>
                                        <div class="em_header_button em_booking-closed kf-tickets"><?php echo em_global_settings_button_title('Bookings Closed'); ?></div>
                                    <?php elseif($current_ts<$event->start_booking_date): ?>  
                                        <div class="em_header_button em_not_started kf-tickets"><?php echo em_global_settings_button_title('Bookings not started yet'); ?></div>
                                    <?php else: ?>
                                        <?php 
                                        if(!empty($booking_allowed)):
                                            if(is_user_logged_in() || $showBookNowForGuestUsers): ?>
                                                <form action="<?php echo get_permalink($global_settings->booking_page); ?>" method="post" name="em_booking">
                                                    <button class="em_header_button em_event-booking kf-tickets" name="tickets" onclick="em_event_booking(<?php echo $event->id ?>)" id="em_booking"><?php echo em_global_settings_button_title('Book Now'); ?></button>
                                                    <input type="hidden" name="event_id" value="<?php echo $event->id; ?>" />
                                                    <input type="hidden" name="venue_id" value="<?php echo $event->venue; ?>" />
                                                </form>
                                            <?php else: ?> 
                                                <a class="em_header_button kf-tickets" target="_blank" href="<?php echo add_query_arg('event_id',$event->id,get_permalink($global_settings->profile_page)); ?>"><?php echo em_global_settings_button_title('Book Now'); ?></a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php elseif($event->status == 'publish' && $event->enable_booking == 1): ?>
                                    <?php  if(isset($event->standing_capacity) && !empty($event->standing_capacity)):?>
                                        <div class="em_event_attr_box em_eventpage_register difl">
                                            <div class="em_header_button em_not_bookable kf-tickets"><?php echo em_global_settings_button_title('All Seats Booked'); ?></div>
                                        </div>
                                    <?php else:?>
                                        <div class="em_event_attr_box em_eventpage_register difl">
                                            <div class="em_header_button em_not_bookable kf-tickets"><?php echo em_global_settings_button_title('Bookings Closed'); ?></div>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php do_action('event_magic_card_view_after_footer',$event); ?>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if(!isset($atts['events'])): 
            if($the_query->max_num_pages > 1):?>
                <?php $curr_page = $the_query->query_vars['paged'];?>
                <div class="ep-view-load-more ep-view-load-more-wrap dbfl" onclick="em_view_load_more_events('.ep-view-load-more','.ep-loading-view-btn','.em_cards')" data-curr_page="<?php echo $curr_page?>" data-loading="<?php _e('Loading...');?>" data-loaded="<?php _e('Load More');?>" data-max_page="<?php echo $the_query->max_num_pages;?>" data-upcoming="<?php echo $upcoming;?>" data-sites="<?php echo $sites;?>" data-types="<?php echo $types;?>" data-show="<?php echo $posts_per_page;?>" data-recurring="<?php echo $recurring;?>" data-i_events="<?php echo $i_events;?>">
                    <div class="ep-loading-view-btn em_color"><?php _e('Load More');?></div>
                </div>
            <?php endif;
        endif;?>
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