<?php

class SLMS_Quiz {

    public function __construct(){

        add_action('wp_ajax_slms_reset_quiz', array($this, 'reset_quiz_ajax'));
        add_action('wp_ajax_slms_save_quiz', array($this, 'save_quiz_ajax'));

        remove_action( 'wp_ajax_stm_lms_user_answers', 'STM_LMS_Quiz::user_answers' );
        remove_action( 'wp_ajax_nopriv_stm_lms_user_answers', 'STM_LMS_Quiz::user_answers' );

        add_action( 'wp_ajax_stm_lms_user_answers', array($this, 'user_answers') );
        add_action( 'wp_ajax_nopriv_stm_lms_user_answers', array($this, 'user_answers') );

//        add_action( 'stm_lms_quiz_passed', array( $this, 'quiz_passed' ), 20, 3 );

    }

    public function reset_quiz_ajax(){

        check_ajax_referer( 'wp_rest', 'nonce' );

        $response = [];

        $post_id = (isset($_POST['post_id'])) ? intval($_POST['post_id']) : 0;
        $item_id = (isset($_POST['item_id'])) ? intval($_POST['item_id']) : 0;
        $return_url = (isset($_POST['return_url'])) ? sanitize_text_field($_POST['return_url']) : '';

        if(empty($post_id) || empty($item_id) || !is_user_logged_in()) {
            $response['success'] = false;
            wp_send_json($response);
        }

        $user_id = get_current_user_id();

//        $this->reset_quiz( $post_id, $item_id, $user_id );

        $response['return_url'] = $return_url;
        $response['success'] = true;

        wp_send_json($response);
    }

    public static function show_answers( $quiz_id ) {
        if( isset($_GET['re-take']) && ! empty( $_GET['re-take'] ) ) {
            return false;
        }

        if ( ! STM_LMS_Quiz::can_watch_answers( $quiz_id ) ) {
            return false;
        }
        if ( STM_LMS_Quiz::quiz_passed( $quiz_id ) ) {
            return true;
        }

        return ( ! empty( $_GET['show_answers'] ) && $_GET['show_answers'] ) ? true : false;
    }

    public static function can_watch_answers( $quiz_id ) {
        if( isset($_GET['re-take']) && ! empty( $_GET['re-take'] ) ) {
            return false;
        }

        $show_answers = get_post_meta( $quiz_id, 'correct_answer', true );
        if ( ! empty( $show_answers ) && 'on' === $show_answers ) {
            return true;
        }

        return STM_LMS_Quiz::quiz_passed( $quiz_id );
    }

    public function reset_quiz( $post_id, $item_id, $user_id ) {
        $this->reset_user_answers( $post_id, $item_id, $user_id );
        stm_lms_delete_user_quiz( $user_id, $post_id, $item_id );
        STM_LMS_Course::update_course_progress( $user_id, $post_id );
    }

    public function reset_user_answers( $course_id, $quiz_id, $student_id ) {
        global $wpdb;
        $table = stm_lms_user_answers_name( $wpdb );
        $wpdb->query(
            $wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                "DELETE FROM {$table} WHERE `course_id` = %d AND `quiz_id` = %d AND `user_id` = %d ",
                $course_id,
                $quiz_id,
                $student_id
            )
        );
        wp_reset_postdata();
    }

