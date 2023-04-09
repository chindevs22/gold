<?php
wp_enqueue_script('em-select2');
wp_enqueue_style('em-select2-css');
wp_enqueue_script('em-public');
wp_enqueue_script('font-awesome');
wp_enqueue_script('masonry');
wp_enqueue_style('masonry');
$event_service = EventM_Factory::get_service('EventM_Service');
global $wp;
/* display filter options */
if( isset( $atts['disable_filter'] ) ){
    $display_filter_options = $atts['disable_filter'];
}else{
    $display_filter_options = $global_settings->disable_filter_options;
}
/* filter elements */
if( isset( $atts['filter_elements'] ) ){
    $keyword = $type = $date = $site = 0;
    $filter_elements = explode( ',', $atts['filter_elements'] );
    if( in_array( 'keyword', $filter_elements ) )
        $keyword = 1;
    if( in_array( 'type', $filter_elements ) )
        $type = 1;
    if( in_array( 'date', $filter_elements ) )
        $date = 1;
    if( in_array( 'site', $filter_elements ) )
        $site = 1;
}else{
    $keyword = $type = $date = $site = 1;
}
/* individual events argument */
if( isset( $events_atts['individual_events'] ) ){
    $i_events = $events_atts['individual_events'];
}else{
    $i_events = '';
}

