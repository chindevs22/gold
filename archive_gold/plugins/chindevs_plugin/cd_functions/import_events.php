<?php
	// --------------------------------------------------------------------------------------------
	// CREATE EVENTS
	// --------------------------------------------------------------------------------------------

	require_once 'helpers.php';
	require_once ABSPATH . 'wp-admin/includes/taxonomy.php';


	function create_event_from_csv($eventData) {
		echo('came to create event from csv method');
		// Event info mapped from CSV
		$wpdata['post_title'] = $eventData['event_name']; // Ensure all content doesn't have any special characters

		$description = $eventData['description'];
		if (isset($eventData['zoom_link']) && $eventData['zoom_link'] != "NULL") {
		    $description .= $eventData['zoom_link'];
		}

		$wpdata['post_content'] = html_entity_decode($description);
		$wpdata['post_excerpt'] = html_entity_decode($eventData['short_description']);

		if ($eventData['status'] == "pending") {
            $wpdata['post_status'] ='private';
		} else {
		    $wpdata['post_status'] ='publish';
		}

		$wpdata['post_type'] = 'stm-courses';
		$event_post_id = wp_insert_post( $wpdata );
        update_post_meta($event_post_id, 'mgml_course_id', $eventData['id']);
		echo "post id: " . $event_post_id;


        //Price metadata fields
         $usPrices = array (
            'price_residential_usd'=> $eventData['price_with_resedential_without_accomodation_usd'],
            'price_online_usd' => $eventData['price_with_online_usd'],
            'price_ac_usd' => $eventData['price_with_ac_accomodation_usd'],
            'price_nonac_usd'=> $eventData['price_with_non_ac_accomodation_usd'],

        );

        $lowestUSPrice = null;

        foreach ($usPrices as $priceKey => $priceValue) {
            if (isset($priceValue) && $priceValue !== "NULL" && $priceValue != 0) {
                update_post_meta($event_post_id, $priceKey, $priceValue);
                 if (!isset($lowestUSPrice) || $priceValue < $lowestUSPrice) {
                    $lowestUSPrice = $priceValue;
                 }
            }
        }

        $inrPrices = array (
            'price_residential'=> $eventData['price_with_resedential_without_accomodation'],
            'price_ac'=> $eventData['price_with_ac_accomodation'],
            'price_online' => $eventData['price_with_online'],
            'price_nonac'=> $eventData['price_with_non_ac_accomodation']
         );

        $lowestINRPrice = null;

        foreach ($inrPrices as $priceKey => $priceValue) {
            if (isset($priceValue) && $priceValue !== "NULL" && $priceValue != 0) {
                update_post_meta($event_post_id, $priceKey, $priceValue);
                 if (!isset($lowestINRPrice) || $priceValue < $lowestINRPrice) {
                    $lowestINRPrice = $priceValue;
                 }
            }
        }

        // Handling Pricing - what to do when 1 or both prices are null?
        // TODO: Update this code with additional prices list fields (Currency etc)

        if ( !empty($eventData['price_usd']) && $eventData['price_usd'] != "NULL" ) {
         $us_price = $eventData['price_usd'];
        } else {
         $us_price = isset($lowestUSPrice) ? $lowestUSPrice : 0;
        }

        if ( !empty($eventData['price']) && $eventData['price'] != "NULL" ) {
            $inr_price = $eventData['price'];
        } else {
            $inr_price = isset($lowestINRPrice) ? $lowestINRPrice : 0;
        }

        update_post_meta($event_post_id, 'price', $inr_price);

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

        update_post_meta($event_post_id, 'prices_list', json_encode($price_arr));
        update_post_meta($event_post_id, 'level', $eventData['level']);
        update_post_meta($event_post_id, 'current_students', 0);



        // Generate Curriculum String
        $isWebinar = false;
        $eventLessons = cd_get_posts('stm-lessons', 'mgml_webinar_id', $eventData['id']);
        $curriculum_string = "";
        if ( !isset($eventLessons) ||  count($eventLessons) == 0 ) {
            error_log("No lessons for this Event: " .  $eventData['id']);
        } else {
             $isWebinar = true;
             $eSection = array($eventData['event_name']);
             $combinedArray = array_merge($eSection, $eventLessons);
             $curriculum_string = implode(",", $combinedArray);
        }
        error_log($curriculum_string);

        //TODO: how to handle webinars that don't have any lessons
        if(empty($curriculum_string) || strlen($curriculum_string) == 0) {
            $curriculum_string = "InPlace Section, 215654";
        }
        update_post_meta($event_post_id, 'curriculum', $curriculum_string);

        // Handle Category  -----------------------------------------------------------------
        $taxonomy = 'stm_lms_course_taxonomy';
        $parent_cat_name =  $eventData['parent_category'];
        if (!isset($parent_cat_name) || $parent_cat_name == "NULL") {
            $parent_cat_name = "Events";
        }
        $parentTerm = get_term_by( 'name', $parent_cat_name , $taxonomy );

        // Create or Find Parent Category ID
        if ( $parentTerm ) { // Parent Term Exists
            $parent_category_id = $parentTerm->term_id;
            echo "The ID of the term " . $parent_cat_name. " is: " . $parent_category_id;
        } else { // Create the Parent Term
            $new_term = wp_insert_term($parent_cat_name, $taxonomy, array('parent'=> 0));
            $parent_category_id = intval($new_term['term_id']);
            update_term_meta($parent_category_id, 'is_lite_category', 1);
            if ($isWebinar) {
                update_term_meta($parent_category_id, 'lite_category_name', 'webinar');
            } else {
                update_term_meta($parent_category_id, 'lite_category_name', 'event');
            }

            echo "The resulting id: " . $parent_category_id;
            echo "Created category " . $parent_cat_name;
            error_log("Created category " . $parent_cat_name);
        }

        // Create or Find Sub Category ID
        $sub_cat_name =  $eventData['sub_category'];
        if (!isset($sub_cat_name) || $sub_cat_name == "NULL") {
            wp_set_object_terms($event_post_id, $parent_category_id,  $taxonomy, $append = true );
        } else {
            $subCatTerm = get_term_by( 'name', $sub_cat_name , $taxonomy );
            if ( $subCatTerm ) { // if Sub Category Exists already
                 $sub_category_id = $subCatTerm->term_id;
                 echo "The ID of the term " . $sub_cat_name . " is: " . $sub_category_id;
            } else {
                $new_sub_term = wp_insert_term($sub_cat_name, $taxonomy, array('parent'=> $parent_category_id));
                $sub_category_id = intval($new_sub_term['term_id']);
                update_term_meta($sub_category_id, 'is_lite_category', 1);
                update_term_meta($sub_category_id, 'lite_category_name', 'event');
                echo "Created category " . $sub_cat_name;
                error_log("Created category " . $sub_cat_name);
            }

            wp_set_object_terms($event_post_id, $sub_category_id,  $taxonomy, $append = true );
        }

        //Set Event Dates
        // only set these dates for events not webinars
		$registrationCloseDate = strtotime($eventData['registration_close_date']  . "06:00:00") * 1000;

		if (!empty( $eventData['start_date']) &&  $eventData['start_date'] != "NULL") {
			update_post_meta($event_post_id, 'start_event_date',  $eventData['start_date']); // Ensure format is YYYY-MM-DD
		}
		// set end date and registration close date to the same so it doesn't show up on the calendar
		if (!empty( $registrationCloseDate ) &&  $registrationCloseDate != "NULL") {
		    update_post_meta($event_post_id, 'end_event_date', $eventData['registration_close_date']);
        	update_post_meta($event_post_id, 'registration_close_date', $registrationCloseDate);
		} else {
		    update_post_meta($event_post_id, 'end_event_date', $eventData['start_date']);
//             update_post_meta($event_post_id, 'registration_close_date', $eventData['start_date']);
		}

		// Set Event Permalinking to Calendar
		update_post_meta($event_post_id, 'codemine_event_stm-courses_url_new_window', 'true');
		update_post_meta($event_post_id, 'codemine_event_stm-courses_action_on_click', 'goto_permalink');

	}
?>