<?php
	// --------------------------------------------------------------------------------------------
	// CREATE QUESTIONS SECTION
	// --------------------------------------------------------------------------------------------
	require_once 'helpers.php';
	function create_question_from_csv($questionData) {
		echo "<br> inside create question <br>";

		// TODO: we currently cannot handle "descriptive" type -> should actually be an assignment or fill gap?
		if ($questionData['type'] == 'descriptive') {
			return;
		}

		// Not handling $wpQuestionsToAnswers
		global $lessonToQuestionsMap, $questionMGMLtoWP, $wpQuestionsToAnswers;


		$wpdata['post_title'] = $questionData['title'];
		$wpdata['post_content'] = $questionData['instruction'];
		$wpdata['post_status'] ='publish';
		$wpdata['post_type'] = 'stm-questions';
		$question_post_id = wp_insert_post( $wpdata );


		echo "QUESTION ID: " . $questionData['id'] . "POST ID:  " . $question_post_id;
		error_log("QUESTION ID:  ");
		error_log($questionData['id']);
		error_log("POST ID:  ");
		error_log($question_post_id);

		$quiz_id = $questionData['quiz_id'];

		// Create metadata fields for MGML question and MGML quiz
		update_post_meta($question_post_id, 'mgml_question_id', $questionData['id']);
		update_post_meta($question_post_id, 'mgml_quiz_id', $quiz_id);
// 		$questionMGMLtoWP[$questionData['id']] = $question_post_id; //map MGML question ID
		// MAP question ID to quiz
// 		if (!array_key_exists($quiz_id, $lessonToQuestionsMap)) {
// 			$lessonToQuestionsMap[$quiz_id] = array($question_post_id);
// 		} else {
// 			array_push($lessonToQuestionsMap[$quiz_id], $question_post_id);
// 		}

		// add metadata for question
		if ($questionData['type'] == 'multiple_choice') {
			update_post_meta($question_post_id, 'type', 'multi_choice');
		} elseif ($questionData['type'] == 'matching')  {
			update_post_meta($question_post_id, 'type', 'item_match');
		} else {
		   update_post_meta($question_post_id, 'type', $questionData['type']);
		}

		// Add points value ** Need to check if it shows at 0 or 1 or as a string????
        if (!isset($questionData['marks']) || $questionData['marks'] == 0) {
            echo "no points";
        }
        else {
            update_post_meta($question_post_id, 'slms_points', $questionData['marks']);
        }


		$answers = array();

		$isCorrect = $questionData['correct_answers'];

		if ($questionData['type'] != 'matching')  {
			// if not matching
			$count = 1;
			$options = create_array_from_string($questionData['options'], '","');

			foreach ($options as $option) {
				$option = trim($option, "\"");
				$saniOption = html_entity_decode($option, ENT_COMPAT, 'UTF-8');
				$optionArray["text"] = $saniOption;
				$optionArray["isTrue"] = str_contains($isCorrect, $count++) ? 1 : 0;
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
			$correctAnswers = create_array_from_string($isCorrect, '","');

			for($x = 0; $x < $arrLength; $x++) {
				$correctAnswer = $correctAnswers[$x];
				$optionArray["question"] = html_entity_decode($questions[$x], ENT_COMPAT, 'UTF-8');
				$optionArray["text"] =  html_entity_decode($options[$correctAnswer - 1], ENT_COMPAT, 'UTF-8');
				$optionArray["isTrue"] = 0;
				array_push($answers, $optionArray);
			}
		}
		// map WP qusetion to WP Answers List
//		$wpQuestionsToAnswers[$question_post_id] = $options;
		update_post_meta($question_post_id, 'mgml_answer_options', $options);
		update_post_meta($question_post_id, 'answers', $answers);
	}
?>