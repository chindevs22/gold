<?php

class SLMS_Quiz_Admin {

    public function __construct() {

        add_filter( 'wpcfto_search_posts_response', array( $this, 'questions_modify_search' ), 15, 2 );
        add_filter( 'stm_lms_wpcfto_create_question', array( $this, 'questions_modify_search' ), 15, 2 );

        add_action('stm_lms_before_save_questions', array( $this, 'before_save_questions'), 15, 2);

    }

    public function questions_modify_search( $response, $post_type ) {
        if ( in_array( 'stm-questions', $post_type ) ) {
            $response = array_merge( $response, $this->question_fields( $response['id'] ) );
        }

        return $response;
    }

    public function get_question_fields() {
        return array(
            'type'                 => array(
                'default' => 'single_choice',
            ),
            'answers'              => array(
                'default' => array(),
            ),
            'question'             => array(),
            'question_explanation' => array(),
            'question_hint'        => array(),
            'question_view_type'   => '',
            'image'                => '',
            'slms_points'          => '',
        );
    }

    public function question_fields( $post_id ) {
        $fields = $this->get_question_fields();
        $meta   = array();

        foreach ( $fields as $field_key => $field ) {
            $meta[ $field_key ] = get_post_meta( $post_id, $field_key, true );
            $default            = ( isset( $field['default'] ) ) ? $field['default'] : '';
            $meta[ $field_key ] = ( ! empty( $meta[ $field_key ] ) ) ? $meta[ $field_key ] : $default;
        }

        $meta['opened'] = true;

        if ( ! empty( $meta['answers'] ) && ! empty( $meta['answers'][0] && ! empty( $meta['answers'][0]['categories'] ) ) ) {
            $categories         = $meta['answers'][0]['categories'];
            $checked_categories = array();
            foreach ( $categories as $category ) {
                if ( term_exists( $category['term_id'] ) ) {
                    $checked_categories[] = $category;
                }
            }

            $meta['answers'][0]['categories'] = $checked_categories;
        }

        return $meta;
    }

    public function before_save_questions(){
        $request_body = file_get_contents( 'php://input' );
        if ( ! empty( $request_body ) ) {

            $fields = $this->get_question_fields();

            $data = json_decode($request_body, true);

            foreach ( $data as $question ) {

                if ( empty( $question['id'] ) ) {
                    continue;
                }
                $post_id = $question['id'];

                foreach ( $fields as $field_key => $field ) {

                    if($field_key !== 'slms_points') {
                        continue;
                    }

                    if ( isset( $question[ $field_key ] ) ) {
                        foreach ( $question[ $field_key ] as $index => $value ) {
                            if ( is_array( $question[ $field_key ][ $index ] ) ) {
                                $question[ $field_key ][ $index ]['text'] = sanitize_text_field( wp_slash( $value['text'] ) );
                            }
                        }

                        update_post_meta( $post_id, $field_key, $question[ $field_key ] );
                    }
                }
            }
        }
    }

}

new SLMS_Quiz_Admin();