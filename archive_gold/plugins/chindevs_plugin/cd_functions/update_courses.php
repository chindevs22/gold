<?php

	// --------------------------------------------------------------------------------------------
	// UPDATE COURSES
	// --------------------------------------------------------------------------------------------
    require_once 'helpers.php';

    // TODO: FOR ALL UPDATES , SWITCH queries from "_test"

    // Update Button Code & Link PDFs to real location
    // Expect $linkingData row to have mgml_lesson_id, mgml_course_id, attachment, audio_url
    function update_lesson_with_pdf($linkingData) {
        $wp_lesson_id = get_from_post('stm-lessons', 'mgml_lesson_id', $linkingData['mgml_lesson_id']);
        error_log("WP Lesson ID being updated: " . $wp_lesson_id);

        $file_content = '';
		$embedded_audio = '';
        if (isset($linkingData['attachment']) && $linkingData['attachment'] != "NULL") {
            $link = '/wp-content/uploads/cd_media/lesson_materials/course_'.$linkingData['mgml_course_id'].'/'.$linkingData['attachment'];
            error_log("Crafted PDF Link path: " . $link);
            $file_content = '<a class="elementor-button elementor-button-link elementor-size-md study_material" title="Study Material" href="/wp-content/plugins/pdfjs-viewer-shortcode/pdfjs/web/viewer.php?file='.$link.'" target="_blank" rel="noopener"><img class="pdf_img" src="/wp-content/uploads/2023/10/pdf.png" width="24" height="24" /> Study Material</a></p>';
        }

		if (isset($linkingData['audio_url']) && $linkingData['audio_url'] != "NULL") {
        	$embedded_audio = '[embed]'.$linkingData['audio_url'].'[/embed]';
		}
        $updated_lesson_content = $embedded_audio . $file_content;

        wp_update_post(
            array(
                'ID'           => $wp_lesson_id,
                'post_content' => $updated_lesson_content
            )
        );

		error_log("WP Lesson ID Updated: " . $wp_lesson_id);
    }

      //Adds Image to Course
      function update_course_thumbnail() {
          global $wpdb;

          $posts = get_posts(
              array(
                  'post_type'   => 'stm-courses',
                  'post_status'    => 'publish',
                  'posts_per_page' => -1,
                  'meta_key' => 'mgml_type',
                  'meta_value' => 'course'
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
              course_image_adding($post->ID, $mgml_course_id);
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

    // Strip CSS for Courses
    function update_course_description() {
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
            $description = $post->post_content;
            // Define the pattern for matching TEXT 1 style
            $pattern = '/<p\s+style="font-size:22px;color:#007e7d;"><strong>(.*?)<\/strong><\/p>/';
            // Replace matches with <h3> tags
            $replacement = '<h3 style="color: #007e7d;">$1</h3>';
            // Perform the replacement
            $newHtml = preg_replace($pattern, $replacement, $description);
            $post->post_content = $newHtml;
            wp_update_post( $post );
        }
    }

    // Updates the "Preview" video in includes bar for shravana mangalam and courses
    function update_preview_video() {
        global $wpdb;

        $posts = get_posts(
            array(
                'post_type'   => 'stm-courses',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'meta_query' => array(
                    'relation' => 'OR',
                    array(
                        'key'     => 'mgml_type',
                        'value'   => 'course_test',
                        'compare' => '=',
                    ),
                    array(
                        'key'     => 'mgml_type',
                        'value'   => 'shravana_mangalam_test',
                        'compare' => '=',
                    ),
                ),
            )
        );

        error_log("# of courses/sm returned from query: " . count($posts));

        foreach ($posts as $post) {
            $free_lesson_id = get_post_meta($post->ID, 'free_lesson', true);
            $lesson_url = get_post_meta($free_lesson_id, 'lesson_vimeo_url', true);

            if(empty($lesson_url) || $lesson_url == "NULL") {
                error_log("MGML Course ID " . $post->ID . " did not have a video for free lesson found: " . $free_lesson_id);
            } else {
                update_post_meta($post->ID, 'rsfv_featured_embed_video', $lesson_url);
                update_post_meta($post->ID, 'rsfv_source', 'embed');
            }
        }
    }


    // For all Courses - Mark the Feedback Lesson as complete if the last quiz is complete
    // This is taken care of by the import code the next time we run USAD - using update script just for interim
    function mark_feedback_lesson_as_complete() {
        global $wpdb, $feedbackLessonID;

        // Get all Courses
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


        // For Each Course select the last item of its curriculum
        $last_quizzes = array();
        foreach ($posts as $post) {
            $old_curr = get_post_meta($post->ID, 'curriculum_old', true);
            $curr_array = create_array_from_string($old_curr, ',');
            array_push($last_quizzes, end($curr_array));
        }

        // Get all the user quiz entries
        $user_quiz_table_name = 'wp_stm_lms_user_quizzes';
        $query = $wpdb->prepare("SELECT * FROM $user_quiz_table_name WHERE quiz_id IN (%s)", implode(',', $last_quizzes));
        $results = $wpdb->get_results($query);

        error_log("# of user quizzes returned from query: " . count($results));

        // For each user quiz complete the feedback lesson for that course / user
        foreach ($results as $row) {
            $user_id = $row->user_id;
            $course_id = $row->course_id;

            $lesson_table_name = 'wp_stm_lms_user_lessons';
            $wpdb->insert($lesson_table_name, array(
                'user_lesson_id' => NULL,
                'user_id' => $user_id,
                'course_id' => $course_id,
                'lesson_id' => $feedbackLessonID
            ));
        }
    }

    // Remove Feedback Lesson from SM/Webinar Curriculums
    // This is taken care of by the import code the next time we run Imports for SM/Webinar - using update script just for interim
    function remove_feedback_lesson_from_noncourse() {
        global $wpdb;

        $posts = get_posts(
            array(
                'post_type'   => 'stm-courses',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'meta_query' => array(
                    'relation' => 'OR',
                    array(
                        'key'     => 'mgml_type',
                        'value'   => 'webinar_test',
                        'compare' => '=',
                    ),
                    array(
                        'key'     => 'mgml_type',
                        'value'   => 'shravana_mangalam_test',
                        'compare' => '=',
                    ),
                ),
            )
        );
        // Can i delete this and just do it via
        // SELECT * FROM `wp_stm_lms_curriculum_materials` where post_id = 321807;
    }

    // Updates Includes Bar with Attachments from CM Media
    // TODO: If this works update the import script
    function update_includes_bar_with_attachments() {
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

            $filename = 'TestCourseSyllabus.pdf'; // TODO: For each file in directory?
            $upload_path = "cd_media/course_includes/{$mgml_course_id}/{$filename}";

            $attachment = array(
                'post_mime_type' => 'application/pdf',
                'post_title'     => sanitize_file_name($filename),
                'post_content' => '',
                'post_status' => 'inherit'
            );

            $attachment_id = wp_insert_attachment( $attachment, $upload_path );
            if ( ! is_wp_error( $attachment_id ) ) {
                require_once ABSPATH . 'wp-admin/includes/image.php';
                $attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload_path );
                wp_update_attachment_metadata( $attachment_id, $attachment_data );
                update_post_meta( $post->ID, 'course_files', array($attachment_id) );
            }
        }
    }
?>

