<?php

/* Actions */
add_action( 'wp_ajax_stm_lms_add_to_cart_gc', 'add_to_cart_gc' );
add_action( 'stm_lms_woocommerce_order_approved', 'gc_order_approved' );
add_action( 'stm_lms_order_accepted', 'gc_order_accepted', 10, 2 );
add_action( 'stm_lms_order_remove', 'gc_order_removed', 10, 2 );
add_action( 'stm_lms_buy_button_end', 'add_gift_course_button', 10, 1 );
add_action( 'stm_lms_delete_from_cart', 'delete_from_cart_gc', 10, 1 );

/* Filters */
add_filter( 'stm_lms_delete_from_cart_filter', 'delete_from_cart_gc_filter', 10, 1 );
add_filter( 'stm_lms_cart_items_fields', 'gc_cart_items_fields' );
add_filter( 'stm_lms_before_create_order', 'stm_lms_before_create_gc_order', 100, 2 );
add_filter( 'stm_lms_post_types', 'gc_stm_lms_post_types', 10, 1 );
add_filter( 'stm_lms_accept_order', 'stm_lms_accept_order' );
add_filter( 'stm_lms_after_single_item_cart_title', 'after_single_item_cart_title_gc');
add_filter( 'woocommerce_cart_item_name', 'woo_cart_gift_course_name', 999, 3 );

/** ORDER FUNCTIONS **/
function gc_order_accepted( $user_id, $cart_items ) {
error_log("here inside woocommerce order accepted");
	if ( ! empty( $cart_items ) ) {
		foreach ( $cart_items as $cart_item ) {
			if ( ! empty( $cart_item['gift_course'] ) ) {
				/*Get Group Members*/
				$gc_email_id = intval( $cart_item['gift_course'] );
				$emails = get_post_meta( $gc_email_id, 'emails', true );
				add_users_to_course($emails, $cart_item['item_id']);
			}
		}
	}
	/*Delete Cart*/
	stm_lms_get_delete_cart_items( $user_id );
}

function gc_order_approved( $course_data ) {
error_log("here inside woocommerce order approved");
	if ( ! empty( $course_data['gift_course_id'] ) ) {
		error_log(print_r($course_data, true));
		/* Get Group Members */
		$gc_email_id = intval( $course_data['gift_course_id'] );
		$emails = get_post_meta( $gc_email_id, 'emails', true );
		error_log($emails);
		add_users_to_course($emails, $course_data['item_id']);
	}
	// 	Delete Cart
	// 	stm_lms_get_delete_cart_items( $user_id );
}

// TODO: need to re-address ideal functionality
function gc_order_removed( $course_id, $cart_item ) {
	stm_lms_get_delete_cart_items( $user_id );
}

function stm_lms_before_create_gc_order( $order_meta, $cart_item ) {
	if ( ! empty( $cart_item['gift_course_id'] ) ) {
		$order_meta['gift_course_id'] = $cart_item['gift_course_id'];
	}
	return $order_meta;
}

function stm_lms_accept_order() {
	return false;
}

//** CART FUNCTIONS **/
// DELETE FROM CART RELIES on LMS.js and masterstudy-lms-learning-management-system/_core/stm-lms-templates/checkout/items.php
function delete_from_cart_gc_filter() {
	return false;
}

function gc_cart_items_fields( $fields ) {
	$fields[] = 'gift_course';
	return $fields;
}

function check_gift_course_in_cart( $user_id, $item_id, $gc_id, $fields = array() ) {
	global $wpdb;
	$table = stm_lms_user_cart_name( $wpdb );
	$fields = ( empty( $fields ) ) ? '*' : implode( ',', $fields );
	return $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM wp_stm_lms_user_cart WHERE `user_id` = %d AND `item_id` = %d AND `gift_course` = %d",
      $user_id, $item_id, $gc_id), ARRAY_N );
}