//    public function reset_user_quiz_points( $quiz_id, $student_id ) {
//        global $wpdb;
//        $table = stm_lms_point_system_name( $wpdb );
//        $wpdb->query(
//            $wpdb->prepare(
//                "DELETE FROM {$table} WHERE `id` = %d AND `user_id` = %d ",
//                $quiz_id,
//                $student_id
//            )
//        );
//        wp_reset_postdata();
//    }

    public function save_quiz_ajax(){
        check_ajax_referer( 'wp_rest', 'nonce' );

        $response = [];

        $course_id = (isset($_POST['post_id'])) ? intval($_POST['post_id']) : 0;
        $quiz_id = (isset($_POST['item_id'])) ? intval($_POST['item_id']) : 0;

        if(empty($course_id) || empty($quiz_id) || !is_user_logged_in()) {
            $response['success'] = false;
            wp_send_json($response);
        }

        $user_id = get_current_user_id();

        $has_answers = false;

//        $progress = 0;
        $quiz_info       = STM_LMS_Helpers::parse_meta_field( $quiz_id );
        $total_questions = count( explode( ',', $quiz_info['questions'] ) );
        $single_question_score_percent = 100 / $total_questions;

        $cutting_rate                  = ( ! empty( $quiz_info['re_take_cut'] ) ) ? ( 100 - $quiz_info['re_take_cut'] ) / 100 : 1;

        $user_quizzes   = stm_lms_get_user_quizzes( $user_id, $quiz_id, array( 'user_quiz_id', 'progress' ) );
        $attempt_number = count( $user_quizzes ) + 1;
        $prev_answers   = ( 1 !== $attempt_number ) ? stm_lms_get_user_answers( $user_id, $quiz_id, $attempt_number - 1, true, array( 'question_id' ) ) : array();

        foreach ( $_POST as $question_id => $value ) {
            if ( is_numeric( $question_id ) ) {
                $question_id = intval( $question_id );
                $type        = get_post_meta( $question_id, 'type', true );

                if ( 'fill_the_gap' === $type ) {
                    $answer = STM_LMS_Quiz::encode_answers( $value );
                } else {
                    if ( is_array( $value ) ) {
                        $answer = STM_LMS_Quiz::sanitize_answers( $value );
                    } else {
                        $answer = sanitize_text_field( $value );
                    }
                }

                $user_answer = ( is_array( $answer ) ) ? implode( ',', $answer ) : $answer;

                $correct_answer = STM_LMS_Quiz::check_answer( $question_id, $answer );

                if ( $correct_answer ) {
                    if ( 1 === $attempt_number || STM_LMS_Helpers::in_array_r( $question_id, $prev_answers ) ) {
                        $single_question_score = $single_question_score_percent;
                    } else {
                        $single_question_score = $single_question_score_percent * $cutting_rate;
                    }

//                    $progress += $single_question_score;
                }

                $add_answer = compact( 'user_id', 'course_id', 'quiz_id', 'question_id', 'attempt_number', 'user_answer', 'correct_answer' );
                stm_lms_add_user_answer( $add_answer );

                $has_answers = true;
            }
        }

        $response['success'] = $has_answers;

        wp_send_json($response);
    }

    public function user_answers(){

        check_ajax_referer( 'user_answers', 'nonce' );

        $source   = ( ! empty( $_POST['source'] ) ) ? intval( $_POST['source'] ) : '';
        $sequency = ! empty( $_POST['questions_sequency'] ) ? $_POST['questions_sequency'] : array();
        $sequency = json_encode( $sequency );
        $user     = apply_filters( 'user_answers__user_id', STM_LMS_User::get_current_user(), $source );
        /*Checking Current User*/
        if ( ! $user['id'] ) {
            die;
        }
        $user_id   = $user['id'];
        $course_id = ( ! empty( $_POST['course_id'] ) ) ? intval( $_POST['course_id'] ) : '';
        $course_id = apply_filters( 'user_answers__course_id', $course_id, $source );

        if ( empty( $course_id ) || empty( $_POST['quiz_id'] ) ) {
            die;
        }
        $quiz_id = intval( $_POST['quiz_id'] );
        $progress        = 0;
        $quiz_info       = STM_LMS_Helpers::parse_meta_field( $quiz_id );
        $total_questions = count( explode( ',', $quiz_info['questions'] ) );

        $questions = explode( ',', $quiz_info['questions'] );

        foreach ( $questions as $question ) {
            $type = get_post_meta( $question, 'type', true );

            if ( 'question_bank' !== $type ) {
                continue;
            }

            $answers = get_post_meta( $question, 'answers', true );

            if ( ! empty( $answers[0] ) && ! empty( $answers[0]['categories'] ) && ! empty( $answers[0]['number'] ) ) {
                $number     = $answers[0]['number'];
                $categories = wp_list_pluck( $answers[0]['categories'], 'slug' );

                $questions = get_post_meta( $quiz_id, 'questions', true );
                $questions = ( ! empty( $questions ) ) ? explode( ',', $questions ) : array();

                $args = array(
                    'post_type'      => 'stm-questions',
                    'posts_per_page' => $number,
                    'post__not_in'   => $questions,
                    'tax_query'      => array(
                        array(
                            'taxonomy' => 'stm_lms_question_taxonomy',
                            'field'    => 'slug',
                            'terms'    => $categories,
                        ),
                    ),
                );

                $q = new WP_Query( $args );

                if ( $q->have_posts() ) {

                    $total_in_bank = $q->found_posts - 1;
                    if ( $total_in_bank > $number ) {
                        $total_in_bank = $number - 1;
                    }
                    $total_questions += $total_in_bank;
                    wp_reset_postdata();
                }
            }
        }
        $single_question_score_percent = 100 / $total_questions;
        $cutting_rate                  = ( ! empty( $quiz_info['re_take_cut'] ) ) ? ( 100 - $quiz_info['re_take_cut'] ) / 100 : 1;
        $passing_grade                 = ( ! empty( $quiz_info['passing_grade'] ) ) ? intval( $quiz_info['passing_grade'] ) : 0;

        $user_quizzes   = stm_lms_get_user_quizzes( $user_id, $quiz_id, array( 'user_quiz_id', 'progress' ) );
        $attempt_number = count( $user_quizzes ) + 1;
        $prev_answers   = ( 1 !== $attempt_number ) ? stm_lms_get_user_answers( $user_id, $quiz_id, $attempt_number - 1, true, array( 'question_id' ) ) : array();

        $slms_total_questions_points = 0;
        $slms_total_answers_points = 0;

        foreach ( $_POST as $question_id => $value ) {
            if ( is_numeric( $question_id ) ) {
                $question_id = intval( $question_id );
                $type        = get_post_meta( $question_id, 'type', true );

                if ( 'fill_the_gap' === $type ) {
                    $answer = STM_LMS_Quiz::encode_answers( $value );
                } else {
                    if ( is_array( $value ) ) {
                        $answer = STM_LMS_Quiz::sanitize_answers( $value );
                    } else {
                        $answer = sanitize_text_field( $value );
                    }
                }


                $user_answer = ( is_array( $answer ) ) ? implode( ',', $answer ) : $answer;

                $correct_answer = STM_LMS_Quiz::check_answer( $question_id, $answer );

                if ( $correct_answer ) {
                    if ( 1 === $attempt_number || STM_LMS_Helpers::in_array_r( $question_id, $prev_answers ) ) {
                        $single_question_score = $single_question_score_percent;
                    } else {
                        $single_question_score = $single_question_score_percent * $cutting_rate;
                    }

                    $progress += $single_question_score;
                }

                $slms_points = (int)get_post_meta($question_id, 'slms_points', true);

                $slms_total_questions_points += $slms_points;

                if($correct_answer) {
                    $slms_total_answers_points += $slms_points;
                }

                $add_answer = compact( 'user_id', 'course_id', 'quiz_id', 'question_id', 'attempt_number', 'user_answer', 'correct_answer' );
                stm_lms_add_user_answer( $add_answer );
            }
        }

        if(!empty($slms_total_questions_points)) {
            $progress = ($slms_total_answers_points / $slms_total_questions_points) * 100;
        }

        /*Add user quiz*/
        $progress  = round( $progress );
        $status    = ( $progress < $passing_grade ) ? 'failed' : 'passed';
        $user_quiz = compact( 'user_id', 'course_id', 'quiz_id', 'progress', 'status', 'sequency' );

        stm_lms_add_user_quiz( $user_quiz );

        /*REMOVE TIMER*/
        stm_lms_get_delete_user_quiz_time( $user_id, $quiz_id );

        if ( 'passed' === $status ) {
            STM_LMS_Course::update_course_progress( $user_id, $course_id );
            $user_login   = $user['login'];
            $course_title = get_the_title( $course_id );
            $quiz_name    = get_the_title( $quiz_id );
            $message      = sprintf(
            /* translators: %1$s Course Title, %2$s User Login */
                esc_html__( '%1$s completed the %2$s on the course %3$s with a Passing grade of %4$s%%', 'masterstudy-lms-learning-management-system' ),
                $user_login,
                $quiz_name,
                $course_title,
                $passing_grade,
            );

            STM_LMS_Mails::send_email( 'Quiz Completed', $message, $user['email'], array(), 'stm_lms_course_quiz_completed_for_user', compact( 'user_login', 'course_title', 'quiz_name', 'passing_grade' ) );

        }
        $user_quiz['passed']   = $progress >= $passing_grade;
        $user_quiz['progress'] = round( $user_quiz['progress'] );
        $user_quiz['url']      = '<a class="btn btn-default btn-close-quiz-modal-results" href="' . apply_filters( 'stm_lms_item_url_quiz_ended', STM_LMS_Course::item_url( $course_id, $quiz_id ) ) . '">' . esc_html__( 'Close', 'masterstudy-lms-learning-management-system' ) . '</a>';
        $user_quiz['url']      = apply_filters( 'user_answers__course_url', $user_quiz['url'], $source );

        do_action( 'stm_lms_quiz_' . $status, $user_id, $quiz_id, $user_quiz['progress'] );

        wp_send_json( $user_quiz );
    }

