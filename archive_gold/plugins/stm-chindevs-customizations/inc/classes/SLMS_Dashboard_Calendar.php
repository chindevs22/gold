<?php

class SLMS_Dashboard_Calendar {

    public function __construct()
    {
        add_filter( 'stm_lms_menu_items', array($this, 'user_menu_items'), 15 );
        add_filter( 'stm_lms_custom_routes_config', array($this, 'page_routes'), 15 );
        add_action( 'wp_enqueue_scripts', array($this, 'enqueue_script_styles'), 15 );
    }

    public function enqueue_script_styles(){
        $lms_template_current = get_query_var( 'lms_template' );
        if($lms_template_current == 'stm-lms-user-calendar') {
            wp_enqueue_style( 'slms-full-calendar', SLMS_URL . 'assets/css/full-calendar.css', array( 'stm_theme_styles' ), SLMS_VERSION, 'all' );
            wp_enqueue_script( 'slms-full-calendar', SLMS_URL . 'assets/js/full-calendar.min.js', array( 'jquery' ), SLMS_VERSION, true );
            wp_enqueue_script( 'slms-calendar-init', SLMS_URL . 'assets/js/calendar-init.js', array( 'jquery' ), SLMS_VERSION, true );
            wp_localize_script('slms-calendar-init', 'slms_calendar', $this->calendar_data());
        }
    }

    public function user_menu_items($menus){

        $menus[] = array(
            'order'        => 110,
            'id'           => 'calendar',
            'slug'         => 'calendar',
            'lms_template' => 'stm-lms-user-calendar',
            'menu_title'   => esc_html__( 'Calendar', 'slms' ),
            'menu_icon'    => 'fa-book',
            'menu_url'     => self::calendar_url(),
            'menu_place'   => 'learning',
        );

        return $menus;
    }

    public function page_routes($page_routes){
        $page_routes['user_url']['sub_pages']['calendar'] = array(
            'template'  => 'stm-lms-user-calendar',
            'protected' => true,
            'url'       => 'calendar',
        );

        return $page_routes;
    }

    public static function calendar_url() {
        return STM_LMS_User::login_page_url() . 'calendar';
    }

    public function calendar_data(){

//        $events = array_merge(SLMS_Events::get_courses_events(), SLMS_Events::get_webinars());
        $events = SLMS_Events::get_courses_events();
//        $events = [];

        return [
            'events' => $events
        ];

    }

}

new SLMS_Dashboard_Calendar();