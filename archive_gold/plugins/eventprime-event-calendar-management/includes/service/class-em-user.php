<?php

if (!defined('ABSPATH')) {
    exit;
}

class EventM_User_Service {

    private $dao;
    private static $instance = null;
    
    private function __construct() {}
    
    public static function get_instance()
    {   
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function register_user() {
        $user_data = new stdClass();
        $response = new stdClass();
        $user_data->email = sanitize_email(event_m_get_param("email", true));
        $user_data->password = sanitize_text_field(event_m_get_param("password", true));
        $recaptcha_enabled = sanitize_text_field(event_m_get_param("recaptcha_enabled", true));
        if( !empty($recaptcha_enabled) && $recaptcha_enabled == 1 ){
            $user_data->captchaResponse = sanitize_text_field(event_m_get_param("captchaResponse", true));
            if (empty($user_data->captchaResponse)) {
                wp_send_json_error(array('captcha_msg' => __('Please check google recaptcha box.', 'eventprime-event-calendar-management')));
            }
            $result = $this->verify_captcha($user_data->captchaResponse);
            if (empty($result['success'])) {
                wp_send_json_error(array('captcha_msg' => __('Invalid google recaptcha response.', 'eventprime-event-calendar-management')));
            }
        }
        if (!$this->verify_user($user_data->email)) {
            $user_id = wp_create_user($user_data->email, $user_data->password, $user_data->email);
            if (!is_wp_error($user_id)) {
                $this->add_user_meta($user_id);
                do_action('event_magic_after_user_registration', $user_id);
                $user_data->user_id = $user_id;
                EventM_Notification_Service::user_registration($user_data);
                    /* login user after registration */
                    $user = get_user_by('email', $user_data->email);
                    $user_id = $user->ID;
                    $info['user_login'] = $user->user_login;
                    $info['user_password'] = $user_data->password;
                    $info['remember'] = true;
                    $user_signon = wp_signon($info, false);
                    wp_set_current_user($user_data->user_id);
                    $response->disable_login = 1;
                    $response->success = true;
                    $url_event_id = event_m_get_param("event_id", true);
                    if(!empty($url_event_id)){
                        $events_page = em_global_settings('events_page');
                        $link_href = add_query_arg("event", $url_event_id, get_permalink($events_page));
                        if(!empty($link_href)){
                            // check for event custom link
                            $service = EventM_Factory::get_service('EventM_Service');
                            $event = $service->load_model_from_db($url_event_id);
                            if(absint($event->custom_link_enabled) == 1){
                                $response->redirect = $event->custom_link;
                            }
                            else{
                                $response->redirect = $link_href;
                            }
                        }
                    }
                    $page_id = em_global_settings('redirect_after_registration');
                    if( ! empty( $page_id ) ){
                        $response->redirect = get_page_link( $page_id );
                    }
                    wp_send_json_success($response);
            } else {
                wp_send_json_error(array('msg' => $user_id->get_error_message()));
            }
        }
        wp_send_json_error(array('msg' => __('User already exists.', 'eventprime-event-calendar-management')));   
        
    }

    public function verify_user($email) {
        // Assuming both email and username are same
        return (username_exists($email) || email_exists($email));
    }

    private function add_user_meta($user_id) {
        $user = get_user_by('ID', $user_id);
        if ($user) {
            update_user_meta($user_id, 'first_name', event_m_get_param('first_name', true));
            update_user_meta($user_id, 'last_name', event_m_get_param('last_name', true));
            update_user_meta($user_id, 'phone', event_m_get_param('phone', true));
            do_action('event_magic_add_user_custom_meta', $user_id);
        }
    }

    public function login_user() {
        $user_name = event_m_get_param("user_name", true);
        $password = event_m_get_param("password", true);
        $response = new stdClass();
        $user_id = 0;
        $is_disabled = 1;
        // Check if user exists
        if (username_exists($user_name)) {
            $user = get_user_by('login', $user_name);
            $user_id = $user->ID;
        } elseif (email_exists($user_name)) {
            $user = get_user_by('email', $user_name);
            $user_id = $user->ID;
        } elseif ($user_name == "") {
            $error = new WP_Error('user_not_exists', __('Username cannot be blank.', 'eventprime-event-calendar-management'));
            echo json_encode($error);
            die;
        } elseif ($password == "") {
            $error = new WP_Error('user_not_exists', __('Password cannot be blank.', 'eventprime-event-calendar-management'));
            echo json_encode($error);
            die;
        } else {
            $error = new WP_Error('user_not_exists', __('User does not exists. Please check your details.', 'eventprime-event-calendar-management'));
            echo json_encode($error);
            die;
        }
        $is_disabled = (int) get_user_meta($user_id, 'rm_user_status', true);
        if ($is_disabled) {
            $error = new WP_Error('user_not_exists', __('User is not active yet.', 'eventprime-event-calendar-management'));
            echo json_encode($error);
            die;
        }
        $info['user_login'] = $user->user_login;
        $info['user_password'] = event_m_get_param("password");
        $info['remember'] = true;
        $user_signon = wp_signon($info, false);
        if (is_wp_error($user_signon)) {
            $error = new WP_Error('invalid_user', __('Wrong Username/Email or Password.', 'eventprime-event-calendar-management'));
            echo json_encode($error);
            die;
        } else {
            wp_set_current_user($user_signon->ID);
            $response->success = true;
            $response->redirect = '';
            $url_event_id = event_m_get_param("event_id", true);
            if(!empty($url_event_id)){
                $events_page = em_global_settings('events_page');
                $link_href = add_query_arg("event", $url_event_id, get_permalink($events_page));
                if(!empty($link_href)){
                    // check for event custom link
                    $service = EventM_Factory::get_service('EventM_Service');
                    $event = $service->load_model_from_db($url_event_id);
                    if(absint($event->custom_link_enabled) == 1){
                        $response->redirect = $event->custom_link;
                    }
                    else{
                        $response->redirect = $link_href;
                    }
                }
            }
            echo json_encode($response);
            die;
        }
        die();
    }
    
    public function verify_captcha($userResponse) {
        $settings_service= EventM_Factory::get_service('EventM_Setting_Service');
        $gs = $settings_service->load_model_from_db();
        $fields_string = '';
        $fields = array();
        $fields['secret'] = $gs->google_recaptcha_secret_key;
        $fields['response'] = $userResponse;
          
        foreach($fields as $key=>$value)
        $fields_string .= $key . '=' . $value . '&';
        $fields_string = rtrim($fields_string, '&');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, True);

        $res = curl_exec($ch);
        curl_close($ch);

        return json_decode($res, true);
    }

