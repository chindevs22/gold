<?php
	// --------------------------------------------------------------------------------------------
	// CREATE LESSONS SECTION
	// --------------------------------------------------------------------------------------------

	// Create Lesson Data
	require_once 'helpers.php';
	function create_lesson_from_csv($lessonData) {
		global $lessonToQuestionsMap, $sectionToLessonMap, $lessonMGMLtoWP;

		$questionArray = array();

		$wpdata['post_title'] = $lessonData['title'];
		$wpdata['post_status'] ='publish';
		if ($lessonData['lesson_type'] == 'quiz') {
			$wpdata['post_type'] = 'stm-quizzes';
			$wpdata['post_content'] = $lessonData['summary'];
			$questionArray = get_questions_for_quiz($lessonData['id']);
			if (empty($questionArray)) {
				error_log("No questions available for Quiz " . $lessonData['id'] . "  so not making <br> ");
				echo "No questions available for Quiz " . $lessonData['id'] . "  so not making <br> ";
				return;
			}
		} else {
			$file_content = '';
			$embedded_audio = '';
			//study material post meta
			if (isset($lessonData['attachment']) && $lessonData['attachment'] != "NULL") {
				$link = 'https://dev108.freewaydns.net/wp-content/uploads/course_materials/'.$lessonData['course_id'].'/'.$lessonData['attachment'];
				// TODO: edit this to be the PDF flipbook code
			// [3d-flip-book mode="fullscreen" pdf="https://dev108.freewaydns.net/wp-content/uploads/2023/04/shlokas.pdf"][/3d-flip-book]
			$file_content = '<a href="https:////dev108.freewaydns.net/wp-content/plugins/pdfjs-viewer-shortcode/pdfjs/web/viewer.php?file='.$link.'&amp;dButton=false&amp;pButton=true&amp;oButton=false&amp;sButton=true#zoom=auto&amp;pagemode=none" target="_blank" rel="noopener"><img src="https://dev108.freewaydns.net/wp-content/uploads/2023/02/button_open-pdf.png" alt="PDF icon" /></a>';
			}

			if (isset($lessonData['audio_url']) && $lessonData['audio_url'] != "NULL") {
				// audio post meta
				$embedded_audio = '[embed]'.$lessonData['audio_url'].'[/embed]';
			}

			$wpdata['post_type'] = 'stm-lessons';
			$wpdata['post_content'] = $file_content . $embedded_audio;
		}

		$lesson_post_id = wp_insert_post( $wpdata );

		update_post_meta($lesson_post_id, 'mgml_lesson_id', $lessonData['id']);
		update_post_meta($lesson_post_id, 'mgml_section_id', $lessonData['section_id']);
		update_post_meta($lesson_post_id, 'mgml_section_name', $lessonData['section_name']);
        update_post_meta($lesson_post_id, 'type', 'text');

		$sectionID = $lessonData['section_id']; //map section ID for course

		if ($lessonData['lesson_type'] == 'quiz') {
			  update_post_meta($lesson_post_id, 'correct_answer', 'on');
			  update_post_meta($lesson_post_id, 'passing_grade', '0');
			  update_post_meta($lesson_post_id, 're_take_cut', '0');
			  update_post_meta($lesson_post_id, 'quiz_style', 'global');

			  //assign questions
// 			  $questionArray = get_questions_for_quiz($lessonData['id']);
			  error_log(print_r($questionArray, true));
//			  $questionArray = $lessonToQuestionsMap[$lessonData['id']];
			  if (!empty($questionArray)) {
				  $questionString = implode(",", $questionArray);
				  error_log(print_r($questionString, true));
				  update_post_meta($lesson_post_id, 'questions', $questionString);
			  } else {
				  echo "Second No questions available for Quiz " . $lessonData['id'] . "  <br> ";
			  }
		} else if ($lessonData['lesson_type'] == 'video') {
			//video post meta
			if (!empty($lessonData['duration']) && $lessonData['duration'] != "NULL") {
                update_post_meta($lesson_post_id, 'duration', $lessonData['duration']);
			}

			update_post_meta($lesson_post_id, 'type', $lessonData['lesson_type']);
			$video_type = strtolower($lessonData['video_type']);
			update_post_meta($lesson_post_id, 'video_type', $video_type);
			update_post_meta($lesson_post_id, "lesson_{$video_type}_url", $lessonData['video_url']);
		}
	}

	function get_questions_for_quiz($quiz_id) {
        $args = array(
            'post_type' => 'stm-questions',
            'meta_query' => array(
                   array(
                       'key' => 'mgml_quiz_id',
                       'value' => $quiz_id,
                       'compare' => '='
                   )
               )
        );

        $the_query = new WP_Query( $args );

        $post_ids = array(); // create an empty array to store post IDs

        if ( $the_query->have_posts() ) {
            while ( $the_query->have_posts() ) {
                $the_query->the_post();
                $post_ids[] = get_the_ID(); // add post ID to the array
            }
            wp_reset_postdata();
        }

        return $post_ids;

	}
?>