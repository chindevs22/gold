<?php
	// --------------------------------------------------------------------------------------------
	// UPDATE COURSES
	// --------------------------------------------------------------------------------------------
    require_once 'helpers.php';


    // Expect $linkingData row to have mgml_lesson_id, mgml_course_id, attachment, audio_url
    // Update Button Code
    // Link PDFs to real location
    function update_lesson_with_pdf($linkingData) {
        $wp_lesson_id = get_from_post('stm-lessons', 'mgml_lesson_id', $linkingData['mgml_lesson_id']);
        error_log("WP Lesson ID being updated: " . $wp_lesson_id);

        $file_content = '';
        if (isset($linkingData['attachment']) && $linkingData['attachment'] != "NULL") {
            $link = '/wp-content/uploads/cd_media/lesson_materials/course_'.$linkingData['mgml_course_id'].'/'.$linkingData['attachment'];
            error_log("Crafted PDF Link path: " . $link);
            $file_content = '<a class="elementor-button elementor-button-link elementor-size-md study_material" title="Study Material" href="/wp-content/plugins/pdfjs-viewer-shortcode/pdfjs/web/viewer.php?file='.$link.'" target="_blank" rel="noopener"><img class="pdf_img" src="/wp-content/uploads/2023/10/pdf.png" width="24" height="24" /> Study Material</a></p>
        }

        $embedded_audio = '[embed]'.$linkingData['audio_url'].'[/embed]';
        $updated_lesson_content = $embedded_audio . $file_content;

        wp_update_post(
            array(
                'ID'           => $wp_lesson_id,
                'post_content' => $updated_lesson_content
            )
        );

    }

    //Adds Image to Course
    function update_course($attachment_linking_data) {
        global $wpdb;

        $posts = get_posts(
            array(
                'post_type'   => 'stm-courses',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'meta_key' => 'mgml_type',
                'meta_value' => 'course_test'
            )
        );

        error_log("# of courses returned from query: " . count($posts));

        foreach ($posts as $post) {
            $mgml_course_id = get_post_meta($post->ID, 'mgml_course_id', true);
			error_log("RETURNED MGML COURSE_ID: " . $mgml_course_id);

            if (empty($mgml_course_id) || $mgml_course_id == "") {
              error_log("COURSE DOESN'T HAVE MGML ID: " . $post->ID);
              continue;
            }

			// Add Thumbnail to Courses
            // course_image_adding($post->ID, $mgml_course_id);

        }
    }

    // Add image helper method (Currently only for course path)
    function course_image_adding($course_post_id, $mgml_course_id) {
      $upload_dir = wp_upload_dir();
      $filename = "{$mgml_course_id}.jpg";
      $upload_path = "cd_media/course_thumbnails/{$filename}";

      error_log("thumbnail upload path" . $upload_path);

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

