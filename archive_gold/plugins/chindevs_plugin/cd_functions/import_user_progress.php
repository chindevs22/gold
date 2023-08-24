<?php
	// --------------------------------------------------------------------------------------------
	// USER PROGRESS SECTION
	// --------------------------------------------------------------------------------------------
require_once 'helpers.php';

function progress_users_quiz_from_csv($progressData) {
    global $wpdb;
    //, $userMGMLtoWP, $courseMGMLtoWP, $selfAssessmentToUser, $lessonMGMLtoWP, $attemptNumberMap;

    if ($progressData['quiz_marks'] == 0) {
        error_log("DATA ERROR: No quiz marks for the quiz on this row: " . $progressData['id']);
        return;
    }

    $mgml_user_id = $progressData['user_id'];
    $table_name = 'wp_stm_lms_user_quizzes';

    //Get Ids from metadata of each type
    $wp_user_id = get_user_id('mgml_user_id', $progressData['user_id']);
    if (!isset($wp_user_id)) {
		error_log("DATA ERROR: No data for this user: " .  $progressData['user_id']);
		$wp_user_id = 1; //Setting to Dadmin for Testing
        //return;
    }
    $wp_quiz_id = get_from_post('stm-quizzes', 'mgml_lesson_id', $progressData['quiz_id']);
    if (!isset($wp_quiz_id)) {
        error_log("DATA ERROR: No data for this quiz: " . $progressData['quiz_id']);
        return;
    }
    $wp_course_id = get_from_post('stm-courses', 'mgml_course_id', $progressData['course_id']);
    if (!isset($wp_course_id)) {
        error_log("DATA ERROR: No data for this course: " . $progressData['course_id']);
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

function progress_users_answers_from_csv($answerData) {
    global $wpdb;
    $table_name = 'wp_stm_lms_user_answers';

    $sa_id = $answerData['self_assessment_id'];
    $mgml_quiz_id = $answerData['quiz_id'];

    // Ensure the User Answers table has user_id for each self assessment id
    $wp_user_id = get_user_id('mgml_user_id', $answerData['user_id']);
    if (!isset($wp_user_id)) {
        error_log("DATA ERROR: No data for this user: " .  $answerData['user_id']);
		$wp_user_id = 1; //Setting to Dadmin for Testing
        //return;
    }
    $wp_quiz_id = get_from_post('stm-quizzes', 'mgml_lesson_id', $mgml_quiz_id);
    if (!isset($wp_quiz_id)) {
        error_log("DATA ERROR: No data for this quiz: " . $mgml_quiz_id);
        return;
    }
    $wp_course_id = get_from_post('stm-courses', 'mgml_course_id', $answerData['course_id']);
    if (!isset($wp_course_id)) {
        error_log("DATA ERROR: No data for this course: ". $answerData['course_id']);
        return;
    }

    // Check that this question is part of the quiz we expect
    $wp_question_id = get_from_post('stm-questions', 'mgml_question_id', $answerData['question_id']);
    if (get_post_meta($wp_question_id, 'mgml_quiz_id', true) != $mgml_quiz_id) {
        error_log("ERROR: In WP system, this question post " . $wp_question_id . "isn't related to this quiz: " . $mgml_quiz_id);
        return;
    }

    $options = get_post_meta($wp_question_id, 'mgml_answer_options', true);
    $userAnswers = create_array_from_string($answerData['answers'], '","'); // ex [ 2, 4 ]

    $arrLength = count($userAnswers);
    $chosenAnswers = array();
    for($x = 0; $x < $arrLength; $x++) {
        $correctAnswer = $userAnswers[$x]; //2
		if (!is_numeric($correctAnswer)) {
			error_log("Not a integer answer value so quitting");
			return;
		}
        array_push($chosenAnswers, $options[$correctAnswer - 1]);
    }

    $answerString = implode(",", $chosenAnswers); // comma seperated string of answers
    $isCorrect = ($answerData['question_marks'] == $answerData['marks_obtained']) ? "1" : "0";

    $attempts = $answerData['running_total'];
    if (empty($attempts) || $attempts == "NULL") {
        $attempts = 1;
    }
    $wpdb->insert($table_name, array(
        'user_answer_id' => NULL,
        'user_id' => $wp_user_id,
        'course_id' => $wp_course_id,
        'quiz_id' => $wp_quiz_id,
        'question_id' => $wp_question_id,
        'user_answer' => $answerString,
        'correct_answer' => $isCorrect,
        'attempt_number' => $attempts,
    ));
}

function enrol_users_from_csv($enrolData) {
    global $wpdb, $feedbackLessonID;
    // $userMGMLtoWP, $courseMGMLtoWP, $lessonMGMLtoWP, $selfAssessmentToUser, $questionMGMLtoWP;
    $table_name = 'wp_stm_lms_user_courses';
    // TODO: Hardcoding since we didn't make that lesson - $enrolData['current_lesson_id']
    // $wp_course_id = $courseMGMLtoWP[$enrolData['course_id']];

    $wp_course_id = get_from_post('stm-courses', 'mgml_course_id', $enrolData['course_id']);
    if (!isset($wp_course_id)) {
        error_log("DATA ERROR: No data for this course: " . $enrolData['course_id']);
        return;
    }
    $wp_user_id = get_user_id('mgml_user_id', $enrolData['user_id']);
    if (!isset($wp_user_id)) {
        error_log("DATA ERROR: No data for this user: " .  $enrolData['user_id']);
		$wp_user_id = 1; //Setting to Dadmin for Testing
        //return;
    }
    $wpdb->insert($table_name, array(
        'user_course_id' => NULL,
        'user_id' => $wp_user_id,
        'course_id' => $wp_course_id,
        'current_lesson_id' => '0',
        'progress_percent' => $enrolData['progress_percent'],
        'status' => 'enrolled',
        'start_time' => time(),
    ));

    // If course is complete, go back and mark Feedback Lesson as complete
    $user_lessons_table_name = $wpdb->prefix . 'stm_lms_user_lessons';
    if($enrolData['progress_percent'] == 100) {
        $wpdb->insert($user_lessons_table_name, array(
            'user_lesson_id' => NULL,
            'user_id' => $wp_user_id,
            'course_id' => $wp_course_id,
            'lesson_id' => $feedbackLessonID
        ));
    }

    $students = get_post_meta($wp_course_id, 'current_students', true);
    update_post_meta($wp_course_id, 'current_students', $students + 1);
}

?>