<?php

class SLMS_Whatsapp {

    public function __construct() {
        add_shortcode('slms_whatsapp', array($this, 'shortcode_html'));
        add_filter('stm_wpcfto_fields', array($this, 'wpcfto_fields'), 15);
        add_filter('wpcfto_options_page_setup', array($this, 'wpcfto_options'), 15);
    }

    public function shortcode_html($args = []) {
        ob_start();
        $post_id = get_the_ID();
        $default_args = array(
            'text' => '',
            'button' => __('Start chat', 'slms'),
            'phone' => '',
        );

        $stream = '';
        $post_terms = wp_get_post_terms($post_id, 'stm_lms_course_taxonomy', ['fields' => 'ids']);
        if(count($post_terms)) {
            foreach ($post_terms as $term_id) {
                $term_meta = get_term_meta($term_id, 'lite_category_name', true);
                if(!empty($term_meta)) {
                    $stream = $term_meta;
                    break;
                }
            }
        }

        $whatsapp_numbers = STM_LMS_Options::get_option( 'whatsapp_numbers', [] );
        if(count($whatsapp_numbers)) {
            foreach ($whatsapp_numbers as $item) {
                if($stream == '') $stream = 'course';

                if($item['stream'] == $stream) {
                    $args['phone'] = $item['number'];
                    break;
                }
            }
        }

        $phone_disable = get_post_meta($post_id, 'whatsapp_number_disable', true);
        $phone = get_post_meta($post_id, 'whatsapp_number', true);

        if(!empty($phone)) {
            $args['phone'] = $phone;
        }

        if(!empty($phone_disable)) {
            $args['phone'] = '';
        }

        $args = wp_parse_args( $args, $default_args );

        if(!empty($args['phone']) && $this->is_valid_phone_number($args['phone'])) {
            $__vars = [
                'post_id' => $post_id,
                'whatsapp_number' => $args['phone'],
                'button' => $args['button'],
                'text' => $args['text']
            ];
            slms_include_template( 'frontend/whatsapp_button', $__vars );
        }
        return ob_get_clean();
    }

    public function wpcfto_fields($fields){

        $old_settings_fields = $fields['stm_courses_settings']['section_settings']['fields'];

        $new_settings_fields = array(
            'whatsapp_number_disable' => array(
                'type'  => 'checkbox',
                'label' => esc_html__( 'Disable Whatsapp Number', 'slms' ),
            ),
            'whatsapp_number' => array(
                'type'  => 'text',
                'label' => esc_html__( 'Whatsapp Number', 'slms' ),
            )
        );

        $fields['stm_courses_settings']['section_settings']['fields'] = array_merge($new_settings_fields, $old_settings_fields);

        return $fields;
    }

    public function wpcfto_options($setups){
        $setups[0]['fields']['whatsapp_settings'] = array(
            'name'   => esc_html__( 'Whatsapp', 'slms' ),
            'label'  => esc_html__( 'Whatsapp Settings', 'slms' ),
            'icon'   => 'fa fa-whatsapp',
            'fields' => array(
                'whatsapp_numbers' => array(
                    'type'        => 'repeater',
                    'label'       => esc_html__( 'Item', 'slms' ),
                    'description' => esc_html__( 'Whatsapp numbers for each course stream', 'slms' ),
                    'fields'      => array(
                        'stream' => array(
                            'type' => 'select',
                            'label' => esc_html__( 'Stream', 'slms' ),
                            'value' => 'course',
                            'options' => array(
                                'course' => esc_html__( 'Course', 'slms' ),
                                'event' => esc_html__( 'Event', 'slms' ),
                                'shravana_mangalam' => esc_html__( 'Shravana Mangalam', 'slms' ),
                                'webinar' => esc_html__( 'Webinar', 'slms' )
                            )
                        ),
                        'number' => array(
                            'type' => 'text',
                            'label' => esc_html__( 'Phone', 'slms' ),
                        )
                    )
                )
            )
        );

        return $setups;
    }

    public function is_valid_phone_number($phone_number) {
        $cleaned_number = preg_replace('/[^0-9]/', '', $phone_number);
        $pattern = '/^\+?[1-9]\d{1,14}$/';
        return preg_match($pattern, $cleaned_number);
    }
}

new SLMS_Whatsapp();