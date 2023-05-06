<?php
    function enrol_sm_users_from_csv($enrolData) {
        global $wpdb;
        // $userMGMLtoWP, $courseMGMLtoWP, $lessonMGMLtoWP, $selfAssessmentToUser, $questionMGMLtoWP;
        $table_name = 'wp_stm_lms_user_courses';
        // TODO: Hardcoding since we didn't make that lesson - $enrolData['current_lesson_id']
        // $wp_course_id = $courseMGMLtoWP[$enrolData['course_id']];

		error_log($enrolData['course_id']);
		$wp_course_id = get_sm($enrolData['course_id']);
//         $wp_course_id = get_from_post('stm-courses', 'mgml_course_id', $enrolData['course_id']);
        if (!isset($wp_course_id) || $wp_course_id === 0) {
            error_log("No data for this course: ");
            return;
        }
        echo $wp_course_id;
		$wp_user_id = get_user_id('mgml_user_id', $enrolData['user_id']);
        if (!isset($wp_user_id)) {
            error_log("No data for this user: ");
            return;
        }
		echo "User idL " . $wp_user_id;
        $wpdb->insert($table_name, array(
            'user_course_id' => NULL,
            'user_id' => $wp_user_id,
            'course_id' => $wp_course_id,
            'current_lesson_id' => '0',
            'progress_percent' => '100',
            'status' => 'enrolled',
            'start_time' => time(),
        ));
		error_log("inserted into table");
        $students = get_post_meta($wp_course_id, 'current_students', true);
        update_post_meta($wp_course_id, 'current_students', $students + 1);
		error_log("finished enrol");
    }
?>