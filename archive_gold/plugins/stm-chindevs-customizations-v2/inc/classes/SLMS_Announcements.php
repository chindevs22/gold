<?php

class SLMS_Announcements extends STM_LMS_Pro_Announcements {
    public function __construct() {
        remove_all_actions( 'wp_ajax_stm_lms_create_announcement' );
        add_action( 'wp_ajax_stm_lms_create_announcement', array( $this, 'create_announcement' ) );
    }

    public function create_announcement() {
        check_ajax_referer( 'stm_lms_create_announcement', 'nonce' );

        $current_user = STM_LMS_User::get_current_user();
        $user_id      = $current_user['id'];

        $response = array(
            'status'  => 'success',
            'message' => esc_html__( 'Announcement has been sent to course students.', 'masterstudy-lms-learning-management-system-pro' ),
        );

        if ( empty( $_GET['post_id'] ) || empty( $_GET['mail'] ) ) {
            $response['status']  = 'error';
            $response['message'] = esc_html__( 'Please fill all fields', 'masterstudy-lms-learning-management-system-pro' );

            wp_send_json( $response );
        }

        $post_id = intval( $_GET['post_id'] );
        $mail    = sanitize_text_field( $_GET['mail'] );

        /*get post author*/
        $post_author_id = get_post_field( 'post_author', $post_id );

        if ( intval( $post_author_id ) === intval( $user_id ) ) {
            do_action( 'stm_lms_announcement_ready_to_send', $post_id, $user_id, $mail );

            $users = stm_lms_get_course_users( $post_id, array( 'user_id' ) );

            foreach ( $users as $user ) {
                $user_id = $user['user_id'];
                // ChinDevs code to send different email to instructor vs students
                if ( $user_id == $post_author_id) {
                    $email_key = 'stm_lms_announcement_from_instructor';
                } else {
                    $email_key = 'stm_lms_announcement_from_instructor_to_user';
                }
                $user_info = get_userdata( $user_id );
                STM_LMS_Helpers::send_email(
                    $user_info->user_email,
                    esc_html__( 'Announcement from the Instructor', 'masterstudy-lms-learning-management-system-pro' ),
                    $mail,
                    $email_key,
                    compact( 'mail' )
                );
            }
            wp_send_json( $response );
        }
    }
}

new SLMS_Announcements();