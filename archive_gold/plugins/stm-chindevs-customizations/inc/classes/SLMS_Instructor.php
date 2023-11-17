<?php

SLMS_Instructor::init();

class SLMS_Instructor extends STM_LMS_Instructor {

    public static function init() {

        remove_action( 'wp_ajax_stm_lms_get_instructor_courses', 'STM_LMS_Instructor::get_courses' );
        add_action( 'wp_ajax_stm_lms_get_instructor_courses', 'SLMS_Instructor::get_courses' );

    }

    public static function search($quote = ''){
        if(empty($quote)) return [];

        global $wpdb;
        $search_query = "SELECT ID FROM {$wpdb->prefix}posts
                         WHERE post_type = 'stm-courses' 
                         AND post_title LIKE %s LIMIT 50";

        $like = '%' . $quote . '%';
        $results = $wpdb->get_results($wpdb->prepare($search_query, $like), ARRAY_A);

        $quote_ids = (count($results)) ? array_column($results, 'ID') : [];
        $quote_ids = array_map('intval', $quote_ids);
        return array_unique($quote_ids);
    }

    public static function get_courses( $args = array(), $return = false, $get_all = false ) {

        if ( ! $return ) {
            check_ajax_referer( 'stm_lms_get_instructor_courses', 'nonce' );
        }

        $user = STM_LMS_User::get_current_user();
        if ( empty( $user['id'] ) ) {
            die;
        }
        $user_id = $user['id'];

        $r = array(
            'posts' => array(),
        );

        $pp     = ( empty( $_GET['pp'] ) ) ? 8 : sanitize_text_field( $_GET['pp'] );
        $offset = ( ! empty( $_GET['offset'] ) ) ? intval( $_GET['offset'] ) : 0;

        $search = ( ! empty( $_GET['search'] ) ) ? sanitize_text_field( $_GET['search'] ) : '';
        $searched = self::search($search);

        $get_ids = ( ! empty( $_GET['ids_only'] ) );

        if ( ! empty( $args['posts_per_page'] ) ) {
            $pp = intval( $args['posts_per_page'] );
        }

        $offset = $offset * $pp;

        $default_args = array(
            'post_type'      => 'stm-courses',
            'posts_per_page' => $pp,
            'post_status'    => array( 'publish', 'draft', 'pending' ),
            'offset'         => $offset,
        );

        if ( ! $get_all ) {
            $default_args['author'] = $user_id;
        }

        $args = wp_parse_args( $args, $default_args );

        if ( empty( $args['s'] ) && ! empty( $_GET['s'] ) ) {
            $args['s'] = sanitize_text_field( $_GET['s'] );
        }

//        if ( empty( $args['search'] ) && ! empty( $_GET['search'] ) ) {
//            $args['search'] = sanitize_text_field( $_GET['search'] );
//        }

        if ( ! empty( $_GET['status'] ) ) {
            $args['post_status'] = sanitize_text_field( $_GET['status'] );
        }

        $q = new WP_Query( $args );

        $total         = $q->found_posts;
        $r['total']    = $total <= $offset + $pp;
        $r['found']    = $total;
        $r['per_page'] = (int) $pp;
        $r['pages']    = (int) ceil( $r['found'] / $r['per_page'] );

        if ( $q->have_posts() ) {

            while ( $q->have_posts() ) {
                $q->the_post();
                $id = get_the_ID();
                if ( $get_ids ) {
                    $r['posts'][ $id ] = get_the_title( $id );
                    continue;
                }

                if(count($searched) && !empty($search)) {
                    if(!in_array($id, $searched)) {
                        continue;
                    }
                }

                $rating  = get_post_meta( $id, 'course_marks', true );
                $rates   = STM_LMS_Course::course_average_rate( $rating );
                $average = $rates['average'];
                $percent = $rates['percent'];

                $status = get_post_status( $id );

//                $price      = get_post_meta( $id, 'price', true );
//                $sale_price = STM_LMS_Course::get_sale_price( $id );

                $price      = SLMS_Course_Price::get( $id );
                $sale_price = SLMS_Course_Price::get_sale( $id );

                if ( empty( $price ) && ! empty( $sale_price ) ) {
                    $price      = $sale_price;
                    $sale_price = '';
                }

                switch ( $status ) {
                    case 'publish':
                        $status_label = esc_html__( 'Published', 'masterstudy-lms-learning-management-system' );
                        break;
                    case 'pending':
                        $status_label = esc_html__( 'Pending', 'masterstudy-lms-learning-management-system' );
                        break;
                    default:
                        $status_label = esc_html__( 'Draft', 'masterstudy-lms-learning-management-system' );
                        break;
                }

                $post_status = STM_LMS_Course::get_post_status( $id );

                $image       = ( function_exists( 'stm_get_VC_img' ) ) ? html_entity_decode( stm_get_VC_img( get_post_thumbnail_id(), '272x161' ) ) : get_the_post_thumbnail( $id, 'img-300-225' );
                $image_small = ( function_exists( 'stm_get_VC_img' ) ) ? html_entity_decode( stm_get_VC_img( get_post_thumbnail_id(), '50x50' ) ) : get_the_post_thumbnail( $id, 'img-300-225' );
                $is_featured = get_post_meta( $id, 'featured', true );

                $rating_count = ( ! empty( $rating ) ) ? count( $rating ) : '';

                $post = array(
                    'id'           => $id,
                    'time'         => get_post_time( 'U', true ),
                    'title'        => get_the_title(),
                    'updated'      => sprintf( esc_html__( 'Last updated: %s', 'masterstudy-lms-learning-management-system' ), stm_lms_time_elapsed_string( get_post( $id )->post_modified ) ),
                    'link'         => get_the_permalink(),
                    'image'        => $image,
                    'image_small'  => $image_small,
                    'terms'        => stm_lms_get_terms_array( $id, 'stm_lms_course_taxonomy', false, true ),
                    'status'       => $status,
                    'status_label' => $status_label,
                    'percent'      => $percent,
                    'is_featured'  => $is_featured,
                    'average'      => $average,
                    'total'        => $rating_count,
                    'views'        => STM_LMS_Course::get_course_views( $id ),
                    'simple_price' => $price,
                    'price'        => SLMS_Course_Price::display_price( $price ),
                    'edit_link'    => apply_filters( 'stm_lms_course_edit_link', admin_url( "post.php?post={$id}&action=edit" ), $id ),
                    'post_status'  => $post_status,
                );

                $post['sale_price'] = ( ! empty( $sale_price ) ) ? STM_LMS_Helpers::display_price( $sale_price ) : '';

                $r['posts'][] = $post;
            }
        }

        wp_reset_postdata();

        if ( $return ) {
            return $r;
        }

        wp_send_json( $r );
    }

}
