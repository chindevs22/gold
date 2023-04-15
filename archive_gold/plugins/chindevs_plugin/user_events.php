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

add_filter(
	'stm_lms_menu_items',
	function ( $menus ) {
		$menus[] = array(
			'order'        => 111,
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