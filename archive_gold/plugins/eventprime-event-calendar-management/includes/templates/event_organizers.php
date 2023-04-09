<?php
wp_enqueue_script('em-public');
wp_enqueue_style('em-public-css');
$paged = 1;
if( ! empty( get_query_var('paged') ) ) {
    $paged = (int)get_query_var('paged');
}
$service = EventM_Factory::get_service('EventOrganizerM_Service');
$setting_service = EventM_Factory::get_service('EventM_Setting_Service');
$global_settings = $setting_service->load_model_from_db();
$showBookNowForGuestUsers = em_show_book_now_for_guest_users();
$display_style = isset( $atts['display_style'] ) ? $atts["display_style"] : $global_settings->organizer_display_view;
if( isset( $atts['limit'] ) ){
    $organizer_limit = ( $atts["limit"] == 0 || $atts["limit"] == '' ) ? EM_PAGINATION_LIMIT : $atts["limit"];
}else{
    $organizer_limit = ( $global_settings->organizer_limit == 0 ) ? EM_PAGINATION_LIMIT : $global_settings->organizer_limit;
}
$organizer_cols = isset( $atts['cols'] ) ? ep_check_column_size( $atts['cols'] ) : ep_check_column_size( $global_settings->organizer_no_of_columns );
$load_more = isset( $atts['load_more'] ) ? $atts['load_more'] : $global_settings->organizer_load_more;
$enable_search = isset( $atts['search'] ) ? $atts['search'] : $global_settings->organizer_search;
$featured = isset( $atts["featured"] ) ? $atts["featured"] : 0;
$popular = isset( $atts["popular"] ) ? $atts["popular"] : 0;

$em_search = isset( $_REQUEST['em_search'] ) ? $_REQUEST['em_search'] : '';
$meta_query = [];

$args = array(
    'orderby' => 'date',
    'number' => $organizer_limit,
    'offset'=> (int)($paged-1) * (int)$organizer_limit,
    'paged' => $paged,
    'name__like' => $em_search,
);
if( $featured == 1 && ( $popular == 0 || $popular == '' ) ){ 
    array_push( $meta_query, array(
        array(
            'key'     => em_append_meta_key('is_featured'),
            'value'   => 1
        )
    ));
}
if( $popular == 1 && ( $featured == 0 || $featured == '' ) ){
    $filter = array(
        'post_status' => 'publish',
        'meta_query' => $meta_query
     );
    $organizer_ids = $service->get_popular_organizers( $filter );
    $organizer_ids = array_slice( (array)$organizer_ids[0], 0, $organizer_limit, true );
    if(!empty($organizer_ids)){
        $args['orderby'] = 'include';
        $args['include'] = $organizer_ids;
    } 
}
if( $popular == 1 && $featured == 1 ){
    // Get featured and popular organizers
    $filter = array(
        'post_status' => 'publish',
        'meta_query' => array(
            'key'     => em_append_meta_key('is_featured'),
            'value'   => 1
        ) 
    );

    $organizer_ids = $service->get_popular_organizers( $filter );
    $organizer_ids = array_slice( (array)$organizer_ids[0], 0, $organizer_limit, true );
    if( ! empty( $organizer_ids ) ){
        $args['orderby'] = 'include';
        $args['include'] = $organizer_ids;
    } 
}
$args['meta_query'] = $meta_query;

