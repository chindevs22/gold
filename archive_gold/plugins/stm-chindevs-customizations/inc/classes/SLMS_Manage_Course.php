<?php

SLMS_Manage_Course::init();

class SLMS_Manage_Course extends STM_LMS_Manage_Course {

    public static function init(){
        add_action( 'stm_lms_pro_course_added', 'SLMS_Manage_Course::save_course_hook', 15, 3 );

        remove_all_actions( 'wp_ajax_stm_lms_pro_save_front_course' );
        add_action( 'wp_ajax_stm_lms_pro_save_front_course', array(self::class, 'save_course') );
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

    public static function save_course() {

        check_ajax_referer( 'stm_lms_pro_save_front_course', 'nonce' );

        $validation = new Validation();

        $required_fields = apply_filters(
            'stm_lms_manage_course_required_fields',
            array(
                'title'    => 'required',
                'category' => 'required',
                'image'    => 'required|integer',
                'content'  => 'required',
                'price'    => 'float',
            )
        );

        $validation->validation_rules( $required_fields );

        $validation->filter_rules(
            array(
                'title'                      => 'trim|sanitize_string',
                'category'                   => 'trim|sanitize_string',
                'image'                      => 'sanitize_numbers',
                'content'                    => 'trim',
                'price'                      => 'sanitize_floats',
                'sale_price'                 => 'sanitize_floats',
                'curriculum'                 => 'sanitize_string',
                'duration'                   => 'sanitize_string',
                'video'                      => 'sanitize_string',
                'prerequisites'              => 'sanitize_string',
                'prerequisite_passing_level' => 'sanitize_floats',
                'enterprise_price'           => 'sanitize_floats',
                'co_instructor'              => 'sanitize_floats',
            )
        );

        $validated_data = $validation->run( $_POST );

        if ( false === $validated_data ) {
            wp_send_json(
                array(
                    'status'  => 'error',
                    'message' => $validation->get_readable_errors( true ),
                )
            );
        }

        $user = STM_LMS_User::get_current_user();

        do_action( 'stm_lms_pro_course_data_validated', $validated_data, $user );

        $is_updated = ( ! empty( $validated_data['post_id'] ) );

        $course_id = self::create_course( $validated_data, $user, $is_updated );

        self::update_course_meta( $course_id, $validated_data );

        self::update_course_category( $course_id, $validated_data );

        self::update_course_image( $course_id, $validated_data );

        do_action( 'stm_lms_pro_course_added', $validated_data, $course_id, $is_updated );

        $course_url = get_the_permalink( $course_id );

        wp_send_json(
            array(
                'status'  => 'success',
                'message' => esc_html__( 'Course Saved, redirecting...', 'masterstudy-lms-learning-management-system-pro' ),
                'url'     => $course_url,
            )
        );

    }

    public static function create_course( $data, $user, $is_updated ) {

        STM_LMS_Mails::wp_mail_text_html();
        $premoderation = STM_LMS_Options::get_option( 'course_premoderation', false );

        $post_status = ( $premoderation ) ? 'pending' : 'publish';

        if ( ! empty( $data['save_as_draft'] ) && $data['save_as_draft'] ) {
            $post_status = 'draft';
        }

        $post = array(
            'post_type'    => 'stm-courses',
            'post_title'   => $data['title'],
            'post_content' => $data['content'],
            'post_status'  => $post_status,
            'post_author'  => $user['id'],
        );
        if ( ! empty( $data['post_id'] ) ) {
            $post['ID']          = $data['post_id'];
            $post['post_author'] = intval( get_post_field( 'post_author', $data['post_id'] ) );
        }

        kses_remove_filters();
        $r = wp_insert_post( $post );
        kses_init_filters();

        $action  = ( $is_updated ) ? esc_html__( 'updated', 'masterstudy-lms-learning-management-system-pro' ) : esc_html__( 'created', 'masterstudy-lms-learning-management-system-pro' );

        // By ChinDevs: update this to only send course added on create
        if (!$is_updated) {
            $subject = esc_html__( 'Course added', 'masterstudy-lms-learning-management-system-pro' );
            $message = sprintf(
            /* translators: %s: course info */
                esc_html__( 'Course %1$s added by instructor, your (%3$s). Please review this information from the admin Dashboard.', 'masterstudy-lms-learning-management-system-pro' ),
                $data['title'],
                $user['login']
            );
            STM_LMS_Mails::send_email(
                $subject,
                $message,
                get_option( 'admin_email' ),
                array(),
                'stm_lms_course_added',
                array(
                    'course_title' => $data['title'],
                    'user_login'   => $user['login'],
                )
            );
        }


        //By:ChinDevs add send email to instructor as well
        $subject = esc_html__( 'Your Course has been Created', 'masterstudy-lms-learning-management-system-pro' );
        $message = sprintf(
        /* translators: %s: course info */
            esc_html__( 'Course %1$s was added.', 'masterstudy-lms-learning-management-system-pro' ),
            $data['title']
        );
        if (!$is_updated) {
            STM_LMS_Mails::send_email(
                $subject,
                $message,
                $user['email'],
                array(),
                'stm_lms_course_created_for_instructor',
                array('course_title' => $data['title'])
            );
        } else {
            //send email to instructor
            STM_LMS_Mails::send_email(
                $subject,
                $message,
                $user['email'],
                array(),
                'stm_lms_course_updated_for_instructor',
                array('course_title' => $data['title'])
            );

            //send email to all students
            $student_users = stm_lms_get_course_users( $data['post_id'], array( 'user_id' ) );

            foreach ( $student_users as $suser ) {
                $student_user_id = $suser['user_id'];
                if ( $student_user_id == $user['id']) { //skip sending to instructor
                    continue;
                }
                $student_user_info = get_userdata( $student_user_id );
                STM_LMS_Mails::send_email(
                    $subject,
                    $message,
                    $student_user_info->user_email,
                    array(),
                    'stm_lms_course_updated_for_user',
                    array('course_title' => $data['title'])
                );
            }
        }

        //End ChinDevs code
        STM_LMS_Mails::remove_wp_mail_text_html();

        return $r;
    }

}
