<?php 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
// check if pretty seo urls enabled from global settings
$ep_seo_full_class = $ep_seo_side_class = '';
$enable_seo_urls = em_global_settings('enable_seo_urls');
if ( ! empty( $enable_seo_urls ) && ! isset( $type_id ) ) {
    // if pretty url enabled then load header and footers
    get_header();
    $term = get_queried_object();
    $type_id = $term->term_id;
    $event_type_service = EventM_Factory::get_service('EventTypeM_Service');
    $type = $event_type_service->load_model_from_db($type_id);
    $ep_seo_full_class = 'ep-single-page';
    $ep_seo_side_class = 'ep-event-sidebar';
} 
wp_enqueue_style('em-public-css'); 
wp_enqueue_script('em-public');
$event_service = EventM_Factory::get_service('EventM_Service');
$setting_service = EventM_Factory::get_service('EventM_Setting_Service');
$global_settings = $setting_service->load_model_from_db();
$currency_symbol = em_currency_symbol();
$type->count = $event_service->event_count_by_type($type->id);
$events = $event_service->events_by_type($type->id);
$today = em_current_time_by_timezone();
$showBookNowForGuestUsers = em_show_book_now_for_guest_users();
$class = $section_id = $sec_unique = '';
$page = true;
if(isset($atts['section_id'])){
    $section_id = $atts['section_id'];
    $sec_unique = 'section-event-type-'.$section_id;
    $page = false;
}
if(isset($atts['class'])){
    $class = $atts['class'];
}
/* shortcodes start */
if(isset($atts['event_style'])){
    $event_display_style = $atts["event_style"];
}else{
    $event_display_style = $global_settings->single_type_event_display_view;
}
if(isset($atts['event_limit'])){
    $single_type_event_limit = ( $atts["event_limit"] == 0 || $atts["event_limit"] == '' ) ? EM_PAGINATION_LIMIT : $atts["event_limit"];
}else{
    $single_type_event_limit = ($global_settings->single_type_event_limit == 0) ? EM_PAGINATION_LIMIT : $global_settings->single_type_event_limit;
}
if(isset($atts['event_cols'])){
    $event_cols = ep_check_column_size( $atts['event_cols'] );
}else{
    $event_cols = ep_check_column_size( $global_settings->single_type_event_column );
}
if(isset($atts['load_more'])){
    $load_more = $atts["load_more"];
}else{
    $load_more = $global_settings->single_type_event_load_more;
}
if(isset($atts['show_events'])){
    $single_type_show_events = $atts['show_events'];
}else{
    $single_type_show_events = $global_settings->single_type_show_events;
}
if(isset($atts['hide_past_events'])){
    $hide_past_events = $atts['hide_past_events'];
}else{
    $hide_past_events = $global_settings->single_type_hide_past_events;
}
/* shortcode end */
$paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;?>
<div class="ep-performer-page-container">
    <div class="ep-container <?php echo esc_attr($class);?> <?php echo esc_attr($ep_seo_full_class);?>" id="<?php echo esc_attr($sec_unique);?>">
        <?php if ( ! empty( $enable_seo_urls ) && ! isset( $type_id ) ) {?>
            <div class="em-single-header-area">
                <h1> <?php echo esc_html($term->name);?></h1>
            </div><?php
        }?>
        <div class="ep-single-performer-wrapper">
            <div class="ep-box-wrap ep-performer-details-info-wrap">
                <div class="ep-box-row">
                    <div class="ep-box-col-2">
                        <div class="ep-single-box-thumb">
                            <div class="ep-single-figure-box">
                                <img src="<?php if (isset($type->image_id)) echo wp_get_attachment_image_src($type->image_id, 'large')[0]; else echo esc_url(EM_BASE_FRONT_IMG_URL.'dummy_image.png'); ?>" alt="<?php _e('Event Type Image', 'eventprime-event-calendar-management'); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="ep-box-col-10">
                        <div class="ep-single-box-info">
                            <div class="ep-single-box-content">
                                <div>
                                    <div class="ep-single-box-title-info">
                                        <h3 class="ep-single-box-title ep-performer-name" title="<?php echo $type->name; ?>"><?php echo $type->name; ?></h3>
                                        <p class="ep-single-box-designation">
                                            <?php _e('Age Group', 'eventprime-event-calendar-management'); ?></span>
                                            <span>
                                                <?php if ($type->age_group !== 'custom_group') echo em_code_to_display_string($type->age_group); else _e($type->custom_group, 'eventprime-event-calendar-management'); ?>
                                            </span>
                                        </p>
                                    </div>
                                    <div class="ep-single-box-summery">
                                        <?php if (isset($type->description) && $type->description !== '') echo $type->description; else _e('No description available', 'eventprime-event-calendar-management'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php if( $single_type_show_events == 1 && empty($hide_upcoming_events) ){
            $event_args  = new stdClass();
            $event_args->event_style = $event_display_style;
            $event_args->event_limit = $single_type_event_limit;
            $event_args->event_cols = $event_cols;
            $event_args->load_more = $load_more;
            $event_args->hide_past_events = $hide_past_events;
            if( $event_display_style == 'card' || $event_display_style == 'list' || $event_display_style == 'mini-list' ){ ?>
                <div class="emagic <?php echo $class;?>" id="<?php echo $sec_unique;?>">
                    <!-- .content-area starts-->
                    <div id="em_primary" class="em_content_area">
                        <div id="post-<?php echo $type->id; ?>">
                            <?php        
                            $args = array(
                                'orderby' => em_append_meta_key('start_date'),
                                'posts_per_page' => $single_type_event_limit,
                                'offset'=> (int) ($paged-1) * $single_type_event_limit,
                                'paged' => $paged,
                            );
                            $args['post_status'] = !empty($hide_past_events) == 1 ? 'publish' : 'any';
                            $upcoming_events = $event_service->upcoming_events_for_type( $type->id, $args );
                            $event_service->print_upcoming_event_block_for_types( $upcoming_events, $event_args );
                            ?>
                        </div>
                    </div>
                </div><?php 
            }
        } ?>
        
    </div>
    <?php
    if(!empty($enable_seo_urls) && ! isset( $type_id ) ){?>
        <div class="emagic-sidebar <?php echo $ep_seo_side_class;?>">
            <?php dynamic_sidebar();?>
        </div><?php
    }?>
</div>
<?php 
if ( ! empty( $enable_seo_urls ) ) {
    get_footer();
}