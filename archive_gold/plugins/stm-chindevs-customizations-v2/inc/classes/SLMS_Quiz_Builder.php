<?php

class SLMS_Quiz_Builder {

    public function __construct(){
        add_filter('masterstudy_lms_quiz_custom_fields', array($this, 'custom_fields'), 15);
        add_action( 'masterstudy_lms_custom_fields_updated', array($this, 'updated_custom_fields'), 15, 2 );
    }

    public static function custom_fields($fields){

        $custom_fields = [];

        $current_url = ( isset( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ? 'https' : 'http' ) . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $current_id = intval(basename($current_url));

        if('stm-quizzes' == get_post_type($current_id)) {
            $quiz_info = STM_LMS_Helpers::parse_meta_field( $current_id );
            if(isset($quiz_info['questions']) && !empty($quiz_info['questions'])) {
                $questions = explode(',', $quiz_info['questions']);
                $questions = array_map('intval', $questions);

                if(count($questions)) {
                    foreach ($questions as $item_id) {
                        $custom_fields[] = array(
                            'type' => 'number',
                            'name' => 'slms_points__'.$item_id,
                            'label' => esc_html__('Question Point:', 'slms').' '.strip_tags(get_the_title($item_id)),
                            'default' => get_post_meta($item_id, 'slms_points', true)
                        );
                    }
                }
            }
        }

        return array_merge( $fields, $custom_fields );
    }

    public function updated_custom_fields($post_id, $data){
        if('stm-quizzes' == get_post_type($post_id)) {
            if(count($data)) {
                foreach ($data as $key => $value) {
                    if(strpos($key, 'slms_points_')) {
                        $item = explode('__', $key);
                        if(!empty($item[1])) {
                            $question_id = intval($item[1]);
                            update_post_meta($question_id, 'slms_points', $value);
                        }
                    }
                }
            }
        }
    }

}

new SLMS_Quiz_Builder();