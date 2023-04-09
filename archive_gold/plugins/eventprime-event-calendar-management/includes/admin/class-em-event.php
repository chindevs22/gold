<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class EventM_Event {

    public function __construct() {
        $page = isset($_GET['page']) ? $_GET['page'] : '';
        if ('em_dashboard' === $page) {
            $this->dashboard();
        } 
        else if('em_new_event' === $page){
            $this->new_event();
        }
        else if('event_magic' === $page) {
            $this->events();
        }
    }

    protected function dashboard() {
        $tab = isset($_GET['tab']) ? $_GET['tab'] : '';
        $post_id = isset($_GET['post_id']) ? absint($_GET['post_id']) : 0;
        if (empty($post_id)) {
            return;
        }
        $event_service = EventM_Factory::get_service('EventM_Service');
        $event = $event_service->load_model_from_db($post_id);
        // default html for custom booking confirmation email
        if(empty($event->custom_booking_confirmation_email_body)){
            ob_start();
            include(EM_BASE_DIR . 'includes/admin/template/dashboard/event-booking-confirmation-email.html');
            $custom_booking_confirmation_email_body = ob_get_clean();
            update_post_meta( $post_id, 'em_custom_booking_confirmation_email_body', $custom_booking_confirmation_email_body );
        }
        if (!empty($tab)) {
            switch ($tab) {
                case 'setting': $this->setting();
                    break;
                case 'venue': $this->venue();
                    break;
                case 'performer': $this->performer();
                    break;
                case 'organizer': $this->organizer();
                    break;
                case 'social': $this->social_integration();
                    break;
                case 'email': $this->email();
                    break;
                case 'price_manager': $this->price_manager();
                    break;
                case 'add_price_manager': $this->add_price_manager();
                    break;
            }
            do_action('event_magic_dashboard_' . $tab . '_tab', $event->id);
        } else {
            include_once('template/dashboard/dashboard.php');
        }
    }

    protected function venue() {
        $post_id = isset($_GET['post_id']) ? absint($_GET['post_id']) : 0;
        wp_enqueue_script('em-event-controller');
        include_once('template/dashboard/venue.php');
    }

    protected function performer() {
        $post_id = isset($_GET['post_id']) ? absint($_GET['post_id']) : 0;
        wp_enqueue_script('em-event-controller', plugin_dir_url(__DIR__) . '/admin/template/js/em-event-controller.js', false, EVENTPRIME_VERSION);
        include_once('template/dashboard/performers.php');
    }
    
    protected function organizer() {
        $post_id = isset($_GET['post_id']) ? absint($_GET['post_id']) : 0;
        wp_enqueue_script('em-event-controller', plugin_dir_url(__DIR__) . '/admin/template/js/em-event-controller.js', false, EVENTPRIME_VERSION);
        include_once('template/dashboard/organizer.php');
    }

    protected function setting() {
        $post_id = isset($_GET['post_id']) ? absint($_GET['post_id']) : 0;
        wp_enqueue_script('em-event-controller', plugin_dir_url(__DIR__) . '/admin/template/js/em-event-controller.js', false, EVENTPRIME_VERSION);
        include_once('template/dashboard/setting.php');
    }

    protected function social_integration() {
        $post_id = isset($_GET['post_id']) ? absint($_GET['post_id']) : 0;
        wp_enqueue_script('em-event-controller', plugin_dir_url(__DIR__) . '/admin/template/js/em-event-controller.js', false, EVENTPRIME_VERSION);
        include_once('template/dashboard/social.php');
    }
    
    protected function events() {
        $setting_service = EventM_Factory::get_service('EventM_Setting_Service');
        $gs = $setting_service->load_model_from_db();
        $locale = em_get_calendar_locale();
        // datepicker format from global settings. index 0 for js
        $datepicker_format = explode('&', em_global_settings('datepicker_format'))[0];
        // capabilities for add/edit events
        $dayClickAllowed = true;
        if( empty( em_check_context_user_capabilities( array( 'create_events', 'edit_events', 'edit_others_events' ) ) ) ){
            $dayClickAllowed = false;
        }
        wp_enqueue_script('em-event-controller');
        wp_localize_script('em-event-controller','em_calendar_data',array(
            'time_format'       => $gs->time_format,
            'week_start'        => get_option('start_of_week'),
            'locale'            => $locale,
            // datepicker format from global settings. index 0 for js
            'datepicker_format' => (!empty($datepicker_format)) ? $datepicker_format : 'mm/dd/yy',
            'dayClickAllowed'   => $dayClickAllowed
        ));
        wp_enqueue_style('em-full-calendar-css');
        wp_enqueue_style('em-full-calendar-daygrid-css');
        wp_enqueue_style('em-full-calendar-list-css');
        wp_enqueue_script('em-full-calendar');
        wp_enqueue_script('em-full-interaction-calendar');
        wp_enqueue_script('em-full-daygrid-calendar');
        wp_enqueue_script('em-full-list-calendar');
        wp_enqueue_script('em-full-calendar-locales');
        wp_enqueue_script('em-full-calendar-moment');
        wp_enqueue_media();
        include_once('template/events.php');
    }

    protected function new_event(){
        wp_enqueue_script('em-event-controller');
        include_once('template/new_event.php');
    }

    protected function email() {
        $post_id = isset($_GET['post_id']) ? absint($_GET['post_id']) : 0;
        wp_enqueue_script('em-event-controller', plugin_dir_url(__DIR__) . '/admin/template/js/em-event-controller.js', false, EVENTPRIME_VERSION);
        include_once('template/dashboard/email.php');
    }

    /**
     * Price Manager Dashboard
     */
    protected function price_manager() {
        $post_id = isset($_GET['post_id']) ? absint(sanitize_text_field($_GET['post_id'])) : 0;
        //wp_enqueue_script('em-price-manager-controller', plugin_dir_url(__DIR__) . '/admin/template/js/em-price-manager-controller.js', false, EVENTPRIME_VERSION);
        wp_enqueue_script('em-price-manager-controller');
        wp_enqueue_script('jquery-ui-sortable');
        include_once('template/dashboard/price_manager.php');
    }
    /**
     * Add Price Manager
     */
    protected function add_price_manager() {
        $post_id = isset($_GET['post_id']) ? absint(sanitize_text_field($_GET['post_id'])) : 0;
        $option_id = isset($_GET['option_id']) ? absint(sanitize_text_field($_GET['option_id'])) : 0;
        wp_enqueue_script('em-price-manager-controller');
        wp_enqueue_media();
        include_once('template/dashboard/add_price_manager.php');
    }
}

new EventM_Event;
