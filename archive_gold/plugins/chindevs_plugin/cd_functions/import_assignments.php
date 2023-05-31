<?php
	// --------------------------------------------------------------------------------------------
	// CREATE ASSIGNMENTS SECTION
	// --------------------------------------------------------------------------------------------
	require_once 'helpers.php';
	function create_assignment_from_csv($assignmentData) {

         $ids = explode( '.', $assignmentData['id']); //10 , 51
        // get existing assignments in this quiz
        $assignment_post_id = get_from_post('stm-assignments', 'mgml_assignment_quiz_id', $ids[0]); //10

        if (empty($assignment_post_id)) {
            $wpdata['post_title'] = $assignmentData['title'];
            $wpdata['post_content'] = '1) ' . $assignmentData['summary'];
            $wpdata['post_status'] ='publish';
            $wpdata['post_type'] = 'stm-assignments';
            $assignment_post_id = wp_insert_post( $wpdata );
			error_log("assignment post id for new assignment");
			error_log(print_r($assignment_post_id, true));
            //Assignment Metadata Fields
            update_post_meta($assignment_post_id, 'assignment_tries', 100);
            update_post_meta($assignment_post_id, 'mgml_assignment_id', $assignmentData['id']); // quizID.questionID
            update_post_meta($assignment_post_id, 'mgml_assignment_quiz_id', $ids[0]); //10
            update_post_meta($assignment_post_id, 'mgml_assignment_question_id', array($ids[1])); //[20, 51]
            update_post_meta($assignment_post_id, 'mgml_section_id', $assignmentData['section_id']);
            update_post_meta($assignment_post_id, 'mgml_section_name', $assignmentData['section_name']);
        } else {
			error_log("assignment post id for old assignment");
			error_log(print_r($assignment_post_id, true));
            // Append Next "question" to existing assignment
            $questions = get_post_meta($assignment_post_id, 'mgml_assignment_question_id', true); //20, 51
            $numQuestions = count($questions) + 1;

			//Update post content
			$current_post = get_post($assignment_post_id);
		 	error_log(print_r($current_post, true));
			$content = $current_post->post_content;
			$content .= '<br><br>' . $numQuestions . ') ' . $assignmentData['summary'];
			$updated_post = array(
				'ID'           => $current_post->ID,
				'post_content' => $content
			);
			wp_update_post($updated_post);
            array_push($questions, $ids[1]);
            update_post_meta($assignment_post_id, 'mgml_assignment_question_id', $questions);
        }

        // Parse Options for Matching, Single/Multiple if Existing
        $matching_data = $assignmentData['options'];
        if (!empty($matching_data) && $matching_data != "NULL") {
			 error_log("matching data");
            $summaryString = parse_matching_options_to_string($matching_data);
			 error_log("summary string");
			error_log(print_r($summaryString, true));

            //Update post content
            $current_post = get_post($assignment_post_id);
            error_log(print_r($current_post, true));
            $content = $current_post->post_content;
            $content .= $summaryString;
            $updated_post = array(
                'ID'           => $current_post->ID,
                'post_content' => $content
            );
            wp_update_post($updated_post);

            update_post_meta($assignment_post_id, 'mgml_postal_answers', $assignmentData['correct_answers']);
        }
	}

    function parse_matching_options_to_string($matching_data) {
        $questionKey  = '"questions":';
        $optionKey = '"options":';
        $qPos = stripos($matching_data, $questionKey);
        $oPos = stripos($matching_data, $optionKey);
        $counter = 1;


        // Single / Multi Choice
        if ($qPos == false) {
            $choiceOptions = create_array_from_string($matching_data, ',');
            $newMatchingString = 'Choices <br><br>';
            foreach($choiceOptions as $c) {
                 $c = trim($c, '"');
                $newMatchingString .=  $counter++ . '. ' . $c . '<br>';
            }
            return html_entity_decode($newMatchingString);
        }

        $questionString = substr($matching_data, $qPos+12, $oPos-14);
        $optionString = substr($matching_data, $oPos+10, -1);

        $questions = create_array_from_string($questionString, '","');
        $options = create_array_from_string($optionString, '","');

        $newMatchingString = 'Questions <br><br>';
        foreach($questions as $q) {
            $newMatchingString .= 'Q' . $counter++ . '. ' . $q . '<br>';
        }
        $counter = 1;
        $newMatchingString .= '<br> Options <br><br>';
        foreach($options as $o) {
            $newMatchingString .=  $counter++ . '. ' . $o . '<br>';
        }
        return html_entity_decode($newMatchingString);
    }
?>