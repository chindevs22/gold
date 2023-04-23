<?php
	// --------------------------------------------------------------------------------------------
	// CREATE EVENTS
	// --------------------------------------------------------------------------------------------

	// create the course
	require_once 'helpers.php';
	function create_event_from_csv($eventData) {

        // if no lessons, add default lesson
        global $templateEventSection;
        //TODO: add check here
        $curriculum_string = $templateEventSection;

		// Event info mapped from CSV
		$wpdata['post_title'] = $eventData['title'];
		$wpdata['post_content'] = html_entity_decode($eventData['description']);
		$wpdata['post_excerpt'] = $eventData['short_description'];
		$wpdata['post_status'] ='publish';
		$wpdata['post_type'] = 'stm-courses';
		$event_post_id = wp_insert_post( $wpdata );

        //TODO: discounts?
		$price = $eventData['price_usd']; //TODO: make compatible with that price changer thing
        update_post_meta($event_post_id, 'price', $price);
        update_post_meta($event_post_id, 'curriculum', $curriculum_string);

        //TODO: add FAQ parsing :(

        // EVENT sub categories
        // this appends the category as a term with the taxonomy relationship to the event
        // requires the parent category to be set in the eventCategoryMap
        // creates all sub categories

        global $eventCategoryMap; // make sure global var exists
        $taxonomy = 'stm_lms_course_taxonomy';
        $parent_cat = $eventData['parent_category_name']; // ensure in CSV
        $sub_cat = $eventData['sub_category_name']; // ensure in CSV
        $wp_category_int = 0;
        $parent_cat_id = $eventCategoryMap[$parent_cat];

        // Set the default meta for every category
        // TODO: when we handle webinars we need to figure out event vs shravana mangalam
        $defaults = array(
            'parent'=> $parent_cat_id,
            'meta' => array(
                'is_lite_category' => 1,
                'lite_category_name' => 'event'
            )
        );

        $term_response = term_exists($sub_cat, $taxonomy); // a record
        if ($term_response == null) {
            $new_term = wp_insert_term($sub_cat, $taxonomy , $defaults);
            $wp_category_int = intval($new_term['term_id']);
        } else {
            $current_term = term_exists($sub_cat, $taxonomy, $parent_cat_id);
            if ($current_term == null) {
                $new_term = wp_insert_term($sub_cat, $taxonomy, $defaults);
                $wp_category_int = intval($new_term['term_id']);
            } else {
                $wp_category_int = intval($current_term['term_id']);
            }
        }
        wp_set_object_terms($event_post_id, $wp_category_int, $taxonomy, $append = true );

	}
?>