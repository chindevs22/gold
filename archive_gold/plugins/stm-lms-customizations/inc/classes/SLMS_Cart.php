<?php

SLMS_Cart::init();

class SLMS_Cart extends STM_LMS_Cart {

    public static function init() {
        remove_action( 'wp_ajax_stm_lms_add_to_cart', 'STM_LMS_Cart::add_to_cart' );
        remove_action( 'wp_ajax_nopriv_stm_lms_add_to_cart', 'STM_LMS_Cart::add_to_cart' );

        add_action( 'wp_ajax_stm_lms_add_to_cart', 'SLMS_Cart::add_to_cart', 15 );
        add_action( 'wp_ajax_nopriv_stm_lms_add_to_cart', 'SLMS_Cart::add_to_cart', 15 );
    }

    public static function _add_to_cart( $item_id, $user_id ) {

        $r = array();

        $not_salebale = get_post_meta( $item_id, 'not_single_sale', true );
        if ( $not_salebale ) {
            die;
        }

        $item_meta = STM_LMS_Helpers::parse_meta_field( $item_id );
        $quantity  = 1;
        $price     = self::get_course_price( $item_meta );

        $is_woocommerce = self::woocommerce_checkout_enabled();

        $item_added = count( stm_lms_get_item_in_cart( $user_id, $item_id, array( 'user_cart_id' ) ) );

        if ( ! $item_added ) {
            stm_lms_add_user_cart( compact( 'user_id', 'item_id', 'quantity', 'price' ) );
        }

        if ( ! $is_woocommerce ) {
            $r['text']     = esc_html__( 'Go to Cart', 'masterstudy-lms-learning-management-system' );
            $r['cart_url'] = esc_url( self::checkout_url() );
        } else {
            $r['added']    = SLMS_Woocommerce::add_to_cart( $item_id );
            $r['text']     = esc_html__( 'Go to Cart', 'masterstudy-lms-learning-management-system' );
            $r['cart_url'] = esc_url( wc_get_cart_url() );
        }

        $r['redirect'] = STM_LMS_Options::get_option( 'redirect_after_purchase', false );

        return apply_filters( 'stm_lms_add_to_cart_r', $r, $item_id );
    }

    public static function add_to_cart() {
        check_ajax_referer( 'stm_lms_add_to_cart', 'nonce' );

        if ( ! is_user_logged_in() || empty( $_GET['item_id'] ) ) {
            die;
        }

        $item_id = intval( $_GET['item_id'] );
        $user    = STM_LMS_User::get_current_user();
        $user_id = $user['id'];

        $r = self::_add_to_cart( $item_id, $user_id );

        wp_send_json( $r );
    }

}
