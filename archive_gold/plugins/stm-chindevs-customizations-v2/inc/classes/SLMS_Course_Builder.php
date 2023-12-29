<?php

class SLMS_Course_Builder {

    public function __construct(){
        add_filter('masterstudy_lms_course_custom_fields', array($this, 'custom_fields'), 15);
        add_action( 'masterstudy_lms_custom_fields_updated', array($this, 'updated_custom_fields'), 15, 2 );
    }

    public static function custom_fields($fields){
//        $style_input = "border: 1px solid #dbe0e9;padding: 10px 20px;    min-height: 40px;";
//        $html_prices_list = '';
//
//        $currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
//
//        $pattern = '#/(\d+)/#';
//
//        if (preg_match($pattern, $currentUrl, $matches)) {
//            $course_id = $matches[1];
//            if(!empty($course_id)) {
//                $course_id = intval($course_id);
//                $prices_list_meta = get_post_meta($course_id, 'prices_list', true);
//                $prices_list_meta = (!empty($prices_list_meta)) ? json_decode($prices_list_meta, true) : [];
//                $prices_list_meta = array_filter($prices_list_meta);
//                if(count($prices_list_meta)) {
//                    foreach ($prices_list_meta as $item) {
//                        $html_prices_list .= '<div class="chakra-form-control">';
//                        $html_prices_list .= '<label>'.$item['country'].'<label>';
//                        $html_prices_list .= '<input type="text" name="prices_list[]" style="'.$style_input.'" value="'.$item['price'].'">';
//                        $html_prices_list .= '</div>';
//                    }
//                }
//            }
//        }

        $custom_fields = array(
//            array(
//                'type' => 'number',
//                'name' => 'prices_list_input',
//                'label' => __( 'Prices List', 'slms' ),
//                'custom_html' => $html_prices_list
//            ),
            array(
                'type' => 'number',
                'name' => 'price_saarc',
                'label' => __( 'Price for SAARC (₹)', 'slms' ),
            ),
            array(
                'type' => 'number',
                'name' => 'sale_price_saarc',
                'label' => __( 'Sale Price for SAARC (₹)', 'slms' ),
            ),
            array(
                'type' => 'number',
                'name' => 'enterprise_price_saarc',
                'label' => __( 'Enterprise Price for SAARC (₹)', 'slms' ),
                'custom_html' => '<hr>'
            ),
            array(
                'type'  => 'checkbox',
                'name' => 'whatsapp_number_disable',
                'label' => esc_html__( 'Disable Whatsapp Number', 'slms' ),
            ),
            array(
                'type'  => 'text',
                'name' => 'whatsapp_number',
                'label' => esc_html__( 'Whatsapp Number', 'slms' ),
                'custom_html' => '<hr>'
            ),
            array(
                'type' => 'text',
                'name' => 'start_event_date',
                'label' => esc_html__( 'Event Start Date', 'slms' ),
            ),
            array(
                'type' => 'text',
                'name' => 'end_event_date',
                'label' => esc_html__( 'Event End Date', 'slms' ),
            ),
            array(
                'type' => 'text',
                'name' => 'start_event_time',
                'label' => esc_html__( 'Event Start Time', 'slms' ),
            ),
            array(
                'type' => 'text',
                'name' => 'end_event_time',
                'label' => esc_html__( 'Event End Time', 'slms' ),
            ),
            array(
                'type' => 'text',
                'name' => 'event_repetition_days',
                'label' => esc_html__( 'Repetition Days', 'slms' ),
            ),
            array(
                'type' => 'date',
                'name' => 'registration_close_date',
                'label' => esc_html__( 'Registration Close Date', 'slms' ),
                'custom_html' => '<hr>'
            ),

            array(
                'type' => 'number',
                'name' => 'price_nonac',
                'label' => __( 'Price Non AC (INR)', 'slms' ),
            ),
            array(
                'type' => 'number',
                'name' => 'price_ac',
                'label' => __( 'Price AC (INR)', 'slms' ),
            ),
            array(
                'type' => 'number',
                'name' => 'price_online',
                'label' => __( 'Price Online (INR)', 'slms' ),
            ),
            array(
                'type' => 'number',
                'name' => 'price_residential',
                'label' => __( 'Price Residential (INR)', 'slms' ),
                'custom_html' => '<hr>'
            ),

            array(
                'type' => 'number',
                'name' => 'price_nonac_usd',
                'label' => __( 'Price Non AC (USD)', 'slms' ),
            ),
            array(
                'type' => 'number',
                'name' => 'price_ac_usd',
                'label' => __( 'Price AC (USD)', 'slms' ),
            ),
            array(
                'type' => 'number',
                'name' => 'price_online_usd',
                'label' => __( 'Price Online (USD)', 'slms' ),
            ),
            array(
                'type' => 'number',
                'name' => 'price_residential_usd',
                'label' => __( 'Price Residential (USD)', 'slms' ),
            ),
        );
        return array_merge( $fields, $custom_fields );
    }

    public function updated_custom_fields($post_id, $data){

    }

}

new SLMS_Course_Builder();