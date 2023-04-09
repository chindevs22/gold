<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class EventM_Admin {
    
    private static $instance = null;
    private $current_page= null;
    public $pages = array('event_magic', 'em_add', 'em_event_types', 'em_venues', 'em_bookings', 'em_booking_add', 'em_performers','em_analytics', 'em_global_settings', 'em_frontend', 'em_extensions', 'em_want_more', 'em_event_type_add', 'em_venue_add', 'em_dashboard', 'em_sponsers', 'em_social','em_new_event','em_ticket_templates','em_analytics', 'em_coupons', 'em_add_new_coupon', 'em_add_new_attendee', 'em_new_attendee_booking', 'em_attendee_event', 'em_google_import_export_events', 'em_file_import_export_events', 'em_bulk_emails', 'em_offers_section', 'em_zapiers', 'em_add_new_trigger', 'em_event_organizers', 'em_event_organizer_add', 'em_user_capabilities');
    
    public static function get_instance()
    {   
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', array($this, 'menus'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue'));
        add_action('admin_init', array($this,'plugin_redirect'));
        //add_action('admin_footer', array($this,'deactivation_feedback_form'));
        // admin header promotion banner
        add_action('event_magic_admin_promotion_banner',array($this,'event_magic_admin_promotion_banner_data'));
        add_action('event_magic_admin_bottom_premium_banner',array($this,'event_magic_admin_bottom_premium_banner_data'));
        $this->current_page = isset($_GET['page']) ? $_GET['page'] : '';
    }

    public function menus() {
        $global_options = get_option(EM_GLOBAL_SETTINGS);
        $capability = (current_user_can('administrator') ? 'manage_options' : 'view_events');
        add_menu_page(__('EventPrime', 'eventprime-event-calendar-management'), __('EventPrime', 'eventprime-event-calendar-management'), $capability, "event_magic", array($this, 'events'), 'dashicons-tickets-alt', '25');
        add_submenu_page("event_magic", __('Events', 'eventprime-event-calendar-management'), __('Events', 'eventprime-event-calendar-management'), $capability, "event_magic", array($this, 'events'));

        $type_capability = (current_user_can('administrator') ? 'manage_options' : 'view_event_types');
        add_submenu_page("event_magic", __('Event Types', 'eventprime-event-calendar-management'), __('Event Types', 'eventprime-event-calendar-management'), $type_capability, "em_event_types", array($this, 'event_types'));

        $site_capability = (current_user_can('administrator') ? 'manage_options' : 'view_event_sites');
        add_submenu_page("event_magic", __('Event Sites/Locations', 'eventprime-event-calendar-management'), __('Event Sites/Locations', 'eventprime-event-calendar-management'), $site_capability, "em_venues", array($this, 'venues'));

        $performers_text = em_global_settings_button_title('Performers');
        $performer_capability = (current_user_can('administrator') ? 'manage_options' : 'view_event_performers');
        add_submenu_page("event_magic", $performers_text, $performers_text, $performer_capability, "em_performers", array($this, 'performers'));

        $organizers_text = em_global_settings_button_title('Organizers');
        $organizer_capability = (current_user_can('administrator') ? 'manage_options' : 'view_event_organizers');
        add_submenu_page("event_magic", $organizers_text, $organizers_text, $organizer_capability, "em_event_organizers", array($this, 'event_organizers'));

        add_submenu_page("event_magic", __('Bookings', 'eventprime-event-calendar-management'), __('Bookings', 'eventprime-event-calendar-management'), "manage_options", "em_bookings", array($this, 'bookings'));

        add_submenu_page("event_magic", __('Email Attendees', 'eventprime-event-calendar-management'), __('Email Attendees', 'eventprime-event-calendar-management'), "manage_options", "em_bulk_emails", array($this, 'bulk_emails'));

        $capability = (current_user_can('administrator') ? 'manage_options' : 'view_events');
        add_submenu_page("", __('Event Dashboard', 'eventprime-event-calendar-management'), __('Event Dashboard', 'eventprime-event-calendar-management'), $capability, "em_dashboard", array($this, 'events'));

        $new_performer_capability = (current_user_can('administrator') ? 'manage_options' : 'create_event_performers');
        add_submenu_page("", __('Performers', 'eventprime-event-calendar-management'), __('Performers', 'eventprime-event-calendar-management'), $new_performer_capability, "em_dashboard", array($this, 'events'));

        add_submenu_page("", __('Attendee', 'eventprime-event-calendar-management'), __('Attendee', 'eventprime-event-calendar-management'), "manage_options", "em_booking_add", array($this, 'bookings'));

        $new_type_capability = (current_user_can('administrator') ? 'manage_options' : 'create_event_types');
        add_submenu_page("", __('New Event Type', 'eventprime-event-calendar-management'), __('New Event Type', 'eventprime-event-calendar-management'), $new_type_capability, "em_event_type_add", array($this, 'event_types'));

        $new_site_capability = (current_user_can('administrator') ? 'manage_options' : 'create_event_sites');
        add_submenu_page("", __('Event Site/Location', 'eventprime-event-calendar-management'), __('Event Site/Location', 'eventprime-event-calendar-management'), $new_site_capability, "em_venue_add", array($this, 'venues'));

        $new_event_capability = (current_user_can('administrator') ? 'manage_options' : 'create_events');
        add_submenu_page("", __('Add New Event', 'eventprime-event-calendar-management'), __('Add New Event', 'eventprime-event-calendar-management'), $new_event_capability, "em_new_event", array($this, 'events'));

        $new_organizer_capability = (current_user_can('administrator') ? 'manage_options' : 'create_event_organizers');

        add_submenu_page("", __('New Event Organizer', 'eventprime-event-calendar-management'), __('New Event Organizer', 'eventprime-event-calendar-management'), $new_organizer_capability, "em_event_organizer_add", array($this, 'event_organizers'));

        $extensions = event_magic_instance()->extensions;
        
        /*
        if(!in_array('analytics', $extensions)){
            add_submenu_page("event_magic", __('Analytics', 'eventprime-event-calendar-management'), __('Analytics', 'eventprime-event-calendar-management'), "manage_options", "em_analytics", array($this, 'analytics_banner'));
        }
        if(!in_array('seating', $extensions)){
            add_submenu_page("event_magic", __('Ticket Manager', 'eventprime-event-calendar-management'), __('Ticket Manager', 'eventprime-event-calendar-management'), "manage_options", "em_ticket_templates", array($this, 'ticket_templates_banner'));
        }
        if(!in_array('coupons', $extensions)){
            add_submenu_page("event_magic", __('Coupon Codes', 'eventprime-event-calendar-management'), __('Coupon Codes', 'eventprime-event-calendar-management'), "manage_options", "em_coupons", array($this, 'coupon_codes_banner'));
        }*/
        
        do_action('event_magic_menus');

        add_submenu_page("event_magic", __('Global Settings', 'eventprime-event-calendar-management'), __('Global Settings', 'eventprime-event-calendar-management'), "manage_options", "em_global_settings", array($this, 'global_settings'));

        add_submenu_page("", __('User Capabilities', 'eventprime-event-calendar-management'), __('User Capabilities', 'eventprime-event-calendar-management'), "manage_options", "em_user_capabilities", array($this, 'user_capabilities'));

        add_submenu_page("event_magic", __('Publish Shortcodes', 'eventprime-event-calendar-management'), __('Publish Shortcodes', 'eventprime-event-calendar-management'), "manage_options", "em_frontend", array($this, 'frontend'));

        add_submenu_page("event_magic", __('Extensions', 'eventprime-event-calendar-management'), __('Extensions', 'eventprime-event-calendar-management'), "manage_options", "em_extensions", array($this, 'extensions'));

        /*if(!in_array('analytics',$extensions) || !in_array('seating',$extensions)){
            add_submenu_page("event_magic", __('More', 'eventprime-event-calendar-management'), __('More', 'eventprime-event-calendar-management'), "manage_options", "em_want_more", array($this, 'want_more'));
        }*/

        // offers menu
        if(empty($extensions) && count($extensions) < 1){
            //add_submenu_page("event_magic", __('Offers', 'eventprime-event-calendar-management'), __('Offers', 'eventprime-event-calendar-management'), "manage_options", "em_offers_section", array($this, 'offers_section'));
        }
    }


    public function enqueue() {
        $this->pages = apply_filters('event_magic_admin_pages', $this->pages);
        if (is_admin() && isset($_REQUEST['page']) && in_array($_REQUEST['page'], $this->pages)) {
            wp_enqueue_script('jquery-ui-datepicker');
            wp_enqueue_script('jquery-ui-sortable');

            wp_register_script('em-angular', EM_BASE_URL . 'includes/js/angular.js', array(), EVENTPRIME_VERSION);
            wp_register_script('dir-pagination', EM_BASE_URL . 'includes/admin/template/js/dirPagination.js', array('em-angular'), EVENTPRIME_VERSION);
            wp_register_script('em-angular-module', EM_BASE_URL . 'includes/admin/template/js/em-module.js', array('dir-pagination'), EVENTPRIME_VERSION);
            wp_localize_script('em-angular-module', 'em_ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
            wp_register_script('em-google-map', EM_BASE_URL . 'includes/js/em-map.js', array('jquery'), EVENTPRIME_VERSION);
            wp_register_script('em-timepicker', EM_BASE_URL . 'includes/admin/template/js/timepicker-addon.js', false, EVENTPRIME_VERSION);
            
            wp_register_script('em-event-controller', EM_BASE_URL . 'includes/admin/template/js/em-event-controller.js', array('em-angular-module', 'em-timepicker', 'em-google-map'), EVENTPRIME_VERSION);
            // localize the event controller and add the nonce
            wp_localize_script('em-event-controller', 'em_event_object', array('nonce' => wp_create_nonce('em_event_object_nonce')));

            wp_register_script('em-venue-controller', EM_BASE_URL . 'includes/admin/template/js/em-venue-controller.js', array('em-angular-module', 'em-timepicker', 'em-google-map'), EVENTPRIME_VERSION);
            // localize the venue controller and add the nonce
            wp_localize_script('em-venue-controller', 'em_venue_object', array('nonce' => wp_create_nonce('em_venue_object_nonce'), "delete_confirm" => esc_html__('All Events associated to Event-Type(s) will be deleted. Please confirm.', 'eventprime-event-calendar-management')));

            wp_register_script('em-event-type-controller', EM_BASE_URL . 'includes/admin/template/js/em-event-type-controller.js', array('em-angular-module'), EVENTPRIME_VERSION);
            // localize the event controller and add the nonce
            wp_localize_script('em-event-type-controller', 'em_event_type_object', array('nonce' => wp_create_nonce('em_event_type_object_nonce'), "delete_confirm" => esc_html__('All Events associated to Event-Type(s) will be deleted. Please confirm.', 'eventprime-event-calendar-management')));

            wp_register_script('em-booking-controller', EM_BASE_URL . 'includes/admin/template/js/em-booking-controller.js', array('em-angular-module'), EVENTPRIME_VERSION);
            // localize the booking controller and add the nonce
            wp_localize_script('em-booking-controller', 'em_booking_object', array('nonce' => wp_create_nonce('em_booking_object_nonce')));

            wp_register_script('em-bulk-emails-controller', EM_BASE_URL . 'includes/admin/template/js/em-bulk-emails-controller.js', array('em-angular-module'), EVENTPRIME_VERSION);

            wp_register_script('em-performer-controller', EM_BASE_URL . 'includes/admin/template/js/em-performer-controller.js', array('em-angular-module'), EVENTPRIME_VERSION);
            // localize the performer controller and add the nonce
            wp_localize_script('em-performer-controller', 'em_performer_object', array('nonce' => wp_create_nonce('em_performer_object_nonce')));
            
            wp_register_script('em-global-settings-controller', EM_BASE_URL . 'includes/admin/template/js/em-global-settings-controller.js', array('em-angular-module','jquery-ui-datepicker'), EVENTPRIME_VERSION);
            wp_register_script('google_charts', "https://www.gstatic.com/charts/loader.js", array(), EVENTPRIME_VERSION);
            wp_register_script('moment', EM_BASE_URL . 'includes/templates/js/moment.min.js', array(), EVENTPRIME_VERSION);
            //wp_register_script('em-full-calendar',EM_BASE_URL.'includes/templates/js/calendar-3.9.0.js', array('jquery', 'moment'), EVENTPRIME_VERSION);
            //wp_register_script('em-full-calendar-locales', EM_BASE_URL . 'includes/templates/js/calendar-locales-3.9.0.js', array('em-full-calendar'), EVENTPRIME_VERSION);
            wp_register_script('em-full-calendar',EM_BASE_URL.'includes/templates/js/calendar-4.4.2/core/main.min.js', array(), EVENTPRIME_VERSION);
            wp_register_script('em-full-interaction-calendar',EM_BASE_URL.'includes/templates/js/calendar-4.4.2/interaction/main.min.js', array(), EVENTPRIME_VERSION);
            wp_register_script('em-full-daygrid-calendar',EM_BASE_URL.'includes/templates/js/calendar-4.4.2/daygrid/main.min.js', array(), EVENTPRIME_VERSION);
            wp_register_script('em-full-list-calendar',EM_BASE_URL.'includes/templates/js/calendar-4.4.2/list/main.min.js', array(), EVENTPRIME_VERSION);
            wp_register_script('em-full-calendar-locales', EM_BASE_URL . 'includes/templates/js/calendar-4.4.2/core/locales-all.min.js', array('em-full-calendar'), EVENTPRIME_VERSION);
            wp_register_script('em-full-calendar-moment', EM_BASE_URL . 'includes/templates/js/calendar-4.4.2/moment/main.js', array('em-full-calendar', 'moment'), EVENTPRIME_VERSION);
            wp_enqueue_script('em-utility', EM_BASE_URL . 'includes/js/em-utility.js', array('jquery'), EVENTPRIME_VERSION);
            wp_enqueue_script('em-admin-utility', EM_BASE_URL . 'includes/admin/template/js/em-admin.js', array('jquery'), EVENTPRIME_VERSION);
            wp_localize_script('em-admin-utility','admin_vars',array('admin_url'=>admin_url(), 'ajax_url' => admin_url('admin-ajax.php')));
            wp_enqueue_script('em_joyride_js', plugin_dir_url(__DIR__) . 'admin/template/js/jquery.joyride-2.1.js', false, EVENTPRIME_VERSION);
            wp_enqueue_style('em_joyride_css', plugin_dir_url(__DIR__) . 'admin/template/css/joyride-2.1.css', false, EVENTPRIME_VERSION);
            wp_enqueue_style('jquery-style', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css', array(), EVENTPRIME_VERSION);
            wp_enqueue_script('jquery-ui-draggable', false, array('jquery'), EVENTPRIME_VERSION);
            wp_enqueue_script('em-admin-jscolor', plugin_dir_url(__DIR__) . '/admin/template/js/em-jscolor.js', false, EVENTPRIME_VERSION);
            wp_enqueue_style('em_admin_css', plugin_dir_url(__DIR__) . 'admin/template/css/em_admin.css', false, EVENTPRIME_VERSION);
            //wp_register_style('em-full-calendar-css', EM_BASE_URL . 'includes/templates/css/calendar.min.css', array(), EVENTPRIME_VERSION);
            wp_register_style('em-full-calendar-css', EM_BASE_URL . 'includes/templates/js/calendar-4.4.2/core/main.min.css', array(), EVENTPRIME_VERSION);
            wp_register_style('em-full-calendar-daygrid-css', EM_BASE_URL . 'includes/templates/js/calendar-4.4.2/daygrid/main.min.css', array(), EVENTPRIME_VERSION);
            wp_register_style('em-full-calendar-list-css', EM_BASE_URL . 'includes/templates/js/calendar-4.4.2/list/main.min.css', array(), EVENTPRIME_VERSION);

            wp_enqueue_script('em_font-awesome', EM_BASE_URL . 'includes/js/font_awesome.js', array(), EVENTPRIME_VERSION);
            wp_register_style('em-select2-css', EM_BASE_URL . 'includes/templates/css/select2.min.css', array(), EVENTPRIME_VERSION);
            wp_register_script('em-select2', EM_BASE_URL . 'includes/admin/template/js/select2.min.js', false, EVENTPRIME_VERSION);

            wp_register_script('em-price-manager-controller', EM_BASE_URL . 'includes/admin/template/js/em-price-manager-controller.js', array('em-angular-module', 'em-timepicker'), EVENTPRIME_VERSION);

            wp_localize_script('em-price-manager-controller', 'em_price_manager_cap_object', array('nonce' => wp_create_nonce('em_price_manager_cap_object_nonce')));

            wp_register_script('em-event-organizer-controller', EM_BASE_URL . 'includes/admin/template/js/em-event-organizer-controller.js', array('em-angular-module'), EVENTPRIME_VERSION);
            // localize the performer controller and add the nonce
            wp_localize_script('em-event-organizer-controller', 'em_organizer_object', array('nonce' => wp_create_nonce('em_organizer_object_nonce'), "delete_confirm" => esc_html__('All Events associated to Event-Organizer(s) will be deleted. Please confirm.', 'eventprime-event-calendar-management')));

            wp_register_script('em-user-capabilities-controller', EM_BASE_URL . 'includes/admin/template/js/em-user-capabilities-controller.js', array('em-angular-module'), EVENTPRIME_VERSION);
            wp_localize_script('em-user-capabilities-controller', 'em_user_cap_object', array('nonce' => wp_create_nonce('em_user_cap_object_nonce')));

            do_action('event_magic_admin_enqueues');
        }
    }

    public function events() {
        include( 'class-em-event.php' );
        if (get_option('event_magic_show_welcome_popup', false)) {
            delete_option('event_magic_show_welcome_popup');?>
            <script type="text/javascript">
                setTimeout(function(){
                    jQuery('#ep-activation-popup').show();
                    jQuery('.ep-modal-box-wrap').removeClass('ep-modal-box-out');
                    jQuery('.ep-modal-box-wrap').addClass('ep-modal-box-in');
                }, 5000);
            </script><?php
        }
    }

    public function venues() {
        if ('em_venues'==$this->current_page) {
            wp_enqueue_script('em-venue-controller');
            include_once('template/venues.php');
        }
        else if('em_venue_add'=== $this->current_page){
            $term_id= isset($_REQUEST['term_id']) ? absint($_REQUEST['term_id']) : 0; 
            wp_enqueue_media();
            $gmap_api_key= em_global_settings('gmap_api_key');
            if($gmap_api_key)
              wp_enqueue_script('google_map_key', 'https://maps.googleapis.com/maps/api/js?key='.$gmap_api_key.'&libraries=places', array(), EVENTPRIME_VERSION);
            wp_enqueue_script('em-venue-controller'); 
            wp_enqueue_style('jquery-style', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css', array(), EVENTPRIME_VERSION);
            include_once('template/venue_add.php');
        }
    }

    public function bookings() {
        if ('em_bookings'== $this->current_page) {
            wp_enqueue_script('em-booking-controller');
            wp_enqueue_style('jquery-style', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css', array(), EVENTPRIME_VERSION);
            $tab = isset($_GET['tab']) ? $_GET['tab'] : '';
            if($tab=='view'){
                include_once('template/booking.php');
            }
            else
            {
               include_once('template/bookings.php');
            }
        }
        elseif('em_booking_add'==$this->current_page){
            $post_id= isset($_REQUEST['post_id']) ? absint($_REQUEST['post_id']) : 0; 
            if(empty($post_id))
                return;
            wp_enqueue_script('em-booking-controller');
            include_once('template/booking.php');
        }
    }

    public function event_types() {
        if ('em_event_types'==$this->current_page) {
            wp_enqueue_script('em-event-type-controller');
            include_once('template/event_types.php');
        }
        else if('em_event_type_add'==$this->current_page){
            wp_enqueue_media();
            wp_enqueue_script('jquery-ui-slider');		
            wp_enqueue_style('jquery-ui-css','https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css',array(),EVENTPRIME_VERSION);
            wp_enqueue_script('em-event-type-controller');
            include_once('template/event_type_add.php');
        }
    }

    public function performers() {
        if ('em_performers'==$this->current_page) {
            wp_enqueue_script('em-performer-controller');
            $tab = isset($_GET['tab']) ? $_GET['tab'] : '';
            if($tab=='add'){
                include_once('template/performer_add.php');
            }
            else
            {
               include_once('template/performers.php');
            }
        }
    }

    public function global_settings() {
        if($this->current_page=='em_global_settings'){
            wp_enqueue_script('em-global-settings-controller',plugin_dir_url(__DIR__) . '/admin/template/js/em-global-settings-controller.js',false,EVENTPRIME_VERSION);
            wp_enqueue_script('em-select2', plugin_dir_url(__DIR__) . '/admin/template/js/select2.min.js', false, EVENTPRIME_VERSION);
            wp_enqueue_style('em_select2_css', plugin_dir_url(__DIR__) . 'admin/template/css/select2.min.css', false, EVENTPRIME_VERSION);
            include_once('template/global_settings.php');
        }
    }

    public function frontend() {
        include_once( 'template/frontend.php' );
    }

    public function extensions() {
        include_once( 'template/extensions.php' );
    }
    
    public function want_more() {
        include_once( 'template/want_more.php' );
    }
    
    public function plugin_redirect(){
        if (get_option('event_magic_do_activation_redirect', false)) {
            delete_option('event_magic_do_activation_redirect');
            add_option('event_magic_show_welcome_popup', true);
            wp_redirect(admin_url("admin.php?page=event_magic"));
            exit;
        }
    }
    
    public function deactivation_feedback_form() {
        // Enqueue feedback form scripts and render HTML on the Plugins backend page
        /*if (get_current_screen()->parent_base == 'plugins') {
            wp_enqueue_script('em-angular', EM_BASE_URL . 'includes/js/angular.js', array(), EVENTPRIME_VERSION);
            wp_enqueue_script('dir-pagination', EM_BASE_URL . 'includes/admin/template/js/dirPagination.js', array('em-angular'), EVENTPRIME_VERSION);
            wp_enqueue_script('em-angular-module', EM_BASE_URL . 'includes/admin/template/js/em-module.js', array('dir-pagination'), EVENTPRIME_VERSION);
            wp_localize_script('em-angular-module', 'em_ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
            wp_enqueue_script('em-feedback-controller', EM_BASE_URL . 'includes/admin/template/js/em-feedback-controller.js', array('em-angular-module'), EVENTPRIME_VERSION);
            wp_enqueue_style('em_admin_css', plugin_dir_url(__DIR__) . 'admin/template/css/em_admin.css', false, EVENTPRIME_VERSION);
            include_once('template/feedback.php');
        }*/
    }
    
    public function analytics_banner(){
        include_once('template/analytics.php' );
    }
    
    public function ticket_templates_banner(){
        include_once('template/ticket_templates.php' );
    }

    public function coupon_codes_banner(){
        include_once('template/coupon_codes.php' );
    }

    public function bulk_emails() {
        wp_enqueue_script('em-bulk-emails-controller');
        include_once('template/bulk_emails.php');
    }

    //offers page
    public function offers_section() {
        include_once('template/offers_page.php');
    }

    public function event_magic_admin_promotion_banner_data() {
        $em = event_magic_instance(); 
        // $free_extension = array('em_mailpoet', 'analytics', 'file_import_export_events');
        // $have_paid = array_diff($em->extensions, $free_extension);
        // $get_dismiss_option = get_option('event_magic_dismiss_offer_notice', false);
        // if(empty($have_paid) && empty($get_dismiss_option)){
        //     $html = '<div class="ep-notice-banner epnotice"> <div class="ep-extensions-notice">';
        //     $html .= '<div class="ep-notice-text-wrap">';
        //     $html .= __(' Best time to upgrade to the best version of EventPrime! Flat 22% off to celebrate 2022. Use coupon <b>EPNY2022</b> during checkout. ', 'eventprime-event-calendar-management');
        //     $html .= '</div>';
        //     $html .= '<div class="ep-notice-banner-button-wrap">';
        //     $html .= '<span class="ep-notice-banner-btn"><a href="'.esc_url('https://eventprime.net/extensions/?utm_source=ep_plugin&utm_medium=wp_notice&utm_campaign=ny_2022_promo').'" target="_blank">'.esc_html__('Upgrade Now', 'eventprime-event-calendar-management').'</a></span>'; 
        //     $html .= '<span class="ep-notice-banner-dismiss-btn"><a href="'.esc_url('#').'" onclick="em_dismiss_notice()" >'.esc_html__('Dismiss', 'eventprime-event-calendar-management').'</a></span>'; 
        //     $html .= '</div>';
        //     $html .= '</div></div>';
        //     echo $html;
        // }
        
    }

    public function event_organizers() {
        if ( 'em_event_organizers' == $this->current_page ) {
            wp_enqueue_script( 'em-event-organizer-controller' );
            include_once( 'template/event_organizers.php' );
        }
        else if ( 'em_event_organizer_add' == $this->current_page ) {
            wp_enqueue_media();
            wp_enqueue_script( 'jquery-ui-slider' );
            wp_enqueue_style( 'jquery-ui-css', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css', array(), EVENTPRIME_VERSION );
            wp_enqueue_script( 'em-event-organizer-controller' );
            include_once( 'template/em_event_organizer_add.php' );
        }
    }

    public function user_capabilities() {
        if( $this->current_page == 'em_user_capabilities' ) {
            wp_enqueue_script( 'em-user-capabilities-controller' );
            include_once( 'template/em_user_capabilities.php' );
        }
    }

    public function event_magic_admin_bottom_premium_banner_data() {?>
        <a href="admin.php?page=em_extensions" target="_blank" class="ep-promo-banner-wrap"> 
            <div class="ep-upgrade-banner">        
                <img src="<?php echo esc_url(EM_BASE_URL . 'includes/admin/template/images/svg/ep-promo-banner.svg'); ?>">
                </div>

        </a><?php
    }

}

EventM_Admin::get_instance();