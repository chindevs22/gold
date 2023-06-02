<?php


//
// -------------------------------------------------------- Code Related to Registering User for Event
//
add_action( 'wp_ajax_stm_lms_add_to_cart_reg_event', 'add_to_cart_reg_event' );

function add_to_cart_reg_event() {
	error_log("adding to cart reg e ");
	if ( ! is_user_logged_in() || empty( $_GET['course_id'] ) ) {
		die;
	}
	$r = array();

	$user     = STM_LMS_User::get_current_user();
	$user_id  = $user['id'];
	$item_id  = intval( $_GET['course_id'] );
	// Set the price
	$price = intval( $_GET['price'] );
	$priceLabel = intval( $_GET['label'] );
	$quantity  = 1;
	$is_woocommerce = STM_LMS_Cart::woocommerce_checkout_enabled();
	$item_added = count( stm_lms_get_item_in_cart( $user_id, $item_id, array( 'user_cart_id' ) ) );
	if ( ! $item_added ) {
		error_log("adding item ");
		stm_lms_add_user_cart( compact( 'user_id', 'item_id', 'quantity', 'price' ) );
	}

	if ( ! $is_woocommerce ) {
		error_log("not woocomerce");
		$r['text']     = esc_html__( 'Go to Cart', 'masterstudy-lms-learning-management-system' );
		$r['cart_url'] = esc_url( STM_LMS_Cart::checkout_url() );
	} else {

		$product_id = STM_LMS_Woocommerce::create_product( $item_id );
		update_post_meta( $product_id, '_regular_price', $price );
		update_post_meta( $product_id, '_price', $price );
		update_post_meta( $product_id, 'price_option_label', "abc" );

		// Load cart functions which are loaded only on the front-end.
		include_once WC_ABSPATH . 'includes/wc-cart-functions.php';
		include_once WC_ABSPATH . 'includes/class-wc-cart.php';

		if ( is_null( WC()->cart ) ) {
			wc_load_cart();
		}

		WC()->cart->add_to_cart( $product_id );

		$r['text']     = esc_html__( 'Go to Cart', 'masterstudy-lms-learning-management-system-pro' );
		$r['cart_url'] = esc_url( wc_get_cart_url() );
	}


	$r['redirect'] = STM_LMS_Options::get_option( 'redirect_after_purchase', false );
	wp_send_json( $r );
}

// Get the different price options for an Event from database
function get_event_prices($event_id) {
	error_log("Getting prices for this event" . $event_id);
	error_log(get_post_meta($event_id, 'price_nonac', true));
	$prices = array (
		'Non AC Price (INR)' => get_post_meta($event_id, 'price_nonac', true),
		'AC Price (INR)' => get_post_meta($event_id, 'price_ac', true),
		'Online Price (INR)' => get_post_meta($event_id, 'price_online', true),
		'Residential Price (INR)' => get_post_meta($event_id, 'price_residential', true),
		'Non AC Price (USD)' => get_post_meta($event_id, 'price_nonac_usd', true),
		'AC Price (USD)' => get_post_meta($event_id, 'price_ac_usd', true),
		'Online Price (USD)' => get_post_meta($event_id, 'price_online_usd', true),
		'Residential Price (USD)' => get_post_meta($event_id, 'price_residential_usd', true)
	);

	foreach ($prices as $key => $value) {
		if ($value === null || $value === 0 || $value === "0") {
			unset($prices[$key]);
		}
	}
	return $prices;
}

// function convert_to_utc_date($date) {
// 	$new_date = new DateTime('@' . floor($date / 1000)); // create DateTime object using the floor value of the epoch date divided by 1000 to get seconds
// 	$new_date->setTimeZone(new DateTimeZone('UTC')); // set timezone to UTC
// 	$utc_time = $new_date->format('Y-m-d H:i:s'); // format date as UTC timestamp
// 	return $utc_time;
// }

function get_event_deadline_date($event_id) {
	error_log("inside event end date");
    // Get the event dates metadata for the post
    $close_date = get_post_meta( $event_id, 'registration_close_date', true );

    if ( !empty($close_date) ) {
		return $close_date;
    }
	// check if there is an event end date
	$end_event_date = get_post_meta( $event_id, 'end_event_date', true );

	if( ! empty($end_event_date) ) {
		return $end_event_date;
	}
	return null;
}

//
// -------------------------------------------------------- Code Related to adding Enrolled Events for User Dashboard
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

function is_course_event($course_id) {
    $terms = get_the_terms( $course_id, 'stm_lms_course_taxonomy' );
    if ( !$terms || is_wp_error( $terms ) ) {
        return false;
    }
    $category = $terms[0];
    $category_id = $category->term_id;
    $cat_lite =  get_term_meta( $category_id, 'is_lite_category', true );
    $cat_name = get_term_meta( $category_id, 'lite_category_name', true );

    if ($cat_lite == 1 && $cat_name == 'event') {
        return true;
    }
    return false;
}
// This creates the event button for a course where the first category is of type event (TODO: for any category)
add_filter( 'stm_lms_template_name', 'event_button', 100, 2 );
function event_button( $template_name, $vars ) {

	if (! array_key_exists('course_id', $vars)) {
		return $template_name;
	}

	if ( $template_name === '/stm-lms-templates/global/buy-button.php' && is_course_event($vars['course_id']) ) {
		$template_name = '/stm-lms-templates/buy-button/mixed1.php';
	}
	return $template_name;
}

?>