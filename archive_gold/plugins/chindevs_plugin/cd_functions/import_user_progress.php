<?php
	// --------------------------------------------------------------------------------------------
	// USER PROGRESS SECTION
	// --------------------------------------------------------------------------------------------
	require_once 'helpers.php';
	function progress_users_quiz_from_csv($progressData) {
		global $wpdb, $userMGMLtoWP, $courseMGMLtoWP, $selfAssessmentToUser, $lessonMGMLtoWP, $attemptNumberMap;
		$mgml_user_id = $progressData['user_id'];
		$wp_user_id = $userMGMLtoWP[$mgml_user_id];

		$table_name = 'wp_stm_lms_user_quizzes';

	//     $mgml_user_id = $progressData['user_id'];
	//     $wp_user_id = $userMGMLtoWP[$mgml_user_id];
		$wp_quiz_id = $lessonMGMLtoWP[$progressData['quiz_id']];
		$wp_course_id =  $courseMGMLtoWP[$progressData['course_id']];
		$attempt_key = "" . $wp_user_id . $wp_course_id . $wp_quiz_id;
		$attempt_number = $progressData['attempt'];
		$attemptNumberMap[$attempt_key] = $attempt_number;


	  $grade = $progressData['marks']/$progressData['quiz_marks'] * 100; //aka progress

	  // map self assessment id to user id
	  $selfAssessmentToUser[$progressData['id']] =  $mgml_user_id;

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
		$isLeft = false;
		$isRight = false;
		$lessonsCompleted = array();
		$indexLeft = $quizIndex - 1;
		$indexRight = $quizIndex + 1;
		while (!$isLeft || !$isRight) {
		   if ($indexRight == $arrLength || intval($ca[$indexRight]) == 0) {
				$isRight = true;
		   } else {
				array_push($lessonsCompleted, $ca[$indexRight++]);
		   }
		   if(intval($ca[$indexLeft]) == 0) {
				$isLeft = true;
		   } else {
			  array_push($lessonsCompleted, $ca[$indexLeft--]);
		   }
		}

		echo "LESSON COMPLETED <br>";
		// Insert Each Completed Lesson Based on Completed Quiz
		foreach($lessonsCompleted as $lesson_id) {
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
		global $wpdb, $userMGMLtoWP, $attemptNumberMap, $courseMGMLtoWP, $lessonMGMLtoWP, $selfAssessmentToUser, $questionMGMLtoWP, $wpQuestionsToAnswers;
		$table_name = 'wp_stm_lms_user_answers';

		// get IDS
		$sa_id = $answerData['self_assessment_id'];
		$mgml_user_id = $selfAssessmentToUser[$sa_id];
		$wp_user_id = $userMGMLtoWP[$mgml_user_id];
		$wp_course_id = $courseMGMLtoWP[$answerData['course_id']];
		$wp_quiz_id = $lessonMGMLtoWP[$answerData['quiz_id']];

		// TODO CHECK the WP question ID should be part of the $lessonToQuestionsMap of $answerData['quiz_id']

		$wp_question_id = $questionMGMLtoWP[$answerData['question_id']];
		$options = $wpQuestionsToAnswers[$wp_question_id];
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
		$wpdb->insert($table_name, array(
			'user_answer_id' => NULL,
			'user_id' => $wp_user_id,
			'course_id' => $wp_course_id,
			'quiz_id' => $lessonMGMLtoWP[$answerData['quiz_id']],
			'question_id' => $wp_question_id,
			'user_answer' => $answerString,
			'correct_answer' => $isCorrect,
			'attempt_number' => $attemptNumberMap[$attempt_key],
		));
	}

	function enrol_users_from_csv($enrolData) {
		global $wpdb, $userMGMLtoWP, $courseMGMLtoWP, $lessonMGMLtoWP, $selfAssessmentToUser, $questionMGMLtoWP;
		$table_name = 'wp_stm_lms_user_courses';
		// TODO: Hardcoding since we didn't make that lesson - $enrolData['current_lesson_id']
		$wp_course_id = $courseMGMLtoWP[$enrolData['course_id']];
		$wpdb->insert($table_name, array(
			'user_course_id' => NULL,
			'user_id' => $userMGMLtoWP[$enrolData['user_id']],
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