function delete_from_cart_gc( $user_id ) {
	$gc_id= ( ! empty( $_GET['gift_course_id'] ) ) ? intval( $_GET['gift_course_id'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	$group_id = ( ! empty( $_GET['group_id'] ) ) ? intval( $_GET['group_id'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    error_log("is there a gc id present?");
    error_log($gc_id);

	$item_id  = intval( $_GET['item_id'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

   // if u have an enterprise and course id
	if ( ! empty( $gc_id ) && ! empty( $item_id ) ) {
		global $wpdb;
		$table = stm_lms_user_cart_name( $wpdb );

		$wpdb->delete(
			$table,
			array(
				'user_id'    => $user_id,
				'item_id'    => $item_id,
				'gift_course' => $gc_id,
			)
		);
	}

	if ( empty( $gc_id ) && empty($group_id) ) {
		error_log("hitting GC else condition");
		stm_lms_get_delete_cart_item( $user_id, $item_id );
	}
}

function add_to_cart_gc() {
	error_log("adding to cart gc");
	if ( ! is_user_logged_in() || empty( $_GET['course_id'] ) ) {
		die;
	}
	$r = array();

	$user     = STM_LMS_User::get_current_user();
	$user_id  = $user['id'];
	$item_id  = intval( $_GET['course_id'] );
	$limit = 3;
	if ( ! empty( $_GET['emails'] ) ) {
		$data['emails'] = array_splice( $_GET['emails'], 0, $limit );
	}

	$emails = ( ! empty( $data['emails'] ) ) ? sanitize_text_field( implode( ',', $data['emails'] ) ) : '';
	// create a post with the email
	$gc_email_id = wp_insert_post(
		array(
			'post_title' => sanitize_text_field("Gift Course for ". $emails),
			'post_content' => sanitize_text_field($emails),
			'post_type'  => 'stm-gc-emails',
			'post_status' => 'publish'
		)
	);

	update_post_meta( $gc_email_id, 'emails', $emails );
	update_post_meta( $gc_email_id, 'author_id', $user_id );

	$gift_course = $gc_email_id;
	$quantity = 1;
	$price = get_price($item_id);
	$is_woocommerce = STM_LMS_Cart::woocommerce_checkout_enabled();

	// check if in cart
	$item_added = count( check_gift_course_in_cart( $user_id, $item_id, $gift_course, array( 'user_cart_id', 'gift_course' ) ) );

	error_log("item already added?");
	error_log($item_added);

	if ( ! $item_added ) {
		error_log("add it to cart!");
		stm_lms_add_user_cart( compact( 'user_id', 'item_id', 'quantity', 'price', 'gift_course' ) );
	}

	if ( ! $is_woocommerce ) {
		$r['text']     = esc_html__( 'Go to Cart', 'masterstudy-lms-learning-management-system-pro' );
		$r['cart_url'] = esc_url( STM_LMS_Cart::checkout_url() );
	} else {
		$product_id = create_product( $item_id );
		error_log("the product ID");
		error_log($product_id);
		// Load cart functions which are loaded only on the front-end.
		include_once WC_ABSPATH . 'includes/wc-cart-functions.php';
		include_once WC_ABSPATH . 'includes/class-wc-cart.php';

		if ( is_null( WC()->cart ) ) {
			wc_load_cart();
		}
		WC()->cart->add_to_cart( $product_id, 1, 0, array(), array( 'gift_course_id' => $gc_email_id ) );

		$r['text']     = esc_html__( 'Go to Cart', 'masterstudy-lms-learning-management-system-pro' );
		$r['cart_url'] = esc_url( wc_get_cart_url() );
	}


	$r['redirect'] = STM_LMS_Options::get_option( 'redirect_after_purchase', false );
	wp_send_json( $r );
}

// update the name of the item in the cart with a "gift" tag
function after_single_item_cart_title_gc( $item ) {
	$gift_course = '';
	$gc_email = get_post_field('post_content', $item['gift_course'] );
	if ( ! empty( $item['gift_course'] ) ) {
		/* translators: %s Title */
		$gift_course = "<span class='enterprise-course-added'> " . sprintf( esc_html__( '%1$sCourse Gift%2$s for user %3$s', 'masterstudy-lms-learning-management-system-pro' ), '<label>', '</label>', '<strong>' . $gc_email . '</strong>' ) . '</span>';
	}
	echo wp_kses_post( $gift_course );
}

function woo_cart_gift_course_name( $title, $cart_item, $cart_item_key ) {
	if ( ! empty( $cart_item['gift_course_id'] ) ) {
		$course_id = $cart_item['gift_course_id'];
		error_log("shud apply tag");
		$sub_title = "<span class='product-enterprise-group'>" . sprintf( esc_html__( 'Course Gift For %s', 'masterstudy-lms-learning-management-system-pro' ), "friends" ) . '</span>';

		$title .= $sub_title;
	}
	return $title;
}

function gc_stm_lms_post_types( $post_types ) {
	$post_types[] = 'stm-gc-emails';

	return $post_types;
}

function add_gift_course_button( $course_id ) {
	$price = get_price( $course_id );
	if ( ! empty( $price ) ) {
		STM_LMS_Templates::show_lms_template( 'gift_courses/buy', compact( 'course_id', 'price' ) );
	}
}

function get_price( $course_id ) {
	return get_post_meta( $course_id, 'price', true );
}

//** USER FUNCTIONS **/
function add_users_to_course($emails, $course_id) {
	$users    = create_group_users( $emails );
	if ( ! empty( $users ) ) {
		foreach ( $users as $id ) {
			STM_LMS_Course::add_user_course( $course_id, $id, 0, 0 );
			STM_LMS_Course::add_student( $course_id );

			//Send Email to Donor
            $user = get_user_by( 'ID', $id );
            $course_title = get_the_title( $course_id );

            gift_course_emails($user, $course_title);
		}
	}
}

function create_group_users( $emails ) {
	error_log("create group users");
	error_log($emails);
	if ( ! is_array( $emails ) ) {
		$emails = array( $emails );
	}

	error_log( print_r( $emails, true ) );

	$userIds = array();
	foreach ( $emails as $email ) {
		error_log("an email");
		error_log($email);
		$user = get_user_by( 'email', $email );
		if ( $user ) {
			array_push($userIds, $user->ID);
			continue;
		}
		/* Create USER (Need to Send Mail) */

		$username = sanitize_title( $email );
		$password = "hariom"; //TODO - $password = wp_generate_password();
		$user_id = wp_create_user($username, $password, $email);
		$wp_user = new WP_User($user_id);
		$wp_user->set_role('subscriber');

		array_push($userIds, $wp_user->ID);
	}
	return $userIds;
}

function create_product( $id ) {
	$product_id = has_product( $id );

	/* translators: %s Title */
	$title        = sprintf( esc_html__( 'Gift for %s', 'masterstudy-lms-learning-management-system-pro' ), get_the_title( $id ) );
	$price        = get_price( $id );
	$thumbnail_id = get_post_thumbnail_id( $id );

	if ( isset( $price ) && '' === $price ) {
		return false;
	}

	$product = array(
		'post_title'  => $title,
		'post_type'   => 'product',
		'post_status' => 'publish',
	);

	if ( $product_id ) {
		$product['ID'] = $product_id;
	}

	$product_id = wp_insert_post( $product );
	error_log($product_id);
	wp_set_object_terms(
		$product_id,
		array( 'exclude-from-catalog', 'exclude-from-search' ),
		'product_visibility'
	);

	if ( ! empty( $price ) ) {
		update_post_meta( $product_id, '_price', $price );
		update_post_meta( $product_id, '_regular_price', $price );
	}

	if ( ! empty( $thumbnail_id ) ) {
		set_post_thumbnail( $product_id, $thumbnail_id );
	}

	wp_set_object_terms( $product_id, 'stm_lms_product', 'product_type' );

	add_post_meta( $id, 'stm_lms_gift_course_id', $product_id ); //unique true?
	add_post_meta( $product_id, 'stm_lms_gift_course_id', $id );

	update_post_meta( $product_id, '_virtual', 1 );
	update_post_meta( $product_id, '_downloadable', 1 );

	return $product_id;
}

function has_product( $id ) {
	 $product_id = get_post_meta( $id , 'stm_lms_gift_course_id', true );
	 if ( empty( $product_id ) ) {
		 error_log("product doesn't exist!");
		 return false;
	 }
	 return $product_id;
}

?>