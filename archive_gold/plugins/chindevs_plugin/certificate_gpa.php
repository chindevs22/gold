<?php
add_filter( 'stm_certificates_fields', 'add_gpa_field' );
function add_gpa_field( $fields ) {

    // ChinDevs Code to Add GPA
    $fields['student_gpa']  = array (
        'name'  => esc_html__( 'GPA', 'masterstudy-lms-learning-management-system-pro' ),
        'value' => esc_html__( '-GPA-', 'masterstudy-lms-learning-management-system-pro' ),
    );

    return $fields;
}

//ChinDevs function to add GPA to certificate
function chindevs_generate_certificate_student_gpa ( $user_id , $course_id ) {
	error_log("generating gpa for chindevs certificitae");
    global $wpdb;
    $table = stm_lms_user_quizzes_name( $wpdb );

	// Calculate Quiz Grade
    $request =  "SELECT t1.* FROM {$table} t1 INNER JOIN (SELECT user_id, course_id, quiz_id, MIN(user_quiz_id) AS min_id FROM wp_stm_lms_user_quizzes WHERE user_id =  {$user_id} AND course_id = {$course_id} GROUP BY user_id, quiz_id, course_id) t2 ON t1.user_id = t2.user_id AND t1.course_id = t2.course_id AND t1.quiz_id = t2.quiz_id AND t1.user_quiz_id = t2.min_id where t1.user_id = {$user_id} and t1.course_id = {$course_id}";

    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    $quiz_grades = $wpdb->get_results( $request, ARRAY_A );
	error_log("quiz grades");
	error_log(print_r($quiz_grades, true));
    // get grade for quizzes
    $total_quizzes = count($quiz_grades);
    $quiz_grade = 0;
    foreach($quiz_grades as $quiz_row) {
        $quiz_grade += $quiz_row['progress'];
    }

   	// Calculate Assignment Grade
    $user_assignments = get_user_assignment($user_id, $course_id);
    $assignment_grade = 0;
    $total_assignments = 0;

	error_log($user_id);
	error_log(print_r($user_assignments, true));

    foreach($user_assignments as $assignment) {
		$grade = get_post_meta($assignment, 'assignment_grade', true);
		if (!empty($grade)) {
			$assignment_grade += $grade;
			$total_assignments += 1;
		}
	}

	//calculate totals
    $total_grade = $assignment_grade + $quiz_grade;
    $total_lessons = $total_assignments + $total_quizzes;

    $final_percent = $total_grade/$total_lessons;

    //what to show on a course with only lessons that has certificate?
    if ($total_lessons == 0) {
        return "P";
    }
    if ($final_percent >= 80) {
        return "O+";
    }
    if ($final_percent >= 60) {
        return "A+";
    }
    if ($final_percent >= 50) {
        return "A";
    }
	return "B";
}

function get_user_assignment($user_id, $course_id) {

	$args = array(
		'post_type'      => 'stm-user-assignment',
		'posts_per_page' => -1,
		'meta_query'     => array(
			'relation' => 'AND',
			array(
				'key'     => 'student_id',
				'value'   => $user_id,
				'compare' => '=',
			),
			array(
				'key'     => 'course_id',
				'value'   => $course_id,
				'compare' => '=',
			),
			array(
				'key'     => 'status',
				'value'   => 'pending',
				'compare' => '!=',
			),
			array(
				'key'     => 'try_num',
				'value'   => 1,
				'compare' => '=',
			),
		),
	);


    $query = new WP_Query( $args );
    $posts = wp_list_pluck( $query->posts, 'ID' );
	error_log("the assignments");
	error_log(print_r($posts, true));

    return $posts;
}
?>