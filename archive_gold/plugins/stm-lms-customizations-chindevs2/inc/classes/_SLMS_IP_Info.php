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

    public static function get_ip_info($ip = NULL, $purpose = "location", $deep_detect = TRUE)
    {
        $output = NULL;
//        if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
//            $ip = self::get_client_ip($deep_detect);
//        }

        if(is_user_logged_in()) {
            $ip_info = get_user_meta(get_current_user_id(), 'slms_ip_info', true);
            if(!empty($ip_info) && (isset($ip_info[$purpose]) && !empty($ip_info[$purpose]))) {
                return $ip_info[$purpose];
            }
        } else {
            if (isset($_COOKIE['slms_ip_info'])) {
                $ip_info = unserialize($_COOKIE['slms_ip_info']);
                if(!empty($ip_info) && (isset($ip_info[$purpose]) && !empty($ip_info[$purpose]))) {
                    return $ip_info[$purpose];
                }
            }
        }

        $purpose    = str_replace(array("name", "\n", "\t", " ", "-", "_"), NULL, strtolower(trim($purpose)));
        $support    = array("country", "countrycode", "state", "region", "city", "location", "address");
        $continents = array(
            "AF" => "Africa",
            "AN" => "Antarctica",
            "AS" => "Asia",
            "EU" => "Europe",
            "OC" => "Australia (Oceania)",
            "NA" => "North America",
            "SA" => "South America"
        );
//        if (filter_var($ip, FILTER_VALIDATE_IP) && in_array($purpose, $support)) {
        if (in_array($purpose, $support)) {
            if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
                $ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp"));
            } else {
                $ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
            }
            if (@strlen(trim($ipdat->geoplugin_countryCode)) == 2) {
                switch ($purpose) {
                    case "location":
                        $output = array(
                            "city"           => @$ipdat->geoplugin_city,
                            "state"          => @$ipdat->geoplugin_regionName,
                            "country"        => @$ipdat->geoplugin_countryName,
                            "country_code"   => @$ipdat->geoplugin_countryCode,
                            "continent"      => @$continents[strtoupper($ipdat->geoplugin_continentCode)],
                            "continent_code" => @$ipdat->geoplugin_continentCode
                        );
                        break;
                    case "address":
                        $address = array($ipdat->geoplugin_countryName);
                        if (@strlen($ipdat->geoplugin_regionName) >= 1)
                            $address[] = $ipdat->geoplugin_regionName;
                        if (@strlen($ipdat->geoplugin_city) >= 1)
                            $address[] = $ipdat->geoplugin_city;
                        $output = implode(", ", array_reverse($address));
                        break;
                    case "city":
                        $output = @$ipdat->geoplugin_city;
                        break;
                    case "state":
                        $output = @$ipdat->geoplugin_regionName;
                        break;
                    case "region":
                        $output = @$ipdat->geoplugin_regionName;
                        break;
                    case "country":
                        $output = @$ipdat->geoplugin_countryName;
                        break;
                    case "countrycode":
                        $output = @$ipdat->geoplugin_countryCode;
                        break;
                }
            }
        }

        $ip_info = array($purpose => $output);

        if(is_user_logged_in()) {
            update_user_meta(get_current_user_id(), 'slms_ip_info', $ip_info);
        } else {
            setcookie('slms_ip_info', serialize(array($purpose => $output)), time() + 3600, '/');
        }

        return $output;
    }

    public static function template_redirect_callback(){
        self::get_ip_info(null, 'countrycode');
    }

}

SLMS_IP_Info::init();