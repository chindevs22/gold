<?php
wp_enqueue_script('em-public');
wp_enqueue_style('em-public-css');
$paged = 1;
if( ! empty( get_query_var('paged') ) ) {
    $paged = (int)get_query_var('paged');
}
$service = EventM_Factory::get_service('EventTypeM_Service');
/*if(isset($atts['types'])){
    $page = false;
    $types = $atts['types'];
} else{
    $types = $service->get_front_types(array('paged' => $paged,'offset'=> (int) ($paged-1) * EM_PAGINATION_LIMIT,'number'=>EM_PAGINATION_LIMIT));   
}*/

$setting_service = EventM_Factory::get_service('EventM_Setting_Service');
$global_settings = $setting_service->load_model_from_db();
$showBookNowForGuestUsers = em_show_book_now_for_guest_users();
$featured = ''; $popular = '';

if( isset( $atts['display_style'] ) ){
    $display_style = $atts["display_style"];
}else{
    $display_style = $global_settings->type_display_view;
}
if( isset( $atts['limit'] ) ){
    $type_limit = ( $atts["limit"] == 0 || $atts["limit"] == '' ) ? EM_PAGINATION_LIMIT : $atts["limit"];
}else{
    $type_limit = ( $global_settings->type_limit == 0 ) ? EM_PAGINATION_LIMIT : $global_settings->type_limit;
}
if( isset( $atts['cols'] ) ){
    $type_cols = ep_check_column_size( $atts['cols'] ); 
}else{
    $type_cols = ep_check_column_size( $global_settings->type_no_of_columns );
}
if( isset( $atts['load_more'] ) ){
    $load_more = $atts["load_more"];
}else{
    $load_more = $global_settings->type_load_more;
}
if( isset( $atts['search'] ) ){
    $enable_search = $atts['search'];
}else{
    $enable_search = $global_settings->type_search;
}
$featured = isset( $atts["featured"] ) ? $atts["featured"] : 0;
$popular = isset( $atts["popular"] ) ? $atts["popular"] : 0;

$em_search = isset( $_REQUEST['em_search'] ) ? $_REQUEST['em_search'] : '';
$meta_query = [];
$args = array(
    'orderby' => 'date',
    'number' => $type_limit,
    'offset'=> (int)($paged-1) * (int)$type_limit,
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
    $type_ids = $service->get_popular_types( $filter );
    $type_ids = array_slice( (array)$type_ids[0], 0, $type_limit, true );
    if(!empty($type_ids)){
        $args['orderby'] = 'include';
        $args['include'] = $type_ids;
    } 
}
if( $popular == 1 && $featured == 1 ){
    // Get featured and popular event types
    $filter = array(
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key'     => em_append_meta_key('is_featured'),
                'value'   => 1
            ) 
        ) );

    $type_ids = $service->get_popular_types( $filter );
    $type_ids = array_slice( (array)$type_ids[0], 0, $type_limit, true );
    if( ! empty( $type_ids ) ){
        $args['orderby'] = 'include';
        $args['include'] = $type_ids;
    } 
}
$args['meta_query'] = $meta_query;
$the_query = $service->get_all_types_query( $args );
$types = is_object( $the_query ) ? $the_query->terms : '';
$total = $service->count( array( 'hide_empty' => false ) , $em_search, $featured, $popular );
$max_num_pages = ceil( $total / $type_limit );
$types_page_url = get_permalink( em_global_settings("event_types") );
$section_id = $column_class = $sec_unique = $class = '';
if(isset($atts['section_id'])){
    $section_id = $atts['section_id'];
    $sec_unique = 'section-event-types-'.$section_id;
    $page = false;
}
if(isset($atts['column_class'])){
    $column_class = $atts['column_class'];
}
if(isset($atts['class'])){
    $class = $atts['class'];
}?>
<div class="emagic <?php echo $class;?>" id="<?php echo $sec_unique;?>">
    <?php if( isset( $enable_search ) && $enable_search == 1 ) { ?>
        <div class="ep-box-wrap ep-box-search-wrap">
            <form id="em_type_search_form" class="ep-box-search-form ep-box-row ep-box-bottom" name="em_type_search_form" action="">
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
    <?php if (!empty($types)): ?>
        <div class="ep-event-type-cards dbfl">
            <?php
            switch ( $display_style ) {
                case 'card': include('type_views/card.php');
                    break;
                case 'box': include('type_views/box.php');
                    break;
                case 'list': include('type_views/list.php');
                    break;
                default: include('type_views/card.php');
            }?>        
        </div>
        <?php
        if( $max_num_pages > 1 && $load_more == 1 ){
            $curr_page = $the_query->query_vars['paged'];
            if( $display_style == 'card' ){ ?>
                <div class="ep-type-cards-load-more ep-view-load-more-wrap dbfl" onclick="em_load_more_types_card('.ep-type-cards-load-more','.ep-loading-view-btn','.ep-type-cards')" data-curr_page="<?php echo $curr_page?>" data-loading="<?php _e('Loading...');?>" data-loaded="<?php _e('Load More');?>" data-max_page="<?php echo $max_num_pages;?>" data-show="<?php echo $type_limit;?>" data-featured="<?php echo $featured;?>" data-cols = "<?php echo $type_cols;?>">
                    <div class="ep-loading-view-btn em_color"><?php _e('Load More');?></div>
                </div>
            <?php }elseif( $display_style == 'box' ){ ?>
                <div class="ep-type-boxes-load-more ep-view-load-more-wrap dbfl" onclick="em_load_more_types_box('.ep-type-boxes-load-more','.ep-loading-view-btn','.ep-type-box-wrap')" data-curr_page="<?php echo $curr_page?>" data-loading="<?php _e('Loading...');?>" data-loaded="<?php _e('Load More');?>" data-max_page="<?php echo $max_num_pages;?>" data-show="<?php echo $type_limit;?>" data-featured="<?php echo $featured;?>" data-cols = "<?php echo $type_cols;?>" data-bnum="<?php echo isset($b) ? $b : '';?>">
                    <div class="ep-loading-view-btn em_color"><?php _e('Load More');?></div>
                </div>
            <?php }elseif( $display_style == 'list' ){ ?>
                <div class="ep-type-lists-load-more ep-masonry-load-more-wrap" onclick="em_load_more_types_list()" data-curr_page="<?php echo $curr_page?>" data-loading="<?php _e('Loading...');?>" data-loaded="<?php _e('Load More');?>" data-max_page="<?php echo $max_num_pages;?>" data-show="<?php echo $type_limit;?>" data-featured="<?php echo $featured;?>"><div class="ep-load-more-button em_color"><?php _e('Load More');?></div></div>
            <?php }else { ?>
                <div class="ep-type-cards-load-more ep-view-load-more-wrap dbfl" onclick="em_load_more_types_card('.ep-type-cards-load-more','.ep-loading-view-btn','.ep-type-cards')" data-curr_page="<?php echo $curr_page?>" data-loading="<?php _e('Loading...');?>" data-loaded="<?php _e('Load More');?>" data-max_page="<?php echo $max_num_pages;?>" data-show="<?php echo $type_limit;?>" data-featured="<?php echo $featured;?>" data-cols = "<?php echo $type_cols;?>">
                    <div class="ep-loading-view-btn em_color"><?php _e('Load More');?></div>
                </div>
            
            <?php }   
        } ?>
    <?php else: ?>
        <div class="ep-alert-warning ep-alert-info">
            <?php (isset($_REQUEST['em_search'])) ? _e(' No Event Type found related to your search.') : _e(' No Event Type found for the listed events.'); ?>
        </div>
    <?php endif; ?>
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