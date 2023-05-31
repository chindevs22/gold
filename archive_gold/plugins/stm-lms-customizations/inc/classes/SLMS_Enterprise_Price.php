<?php

class SLMS_Enterprise_Price
{

    public static function init()
    {
        remove_action( 'wp_ajax_stm_lms_add_to_cart_enterprise', array( 'STM_LMS_Enterprise_Courses', 'add_to_cart_enterprise_course' ) );
        add_action( 'wp_ajax_stm_lms_add_to_cart_enterprise', array( self::class, 'add_to_cart_enterprise_course' ) );
    }

    public static function add_to_cart_enterprise_course() {
        check_ajax_referer( 'stm_lms_add_to_cart_enterprise', 'nonce' );

        if ( ! is_user_logged_in() || empty( $_GET['course_id'] ) ) {
            die;
        }
        $r = array();

        $user     = STM_LMS_User::get_current_user();
        $user_id  = $user['id'];
        $item_id  = intval( $_GET['course_id'] );
        $groups   = array_map( 'intval', wp_unslash( $_GET['groups'] ) );
        $quantity = 1;
        $price    = SLMS_Course_Price::get_enterprise($item_id);

        foreach ( $groups as $enterprise ) {
            $is_woocommerce = STM_LMS_Cart::woocommerce_checkout_enabled();

            $item_added = count( STM_LMS_Enterprise_Courses::check_enterprise_in_cart( $user_id, $item_id, $enterprise, array( 'user_cart_id', 'enterprise' ) ) );

            if ( ! $item_added ) {
                stm_lms_add_user_cart( compact( 'user_id', 'item_id', 'quantity', 'price', 'enterprise' ) );
            }

            if ( ! $is_woocommerce ) {
                $r['text']     = esc_html__( 'Go to Cart', 'masterstudy-lms-learning-management-system-pro' );
                $r['cart_url'] = esc_url( STM_LMS_Cart::checkout_url() );
            } else {
                $product_id = self::create_product( $item_id );

                // Load cart functions which are loaded only on the front-end.
                include_once WC_ABSPATH . 'includes/wc-cart-functions.php';
                include_once WC_ABSPATH . 'includes/class-wc-cart.php';

                if ( is_null( WC()->cart ) ) {
                    wc_load_cart();
                }

                error_log("inside enterprise course clicking add to cart");
                error_log(print_r($enterprise, true));
                WC()->cart->add_to_cart( $product_id, 1, 0, array(), array( 'enterprise_id' => $enterprise ) );

                $r['text']     = esc_html__( 'Go to Cart', 'masterstudy-lms-learning-management-system-pro' );
                $r['cart_url'] = esc_url( wc_get_cart_url() );
            }
        }

        $r['redirect'] = STM_LMS_Options::get_option( 'redirect_after_purchase', false );

        wp_send_json( $r );
    }

    /*Product*/
    public static function create_product( $id ) {
        $product_id = STM_LMS_Enterprise_Courses::has_product( $id );

        /* translators: %s Title */
        $title        = sprintf( esc_html__( 'Enterprise for %s', 'masterstudy-lms-learning-management-system-pro' ), get_the_title( $id ) );
        $price        = SLMS_Course_Price::get_enterprise( $id );
        $currency     = SLMS_Course_Price::get_currency( $id );
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

        update_post_meta( $id, STM_LMS_Enterprise_Courses::$enteprise_meta_key, $product_id );
        update_post_meta( $product_id, STM_LMS_Enterprise_Courses::$enteprise_meta_key, $id );

        update_post_meta( $product_id, '_virtual', 1 );
        update_post_meta( $product_id, '_downloadable', 1 );

        setcookie('slms_currency', $currency, time() + 3600, '/');

        return $product_id;
    }

}

SLMS_Enterprise_Price::init();