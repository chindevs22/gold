<?php

class SLMS_User extends STM_LMS_User {

    public static function init() {
        remove_action( 'wp_ajax_stm_lms_save_user_info', 'STM_LMS_User::save_user_info' );
        add_action( 'wp_ajax_stm_lms_save_user_info', 'SLMS_User::save_user_info' );

        add_action( 'personal_options_update', array(self::class, 'save_extra_fields') );
        add_action( 'edit_user_profile_update', array(self::class, 'save_extra_fields') );
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
        foreach ( $fields as $field_name => $field ) {
            if ( isset( $user_data[ $field_name ] ) ) {
                if ( ! empty( $field['required'] ) && empty( $user_data[ $field_name ] ) ) {
                    wp_send_json(
                        array(
                            'status'  => 'error',
                            /* translators: %s: field name */
                            'message' => sprintf( esc_html__( 'Please fill %s field', 'masterstudy-lms-learning-management-system' ), $field['label'] ),
                        )
                    );
                }
                $new_value = wp_kses_post( $user_data[ $field_name ] );
                update_user_meta( $user_id, $field_name, $new_value );
                $data[ $field_name ] = $new_value;
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

}

SLMS_User::init();