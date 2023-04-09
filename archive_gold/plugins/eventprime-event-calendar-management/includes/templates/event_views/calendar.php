<?php
    $events = $event_service->get_events_for_calendar_view($events_atts);
    $week_start = absint(get_option('start_of_week'));
    $locale = em_get_calendar_locale();
    if (absint(em_global_settings('enable_default_calendar_date')) == 1) {
        $default_date = date('c',em_global_settings('default_calendar_date'));
    } else {
        $default_date = date('c',em_get_local_timestamp());
    }
    $calendar_title_format = "MMMM YYYY";
    if(!empty(em_global_settings('calendar_title_format'))){
        $calendar_title_format = em_global_settings('calendar_title_format');
    }
    $calendar_column_header_format = "ddd";
    if(!empty(em_global_settings('calendar_column_header_format'))){
        $calendar_column_header_format = em_global_settings('calendar_column_header_format');
    }
    $hide_calendar_rows = true;
    if(empty(em_global_settings('hide_calendar_rows'))){
        $hide_calendar_rows = false;
    }
    $hide_time_on_front_calendar = 0;
    if(!empty(em_global_settings('hide_time_on_front_calendar'))){
        $hide_time_on_front_calendar = 1;
    }
    $time_format = em_global_settings('time_format');
    $month_data = array(
        __('January', 'eventprime-event-calendar-management'),
        __('February', 'eventprime-event-calendar-management'),
        __('March', 'eventprime-event-calendar-management'),
        __('April', 'eventprime-event-calendar-management'),
        __('May', 'eventprime-event-calendar-management'),
        __('June', 'eventprime-event-calendar-management'),
        __('July', 'eventprime-event-calendar-management'),
        __('August', 'eventprime-event-calendar-management'),
        __('September', 'eventprime-event-calendar-management'),
        __('October', 'eventprime-event-calendar-management'),
        __('November', 'eventprime-event-calendar-management'),
        __('December', 'eventprime-event-calendar-management'),
    );
    $js_data = array(
        'events'                        => $events,
        'view'                          => $view,
        'settings'                      => $global_settings,
        'week_start'                    => $week_start,
        'locale'                        => $locale,
        'default_date'                  => $default_date,
        'calendar_title_format'         => $calendar_title_format,
        'calendar_column_header_format' => $calendar_column_header_format,
        'em_calendar_month_data'        => $month_data,
        'hide_calendar_rows'            => $hide_calendar_rows,
        'time_format'                   => $time_format,
        'hide_time_on_front_calendar'   => $hide_time_on_front_calendar,
    );
    if ( !wp_script_is( 'em-calendar-util', 'registered' ) ) {
        wp_register_script( 'moment', EM_BASE_URL . 'includes/templates/js/moment.min.js', array(), EVENTPRIME_VERSION, false );
        wp_register_script( 'em-full-calendar', EM_BASE_URL . 'includes/templates/js/calendar-4.4.2/core/main.min.js', array(), EVENTPRIME_VERSION, false );
        wp_register_script( 'em-full-interaction-calendar', EM_BASE_URL . 'includes/templates/js/calendar-4.4.2/interaction/main.min.js', array( 'em-full-calendar' ), EVENTPRIME_VERSION, false );
        wp_register_script( 'em-full-daygrid-calendar', EM_BASE_URL . 'includes/templates/js/calendar-4.4.2/daygrid/main.min.js', array( 'em-full-calendar' ), EVENTPRIME_VERSION, false );
        wp_register_script( 'em-full-list-calendar', EM_BASE_URL . 'includes/templates/js/calendar-4.4.2/list/main.min.js', array( 'em-full-calendar' ), EVENTPRIME_VERSION, false );
        wp_register_script( 'em-full-calendar-locales', EM_BASE_URL . 'includes/templates/js/calendar-4.4.2/core/locales-all.min.js', array( 'em-full-calendar' ), EVENTPRIME_VERSION, false );
        wp_register_script( 'em-full-calendar-moment', EM_BASE_URL . 'includes/templates/js/calendar-4.4.2/moment/main.js', array( 'em-full-calendar', 'moment' ), EVENTPRIME_VERSION, false );
        wp_register_script( 'em-calendar-util', EM_BASE_URL . 'includes/templates/js/em-calendar-util.js', array( 'em-full-calendar', 'em-full-interaction-calendar', 'em-full-daygrid-calendar', 'em-full-list-calendar', 'em-public', 'em-full-calendar-locales', 'moment', 'em-full-calendar-moment' ), EVENTPRIME_VERSION, false );
    }
    
    wp_localize_script('em-calendar-util', 'em_calendar_data', $js_data);
    wp_enqueue_script('em-calendar-util');
    wp_enqueue_style('em-full-calendar-css');
    wp_enqueue_style('em-full-calendar-daygrid-css');
    wp_enqueue_style('em-full-calendar-list-css');
    $events_page_id= em_global_settings("events_page");
?>

<!-- Calendar View -->
<div class="emagic">
    <div class="ep-all_event_calendar dbfl">
        <div id="em_calendar" class=""></div>
        <!-- Calendar ends here -->
        <!-- Event type swatches -->
        <div class="ep-event-types">
            <?php 
            $type_service= EventM_Factory::get_service('EventTypeM_Service');
            if(isset($events_atts['types']) && !empty($events_atts['types'])){
                $types= $type_service->get_types(array('include'=>$events_atts['types']));
            } else {
                $types= $type_service->get_types();
            }
            foreach($types as $type):?>
                <div class="ep-event-type">
                    <?php echo $type->name; ?>
                    <span  style="background-color: #<?php echo $type->color; ?>"></span>
                </div><?php 
            endforeach; ?>  
        </div>      
        <!-- Swatches ends here -->
    </div>
</div>