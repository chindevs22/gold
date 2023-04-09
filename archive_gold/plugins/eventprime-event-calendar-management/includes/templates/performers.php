<?php
wp_enqueue_style('em-public-css');
wp_enqueue_script('em-public');
$paged = 1;
if( ! empty( get_query_var('paged') ) ) {
    $paged = (int)get_query_var('paged');
}
$setting_service = EventM_Factory::get_service('EventM_Setting_Service');
$global_settings = $setting_service->load_model_from_db();
$showBookNowForGuestUsers = em_show_book_now_for_guest_users();
$service = EventM_Factory::get_service('EventM_Performer_Service');
$display_style = isset( $atts['display_style'] ) ? $atts["display_style"] : $global_settings->performer_display_view;
$performer_limit = isset( $atts['limit'] ) ? (empty($atts["limit"]) ? EM_PAGINATION_LIMIT : $atts["limit"]) : (empty($global_settings->performer_limit) ? EM_PAGINATION_LIMIT : $global_settings->performer_limit );
$performer_cols = isset( $atts['cols'] ) ? ep_check_column_size( $atts['cols'] ) : ep_check_column_size( $global_settings->performer_no_of_columns );
$load_more = isset( $atts['load_more'] ) ? $atts['load_more'] : $global_settings->performer_load_more;
$enable_search = isset( $atts['search'] ) ? $atts['search'] : $global_settings->performer_search;
$featured = isset( $atts["featured"] ) ? $atts["featured"] : 0;
$popular = isset( $atts["popular"] ) ? $atts["popular"] : 0;

$em_search = isset( $_REQUEST['em_search'] ) ? $_REQUEST['em_search'] : '';
$args = array(
    'orderby' => 'date',
    'posts_per_page' => $performer_limit,
    'offset'=> (int)( $paged-1 ) * (int)$performer_limit,
    'paged' => $paged,
    's' => $em_search,
);
if( $featured == 1 && ( $popular == 0 || $popular == '' )){ 
    $args['meta_query'] = array(
        array(
            'key'     => em_append_meta_key('is_featured'),
            'value'   => 1
        )
    );
}
if( $popular == 1 && ( $featured == 0 || $featured == '') ){
    $filter = [];
    $performer_ids = $service->get_popular_performers( $filter );
    $performer_ids = array_slice( (array)$performer_ids[0], 0, $performer_limit, true );
    if( ! empty( $performer_ids ) ){
        $args['orderby'] = 'post__in';
        $args['post__in'] = $performer_ids;
    } 
}
if( $popular == 1 && $featured == 1 ){
    // Get featured and popular performers (posts)
    $filter = array(
        'post_type' => EM_PERFORMER_POST_TYPE,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key'     => em_append_meta_key('is_featured'),
                'value'   => 1
            )
        )
    );
    $performer_ids = $service->get_popular_performers( $filter );
    $performer_ids = array_slice( (array)$performer_ids[0], 0, $performer_limit, true );
    if( ! empty( $performer_ids ) ){
        $args['orderby'] = 'post__in';
        $args['post__in'] = $performer_ids;
    } 
}
$the_query = $service->get_all_performers_query( $args );
$performers = is_object( $the_query ) ? $the_query->posts : '';
$sites = $types = '';
$performers_page_url= get_permalink(em_global_settings("performers_page"));
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
            <form id="em_performer_search_form" class="ep-box-search-form ep-box-row ep-box-bottom" name="em_performer_search_form" action="">
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
    <?php if (!empty($performers)) { ?>
        <div class="em_performers dbfl">
            <?php
            switch ( $display_style ) {
                case 'card': include('performer_views/card.php');
                    break;
                case 'box': include('performer_views/box.php');
                    break;
                case 'list': include('performer_views/list.php');
                    break;
                default: include('performer_views/card.php'); // Loading card view by default
            }    
            ?>
        </div>
        <?php
        if( $the_query->max_num_pages > 1 && $load_more == 1 ){
            $curr_page = $the_query->query_vars['paged'];
            if( $display_style == 'box' ){ ?>
                <div class="ep-p-boxes-load-more ep-view-load-more-wrap dbfl" onclick="em_load_more_performers_box('.ep-p-boxes-load-more','.ep-loading-view-btn','.ep-performer-box-wrap')" data-curr_page="<?php echo $curr_page?>" data-loading="<?php _e('Loading...');?>" data-loaded="<?php _e('Load More');?>" data-max_page="<?php echo $the_query->max_num_pages;?>" data-show="<?php echo $performer_limit;?>" data-featured="<?php echo $featured;?>" data-cols = "<?php echo $performer_cols;?>" data-bnum="<?php echo isset($b) ? $b : '';?>">
                    <div class="ep-loading-view-btn em_color"><?php _e('Load More');?></div>
                </div><?php 
            } elseif( $display_style == 'list' ){ ?>
                <div class="ep-p-lists-load-more ep-masonry-load-more-wrap" onclick="em_load_more_performers_list()" data-curr_page="<?php echo $curr_page?>" data-loading="<?php _e('Loading...');?>" data-loaded="<?php _e('Load More');?>" data-max_page="<?php echo $the_query->max_num_pages;?>" data-show="<?php echo $performer_limit;?>" data-featured="<?php echo $featured;?>"><div class="ep-load-more-button em_color"><?php _e('Load More');?></div></div><?php 
            }else { ?>
                <div class="ep-p-cards-load-more ep-view-load-more-wrap dbfl" onclick="em_load_more_performers_card('.ep-p-cards-load-more','.ep-loading-view-btn','.ep-performer-cards')" data-curr_page="<?php echo $curr_page?>" data-loading="<?php _e('Loading...');?>" data-loaded="<?php _e('Load More');?>" data-max_page="<?php echo $the_query->max_num_pages;?>" data-show="<?php echo $performer_limit;?>" data-featured="<?php echo $featured;?>" data-cols = "<?php echo $performer_cols;?>">
                    <div class="ep-loading-view-btn em_color"><?php _e('Load More');?></div>
                </div><?php 
            }   
        } 
    }else{ ?>
        <div class="ep-alert-warning ep-alert-info">
           <?php ( isset( $_REQUEST['em_search'] ) ) ? _e(' No performers found related to your search.') : _e( ' No performers found for the listed events.' ); ?>
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