if (event_m_get_param('em_types')) {
    $type_id = event_m_get_param('em_types');
    $type_service = EventM_Factory::get_service('EventTypeM_Service');
    $type_model = $type_service->load_model_from_db($type_id);
    if (!empty($type_model->id) && !empty($type_model->description)) {
        ?>    
        <div class="em_event_type_note">
            <?php echo $type_model->name; ?>
            <?php echo do_shortcode(wpautop($type_model->description)); ?>
        </div>
    <?php
    }
}
$currency_symbol = em_currency_symbol();
$request_data = $_REQUEST;
$view = isset($request_data['events_view']) ? $request_data['events_view'] : $events_atts['view'];
// set url for plain permalinks
$page_action = $form_action = '';
if(isset($request_data['page_id']) && !empty($request_data['page_id'])){
    $page_action = add_query_arg('page_id', $request_data['page_id'], $page_action);
    $form_action = add_query_arg('events_view', $view, $page_action);
}
else{
    $form_action = !empty($events_page_id) ? get_permalink($events_page_id) : "";
    $form_action = add_query_arg('events_view', $view, $form_action);
}
if(!empty($request_data['em_s'])){
    $view = (!empty($view)) ? $view : 'card';
    if(!empty($request_data['em_types']) && !is_array($request_data['em_types'])){
        $request_data['em_types']= explode(',', $request_data['em_types']);
    }
}
if(!empty($request_data['em_s'])){
    $view = (!empty($view)) ? $view : 'card';
    if(!empty($request_data['em_venue']) && !is_array($request_data['em_venue'])){
        $request_data['em_venue']= explode(',', $request_data['em_venue']);
    }
}
$current_ts = em_current_time_by_timezone();
$front_switch_view_option = $global_settings->front_switch_view_option;?>
<div class="emagic">
    <?php if ( (isset( $display_filter_options ) && $display_filter_options == 0 ) || !isset( $display_filter_options ) ) { ?>
    <form id="em_event_search_form" class="ep-event_search_form dbfl" name="em_event_search_form" action="<?php echo $form_action; ?>"> 
        <input type="text" name="events_view" value="<?php echo esc_attr($view);?>" style="display:none;">
        <div class="em_event_views_wrap dbfl" >
            <div class="em_event-filter-reset difl"><a href="<?php echo home_url($wp->request); ?>"><?php _e('Reset Filters', 'eventprime-event-calendar-management'); ?></a></div>
            <?php if(!empty($front_switch_view_option)){?>
                <div class="em_event_views difr">
                    <div class="ep-event-view-sort difl"><?php _e('View as', 'eventprime-event-calendar-management'); ?></div>
                    <?php //Profile Grid group ID  
                        $gid = isset($_REQUEST['gid']) ? '&gid='.absint($_REQUEST['gid']) : '';
                        $events_view_url = !empty($page_action) ? $page_action.'&events_view=' : '?events_view=';
                    ?>
                    <div class="ep-event-view-sort difl">
                        <div class="ep-sort-select-wrapper">
                            <div class="ep-sort-select">
                                <div class="ep-sort-select__trigger"><span><?php _e('Select View Type', 'eventprime-event-calendar-management'); ?></span>
                                    <div class="ep-sorting-arrow"></div>
                                </div>
                                <ul class="ep-sort-options">
                                    <?php if(in_array('day', $front_switch_view_option)){?>
                                        <li class="ep-sort-option <?php echo $view == 'day' ? 'selected' : ''; ?>" data-value="day" data-text="<?php _e('Day', 'eventprime-event-calendar-management'); ?>" data-url="<?php echo $events_view_url;?>day<?php echo $gid; ?>"><span class="difl"><i class="material-icons">today</i></span><?php _e('Day', 'eventprime-event-calendar-management'); ?></li><?php
                                    }
                                    if(in_array('week', $front_switch_view_option)){?>
                                        <li class="ep-sort-option <?php echo $view == 'week' ? 'selected' : ''; ?>" data-value="week" data-text="<?php _e('Week', 'eventprime-event-calendar-management'); ?>" data-url="<?php echo $events_view_url;?>week<?php echo $gid; ?>"><span class="difl"><i class="material-icons">date_range</i></span><?php _e('Week', 'eventprime-event-calendar-management'); ?></li><?php
                                    }
                                    if(in_array('month', $front_switch_view_option)){?>
                                        <li class="ep-sort-option <?php echo $view == 'month' ? 'selected' : ''; ?>" data-value="month" data-text="<?php _e('Month', 'eventprime-event-calendar-management'); ?>" data-url="<?php echo $events_view_url;?>month<?php echo $gid; ?>"><span class="difl"><i class="material-icons">date_range</i></span><?php _e('Month', 'eventprime-event-calendar-management'); ?></li><?php
                                    }
                                    if(in_array('listweek', $front_switch_view_option)){?>
                                        <li class="ep-sort-option <?php echo $view == 'listweek' ? 'selected' : ''; ?>" data-value="listweek" data-text="<?php _e('List Week', 'eventprime-event-calendar-management'); ?>" data-url="<?php echo $events_view_url;?>listweek<?php echo $gid; ?>"><span class="difl"><i class="material-icons">date_range</i></span><?php _e('List Week', 'eventprime-event-calendar-management'); ?></li>
                                        <?php
                                    }
                                    if(in_array('card', $front_switch_view_option)){?>
                                        <li class="ep-sort-option <?php echo $view == 'card' ? 'selected' : ''; ?>" data-value="card" data-text="<?php _e('Card', 'eventprime-event-calendar-management'); ?>" data-url="<?php echo $events_view_url;?>card<?php echo $gid; ?>"><span class="difl"><i class="material-icons">view_module</i></span><?php _e('Card', 'eventprime-event-calendar-management'); ?></li><?php
                                    }
                                    if(in_array('masonry', $front_switch_view_option)){?>
                                        <li class="ep-sort-option <?php echo $view == 'masonry' ? 'selected' : ''; ?>" data-value="masonry" data-text="<?php _e('Masonry', 'eventprime-event-calendar-management'); ?>" data-url="<?php echo $events_view_url;?>masonry<?php echo $gid; ?>"><span class="difl"><i class="material-icons">dashboard</i></span><?php _e('Masonry', 'eventprime-event-calendar-management'); ?></li><?php
                                    }
                                    if(in_array('slider', $front_switch_view_option)){?>
                                        <li class="ep-sort-option <?php echo $view == 'slider' ? 'selected' : ''; ?>" data-value="slider" data-text="<?php _e('Slider', 'eventprime-event-calendar-management'); ?>" data-url="<?php echo $events_view_url;?>slider<?php echo $gid; ?>"><span class="difl"><i class="material-icons">slideshow</i></span><?php _e('Slider', 'eventprime-event-calendar-management'); ?></li><?php
                                    }
                                    if(in_array('list', $front_switch_view_option)){?>
                                        <li class="ep-sort-option <?php echo $view == 'list' ? 'selected' : ''; ?>" data-value="list" data-text="<?php _e('List', 'eventprime-event-calendar-management'); ?>" data-url="<?php echo $events_view_url;?>list<?php echo $gid; ?>"><span class="difl"><i class="material-icons">view_list</i></span><?php _e('List', 'eventprime-event-calendar-management'); ?></li>
                                        <?php
                                    }?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div><?php
            }?>
        </div>
        <!-- Filter -->
        <button id="ep-filter-bar-collapse-toggle"  type="button">
            <span class="ep-filters-toggle-text-show"><?php _e('Show Event Search', 'eventprime-event-calendar-management'); ?></span>
            <span class="ep-filters-toggle-text-hide"><?php _e('Hide Event Search', 'eventprime-event-calendar-management'); ?></span>
            <span class="tribe-bar-toggle-arrow"></span>
        </button>
        <div id="ep-event-filterbar" class="ep-event-filters dbfl">
            <?php
            if(isset($request_data['page_id']) && !empty($request_data['page_id'])){?>
                <input type="hidden" name="page_id" value="<?php echo $request_data['page_id'];?>" /><?php
            }
            if( isset( $keyword ) && $keyword == 1 ){ ?>
                <div class="ep-event-filter-block ep-search-filter-block">
                    <div class="ep-event-filter-search">
                        <div class='ep-filter-label dbfl'><?php _e('Search by keyword', 'eventprime-event-calendar-management'); ?></div>
                        <input type="hidden" name="em_s" value="1" />
                        <input placeholder="<?php _e('Keyword', 'eventprime-event-calendar-management'); ?>" class="em_input" type="text" name="em_search" id="em_search" value="<?php $data = event_m_get_param('em_search'); echo $data; ?>" />
                    </div>
                </div><?php 
            }
            // event type filter
            if( isset( $type ) && $type == 1 ){
                $eventType_service = EventM_Factory::get_service('EventTypeM_Service');
                $types = $eventType_service->get_front_types();
                if( count( $types ) > 0 ) {?>
                    <div class="ep-event-filter-block">
                        <div class="ep-event-types">
                            <div class="ep-filter-label"><?php echo __('Search by Event Type', 'eventprime-event-calendar-management'); ?></div>
                            <div class="ep-event-types-wrap">
                                <?php
                                if(isset($events_atts['types']) && !empty($events_atts['types'])){
                                    $types = $eventType_service->get_front_types(array('include'=>$events_atts['types']));
                                }
                                $em_types = (array) event_m_get_param('em_types');
                                if (count($types) > 0):?>
                                    <select multiple name="em_types[]" id="em-event-type-filter">
                                        <?php
                                        foreach ($types as $type):?>
                                            <option value="<?php echo $type->id; ?>" <?php echo !empty($request_data['em_types']) ? in_array($type->id, $request_data['em_types']) ? 'selected' : '' :'' ?>>
                                                <label for="<?php echo $type->id; ?>"><?php echo $type->name; ?></label>
                                            </option>
                                            <?php
                                        endforeach;?>
                                    </select><?php
                                endif;?> 
                            </div>
                        </div>
                    </div><?php 
                } 
            }
            // date filter
            if( isset( $date ) && $date == 1 ){?>
                <div class="ep-event-filter-block">
                    <div class='start-end-date'>
                        <div class="ep-filter-label"> <?php echo __('Search by Date', 'eventprime-event-calendar-management'); ?></div>
                        <div class="ep-event-date"> 
                            <input type="text" placeholder="<?php echo __('Select Date', 'eventprime-event-calendar-management'); ?>" readonly class="em_date" name="em_sd" value="<?php echo isset($_REQUEST['em_sd']) ? $_REQUEST['em_sd'] : ''; ?>"/>
                        </div>
                    </div>
                </div><?php 
            } 
            // event site filter
            if( isset( $site ) && $site == 1 ){
                $venue_service = EventM_Factory::get_service('EventM_Venue_Service');
                $all_venues = $venue_service->get_venues();
                if( count( $all_venues ) > 0 ) {?>
                    <div class="ep-event-filter-block">
                        <div class="ep-event-types">
                            <div class="ep-filter-label"><?php echo __('Search by Event Site', 'eventprime-event-calendar-management'); ?></div>
                            <div class="ep-event-types-wrap">
                                <?php
                                if(isset($events_atts['sites']) && !empty($events_atts['sites'])){
                                    $all_venues= $venue_service->get_venues(array('include'=>$events_atts['sites']));
                                }
                                $selected_venue = isset($_REQUEST['em_venue']) ? $_REQUEST['em_venue'] : '';
                                $em_venue = (array) event_m_get_param('em_venue');
                                if (count($all_venues) > 0):
                                    ?>
                                    <select multiple name="em_venue[]" id="em-event-venue-filter">
                                        <?php foreach ($all_venues as $venue): ?>
                                            <option value="<?php echo $venue->id; ?>" <?php echo !empty($request_data['em_venue']) ? in_array($venue->id, $request_data['em_venue']) ? 'selected' : '' :'' ?>>
                                                <label for="<?php echo $venue->id; ?>"><?php echo $venue->name; ?></label>
                                            </option>
                                            <?php
                                        endforeach;?>
                                    </select>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div><?php 
                } 
            }?>
            <?php do_action('em_event_filter_form'); ?>
            <?php if( $keyword == 1 ){?>
            <div class="ep-event-filter-block">
                <div class="ep-event-filter-search_buttons">    
                    <input class="" type="submit" value="<?php _e('Search', 'eventprime-event-calendar-management'); ?>"/>
                </div>
            </div>
            <?php } ?>
        </div>
    </form> 
    <?php } ?>
</div>  
<?php
switch ($view) {
    case 'listweek':
    case 'day':
    case 'week':
    case 'month': include('event_views/calendar.php');
        break;
    case 'masonry': include('event_views/masonry.php');
        break;
    case 'slider': include('event_views/slider.php');
        break;
    case 'list': include('event_views/list.php'); // Loading list view
        break;
    default: include('event_views/card.php'); // Loading card view
}
$em = event_magic_instance();
do_action('em_event_popup_data_scripts');
if (!in_array('event-comments',$em->extensions)) {
    add_filter('comments_array', '__return_empty_array', 10, 2);
}
?>