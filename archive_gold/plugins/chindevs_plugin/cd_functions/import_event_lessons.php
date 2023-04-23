<?php
	// --------------------------------------------------------------------------------------------
	// CREATE EVENT LESSONS
	// --------------------------------------------------------------------------------------------

	// create the course
	require_once 'helpers.php';
	function create_event_lesson_from_csv($eventData) {


		// Create array of Course info from CSV data
		$wpdata['post_title'] = $eventData['title'];
		$wpdata['post_content'] = $new_desc;
		$wpdata['post_excerpt'] = $eventData['short_description'];
		$wpdata['post_status'] ='publish';
		$wpdata['post_type'] = 'stm-courses';
		$course_post_id = wp_insert_post( $wpdata );
	}
?>