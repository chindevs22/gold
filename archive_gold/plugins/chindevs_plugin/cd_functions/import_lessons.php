<?php
	// --------------------------------------------------------------------------------------------
	// CREATE LESSONS SECTION
	// --------------------------------------------------------------------------------------------

	// Create Lesson Data
	require_once 'helpers.php';
	function create_lesson_from_csv($lessonData) {
		$questionArray = array();

		$wpdata['post_title'] = $lessonData['title'];
		$wpdata['post_status'] ='publish';
		if ($lessonData['lesson_type'] == 'quiz') {
			$wpdata['post_type'] = 'stm-quizzes';
			$wpdata['post_content'] = $lessonData['summary'];
			$questionArray = get_questions_for_quiz($lessonData['id']);
			if (empty($questionArray)) {
				error_log("ERROR: No questions available for Quiz " . $lessonData['id'] . "  so not making <br> ");
				echo "No questions available for Quiz " . $lessonData['id'] . "  so not making <br> ";
				return;
			}
		} else {
			$file_content = '';
			$embedded_audio = '';
            if (isset($linkingData['attachment']) && $linkingData['attachment'] != "NULL") {
                $link = '/wp-content/uploads/cd_media/lesson_materials/course_'.$linkingData['mgml_course_id'].'/'.$linkingData['attachment'];
                error_log("Crafted PDF Link path: " . $link);
                $file_content = '<a class="elementor-button elementor-button-link elementor-size-md study_material" title="Study Material" href="/wp-content/plugins/pdfjs-viewer-shortcode/pdfjs/web/viewer.php?file='.$link.'" target="_blank" rel="noopener"><img class="pdf_img" src="/wp-content/uploads/2023/10/pdf.png" width="24" height="24" /> Study Material</a></p>';
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

			  error_log(print_r($questionArray, true));
			  if (!empty($questionArray)) {
				  $questionString = implode(",", $questionArray);
				  error_log(print_r($questionString, true));
				  update_post_meta($lesson_post_id, 'questions', $questionString);
			  } else {
				  echo "ERROR: Second No questions available for Quiz " . $lessonData['id'] . "  <br> ";
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
			'post_type'      => 'stm-questions',
			'meta_key'       => 'mgml_quiz_id',
			'meta_value'     => $quiz_id,
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'posts_per_page' => -1, // Retrieve all matching posts
		);

        $query = new WP_Query( $args );

		$posts = wp_list_pluck( $query->posts, 'ID' );
		return $posts;

	}
?>