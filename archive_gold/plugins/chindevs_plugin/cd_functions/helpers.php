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

// Builds FAQ Block from a string
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

// Get all posts matching post_type and meta_key / meta_value pair
function cd_get_posts($post_type, $key, $value) {
    error_log("Finding if " . $post_type . " exists at " . $key . " for this value: " . $value);
    $args = array(
        'post_type'      => $post_type,
        'meta_key'       => $key,
        'meta_value'     => $value,
// 		'orderby'        => 'ID',
//         'order'          => 'ASC',
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
	error_log("in get from post");
	error_log(print_r($posts, true));
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
        'post_type'      => array( 'stm-lessons', 'stm-quizzes' ),
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