<?php


//Helper Function to create an Array from a String
function create_array_from_string($sectionString, $delimiter) {
	$sectionString = trim($sectionString, '[');
    $sectionString = trim($sectionString, ']');
    $sectionString = trim($sectionString, ' ');
	$sectionString = trim($sectionString, '"');
    $sectionArray = explode($delimiter, $sectionString);
    return $sectionArray;
}

// Helper Function for Building Attribute Array for Publications
function build_attr_array($attr, $count) {
	$key = trim($attr['key'], " \x3A");
	$value = trim($attr['value'], " \x3A");
    $data_arr = array();
    $data_arr['name'] = $key;
    $data_arr['value'] = $value;
    $data_arr['position'] = $count;
    $data_arr['is_visible'] = 1;
    $data_arr['is_variation'] = 0;
    $data_arr['is_taxonomy'] = 0;
    return $data_arr;
}


function progress_user_lessons($wp_course_id, $wp_quiz_id, $wp_user_id) {
    global $wpdb;
	error_log("progressing user lessons");
    $curriculum_string = get_post_meta($wp_course_id, 'curriculum', true);
    $ca = create_array_from_string($curriculum_string, ',');
    $arrLength = count($ca);
    $quizIndex = 0;
    for($x = 0; $x < $arrLength; $x++) {
        if ($wp_quiz_id == $ca[$x]) {
            $quizIndex = $x;
            break;
        }
    }
    error_log("Course ID: " . $wp_course_id . " Quiz ID: " . $wp_quiz_id . " Index found: " . $quizIndex);

    $isLeft = false;
    $isRight = false;
    $lessonsCompleted = array();
    $indexLeft = $quizIndex - 1;
    $indexRight = $quizIndex + 1;
    // Go through curriculum array searching for the Lessons surrounding the quiz
    // When you hit a Section Name (intval will be false) or the ends of the array stop.
    // TODO: This code cant handle 2 quizzes in a section
    // Solution: Go Left Only, Check if I've hit a Quiz or Section
    while (!$isLeft) {
        if( $indexLeft < 0 || intval($ca[$indexLeft]) == 0 || 'stm-quizzes' === get_post_type( $ca[$indexLeft] ) ) {
            $isLeft = true;
        } else {
            array_push($lessonsCompleted, $ca[$indexLeft--]);
        }
    }

    echo "COMPLETING LESSONS FOR QUIZ <br>";
    error_log("# of LESSONS COMPLETED : " . count($lessonsCompleted));
    // Insert Each Completed Lesson Based on Completed Quiz
    foreach($lessonsCompleted as $lesson_id) {
        echo "lesson completed: " . $lesson_id . " <br>";
        $table_name = 'wp_stm_lms_user_lessons';
        $wpdb->insert($table_name, array(
            'user_lesson_id' => NULL,
            'user_id' => $wp_user_id,
            'course_id' => $wp_course_id,
            'lesson_id' => $lesson_id
        ));
    }
}


