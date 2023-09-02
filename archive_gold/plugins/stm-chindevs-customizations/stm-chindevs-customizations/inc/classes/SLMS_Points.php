<?php

class SLMS_Points
{
    public static function init()
    {
        add_action('add_meta_boxes', array(self::class, 'metaboxes_init'), 15);
        add_action( 'save_post', array(self::class, 'save_post'), 15, 3 );
    }

    public static function metaboxes_init()
    {
        add_meta_box(
            'slms_points_data',
            __('Questions Points', 'slms'),
            array(self::class, 'display_slms_points'),
            'stm-courses',
            'normal',
            '',
            ''
        );
    }

    public static function display_slms_points($post, $meta){
        $questions_points = self::get_questions_points($post->ID);
        slms_include_template('admin/points', ['post_id' => $post->ID, 'questions_points' => $questions_points]);
    }

    public static function get_questions_points(int $post_id)
    {
        $questions_points = [];

        $material_ids     = ( new \MasterStudy\Lms\Repositories\CurriculumMaterialRepository() )->get_course_materials( $post_id );
        if(count($material_ids)) {
            foreach ($material_ids as $id) {
                if('stm-quizzes' == get_post_type($id)) {
                    $quiz_info       = STM_LMS_Helpers::parse_meta_field( $id );
                    if(isset($quiz_info['questions']) && !empty($quiz_info['questions'])) {
                        $questions = explode(',', $quiz_info['questions']);
                        $questions = array_map('intval', $questions);

                        if(count($questions)) {
                            foreach ($questions as $item_id) {
                                $questions_points[$id][$item_id] = get_post_meta($item_id, 'slms_points', true);
                            }
                        }
                    }
                }
            }
        }

        return $questions_points;
    }

    public static function save_post($post_id, $post, $update)
    {
        if ($post->post_type != 'stm-courses' || $post->post_status == 'auto-draft') {
            return;
        }

        if(!isset($_POST['slms_points'])) {
            return;
        }

        if(count($_POST['slms_points'])) {
            foreach ($_POST['slms_points'] as $quiz_id => $items) {
                if(count($items)) {
                    foreach ($items as $question_id => $value) {
                        update_post_meta($question_id, 'slms_points', $value);
                    }
                }
            }
        }

    }

}

SLMS_Points::init();