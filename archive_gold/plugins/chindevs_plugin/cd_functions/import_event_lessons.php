<?php
	// --------------------------------------------------------------------------------------------
	// CREATE SM LESSONS SECTION
	// --------------------------------------------------------------------------------------------

	// Create Lesson Data
	require_once 'helpers.php';
	function create_event_lesson_from_csv($eventLessonData) {

		$wpdata['post_title'] = $eventLessonData['title'];
		$wpdata['post_status'] ='publish';

        $embedded_audio = '';
        if (isset($eventLessonData['audio_url']) && $eventLessonData['audio_url'] != "NULL") {
            $embedded_audio = '[embed]'.$eventLessonData['audio_url'].'[/embed]';
        }

        $file_content = '';
        if (isset($eventLessonData['attachment']) && $eventLessonData['attachment'] != "NULL") {
            $link = 'https://dev108.freewaydns.net/wp-content/uploads/course_materials/'.$eventLessonData['event_id'].'/'.$eventLessonData['attachment'];
            $file_content = '<a href="https:////dev108.freewaydns.net/wp-content/plugins/pdfjs-viewer-shortcode/pdfjs/web/viewer.php?file='.$link.'&amp;dButton=false&amp;pButton=true&amp;oButton=false&amp;sButton=true#zoom=auto&amp;pagemode=none" target="_blank" rel="noopener"><img src="https://dev108.freewaydns.net/wp-content/uploads/2023/02/button_open-pdf.png" alt="PDF icon" /></a>';
        }

        if (isset($lessonData['audio_url']) && $lessonData['audio_url'] != "NULL") {
            // audio post meta
            $embedded_audio = '[embed]'.$lessonData['audio_url'].'[/embed]';
        }

        $wpdata['post_type'] = 'stm-lessons';
        $wpdata['post_content'] = $file_content . $embedded_audio;

		$lesson_post_id = wp_insert_post( $wpdata );

		update_post_meta($lesson_post_id, 'mgml_lesson_id', $eventLessonData['id']);
		update_post_meta($lesson_post_id, 'mgml_event_id', $eventLessonData['event_id']); //change to webinar

		update_post_meta($lesson_post_id, 'lite_type', 'event'); //change to webinar

        //video post meta

		if (isset($eventLessonData['duration']) && $eventLessonData['duration'] != "NULL") {
			 update_post_meta($lesson_post_id, 'duration', $eventLessonData['duration']);
		}
		if ($eventLessonData['lesson_type'] == 'video') {
			update_post_meta($lesson_post_id, 'type', 'video');
			$video_type = strtolower($eventLessonData['video_type']);
			update_post_meta($lesson_post_id, 'video_type', $video_type);
			update_post_meta($lesson_post_id, "lesson_{$video_type}_url", $eventLessonData['video_url']);
		};

    }
?>