// Builds FAQ Block from a string
function build_faq($faq) {
    $faq_string = "[";
    $qna = explode("panel-title", $faq);

    foreach($qna as $qa) {
        $arr = explode("panel-body", $qa);
// 		error_log("faq arr");
// 		error_log(print_r($arr, true));
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


function replace_section_2($original_string, $start, $end, $replacement) {
	$startPosition = strpos($original_string, $start);
	$endPosition = strrpos($original_string, $end);

	if ($startPosition && $endPosition) {
		$startTagPosition = strrpos(substr($original_string, 0, $startPosition), '<p');
		$endTagPosition = strpos($original_string, '</p>', $endPosition) + 4;
		$modifiedText = substr_replace($original_string, $replacement, $startTagPosition, ($endTagPosition - $startTagPosition));
		return $modifiedText;
	}
	return $original_string;
}
// Original String = full text
// Start = starting phrase to look for
// Ending phrase to look for
// Replacement = shortcoded string '[shortcode]'
function replace_section($original_string, $start, $end, $replacement) {
    $start_pos = strrpos($original_string, $start);
    $end_pos = strpos($original_string, $end, $start_pos + strlen($start));

    if ($start_pos !== false && $end_pos !== false && $start_pos < $end_pos) {
        $start_pos += strlen($start);
        $text_before = substr($original_string, 0, $start_pos - strlen($start));
        $text_after = substr($original_string, $end_pos + strlen($end));

        $new_text_blob = $text_before . $replacement . $text_after;
        return $new_text_blob;
    } else {
        // The start and end markers were not found in the expected order
        echo 'Start and/or end markers not found for ' . $replacement;
        return $original_string;
    }
}
// Add image (Currently only for course path)
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
// QUERY HELPERS

//Unique Case for Shravana Mangalam because it uses UIDs which is funky
function get_sm($value) {
	global $wpdb;

	$post_type = 'stm-courses';
	$meta_key = 'mgml_course_id';
	$meta_value = $value;
	$query = "SELECT $wpdb->posts.*
			  FROM $wpdb->posts
			  INNER JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id)
			  WHERE 1=1
				AND $wpdb->posts.post_type = %s
				AND ($wpdb->postmeta.meta_key = %s AND $wpdb->postmeta.meta_value = %s)
			  ORDER BY $wpdb->posts.ID ASC";

	$results = $wpdb->get_results( $wpdb->prepare( $query, $post_type, $meta_key, $meta_value ) );
	error_log("getting result from sm for " . $value);

	 if (count($results) > 1 ) {
        error_log("ERROR: More than one " . $post_type . " with the same MGML ID: " . $value);
        error_log(print_r($results, true));
        return null;
    }
    if ( count($results) == 0) {
        error_log("ERROR: No " . $post_type . " with this MGML ID: " . $value);
        return null;
    }
    return $results[0]->ID;
// 	error_log(print_r($results[0], true));
}

// Get all posts matching post_type and meta_key / meta_value pair
function cd_get_posts($post_type, $key, $value) {
    error_log("Finding if " . $post_type . " exists at " . $key . " for this value: " . $value);
    $args = array(
        'post_type'      => $post_type,
        'meta_key'       => $key,
        'meta_value'     => $value,
		'orderby'        => 'ID',
        'order'          => 'ASC',
        'posts_per_page' => -1, // Retrieve all matching posts
    );
    $query = new WP_Query( $args );
    $posts = wp_list_pluck( $query->posts, 'ID' );
	error_log(print_r($args, true));
	error_log(print_r($posts, true));

    return $posts;
}

// Get single post matching post_type and meta_key / meta_value pair or error if more than 1 found
function get_from_post($post_type, $key, $value) {

    $posts = cd_get_posts($post_type, $key, $value);
	
    if (count($posts) > 1 ) {
        error_log("ERROR: More than one " . $post_type . " with the same MGML ID: " . $value);
        error_log(print_r($posts, true));
        return null;
    }

    if ( count($posts) == 0) {
        error_log("ERROR: No " . $post_type . " with this MGML ID: " . $value);
        return null;
    }

    return $posts[0];
}

// Gets lessons for the section
function get_lessons_for_section($section_id) {
    $args = array(
        'post_type'      => array( 'stm-lessons', 'stm-quizzes', 'stm-assignments' ),
        'meta_key'       => 'mgml_section_id',
        'meta_value'     => $section_id,
        'orderby'        => 'ID',
        'order'          => 'ASC',
        'posts_per_page' => -1, // Retrieve all matching posts
    );

    $query = new WP_Query( $args );

    $posts = wp_list_pluck( $query->posts, 'ID' );
    return $posts;
}


// Get a user_id from metakey and metavalue pair
function get_user_id($key, $val) {

    $args = array(
        'meta_query' => array(
            array(
                'key'   => $key,
                'value' => $val,
            ),
        ),
    );

    $user_query = new WP_User_Query( $args );
    $users = $user_query->get_results();
    if ( $user_query->found_users > 1 ) {
        error_log("ERROR: More than one user with the same MGML user ID: " . $val);
        error_log(print_r($users, true));
        return null;
    }

    if ( ! empty( $users ) ) {
        // Get the first user ID and return it
        return $users[0]->ID;
    } else {
        error_log("ERROR: No users found with this MGML user ID: " . $val);
        return null;
    }
}
?>