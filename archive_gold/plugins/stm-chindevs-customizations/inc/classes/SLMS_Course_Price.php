<?php

class SLMS_Course_Price {

    public static function init(){
        add_filter('stm_wpcfto_fields', array(self::class, 'wpcfto_fields'), 15);
        add_action('admin_head', array(self::class, 'add_css'));
    }

    public static function course_saarc_price(){
        $symbol = SLMS_SAARC::get_currency_symbol();
        return array(
            'price_saarc' => array(
                'type'  => 'number',
                'group' => 'started',
                'group_title' => esc_html__( 'Price for SAARC Nations', 'slms' ),
                'label' => esc_html__( 'Price', 'slms' ) . " ($symbol)"
            ),
            'sale_price_saarc' => array(
                'type'  => 'number',
                'label' => esc_html__( 'Sale Price', 'slms' ) . " ($symbol)"
            ),
            'enterprise_price_saarc' => array(
                'type'  => 'number',
                'group' => 'ended',
                'label' => esc_html__( 'Enterprise Price', 'slms' ) . " ($symbol)"
            ),
        );
    }

    public static function wpcfto_fields($fields){

        $old_settings_fields = $fields['stm_courses_settings']['section_accessibility']['fields'];

        $saarc_settings_fields = self::course_saarc_price();

        $fields['stm_courses_settings']['section_accessibility']['fields'] = array_merge($saarc_settings_fields, $old_settings_fields);

        return $fields;
    }

    public static function get($post_id = 0){
        $post_id = (empty($post_id)) ? get_the_ID() : $post_id;
        $countrycode = SLMS_IP_Info::get_ip_info();

        if(SLMS_SAARC::is_saarc($countrycode)) {
            $price_saarc = SLMS_SAARC::get_price($post_id);
            if(!empty($price_saarc)) return $price_saarc;
        }

        return STM_LMS_Course::get_course_price( $post_id );
    }

    public static function get_sale($post_id = 0){
        $post_id = (empty($post_id)) ? get_the_ID() : $post_id;
        $countrycode = SLMS_IP_Info::get_ip_info();

        if(SLMS_SAARC::is_saarc($countrycode)) {
            $price_saarc = SLMS_SAARC::get_sale($post_id);
            if(!empty($price_saarc)) return $price_saarc;
        }

        return '';
    }

    public static function get_enterprise($post_id = 0){
        $post_id = (empty($post_id)) ? get_the_ID() : $post_id;
        $countrycode = SLMS_IP_Info::get_ip_info();

        if($prices_list = self::get_prices_list($post_id)) {
            foreach ($prices_list as $item) {
                if($item['country'] == $countrycode) {
                    if(isset($item['enterprise_price'])) {
                        return $item['enterprise_price'];
                    }
                }
            }
        }

        if(SLMS_SAARC::is_saarc($countrycode)) {
            $price_saarc = SLMS_SAARC::get_enterprise($post_id);
            if(!empty($price_saarc)) return $price_saarc;
        }

        return STM_LMS_Enterprise_Courses::get_enterprise_price( $post_id );
    }

    public static function get_currency_symbol($post_id = 0){
        $post_id = (empty($post_id)) ? get_the_ID() : $post_id;
        $countrycode = SLMS_IP_Info::get_ip_info();

        if(SLMS_SAARC::is_saarc($countrycode)) {
            $price_saarc = SLMS_SAARC::get_price($post_id);
            if(!empty($price_saarc)) return SLMS_SAARC::get_currency_symbol();
        }

        return STM_LMS_Options::get_option( 'currency_symbol', '$' );
    }

    public static function get_currency($post_id = 0){
        $post_id = (empty($post_id)) ? get_the_ID() : $post_id;
        $countrycode = SLMS_IP_Info::get_ip_info();

        if(SLMS_SAARC::is_saarc($countrycode)) {
            $price_saarc = SLMS_SAARC::get_price($post_id);
            if(!empty($price_saarc)) return SLMS_SAARC::get_currency();
        }

        return 'USD';
    }

    public static function display_price( $price, $post_id = 0 ) {
        if ( ! isset( $price ) ) {
            return '';
        }
//        $symbol             = STM_LMS_Options::get_option( 'currency_symbol', '$' );
        $symbol             = self::get_currency_symbol($post_id);
        $position           = STM_LMS_Options::get_option( 'currency_position', 'left' );
        $currency_thousands = STM_LMS_Options::get_option( 'currency_thousands', ',' );
        $currency_decimals  = STM_LMS_Options::get_option( 'currency_decimals', '.' );
        $decimals_num       = STM_LMS_Options::get_option( 'decimals_num', 2 );

        $post_id = (empty($post_id)) ? get_the_ID() : $post_id;

        $price = floatval( $price );

        if ( strpos( $price, '.' ) ) {
            $price = number_format( $price, $decimals_num, $currency_decimals, $currency_thousands );
        } else {
            $price = number_format( $price, 0, '', $currency_thousands );
        }

        if ( 'left' == $position ) {
            return $symbol . $price;
        } else {
            return $price . $symbol;
        }
    }

    public static function add_css(){
    ?>
        <style>
            .wpcfto-box-repeater.prices_list .wpcfto-repeater-single {
                margin: 0 0 1.5rem;
            }
            .wpcfto-box-repeater.prices_list .wpcfto-repeater-single .repeater_inner {
                display: flex;
                column-gap: 20px;
                flex-direction: row;
                flex-wrap: wrap;
                /*justify-content: space-between;*/
            }
            .wpcfto-box-repeater.prices_list .wpcfto-repeater-single .repeater_inner .wpcfto-repeater-field {
                margin: 0 0 15px;
                width: calc(50% - 10px);
            }
            .wpcfto-box-repeater.prices_list .wpcfto-repeater-single .repeater_inner .wpcfto-field-aside {
                padding-right: 1rem;
            }
        </style>
    <?php
    }

}

SLMS_Course_Price::init();