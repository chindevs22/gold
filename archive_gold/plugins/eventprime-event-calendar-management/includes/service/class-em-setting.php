<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class EventM_Setting_Service {
    
    private $dao;
    private static $instance = null; 
    
    private function __construct() {
        $this->dao= new EventM_Global_Settings_DAO();
    }
    
    public static function get_instance()
    {   
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /*
     * Loading settings data on REST call
     */
    public function load_edit_page()
    {
        global $wp_roles;
        $options = $this->load_model_from_db();
        $options->pages= $this->load_global_pages();
        $options->base_path= EM_BASE_URL.'includes/admin/template/';
        $options->currencies= em_array_to_options(EventM_Constants::$currencies);
        $options->time_formats= $this->load_time_formats();
        $options->status_list= array(
            "publish" => __('Active','eventprime-event-calendar-management'),
            "draft" => __('Draft','eventprime-event-calendar-management')
        );
        $options->default_calendar_date = date(get_option('date_format'),$options->default_calendar_date);
        $options->user_roles = array();
        $userRoles = $wp_roles->roles;
        if(!empty($userRoles)){
            foreach ($userRoles as $key => $value) {
                $options->user_roles[$key] = $value['name'];
            }
        }
        $viewOptions = array("month" => "Month", "week" => "Week", "day" => "Day", "card" => "Card", "listweek" => "List Week", "masonry" => "Masonry", "slider" => "Slider", "list" => "List");
        $options->front_view_option = $viewOptions;
        $fesSections = array(
            'fes_event_text_color' => esc_html__('Event Text Color','eventprime-event-calendar-management'),
            'fes_event_featured_image' => esc_html__('Event Featured Image','eventprime-event-calendar-management'),
            'fes_event_booking'  => esc_html__('Event Booking','eventprime-event-calendar-management'),
            'fes_event_link'  => esc_html__('Event Link','eventprime-event-calendar-management'),
            'fes_event_type'  => esc_html__('Event Type','eventprime-event-calendar-management'),
            'fes_new_event_type'  => esc_html__('Add New Event Type','eventprime-event-calendar-management'),
            'fes_event_location'  => esc_html__('Event Sites/Locations','eventprime-event-calendar-management'),
            'fes_new_event_location'  => esc_html__('Add New Event Sites/Locations','eventprime-event-calendar-management'),
            'fes_event_performer'  => esc_html__('Event Performer','eventprime-event-calendar-management'),
            'fes_new_event_performer'  => esc_html__('Add New Event Performer','eventprime-event-calendar-management'),
            'fes_event_organizer'  => esc_html__('Event Organizer','eventprime-event-calendar-management'),
            'fes_new_event_organizer'  => esc_html__('Add New Event Organizer','eventprime-event-calendar-management'),
            'fes_event_more_options'  => esc_html__('Event More Options','eventprime-event-calendar-management')
        );
        $options->fes_sections = $fesSections;
        $fesRequired = array(
            'fes_event_description' => esc_html__('Event Description','eventprime-event-calendar-management'),
            'fes_event_booking'  => esc_html__('Event Booking','eventprime-event-calendar-management'),
            'fes_booking_price'  => esc_html__('Event Booking Price','eventprime-event-calendar-management'),
            'fes_event_link'  => esc_html__('Event Link','eventprime-event-calendar-management'),
            'fes_event_type'  => esc_html__('Event Type','eventprime-event-calendar-management'),
            'fes_event_location'  => esc_html__('Event Sites/Locations','eventprime-event-calendar-management'),
            'fes_event_performer'  => esc_html__('Event Performer','eventprime-event-calendar-management'),
            'fes_event_organizer'  => esc_html__('Event Organizer','eventprime-event-calendar-management')
        );
        $options->fes_required = $fesRequired;
        // custom fields options
        $fieldOptions = array(
            'text'  => esc_html__('Text','eventprime-event-calendar-management'), 
            'email' => esc_html__('Email','eventprime-event-calendar-management'), 
            'tel'   => esc_html__('Tel','eventprime-event-calendar-management'), 
            'date'  => esc_html__('date','eventprime-event-calendar-management')
        );
        $options->custom_fields_option = $fieldOptions;
        $custom_field_data['text']  = $this->get_text_field(':em:', 'booking');
        $custom_field_data['email'] = $this->get_email_field(':em:', 'booking');
        $custom_field_data['tel']   = $this->get_tel_field(':em:', 'booking');
        $custom_field_data['date']  = $this->get_date_field(':em:', 'booking');
        $options->custom_field_data = $custom_field_data;
        //currency options
        $currencyOptions = array(
            'before'  => esc_html__('$10 (Before)','eventprime-event-calendar-management'), 
            'before_space' => esc_html__('$ 10 (Before, with space)','eventprime-event-calendar-management'), 
            'after'   => esc_html__('10$ (After)','eventprime-event-calendar-management'), 
            'after_space'  => esc_html__('10 $ (After, with space)','eventprime-event-calendar-management')
        );
        $options->currency_view_option = $currencyOptions;

        $buttonsections = array('Book Now', 'Proceed', 'Checkout', 'Bookings not allowed', 'Bookings not started yet', 'Bookings Closed', 'Bookings Expired', 'All Seats Booked', 'View Details', 'Login to View', 'Click for Details', 'Register', 'Login');

        $labelsections = array('Performer', 'Performers', 'Organizer', 'Organizers', 'Please enter number of tickets you wish to book');
        
        $em = event_magic_instance();
        if ( in_array( 'seating', $em->extensions ) ){
            $labelsections[] = 'ALL EYES THIS WAY';
        }
        $options->buttonsections = $buttonsections;
        $options->labelsections = $labelsections;

        $options->load_extension_services = array();

        $urlsec = array(
            'event_page_type_url' => array("title" => esc_html__('Events Subdirectory', 'eventprime-event-calendar-management'), "desc" => esc_html__('Define subdirectory for single event pages. The title of the event will be automatically added to this to form the complete URL to your event page. For example: https://yourwebsite.com/subdirectory/event-name', 'eventprime-event-calendar-management')),
            'performer_page_type_url' => array("title" => esc_html__('Performers Subdirectory', 'eventprime-event-calendar-management'), "desc" => esc_html__('Define subdirectory for single performer pages. The name of the performer will be automatically added to this to form the complete URL to your performer page. For example: https://yourwebsite.com/subdirectory/performer-name', 'eventprime-event-calendar-management')),
            'organizer_page_type_url' => array("title" => esc_html__('Organizers Subdirectory', 'eventprime-event-calendar-management'), "desc" => esc_html__('Define subdirectory for single organizer pages. The name of the organizer will be automatically added to this to form the complete URL to your organizer page. For example: https://yourwebsite.com/subdirectory/organizer-name', 'eventprime-event-calendar-management')),
            'venues_page_type_url' => array("title" => esc_html__('Event Sites Subdirectory', 'eventprime-event-calendar-management'), "desc" => esc_html__('Define subdirectory for single sites/ venues pages. The name of the site will be automatically added to this to form the complete URL to your site page. For example: https://yourwebsite.com/subdirectory/site-name', 'eventprime-event-calendar-management')),
            'types_page_type_url' => array("title" => esc_html__('Event Types Subdirectory', 'eventprime-event-calendar-management'), "desc" => esc_html__('Define subdirectory for single event type pages. The title of the event type will be automatically added to this to form the complete URL to your event type page. For example: https://yourwebsite.com/subdirectory/event-type-title', 'eventprime-event-calendar-management')),
        );
        $options->seo_url = $urlsec;
        $options->seo_urls = [];
        if(!isset($options->seo_urls['event_page_type_url'])) {
            $options->seo_urls['event_page_type_url'] = em_custom_type_page_url('event');
        }
        if(!isset($options->seo_urls['performer_page_type_url'])) {
            $options->seo_urls['performer_page_type_url'] = em_custom_type_page_url('performer');
        }
        if(!isset($options->seo_urls['organizer_page_type_url'])) {
            $options->seo_urls['organizer_page_type_url'] = em_custom_type_page_url('organizer');
        }
        if(!isset($options->seo_urls['venues_page_type_url'])) {
            $options->seo_urls['venues_page_type_url'] = em_custom_type_page_url('venues');
        }
        if(!isset($options->seo_urls['types_page_type_url'])) {
            $options->seo_urls['types_page_type_url'] = em_custom_type_page_url('types');
        }

        $options->performer_front_view_options = $options->type_front_view_options = $options->venue_front_view_options = $options->organizer_front_view_options = ep_listing_page_view_options();
        
        $options->single_performer_event_front_view_options = $options->single_type_efv_options = $options->single_venue_efv_options = $options->single_organizer_efv_options = ep_upcoming_event_view_options();
        $options->is_offline_payment_enabled = (in_array( 'offline_payments', $em->extensions )) ? 1 : 0;
        $options = apply_filters('em_load_gs_ext_options',$options);

        return $options;
    }

    public function save($model) {   
        $model = $this->format_model_to_save($model);
        // check if any slug changed
        $urlChanged = 0;
        $seo_urls = em_global_settings('seo_urls');
        if(isset($seo_urls->event_page_type_url)) {
            $ept = $seo_urls->event_page_type_url;
            $mept = $model->seo_urls->event_page_type_url;
            if($ept !== $mept){
                $urlChanged = 1;
            }
        }
        $this->dao->save($model);
        if(!empty($urlChanged)){
            flush_rewrite_rules();
        }
    }
    
    
    public function load_global_pages(){
        $list= array();
        $pages = get_pages(array(
            'post_status'=>'publish',
             'numberposts'=>-1,
            ) );
         
         if (!empty($pages)) {
           foreach ($pages as $page) {
                $tmp = new stdClass();
                $tmp->id = $page->ID;
                $tmp->name = $page->post_title;
                $list[] = $tmp;
            }
       }
       return $list; 
    }
    
    public function load_time_formats() {
        $list = array(
            'h:mmt' => __('12-hour','eventprime-event-calendar-management'),
            'HH:mm' => __('24-hour','eventprime-event-calendar-management')
        );
        
        return $list;
    }
    
    public function load_model_from_db()
    {
        $model= $this->dao->get();
        $model->gcal_sharing= absint($model->gcal_sharing);
        $model->events_page= absint($model->events_page);
        $model->event_types= absint($model->event_types);
        $model->performers_page= absint($model->performers_page);
        $model->profile_page= absint($model->profile_page);
        $model->venues_page= absint($model->venues_page);
        $model->booking_page= absint($model->booking_page);
        $model->event_submit_form= absint($model->event_submit_form);
        $model->paypal_processor= absint($model->paypal_processor);
        $model->payment_test_mode= absint($model->payment_test_mode);
        if(empty($model->event_submitted_email) || is_null($model->event_submitted_email)){
            ob_start();
            include(EM_BASE_DIR . 'includes/mail/event_submitted.html');
            $model->event_submitted_email = ob_get_clean();
        }
        if(empty($model->event_approved_email) || is_null($model->event_approved_email)){
            ob_start();
            include(EM_BASE_DIR . 'includes/mail/event_approved.html');
            $model->event_approved_email = ob_get_clean();
        }
        if(empty($model->ues_confirm_message)){
            $model->ues_confirm_message = __('Thank you for submitting your event. We will review and publish it soon.','eventprime-event-calendar-management');
        }
        if(empty($model->ues_login_message)){
            $model->ues_login_message = __('Please login to submit your event.','eventprime-event-calendar-management');
        }
        if(empty($model->default_calendar_date)){
            $model->default_calendar_date = em_get_local_timestamp();
        }
        if(empty($model->ues_restricted_submission_message)){
            $model->ues_restricted_submission_message = __('You are not authorised to access this page. Please contact with your administrator.','eventprime-event-calendar-management');
        }
        $model->booking_details_page = absint($model->booking_details_page);

        $model->event_organizers= absint($model->event_organizers);

        return $model;
    }
    
    private function format_model_to_save($model){
        $model->gcal_sharing= absint($model->gcal_sharing);
        $model->events_page= absint($model->events_page);
        $model->event_types= absint($model->event_types);
        $model->performers_page= absint($model->performers_page);
        $model->profile_page= absint($model->profile_page);
        $model->venues_page= absint($model->venues_page);
        $model->booking_page= absint($model->booking_page);
        $model->event_submit_form= absint($model->event_submit_form);
        $model->paypal_processor= empty($model->paypal_processor) ? 0 : 1;
        $model->payment_test_mode= empty($model->payment_test_mode) ? 0 : 1;
        $model->paypal_email=  sanitize_email($model->paypal_email);
        $model->paypal_api_username= sanitize_text_field($model->paypal_api_username);
        $model->paypal_api_password= sanitize_text_field($model->paypal_api_password);
        $model->paypal_api_sig= sanitize_text_field($model->paypal_api_sig);
        $model->ues_confirm_message= sanitize_text_field($model->ues_confirm_message);
        $model->ues_login_message= sanitize_text_field($model->ues_login_message);
        $model->ues_default_status= sanitize_text_field($model->ues_default_status);
        $model->enable_default_calendar_date= absint($model->enable_default_calendar_date);
        $model->default_calendar_date= em_timestamp(sanitize_text_field($model->default_calendar_date),get_option('date_format'));
        $model->ues_restricted_submission_message= sanitize_text_field($model->ues_restricted_submission_message);

        $model->booking_details_page = absint($model->booking_details_page);
        return $model;
    }
    
    public function map_request_to_model($model=null)
    {  
        $settings= new EventM_Global_Settings_Model();
        $data= (array) $model;
        
        if(!empty($data) && is_array($data))
        {
            foreach($data as $key=>$val)
            {
                if(property_exists($settings, $key)){
                    $settings->{$key}= $val;
                }
            }
        }
        return $settings;
    }

    public function get_text_field($key, $prefix = 'booking') {
        $html = '<li id="em_'.$prefix.'_fields_'.$key.'" class="em_custom_'.$prefix.'_field_html" data-keyval="'.$key.'">';
            $html .= '<span class="em_'.$prefix.'_field_type">'.__('Text', 'eventprime-event-calendar-management').'</span>';
            $html .= '<span ng-click="removeFieldFromCustomizer('.$key.')" class="em_'.$prefix.'_field_remove" id="em_'.$prefix.'_remove'.$key.'"></span>';
            $html .= '<p class="em_'.$prefix.'_field_options">
                <label>
                    <input type="checkbox" ng-true-value="1" ng-false-value="0" name="custom_'.$prefix.'_field_data[]" ng-model="data.options.custom_'.$prefix.'_field_data['.$key.'].required" id="'.$prefix.'_required'.$key.'" />
                    '.__('Required Field', 'eventprime-event-calendar-management').'
                </label>
            </p>';
            $html .= '<div>
                <input type="text" ng-model="data.options.custom_'.$prefix.'_field_data['.$key.'].type" value="text" style="display:none;" />
                <input type="text" ng-model="data.options.custom_'.$prefix.'_field_data['.$key.'].label" id="'.$prefix.'_label'.$key.'" placeholder="'.esc_attr__('Insert a label for this field', 'eventprime-event-calendar-management').'" />
            </div>';
        $html .= '</li>';
        return $html;
    }

    public function get_email_field($key, $prefix = 'booking') {
        $html = '<li id="em_'.$prefix.'_fields_'.$key.'" class="em_custom_'.$prefix.'_field_html" data-keyval="'.$key.'">';
            $html .= '<span class="em_'.$prefix.'_field_type">'.__('Email', 'eventprime-event-calendar-management').'</span>';
            $html .= '<span ng-click="removeFieldFromCustomizer('.$key.')" class="em_'.$prefix.'_field_remove" id="em_'.$prefix.'_remove'.$key.'"></span>';
            $html .= '<p class="em_'.$prefix.'_field_options">
                <label>
                    <input type="checkbox" ng-true-value="1" ng-false-value="0" name="custom_'.$prefix.'_field_data[]" ng-model="data.options.custom_'.$prefix.'_field_data['.$key.'].required" id="'.$prefix.'_required'.$key.'" />
                    '.__('Required Field', 'eventprime-event-calendar-management').'
                </label>
            </p>';
            $html .= '<div>
                <input type="text" ng-model="data.options.custom_'.$prefix.'_field_data['.$key.'].type" value="email" style="display:none;" />
                <input type="text" ng-model="data.options.custom_'.$prefix.'_field_data['.$key.'].label" id="'.$prefix.'_label'.$key.'" placeholder="'.esc_attr__('Insert a label for this field', 'eventprime-event-calendar-management').'" />
            </div>';
        $html .= '</li>';
        return $html;
    }

    public function get_tel_field($key, $prefix = 'booking') {
        $html = '<li id="em_'.$prefix.'_fields_'.$key.'" class="em_custom_'.$prefix.'_field_html" data-keyval="'.$key.'">';
            $html .= '<span class="em_'.$prefix.'_field_type">'.__('Tel', 'eventprime-event-calendar-management').'</span>';
            $html .= '<span ng-click="removeFieldFromCustomizer('.$key.')" class="em_'.$prefix.'_field_remove" id="em_'.$prefix.'_remove'.$key.'"></span>';
            $html .= '<p class="em_'.$prefix.'_field_options">
                <label>
                    <input type="checkbox" ng-true-value="1" ng-false-value="0" name="custom_'.$prefix.'_field_data[]" ng-model="data.options.custom_'.$prefix.'_field_data['.$key.'].required" id="'.$prefix.'_required'.$key.'" />
                    '.__('Required Field', 'eventprime-event-calendar-management').'
                </label>
            </p>';
            $html .= '<div>
                <input type="text" ng-model="data.options.custom_'.$prefix.'_field_data['.$key.'].type" value="tel" style="display:none;" />
                <input type="text" ng-model="data.options.custom_'.$prefix.'_field_data['.$key.'].label" id="'.$prefix.'_label'.$key.'" placeholder="'.esc_attr__('Insert a label for this field', 'eventprime-event-calendar-management').'" />
            </div>';
        $html .= '</li>';
        return $html;
    }

    public function get_date_field($key, $prefix = 'booking') {
        $html = '<li id="em_'.$prefix.'_fields_'.$key.'" class="em_custom_'.$prefix.'_field_html" data-keyval="'.$key.'">';
            $html .= '<span class="em_'.$prefix.'_field_type">'.__('Date', 'eventprime-event-calendar-management').'</span>';
            $html .= '<span ng-click="removeFieldFromCustomizer('.$key.')" class="em_'.$prefix.'_field_remove" id="em_'.$prefix.'_remove'.$key.'"></span>';
            $html .= '<p class="em_'.$prefix.'_field_options">
                <label>
                    <input type="checkbox" ng-true-value="1" ng-false-value="0" name="custom_'.$prefix.'_field_data[]" ng-model="data.options.custom_'.$prefix.'_field_data['.$key.'].required" id="'.$prefix.'_required'.$key.'" />
                    '.__('Required Field', 'eventprime-event-calendar-management').'
                </label>
            </p>';
            $html .= '<div>
                <input type="text" ng-model="data.options.custom_'.$prefix.'_field_data['.$key.'].type" value="date" style="display:none;" />
                <input type="text" class="em-cbf-datepicker" ng-model="data.options.custom_'.$prefix.'_field_data['.$key.'].label" id="'.$prefix.'_label'.$key.'" placeholder="'.esc_attr__('Insert a label for this field', 'eventprime-event-calendar-management').'" />
            </div>';
        $html .= '</li>';
        return $html;
    }
        
}