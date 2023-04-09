<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
// check if pretty seo urls enabled from global settings
$ep_seo_full_class = $ep_seo_side_class = '';
$enable_seo_urls = em_global_settings('enable_seo_urls');
if ( ! empty( $enable_seo_urls ) ) {
    // if pretty url enabled then load header and footers
    get_header();
    $post = get_post();
    if ( ! empty( $post ) && 'em_event' === $post->post_type && 'trash' !== $post->post_status ) {
        if ( 'draft' === $post->post_status && ! current_user_can( 'manage_options' ) ) {
            esc_html_e( 'This event is currently in draft state', 'eventprime-event-calendar-management' );
        } else {
            $event_id = $post->ID;
        }
    }
    $ep_seo_full_class = 'ep-single-page';
    $ep_seo_side_class = 'ep-event-sidebar';
}

em_localize_map_info('em-google-map');
wp_enqueue_script('jquery-ui-tabs');
wp_enqueue_script('em-public');
wp_enqueue_script('jquery-colorbox');
wp_enqueue_style('em-colorbox-css');
wp_enqueue_style('em-public-css');
wp_enqueue_script('em-single-event');
wp_enqueue_script('fontawesome');
global $wp;

$event_service = EventM_Factory::get_service('EventM_Service');
$performer_service = EventM_Factory::get_service('EventM_Performer_Service');
$setting_service = EventM_Factory::get_service('EventM_Setting_Service');
$global_settings = $setting_service->load_model_from_db();
$event_model = $event_service->load_model_from_db($event_id);
$event_service->get_header($event_model);
$currency_symbol = em_currency_symbol();
$venue_service = EventM_Factory::get_service('EventM_Venue_Service');
$venue_model = $venue_service->load_model_from_db($event_model->venue);
$upcoming_events = $venue_service->get_upcoming_events($venue_model->id);
$booked_seats = $event_service->booked_seats($event_model->id);
$booking_allowed = 1;
if((isset($event_model->parent) && !empty($event_model->parent)) && (isset($event_model->enable_recurrence_automatic_booking) && !empty($event_model->enable_recurrence_automatic_booking))){
    $booking_allowed = 0;
}
$showBookNowForGuestUsers = em_show_book_now_for_guest_users();
$extensions = event_magic_instance()->extensions;
$class = $sec_unique = '';
$page = true;
if(isset($section_id) && !empty($section_id)){
    $sec_unique = 'section-single-event-'.$section_id; 
    $page = false;
}
if(isset($atts['class'])){
    $class = $atts['class'];
}
$organizer_service = EventM_Factory::get_service('EventOrganizerM_Service');?>
<div class="ep-main-container">
    <div class="emagic <?php echo esc_attr($class);?> <?php echo esc_attr($ep_seo_full_class);?>" id="<?php echo esc_attr($sec_unique);?>">
        <?php if ( ! empty( $enable_seo_urls ) ) {?>
            <div class="em-single-header-area">
                <h1><?php echo get_the_title($event_id);?></h1>
            </div><?php
        }?>
        <?php do_action('em_event_before_main_content'); ?>
        <!-- content-area starts-->
        <div id="em_primary" class="em_content_area <?php echo empty($event_model->enable_booking) ? 'em_event_disabled' : ''; ?>">
    		<!-- Cover Image -->
    		<div class="em_cover_image dbfl">
                <img class="ep-event-cover <?php if(empty($event_model->cover_image_id)){ echo 'em-no-image';}?>" src="<?php if(!empty($event_model->cover_image_id)): echo wp_get_attachment_image_src($event_model->cover_image_id, 'large')[0]; else: echo esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png'); ?>" alt="<?php _e('Event Cover Image', 'eventprime-event-calendar-management'); endif; ?>">
    		</div>
    		<div class="kf-event-header dbfl">
                <div class="kf-event-title ep-event-title-block dbfl">
                    <div class="ep-event-title-wrap">
                        <div class="kf-event-date-large difl">
                            <div class="kf-date-large-icon em_bg dbfl">
                                <div class="kf-date-icon-top dbfl">
                                    <?php echo date_i18n("M", $event_model->start_date);?>
                                </div>
                                <div class="kf-date-icon-bottom dbfl">
                                    <?php echo date_i18n("j", $event_model->start_date);?>
                                </div>
                            </div>
                        </div>
                        <div class="ep-single-event-title difl">
                            <div class="kf-post-title dbfl">
                                <?php echo $event_model->name; ?>
                                <?php if(is_user_logged_in()): ?>
                                    <?php do_action('event_magic_wishlist_link',$event_model); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <!--social sharing-->
                        <?php if(!empty($global_settings->social_sharing)): ?>
                            <?php if ( ! empty( $enable_seo_urls ) ) {
                                        $social_links_url = home_url( $wp->request );
                                  }else{
                                        $social_links_url = home_url( $wp->request.'?event='.$event_model->id );
                                  }?>
                            <div class="kf-event-share dbfl">
                                <a href="https://twitter.com/share?url=<?php echo $social_links_url; ?>" onclick="javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=500'); return false;" target="_blank">
                                    <i class="fa fa-twitter em_share" aria-hidden="true"></i>
                                </a>
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $social_links_url; ?>" onclick="javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=500,width=600'); return false;" target="_blank">
                                    <i class="fa fa-facebook-official em_share_fb em_share" aria-hidden="true"></i>
                                    <div id="em_fb_root"></div>
                                </a>
                                <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo $social_links_url; ?>" onclick="javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=500'); return false;" target="_blank">
                                    <i class="fa fa-linkedin fa-linkedin-official em_share" aria-hidden="true"></i>
                                </a>
                                <a href="https://api.whatsapp.com/send?text=<?php echo $social_links_url; ?>" target="_blank">
                                    <i class="fa fa-whatsapp fa-whatsapp-official em_share" aria-hidden="true"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <!-- Book now button -->
                    <?php 
                    if(absint($event_model->custom_link_enabled) == 1):?>
                        <div class="em_event_custom_link">
                            <a class="ep-event-custom-link" target="_blank" href="<?php echo $event_model->custom_link; ?>">
                                <?php 
                                if(!empty($global_settings->hide_event_custom_link) && !is_user_logged_in()){
                                    echo em_global_settings_button_title('Login to View');
                                }
                                else{
                                    echo em_global_settings_button_title('Click for Details');
                                }?>
                            </a>
                        </div><?php 
                    elseif($event_service->is_bookable($event_model)): ?>
                        <div class="ep_ticket_price-wrap">
                            <div class="ep_ticket_price-button difl">
                                <?php 
                                if(in_array('woocommerce_integration', $extensions)){
                                    do_action('event_magic_woocommerce_integration_event_detail',$event_model);
                                }?>
                                <?php if($event_service->is_bookable($event_model)): $current_ts = em_current_time_by_timezone();?>
                                    <?php if( empty($booking_allowed) ): ?>
                                        <button class="em_header_button em_not_bookable kf-tickets"><?php echo em_global_settings_button_title('Bookings not allowed'); ?></button>
                                    <?php elseif($event_model->status=='expired'):?>
                                        <button class="em_header_button em_not_bookable kf-tickets"><?php echo em_global_settings_button_title('Bookings Expired'); ?></button>
                                    <?php elseif($current_ts>$event_model->last_booking_date): ?>
                                        <button class="em_header_button em_not_bookable kf-tickets"><?php echo em_global_settings_button_title('Bookings Closed'); ?></button>
                                    <?php elseif($current_ts<$event_model->start_booking_date): ?>  
                                        <button class="em_header_button em_not_bookable kf-tickets"><?php echo em_global_settings_button_title('Bookings not started yet'); ?></button>
                                    <?php else: ?>
                                    <?php if(is_user_logged_in() || $showBookNowForGuestUsers): ?>
                                        <form action="<?php echo get_permalink($global_settings->booking_page); ?>" method="post" name="em_booking">
                                            <button class="kf-tickets" name="tickets" onclick="em_event_booking(<?php echo $event_model->id ?>)" class="em_header_button" id="em_booking">
                                                <i class="fa fa-ticket" aria-hidden="true"></i>
                                                <?php
                                                echo em_global_settings_button_title('Book Now');
                                                $ticketPrice = $event_model->ticket_price;
                                                // check if show one time event fees at front enable
                                                if($event_model->show_fixed_event_price){
                                                    if($event_model->fixed_event_price > 0){
                                                        $ticketPrice = $event_model->fixed_event_price;
                                                    }
                                                }
                                                $ticketPrice = apply_filters('event_magic_single_event_ticket_price', $ticketPrice, $event_model->id);
                                                if ($ticketPrice > 0){
                                                    echo " - " . '<span class="em_event_price">' . em_price_with_position($ticketPrice) . '</span>';
                                                }
                                                do_action('event_magic_single_event_ticket_price_after', $event_model, $ticketPrice);
                                                ?>
                                            </button>
                                            <input type="hidden" name="event_id" value="<?php echo $event_model->id; ?>" />
                                            <input type="hidden" name="venue_id" value="<?php echo $event_model->venue; ?>" />
                                        </form>
                                    <?php else: ?>
                                        <a class="em_header_button kf-tickets" target="_blank" href="<?php echo add_query_arg('event_id',$event_model->id,get_permalink($global_settings->profile_page)); ?>"><?php echo em_global_settings_button_title('Book Now'); ?></a>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php else: ?>
                                    <div class="em_event_attr_box em_eventpage_register difl">
                                        <?php if( empty($booking_allowed) ): ?>
                                            <button class="em_header_button em_not_bookable kf-tickets"><?php echo em_global_settings_button_title('Bookings not allowed'); ?></button>
                                        <?php else: ?>
                                            <button class="em_header_button em_not_bookable kf-tickets"><?php echo em_global_settings_button_title('Bookings Closed'); ?></button>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?> 
                            </div>
                            <?php
                            if(in_array('em_automatic_discounts', $extensions)){ 
                                do_action('event_magic_ebd_rule_description',$event_model);
                            }?>                                             
                        </div>    
                    <?php endif; ?>
                </div>                    
                
                <?php do_action( 'event_magic_single_event_after_event_block', $event_model); ?>       
                
                <div class="kf-event-attributes dbfl">
                    <div class="kf-event-attr difl">
                        <div class="kf-event-attr-value dbfl">
                            <?php if(!empty($event_model->all_day)){?>
                                <div class="kf-event-attr-name dbfl">
                                    <?php _e('Date and Time','eventprime-event-calendar-management'); ?>
                                </div><?php
                                if(is_multidate_event($event_model)){
                                    echo date_i18n(get_option('date_format'), $event_model->start_date);
                                    if(empty($event_model->hide_end_date)) {
                                        echo' - ' . date_i18n(get_option('date_format'), $event_model->end_date);
                                    }
                                }
                                else{
                                    echo date_i18n(get_option('date_format'),$event_model->start_date).' - '.__('ALL DAY','eventprime-event-calendar-management');
                                }
                            }
                            else{?>
                                <div class="kf-event-attr-name dbfl">
                                    <?php echo __("Starts",'eventprime-event-calendar-management'); ?>
                                </div>
                                <div class="kf-event-attr-value dbfl">  
                                    <?php echo date_i18n(get_option('date_format').' '.get_option('time_format'),$event_model->start_date); ?>
                                </div>
                                <?php if(empty($event_model->hide_end_date)) {?>
                                    <div class="kf-event-attr-name dbfl">
                                        <?php echo __("Ends",'eventprime-event-calendar-management'); ?>
                                    </div>
                                    <div class="kf-event-attr-value dbfl">  
                                        <?php echo date_i18n(get_option('date_format').' '.get_option('time_format'),$event_model->end_date); ?>
                                    </div><?php
                                }
                            }?>
                            <?php if($global_settings->gcal_sharing == 1) :?>
                                <div id="add-to-google-calendar" class="ep-addto-calendar dbfl">
                                    <p><label><input type="text" id="event_<?php echo $event_model->id ?>"  style="display: none" value="<?php echo $event_model->name; ?>"></label></p>
                                    <p><label> <input type="text" id="s_date_<?php echo $event_model->id ?>"  style="display: none"value="<?php echo date_i18n(get_option('date_format').' '.get_option('time_format'),$event_model->start_date); ?>" ></label></p>
                                    <p><label><input type="text" id="e_date_<?php echo $event_model->id ?>"  style="display: none" value="<?php echo date_i18n(get_option('date_format').' '.get_option('time_format'),$event_model->end_date); ?>" ></label></p> 
                                    <p>
                                        <div onclick="em_gcal_handle_auth_click()"  id="authorize-button" class="kf-event-add-calendar em_color dbfl">
                                            <img class="kf-google-calendar-add" src="<?php echo esc_url(plugins_url('/images/gcal.png', __FILE__)); ?>" />
                                            <?php _e("Add To Calendar",'eventprime-event-calendar-management'); ?>
                                        </div>
                                    </p>
                                    <?php if(current_time('timestamp')<$event_model->start_date): ?>
                                        <div class="pm-edit-user pm-difl">
                                            <a href="" class="pm_button pm-dbfl" id="pm-change-password" onclick="return false;">      
                                                <p>
                                                    <div  onclick="em_add_to_calendar('<?php echo $event_model->id ?>')"  id="addToCalendar" style="display: none;"  class="kf-event-add-calendar em_color dbfl">
                                                        <img class="kf-google-calendar-add" src="<?php echo esc_url(plugins_url('/images/gcal.png', __FILE__)); ?>">
                                                        <?php echo __("Add To Calendar",'eventprime-event-calendar-management'); ?>
                                                    </div>
                                                </p>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    <div class="pm-popup-mask"></div>
                                    <div id="pm-change-password-dialog">
                                        <div class="pm-popup-container">
                                            <div class="pm-popup-title pm-dbfl pm-bg-lt pm-pad10 pm-border-bt">
                                                <div class="title">
                                                    <?php echo __("Event Added",'eventprime-event-calendar-management'); ?>
                                                    <div class="pm-popup-close pm-difr">
                                                        <img src="<?php echo esc_url(plugins_url('/images/popup-close.png', __FILE__)); ?>"  height="24px" width="24px">
                                                    </div>
                                                </div>
                                                <div class="pm-popup-action pm-dbfl pm-pad10 pm-bg">
                                                    <div class="pm-login-box GCal-confirm-message">
                                                        <div class="pm-login-box-error pm-pad10" style="display:none;" id="pm_reset_passerror"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <?php if( ! empty( $event_model->start_date ) && ! empty( $event_model->end_date ) ) {?>
                                    <div id="authorize-button" class="kf-event-add-calendar em_color dbfl">
                                        <a class="em-events-gcal em-events-button em-color em-bg-color-hover em-border-color" href="https://www.google.com/calendar/event?action=TEMPLATE&text=<?php echo urlencode($event_model->name); ?>&dates=<?php echo gmdate('Ymd\\THi00', ($event_model->start_date)); ?>/<?php echo gmdate('Ymd\\THi00', ($event_model->end_date)); ?>&details=<?php echo urlencode($event_model->description).$venue_model->name; ?>" target="_blank">
                                            <img class="kf-google-calendar-add" src="<?php echo esc_url(plugins_url('/images/gcal.png', __FILE__)); ?>" />
                                            <?php _e("Add To Calendar",'eventprime-event-calendar-management'); ?>
                                        </a>
                                    </div><?php
                                }?>
                            <?php endif; ?>
                            <div class="ep-addto-calendar dbfl">
                                <div class="ep-ical-download em_color"  title="Download .ics file" onclick="em_get_ical_file(<?php echo $event_model->id; ?>)"><svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0z" fill="none"/><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/></svg> <?php _e('+ iCal Export','eventprime-event-calendar-management'); ?></div>
                            </div>
                        </div>
                    </div>
                    <?php if(!empty($event_model->enable_booking) && !empty($booking_allowed)): ?>
                        <div class="kf-event-attr difl">
                            <div class="kf-event-attr-name dbfl">
                                <?php echo __("Booking Starts",'eventprime-event-calendar-management'); ?>
                            </div>
                            <div class="kf-event-attr-value dbfl">  
                                <?php echo date_i18n(get_option('date_format').' '.get_option('time_format'),$event_model->start_booking_date); ?>
                            </div>
                            <div class="kf-event-attr-name dbfl">
                                <?php echo __("Booking Ends",'eventprime-event-calendar-management'); ?>
                            </div>
                            <div class="kf-event-attr-value dbfl">  
                                <?php echo  date_i18n(get_option('date_format').' '.get_option('time_format'),$event_model->last_booking_date); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php  
                    $capacity =  em_event_seating_capcity($event_model->id);
                    if(!empty($event_model->enable_booking) && empty($event_model->hide_booking_status) && !empty($capacity)):?>
                        <div class="kf-event-attr difl">
                            <div>
                                <div class="kf-event-attr-name dbfl">
                                    <?php _e('Booking Status','eventprime-event-calendar-management'); ?>
                                </div>
                                <div class="kf-event-attr-value dbfl">  
                                    <?php $sum = $event_service->booked_seats($event_model->id); ?>
                                    <?php echo $sum; ?> / <?php echo $capacity; ?> 
                                    <?php $width = ($sum / $capacity) * 100; ?>
                                    <div id="progressbar" class="em_progressbar dbfl">
                                        <div style="width:<?php echo $width . '%'; ?>" class="em_progressbar_fill em_bg" ></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if( ! empty( $venue_model->id ) ){ ?>
                    <div class="kf-event-attr difl">
                        <div class="kf-event-attr-name dbfl">
                            <?php echo __("Location",'eventprime-event-calendar-management'); ?>
                        </div>
                            <a href="<?php echo add_query_arg('venue',$event_model->venue,get_permalink($global_settings->venues_page)); ?>"><?php echo $venue_model->name; ?></a><br>
                        <?php 
                        if(!empty($venue_model->display_address_on_frontend)):
                            if(!empty($venue_model->address)):?>
                                <div class="kf-event-attr-value dbfl"><?php echo $venue_model->address; ?></div>
                            <?php else:
                                echo '<span class="kf_no_info">'.__("Event Site details are not available.",'eventprime-event-calendar-management').'</span>';
                            endif;
                        endif;?>
                    </div>
                    <?php } ?>
                    <?php
                    if($global_settings->show_qr_code_on_single_event == 1){?>
                        <div class="kf-event-attr difl">
                            <?php
                            $url = em_get_single_event_page_url($event_model, $global_settings);
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
                            <div class="ep-qrcode-details">
                                <img src="<?php echo $image_url; ?>" width="120" height="120" alt="<?php echo __('QR Code', 'eventprime-event-calendar-management'); ?>" />
                            </div>
                        </div><?php
                    }?>
                </div>
            </div>
            <!--Event Header Ends--->
            <div class="kf-event-content kf-event-row dbfl">
                <?php 
                $ages_group = em_get_term_meta($event_model->event_type, 'age_group', true);
                if($ages_group=='custom_group'){
                    $ages_group= em_get_term_meta($event_model->event_type, 'custom_group', true);
                }
                if ((!empty($ages_group) && $ages_group!=='all') || ($event_model->audience_notice) || $event_model->facebook_page ):?>
                    <!--start sidebar -->
                    <div class="kf-event-col2 em_bg difl">
                        <div class="kf-event-col-title em_bg"><?php _e('More Information','eventprime-event-calendar-management'); ?></div>
                        <?php if(!empty($ages_group) && $ages_group!=='all'): ?>
                            <div class="kf-event-attr dbfl">
                                <div class="kf-event-attr-name em_color dbfl">
                                    <?php echo __("Age Group", 'eventprime-event-calendar-management'); ?>
                                </div>
                                <div class="kf-event-attr-value dbfl">  
                                    <?php       
                                    if ($ages_group == "parental_guidance"){
                                        echo __('All ages but parental guidance', 'eventprime-event-calendar-management');
                                    }
                                    else{
                                        echo str_replace('-', ' '.__('to', 'eventprime-event-calendar-management').' ', $ages_group);  
                                    }?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php  if(!empty($event_model->audience_notice)):?>
                            <div class="kf-event-attr dbfl">
                                <div class="kf-event-attr-name em_color dbfl">
                                    <?php  echo __('Note', 'eventprime-event-calendar-management'); ?>
                                </div>
                                <div class="kf-event-attr-value dbfl">
                                    <?php  echo $event_model->audience_notice; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if(!empty($event_model->facebook_page)):?>
                            <div class="kf-event-attr dbfl">
                                <div class="kf-event-attr-name em_color dbfl">
                                    <?php  echo __('Facebook Page','eventprime-event-calendar-management'); ?>
                                </div>
                                <div class="kf-event-attr-value kf-fb-link dbfl">  
                                    <?php echo $event_model->facebook_page . "&nbsp; &nbsp;" . "<a target='_blank' href = ". $event_model->facebook_page . "><i class='fa fa-external-link' aria-hidden='true'></i></a>";?>              
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <!--End of Sidebar !-->
                <div class="kf-event-col1 difl">
                    <?php if(strlen($event_model->description)>0): ?>
                        <div class="kf-event-attr-name dbfl"><?php _e('Event Details', 'eventprime-event-calendar-management'); ?></div>
                        <span><?php echo do_shortcode(wpautop($event_model->description));?></span>
                    <?php endif; ?>
                    <?php
                    // Event Gallery section
                    $gallery_ids= $event_model->gallery_image_ids;     
                    if (is_array($gallery_ids) && !empty($gallery_ids)):?>       
                        <div class="kf-event-attr-name dbfl"><?php  echo __('Event Photos', 'eventprime-event-calendar-management'); ?></div>
                        <div class="kf-event-gallery dbfl">
                            <div class="em_photo_gallery dbfl">
                                <?php foreach ($gallery_ids as $id): ?>
                                    <a class="difl" rel="gal" href="<?php echo wp_get_attachment_url($id); ?>"><?php echo wp_get_attachment_image($id, array(100, 100)); ?> </a>
                                <?php endforeach; ?>
                            </div> 
                        </div>                   
                    <?php endif;?>
                </div>
            </div>
            <!---Event Content Ends--->
            <!-- Organizer details -->
            <?php
            if(empty($event_model->hide_organizer) && !empty($event_model->organizer)): 
                $organizer_text = em_global_settings_button_title('Organizer');?>
                <div class="kf-event-organizers kf-event-row dbfl">
                    <div class="kf-row-heading">
                        <span class="kf-row-title em_color">
                            <i class="fa fa-star-o" aria-hidden="true"></i>
                            <?php echo $organizer_text; ?>
                        </span>
                    </div>
                             
                    <?php if(!empty($event_model->organizer)):
                        if(is_serialized($event_model->organizer)):
                          $event_model->organizer = unserialize($event_model->organizer); 
                        endif;
                        $orn_sin_class = count($event_model->organizer) == 1 ? 'organizer_single' : '';
                        foreach($event_model->organizer as $organizer_id):
                            $organizer_detail = $organizer_service->get_organizer($organizer_id);
                            if(!empty($organizer_detail)):
                                $organizers_page_url = get_permalink(em_global_settings("event_organizers"));?>
                                <div class="kf-organizer-card difl <?php echo esc_attr($orn_sin_class);?>">
                                    <div class="kf-organizer-img difl"><?php
                                        $thumbImage = esc_url(plugins_url('/images/dummy-performer.png', __FILE__));
                                        if (!empty($organizer_detail->image_id)):
                                            $thumbImage = wp_get_attachment_image_src($organizer_detail->image_id, 'large')[0];
                                        endif;?>
                                        <a href="<?php echo add_query_arg("organizer", $organizer_id, $organizers_page_url); ?>">
                                            <img src="<?php echo $thumbImage; ?>" alt="<?php echo $organizer_text; _e(' Photo', 'eventprime-event-calendar-management');?>">
                                        </a>
                                    </div>
                                    <div class="kf-organizer-details difl"><?php
                                        if(!empty($organizer_detail->name)): ?>
                                            <div class="kf-event-attr-value dbfl">
                                                <div class="ep-organizer-name-label">
                                                    <?php 
                                                    if(empty($orn_sin_class)){
                                                        echo '<i class="fa fa-user" aria-hidden="true"></i>';
                                                    }
                                                    else{
                                                        echo __("Name",'eventprime-event-calendar-management');
                                                    }?>
                                                </div>
                                                <div class="ep-organizer dbfl">
                                                    <?php echo $organizer_detail->name; ?>
                                                </div>
                                            </div>
                                        <?php endif;
                                        if(!empty($organizer_detail->organizer_phones)): ?>
                                            <div class="kf-event-attr-value dbfl">
                                                <div class="ep-organizer-tel-label">
                                                    <?php
                                                    if(empty($orn_sin_class)){
                                                        echo '<i class="fa fa-phone" aria-hidden="true"></i>';
                                                    }
                                                    else{
                                                        echo __("Phone",'eventprime-event-calendar-management');
                                                    }?>
                                                </div>
                                                <div class="ep-organizer-tel dbfl">
                                                    <?php echo implode(', ',$organizer_detail->organizer_phones); ?>
                                                </div>
                                            </div>
                                        <?php endif;
                                        if(!empty($organizer_detail->organizer_emails)): ?>
                                            <div class="kf-event-attr-value dbfl">
                                                <div class="ep-organizer-email-label">
                                                    <?php
                                                    if(empty($orn_sin_class)){
                                                        echo '<i class="fa fa-envelope-o" aria-hidden="true"></i>';
                                                    }
                                                    else{
                                                        echo __("Email",'eventprime-event-calendar-management');
                                                    }?>
                                                </div>
                                                <div class="ep-organizer-email dbfl">
                                                    <?php foreach($organizer_detail->organizer_emails as $key => $val) {
                                                        $organizer_detail->organizer_emails[$key] = '<a href="mailto:'.$val.'">'.htmlentities($val).'</a>';
                                                    }
                                                    echo implode(', ',$organizer_detail->organizer_emails);?>
                                                </div>
                                            </div>
                                        <?php endif;
                                        if(!empty($organizer_detail->organizer_websites)): ?>
                                            <div class="kf-event-attr-value dbfl">
                                                <div class="ep-organizer-website-label">
                                                    <?php
                                                    if(empty($orn_sin_class)){
                                                        echo '<i class="fa fa-globe" aria-hidden="true"></i>';
                                                    }
                                                    else{
                                                        echo __("Website",'eventprime-event-calendar-management');
                                                    }?>
                                                </div>
                                                <div class="ep-organizer-website dbfl">
                                                    <?php 
                                                     foreach($organizer_detail->organizer_websites as $key => $val) {
                                                        if(!empty($val)){
                                                            $organizer_detail->organizer_websites[$key] = '<a href="'.$val.'" target="_blank">'.htmlentities($val).'</a>';
                                                        }
                                                    }
                                                    echo implode(', ',$organizer_detail->organizer_websites); ?>
                                                </div>
                                            </div><?php
                                        endif;?>
                                    </div>
                                </div><?php
                            endif;
                        endforeach;
                    endif;?>
                </div>
            <?php endif; ?>

            <!-- Performer's details -->
            <?php  if(!empty($event_model->enable_performer) && !empty($event_model->performer)): 
                $performer_text = em_global_settings_button_title('Performer');?>
                <div class="kf-event-performers kf-event-row dbfl">
                    <div class="kf-row-heading">
                        <span class="kf-row-title em_color"><i class="fa fa-star-o" aria-hidden="true"></i> <?php  echo $performer_text; ?></span>
                    </div>
                    <?php 
                    if(!empty($event_model->match) && count($event_model->performer) > 1): ?>
                        <div class="kf-match-performers"> <span class="em_bg"><?php _e('VS','eventprime-event-calendar-management'); ?></span></div>
                    <?php endif;
                    foreach ($event_model->performer as $id):  
                        $performer= $performer_service->load_model_from_db($id);
                        $show_performer = get_post_meta($id, 'em_display_front', true);
                        if (!empty($performer->id) && ($show_performer == 'true' || $show_performer == 1) ):?> 
                            <div class="kf-performer-card difl">
                                <div class="kf-performer-img difl">
                                    <?php if (!empty($performer->feature_image_id)): ?>
                                        <a href="<?php echo get_permalink($id); ?>"><?php echo get_the_post_thumbnail($id, 'full'); ?></a>
                                    <?php else : ?>
                                        <a href="<?php echo get_permalink($id); ?>"><img src="<?php echo esc_url(plugins_url('/images/dummy-performer.png', __FILE__)) ?>" alt="Dummy Image" class="em-no-image"></a>
                                    <?php endif; ?>
                                </div>
                                <div class="kf-performer-details difl">
                                    <div class="kf-performer-name dbfl em_wrap" title="<?php echo $performer->name; ?>">
                                        <a href="<?php echo add_query_arg('performer',$performer->id,get_permalink($global_settings->performers_page)); ?>" target="_blank" style="padding-left:0;"><?php echo $performer->name; ?></a>         
                                    </div>
                                    <?php
                                    $role = em_get_post_meta($id, 'role', true);
                                    if (!empty($role)):?>
                                        <div class="kf-performer-role dbfl em_wrap" title="<?php echo em_get_post_meta($id, 'role', true); ?>">
                                            <?php echo em_get_post_meta($id, 'role', true); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="kf-performer-description dbfl">
                                        <span class="ep-performer-description" style="width:100%;text-align:center;">
                                            <?php if (!empty($performer->description)) echo wp_trim_words($performer->description, 6); else _e( $performer_text. ' details are not available', 'eventprime-event-calendar-management'); ?>
                                        </span>
                                    </div>
                                </div>
                            </div><?php
                        endif;
                    endforeach;?>       
                </div>
            <?php endif; ?>
            <?php if( ! empty( $venue_model->id ) ){ ?>
            <div class="kf-event-venue-area dbfl">
                <div class="kf-event-performers kf-event-row dbfl">
                    <div class="kf-row-heading">
                        <span class="kf-row-title em_color"><i class="fa fa-map-o" aria-hidden="true"></i><?php  echo __('Location', 'eventprime-event-calendar-management'); ?></span>
                    </div>
                    <?php $gmap_api_key= em_global_settings('gmap_api_key');
                    if (!empty($gmap_api_key) && !empty($venue_model->address)): ?>
                        <div class="kf-event-venue-map dbfl">
                            <div>
                                <div data-venue-id="<?php echo $venue_model->id; ?>" id="em_event_map_canvas" style="height: 400px;"></div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="kf-event-venue-details dbfl">
                <div class="kf-event-venue-name dbfl">
                    <?php 
                    // Venue images
                    $gallery_ids = $venue_model->gallery_images;
                    if (!empty($gallery_ids) && is_array($gallery_ids)):?>                                            
                        <div class="em_venue_images em_block dbfl">
                            <?php
                            for ($i = 0; $i < 3; $i++):
                                if (isset($gallery_ids[$i])):
                                    echo wp_get_attachment_image($gallery_ids[$i], 'thumbnail');
                                else:
                                    break;
                                endif;
                            endfor;?>   
                        </div>
                    <?php endif; // Venue images ends here ?>
                    <?php if (!empty($venue_model->name)): ?>  
                        <i class="fa fa-map-marker" aria-hidden="true"></i>
                        <?php echo $venue_model->name;?>  <a href="<?php echo add_query_arg('venue',$venue_model->id,get_permalink($global_settings->venues_page)); ?>" target="_blank"><?php echo '<i class="fa fa-external-link" aria-hidden="true"></i>'.'<br>'?></a>                  
                    <?php endif;?>
                </div>
                <?php 
                if(!empty($venue_model->display_address_on_frontend)):
                    if(!empty($venue_model->address)): ?>
                        <div class="kf-event-venue-address dbfl">
                            <?php echo $venue_model->address; ?>
                        </div>
                    <?php else:
                        echo '<span class="kf_no_info">'.__("Event Site details are not available.",'eventprime-event-calendar-management').'</span>';
                    endif; 
                endif;?>
                <?php  if(!empty($venue_model->seating_capacity)): ?>
                    <div class="kf-event-venue-capacity em_color dbfl">   
                        <?php echo __("Can hold",'eventprime-event-calendar-management').' '.$venue_model->seating_capacity .' '.__("People",'eventprime-event-calendar-management'); ?>
                    </div> 
                <?php endif;?>
                <?php if(!empty($venue_model->address)): ?>
                    <div class="kf-event-venue-markers dbfl em_color">
                        <a target="blank" href='https://www.google.com/maps?saddr=My+Location&daddr=<?php echo urlencode($venue_model->address); ?>&dirflg=w'>
                            <i class="fa fa-male" aria-hidden="true"></i>
                        </a>
                        <a target="blank" href='https://www.google.com/maps?saddr=My+Location&daddr=<?php echo urlencode($venue_model->address); ?>&dirflg=d'>
                            <i class="fa fa-car" aria-hidden="true"></i>
                        </a>
                        <a target="blank" href='https://www.google.com/maps?saddr=My+Location&daddr=<?php echo urlencode($venue_model->address); ?>&dirflg=r'>
                            <i class="fa fa-bus" aria-hidden="true"></i>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (!empty($venue_model->description)): ?>
                <div class="kf-event-venue-description difl" <?php if (count($upcoming_events) <= 1) echo 'style="width:100%;"'; ?>>
                    <?php echo do_shortcode(wp_trim_words(wpautop($venue_model->description), 50, '...')); ?>                             
                    <a href="<?php echo add_query_arg('venue',$venue_model->id,get_permalink($global_settings->venues_page)); ?>" target="_blank"><?php echo '<i class="fa fa-arrow-circle-right" aria-hidden="true"></i>'.'<br>'?></a>                  
                </div> 
            <?php endif; } ?>
            <?php 
            $hide_upcoming_events = em_global_settings( 'shortcode_hide_upcoming_events' );
            if( empty( $hide_upcoming_events ) ) {
                if (count($upcoming_events) > 1) { ?>
                    <div class="kf-event-venue-events em_bg difl">
                        <div class="kf-event-attr-name em_bg dbfl">
                            <i class="fa fa-clock-o" aria-hidden="true"></i>
                            <?php _e('Upcoming Events Here', 'eventprime-event-calendar-management');?>
                        </div>
                        <div class="kf-upcoming-events dbfl">
                            <?php 
                            $i = 1;
                            foreach ($upcoming_events as $index=>$ev):
                                if($ev->id==$event_id){
                                    unset($upcoming_events[$index]);
                                    continue;
                                }
                                $emstyle = '';
                                if($i > 5){
                                    $emstyle = 'style="display:none;"';
                                }?>
                                <div class="dbfl" id="em-upcoming-<?php echo $i;?>" <?php echo $emstyle;?>>
                                    <a href="<?php echo add_query_arg('event', $ev->id, get_page_link($global_settings->events_page)); ?>" target="_blank"><?php echo $ev->name; ?></a>
                                    <?php if(current_time('timestamp') > $ev->start_date && current_time('timestamp') <$ev->end_date): ?>
                                       <span class="kf-live em_color"><?php echo __('Live', 'eventprime-event-calendar-management');?></span>
                                    <?php endif; ?>
                                </div>
                            <?php $i++; endforeach; ?>
                            <?php if(count($upcoming_events) > 5){?>
                                <div class="em-upcoming-event-load-more dbfl">
                                    <a class="ep-load-more" id="em-upcoming-event-load-more" data-total_count="<?php echo count($upcoming_events);?>" data-current_count="5"><?php echo __('Load More', 'eventprime-event-calendar-management');?></a>
                                </div><?php
                            }?>
                        </div> 
                    </div><?php 
                }
            } ?>
            <?php do_action('event_magic_attendees_list',$event_model); ?>
            <?php do_action('event_magic_event_sponsers',$event_model); ?>
            <?php 
            add_filter('comments_array', '__return_empty_array', 10, 2);
            do_action('event_magic_event_comments_list',$event_model);
            if ( ! empty( $enable_seo_urls ) && empty(event_m_get_param('event'))) {
                do_action('event_magic_show_comment_template_for_seo_urls');
            }?>
        </div>
        <?php do_action('em_event_aftetr_main_content'); ?>
    </div>
    <?php
    if(!empty($enable_seo_urls)){?>
        <div class="emagic-sidebar <?php echo $ep_seo_side_class;?>">
            <?php dynamic_sidebar();?>
        </div><?php
    }?>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function(event) { 
        $ = jQuery;
        em_width_adjust(".em_performer");
        $(this).prop("disabled", true);

        $("#em-upcoming-event-load-more").click(function(){
            var total_count = $(this).data('total_count');
            var current_count = $(this).data('current_count');
            if(current_count < total_count){
                for(var i = 1; i < 6 ; i++){
                    ++current_count;
                    if($("#em-upcoming-"+current_count).length > -1){
                        $("#em-upcoming-"+current_count).show();
                        $("#em-upcoming-event-load-more").data('current_count', current_count);
                    }
                    if(current_count == total_count){
                        $(".em-upcoming-event-load-more").hide();
                        return false;
                    }
                }
            }
        });
    });
</script>

<?php 
if ( ! empty( $enable_seo_urls ) ) {
    get_footer();
}