<?php
	// --------------------------------------------------------------------------------------------
	// CREATE SM LESSONS SECTION
	// --------------------------------------------------------------------------------------------

	// Create Lesson Data
	require_once 'helpers.php';
	function create_sm_lesson_from_csv($smLessonData) {

		$wpdata['post_title'] = $smLessonData['title'];
		$wpdata['post_status'] ='publish';
        $embedded_audio = '';
        if (isset($smLessonData['audio_url']) && $smLessonData['audio_url'] != "NULL") {
            $embedded_audio = '[embed]'.$smLessonData['audio_url'].'[/embed]';
        }
        $wpdata['post_type'] = 'stm-lessons';
        $wpdata['post_content'] =  $embedded_audio;
		$lesson_post_id = wp_insert_post( $wpdata );

		update_post_meta($lesson_post_id, 'mgml_lesson_id', $smLessonData['id']);
		update_post_meta($lesson_post_id, 'mgml_sm_id', $smLessonData['course_id']); //
		update_post_meta($lesson_post_id, 'mgml_section_name', $smLessonData['title']);
		update_post_meta($lesson_post_id, 'lite_type', 'shravana_mangalam');

        //video post meta

		if (isset($smLessonData['duration']) && $smLessonData['duration'] != "NULL") {
			 update_post_meta($lesson_post_id, 'duration', $smLessonData['duration']);
		}
		if ($smLessonData['lesson_type'] == 'audio_video') {
			update_post_meta($lesson_post_id, 'type', 'video');
			$video_type = strtolower($smLessonData['video_type']);
			update_post_meta($lesson_post_id, 'video_type', $video_type);
			update_post_meta($lesson_post_id, "lesson_{$video_type}_url", $smLessonData['video_url']);
		};

    }
?>