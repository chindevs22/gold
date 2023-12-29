<?php

class SLMS_SAARC {

    public static function get_list(): array
    {
        return array(
            "AF" => "Afghanistan",
            "BD" => "Bangladesh",
            "BT" => "Bhutan",
            "IN" => "India",
            "MV" => "Maldives",
            "NP" => "Nepal",
            "PK" => "Pakistan",
            "LK" => "Sri Lanka",
        );
    }

    public static function get_currency(): string
    {
        return 'INR';
    }

    public static function get_currency_symbol(): string
    {
        return '₹';
    }

    public static function get_price($post_id = 0): string
    {
        return get_post_meta($post_id, 'price_saarc', true);
    }

    public static function get_sale($post_id = 0): string
    {
        return get_post_meta($post_id, 'sale_price_saarc', true);
    }

    public static function get_enterprise($post_id = 0): string
    {
        return get_post_meta($post_id, 'enterprise_price_saarc', true);
    }

    public static function is_saarc($countrycode = ''): bool
    {
        $list = self::get_list();
        return (isset($list[$countrycode]));
    }

}