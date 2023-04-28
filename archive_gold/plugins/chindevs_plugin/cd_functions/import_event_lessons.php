<?php
	// --------------------------------------------------------------------------------------------
	// CREATE EVENT LESSONS
	// --------------------------------------------------------------------------------------------

	// create the course
	require_once 'helpers.php';
	function create_event_lesson_from_csv($eventLessonData) {
         global $lessonToQuestionsMap, $sectionToLessonMap, $lessonMGMLtoWP;

        $wpdata['post_title'] = $eventLessonData['title'];  // Ensure all content doesn't have any special characters
        $wpdata['post_status'] ='publish';
        if ($eventLessonData['lesson_type'] == 'quiz') {
            $wpdata['post_type'] = 'stm-quizzes';
            $wpdata['post_content'] = $eventLessonData['summary'];
        } else {
            //study material post meta
//             $link = 'https://dev108.freewaydns.net/wp-content/uploads/course_materials/'.$eventLessonData['event_id'].'/'.$eventLessonData['attachment'];
//             // TODO: edit this to be the PDF flipbook code
//             // [3d-flip-book mode="fullscreen" pdf="https://dev108.freewaydns.net/wp-content/uploads/2023/04/shlokas.pdf"][/3d-flip-book]
//             $file_content = '<a href="https:////dev108.freewaydns.net/wp-content/plugins/pdfjs-viewer-shortcode/pdfjs/web/viewer.php?file='.$link.'&amp;dButton=false&amp;pButton=true&amp;oButton=false&amp;sButton=true#zoom=auto&amp;pagemode=none" target="_blank" rel="noopener"><img src="https://dev108.freewaydns.net/wp-content/uploads/2023/02/button_open-pdf.png" alt="PDF icon" /></a>';
            // audio post meta
            $embedded_audio = '[embed]'.$eventLessonData['audio_url'].'[/embed]';
            $wpdata['post_type'] = 'stm-lessons';
            $wpdata['post_content'] = $embedded_audio; //removed file_content from this since we dont have PDFs
        }

        $lesson_post_id = wp_insert_post( $wpdata );

        $lessonMGMLtoWP[$eventLessonData['id']] = $lesson_post_id; //save MGML ID
        $sectionID = $eventLessonData['section_id']; //map section ID for course
        if (!array_key_exists($sectionID, $sectionToLessonMap)) {
            // TODO: replaced section_name with title here
            $sectionToLessonMap[$sectionID] = array("{$eventLessonData['section_name']}", "{$lesson_post_id}");
        } else {
            array_push($sectionToLessonMap[$sectionID], "{$lesson_post_id}");
        }

        if ($eventLessonData['lesson_type'] == 'quiz') {
              update_post_meta($lesson_post_id, 'correct_answer', 'on');
              update_post_meta($lesson_post_id, 'passing_grade', '0');
              update_post_meta($lesson_post_id, 're_take_cut', '0');
              update_post_meta($lesson_post_id, 'quiz_style', 'global');
              $questionArray = $lessonToQuestionsMap[$eventLessonData['id']];
              if (!empty($questionArray)) {
                  $questionString = implode(",", $questionArray);
                  update_post_meta($lesson_post_id, 'questions', $questionString);
              } else {
                  echo "No questions available for Quiz <br>";
              }
        } else {
            //video post meta
            update_post_meta($lesson_post_id, 'duration', $eventLessonData['duration']);
            update_post_meta($lesson_post_id, 'type', $eventLessonData['lesson_type']);
            $video_type = strtolower($eventLessonData['video_type']);
            update_post_meta($lesson_post_id, 'video_type', $video_type);
            update_post_meta($lesson_post_id, "lesson_{$video_type}_url", $eventLessonData['video_url']);
        }
	}
?>