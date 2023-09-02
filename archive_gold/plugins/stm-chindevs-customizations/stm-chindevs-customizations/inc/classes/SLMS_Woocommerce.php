<?php

class SLMS_Woocommerce {

    public static function init(){
        add_filter( 'woocommerce_currency', array(self::class, 'change_currency'), 150 );

        remove_action( 'woocommerce_order_status_completed', array( 'STM_LMS_Woocommerce', 'stm_lms_woocommerce_order_created' ) );
        add_action( 'woocommerce_order_status_completed', array( self::class, 'stm_lms_woocommerce_order_created' ) );
    }

    public static function add_to_cart( $item_id ) {
        $product_id = self::create_product( $item_id );

        if(defined('WC_ABSPATH')) {
            // Load cart functions which are loaded only on the front-end.
            include_once WC_ABSPATH . 'includes/wc-cart-functions.php';
            include_once WC_ABSPATH . 'includes/class-wc-cart.php';
        }

        if ( is_null( WC()->cart ) ) {
            wc_load_cart();
        }

        return WC()->cart->add_to_cart( $product_id );
    }

    public static function create_product( $id ) {
        $product_id = STM_LMS_Woocommerce::has_product( $id );

        $title                  = get_the_title( $id );
//        $price                  = get_post_meta( $id, 'price', true );
//        $sale_price             = get_post_meta( $id, 'sale_price', true );
        $price                  = SLMS_Course_Price::get( $id );
        $sale_price             = SLMS_Course_Price::get_sale( $id );
        $currency               = SLMS_Course_Price::get_currency( $id );
        $sale_price_dates_start = get_post_meta( $id, 'sale_price_dates_start', true );
        $sale_price_dates_end   = get_post_meta( $id, 'sale_price_dates_end', true );
        $thumbnail_id           = get_post_thumbnail_id( $id );
        $now                    = time() * 1000;
        $bundle_price           = ( class_exists( 'STM_LMS_Course_Bundle' ) ) ? STM_LMS_Course_Bundle::get_bundle_price( $id ) : null;
        $bundle_price           = ( $bundle_price <= 0 ) ? null : $bundle_price;

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

        if ( isset( $sale_price_dates_start ) && isset( $sale_price_dates_end ) ) {
            if ( empty( $sale_price_dates_start ) || 'NaN' === $sale_price_dates_start ) {
                $price = ( ! empty( $sale_price ) ) ? $sale_price : $price;

                delete_post_meta( $product_id, '_sale_price_dates_from' );
                delete_post_meta( $product_id, '_sale_price_dates_to' );
            } else {
                $price = ( $now > $sale_price_dates_start && $now < $sale_price_dates_end ) ? $sale_price : $price;

                update_post_meta(
                    $product_id,
                    '_sale_price_dates_from',
                    gmdate( 'Y-m-d', ( $sale_price_dates_start / 1000 ) + 24 * 60 * 60 )
                );
                update_post_meta(
                    $product_id,
                    '_sale_price_dates_to',
                    gmdate( 'Y-m-d', ( $sale_price_dates_end / 1000 ) + 24 * 60 * 60 )
                );
            }
        }

        if ( isset( $price ) ) {
            update_post_meta( $product_id, '_regular_price', $price );
        }

        if ( isset( $sale_price ) ) {
            update_post_meta( $product_id, '_sale_price', $sale_price );
        }

        if ( isset( $price ) ) {
            update_post_meta( $product_id, '_price', $price );
        }

        if ( isset( $bundle_price ) ) {
            update_post_meta( $product_id, 'stm_lms_bundle_price', $bundle_price );
            update_post_meta( $product_id, '_regular_price', $bundle_price );
            update_post_meta( $product_id, '_price', $bundle_price );
        }

        if ( isset( $thumbnail_id ) ) {
            set_post_thumbnail( $product_id, $thumbnail_id );
        }

        wp_set_object_terms( $product_id, 'stm_lms_product', 'product_type' );

        update_post_meta( $id, STM_LMS_Woocommerce::$product_meta_name, $product_id );
        update_post_meta( $product_id, STM_LMS_Woocommerce::$product_meta_name, $id );
        update_post_meta( $product_id, '_sold_individually', 1 );
        update_post_meta( $product_id, '_virtual', 1 );
        update_post_meta( $product_id, '_downloadable', 1 );


        setcookie('slms_currency', $currency, time() + 3600, '/');

        return $product_id;
    }

    public static function change_currency($currency){
        if (isset($_COOKIE['slms_currency'])) {
            $currency = sanitize_text_field($_COOKIE['slms_currency']);
        }
        return $currency;
    }

    public static function stm_lms_woocommerce_order_created( $order_id ){
        $order   = new WC_Order( $order_id );
        $user_id = $order->get_user_id();
        $courses = get_post_meta( $order_id, 'stm_lms_courses', true );

        foreach ( $courses as $course ) {
            if ( get_post_type( $course['item_id'] ) === 'stm-courses' ) {
                //ChinDevs code to redirect Gift Course cart items
                if ( empty( $course['enterprise_id'] ) && empty( $course['gift_course_id']) ) {
                    STM_LMS_Course::add_user_course( $course['item_id'], $user_id, 0, 0 );
                    STM_LMS_Course::add_student( $course['item_id'] );
                }
            }

            do_action( 'stm_lms_woocommerce_order_approved', $course, $user_id );
        }
    }

}

SLMS_Woocommerce::init();