<?php
	// --------------------------------------------------------------------------------------------
	// CREATE LESSONS SECTION
	// --------------------------------------------------------------------------------------------

	// Create Lesson Data
	require_once 'helpers.php';
	function create_lesson_from_csv($lessonData) {
		global $lessonToQuestionsMap, $sectionToLessonMap, $lessonMGMLtoWP;

		$wpdata['post_title'] = $lessonData['title'];
		$wpdata['post_status'] ='publish';
		if ($lessonData['lesson_type'] == 'quiz') {
			$wpdata['post_type'] = 'stm-quizzes';
			$wpdata['post_content'] = $lessonData['summary'];
		} else {
			//study material post meta
			$link = 'https://dev108.freewaydns.net/wp-content/uploads/course_materials/'.$lessonData['course_id'].'/'.$lessonData['attachment'];
			$file_content = '<a href="https:////dev108.freewaydns.net/wp-content/plugins/pdfjs-viewer-shortcode/pdfjs/web/viewer.php?file='.$link.'&amp;dButton=false&amp;pButton=true&amp;oButton=false&amp;sButton=true#zoom=auto&amp;pagemode=none" target="_blank" rel="noopener"><img src="https://dev108.freewaydns.net/wp-content/uploads/2023/02/button_open-pdf.png" alt="PDF icon" /></a>';
			// audio post meta
			$embedded_audio = '[embed]'.$lessonData['audio_url'].'[/embed]';
			$wpdata['post_type'] = 'stm-lessons';
			$wpdata['post_content'] = $file_content . $embedded_audio;
		}

		$lesson_post_id = wp_insert_post( $wpdata );

		$lessonMGMLtoWP[$lessonData['id']] = $lesson_post_id; //save MGML ID
		$sectionID = $lessonData['section_id']; //map section ID for course
		if (!array_key_exists($sectionID, $sectionToLessonMap)) {
			// TODO: replaced section_name with title here
			$sectionToLessonMap[$sectionID] = array("{$lessonData['title']}", "{$lesson_post_id}");
		} else {
			array_push($sectionToLessonMap[$sectionID], "{$lesson_post_id}");
		}

		if ($lessonData['lesson_type'] == 'quiz') {
			  update_post_meta($lesson_post_id, 'correct_answer', 'on');
			  update_post_meta($lesson_post_id, 'passing_grade', '0');
			  update_post_meta($lesson_post_id, 're_take_cut', '0');
			  update_post_meta($lesson_post_id, 'quiz_style', 'global');
			  $questionArray = $lessonToQuestionsMap[$lessonData['id']];
			  if (!empty($questionArray)) {
				  $questionString = implode(",", $questionArray);
				  update_post_meta($lesson_post_id, 'questions', $questionString);
			  } else {
				  echo "No questions available for Quiz <br>";
			  }
		} else {
			//video post meta
			update_post_meta($lesson_post_id, 'duration', $lessonData['duration']);
			update_post_meta($lesson_post_id, 'type', $lessonData['lesson_type']);
			$video_type = strtolower($lessonData['video_type']);
			update_post_meta($lesson_post_id, 'video_type', $video_type);
			update_post_meta($lesson_post_id, "lesson_{$video_type}_url", $lessonData['video_url']);
		}
	}
?>