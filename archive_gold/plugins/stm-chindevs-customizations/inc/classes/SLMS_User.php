<?php

class SLMS_User extends STM_LMS_User {

    public static function init() {
        remove_action( 'wp_ajax_stm_lms_save_user_info', 'STM_LMS_User::save_user_info' );
        add_action( 'wp_ajax_stm_lms_save_user_info', 'SLMS_User::save_user_info' );

        add_action( 'personal_options_update', array(self::class, 'save_extra_fields') );
        add_action( 'edit_user_profile_update', array(self::class, 'save_extra_fields') );

        add_action('template_redirect', array(self::class, 'redirect_is_fields_empty'));

        remove_action( 'wp_ajax_stm_lms_get_user_courses', 'STM_LMS_User::get_user_courses' );
        add_action( 'wp_ajax_stm_lms_get_user_courses', array(self::class, 'get_user_courses') );
    }

    public static function save_user_info() {
        check_ajax_referer( 'stm_lms_save_user_info', 'nonce' );

        $user = self::get_current_user();
        if ( empty( $user['id'] ) ) {
            die;
        }
        $user_id = $user['id'];

        $user_data = json_decode( file_get_contents( 'php://input' ), true );

        $new_pass    = ( isset( $user_data['new_pass'] ) ) ? $user_data['new_pass'] : '';
        $new_pass_re = ( isset( $user_data['new_pass_re'] ) ) ? $user_data['new_pass_re'] : '';

        if ( ! empty( $new_pass ) && ! empty( $new_pass_re ) ) {
            if ( $new_pass !== $new_pass_re ) {
                wp_send_json(
                    array(
                        'status'  => 'error',
                        'message' => esc_html__( 'New password do not match', 'masterstudy-lms-learning-management-system' ),
                    )
                );
            } elseif ( strlen( $new_pass ) < 8 ) {
                /* If Password shorter than 8 characters*/
                $r['status']  = 'error';
                $r['message'] = esc_html__( 'Password must have at least 8 characters', 'masterstudy-lms-learning-management-system' );

                wp_send_json( $r );

            } elseif ( strlen( $new_pass ) > 20 ) {
                /* if Password longer than 20 -for some tricky user try to enter long characters to block input.*/
                $r['status']  = 'error';
                $r['message'] = esc_html__( 'Password too long', 'masterstudy-lms-learning-management-system' );

                wp_send_json( $r );

                die;

            } elseif ( ! preg_match( '#[a-z]+#', $new_pass ) ) {
                /* if contains letter */
                $r['status']  = 'error';
                $r['message'] = esc_html__( 'Password must include at least one lowercase letter!', 'masterstudy-lms-learning-management-system' );

                wp_send_json( $r );

                die;

            } elseif ( ! preg_match( '#[0-9]+#', $new_pass ) ) {
                /* if contains number */
                $r['status']  = 'error';
                $r['message'] = esc_html__( 'Password must include at least one number!', 'masterstudy-lms-learning-management-system' );

                wp_send_json( $r );

                die;

            } elseif ( ! preg_match( '#[A-Z]+#', $new_pass ) ) {
                /* if contains CAPS */
                $r['status']  = 'error';
                $r['message'] = esc_html__( 'Password must include at least one capital letter!', 'masterstudy-lms-learning-management-system' );

                wp_send_json( $r );

                die;

            } else {

                $subject = esc_html__( 'Password change', 'masterstudy-lms-learning-management-system' );
                $message = esc_html__( 'Password changed successfully.', 'masterstudy-lms-learning-management-system' );
                STM_LMS_Helpers::send_email(
                    $user['email'],
                    $subject,
                    $message,
                    'stm_lms_password_change'
                );

                wp_set_password( $new_pass, $user_id );
                wp_send_json(
                    array(
                        'relogin' => self::login_page_url(),
                        'status'  => 'success',
                        'message' => esc_html__( 'Password Changed. Re-login now', 'masterstudy-lms-learning-management-system' ),
                    )
                );
            }
        }

        $fields = self::extra_fields();
        $fields = array_merge( $fields, self::additional_fields() );

        /* SLMS IP Info */
        $ip_info = [];
        foreach (self::additional_fields() as $field_name => $field) {
            $field_data = self::get_form_builder_field_by_id($field_name);
            if($field_data && $field_data['slug'] == 'country-field') {
                if ( isset( $user_data[ $field_name ] ) ) {
                    $ip_info['countryCode'] = slms_get_code_by_country($user_data[ $field_name ]);
                    break;
                }
            }
        }


        $data = array();
        $errors = array();
        $field_values = array();
        foreach ( $fields as $field_name => $field ) {
            if ( isset( $user_data[ $field_name ] ) ) {
                if ( ! empty( $field['required'] ) && empty( $user_data[ $field_name ] ) ) {
                    $errors[] = $field_name;
//                    wp_send_json(
//                        array(
//                            'status'  => 'error',
//                            /* translators: %s: field name */
//                            'message' => sprintf( esc_html__( 'Please fill %s field', 'masterstudy-lms-learning-management-system' ), $field['label'] ),
//                        )
//                    );
                } else {
                    $field_values[] = array(
                        'name' => $field_name,
                        'value' => wp_kses_post( $user_data[ $field_name ] )
                    );
                }
//                $new_value = wp_kses_post( $user_data[ $field_name ] );
//                update_user_meta( $user_id, $field_name, $new_value );
//                $data[ $field_name ] = $new_value;
            }
        }

        if(count($errors)) {
            wp_send_json(
                array(
                    'status'  => 'error',
                    'errors'  => $errors,
                    /* translators: %s: field name */
                    'message' => esc_html__( 'Please fill all required fields', 'masterstudy-lms-learning-management-system' ),
                )
            );
        } else {
            if(count($field_values)) {
                foreach ($field_values as $item) {
                    $field_name = $item['name'];
                    update_user_meta( $user_id, $field_name, $item['value'] );
                    $data[ $field_name ] = $item['value'];
                }
            }
        }

        if(!empty($ip_info)) {
            update_user_meta($user_id, 'slms_ip_info', $ip_info);
        }

        /*change nicename*/
        $nicename = '';
        if ( ! empty( $user_data['first_name'] ) ) {
            $nicename = sanitize_text_field( $user_data['first_name'] );
        }
        if ( ! empty( $user_data['last_name'] ) ) {
            $nicename = ( ! empty( $nicename ) ) ? $nicename . ' ' . sanitize_text_field( $user_data['last_name'] ) : sanitize_text_field( $user_data['last_name'] );
        }
        if ( ! empty( $nicename ) ) {
            wp_update_user(
                array(
                    'ID'           => $user_id,
                    'display_name' => $nicename,
                )
            );
        }

        $r = array(
            'data'    => $data,
            'status'  => 'success',
            'message' => esc_html__( 'Successfully saved', 'masterstudy-lms-learning-management-system' ),
        );

        wp_send_json( $r );
    }

