<?php
function ms_lms_courses_archive_filter() {
	check_ajax_referer( 'filtering', 'nonce' );

	/* check & sanitize all ajax data */
	$cards_to_show    = ( isset( $_POST['cards_to_show'] ) ) ? intval( $_POST['cards_to_show'] ) : 8;
	$posts_per_page   = ( ! isset( $_POST['cards_to_show_choice'] ) || 'all' === $_POST['cards_to_show_choice'] ) ? -1 : $cards_to_show;
	$current_page     = ( isset( $_POST['current_page'] ) ) ? intval( $_POST['current_page'] ) : false;
	$offset           = ( isset( $_POST['offset'] ) ) ? intval( $_POST['offset'] ) : false;
	$card_style       = ( isset( $_POST['card_template'] ) ) ? sanitize_text_field( wp_unslash( $_POST['card_template'] ) ) : 'card_style_1';
	$pagination_style = ( isset( $_POST['pagination_template'] ) ) ? sanitize_text_field( wp_unslash( $_POST['pagination_template'] ) ) : '';
	$meta_slots       = ( isset( $_POST['meta_slots'] ) ) ? STM_LMS_Helpers::array_sanitize( wp_unslash( $_POST['meta_slots'] ) ) : array();
	$card_data        = ( isset( $_POST['card_data'] ) ) ? STM_LMS_Helpers::array_sanitize( wp_unslash( $_POST['card_data'] ) ) : array();
	$popup_data       = ( isset( $_POST['popup_data'] ) ) ? STM_LMS_Helpers::array_sanitize( wp_unslash( $_POST['popup_data'] ) ) : array();
	$sort_by          = ( isset( $_POST['sort_by'] ) ) ? sanitize_text_field( wp_unslash( $_POST['sort_by'] ) ) : '';
	$search           = ( isset( $_POST['args']['s'] ) ) ? sanitize_text_field( wp_unslash( $_POST['args']['s'] ) ) : '';
	$terms            = ( isset( $_POST['args']['terms'] ) ) ? STM_LMS_Helpers::array_sanitize( wp_unslash( $_POST['args']['terms'] ) ) : array();
	$metas            = ( isset( $_POST['args']['meta_query'] ) ) ? STM_LMS_Helpers::array_sanitize( wp_unslash( $_POST['args']['meta_query'] ) ) : array();

	/* query courses */
	$default_args = array(
		'posts_per_page' => $posts_per_page,
		's'              => $search,
		'meta_query'     => array(
			'relation' => 'AND',
			'featured' => array(
				'relation' => 'OR',
				array(
					'key'     => 'featured',
					'value'   => 'on',
					'compare' => '!=',
				),
				array(
					'key'     => 'featured',
					'compare' => 'NOT EXISTS',
				),
			),
		),
	);
	if ( ! empty( $metas ) || ! empty( $terms ) || ! empty( $search ) ) {
		$default_args['meta_query']['featured'] = array();
	}
	if ( ! empty( $current_page ) ) {
		$default_args['paged'] = $current_page;
	}
	if ( ! empty( $offset ) ) {
		$default_args['offset'] = $offset;
	}
	$default_args = apply_filters( 'stm_lms_filter_courses', $default_args, $terms, $metas, $sort_by );
	$courses      = STM_LMS_Courses::get_all_courses( $default_args );

	/* content send*/
	$response = array();
	if ( ! empty( $courses ) && is_array( $courses ) ) {
		$response['cards'] = STM_LMS_Templates::load_lms_template(
			"elementor-widgets/courses/card/{$card_style}/main",
			array(
				'courses'             => ( isset( $courses['posts'] ) ) ? $courses['posts'] : array(),
				'meta_slots'          => $meta_slots,
				'card_data'           => $card_data,
				'popup_data'          => $popup_data,
				'course_card_presets' => $card_style,
			)
		);
		if ( ! empty( $pagination_style ) && $courses['total_pages'] > 1 ) {
			$response['pagination'] = STM_LMS_Templates::load_lms_template(
				"elementor-widgets/courses/courses-archive/pagination/{$pagination_style}",
				array(
					'pagination_data' => array(
						'current_page'   => $current_page,
						'total_pages'    => $courses['total_pages'],
						'total_posts'    => $courses['total_posts'],
						'posts_per_page' => $posts_per_page,
						'offset'         => $posts_per_page + $offset,
					),
				)
			);
		}
	} else {
		$response['no_found'] = STM_LMS_Templates::load_lms_template( 'elementor-widgets/courses/courses-archive/filter/no-results' );
	}
	wp_send_json( $response );
}
add_action( 'wp_ajax_ms_lms_courses_archive_filter', 'ms_lms_courses_archive_filter' );
add_action( 'wp_ajax_nopriv_ms_lms_courses_archive_filter', 'ms_lms_courses_archive_filter' );
