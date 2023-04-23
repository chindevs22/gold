<?php
	// --------------------------------------------------------------------------------------------
	// CREATE QUESTIONS SECTION
	// --------------------------------------------------------------------------------------------
	require_once 'helpers.php';
	function create_question_from_csv($questionData) {
		global $lessonToQuestionsMap, $questionMGMLtoWP, $wpQuestionsToAnswers;

		$wpdata['post_title'] = $questionData['title'];
		$wpdata['post_status'] ='publish';
		$wpdata['post_type'] = 'stm-questions';
		$question_post_id = wp_insert_post( $wpdata );
		$quiz_id = $questionData['quiz_id'];

		$questionMGMLtoWP[$questionData['id']] = $question_post_id; //map MGML question ID

		// MAP question ID to quiz
		if (!array_key_exists($quiz_id, $lessonToQuestionsMap)) {
			$lessonToQuestionsMap[$quiz_id] = array($question_post_id);
		} else {
			array_push($lessonToQuestionsMap[$quiz_id], $question_post_id);
		}

		// add metadata for question
		if ($questionData['type'] == 'multiple_choice') {
			update_post_meta($question_post_id, 'type', 'multi_choice');
		} elseif ($questionData['type'] == 'matching')  {
			update_post_meta($question_post_id, 'type', 'item_match');
		} else {
		   update_post_meta($question_post_id, 'type', $questionData['type']);
		}

		$answers = array();
		if ($questionData['type'] != 'matching')  {
			// if not matching
			$count = 1;
			$options = create_array_from_string($questionData['options'], '","');
			//print_r($options);
			$isCorrect = $questionData['correct_answers'];
			foreach ($options as $option) {
				$option = trim($option, "\"");
				$optionArray["text"] = $option;
				$optionArray["isTrue"] = str_contains($isCorrect, $count++) ? "1" : "0";
				array_push($answers, $optionArray);
			}
		}
		else {
			$matching_data = $questionData['options'];
			$questionKey  = '"questions":';
			$optionKey = '"options":';
			$qPos = stripos($matching_data, $questionKey);
			$oPos = stripos($matching_data, $optionKey);
			$questionString = substr($matching_data, $qPos+12, $oPos-14);
			$optionString = substr($matching_data, $oPos+10, -1);

			$questions = create_array_from_string($questionString, '","');
			$options = create_array_from_string($optionString, '","');

			$arrLength = count($questions);
			$correctAnswers = create_array_from_string($questionData['correct_answers'], '","');

			for($x = 0; $x < $arrLength; $x++) {
				$correctAnswer = $correctAnswers[$x];
				$optionArray["question"] = $questions[$x];
				$optionArray["text"] = $options[$correctAnswer - 1];
				$optionArray["isTrue"] = 0;
				array_push($answers, $optionArray);
			}
		}
		// map WP qusetion to WP Answers List
		$wpQuestionsToAnswers[$question_post_id] = $options;
		update_post_meta($question_post_id, 'answers', $answers);
	}
?>