$the_query = $service->get_all_organizers_query( $args );
$organizers = is_object( $the_query ) ? $the_query->terms : '';
$total = $service->count( array( 'hide_empty' => false ) , $em_search, $featured, $popular );
$max_num_pages = ceil( $total / $organizer_limit );
$organizers_page_url = get_permalink(em_global_settings("event_organizers"));
$class = $section_id = $column_class = $sec_unique = '';
if(isset($atts['section_id'])){
    $section_id=$atts['section_id'];
    $sec_unique = 'section-performers-'.$section_id;
    $page = false;
}
if(isset($atts['class'])){
    $class = $atts['class'];
}
if(isset($atts['column_class'])){
    $column_class = $atts['column_class'];
}
?>
<div class="emagic <?php echo $class;?>" id="<?php echo $sec_unique;?>">
    <?php if( isset( $enable_search ) && $enable_search == 1 ) {?>
        <div class="ep-box-wrap ep-box-search-wrap">
            <form id="em_organizer_search_form" class="ep-box-search-form ep-box-row ep-box-bottom" name="em_organizer_search_form" action="">
                <div class="ep-box-col-10 ep-event-filter-block">
                    <div class="ep-box-filter-search">
                        <div class='ep-box-search-label'><?php _e('Search by keyword', 'eventprime-event-calendar-management'); ?></div>
                        <input type="hidden" name="em_s" value="1" />
                        <input placeholder="<?php _e('Keyword', 'eventprime-event-calendar-management'); ?>" class="em_input" type="text" name="em_search" id="em_search" value="<?php $data = event_m_get_param('em_search'); echo $data; ?>" />
                    </div>
                </div>
                <div class="ep-box-col-2 ep-event-filter-block ep-text-item-right">
                    <div class="ep-box-filter-search_buttons">    
                        <input class="" type="submit" value="<?php _e('Search', 'eventprime-event-calendar-management'); ?>"/>
                    </div>
                </div>
            </form>
        </div>
    <?php } ?>
    <?php if (!empty($organizers)){ ?>    
        <div class="em_organizers dbfl">
            <?php
            switch ( $display_style ) {
                case 'card': include('organizer_views/card.php');
                    break;
                case 'box': include('organizer_views/box.php');
                    break;
                case 'list': include('organizer_views/list.php');
                    break;
                default: include('organizer_views/card.php'); // Loading card view by default
            }    
            ?>
        </div>
        <?php
        if( $max_num_pages > 1 && $load_more == 1 ){
            $curr_page = $the_query->query_vars['paged'];
            if( $display_style == 'card' ){ ?>
                <div class="ep-p-cards-load-more ep-view-load-more-wrap dbfl" onclick="em_load_more_organizers_card('.ep-p-cards-load-more','.ep-loading-view-btn','.ep-organizer-cards')" data-curr_page="<?php echo $curr_page?>" data-loading="<?php _e('Loading...');?>" data-loaded="<?php _e('Load More');?>" data-max_page="<?php echo $max_num_pages;?>" data-show="<?php echo $organizer_limit;?>" data-featured="<?php echo $featured;?>" data-cols = "<?php echo $organizer_cols;?>">
                    <div class="ep-loading-view-btn em_color"><?php _e('Load More');?></div>
                </div>
            <?php }elseif( $display_style == 'box' ){ ?>
                <div class="ep-p-boxes-load-more ep-view-load-more-wrap dbfl" onclick="em_load_more_organizers_box('.ep-p-boxes-load-more','.ep-loading-view-btn','.ep-organizer-box-wrap')" data-curr_page="<?php echo $curr_page?>" data-loading="<?php _e('Loading...');?>" data-loaded="<?php _e('Load More');?>" data-max_page="<?php echo $max_num_pages;?>" data-show="<?php echo $organizer_limit;?>" data-featured="<?php echo $featured;?>" data-cols = "<?php echo $organizer_cols;?>" data-bnum="<?php echo isset($b) ? $b : '';?>">
                    <div class="ep-loading-view-btn em_color"><?php _e('Load More');?></div>
                </div>
            <?php }elseif( $display_style == 'list' ){ ?>
                <div class="ep-p-lists-load-more ep-masonry-load-more-wrap" onclick="em_load_more_organizers_list()" data-curr_page="<?php echo $curr_page?>" data-loading="<?php _e('Loading...');?>" data-loaded="<?php _e('Load More');?>" data-max_page="<?php echo $max_num_pages;?>" data-show="<?php echo $organizer_limit;?>" data-featured="<?php echo $featured;?>"><div class="ep-load-more-button em_color"><?php _e('Load More');?></div></div>
            <?php }else { ?>
                <div class="ep-p-cards-load-more ep-view-load-more-wrap dbfl" onclick="em_load_more_organizers_card('.ep-p-cards-load-more','.ep-loading-view-btn','.ep-organizer-cards')" data-curr_page="<?php echo $curr_page?>" data-loading="<?php _e('Loading...');?>" data-loaded="<?php _e('Load More');?>" data-max_page="<?php echo $max_num_pages;?>" data-show="<?php echo $organizer_limit;?>" data-featured="<?php echo $featured;?>" data-cols = "<?php echo $organizer_cols;?>">
                    <div class="ep-loading-view-btn em_color"><?php _e('Load More');?></div>
                </div>
            
            <?php }   
        }
    } else{ ?>
        <div class="ep-alert-warning ep-alert-info">     
         <?php _e('No Event Organizer found','eventprime-event-calendar-management'); ?>
        </div><?php 
    } ?>
</div>
<script>
// get theme color in CSS variable
function epGetColor() {
    $ = jQuery;
    $('.emagic, #primary.content-area .entry-content').find('a').css('color');
}

document.addEventListener("DOMContentLoaded", function(event) { 
    $ = jQuery;
    var epColor = $('.emagic, #primary.content-area .entry-content').find('a').css('color');
    jQuery(':root').css('--themeColor', epColor);
});
</script>