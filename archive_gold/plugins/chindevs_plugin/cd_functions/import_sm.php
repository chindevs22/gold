<?php
	// --------------------------------------------------------------------------------------------
	// CREATE SHRAVANA MANGALAM
	// --------------------------------------------------------------------------------------------

	// create the course
	require_once 'helpers.php';
	require_once ABSPATH . 'wp-admin/includes/taxonomy.php';

	function create_sm_from_csv($smData) {
		echo "IMPORTING SHRAVANA MANGALAM <br> <br>";

        $faq_description;
        $new_desc = html_entity_decode($smData['description']);
        $faq_flag = false;

        // Parse Out FAQ
        if (strpos($new_desc, "Frequently Asked Questions")) {
            $faq_flag = true;
            $desc_arr = explode("Frequently Asked Questions", $smData['description']);
			$orig_description = html_entity_decode($desc_arr[0]);
			$new_desc = substr($orig_description, 0, strrpos($orig_description, "<br>") );
            $faq_description = $desc_arr[1];
        }

		if (!isset($new_desc) || $new_desc == "") {
			$new_desc = "Could not parse course description. Populate from front end.";
			echo "Could not parse course description for " . $smData['id'];
			error_log("Could not parse course description for " . $smData['id']);
		}

		echo "New Desc: " . $new_desc;
		// Create array of Course info from CSV data
		$wpdata['post_title'] = $smData['title'];
		$wpdata['post_content'] = $new_desc;
		$wpdata['post_excerpt'] = $smData['short_description'];
		$wpdata['post_status'] ='publish';
		$wpdata['post_type'] = 'stm-courses';
		$sm_post_id = wp_insert_post( $wpdata );

		echo "Course ID: " . $smData['id'] . "  Course Post Id: " . $sm_post_id . " <br> <br>";
		update_post_meta($sm_post_id, 'mgml_course_id', $smData['id']);

        // Generate Curriculum String
        $smLessons = cd_get_posts('stm-lessons', 'mgml_sm_id', $smData['id']);
        if ( count($smLessons) == 0 ) {
            error_log("No lessons for this Shravana Mangalam: " .  $smData['id']);
            return;
        }

        //Create a Section Record
         $section_table_name = 'wp_stm_lms_curriculum_sections';
         $wpdb->insert($section_table_name, array(
             'title' => $smData['title'],
             'course_id' => $sm_post_id,
             'order' => 1,
         ));
         $wp_section_id = $wpdb->insert_id;

         //Create a Curriculum Materials Record
         $curr_materials_table_name = 'wp_stm_lms_curriculum_materials';
         $lessonCount = 1;
         foreach($smLessons as $lessonID) {
            if ($lessonCount == 1) {
                 //First Lesson - populate free lesson url, preview on
                 update_post_meta($sm_post_id, 'free_lesson', $lessonID);
                 update_post_meta($lessonID, 'preview', 'on');
             }
             $post_type = get_post_type($lessonID);
             $wpdb->insert($curr_materials_table_name, array(
                 'post_id' => $lessonID,
                 'post_type' => 'stm-lessons',
                 'section_id' => $wp_section_id,
                 'order' => $lessonCount++
             ));
         }

        //Old Curriculum String
		$curriculum_string = "";
        $sArray = array($smData['title']);
        $combinedArray = array_merge($sArray, $smLessons);
		$curriculum_string = implode(",", $combinedArray);
        if(empty($curriculum_string) || strlen($curriculum_string) == 0) {
            $curriculum_string = "Sample Section, 5552";
        }
        update_post_meta($sm_post_id, 'curriculum', $curriculum_string);

		// Handling Pricing - what to do when 1 or both prices are null?
		$us_price = $smData['price_usd'];
		$inr_price = $smData['price'];

		update_post_meta($sm_post_id, 'price', $inr_price);

        $price_arr = array();
        if(isset($us_price) && $us_price != "NULL") {
            array_push($price_arr, array(
                "country" => "US",
                "price" => $us_price
            ));
        }

         if(isset($inr_price) && $inr_price != "NULL") {
            array_push($price_arr, array(
                "country" => "IN",
                "price" => $inr_price
            ));
        }
        update_post_meta($sm_post_id, 'prices_list', json_encode($price_arr));
		update_post_meta($sm_post_id, 'lite_type', 'shravana_mangalam');

		update_post_meta($sm_post_id, 'level', $smData['level']);
		update_post_meta($sm_post_id, 'current_students', 0);

// 		Make Trial Course
// 		if (!empty($price) || $price != 0) {
// 			update_post_meta($sm_post_id, 'shareware', 'on');
// 		}
//
		//append faq
		if($faq_flag) {
		    $faq_string = build_faq($faq_description);
			echo "this is the faq string ";
			echo "<br>" . $faq_string . "<br>";
		    update_post_meta($sm_post_id, 'faq', $faq_string);
			update_post_meta($sm_post_id, '_wp_page_template', 'default');
		}

// 		TODO: uncomment this when we actually have images
// 		add_course_image($sm_post_id, $smData['id']); // adds the image to the course

// 		Handle Category  -----------------------------------------------------------------
        $cat_name = $smData['parent_category'];
        $term = get_term_by( 'name', $cat_name, 'stm_lms_course_taxonomy' );
		$defaults = array('parent'=> 0);
        if ( $term ) {
            $parent_category_id = $term->term_id;
            echo "The ID of the term " . $cat_name . " is: " . $parent_category_id;
        } else {

			$new_term = wp_insert_term($cat_name, 'stm_lms_course_taxonomy', $defaults);
			$parent_category_id = intval($new_term['term_id']);
			echo "The resulting id: " . $parent_category_id;

            update_term_meta($parent_category_id, 'is_lite_category', 1);
            update_term_meta($parent_category_id, 'lite_category_name', 'shravana_mangalam');

            echo "Created category " . $cat_name;
            error_log("Created category " . $cat_name);
        }
        wp_set_object_terms($sm_post_id, $parent_category_id, 'stm_lms_course_taxonomy', $append = true );
	}


?>