    /**
     * Load page to list user caps
     */
    public function load_user_cap_edit_page() {
        global $wp_roles;
        $response = new stdClass();
        $cap_docs = $this->default_cap_doc();
        $response->cap_docs = $cap_docs;
        $response->user_cap_data = array();
        $ucpd = $em_user_capabilities = array();
        foreach($wp_roles->role_objects as $role){
            foreach ($cap_docs as $gkey => $gvalue) {
                foreach ($gvalue as $cap_key => $cap_help) {
                    $has_cap = $role->has_cap($cap_key);
                    $ucpd[$role->name][$gkey][$cap_key] = array('has_cap' => $has_cap, "cap_help" => $cap_help);
                    $em_user_capabilities[$role->name][$cap_key] = $has_cap;
                }
            }
        }
        $response->user_cap_data = $ucpd;
        $response->em_user_capabilities = $em_user_capabilities;
        return $response;
    }

    private function default_cap_doc() {
        $cap_docs = array(
            'event' => array(
                'create_events' => sprintf(__('Users can create %s', 'eventprime-event-calendar-management'),__('events', 'eventprime-event-calendar-management')),
                'view_events' => sprintf(__('Users can view %s', 'eventprime-event-calendar-management'),__('events', 'eventprime-event-calendar-management')),
                'view_others_events' => sprintf(__('Users can view other users %s', 'eventprime-event-calendar-management'),__('events', 'eventprime-event-calendar-management')),
                'edit_events' => sprintf(__('User can edit %s', 'eventprime-event-calendar-management'),__('events', 'eventprime-event-calendar-management')),
                'edit_others_events' => sprintf(__('User can edit other users %s', 'eventprime-event-calendar-management'),__('events', 'eventprime-event-calendar-management')),
                'delete_events' => sprintf(__('User can delete their own %s', 'eventprime-event-calendar-management'),__('events', 'eventprime-event-calendar-management')),
                'delete_others_events' => sprintf(__('User can delete other users %s', 'eventprime-event-calendar-management'),__('events', 'eventprime-event-calendar-management')),
                'read_private_events' => sprintf(__('User can view private %s', 'eventprime-event-calendar-management'),__('events', 'eventprime-event-calendar-management')),
            ),
            'event_types' => array(
                'create_event_types' => sprintf(__('Users can create %s', 'eventprime-event-calendar-management'),__('event_types', 'eventprime-event-calendar-management')),
                'view_event_types' => sprintf(__('Users can view %s', 'eventprime-event-calendar-management'),__('event_types', 'eventprime-event-calendar-management')),
                'view_others_event_types' => sprintf(__('Users can view other users %s', 'eventprime-event-calendar-management'),__('event_types', 'eventprime-event-calendar-management')),
                'edit_event_types' => sprintf(__('User can edit %s', 'eventprime-event-calendar-management'),__('event_types', 'eventprime-event-calendar-management')),
                'edit_others_event_types' => sprintf(__('User can edit other users %s', 'eventprime-event-calendar-management'),__('event_types', 'eventprime-event-calendar-management')),
                'delete_event_types' => sprintf(__('User can delete their own %s', 'eventprime-event-calendar-management'),__('event_types', 'eventprime-event-calendar-management')),
                'delete_others_event_types' => sprintf(__('User can delete other users %s', 'eventprime-event-calendar-management'),__('event_types', 'eventprime-event-calendar-management')),
                'read_private_event_types' => sprintf(__('User can view private %s', 'eventprime-event-calendar-management'),__('event_types', 'eventprime-event-calendar-management')),
            ),
            'event_sites' => array(
                'create_event_sites' => sprintf(__('Users can create %s', 'eventprime-event-calendar-management'),__('event_sites', 'eventprime-event-calendar-management')),
                'view_event_sites' => sprintf(__('Users can view %s', 'eventprime-event-calendar-management'),__('event_sites', 'eventprime-event-calendar-management')),
                'view_others_event_sites' => sprintf(__('Users can view other users %s', 'eventprime-event-calendar-management'),__('event_sites', 'eventprime-event-calendar-management')),
                'edit_event_sites' => sprintf(__('User can edit %s', 'eventprime-event-calendar-management'),__('event_sites', 'eventprime-event-calendar-management')),
                'edit_others_event_sites' => sprintf(__('User can edit other users %s', 'eventprime-event-calendar-management'),__('event_sites', 'eventprime-event-calendar-management')),
                'delete_event_sites' => sprintf(__('User can delete their own %s', 'eventprime-event-calendar-management'),__('event_sites', 'eventprime-event-calendar-management')),
                'delete_others_event_sites' => sprintf(__('User can delete other users %s', 'eventprime-event-calendar-management'),__('event_sites', 'eventprime-event-calendar-management')),
                'read_private_event_sites' => sprintf(__('User can view private %s', 'eventprime-event-calendar-management'),__('event_sites', 'eventprime-event-calendar-management')),
            ),
            'event_performers' => array(
                'create_event_performers' => sprintf(__('Users can create %s', 'eventprime-event-calendar-management'),__('event_performers', 'eventprime-event-calendar-management')),
                'view_event_performers' => sprintf(__('Users can view %s', 'eventprime-event-calendar-management'),__('event_performers', 'eventprime-event-calendar-management')),
                'view_others_event_performers' => sprintf(__('Users can view other users %s', 'eventprime-event-calendar-management'),__('event_performers', 'eventprime-event-calendar-management')),
                'edit_event_performers' => sprintf(__('User can edit %s', 'eventprime-event-calendar-management'),__('event_performers', 'eventprime-event-calendar-management')),
                'edit_others_event_performers' => sprintf(__('User can edit other users %s', 'eventprime-event-calendar-management'),__('event_performers', 'eventprime-event-calendar-management')),
                'delete_event_performers' => sprintf(__('User can delete their own %s', 'eventprime-event-calendar-management'),__('event_performers', 'eventprime-event-calendar-management')),
                'delete_others_event_performers' => sprintf(__('User can delete other users %s', 'eventprime-event-calendar-management'),__('event_performers', 'eventprime-event-calendar-management')),
                'read_private_event_performers' => sprintf(__('User can view private %s', 'eventprime-event-calendar-management'),__('event_performers', 'eventprime-event-calendar-management')),
            ),
            'event_organizers' => array(
                'create_event_organizers' => sprintf(__('Users can create %s', 'eventprime-event-calendar-management'),__('event_organizers', 'eventprime-event-calendar-management')),
                'view_event_organizers' => sprintf(__('Users can view %s', 'eventprime-event-calendar-management'),__('event_organizers', 'eventprime-event-calendar-management')),
                'view_others_event_organizers' => sprintf(__('Users can view other users %s', 'eventprime-event-calendar-management'),__('event_organizers', 'eventprime-event-calendar-management')),
                'edit_event_organizers' => sprintf(__('User can edit %s', 'eventprime-event-calendar-management'),__('event_organizers', 'eventprime-event-calendar-management')),
                'edit_others_event_organizers' => sprintf(__('User can edit other users %s', 'eventprime-event-calendar-management'),__('event_organizers', 'eventprime-event-calendar-management')),
                'delete_event_organizers' => sprintf(__('User can delete their own %s', 'eventprime-event-calendar-management'),__('event_organizers', 'eventprime-event-calendar-management')),
                'delete_others_event_organizers' => sprintf(__('User can delete other users %s', 'eventprime-event-calendar-management'),__('event_organizers', 'eventprime-event-calendar-management')),
                'read_private_event_organizers' => sprintf(__('User can view private %s', 'eventprime-event-calendar-management'),__('event_organizers', 'eventprime-event-calendar-management')),
            ),
        );

        return $cap_docs;
    }

    public function update_user_custom_caps( $em_user_capabilities ) {
        global $wp_roles;
        $userRoles = $wp_roles->role_objects;
        foreach ( $em_user_capabilities as $role => $cap_data ) {
            if( !empty( $cap_data ) ) {
                foreach ( $cap_data as $cap_key => $cap_status ) {
                    $has_cap = $userRoles[$role]->has_cap( $cap_key );
                    if( $has_cap != $cap_status) {
                        if( !empty($cap_status ) ) {
                            $wp_roles->add_cap( $role, $cap_key );
                        } else{
                            $get_role = get_role( $role );
                            $get_role->remove_cap( $cap_key );
                        }
                    }
                }
            }
        }
    }
}