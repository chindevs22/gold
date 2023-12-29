<?php

class SLMS_Assignments {

    public function __construct()
    {
        add_action('stm_lms_assignment_before_drafting', array($this, 'assignment_grade'));

        remove_all_actions( 'wp_ajax_stm_lms_accept_draft_assignment' );
        add_action( 'wp_ajax_stm_lms_accept_draft_assignment', array( $this, 'stm_lms_accept_draft_assignment' ) );
    }

    /*ACTIONS*/
    public function assignment_grade($assignment_id) {
        //ChinDevs code for creating assignment for user when they start with a default grade of 0
        $assignment_grade = 0;

        //ChinDevs code for assignment grade
        update_post_meta( $assignment_id, 'assignment_grade', $assignment_grade);
    }

    public function stm_lms_accept_draft_assignment() {
        check_ajax_referer( 'stm_lms_accept_draft_assignment', 'nonce' );

        if ( empty( $_POST['draft_id'] ) || empty( $_POST['course_id'] ) ) {
            $return = array(
                'message' => 'Failed',
            );
            wp_send_json( $return );
        }
        $content = ( ! empty( $_POST['content'] ) ) ? wp_kses_post( $_POST['content'] ) : '';

        wp_send_json( self::stm_lms_accept_draft_assignment_static( $_POST['draft_id'], $_POST['course_id'], $content ) );
    }

    public static function stm_lms_accept_draft_assignment_static( $draft_id = '', $course_id = '', $content = '' ) {
        $course_id = intval( $course_id );
        $draft_id  = intval( $draft_id );

        $user = STM_LMS_User::get_current_user();
        if ( empty( $user['id'] ) ) {
            return 'Failed';
        }
        $user_id = $user['id'];

        $assignment_student_id = intval( get_post_meta( $draft_id, 'student_id', true ) );
        $post_author_id        = get_post_field( 'post_author', get_post_meta( $draft_id, 'assignment_id', true ) );
        $instructor            = STM_LMS_User::get_current_user( $post_author_id );

        if ( $user_id === $assignment_student_id ) {

            wp_update_post(
                array(
                    'ID'           => $draft_id,
                    'post_type'    => 'stm-user-assignment',
                    'post_status'  => 'pending',
                    'post_title'   => get_the_title( $draft_id ),
                    'post_content' => $content,
                )
            );

            update_post_meta( $draft_id, 'end_time', time() * 1000 );
            update_post_meta( $draft_id, 'course_id', $course_id );

            $user_login       = $user['login'];
            $course_title     = get_the_title( $course_id );
            $assignment_title = get_the_title( $draft_id );
            $assignment_meta  = get_post_meta( $draft_id );
            if ( ! empty( $assignment_meta ) && $assignment_meta['assignment_id'] ) {
                $assignment_title = get_the_title( $assignment_meta['assignment_id'][0] );
            }
            $message = sprintf(
            /* translators: %1$s Course Title, %2$s User Login */
                esc_html__( 'Check the new assignment that was submitted by the student. Assignment on %1$s sent by %2$s in the course %3$s', 'masterstudy-lms-learning-management-system' ),
                $assignment_title,
                $user_login,
                $course_title,
            );
            STM_LMS_Helpers::send_email(
                $instructor['email'],
                esc_html__( 'New assignment', 'masterstudy-lms-learning-management-system-pro' ),
                $message,
                'stm_lms_new_assignment',
                compact( 'user_login', 'course_title', 'assignment_title' )
            );

            //ChinDevs code to also send email to Student
            $message = sprintf(
            /* translators: %1$s Assignment Title, %2$s Course Title */
                esc_html__( 'Your assignment, %1$s for course %2$s has been submitted.', 'masterstudy-lms-learning-management-system' ),
                $assignment_title,
                $course_title,
            );
            STM_LMS_Helpers::send_email(
                $user['email'],
                esc_html__('Assignment Submitted and Pending Review', 'masterstudy-lms-learning-management-system-pro' ),
                $message,
                'stm_lms_assignment_submitted',
                compact('course_title', 'assignment_title' )
            );
        }

        return 'OK';
    }

}

new SLMS_Assignments();