    public static function get_form_builder_data(){
        return get_option( 'stm_lms_form_builder_forms', [] );
    }

    public static function get_form_builder_fields(){
        $builder_forms = self::get_form_builder_data();
        $fields = [];

        if(count($builder_forms)) {
            foreach ($builder_forms as $form) {
                if($form['slug'] == 'profile_form') {
                    $fields = $form['fields'];
                    break;
                }
            }
        }

        return $fields;
    }

    public static function get_form_builder_field_by_id($id = ''){

        $fields = self::get_form_builder_fields();
        $key = array_search($id, array_column($fields, 'id'));

        if($key === false || !isset($fields[$key])) return false;

        return $fields[$key];
    }

    public static function save_extra_fields($user_id){
        if ( ! current_user_can( 'edit_user', $user_id ) ) {
            return false;
        }

        /* SLMS IP Info */
        $ip_info = [];
        foreach (self::additional_fields() as $field_name => $field) {
            $field_data = self::get_form_builder_field_by_id($field_name);
            if($field_data && $field_data['slug'] == 'country-field') {
                if ( isset( $_POST[ $field_name ] ) ) {
                    $ip_info['countryCode'] = slms_get_code_by_country($_POST[ $field_name ]);
                    break;
                }
            }
        }

        if(!empty($ip_info)) {
            update_user_meta($user_id, 'slms_ip_info', $ip_info);
        }
    }

    public static function redirect_is_fields_empty(){
        if(is_user_logged_in()) {
            if(current_user_can('administrator')) return;

            $fields = self::get_form_builder_fields();
            $user_id = get_current_user_id();
            $lms_template_current = get_query_var( 'lms_template' );

            $has_empty_field = false;
            $empty_fields = [];

            if(count($fields)) {
                foreach ($fields as $field) {
                    if(isset($field['required']) && !empty($field['required'])) {
                        $meta = STM_LMS_User::get_user_meta($user_id, $field['id']);
                        if(empty($meta)) {
                            $has_empty_field = true;
                            $empty_fields[] = $field['id'];
                        }
                    }
                }
            }

            if($has_empty_field && 'stm-lms-user-settings' != $lms_template_current) {
                $url = site_url() . '/user-account/settings';
                $url = add_query_arg(['msg_error' => 1,'empty_fields' => implode(',',$empty_fields)], $url);
                wp_safe_redirect($url);
            }

        }
    }

