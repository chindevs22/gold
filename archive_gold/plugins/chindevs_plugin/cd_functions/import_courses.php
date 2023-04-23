<?php
	// --------------------------------------------------------------------------------------------
	// CREATE COURSE SECTION
	// --------------------------------------------------------------------------------------------

	// create the course
	require_once 'helpers.php';
	function create_course_from_csv($courseData) {
		global $courseMGMLtoWP, $sectionToLessonMap;

        $faq_description;
        $new_desc = html_entity_decode($courseData['description']);
        $faq_flag = false;
        if (strpos($new_desc, "Frequently Asked Questions")) {
			echo "<br> has faq <br>";
			echo "course name <br>";
			echo $courseData['title'];
            $faq_flag = true;
            $desc_arr = explode("Frequently Asked Questions", $courseData['description']);
			$orig_description = html_entity_decode($desc_arr[0]);
			$new_desc = substr($orig_description, 0, strrpos($orig_description, "<br>") );
//             $orig_description = $desc_arr[0];
            $faq_description = $desc_arr[1];
        }

		echo "<br> the description being sent";
		print_r($new_desc);
		echo "<br>";

		// Create array of Course info from CSV data
		$wpdata['post_title'] = $courseData['title'];
		$wpdata['post_content'] = $new_desc;
		$wpdata['post_excerpt'] = $courseData['short_description'];
		$wpdata['post_status'] ='publish';
		$wpdata['post_type'] = 'stm-courses';
		$course_post_id = wp_insert_post( $wpdata );

		$curriculum_string = "";
		$combinedArray = array();
		$sectionString = $courseData['section'];
		$sectionArray = create_array_from_string($sectionString, ",");

		foreach ($sectionArray as $sectionID) {
			if ($sectionToLessonMap[$sectionID]) {
				$combinedArray = array_merge($combinedArray, $sectionToLessonMap[$sectionID]);
			}
		}

		$curriculum_string = implode(",", $combinedArray);
		$courseMGMLtoWP[$courseData['id']] = $course_post_id;

        if(empty($curriculum_string) || strlen($curriculum_string) == 0) {
            $curriculum_string = "Sample Section, 5552";
        }

		$price = $courseData['price_usd'];
		update_post_meta($course_post_id, 'price', $price);
		update_post_meta($course_post_id, 'curriculum', $curriculum_string);
		update_post_meta($course_post_id, 'level', $courseData['level']);
		update_post_meta($course_post_id, 'current_students', 0);
// 		if (!empty($price) || $price != 0) {
// 			update_post_meta($course_post_id, 'shareware', 'on');
// 		}
		//append faq
		if($faq_flag) {
		    $faq_string = build_faq($faq_description);
			echo "this is the faq string ";
			echo "<br>" . $faq_string . "<br>";
		    update_post_meta($course_post_id, 'faq', $faq_string);
			update_post_meta($course_post_id, '_wp_page_template', 'default');
		}

		//TODO: uncomment this when we actually have images
		//add_course_image($course_post_id, $courseData['id']); // adds the image to the course

		// this appends the category as a term with the taxonomy relationship to the course ID

		$category = $courseData['parent_category'];

		if ($category == 'Satsang Webinars' || $category == 'Text-based Webinars') {
			$category_arr = array("Study Format", $category);
		} else {
			$category_arr = array("Subject Matter", $category);
		}
		wp_set_object_terms($course_post_id, $category_arr, 'stm_lms_course_taxonomy', $append = true );
	}

	// build faq
    function build_faq($faq) {
        $faq_string = "[";
        $qna = explode("panel-title", $faq);

        foreach($qna as $qa) {
            $arr = explode("panel-body", $qa);
            $question = trim(substr(strip_tags(html_entity_decode($arr[0])),3));
            $answer = trim(substr(strip_tags(html_entity_decode($arr[1])),3));

            if (empty($question) || empty($answer)) {
            	continue;
            }
            $faq_string .= '{"question":"'.$question.'","answer":"'.$answer.'"},';
        }
        $faq_string = trim($faq_string, ",") . "]";
    	return $faq_string;
    }

	// add course image
	function add_course_image($course_post_id, $course_id) {
		$upload_dir = wp_upload_dir();
		$upload_path = "course_materials/{$course_id}/thumbnail.jpg";
		$filename = "thumbnail.jpg";
		$wp_filetype = wp_check_filetype(basename($filename), null );
		$attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title' => sanitize_file_name($filename),
			'post_content' => '',
			'post_status' => 'inherit'
		);

		$attachment_id = wp_insert_attachment( $attachment, $upload_path, $course_post_id );
		if ( ! is_wp_error( $attachment_id ) ) {
			require_once(ABSPATH . 'wp-admin/includes/image.php');
			$attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload_path );
			wp_update_attachment_metadata( $attachment_id, $attachment_data );
			set_post_thumbnail( $course_post_id, $attachment_id );
		}
	}
?>