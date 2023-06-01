<?php

/** @var \MasterStudy\Lms\Plugin $plugin */
add_filter( 'wp_rest_search_handlers', array( $plugin, 'register_search_handlers' ) );
add_filter(
	'rest_user_query',
	function ( array $prepared_args, \WP_REST_Request $request ) {
		unset( $prepared_args['has_published_posts'] );

		return $prepared_args;
	},
	10,
	2
);
add_filter(
	'masterstudy_lms_lesson_video_sources',
	function () {
		return array_map(
			function ( $id, $label ) {
				return array(
					'id'    => $id,
					'label' => $label,
				);
			},
			array_keys( apply_filters( 'ms_plugin_video_sources', array() ) ),
			array_values( apply_filters( 'ms_plugin_video_sources', array() ) )
		);
	}
);
