<?php

class SLMS_User_Assignment extends STM_LMS_User_Assignment {

    public function __construct() {
        remove_all_actions( 'wp_ajax_stm_lms_edit_user_answer' );
        add_action( 'wp_ajax_stm_lms_edit_user_answer', array( $this, 'stm_lms_edit_user_answer' ) );

        remove_all_actions( 'wp_ajax_stm_lms_get_enrolled_assignments' );
        add_action( 'wp_ajax_stm_lms_get_enrolled_assignments', array( $this, 'enrolled_assignments' ) );

        remove_filter( 'stm_lms_course_passed_items', array( 'STM_LMS_User_Assignment', 'essay_passed' ), 10, 3 );
        add_filter( 'stm_lms_course_passed_items', array( $this, 'essay_passed' ), 10, 3 );

    }

    private static function per_page() {
        return 6;
    }

    public function stm_lms_edit_user_answer() {
        check_ajax_referer( 'stm_lms_edit_user_answer', 'nonce' );

        $status        = ( 'approve' === $_POST['status'] ) ? 'passed' : 'not_passed';
        $assignment_id = intval( $_POST['assignment_id'] );
        $comment       = wp_kses_post( $_POST['content'] );

        if ( get_post_status( $assignment_id ) !== 'pending' ) {
            die;
        }

        $student_id = get_post_meta( $assignment_id, 'student_id', true );
        $course_id  = get_post_meta( $assignment_id, 'course_id', true );
        $email_sended  = get_post_meta( $assignment_id, 'email_sended', true );

        wp_update_post(
            array(
                'ID'          => $assignment_id,
                'post_status' => 'publish',
            )
        );

        //ChinDevs code to add grade
        $points_earned = wp_kses_post ($_POST['points_earned']);
        $orig_assignment = get_post_meta($assignment_id, 'assignment_id', true);
        $total_points = get_post_meta($orig_assignment, 'total_points', true);
        if (empty($total_points) || $total_points == 0) {
            $total_points = 100;
            update_post_meta($orig_assignment, 'total_points', 100);
        }
        $grade = $points_earned/$total_points * 100;
        update_post_meta( $assignment_id, 'assignment_grade', $grade );
        update_post_meta( $assignment_id, 'points_earned', $points_earned );

        update_post_meta( $assignment_id, 'editor_comment', $comment );
        update_post_meta( $assignment_id, 'status', $status );
        update_post_meta( $assignment_id, 'who_view', 0 );

        if ( 'passed' === $status ) {
            STM_LMS_Course::update_course_progress( $student_id, $course_id );
        }

        $student = STM_LMS_User::get_current_user( $student_id );

        if(empty($email_sended) || $email_sended != $status) {
            $message = esc_html__( 'Your assignment has been checked', 'masterstudy-lms-learning-management-system-pro' );
            STM_LMS_Helpers::send_email(
                $student['email'],
                esc_html__( 'Assignment status change.', 'masterstudy-lms-learning-management-system-pro' ),
                $message,
                'stm_lms_assignment_checked',
                compact( 'message' )
            );

            do_action( 'stm_lms_assignment_' . $status, $student_id, $assignment_id );
        }

        update_post_meta( $assignment_id, 'email_sended', $status );

        wp_send_json( 'OK' );

    }

    public function essay_passed( $passed_items, $curriculum, $user_id ) {
        $curriculum = STM_LMS_Helpers::only_array_numbers( $curriculum );
        foreach ( $curriculum as $item ) {
            if ( get_post_type( $item ) !== 'stm-assignments' ) {
                continue;
            }
            if ( self::assignment_passed( $user_id, $item ) ) {
                $passed_items++;
            }
        }
        return $passed_items;
    }

    public static function my_assignments( $user_id, $page = null ) {
        $args = array(
            'post_type'      => 'stm-user-assignment',
            'posts_per_page' => self::per_page(),
            'offset'         => ( $page * self::per_page() ) - self::per_page(),
            'post_status'    => 'any',
            'meta_query'     => array(
                'relation' => 'AND',
                array(
                    'key'     => 'student_id',
                    'value'   => $user_id,
                    'compare' => '=',
                ),
            ),
        );

        if ( ! empty( $_GET['status'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $status = sanitize_text_field( $_GET['status'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            if ( 'pending' === $status ) {
                $args['post_status'] = 'pending';
            }
            if ( 'passed' === $status ) {
                $args['post_status']  = 'publish';
                $args['meta_query'][] = array(
                    'key'     => 'status',
                    'value'   => 'passed',
                    'compare' => '=',
                );
            }
            if ( 'not_passed' === $status ) {
                $args['post_status']  = 'publish';
                $args['meta_query'][] = array(
                    'key'     => 'status',
                    'value'   => 'not_passed',
                    'compare' => '=',
                );
            }
        }

        if ( ! empty( $_GET['s'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $args['s'] = sanitize_text_field( $_GET['s'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        }

        $q = new WP_Query( $args );

        $posts = array();
        if ( $q->have_posts() ) {
            while ( $q->have_posts() ) {
                $q->the_post();
                $id            = get_the_ID();
                $course_id     = get_post_meta( $id, 'course_id', true );
                $assignment_id = get_post_meta( $id, 'assignment_id', true );
                $who_view      = get_post_meta( $id, 'who_view', true );
                //ChinDev code to grab the assignment grade and return it to JS
                $assignment_grade = get_post_meta ($id, 'assignment_grade', true);

                //ChinDev code to get assignment grade and the course_id
                $posts[] = array(
                    'assignment_title' => get_the_title( $assignment_id ),
                    'course_title'     => get_the_title( $course_id ),
                    'assignment_grade' => $assignment_grade,
                    'course_id'        => $course_id,
                    'updated_at'       => stm_lms_time_elapsed_string( gmdate( 'Y-m-d H:i:s', get_post_timestamp() ) ),
                    'status'           => self::statuses( get_post_status(), get_post_meta( $id, 'status', true ) ),
                    'instructor'       => STM_LMS_User::get_current_user( get_post_field( 'post_author', $course_id ) ),
                    'url'              => STM_LMS_Lesson::get_lesson_url( $course_id, $assignment_id ),
                    'who_view'         => $who_view,
                    'pages'            => ceil( $q->found_posts / self::per_page() ),
                );

            }
        }
        return $posts;
    }

    public function enrolled_assignments() {
        check_ajax_referer( 'stm_lms_get_enrolled_assingments', 'nonce' );
        $page = intval( $_GET['page'] );
        $user = STM_LMS_User::get_current_user();
        wp_send_json( self::my_assignments( $user['id'], $page ) );

    }
}

new SLMS_User_Assignment();