    public static function get_user_courses(){
        check_ajax_referer( 'stm_lms_get_user_courses', 'nonce' );

        $offset = ( ! empty( $_GET['offset'] ) ) ? intval( $_GET['offset'] ) : 0;

        $sort = ( ! empty( $_GET['sort'] ) ) ? sanitize_text_field( $_GET['sort'] ) : 0;

        $r = self::_get_user_courses( $offset, $sort );

        wp_send_json( apply_filters( 'stm_lms_get_user_courses_filter', $r ) );
    }

    public static function _get_user_courses( $offset, $sort = 'date_low' ) {
        $user = self::get_current_user();
        if ( empty( $user['id'] ) ) {
            die;
        }
        $user_id = $user['id'];

        $r = array(
            'posts' => array(),
            'total' => false,
        );

        $pp     = get_option( 'posts_per_page' );
        $offset = $offset * $pp;

        $r['offset'] = $offset;

        $sorts = array(
            'date_low'      => 'ORDER BY start_time DESC',
            'date_high'     => 'ORDER BY start_time ASC',
            'progress_low'  => 'ORDER BY progress_percent DESC',
            'progress_high' => 'ORDER BY progress_percent ASC',
        );

        $sort = ( ! empty( $sorts[ $sort ] ) ) ? $sorts[ $sort ] : '';

        $total       = 0;
        $all_courses = stm_lms_get_user_courses( $user_id, '', '', array() );
        foreach ( $all_courses as $course_user ) {
            if ( get_post_type( $course_user['course_id'] ) !== 'stm-courses' ) {
                stm_lms_get_delete_courses( $course_user['course_id'] );
                continue;
            }

            $total++;
        }

        $columns = array( 'course_id', 'current_lesson_id', 'progress_percent', 'start_time', 'status', 'enterprise_id', 'bundle_id' );
        if ( stm_lms_points_column_available() ) {
            array_push( $columns, 'for_points' );
        }
        $courses = stm_lms_get_user_courses(
            $user_id,
            $pp,
            $offset,
            $columns,
            null,
            null,
            $sort
        );

        $referer = basename($_SERVER['HTTP_REFERER']);
        $type_category_terms = [];

        if($referer == 'enrolled-courses') {
            // For Courses & Albums
            $album_category_terms = get_terms('stm_lms_course_taxonomy', array(
                'meta_key' => 'lite_category_name',
                'meta_value' => 'shravana_mangalam',
                'fields' => 'ids',
            ));
            $empty_category_terms = get_terms('stm_lms_course_taxonomy', array(
                'meta_key' => 'lite_category_name',
                'meta_compare' => 'NOT EXISTS',
                'fields' => 'ids',
            ));
            $type_category_terms = array_merge($album_category_terms,$empty_category_terms);
        } elseif($referer == 'enrolled-events'){
            $event_category_terms = get_terms('stm_lms_course_taxonomy', array(
                'meta_key' => 'lite_category_name',
                'meta_value' => 'event',
                'fields' => 'ids',
            ));
            $webinar_category_terms = get_terms('stm_lms_course_taxonomy', array(
                'meta_key' => 'lite_category_name',
                'meta_value' => 'webinar',
                'fields' => 'ids',
            ));
            $type_category_terms = array_merge($event_category_terms,$webinar_category_terms);
        }

        $r['total_posts'] = $total;
        $r['total']       = $total <= $offset + $pp;
        $r['pages']       = ceil( $total / $pp );
        if ( ! empty( $courses ) ) {
            foreach ( $courses as $course ) {
                $id = $course['course_id'];

                if ( get_post_type( $id ) !== 'stm-courses' ) {
                    stm_lms_get_delete_courses( $id );
                    continue;
                }
                if ( ! get_post_status( $id ) ) {
                    continue;
                }

                $post_terms = wp_get_post_terms($id, 'stm_lms_course_taxonomy', ['fields' => 'ids']);

                if($referer == 'enrolled-courses' || $referer == 'enrolled-events') {
                    if (empty(array_intersect($type_category_terms, $post_terms))) {
                        continue;
                    }
                } else {
                    if (!empty(array_intersect($type_category_terms, $post_terms))) {
                        continue;
                    }
                }

                $price      = get_post_meta( $id, 'price', true );
                $sale_price = STM_LMS_Course::get_sale_price( $id );

                if ( empty( $price ) && ! empty( $sale_price ) ) {
                    $price      = $sale_price;
                    $sale_price = '';
                }

                $post_status = STM_LMS_Course::get_post_status( $id );

                $image = ( function_exists( 'stm_get_VC_img' ) ) ? stm_get_VC_img( get_post_thumbnail_id( $id ), '272x161' ) : get_the_post_thumbnail( $id, 'img-300-225' );

                $course['progress_percent'] = ( $course['progress_percent'] > 100 ) ? 100 : $course['progress_percent'];

                if ( 'completed' === $course['status'] ) {
                    $course['progress_percent'] = '100';
                }

                $current_lesson = ( ! empty( $course['current_lesson_id'] ) ) ? $course['current_lesson_id'] : STM_LMS_Lesson::get_first_lesson( $id );

                /* Check for membership expiration*/
                $in_enterprise       = STM_LMS_Order::is_purchased_by_enterprise( $course, $user_id );
                $my_course           = ( get_post_field( 'post_author', $id ) == $user_id );
                $is_free             = ( ! get_post_meta( $id, 'not_single_sale', true ) && empty( STM_LMS_Course::get_course_price( $id ) ) );
                $is_bought           = STM_LMS_Order::has_purchased_courses( $user_id, $id );
                $not_in_membership   = get_post_meta( $id, 'not_membership', true );
                $in_bundle           = ( isset( $course['bundle_id'] ) ) ? empty( $course['bundle_id'] ) : false;
                $membership_level    = ( STM_LMS_Subscriptions::subscription_enabled() ) ? STM_LMS_Subscriptions::membership_plan_available() : false;
                $membership_status   = ( STM_LMS_Subscriptions::subscription_enabled() ) ? STM_LMS_Subscriptions::get_membership_status( get_current_user_id() ) : 'inactive';
                $membership_expired  = ( STM_LMS_Subscriptions::subscription_enabled() && $membership_level && 'expired' == $membership_status && ! $not_in_membership && ! $is_bought && ! $is_free && ! $my_course && ! $in_enterprise && $in_bundle && empty( $course['for_points'] ) );
                $membership_inactive = ( STM_LMS_Subscriptions::subscription_enabled() && $membership_level && 'active' !== $membership_status && 'expired' !== $membership_status && ! $not_in_membership && ! $is_bought && ! $is_free && ! $my_course && ! $in_enterprise && $in_bundle && empty( $course['for_points'] ) );

                ob_start();
                STM_LMS_Templates::show_lms_template(
                    'global/expired_course',
                    array(
                        'course_id'     => $id,
                        'expired_popup' => false,
                    )
                );
                $expiration = ob_get_clean();

                $post = array(
                    'id'                  => $id,
                    'url'                 => get_the_permalink( $id ),
                    'image_id'            => get_post_thumbnail_id( $id ),
                    'title'               => get_the_title( $id ),
                    'link'                => get_the_permalink( $id ),
                    'image'               => $image,
                    'terms'               => wp_get_post_terms( $id, 'stm_lms_course_taxonomy' ),
                    'terms_list'          => stm_lms_get_terms_array( $id, 'stm_lms_course_taxonomy', 'name' ),
                    'views'               => STM_LMS_Course::get_course_views( $id ),
                    'price'               => STM_LMS_Helpers::display_price( $price ),
                    'sale_price'          => STM_LMS_Helpers::display_price( $sale_price ),
                    'post_status'         => $post_status,
                    'progress'            => strval( $course['progress_percent'] ),
                    /* translators: %s: course complete */
                    'progress_label'      => sprintf( esc_html__( '%s%% Complete', 'masterstudy-lms-learning-management-system' ), $course['progress_percent'] ),
                    'current_lesson_id'   => STM_LMS_Lesson::get_lesson_url( $id, $current_lesson ),
                    'course_id'           => $id,
                    'lesson_id'           => $current_lesson,
                    /* translators: %s: start time */
                    'start_time'          => sprintf( esc_html__( 'Started %s', 'masterstudy-lms-learning-management-system' ), date_i18n( get_option( 'date_format' ), $course['start_time'] ) ),
                    'duration'            => get_post_meta( $id, 'duration_info', true ),
                    'expiration'          => $expiration,
                    'is_expired'          => STM_LMS_Course::is_course_time_expired( get_current_user_id(), $id ),
                    'membership_expired'  => $membership_expired,
                    'membership_inactive' => $membership_inactive,
                );

                $r['posts'][] = $post;
            }
        }

        return $r;

    }

}

SLMS_User::init();