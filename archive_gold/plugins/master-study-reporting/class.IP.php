<?php
/**
 * IP Class for getting the user's IP address and location.
 */

defined('ABSPATH') || exit; // Exit if accessed directly.

class user_ip
{
    public function getIP(): string
    {
        return $this->getIPAddress();
    }

    private function getIPAddress(): string
    {
        $ipHeaders = array(
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'HTTP_CLIENT_IP',
        );
    
        foreach ($ipHeaders as $header) {
            if (isset($_SERVER[$header]) && filter_var($_SERVER[$header], FILTER_VALIDATE_IP)) {
                return $_SERVER[$header];
            }
        }
    
        return $_SERVER['REMOTE_ADDR'];
    }

    

    public function getCurrency(): string
    {
        return $this->getIPField('currency');
    }

   
    private function getIPField(string $field): string
    {
        //$ip = '103.255.107.93';//INDIA//
        $ip = '79.66.232.180';//Outside India//
       // $ip = $this->getIPAddress();
        
        $url = 'http://ip-api.com/json/' . $ip . '?fields=status,continent,country,countryCode,region,regionName,city,lat,lon,timezone,currency,isp,mobile,proxy,hosting,query';
        $response = wp_remote_get($url);

        if (is_array($response)) {
            $response_body = json_decode($response['body'], true);
            if (isset($response_body[$field])) {
                return $response_body[$field];
            }
        }

        return '';
    }

}
