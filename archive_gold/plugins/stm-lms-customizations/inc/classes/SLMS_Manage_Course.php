<?php

SLMS_Manage_Course::init();

class SLMS_Manage_Course extends STM_LMS_Manage_Course {

    public static function init(){
        add_action( 'stm_lms_pro_course_added', 'SLMS_Manage_Course::save_course_hook', 15, 3 );
    }

    public static function localize_script( $course_id ) {
        $localize                          = array();
        $localize['i18n']                  = self::i18n();
        $localize['post_id']               = $course_id;
        $localize['course_file_pack_data'] = stm_lms_course_files_data();
        $localize['lesson_file_pack_data'] = stm_lms_lesson_files_data();
        $localize['course_prices_pack_data'] = SLMS_Course_Price::course_prices_pack();
        if ( ! empty( $course_id ) ) {
            $localize['post_data'] = array(
                'title'   => get_the_title( $course_id ),
                'post_id' => $course_id,
                'content' => get_post_field( 'post_content', $course_id ),
                'image'   => get_post_thumbnail_id( $course_id ),
            );

            $meta = STM_LMS_Helpers::simplify_meta_array( get_post_meta( $course_id ) );
            if ( ! empty( $meta ) ) {
                $localize['post_data'] = array_merge( $localize['post_data'], $meta );
            }

            /*Category*/
            $terms = wp_get_post_terms( $course_id, 'stm_lms_course_taxonomy' );

            if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
                $terms                             = wp_list_pluck( $terms, 'term_id' );
                $localize['post_data']['category'] = $terms[0];
            }

            if ( ! empty( $meta['co_instructor'] ) && class_exists( 'STM_LMS_Multi_Instructors' ) ) {
                $localize['post_data']['co_instructor'] = get_user_by( 'ID', $meta['co_instructor'] );

                if ( ! empty( $localize['post_data']['co_instructor'] ) ) {
                    $localize['post_data']['co_instructor']->data->lms_data = STM_LMS_User::get_current_user( $meta['co_instructor'] );
                }
            }

            if ( ! empty( $meta['course_files_pack'] ) ) {
                $localize['post_data']['course_files_pack'] = json_decode( $meta['course_files_pack'] );
            }

            if ( ! empty( $meta['prices_list'] ) ) {
                $localize['post_data']['course_prices_pack'] = json_decode( $meta['prices_list'] );
            }
        }

        apply_filters( 'stm_lms_localize_manage_course', $localize, $course_id );

        $r = '';

        if ( ! empty( $course_id ) ) {
            $r = 'var stm_lms_manage_course_id = ' . $course_id . '; ';
        }

        $r .= 'var stm_lms_manage_course = ' . wp_json_encode( $localize );

        return $r;

    }

    public static function save_course_hook($validated_data, $course_id, $is_updated){
        if(isset($validated_data['course_prices_pack'])) {
            update_post_meta( $course_id, 'prices_list', $validated_data['course_prices_pack'] );
        }
    }

}
