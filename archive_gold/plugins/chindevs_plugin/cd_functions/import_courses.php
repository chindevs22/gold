<?php
	// --------------------------------------------------------------------------------------------
	// CREATE COURSE SECTION
	// --------------------------------------------------------------------------------------------

	// create the course
	require_once 'helpers.php';
	function create_course_from_csv($courseData) {
		global $courseMGMLtoWP, $sectionToLessonMap, $wpdb;

		echo "IMPORTING COURSE <br> <br>";

        $faq_description;
        $new_desc = html_entity_decode($courseData['description']);
        $faq_flag = false;
		// need to handle course types without this exact faq
        if (strpos($new_desc, "Frequently Asked Questions")) {
            $faq_flag = true;
            $desc_arr = explode("Frequently Asked Questions", $courseData['description']);
			$orig_description = html_entity_decode($desc_arr[0]);
			$new_desc = substr($orig_description, 0, strrpos($orig_description, "<br>") );
            $faq_description = $desc_arr[1];
        }

        // Search Course Description for these 4 sections
		$new_desc = replace_section_2($new_desc, 'Home Study Course Scholarship Initiative', 'Director: <a href="mailto:homestudycourses@chinfo.org">homestudycourses@chinfo.org.', '[homestudy_text]');
		$new_desc =   replace_section_2($new_desc, 'For Queries', 'E-mail: vedantacourses@chinfo.org', '[for_queries_text]');
        $new_desc =  replace_section_2($new_desc, 'Course Access, Query Resolution Process', 'The digital copy of the same will be sent to your registered email ID.', '[course_access_text]');
        $new_desc =  replace_section_2($new_desc, 'A) Question No 1', '4-5: Match the following.', '[question_format_text]');

		if (!isset($new_desc) || $new_desc == "") {
			$new_desc = "Could not parse course description. Populate from front end.";
			echo "Could not parse course description for " . $courseData['id'];
			error_log("Could not parse course description for " . $courseData['id']);
		}

		// Create array of Course info from CSV data
		$wpdata['post_title'] = $courseData['title'];
		$wpdata['post_content'] = $new_desc;
		$wpdata['post_excerpt'] = $courseData['short_description'];
		$wpdata['post_status'] ='publish';
		$wpdata['post_type'] = 'stm-courses';
		$course_post_id = wp_insert_post( $wpdata );

		echo "Course ID: " . $courseData['id'] . "  Course Post Id: " . $course_post_id . " <br> <br>";
// 		$courseMGMLtoWP[$courseData['id']] = $course_post_id;
		update_post_meta($course_post_id, 'mgml_course_id', $courseData['id']);

		// Generate Curriculum

		$curriculum_string = "";
		$combinedArray = array();
		$sectionString = $courseData['section'];
		$sectionArray = create_array_from_string($sectionString, ",");
        $sectionCount = 1;
		foreach ($sectionArray as $sectionID) {
			if (empty($sectionID) || $sectionID == "NULL") {
				error_log("ERROR: No Section provided");
				continue;
			}
			$lessonArray = get_lessons_for_section($sectionID);

            // Add Feedback lesson to the last section
			if ($sectionCount == count($sectionArray) - 1) {
                $insert_index = count($lessonArray) - 1;
                array_splice($lessonArray, $insert_index, 0, 232704); // TODO: Make sure this lesson exists
            }
            //Create a Section Record
            $sectionName = get_post_meta($lessonArray[0], 'mgml_section_name', true);
            $sArray = array($sectionName);
            $section_table_name = 'wp_stm_lms_curriculum_sections';
            $wpdb->insert($section_table_name, array(
                'title' => $sectionName,
                'course_id' => $course_post_id,
                'order' => $sectionCount++,
            ));
            $wp_section_id = $wpdb->insert_id;

            //Create a Curriculum Materials Record
            $curr_materials_table_name = 'wp_stm_lms_curriculum_materials';
            $lessonCount = 1;
            foreach($lessonArray as $lessonID) {
                if ($lessonCount == 1) {
                    //First Lesson - populate free lesson url, preview on
                    update_post_meta($course_post_id, 'free_lesson', $lessonID);
                    update_post_meta($lessonID, 'preview', 'on');
                }
                $post_type = get_post_type($lessonID);
                $wpdb->insert($curr_materials_table_name, array(
                    'post_id' => $lessonID,
                    'post_type' => $post_type,
                    'section_id' => $wp_section_id,
                    'order' => $lessonCount++
                ));
            }

            $combinedArray = array_merge($combinedArray, $sArray, $lessonArray);

		}
        $curriculum_string = implode(",", $combinedArray);
        update_post_meta($course_post_id, 'curriculum_old', $curriculum_string);


		// Handling Pricing - what to do when 1 or both prices are null?
		$us_price = $courseData['price_usd'];
		$inr_price = $courseData['price'];
		$sale_us_price = $courseData['discounted_price_usd'];
		$sale_inr_price = $courseData['discounted_price'];

		if ($sale_us_price == 0 || $sale_us_price == "NULL") {
		    $sale_us_price = "";
		}
        if ($sale_inr_price == 0 || $sale_inr_price == "NULL") {
            $sale_inr_price = "";
        }
		update_post_meta($course_post_id, 'price', $inr_price);
		update_post_meta($course_post_id, 'price', $sale_inr_price);

        $price_arr = array();
        if(isset($us_price) && $us_price != "NULL") {
            array_push($price_arr, array(
                "country" => "US",
				"currency_symbol" => "USD",
                "price" => $us_price,
				"sale_price" => $sale_us_price
            ));
        }

         if(isset($inr_price) && $inr_price != "NULL") {
            array_push($price_arr, array(
                "country" => "IN",
				"currency_symbol" => "INR",
                "price" => $inr_price,
				"sale_price" => $sale_inr_price
            ));
        }
        update_post_meta($course_post_id, 'prices_list', json_encode($price_arr));


		update_post_meta($course_post_id, 'level', $courseData['level']);
		update_post_meta($course_post_id, 'current_students', 0);

// 		Make Trial Course
// 		if (!empty($price) || $price != 0) {
// 			update_post_meta($course_post_id, 'shareware', 'on');
// 		}
//
		//append faq
		if($faq_flag) {
		    $faq_string = build_faq($faq_description);
			echo "this is the faq string ";
			echo "<br>" . $faq_string . "<br>";
		    update_post_meta($course_post_id, 'faq', $faq_string);
			update_post_meta($course_post_id, '_wp_page_template', 'default');
		}

		//handle payment FAQ
		update_post_meta($course_post_id, 'payment_faq', '[{"question":"","answer":""}]');

// 		TODO: uncomment this when we actually have images
// 		add_course_image($course_post_id, $courseData['id']); // adds the image to the course

// 		this appends the category as a term with the taxonomy relationship to the course ID

// 		$category = $courseData['parent_category'];
// 		//TODO: Fix the categorization to mimic publications (create terms)
		$category_arr  = array("Course Category");
// 		if ($category == 'Satsang Webinars' || $category == 'Text-based Webinars') {
// 			$category_arr = array("Study Format", $category);
// 		} else {
// 			$category_arr = array("Subject Matter", $category);
// 		}

        // Handle Category -----------------------------------------------------------------
        $taxonomy = 'stm_lms_course_taxonomy';
        $parent_cat_name =  $courseData['category_name'];
        $parentTerm = get_term_by( 'name', $parent_cat_name , $taxonomy );

        // Create or Find Parent Category ID
        if ( $parentTerm ) { // Parent Term Exists
            $parent_category_id = $parentTerm->term_id;
            echo "The ID of the term " . $parent_cat_name. " is: " . $parent_category_id;
        } else { // Create the Parent Term
            $new_term = wp_insert_term($parent_cat_name, $taxonomy, array('parent'=> 0));
            $parent_category_id = intval($new_term['term_id']);
            echo "The resulting id: " . $parent_category_id;
            echo "Created category " . $parent_cat_name;
            error_log("Created category " . $parent_cat_name);
        }

        // Create or Find Sub Category ID
        $sub_cat_name =  $courseData['subcategory_name'];
        $subCatTerm = get_term_by( 'name', $sub_cat_name , $taxonomy );
        if ( $subCatTerm ) { // if Sub Category Exists already
             $sub_category_id = $subCatTerm->term_id;
             echo "The ID of the term " . $sub_cat_name . " is: " . $sub_category_id;
        } else {
            $new_sub_term = wp_insert_term($sub_cat_name, $taxonomy, array('parent'=> $parent_category_id));
            $sub_category_id = intval($new_sub_term['term_id']);
            echo "Created category " . $sub_cat_name;
            error_log("Created category " . $sub_cat_name);
        }
        wp_set_object_terms($course_post_id, $sub_category_id,  $taxonomy, $append = true );

	}
?>