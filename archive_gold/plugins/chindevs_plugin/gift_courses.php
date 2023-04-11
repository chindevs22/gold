<?php
// if ( class_exists( 'STM_LMS_Woocommerce_Courses_Admin' ) && STM_LMS_Cart::woocommerce_checkout_enabled() ) {
// 	new STM_LMS_Woocommerce_Courses_Admin(
// 		'gift_course',
// 		esc_html__( 'Gift Course LMS Products', 'masterstudy-lms-learning-management-system-pro' ),
// 		$gift_meta_key
// 	);
// }
//

/* Actions */
add_action( 'wp_ajax_stm_lms_add_to_cart_gc', 'add_to_cart_gc' );
add_action( 'stm_lms_woocommerce_order_approved', 'stm_lms_woocommerce_gc_order_approved' );
add_action( 'stm_lms_order_accepted', 'gc_order_accepted', 10, 2 );
add_action( 'stm_lms_order_remove', 'gc_order_removed', 10, 2 );
add_action( 'stm_lms_buy_button_end', 'add_gift_course_button', 10, 1 );
add_action( 'stm_lms_delete_from_cart', 'delete_from_cart_gc', 10, 1 );

/* Filters */
add_filter( 'stm_lms_delete_from_cart_filter', 'delete_from_cart_gc_filter', 10, 1 );
add_filter( 'stm_lms_cart_items_fields', 'gc_cart_items_fields' );
add_filter( 'stm_lms_before_create_order', 'stm_lms_before_create_gc_order', 100, 2 );
add_filter( 'stm_lms_post_types', 'gc_stm_lms_post_types', 10, 1 );


// NEED TO UPDATE THESE TWO FCNS
function gc_order_accepted( $user_id, $cart_items ) {
	error_log("inside order method");
// 	if ( ! empty( $cart_items ) ) {
// 		foreach ( $cart_items as $cart_item ) {

// 			if ( ! empty( $cart_item['enterprise'] ) ) {
// 				/*Get Group Members*/
// 				$group_id = intval( $cart_item['enterprise'] );

// 				$users = self::get_group_users( $group_id );

// 				if ( ! empty( $users ) ) {
// 					foreach ( $users as $id ) {
// 						STM_LMS_Course::add_user_course( $cart_item['item_id'], $id, 0, 0, false, $group_id );
// 						STM_LMS_Course::add_student( $cart_item['item_id'] );
// 					}
// 				}
// 			} else {
// 				STM_LMS_Course::add_user_course( $cart_item['item_id'], $user_id, 0, 0 );
// 				STM_LMS_Course::add_student( $cart_item['item_id'] );
// 			}
// 		}
// 	}
	/*Delete Cart*/
	stm_lms_get_delete_cart_items( $user_id );
}

// NEED TO UPDATE THESE TWO FCNS
function gc_order_removed( $course_id, $cart_item ) {
// 	if ( ! empty( $cart_item['enterprise'] ) ) {
// 		$group_id = intval( $cart_item['enterprise'] );

// 		$users = self::get_group_users( $group_id );

// 		if ( ! empty( $users ) ) {
// 			foreach ( $users as $id ) {
// 				global $wpdb;
// 				$table = stm_lms_user_courses_name( $wpdb );

// 				$wpdb->delete(
// 					$table,
// 					array(
// 						'user_id'       => $id,
// 						'course_id'     => $course_id,
// 						'enterprise_id' => $group_id,
// 					)
// 				);
// 			}
// 		}
// 	}

}

function delete_from_cart_gc_filter() {
	return false;
}

function gc_stm_lms_post_types( $post_types ) {
		$post_types[] = 'stm-gc-emails';

		return $post_types;
	}

function gc_cart_items_fields( $fields ) {
	$fields[] = 'gift_course';
	return $fields;
}

function add_gift_course_button( $course_id ) {
	$price = get_price( $course_id );
    error_log("hi trying to add the button");
	if ( ! empty( $price ) ) {
		STM_LMS_Templates::show_lms_template( 'gift_courses/buy', compact( 'course_id', 'price' ) );
	}
}

function get_price( $course_id ) {
	return get_post_meta( $course_id, 'price', true );
}

