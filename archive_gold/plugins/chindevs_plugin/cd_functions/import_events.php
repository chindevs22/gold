<?php
	// --------------------------------------------------------------------------------------------
	// CREATE EVENTS
	// --------------------------------------------------------------------------------------------

	// create the course
	require_once 'helpers.php';
	function create_event_from_csv($eventData) {
		echo('came to create event from csv method');
        // if no lessons, add default lesson
        global $templateEventSection;
        //TODO: add check here
        $curriculum_string = $templateEventSection;
		error_log(print_r($eventData, true));

		// Event info mapped from CSV
		$wpdata['post_title'] = $eventData['title'];
// 		$wpdata['post_content'] = html_entity_decode($eventData['description']);
// 		$wpdata['post_excerpt'] = $eventData['short_description'];
		$wpdata['post_status'] ='publish';
		$wpdata['post_type'] = 'stm-courses';
		$event_post_id = wp_insert_post( $wpdata );
		error_log(print_r($event_post_id, true));

        //TODO: discounts?
		$price = $eventData['price_usd']; //TODO: make compatible with that price changer thing
        update_post_meta($event_post_id, 'price', $price);
        update_post_meta($event_post_id, 'curriculum', $curriculum_string);
		//Price metadata fields
        update_post_meta($event_post_id, 'price_residential_usd', $eventData['price_with_resedential_without_accomodation_usd']);
		update_post_meta($event_post_id, 'price_online_usd', $eventData['price_with_online_usd']);
		update_post_meta($event_post_id, 'price_ac_usd', $eventData['price_with_ac_accomodation_usd']);
		update_post_meta($event_post_id, 'price_nonac_usd', $eventData['price_with_non_ac_accomodation_usd']);
		update_post_meta($event_post_id, 'price_residential', $eventData['price_with_resedential_without_accomodation']);
		update_post_meta($event_post_id, 'price_online', $eventData['price_with_online']);
		update_post_meta($event_post_id, 'price_ac', $eventData['price_with_ac_accomodation']);
		update_post_meta($event_post_id, 'price_nonac', $eventData['price_with_non_ac_accomodation']);

        //TODO: add FAQ parsing :(

        // EVENT sub categories
        // this appends the category as a term with the taxonomy relationship to the event
        // requires the parent category to be set in the eventCategoryMap
        // creates all sub categories
		echo('came to before categories method');
        global $eventCategoryMap; // make sure global var exists
        $taxonomy = 'stm_lms_course_taxonomy';
        $parent_cat = $eventData['parent_category_name']; // ensure in CSV
        $sub_cat = $eventData['sub_category_name']; // ensure in CSV
        $wp_category_int = 0;
        $parent_cat_id = $eventCategoryMap[$parent_cat];

        // Set the default meta for every category
        // TODO: when we handle webinars we need to figure out event vs shravana mangalam
        $defaults = array(
            'parent'=> $parent_cat_id
        );

        $term_response = term_exists($sub_cat, $taxonomy); // a record
        if ($term_response == null) {
            $new_term = wp_insert_term($sub_cat, $taxonomy , $defaults);
            update_term_meta($new_term['term_id'], 'is_lite_category', 1);
            update_term_meta($new_term['term_id'], 'lite_category_name', 'event');
            $wp_category_int = intval($new_term['term_id']);
        } else {
            $current_term = term_exists($sub_cat, $taxonomy, $parent_cat_id);
            if ($current_term == null) {
                $new_term = wp_insert_term($sub_cat, $taxonomy, $defaults);
                update_term_meta($new_term['term_id'], 'is_lite_category', 1);
                update_term_meta($new_term['term_id'], 'lite_category_name', 'event');
                $wp_category_int = intval($new_term['term_id']);
            } else {
                $wp_category_int = intval($current_term['term_id']);
            }
        }
        wp_set_object_terms($event_post_id, $wp_category_int, $taxonomy, $append = true );
		echo('came to the end');
	}
?>