<?php
	// --------------------------------------------------------------------------------------------
	// USER PROGRESS SECTION
	// --------------------------------------------------------------------------------------------
    require_once 'helpers.php';

    function progress_users_assignment_from_csv($progressData, $isPostal) {
        global $wpdb;

        if ($progressData['quiz_marks'] == 0) {
            error_log("No quiz marks for the quiz on this row: " . $progressData['id']);
            return;
        }

        $mgml_user_id = $progressData['user_id'];
        $table_name = 'wp_stm_lms_user_quizzes';

        //Get Ids from metadata of each type
        $wp_user_id = get_user_id('mgml_user_id', $progressData['user_id']);
        if (!isset($wp_user_id)) {
            error_log("No data for this user: " . $mgml_user_id);
            return;
        }
        $wp_course_id = get_from_post('stm-courses', 'mgml_course_id', $progressData['course_id']);
        if (!isset($wp_course_id)) {
            error_log("No data for this course: ");
            return;
        }

        // Is this a Split Assignment??
        $hasDot = str_contains($progressData['quiz_id'], '.');
        if ($hasDot || $isPostal) {
			error_log("is a split assignment");

            $wp_assignment_id = get_from_post('stm-assignments', 'mgml_assignment_id', $progressData['quiz_id']);
			error_log($wp_assignment_id);
            //Student Name
            $user = get_user_by("id", $wp_user_id);
            $student_name = $user->user_nicename;
            //Course Name
            $course_name = get_the_title($wp_course_id);

            $postTitle = $student_name . " on &#8220;" . $course_name . "&#8221;";
			error_log("Post Tite");
			error_log($postTitle);

            $wpdata['post_title'] = $postTitle;
            $wpdata['post_status'] ='publish';
            $wpdata['post_type'] = 'stm-user-assignment';
			$wpdata['post_author'] = $wp_user_id;
			$wpdata['meta_input']  = array(
				'student_id' => $wp_user_id,
				'course_id' => $wp_course_id,
				'assignment_id' =>  $wp_assignment_id,
				'status' => 'passed'
			);

			error_log("all good before inserting");
            $user_assignment_post_id = wp_insert_post( $wpdata );

			error_log("user assignemnt post id");
			error_log($user_assignment_post_id);
            // Update Metadata
            $date = strtotime($progressData['completion_date'] . "06:00:00") * 1000;
			$grade = $progressData['marks']/$progressData['quiz_marks'] * 100; //aka progress


            update_post_meta($user_assignment_post_id, 'try_num', $progressData['running_total']);
            update_post_meta($user_assignment_post_id, 'start_time', $date);
            update_post_meta($user_assignment_post_id, 'end_time', $date);
            update_post_meta($user_assignment_post_id, 'mgml_usa_id', $progressData['id']);
            update_post_meta($user_assignment_post_id, 'assignment_grade', $grade);
			update_post_meta($user_assignment_post_id, 'points_earned', $progressData['marks']);
            update_post_meta($user_assignment_post_id, 'total_points', $progressData['quiz_marks']);
            update_post_meta($user_assignment_post_id, 'who_view', 1);

            if(!empty($progressData['remarks']) &&  $progressData['remarks'] != NULL) {
               update_post_meta($user_assignment_post_id, 'editor_comment', $progressData['remarks']);
            }

        } else {
			// A Normal Quiz
            $wp_quiz_id = get_from_post('stm-quizzes', 'mgml_lesson_id', $progressData['quiz_id']);
            if (!isset($wp_quiz_id)) {
                error_log("No data for this quiz: ");
                return;
            }
            $grade = $progressData['marks']/$progressData['quiz_marks'] * 100; //aka progress

            $wpdb->insert($table_name, array(
                'user_quiz_id' => NULL,
                'user_id' => $wp_user_id,
                'course_id' => $wp_course_id,
                'quiz_id' => $wp_quiz_id,
                'progress' => $grade,
                'status' => 'passed',
                'sequency' => '[]',
            ));
            progress_user_lessons($wp_course_id, $wp_quiz_id, $wp_user_id);
        }
    }

    function progress_user_assignment_answers_from_csv($answerData) {

        $wp_assignment_id = get_from_post('stm-user-assignment', 'mgml_usa_id', $answerData['self_assessment_id']);

		$current_post = get_post($wp_assignment_id);
		error_log(print_r($current_post, true));
		$content = $current_post->post_content;
        $content .= '<br>' . $answerData['answers'];
		$updated_post = array(
			'ID'           => $current_post->ID,
			'post_content' => $content
		);
		wp_update_post($updated_post);

    }
?>