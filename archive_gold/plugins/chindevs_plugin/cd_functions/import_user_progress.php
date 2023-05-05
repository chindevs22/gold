<?php
	// --------------------------------------------------------------------------------------------
	// USER PROGRESS SECTION
	// --------------------------------------------------------------------------------------------
require_once 'helpers.php';

function progress_users_quiz_from_csv($progressData) {
    global $wpdb;
    //, $userMGMLtoWP, $courseMGMLtoWP, $selfAssessmentToUser, $lessonMGMLtoWP, $attemptNumberMap;

    if ($progressData['quiz_marks'] == 0) {
        error_log("No quiz marks for the quiz on this row: " . $progressData['id']);
        return;
    }

    $mgml_user_id = $progressData['user_id'];
    $table_name = 'wp_stm_lms_user_quizzes';
    //      $wp_user_id = $userMGMLtoWP[$mgml_user_id];
    // 		$wp_quiz_id = $lessonMGMLtoWP[$progressData['quiz_id']];
    // 		$wp_course_id =  $courseMGMLtoWP[$progressData['course_id']];

    //Get Ids from metadata of each type
    $wp_user_id = get_user_id('mgml_user_id', $progressData['user_id']);
    if (!isset($wp_user_id)) {
        error_log("No data for this user: " . $mgml_user_id);
        return;
    }
    $wp_quiz_id = get_from_post('stm-quizzes', 'mgml_lesson_id', $progressData['quiz_id']);
    if (!isset($wp_quiz_id)) {
        error_log("No data for this quiz: ");
        return;
    }
    $wp_course_id = get_from_post('stm-courses', 'mgml_course_id', $progressData['course_id']);
    if (!isset($wp_course_id)) {
        error_log("No data for this course: ");
        return;
    }

    $attempt_key = "" . $wp_user_id . $wp_course_id . $wp_quiz_id;
    $attempt_number = $progressData['running_total'];
    // 		$attemptNumberMap[$attempt_key] = $attempt_number;

    $grade = $progressData['marks']/$progressData['quiz_marks'] * 100; //aka progress


// 		// TODO: don't think we need this! Update/Set the user metadata field to append an array of their assessments
// 		$users_assessments = get_user_meta($wp_user_id, 'mgml_self_assessment_id', true);
// 		if (is_array($users_assessments)) {
// 			// if it's an array, append the new value to it
// 			$users_assessments[] = $progressData['id'];
// 			update_user_meta($wp_user_id, 'mgml_self_assessment_id', $users_assessments);
// 		} else {
// 			// if it's not an array, create a new array with the new value
// 			$users_assessments = array($progressData['id']);
// 			update_user_meta($wp_user_id, 'mgml_self_assessment_id', $users_assessments);
// 		}
    update_user_meta($wp_user_id, $attempt_key, $attempt_number);

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

function progress_user_lessons($wp_course_id, $wp_quiz_id, $wp_user_id) {
    global $wpdb;

    $curriculum_string = get_post_meta($wp_course_id, 'curriculum', true);
    $ca = create_array_from_string($curriculum_string, ',');
    $arrLength = count($ca);
    $quizIndex = 0;
    for($x = 0; $x < $arrLength; $x++) {
        if ($wp_quiz_id == $ca[$x]) {
            $quizIndex = $x;
            break;
        }
    }
    error_log("Course ID: " . $wp_course_id . " Quiz ID: " . $wp_quiz_id . " Index found: " . $quizIndex);

    $isLeft = false;
    $isRight = false;
    $lessonsCompleted = array();
    $indexLeft = $quizIndex - 1;
    $indexRight = $quizIndex + 1;
    // Go through curriculum array searching for the Lessons surrounding the quiz
    // When you hit a Section Name (intval will be false) or the ends of the array stop.
    // TODO: This code cant handle 2 quizzes in a section
    // Solution: Go Left Only, Check if I've hit a Quiz or Section
    while (!$isLeft) {
// 			if ($indexRight == $arrLength || intval($ca[$indexRight]) == 0) {
// 				$isRight = true;
// 			} else {
// 				array_push($lessonsCompleted, $ca[$indexRight++]);
// 			}
// 			// if im at the front, or im a section name or my id returns a quiz type post
        if( $indexLeft < 0 || intval($ca[$indexLeft]) == 0 || 'stm-quizzes' === get_post_type( $ca[$indexLeft] ) ) {
            $isLeft = true;
        } else {
            array_push($lessonsCompleted, $ca[$indexLeft--]);
        }
    }

    echo "COMPLETING LESSONS FOR QUIZ <br>";
    error_log("# of LESSONS COMPLETED : " . count($lessonsCompleted));
    // Insert Each Completed Lesson Based on Completed Quiz
    foreach($lessonsCompleted as $lesson_id) {
        echo "lesson completed: " . $lesson_id . " <br>";
        $table_name = 'wp_stm_lms_user_lessons';
        $wpdb->insert($table_name, array(
            'user_lesson_id' => NULL,
            'user_id' => $wp_user_id,
            'course_id' => $wp_course_id,
            'lesson_id' => $lesson_id
        ));
    }
}

function progress_users_answers_from_csv($answerData) {
    global $wpdb;
    //, $userMGMLtoWP, $attemptNumberMap, $courseMGMLtoWP, $lessonMGMLtoWP, $selfAssessmentToUser, $questionMGMLtoWP, $wpQuestionsToAnswers;
    $table_name = 'wp_stm_lms_user_answers';

    // get IDS
    //		$mgml_user_id = $selfAssessmentToUser[$sa_id];
    //		$wp_user_id = $userMGMLtoWP[$mgml_user_id];
    //		$wp_course_id = $courseMGMLtoWP[$answerData['course_id']];
    //		$wp_quiz_id = $lessonMGMLtoWP[$answerData['quiz_id']];
    // TODO CHECK the WP question ID should be part of the $lessonToQuestionsMap of $answerData['quiz_id']
    //		$wp_question_id = $questionMGMLtoWP[$answerData['question_id']];

    $sa_id = $answerData['self_assessment_id'];
    $mgml_quiz_id = $answerData['quiz_id'];

    // Ensure the User Answers table has user_id for each self assessment id
    $wp_user_id = get_user_id('mgml_user_id', $answerData['user_id']);
    if (!isset($wp_user_id)) {
        error_log("No data for this user: ");
        return;
    }
    $wp_quiz_id = get_from_post('stm-quizzes', 'mgml_lesson_id', $mgml_quiz_id);
    if (!isset($wp_quiz_id)) {
        error_log("No data for this quiz: ");
        return;
    }
    $wp_course_id = get_from_post('stm-courses', 'mgml_course_id', $answerData['course_id']);
    if (!isset($wp_course_id)) {
        error_log("No data for this course: ");
        return;
    }

    // Check that this question is part of the quiz we expect
    $wp_question_id = get_from_post('stm-questions', 'mgml_question_id', $answerData['question_id']);
    if (get_post_meta($wp_question_id, 'mgml_quiz_id', true) != $mgml_quiz_id) {
        error_log("ERROR: In WP system, this question post " . $wp_question_id . "isn't related to this quiz: " . $mgml_quiz_id);
        return;
    }

    // need to re-run the import questions for this to work
    //        $options = $wpQuestionsToAnswers[$wp_question_id];

    $options = get_post_meta($wp_question_id, 'mgml_answer_options', true);
    $userAnswers = create_array_from_string($answerData['answers'], '","'); // ex [ 2, 4 ]

    $arrLength = count($userAnswers);
    $chosenAnswers = array();
    for($x = 0; $x < $arrLength; $x++) {
        $correctAnswer = $userAnswers[$x]; //2
        array_push($chosenAnswers, $options[$correctAnswer - 1]);
    }

    $answerString = implode(",", $chosenAnswers); // comma seperated string of answers
    $isCorrect = ($answerData['question_marks'] == $answerData['marks_obtained']) ? "1" : "0";
    $attempt_key = "" . $wp_user_id . $wp_course_id . $wp_quiz_id;
    $attempt_number = get_post_meta($wp_user_id, $attempt_key, true);

    $wpdb->insert($table_name, array(
        'user_answer_id' => NULL,
        'user_id' => $wp_user_id,
        'course_id' => $wp_course_id,
        'quiz_id' => $wp_quiz_id,
        'question_id' => $wp_question_id,
        'user_answer' => $answerString,
        'correct_answer' => $isCorrect,
        'attempt_number' => $attempt_number,
    ));
}

function enrol_users_from_csv($enrolData) {
    global $wpdb;
    // $userMGMLtoWP, $courseMGMLtoWP, $lessonMGMLtoWP, $selfAssessmentToUser, $questionMGMLtoWP;
    $table_name = 'wp_stm_lms_user_courses';
    // TODO: Hardcoding since we didn't make that lesson - $enrolData['current_lesson_id']
    // $wp_course_id = $courseMGMLtoWP[$enrolData['course_id']];

    $wp_course_id = get_from_post('stm-courses', 'mgml_course_id', $enrolData['course_id']);
    if (!isset($wp_course_id)) {
        error_log("No data for this course: ");
        return;
    }
    $wp_user_id = get_user_id('mgml_user_id', $enrolData['user_id']);
    if (!isset($wp_user_id)) {
        error_log("No data for this user: ");
        return;
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

    $students = get_post_meta($wp_course_id, 'current_students', true);
    update_post_meta($wp_course_id, 'current_students', $students + 1);
}

?>