//    public function get_user_quiz_points( $user_id, $quiz_id ) {
//        global $wpdb;
//        $table = stm_lms_point_system_name( $wpdb );
//
//        $request = "SELECT * FROM `{$table}`
//			WHERE
//			`user_id` = '{$user_id}' AND `id` = '{$quiz_id}' AND `action_id` = 'quiz_passed'";
//
//        return $wpdb->get_results( $request, ARRAY_A );
//    }

//    public function update_user_quiz_points( $user_id, $quiz_id ) {
//        global $wpdb;
//        $table_name = stm_lms_point_system_name( $wpdb );
//
//        $user_points = $this->get_user_quiz_points( $user_id, $quiz_id );
//
//        $latest_answers = stm_lms_get_quiz_latest_answers( $user_id, $quiz_id, 100, array('course_id','question_id','correct_answer') );
//
//        $questions_score = 0;
//
//        if(count($latest_answers)) {
//            foreach ($latest_answers as $item) {
//                $question_id = intval($item['question_id']);
//                $correct_answer = intval($item['correct_answer']);
//                if($points = (int)get_post_meta($question_id, 'points', true)) {
//                    if(!empty($correct_answer) && !empty($points)) {
//                        $questions_score += $points;
//                    }
//                }
//            }
//        }
//
//        if(count($user_points)) {
//            foreach ($user_points as $item) {
//
//                $score = intval($item['score']);
//
//                $score = $score + $questions_score;
//
//                $user_points = array(
//                    'user_id'   => $user_id,
//                    'id'        => $quiz_id,
//                    'score'     => $score,
//                );
//
//                $where = array(
//                    'user_points_id' => intval($item['user_points_id']),
//                    'action_id' => 'quiz_passed'
//                );
//
//                $wpdb->update(
//                    $table_name,
//                    $user_points,
//                    $where
//                );
//            }
//        }
//
//    }

//    public function quiz_passed($user_id, $quiz_id, $progress){
//        $this->update_user_quiz_points( $user_id, $quiz_id );
//    }

}

new SLMS_Quiz();