<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// check if pretty seo urls enabled from global settings
$enable_seo_urls = em_global_settings('enable_seo_urls');
$ep_seo_full_class = $ep_seo_side_class = '';
if ( ! empty( $enable_seo_urls ) ) {
    // if pretty url enabled then load header and footers
    get_header();
    $term = get_queried_object();
    $venue_id = $term->term_id;
    $service = EventM_Factory::get_service('EventM_Venue_Service');
    $venue = $service->load_model_from_db($venue_id);
    $ep_seo_full_class = 'ep-single-page';
    $ep_seo_side_class = 'ep-event-sidebar';
}

wp_enqueue_script('em-public');
wp_enqueue_style('em-public-css');
em_localize_map_info();
wp_enqueue_script('jquery-colorbox');
wp_enqueue_style('em-colorbox-css');
$setting_service = EventM_Factory::get_service('EventM_Setting_Service');
$global_settings= $setting_service->load_model_from_db();
$gmap_api_key = em_global_settings('gmap_api_key');
$events_page_id = em_global_settings("events_page");
$form_action = !empty($events_page_id) ? get_permalink($events_page_id) : "";
$booking_page_id = em_global_settings('booking_page');
$event_service = EventM_Factory::get_service('EventM_Service');
$today = em_current_time_by_timezone();
$currency_symbol= em_currency_symbol();
$showBookNowForGuestUsers = em_show_book_now_for_guest_users();
if (empty($venue->id)){
    return;
}
$class = $section_id = $sec_unique = '';
$page = true;
if(isset($atts['section_id'])){
    $section_id = $atts['section_id'];
    $sec_unique = 'section-venue-'.$section_id;
    $page = false;
}
if(isset($atts['class'])){
    $class = $atts['class'];
}?>
<div class="ep-main-container">
    <div class="emagic emagic_archive <?php echo esc_attr($class);?> <?php echo esc_attr($ep_seo_full_class);?>" id="<?php echo esc_attr($sec_unique);?>">
        <?php if ( ! empty( $enable_seo_urls ) ) {?>
            <div class="em-single-header-area">
                <h1><?php echo esc_html($term->name);?></h1>
            </div><?php
        }?>
        <!-- .content-area starts-->
        <div id="em_primary" class="em_content_area <?php if (!is_active_sidebar('em_right_sidebar-1')) echo 'em_single_fullwidth'; ?>">
            <?php $events = $service->get_upcoming_events($venue->id); ?>    
            <div class="em_venue_image em_block dbfl">
                <!-- Div Display First image  from Gallery of Venues -->
                <div class="em_cover_image">
                    <?php
                    if (!empty($venue->gallery_images)) {
                        echo wp_get_attachment_image($venue->gallery_images[0], 'full');
                    } else {
                        echo '<img src="' . esc_url(plugins_url('/images/dummy_image.png', __FILE__)) . '"  class="em-venue-dummy-cover em-no-image">';
                    }?>
                    <div class="kf-event-single-venue-sidebar difl em_bg ">
                        <div class="kf-event-col-title em_bg"><?php _e('Details', 'eventprime-event-calendar-management'); ?></div>
                        <div class="kf-event-attr-wrap dbfl">
                            <?php if ($venue->established): ?>
                                <div class="kf-event-attr dbfl">
                                    <div class="organizer_det">
                                        <?php echo '<div class="kf-event-attr-name em_color dbfl">' . __('Established', 'eventprime-event-calendar-management') . ': </div><div class="kf-event-attr-value dbfl">' . date_i18n(get_option('date_format'),$venue->established) . '</div>'; ?>
                                    </div> 
                                </div>
                            <?php endif; ?>
                            <div class="kf-event-attr dbfl">
                                <?php
                                if ($venue->type == 'standings'):?>
                                    <div class="kf-event-attr-name em_color dbfl"><?php _e('Type', 'eventprime-event-calendar-management'); ?></div>
                                    <div class="kf-event-attr-value dbfl"><?php _e('Standing', 'eventprime-event-calendar-management'); ?></div>
                                <?php else: ?>
                                    <div class="kf-event-attr-name em_color dbfl"><?php _e('Capacity', 'eventprime-event-calendar-management'); ?></div>
                                    <div class="kf-event-attr-value dbfl"> 
                                        <?php echo $venue->seating_capacity . ' ' . __('People', 'eventprime-event-calendar-management'); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="kf-event-attr dbfl">
                                <?php if (!empty($venue->seating_organizer)): ?>
                                    <div class="organizer_det">
                                        <?php echo '<div class="kf-event-attr-name em_color dbfl">' . __('Coordinator', 'eventprime-event-calendar-management') . ': </div><div class="kf-event-attr-value dbfl"> ' . $venue->seating_organizer . '</div>'; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="kf-event-attr dbfl">
                                <?php if ($venue->facebook_page): ?>  
                                    <div class="kf-event-attr-name em_color dbfl"><?php _e('Facebook', 'eventprime-event-calendar-management'); ?></div>
                                    <div class="kf-event-attr-value kf-fb-link dbfl dbfl"><?php echo $venue->facebook_page; ?><a target='_blank' href="<?php echo $venue->facebook_page; ?>"> <i class='fa fa-external-link' aria-hidden='true'></i></a></div>
                                <?php endif; ?>
                            </div>
                        </div>
                   </div>        
                </div>
                <?php if (!empty($venue->name)): ?>
                    <div class="kf-single-venue-post-title dbfl ">
                        <?php echo $venue->name; ?>
                    </div>
                <?php endif; ?>
                <!---Venue Address----->
                <?php if (!empty($venue->address)): ?>
                    <div class="kf-venue-address dbfl">
                        <?php echo $venue->address; ?>
                        <span class="kf-vanue-directions">
                            <a target="blank" href='https://www.google.com/maps?saddr=My+Location&daddr=<?php echo urlencode($venue->address); ?>&dirflg=w'>
                                <?php _e('Directions', 'eventprime-event-calendar-management'); ?>
                            </a>
                        </span>
                    </div>
                <?php endif; ?>
                <!---End Venue Address----->
            </div>
            <div  class="ep-venue-description dbfl">
                <?php if (!empty($venue->description)): ?>                    
                    <div class="em_venue_desc dbfl">
                        <?php echo do_shortcode($venue->description); ?>
                    </div>
                <?php else: ?>
                    <div class="em_no_venue_desc dbfl"> 
                        <?php _e('Venue description not available.', 'eventprime-event-calendar-management'); ?>
                    </div>
                <?php endif; ?>                            
            </div>
                
            <?php if (!empty($gmap_api_key) && !empty($venue->address)): ?>
                <div class="ep-single-venue-map dbfl">
                    <div class="em-venue-direction">
                        <div class="kf-row-heading">
                            <span class="kf-row-title"><?php _e('Map', 'eventprime-event-calendar-management'); ?></span>
                        </div>
                        <div data-venue-id="<?php echo $venue->id; ?>" id="em_single_venue_map_canvas<?php echo $section_id;?>" style="max-height: 300px; height: 100%"></div>
                    </div> 
                </div>
            <?php endif; ?>        
            <?php if (is_array($venue->gallery_images) && count($venue->gallery_images) > 1): ?>
                <div class="em_photo_gallery em-single-venue-photo-gallery dbfl" >
                    <div class="kf-row-heading">
                        <span class="kf-row-title"><?php _e('Gallery', 'eventprime-event-calendar-management'); ?></span>
                    </div>
                    <?php foreach ($venue->gallery_images as $id): ?>
                        <a rel="gal" href="<?php echo wp_get_attachment_url($id); ?>"><?php echo wp_get_attachment_image($id, array(50, 50)); ?> </a>
                    <?php endforeach; ?>  
                </div>
            <?php endif;
            if(empty($hide_upcoming_events)){
                $event_service->print_upcoming_event_block($events);
            }?>
        </div>
    </div>
    <?php
    if(!empty($enable_seo_urls)){?>
        <div class="emagic-sidebar <?php echo $ep_seo_side_class;?>">
            <?php dynamic_sidebar();?>
        </div><?php
    }?>
    <script>
        jQuery(document).ready(function () {
            $ = jQuery;
            $(".em_photo_gallery a").colorbox({width: "75%", height: "75%"});
            //em_load_map('single_venue', 'em_single_venue_map_canvas');
            em_load_map_nws('single_venue', 'em_single_venue_map_canvas<?php echo $section_id;?>');
        });
        jQuery("#em-upcoming-event-load-more").click(function(){
            var total_count = jQuery(this).data('total_count');
            var current_count = jQuery(this).data('current_count');
            if(current_count < total_count){
                for(var i = 1; i < 6 ; i++){
                    ++current_count;
                    if(jQuery("#em-upcoming-"+current_count).length > -1){
                        jQuery("#em-upcoming-"+current_count).show();
                        jQuery("#em-upcoming-event-load-more").data('current_count', current_count);
                    }
                    if(current_count == total_count){
                        jQuery(".em-upcoming-event-load-more").hide();
                        return false;
                    }
                }
            }
        });
    </script>
    <?php 
    if ( ! empty( $enable_seo_urls ) ) {
        get_footer();
    }