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

?>