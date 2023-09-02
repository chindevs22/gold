<?php

class SLMS_Events {

    public function __construct()
    {
        add_filter( 'stm_lms_menu_items', array($this, 'user_menu_items'), 15 );
        add_filter( 'stm_lms_custom_routes_config', array($this, 'page_routes'), 15 );
//        add_action( 'wp_ajax_stm_lms_get_user_events', array($this, 'get_user_events') );
        add_filter('stm_lms_account_tabs_visible', function (){ return 5; });
    }

    public static function enrolled_events_url() {
        return STM_LMS_User::login_page_url() . 'enrolled-events';
    }

    public function page_routes($page_routes){
        $page_routes['user_url']['sub_pages']['enrolled_events'] = array(
            'template'  => 'stm-lms-user-events',
            'protected' => true,
            'url'       => 'enrolled-events',
        );

        if(isset($page_routes['user_url']['sub_pages']['user_events'])) {
            unset($page_routes['user_url']['sub_pages']['user_events']);
        }

        return $page_routes;
    }


    public function user_menu_items($menus){

        $menus[] = array(
            'order'        => 110,
            'id'           => 'enrolled_events',
            'slug'         => 'enrolled-events',
            'lms_template' => 'stm-lms-user-events',
            'menu_title'   => esc_html__( 'Enrolled Webinars & Events', 'slms' ),
            'menu_icon'    => 'fa-book',
            'menu_url'     => self::enrolled_events_url(),
            'menu_place'   => 'learning',
        );

        foreach ($menus as $key => $item) {
            if($item['id'] == 'user_events') {
                unset($menus[$key]);
            }
            if($item['id'] == 'enrolled_courses') {
                $menus[$key]['menu_title'] = esc_html__( 'Enrolled Courses & Albums', 'slms' );
            }
        }

        return $menus;
    }

    public static function get_courses_events(array $args = [], $for_stream = ''){
        $events = [];

        $user_id = (isset($args['user_id'])) ? intval($args['user_id']) : get_current_user_id();

        $all_courses = stm_lms_get_user_courses( $user_id );

        $date_format = get_option( 'date_format' );

        foreach ( $all_courses as $course_user ) {

            if ( get_post_type( $course_user['course_id'] ) == 'stm-courses' ) {

                $post_id = intval($course_user['course_id']);

                $stream = '';
                $post_terms = wp_get_post_terms($post_id, 'stm_lms_course_taxonomy', ['fields' => 'ids']);
                if(count($post_terms)) {
                    foreach ($post_terms as $term_id) {
                        $term_meta = get_term_meta($term_id, 'lite_category_name', true);
                        if(!empty($term_meta)) {
                            $stream = $term_meta;
                            break;
                        }
                    }
                }
//
//                if($stream !== 'webinar' && $stream !== 'event') {
//                    continue;
//                }

                if(!empty($stream) && !empty($for_stream)) {
//                    if($stream !== $for_stream) {
//                        continue;
//                    }
                    if($for_stream == 'events') {
                        if(!in_array($stream, ['webinar','event'])) {
                            continue;
                        }
                    }
                    if($for_stream == 'courses') {
                        if(!in_array($stream, ['shravana_mangalam','course'])) {
                            continue;
                        }
                    }
                }

                $date_start = get_post_meta($post_id, 'start_event_date', true);
                $date_end = get_post_meta($post_id, 'end_event_date', true);

                $time_start = get_post_meta($post_id, 'start_event_time', true);
                $time_end = get_post_meta($post_id, 'end_event_time', true);

                $event_repetition_days = get_post_meta($post_id, 'event_repetition_days', true);

                $start = (!empty($date_start)) ? strtotime($date_start) : 0;
                $end = (!empty($date_end)) ? strtotime($date_end) : 0;

                $start = (!empty($start)) ? $start + stmTimeToSeconds($time_start) : $start;
                $end = (!empty($end)) ? $end + stmTimeToSeconds($time_end) : $end;

                if(empty($start) || empty($end)) {
                    continue;
                }

                if(!empty($event_repetition_days)) {
                    $event_repetition_days = explode(',', $event_repetition_days);
                    $event_repetition_days = array_filter($event_repetition_days);
                    $event_repetition_days = array_map('trim', $event_repetition_days);
                    $event_repetition_days = array_map('strtolower', $event_repetition_days);
                    $event_repetition_days = array_map('ucfirst', $event_repetition_days);

                    for($i = $start; $i < $end; $i += 86400){
                        $day_of_week = date('D', $i);
                        $day_of_week = substr($day_of_week, 0, 2);
                        if(in_array($day_of_week, $event_repetition_days)) {
                            $events[] = [
                                'title' => get_the_title($post_id),
                                'start' => date('Y-m-d\TH:i:s', $i),
                                'end' => date('Y-m-d\TH:i:s', $i),
                                'url' => get_the_permalink($post_id),
                                'type' => get_post_type($post_id),
                                'type_label' => __('Event','slms'),
                                'start_formatted' => date_i18n($date_format, $start),
                            ];
                        }
                    }
                } else {
                    $events[] = [
                        'title' => get_the_title($post_id),
                        'start' => date('Y-m-d\TH:i:s', $start),
                        'end' => date('Y-m-d\TH:i:s', $end),
                        'url' => get_the_permalink($post_id),
                        'type' => get_post_type($post_id),
                        'type_label' => __('Event','slms'),
                        'start_formatted' => date_i18n($date_format, $start),
                    ];
                }


            }
        }

        return $events;

    }


}

new SLMS_Events();