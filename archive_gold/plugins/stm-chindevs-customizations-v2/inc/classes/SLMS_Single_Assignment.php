<?php

class SLMS_Single_Assignment extends STM_LMS_Single_Assignment {

    public function __construct() {
        remove_all_actions( 'wp_ajax_stm_lms_get_user_assingments' );
        add_action( 'wp_ajax_stm_lms_get_user_assingments', array( $this, 'stm_lms_get_user_assingments' ) );
    }

    public function stm_lms_get_user_assingments() {
        check_ajax_referer( 'stm_lms_get_user_assingments', 'nonce' );

        $assignment_id = intval( $_GET['id'] );
        $status        = sanitize_text_field( $_GET['status'] );

        $page     = intval( $_GET['page'] );
        $per_page = self::per_page();

        $args = array();

        $args['posts_per_page'] = $per_page;
        $args['offset']         = ( $page * $per_page ) - $per_page;

        if ( 'not_passed' === $status ) {
            $args['post_status']  = 'publish';
            $args['meta_query'][] = array(
                'key'     => 'status',
                'value'   => 'not_passed',
                'compare' => '=',
            );
        }

        if ( 'passed' === $status ) {
            $args['post_status']  = 'publish';
            $args['meta_query'][] = array(
                'key'     => 'status',
                'value'   => 'passed',
                'compare' => '=',
            );
        }

        $assignments = self::get_user_assignments( $assignment_id, $args );

        wp_send_json( $assignments );
    }

}

new SLMS_Single_Assignment();