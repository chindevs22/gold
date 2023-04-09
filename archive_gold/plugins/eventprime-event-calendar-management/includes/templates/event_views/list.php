<?php
wp_enqueue_style('em-public-css');
if ( isset( $atts['ep_sin_events'] ) && !empty( $atts['ep_sin_events'] ) ) {
    $posts = $atts['ep_sin_events'];
}
else{
    $the_query = $event_service->get_mesonry_events_query(array(),$events_atts);
    $posts= $the_query->posts;
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
/* $upcoming = isset($events_atts['upcoming']) ? $events_atts['upcoming'] : '';
$recurring = ((!isset($events_atts['recurring']) || $events_atts['recurring'] === 0) ? 0 : 1); */
/* upcoming and recurring improved */
$upcoming = ( isset( $events_atts['upcoming'] ) && $events_atts['upcoming'] != '' ) ? $events_atts['upcoming'] : '';
$recurring = ( isset( $events_atts['recurring'] ) && $events_atts['recurring'] != '' ) ? $events_atts['recurring'] : 1;

$section_id = $class = $sec_unique = $last_month_id = '';
if($section_id){
    $sec_unique = 'section-card-view-'.$section_id;
}
if(isset($atts['class'])){
    $class = $atts['class'];
}?>
<div class="emagic <?php echo $class;?>" id="<?php echo $sec_unique;?>">
    <?php if (!empty($posts)) : ?>
        <div class="em_list_view ep-events-list-wrap em_cards" id="ms-container">
            <div class="ep-wrap">
                <div class="ep-event-list-standard">
                    <!-- the loop -->
                    <?php foreach ($posts as $post) :
                        $event = $service->load_model_from_db($post->ID);
                        if(empty($recurring) && isset($event->parent) && !empty($event->parent)){
                            continue;
                        }
                        $month_id = date('Ym', $event->start_date);
                        if(empty($last_month_id) || $last_month_id != $month_id){
                            $last_month_id = $month_id;?>
                            <div class="ep-month-divider"><span class="ep-listed-event-month"><?php echo date_i18n('F Y', $event->start_date); ?><span class="ep-listed-event-month-tag"></span></span></div><?php
                        }
                        // check for booking allowed
                        $booking_allowed = 1;
                        if((isset($event->parent) && !empty($event->parent)) && (isset($event->enable_recurrence_automatic_booking) && !empty($event->enable_recurrence_automatic_booking))){
                            // if event is recurring and parent has automatic booking enable than not allowed
                            $booking_allowed = 0;
                        }
                        $event->url = em_get_single_event_page_url($event, $global_settings);?>
                        <div id="em-event-<?php echo $event->id;?>" class="ep-event-article <?php if (em_is_event_expired($event->id)) echo 'emlist-expired'; ?> <?php echo empty($event->enable_booking) ? 'em_event_disabled' : ''; ?>">
                            <div class="ep-topsec">
                                <div class="em-col-3 difl ep-event-image-wrap ep-col-table-c">
                                    <div class="em_event_cover_list dbfl">
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
                                                <img src="<?php echo $thumbImage; ?>" alt="<?php _e('Event Cover Image', 'eventprime-event-calendar-management');?>">
                                            </a>
                                        <?php else: ?>
                                            <a href="<?php echo $event->url; ?>"><img src="<?php echo esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png'); ?>" alt="<?php _e('Dummy Image','eventprime-event-calendar-management'); ?>" class="em-no-image" ></a>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="em-col-5 difl ep-col-table-c ep-event-content-wrap">
                                    <div class="ep-event-content">
                                        <h3 class="ep-event-title"><a class="ep-color-hover" data-event-id="<?php echo $event->id;?>" href="<?php echo $event->url; ?>" target="_self"><?php  echo $event->name; ?></a>
                                        </h3>
                                        <?php if(is_user_logged_in()): ?>
                                            <?php do_action('event_magic_wishlist_link',$event); ?>
                                        <?php endif; ?>
                                        <?php if(!empty($event->description)) { ?>
                                            <div class="ep-event-description"><?php echo $event->description; ?></div>
                                        <?php } ?>
                                    </div>
                                </div>

                                <div class="em-col-4 difl ep-col-table-c ep-event-meta-wrap">
                                    <div class="ep-event-meta ep-color-before">
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
                                            <div class="ep-list-event-date-row">
                                                <span class="material-icons em_color">date_range</span> 
                                                <div class="ep-list-event-date">
                                                    <?php echo date_i18n(get_option('date_format'),$event->start_date); ?>
                                                    <span class="em-all-day"> - <?php _e('ALL DAY','eventprime-event-calendar-management');?></span>
                                                </div>
                                            </div>
                                        <?php elseif(!empty($day)): ?>
                                            <div class="ep-list-event-date-row">
                                                <span class="material-icons em_color">date_range</span>
                                                <div class="ep-list-event-date">
                                                    <?php echo $day; ?> - <?php echo $start_time;
                                                    if(empty($event->hide_end_date)) {
                                                        echo '  to  '.$end_time;
                                                    }?>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="ep-list-event-date-row">
                                                <span class="material-icons em_color">date_range</span>
                                                <div class="ep-list-event-date">
                                                    <?php echo $start_date; 
                                                    if(empty($event->hide_end_date)) {
                                                        echo ' - ' . $end_date;
                                                    }?>
                                                </div>
                                            </div>
                                        <?php endif; ?> 
                                        <?php 
                                        if(!empty($event->venue)){
                                            $venue_service= EventM_Factory::get_service('EventM_Venue_Service');
                                            $venue= $venue_service->load_model_from_db($event->venue);
                                            if(!empty($venue->id) && !empty($venue->address)){ ?>
                                                <div class="em-list-view-venue-details" title="<?php echo $venue->address; ?>"><span class="ep-list-event-location"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zM7 9c0-2.76 2.24-5 5-5s5 2.24 5 5c0 2.88-2.88 7.19-5 9.88C9.92 16.21 7 11.85 7 9z"/><circle cx="12" cy="9" r="2.5"/></svg></span><div class="em-list-event-address"><span><?php echo $venue->address; ?></span></div>
                                                </div><?php 
                                            }
                                        } ?> 

                                        <?php if(!empty($event->enable_booking) && empty($event->hide_booking_status)):
                                            $sum = $event_service->booked_seats($event->id);
                                            $capacity = em_event_seating_capcity($event->id);?>  
                                            <div class="ep-list-booking-status ep-event-attenders-main">
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
                                                        if($sum > 0){
                                                            echo '<div class="ep-event-attenders-wrap"><span class="material-icons em_color">person</span><span class="ep-event-attenders">' . $sum . ' </span>'.__('Attending','eventprime-event-calendar-management').'</div>';
                                                        }?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endif;?>
                                        <?php do_action('event_magic_popup_custom_data_before_footer',$event);?>
                                        <div class="ep-list-view-footer">
                                            <div class="em_event_price difl">
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
                                                                    <a class="em_header_button em_event-booking kf-tickets em_color" target="_blank" href="<?php echo add_query_arg('event_id',$event->id,get_permalink($global_settings->profile_page)); ?>">
                                                                        <?php echo em_global_settings_button_title('Book Now'); ?>
                                                                    </a>
                                                                <?php endif; ?>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    <?php elseif($event->status == 'publish' && $event->enable_booking == 1):?>
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
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php
        if($the_query->max_num_pages > 1){
            $curr_page = $the_query->query_vars['paged'];?>
            <div class="ep-masonry-load-more ep-masonry-load-more-wrap" onclick="em_list_load_more_events()" data-curr_page="<?php echo $curr_page?>" data-loading="<?php _e('Loading...');?>" data-loaded="<?php _e('Load More');?>" data-max_page="<?php echo $the_query->max_num_pages;?>" data-upcoming="<?php echo $upcoming;?>" data-sites="<?php echo $sites;?>" data-types="<?php echo $types;?>" data-show="<?php echo $posts_per_page;?>" data-recurring="<?php echo $recurring;?>" data-month_id="<?php echo $last_month_id;?>" data-i_events="<?php echo $i_events;?>"><div class="ep-load-more-button em_color"><?php _e('Load More');?></div></div><?php
        }?>
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