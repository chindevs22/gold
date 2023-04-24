<?php

// Add the Events Tab
//

/*ACTIONS*/
function user_events_url() {
	$settings = get_option( 'stm_lms_settings', array() );

	if ( empty( $settings['user_url'] ) || ! did_action( 'init' ) ) {
		return home_url( '/' );
	}

	return get_the_permalink( $settings['user_url'] ) . 'user-events';
}

add_filter( 'stm_lms_custom_routes_config', function( $routes ) {

    // Define your custom route configuration
    $routes['user_url']['sub_pages']['user_events'] = array(
    			'template'  => 'stm-lms-user-events',
    			'protected' => true,
    			'url'       => 'user-events',
    		);
    return $routes;
    } 
);

// SOME VERSION OF CHANGING THE STM_LMS_SETTINGS for a specific sorting menu worked
// i have no clue which one
// defer to the database blob to figure this out????
// $my_option = array(
// 	'id'           => 'user_events',
// 	'label'        => 'Enrolled Events',
// 	'menu_place'   => 'learning',
// );

// // Get the current settings
// $settings = get_option( 'stm_lms_settings', array() );
// error_log(print_r($settings,true));

// // // Add your custom option to the options array
// // error_log("trying toa dd m");
// // error_log(print_r($settings['sorting_the_menu'],true));

// // // error_log("Trying to add my own setting");
// // // error_log(print_r($settings['options'],true));
// // // // Update the stm_lms_settings option with the new settings
// update_option( 'stm_lms_settings', $settings );


add_filter(
	'stm_lms_menu_items',
	function ( $menus ) {
		$menus[] = array(
			'order'        => 105,
			'id'           => 'user_events',
			'slug'         => 'user-events',
			'lms_template' => 'stm-lms-user-events',
			'menu_title'   => esc_html__( 'Enrolled Events', 'masterstudy-lms-learning-management-system-pro' ),
			'menu_icon'    => 'fa-users',
			'menu_url'     => user_events_url(),
			'menu_place'   => 'learning',
		);

		return $menus;
	}
);

// This creates the event button for a course where the first category is of type event (TODO: for any category)
add_filter( 'stm_lms_template_name', 'event_button', 100, 2 );
function event_button( $template_name, $vars ) {

	if (! array_key_exists('course_id', $vars)) {
		return $template_name;
	}

	$terms = get_the_terms( $vars['course_id'], 'stm_lms_course_taxonomy' );
	if ( !$terms || is_wp_error( $terms ) ) {
		return $template_name;
	}
	$category = $terms[0];
	$category_id = $category->term_id;
	$cat_lite =  get_term_meta( $category_id, 'is_lite_category', true );
	$cat_name = get_term_meta( $category_id, 'lite_category_name', true );

	if ( $template_name === '/stm-lms-templates/global/buy-button.php' && $cat_name == 'event' && $cat_lite == 1 ) {
		$template_name = '/stm-lms-templates/global/buy-button/mixed1.php';
	}
	return $template_name;
}

// Get the different price options for an Event from database
function get_event_prices($event_id) {
	error_log("Getting prices for this event" . $event_id);
	error_log(get_post_meta($event_id, 'price_nonac', true));
	$prices = array (
		'price_nonac' => get_post_meta($event_id, 'price_nonac', true),
		'price_ac' => get_post_meta($event_id, 'price_ac', true),
		'price_online' => get_post_meta($event_id, 'price_online', true),
		'price_residential' => get_post_meta($event_id, 'price_residential', true),
		'price_nonac_usd' => get_post_meta($event_id, 'price_nonac_usd', true),
		'price_ac_usd' => get_post_meta($event_id, 'price_ac_usd', true),
		'price_online_usd' => get_post_meta($event_id, 'price_online_usd', true),
		'price_residential_usd' => get_post_meta($event_id, 'price_residential_usd', true)
	);

	foreach ($prices as $key => $value) {
		if ($value === null) {
			unset($prices[$key]);
		}
	}
	return $prices;
}
?>