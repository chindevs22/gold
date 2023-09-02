<?php

class SLMS_Certificate_Builder extends STM_LMS_Certificate_Builder {

    public function __construct() {
        remove_all_actions( 'wp_ajax_stm_get_certificate' );
        add_action( 'wp_ajax_stm_get_certificate', array( $this, 'get_certificate' ) );
    }

    public function get_certificate() {
        check_ajax_referer( 'stm_get_certificate', 'nonce' );

        $id        = '';
        $course_id = '';

        if ( ! empty( $_GET['course_id'] ) && $_GET['course_id'] ) {
            $course_id      = intval( $_GET['course_id'] );
            $certificate_id = get_post_meta( $course_id, 'course_certificate', true );

            if ( ! empty( $certificate_id ) ) {
                $id = $certificate_id;
            } else {
                $terms      = wp_get_post_terms( $course_id, 'stm_lms_course_taxonomy', array( 'fields' => 'ids' ) );
                $meta_query = array(
                    'relation' => 'OR',
                );

                foreach ( $terms as $term ) {
                    $meta_query[] = array(
                        'key'   => 'stm_category',
                        'value' => $term,
                    );
                }

                $meta_query[] = array(
                    'key'   => 'stm_category',
                    'value' => 'entire_site',
                );
                $args         = array(
                    'post_type'      => 'stm-certificates',
                    'posts_per_page' => 1,
                    'meta_query'     => $meta_query,
                    'meta_key'       => 'stm_category',
                    'orderby'        => 'meta_value',
                    'order'          => 'ASC',
                );

                $query = new WP_Query( $args );

                if ( $query->have_posts() ) {
                    while ( $query->have_posts() ) {
                        $query->the_post();
                        $id = get_the_ID();
                    }
                }

                wp_reset_postdata();
            }
        }

        if ( empty( $id ) && ! empty( $_GET['post_id'] ) ) {
            $id = intval( $_GET['post_id'] );
        }

        if ( ! empty( $id ) ) {
            $certificate     = array();
            $orientation     = get_post_meta( $id, 'stm_orientation', true );
            $fields          = get_post_meta( $id, 'stm_fields', true );
            $image           = get_post_thumbnail_id( $id );
            $current_user_id = get_current_user_id();

            if ( empty( $fields ) ) {
                $fields = array();
            } else {
                $fields = json_decode( $fields, true );
            }
            if ( empty( $orientation ) ) {
                $orientation = 'landscape';
            }

            $base64     = false;
            $image_size = false;

            if ( $image ) {
                $image_file = get_attached_file( $image );
                $type       = pathinfo( $image_file, PATHINFO_EXTENSION );
                $image_data = file_get_contents( $image_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions
                $base64     = 'data:image/' . $type . ';base64,' . base64_encode( $image_data ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
                $image_size = getimagesizefromstring( $image_data );
            }

            $fields_with_data = array();

            foreach ( $fields as $field ) {
                if ( 'image' === $field['type'] && ! empty( $field['imageId'] ) ) {
                    $field['image_data'] = $this->encode_base64( $field['imageId'] );
                } elseif ( 'course_name' === $field['type'] && ! empty( $course_id ) ) {
                    $field['content'] = html_entity_decode( get_the_title( $course_id ) );
                } elseif ( 'author' === $field['type'] && ! empty( $course_id ) ) {
                    $author           = get_post_field( 'post_author', $course_id );
                    $author_name      = get_the_author_meta( 'display_name', $author );
                    $field['content'] = $author_name;
                } elseif ( 'student_name' === $field['type'] && ! empty( $course_id ) ) {
                    $current_user = wp_get_current_user();
                    if ( ! empty( $current_user ) ) {
                        $first_name       = get_user_meta( $current_user->ID, 'first_name', true );
                        $last_name        = get_user_meta( $current_user->ID, 'last_name', true );
                        $field['content'] = $current_user->display_name;
                        if ( ! empty( $first_name ) || ! empty( $last_name ) ) {
                            $field['content']  = ! empty( $first_name ) ? "$first_name " : '';
                            $field['content'] .= $last_name;
                        }
                    }
                } elseif ( 'start_date' === $field['type'] && ! empty( $course_id ) ) {
                    $start_date = stm_lms_get_user_course( $current_user_id, $course_id, array( 'start_time' ) );
                    if ( ! empty( $start_date ) ) {
                        $start_date = STM_LMS_Helpers::simplify_db_array( $start_date );
                        if ( ! empty( $start_date['start_time'] ) ) {
                            $field['content'] = date_i18n( 'j F Y', $start_date['start_time'] );
                        }
                    }
                } elseif ( 'end_date' === $field['type'] && ! empty( $course_id ) ) {
                    $end_date = get_user_meta( $current_user_id, 'last_progress_time', true );
                    if ( ! empty( $end_date[ $course_id ] ) ) {
                        $field['content'] = date_i18n( 'j F Y', $end_date[ $course_id ] );
                    }
                } elseif ( 'current_date' === $field['type'] && ! empty( $course_id ) ) {
                    $field['content'] = date_i18n( 'j F Y', time() );
                } elseif ( 'progress' === $field['type'] && ! empty( $course_id ) ) {
                    $progress = stm_lms_get_user_course( $current_user_id, $course_id, array( 'progress_percent' ) );
                    if ( ! empty( $progress ) ) {
                        $progress = STM_LMS_Helpers::simplify_db_array( $progress );
                        if ( ! empty( $progress['progress_percent'] ) ) {
                            $field['content'] = $progress['progress_percent'] . '%';
                        }
                    }
                } elseif ( 'co_instructor' === $field['type'] && ! empty( $course_id ) ) {
                    $co_instructor = get_post_meta( $course_id, 'co_instructor', true );
                    if ( ! empty( $co_instructor ) ) {
                        $co_instructor_data = get_userdata( $co_instructor );
                        if ( $co_instructor_data ) {
                            $co_instructor_name = $co_instructor_data->data->display_name;
                            $field['content']   = $co_instructor_name;
                        }
                    } else {
                        $field['content'] = '';
                    }
                } elseif ( 'details' === $field['type'] && ! empty( $course_id ) ) {
                    $curriculum_info  = STM_LMS_Course::curriculum_info( $course_id );
                    $field['content'] = sprintf(
                    /* translators: %s: number */
                        esc_html__( '%1$s Lessons, %2$s Quizzes', 'masterstudy-lms-learning-management-system-pro' ),
                        $curriculum_info['lessons'],
                        $curriculum_info['quizzes']
                    );
                } elseif ( 'code' === $field['type'] && ! empty( $course_id ) ) {
                    $field['content'] = get_post_meta( $id, 'code', true );
                } elseif ( 'student_code' === $field['type'] && ! empty( $course_id ) ) {
                    $field['content'] = STM_LMS_Certificates::generate_certificate_user_code( $current_user_id, $course_id );
                } // ChinDevs to add student gpa code
                elseif ( 'student_gpa' === $field['type'] && ! empty( $course_id )) {
                    $field['content'] = chindevs_generate_certificate_student_gpa($current_user_id, $course_id);
                }
                $fields_with_data[] = $field;
            }

            self::store_certificate_data( $id, $current_user_id, $fields_with_data );

            $data                = array(
                'orientation' => $orientation,
                'fields'      => $fields_with_data,
                'image'       => $base64,
                'image_size'  => $image_size,
            );
            $certificate['data'] = $data;

            wp_send_json( $certificate );
        }
    }

}

new SLMS_Certificate_Builder();