//  on the existing action where woocomerce approves order
//  add this code this is from includes/classes/class-woocommerce.php
function stm_lms_woocommerce_gc_order_approved( $course_data ) {

	if ( ! empty( $course_data['gift_course_id'] ) ) {
		echo "here inside woocommerce order approved";
		/* Get Group Members */
		$gc_email_id = intval( $course_data['gift_course_id'] );
		$emails = get_post_meta( $gc_email_id, 'emails', true );

		error_log("on order approved");
		error_log(print_r($emails, true));
		$users    = create_group_users( $emails );
		error_log("the users");
		error_log(print_r($users, true));
		if ( ! empty( $users ) ) {
			foreach ( $users as $id ) {
				STM_LMS_Course::add_user_course( $course_data['item_id'], $id, 0, 0, false, 55555 );
				STM_LMS_Course::add_student( $course_data['item_id'] );
			}
		}
	}
}


//todo add back mail function
// from enterprise main.php
function create_group_users( $emails ) {
	error_log("Creating User");
	$userIds = array();
	foreach ( $emails as $email ) {
		error_log("the email");
		error_log($email);
		$user = get_user_by( 'email', $email );

		if ( $user ) {
			array_push($userIds, $user->ID);
			return $userIds;
			continue;
		}

		/*Create User*/
		$username = sanitize_title( $email );
		//$password = "abc";
		$password = wp_generate_password();

		//Create WP User entry
		$user_id = wp_create_user($username, $password, $email);
		//WP User object
		$wp_user = new WP_User($user_id);
		//Set the role of this user to subscriber.
		$wp_user->set_role('subscriber');
		array_push($userIds, $wp_user->ID);
	}
	return $userIds;
}

// called before it creates the order
// adds the stm_lms_courses with course id to the order metadata
// before that add the enterprise_id equal to the $cart_item enterprise_id
function stm_lms_before_create__gc_order( $order_meta, $cart_item ) {
	if ( ! empty( $cart_item['gift_course_id'] ) ) {
		$order_meta['gift_course_id'] = $cart_item['gift_course_id'];
	}
	return $order_meta;
}



/*Product*/
function create_product( $id ) {
	error_log("create product");
	$product_id = has_product( $id );

	/* translators: %s Title */
	$title        = sprintf( esc_html__( 'LATEST GIFT for %s', 'masterstudy-lms-learning-management-system-pro' ), get_the_title( $id ) );
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
	 $product_id = get_post_meta( $id , 'stm_lms_gift_course_id', false );
	 if ( !$product_id || empty( $product_id ) ) {
		 error_log("product doesn't exist!");
		 return false;
	 }
	 return $product_id;
 }

function check_gift_course_in_cart( $user_id, $item_id, $gc_id, $fields = array() ) {
	error_log("inside check gift course in cart");
	global $wpdb;
	$table = stm_lms_user_cart_name( $wpdb );
	error_log("table");
	error_log($table);

	$fields = ( empty( $fields ) ) ? '*' : implode( ',', $fields );
	error_log("prepare");
	return $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM wp_stm_lms_user_cart WHERE `user_id` = %d AND `item_id` = %d AND `gift_course` = %d",
      $user_id, $item_id, $gc_id), ARRAY_N );

}

// delete from the cart table if they remove it from cart
function delete_from_cart_gc( $user_id ) {
	error_log("inside delete from cart GC");

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

	error_log("The emails returned on add to cart");
	error_log(print_r($emails, true));
	// create a post with the email
	$gc_email_id = wp_insert_post(
		array(
			'post_title' => sanitize_text_field("Gift Course for ". $emails),
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
add_filter( 'woocommerce_cart_item_name', 'woo_cart_group_name', 100, 3 );
function woo_cart_group_name( $title, $cart_item, $cart_item_key ) {
	if ( ! empty( $cart_item['gift_course_id'] ) ) {
		$course_id = $cart_item['gift_course_id'];
		$sub_title = "<span class='product-enterprise-group'>" . sprintf( esc_html__( 'Gift for %s', 'masterstudy-lms-learning-management-system-pro' ), "friends" ) . '</span>';

		$title .= $sub_title;
	}
	return $title;
}

add_filter( 'stm_lms_accept_order', 'stm_lms_accept_order' );
function stm_lms_accept_order() {
	return false;
}

?>