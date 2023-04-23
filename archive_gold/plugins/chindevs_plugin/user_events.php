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

// // Remove the current "Start Course/Buy Course button"
// remove_action( 'stm_lms_buy_button_end', 'stm_lms_course_buy_button', 10 );
// add_action( 'stm_lms_buy_button_end', 'add_buy_event_button', 10, 1 );



add_action( 'stm_lms_before_button_mixed', 'add_buy_event_button', 10, 1 );
// add_filter( 'stm_lms_before_button_stop', 'is_event', 100, 2 );

// function is_event($stop, $course_id){
// 	return true;
// }

// // add event button to course page
function add_buy_event_button( $course_id ) {
	$price = get_price( $course_id );
// 	if ( ! empty( $price ) ) {
		return STM_LMS_Templates::show_lms_template( 'events/buy', compact( 'course_id', 'price' ) );
// 	}
}

// add_action( 'wp', 'my_custom_remove_buy_button' );
// function my_custom_remove_buy_button() {
//     $course_id_to_hide = 123; // Replace with the ID of the course to hide the button for

//     // Remove the stm_lms_buy_button action for the course ID to hide
//     remove_action( 'stm_lms_buy_button', 'my_custom_buy_button', 10 );
// }

// // Custom function to modify the buy button
// function my_custom_buy_button( $html, $course_id, $course_price ) {
// //     if ( $course_id == $course_id_to_hide ) {
//         // Don't include the "Start course" button for the course ID to hide
//         $html = '';
// //     }
//     return $html;
// }

?>