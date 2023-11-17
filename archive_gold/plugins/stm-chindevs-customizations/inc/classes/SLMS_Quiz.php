<?php

class SLMS_Quiz {

    public function __construct(){

        add_action('wp_ajax_slms_reset_quiz', array($this, 'reset_quiz_ajax'));
        add_action('wp_ajax_slms_save_quiz', array($this, 'save_quiz_ajax'));

        remove_action( 'wp_ajax_stm_lms_user_answers', 'STM_LMS_Quiz::user_answers' );
        remove_action( 'wp_ajax_nopriv_stm_lms_user_answers', 'STM_LMS_Quiz::user_answers' );

        add_action( 'wp_ajax_stm_lms_user_answers', array($this, 'user_answers') );
        add_action( 'wp_ajax_nopriv_stm_lms_user_answers', array($this, 'user_answers') );

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

        $response['return_url'] = $return_url;
        $response['success'] = true;

        wp_send_json($response);
    }

    public static function show_results( $quiz_id = 0 ) {
        if( isset($_GET['re-take']) && ! empty( $_GET['re-take'] ) ) {
            return false;
        }

        if( ! isset($_GET['re-take']) && ! isset($_GET['show_answers']) ) {
            return false;
        }

        return true;
    }

    public static function show_answers( $quiz_id ) {
        if( ! isset($_GET['re-take']) && ! isset($_GET['show_answers']) ) {
            return false;
        }

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

        $answers_list = array();

        $slms_total_questions_points = 0;
        $slms_total_answers_points = 0;

        foreach ( $questions as $question ) {
            $type = get_post_meta( $question, 'type', true );

            $slms_points = (int)get_post_meta($question, 'slms_points', true);
            $slms_total_questions_points += $slms_points;

            if ( 'question_bank' !== $type ) {
                continue;
            }

            $answers = get_post_meta( $question, 'answers', true );

            $answers_list[$question] = $answers;

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

                $type_question = get_post_meta( $question_id, 'type', true );

                $slms_points = (int)get_post_meta($question_id, 'slms_points', true);

//              ChinDevs code change to not have partial points for multiple choice
//                    if($type_question == 'multi_choice') {
//                    $multi_choice_data = self::check_multi_choice_answer( $question_id, $answer, $answers_list[$question_id] );
//                    $correct_answer = $multi_choice_data['correct_answer'];
//                    $question_score = $multi_choice_data['question_score'];
//
//                    $slms_points = ($question_score > 0 && $slms_points > 0) ? round(($question_score / 100) * $slms_points) : 0;
//                }
                if($type_question == 'item_match') {
                    $item_match_data = self::check_item_match_answer( $question_id, $answer, $answers_list[$question_id] );
                    $correct_answer = $item_match_data['correct_answer'];
                    $question_score = $item_match_data['question_score'];

                    $slms_points = ($question_score > 0 && $slms_points > 0) ? round(($question_score / 100) * $slms_points) : 0;
                } else {
                    $correct_answer = STM_LMS_Quiz::check_answer( $question_id, $answer );
                }

                if ( $correct_answer ) {
                    if ( 1 === $attempt_number || STM_LMS_Helpers::in_array_r( $question_id, $prev_answers ) ) {
                        $single_question_score = $single_question_score_percent;
                    } else {
                        $single_question_score = $single_question_score_percent * $cutting_rate;
                    }

                    $progress += $single_question_score;
                }

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

        SLMS_User_Quizzes::maybe_create_db_column();
        $stored_points = $slms_total_answers_points.'/'.$slms_total_questions_points;

        $user_quiz = compact( 'user_id', 'course_id', 'quiz_id', 'progress', 'status', 'sequency', 'stored_points' );

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

    public static function check_multi_choice_answer( $question_id, $answer, $answers = array() )
    {

        $answers = ! empty( $answers ) ? $answers : get_post_meta( $question_id, 'answers', true );

        if ( empty( $answers ) ) {
            return false;
        }

        $answer           = wp_unslash( $answer );
        $answer           = array_map( 'rawurldecode', $answer );

        $correct_answers = 0;

        foreach ( $answers as $stored_answer ) {
            $full_answer = $stored_answer['text'];
            if ( ! empty( $stored_answer['text_image']['url'] ) ) {
                $full_answer .= '|' . esc_url( $stored_answer['text_image']['url'] );
            }
            $full_answer = rawurldecode( $full_answer );

            if ( in_array( $full_answer, $answer ) && $stored_answer['isTrue'] ) {
                $correct_answers++;
            } elseif ( ! in_array( $full_answer, $answer ) && ! $stored_answer['isTrue'] ) {
                $correct_answers++;
            }
        }

        return array(
            'correct_answer' =>  ($correct_answers > 0),
            'question_score' => (!empty($correct_answers) && count($answers)) ? ($correct_answers / count($answers)) * 100 : 0
        );

    }

    public static function check_item_match_answer( $question_id, $answer, $answers = array() )
    {

//        return array(
//            'correct_answer' =>  false,
//            'question_score' => 0
//        );
        $answers = ! empty( $answers ) ? $answers : get_post_meta( $question_id, 'answers', true );

        $answer = explode( '[stm_lms_sep]', str_replace( '[stm_lms_item_match]', '', $answer ) );
        $correct_answers = 0;
        foreach ( $answers as $i => $correct_answer ) {
            if ( strtolower( $correct_answer['text'] ) == strtolower( $answer[ $i ] ) ) {
                $correct_answers++;
            };
        }

        return array(
            'correct_answer' =>  ($correct_answers > 0),
            'question_score' => (!empty($correct_answers) && count($answers)) ? ($correct_answers / count($answers)) * 100 : 0
        );

    }

}

new SLMS_Quiz();