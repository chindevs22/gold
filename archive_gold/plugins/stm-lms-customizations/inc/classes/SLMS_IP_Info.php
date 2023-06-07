<?php

class SLMS_IP_Info {

    public static function init(){
        add_action('template_redirect', array(self::class, 'template_redirect_callback'));
    }

    public static function get_client_ip($deep_detect = false) {
        $ip = $_SERVER["REMOTE_ADDR"];
        if ($deep_detect) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            if (isset($_SERVER['HTTP_CLIENT_IP']) && filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
                $ip = $_SERVER['HTTP_CLIENT_IP'];

            if($ip == '::1') {
                $ip = $_SERVER["REMOTE_ADDR"];
            }
        }
        return $ip;
    }

    public static function ip_data_request(){
        $ip = self::get_client_ip(true);

        if( self::get_ip_info_data() ) {
            return;
        }
//        $ip = '94.158.52.223';

        if(!is_user_logged_in()) {
            if (isset($_COOKIE['slms_ip_info'])) {
                $exist_ip_info = unserialize(wp_unslash($_COOKIE['slms_ip_info']));
                if(!empty($exist_ip_info)) {
                    return $exist_ip_info;
                }
            }
        }

        if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
            $ipdat = @json_decode(file_get_contents("http://ip-api.com/json/"), true);
        } else {
            $ipdat = @json_decode(file_get_contents("http://ip-api.com/json/" . $ip), true);
        }

        $ip_info = (isset($ipdat['countryCode']) && !empty($ipdat['countryCode'])) ? array('countryCode' => $ipdat['countryCode']) : array('countryCode' => 'US');

        if(is_user_logged_in()) {
            update_user_meta(get_current_user_id(), 'slms_ip_info', $ip_info);
        } else {
            setcookie('slms_ip_info', serialize($ip_info), time() + 3600, '/');
        }

    }

    public static function get_ip_info_data($purpose = 'countryCode')
    {
        if(is_user_logged_in()) {
            $ip_info = get_user_meta(get_current_user_id(), 'slms_ip_info', true);
            if(!empty($ip_info) && (isset($ip_info[$purpose]) && !empty($ip_info[$purpose]))) {
                return $ip_info[$purpose];
            }
        } else {
            if (isset($_COOKIE['slms_ip_info'])) {
                $ip_info = unserialize(wp_unslash($_COOKIE['slms_ip_info']));
                if(!empty($ip_info) && (isset($ip_info[$purpose]) && !empty($ip_info[$purpose]))) {
                    return $ip_info[$purpose];
                }
            }
        }

        return false;
    }

    public static function get_ip_info()
    {
        $purpose = 'countryCode';

        if($data = self::get_ip_info_data($purpose)) {
            return $data;
        }

        return 'US';
    }

    public static function template_redirect_callback(){
        self::ip_data_request();
    }

}

SLMS_IP_Info::init();