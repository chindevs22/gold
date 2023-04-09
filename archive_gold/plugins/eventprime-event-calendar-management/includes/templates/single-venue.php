<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
// check if pretty seo urls enabled from global settings
$enable_seo_urls = em_global_settings('enable_seo_urls');
$ep_seo_full_class = $ep_seo_side_class = '';
if ( ! empty( $enable_seo_urls ) && ! isset( $venue_id ) ) {
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
$paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;

if ( isset($atts['id']) ){
    $service = EventM_Factory::get_service('EventM_Venue_Service');
    $venue = $service->load_model_from_db($atts['id']);
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
}
/* shortcodes start */
if(isset($atts['event_style'])){
    $event_display_style = $atts["event_style"];
}else{
    $event_display_style = $global_settings->single_venue_event_display_view;
}
if(isset($atts['event_limit'])){
    $single_venue_event_limit = ( $atts["event_limit"] == 0 || $atts["event_limit"] == '' ) ? EM_PAGINATION_LIMIT : $atts["event_limit"];
}else{
    $single_venue_event_limit = ($global_settings->single_venue_event_limit == 0) ? EM_PAGINATION_LIMIT : $global_settings->single_venue_event_limit;
}
if(isset($atts['event_cols'])){
    $event_cols = ep_check_column_size( $atts['event_cols'] );
}else{
    $event_cols = ep_check_column_size( $global_settings->single_venue_event_column );
}
if(isset($atts['load_more'])){
    $load_more = $atts["load_more"];
}else{
    $load_more = $global_settings->single_venue_event_load_more;
}
if(isset($atts['show_events'])){
    $single_venue_show_events = $atts['show_events'];
}else{
    $single_venue_show_events = $global_settings->single_venue_show_events;
}
if(isset($atts['hide_past_events'])){
    $hide_past_events = $atts['hide_past_events'];
}else{
    $hide_past_events = $global_settings->single_venue_hide_past_events;
}
/* shortcode end */
?>
<div class="emagic">
    <div class="ep-venue-page-container">
        <div class="ep-container <?php echo esc_attr($class);?> <?php echo esc_attr($ep_seo_full_class);?>" id="<?php echo esc_attr($sec_unique);?>">
            <?php if ( ! empty( $enable_seo_urls ) && ! isset( $venue_id )  ) {?>
                <div class="em-single-header-area">
                    <h1><?php echo esc_html($term->name);?></h1>
                </div><?php
            }?>
            <div class="ep-single-venue-wrapper">
                <div class="ep-box-wrap ep-venue-details-info-wrap">
                    <div class="ep-box-row">
                        <div class="ep-box-col-2">
                            <div class="ep-single-box-thumb">
                                <div class="ep-single-figure-box">
                                    <img src="<?php if ( ! empty( $venue->gallery_images ) ) echo wp_get_attachment_image_src($venue->gallery_images[0], 'full' )[0]; else echo esc_url( plugins_url( '/images/dummy_image.png', __FILE__ ) ); ?>" alt="<?php _e( 'Event Site/Location Image', 'eventprime-event-calendar-management' ); ?>" <?php if ( empty( $venue->image_id ) ): echo 'class="em-no-image"'; endif; ?>>
                                </div>
                            </div>
                        </div>
                        <div class="ep-box-col-10">
                            <div class="ep-single-box-info">
                                <div class="ep-single-box-content">
                                        <div class="ep-single-box-title-info">
                                            <h3 class="ep-single-box-title ep-venue-name" title="<?php echo $venue->name; ?>">
                                                <?php echo $venue->name; ?>
                                            </h3>
                                            <ul class="ep-single-box-details-meta">
                                                <li> 
                                                    <?php if ($venue->established): ?>                                       
                                                                <?php echo '<div class="kf-event-attr-name em_color">' . __('Established', 'eventprime-event-calendar-management') . ': </div><div class="kf-event-attr-value">' . date_i18n(get_option('date_format'),$venue->established) . '</div>'; ?>
                                                    <?php endif; ?>
                                                </li>
                                                <li>
                                                <?php
                                                    if ($venue->type == 'standings'){?>
                                                        <div class="kf-event-attr-name em_color"><?php _e('Type', 'eventprime-event-calendar-management'); ?></div>
                                                        <div class="kf-event-attr-value dbfl"><?php _e('Standing', 'eventprime-event-calendar-management'); ?></div>
                                                    <?php }else{ ?>
                                                        <div class="kf-event-attr-name em_color"><?php _e('Capacity', 'eventprime-event-calendar-management'); ?></div>
                                                        <div class="kf-event-attr-value"> 
                                                            <?php echo $venue->seating_capacity . ' ' . __('People', 'eventprime-event-calendar-management'); ?>
                                                        </div>
                                                    <?php } ?>
                                                </li>
                                                <li>
                                                <?php if (!empty($venue->seating_venue)){ ?>
                                                    <div class="venue_det">
                                                        <?php echo '<div class="kf-event-attr-name em_color">' . __('Coordinator', 'eventprime-event-calendar-management') . ': </div><div class="kf-event-attr-value dbfl"> ' . $venue->seating_venue . '</div>'; ?>
                                                    </div>
                                                <?php } ?>
                                                </li>
                                                <li>
                                                    <?php if (!empty($venue->address)){ ?>
                                                        <div class="kf-venue-address">
                                                            <?php echo $venue->address; ?>
                                                            <span class="ep-vanue-directions">
                                                                <a target="blank" href='https://www.google.com/maps?saddr=My+Location&daddr=<?php echo urlencode($venue->address); ?>&dirflg=w'>
                                                                    <?php _e('Directions', 'eventprime-event-calendar-management'); ?> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M288 32c-17.7 0-32 14.3-32 32s14.3 32 32 32h50.7L169.4 265.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L384 141.3V192c0 17.7 14.3 32 32 32s32-14.3 32-32V64c0-17.7-14.3-32-32-32H288zM80 64C35.8 64 0 99.8 0 144V400c0 44.2 35.8 80 80 80H336c44.2 0 80-35.8 80-80V320c0-17.7-14.3-32-32-32s-32 14.3-32 32v80c0 8.8-7.2 16-16 16H80c-8.8 0-16-7.2-16-16V144c0-8.8 7.2-16 16-16h80c17.7 0 32-14.3 32-32s-14.3-32-32-32H80z"/></svg>
                                                                </a>
                                                            </span>
                                                        </div>
                                                    <?php } ?>
                                                </li>
                                            </ul>
                                        </div>
                                        <?php if ( isset( $venue->facebook_page ) && !empty( $venue->facebook_page ) ){ 
                                                echo '<div class="ep-single-box-social"><a href="'.$venue->facebook_page.'" target="_blank" title="Facebook" class="ep-facebook-f"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"> <path d="M504 256C504 119 393 8 256 8S8 119 8 256c0 123.78 90.69 226.38 209.25 245V327.69h-63V256h63v-54.64c0-62.15 37-96.48 93.67-96.48 27.14 0 55.52 4.84 55.52 4.84v61h-31.28c-30.8 0-40.41 19.12-40.41 38.73V256h68.78l-11 71.69h-57.78V501C413.31 482.38 504 379.78 504 256z"/></svg></a></div>';
                                        } ?>   
                                        <div class="ep-single-box-summery">
                                            <p class="ep-single-box-desc"><?php if (isset($venue->description) && $venue->description !== '') echo do_shortcode($venue->description); else _e('No desciption available','eventprime-event-calendar-management'); ?></p>
                                        </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <!-- single venue map -->
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

        <!-- single venue gallery images -->
        <?php if (is_array($venue->gallery_images) && count($venue->gallery_images) > 1){ ?>
            <div class="em_photo_gallery em-single-venue-photo-gallery dbfl" >
                <div class="kf-row-heading">
                    <span class="kf-row-title"><?php _e('Gallery', 'eventprime-event-calendar-management'); ?></span>
                </div>
                <?php foreach ($venue->gallery_images as $id) { ?>
                    <a rel="gal" href="<?php echo wp_get_attachment_url($id); ?>"><?php echo wp_get_attachment_image($id, array(50, 50)); ?> </a>
                <?php } ?>  
            </div>
        <?php } ?>

        <!-- single venue related events -->
        <?php if( $single_venue_show_events == 1 ){
        $event_args  = new stdClass();
        $event_args->event_style = $event_display_style;
        $event_args->event_limit = $single_venue_event_limit;
        $event_args->event_cols = $event_cols;
        $event_args->load_more = $load_more;
        $event_args->hide_past_events = $hide_past_events;

            if( $event_display_style == 'card' || $event_display_style == 'list' || $event_display_style == 'mini-list' ){ ?>
                <div class="emagic <?php echo $class;?>" id="<?php echo $sec_unique;?>">
                    <!-- .content-area starts-->
                    <div id="em_primary" class="em_content_area">
                        <div id="post-<?php echo $venue->id; ?>">
                            <?php        
                                $args = array(
                                    'orderby' => em_append_meta_key('start_date'),
                                    'posts_per_page' => $single_venue_event_limit,
                                    'offset'=> (int) ($paged-1) * $single_venue_event_limit,
                                    'paged' => $paged,
                                );
                                $args['post_status'] = !empty($hide_past_events) == 1 ? 'publish' : 'any';

                                $upcoming_events = $event_service->upcoming_events_for_venue( $venue->id, $args ); 
                                $event_service->print_upcoming_event_block_for_venues( $upcoming_events, $event_args );
                            ?>
                        </div>
                    </div>
                </div>
        <?php }
        } ?>

    <?php
        if(!empty($enable_seo_urls) && ! isset( $venue_id ) ){?>
            <div class="emagic-sidebar <?php echo $ep_seo_side_class;?>">
                <?php dynamic_sidebar();?>
            </div><?php
        }?>    
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function(event) {
        em_load_map_nws('single_venue', 'em_single_venue_map_canvas<?php echo $section_id;?>');
    });
   /*  jQuery(document).ready(function () {
            $ = jQuery;
            $(".em_photo_gallery a").colorbox({width: "75%", height: "75%"});
        }); */
</script>
<?php 
if ( ! empty( $enable_seo_urls ) ) {
    get_footer();
}