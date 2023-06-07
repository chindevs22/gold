<?php

class SLMS_Lite_Category {

    public function __construct()
    {
        add_action('init', array($this, 'add_custom_field_to_term_edit_page'));
        add_action('edited_stm_lms_course_taxonomy', array($this, 'save_custom_field_value'), 10, 2);
        add_action('created_stm_lms_course_taxonomy', array($this, 'save_custom_field_value'), 10, 2);
        add_filter( 'manage_edit-stm_lms_course_taxonomy_columns', array($this, 'add_custom_field_column') );
        add_action( 'manage_stm_lms_course_taxonomy_custom_column', array($this, 'populate_custom_field_column'), 10, 3 );

        add_action('pre_get_posts', array($this, 'exclude_category_from_posts'), 15);

        remove_action( 'wp_ajax_ms_lms_courses_archive_filter', 'ms_lms_courses_archive_filter' );
        remove_action( 'wp_ajax_nopriv_ms_lms_courses_archive_filter', 'ms_lms_courses_archive_filter' );

        remove_action( 'wp_ajax_ms_lms_courses_grid_sorting', 'ms_lms_courses_grid_sorting' );
        remove_action( 'wp_ajax_nopriv_ms_lms_courses_grid_sorting', 'ms_lms_courses_grid_sorting' );

        add_action( 'wp_ajax_ms_lms_courses_archive_filter', array($this, 'courses_archive_filter') );
        add_action( 'wp_ajax_nopriv_ms_lms_courses_archive_filter', array($this, 'courses_archive_filter') );

        add_action( 'wp_ajax_ms_lms_courses_grid_sorting', array($this, 'courses_grid_sorting') );
        add_action( 'wp_ajax_nopriv_ms_lms_courses_grid_sorting', array($this, 'courses_grid_sorting') );

        add_filter( 'stm_lms_menu_items', array($this, 'user_menu_items'), 15 );

        add_filter( 'stm_lms_custom_routes_config', array($this, 'page_routes'), 15 );

        remove_action( 'wp_ajax_stm_lms_get_user_courses', 'STM_LMS_User::get_user_courses' );
        add_action( 'wp_ajax_stm_lms_get_user_courses', array($this, 'get_user_courses') );

    }

    public function add_custom_field_to_term_edit_page() {
        add_action('stm_lms_course_taxonomy_edit_form_fields', array($this, 'render_custom_field'), 10, 2);
        add_action('stm_lms_course_taxonomy_add_form_fields', array($this, 'render_custom_add_form_field'), 10, 2);
    }

    // Render custom field on term edit page
    public function render_custom_field($term, $taxonomy) {
        $field_value = get_term_meta($term->term_id, 'is_lite_category', true);
        $lite_category_name = get_term_meta($term->term_id, 'lite_category_name', true);

        ?>
        <tr class="form-field">
            <th scope="row" valign="top">
                <label for="is_lite_category"><?php _e('Is Lite Category', 'slms'); ?></label>
            </th>
            <td>
                <input type="checkbox" id="is_lite_category" name="is_lite_category" value="1" <?php checked($field_value, '1'); ?> />
                <p class="description"><?php _e('This term will display "Lite" non-certified courses on its own page, will not be part of the course carousel', 'slms'); ?></p>
            </td>
        </tr>
         <tr class="form-field">
            <th scope="row" valign="top">
                <label for="lite_category_name"><?php _e('Lite Category Name', 'slms'); ?></label>
            </th>
            <td>
                <select id="lite_category_name" name="lite_category_name">
                    <option value="event" <?php selected($lite_category_name, 'event'); ?>><?php _e('Event', 'slms'); ?></option>
                    <option value="shravana_mangalam" <?php selected($lite_category_name, 'shravana_mangalam'); ?>><?php _e('Shravana Mangalam', 'slms'); ?></option>
                    <option value="webinar" <?php selected($lite_category_name, 'webinar'); ?>><?php _e('Webinar', 'slms'); ?></option>
                </select>
                <p class="description"><?php _e('The name of the lite category, if applicable', 'slms'); ?></p>
            </td>
        </tr>
        <?php
    }

    // Render custom field on term add form
    public function render_custom_add_form_field($taxonomy) {
        ?>
        <div class="form-field">
            <label for="is_lite_category"><?php _e('Is Lite Category', 'slms'); ?></label>
            <input type="checkbox" id="is_lite_category" name="is_lite_category" value="1" />
            <p class="description"><?php _e('This term will display "Lite" non-certified courses on its own page, will not be part of the course carousel', 'slms'); ?></p>
        </div>
        <div class="form-field">
                <label for="lite_category_name"><?php _e('Lite Category Name', 'slms'); ?></label>
                <select id="lite_category_name" name="lite_category_name">
                    <option value=""><?php _e('Select a Lite Category Name', 'slms'); ?></option>
                    <option value="event"><?php _e('Event', 'slms'); ?></option>
                    <option value="shravana_mangalam"><?php _e('Shravana Mangalam', 'slms'); ?></option>
                    <option value="webinar"><?php _e('Webinar', 'slms'); ?></option>
                </select>
                <p class="description"><?php _e('Select a Lite Category Name for this term', 'slms'); ?></p>
            </div>
        <?php
    }

    public function add_custom_field_column( $columns ) {
        $columns['is_lite_category'] = __('Is Lite Category', 'slms');
        return $columns;
    }

    public function populate_custom_field_column($content, $column_name, $term_id ){
        if ( 'is_lite_category' === $column_name ) {
            $term = get_term_meta($term_id, 'is_lite_category', true);
            $content = (!empty($term)) ? __('Yes','slms') : __('No','slms');
        }
        return $content;
    }

    // Save custom field checkbox value
    public function save_custom_field_value($term_id) {
        // Replace 'custom_field_key' with your custom field key
        if (isset($_POST['is_lite_category'])) {
            update_term_meta($term_id, 'is_lite_category', '1');
        } else {
            delete_term_meta($term_id, 'is_lite_category');
        }

        //ChinDevs Code to also save the category name
        if (isset($_POST['lite_category_name'])) {
            $lite_category_name = $_POST['lite_category_name'];
            update_term_meta($term_id, 'lite_category_name', $lite_category_name);
        } else {
            delete_term_meta($term_id, 'lite_category_name');
        }
    }

    public function exclude_category_from_posts($query)
    {
        if( !wp_doing_ajax() ) {
            if(is_admin()) {
                return;
            }
            if(is_tax('stm_lms_course_taxonomy')) {
                return;
            }
        }

        if ($query->is_main_query() || $query->get('post_type') == 'stm-courses') {

            $meta_key = 'is_lite_category';
            $meta_value = '1';

            // Modify the taxonomy query to include term meta
            $tax_query = $query->get('tax_query');
            if (!$tax_query) {
                $tax_query = array();
            }

            if (empty($tax_query)) {
                $tax_query[] = array(
                    'taxonomy' => 'stm_lms_course_taxonomy',
                    'field' => 'term_id',
                    'terms' => get_terms('stm_lms_course_taxonomy', array(
                        'meta_key' => $meta_key,
                        'meta_value' => $meta_value,
                        'fields' => 'ids',
                    )),
                    'operator' => 'NOT IN',
                );
                $query->set('tax_query', $tax_query);
            }

        }
    }

    public function courses_archive_filter(){
        check_ajax_referer( 'filtering', 'nonce' );

        /* check & sanitize all ajax data */
        $cards_to_show    = ( isset( $_POST['cards_to_show'] ) ) ? intval( $_POST['cards_to_show'] ) : 8;
        $posts_per_page   = ( ! isset( $_POST['cards_to_show_choice'] ) || 'all' === $_POST['cards_to_show_choice'] ) ? -1 : $cards_to_show;
        $current_page     = ( isset( $_POST['current_page'] ) ) ? intval( $_POST['current_page'] ) : false;
        $offset           = ( isset( $_POST['offset'] ) ) ? intval( $_POST['offset'] ) : false;
        $card_style       = ( isset( $_POST['card_template'] ) ) ? sanitize_text_field( wp_unslash( $_POST['card_template'] ) ) : 'card_style_1';
        $pagination_style = ( isset( $_POST['pagination_template'] ) ) ? sanitize_text_field( wp_unslash( $_POST['pagination_template'] ) ) : '';
        $meta_slots       = ( isset( $_POST['meta_slots'] ) ) ? STM_LMS_Helpers::array_sanitize( wp_unslash( $_POST['meta_slots'] ) ) : array();
        $card_data        = ( isset( $_POST['card_data'] ) ) ? STM_LMS_Helpers::array_sanitize( wp_unslash( $_POST['card_data'] ) ) : array();
        $popup_data       = ( isset( $_POST['popup_data'] ) ) ? STM_LMS_Helpers::array_sanitize( wp_unslash( $_POST['popup_data'] ) ) : array();
        $sort_by          = ( isset( $_POST['sort_by'] ) ) ? sanitize_text_field( wp_unslash( $_POST['sort_by'] ) ) : '';
        $sort_by_cat      = ( isset( $_POST['sort_by_cat'] ) ) ? sanitize_text_field( wp_unslash( $_POST['sort_by_cat'] ) ) : '';
        $sort_by_default  = ( isset( $_POST['sort_by_default'] ) ) ? sanitize_text_field( wp_unslash( $_POST['sort_by_default'] ) ) : '';

        $terms  =  ( isset( $_POST['args']['terms'] ) ) ? $_POST['args']['terms'] : [];

        $show_only_lite_courses  =  ( isset( $_POST['args']['show_only_lite'] ) ) ? sanitize_text_field($_POST['args']['show_only_lite']) : '';

		//Chindevs code to get the lite category name from ajax call
        $lite_category_name = ( isset($_POST['args']['lite_category_name'] ) ) ? sanitize_text_field($_POST['args']['lite_category_name']) : '';
		error_log("inside the slms lite category php 170");
		error_log("Lite Cat Name: " . $lite_category_name);

        /* query courses */
        $default_args = array(
            'posts_per_page' => $posts_per_page,
            'meta_query'     => array(
                'relation' => 'AND',
                'featured' => array(
                    'relation' => 'OR',
                    array(
                        'key'     => 'featured',
                        'value'   => 'on',
                        'compare' => '!=',
                    ),
                    array(
                        'key'     => 'featured',
                        'compare' => 'NOT EXISTS',
                    ),
                ),
            ),
        );
        if ( ! empty( $current_page ) ) {
            $default_args['paged'] = $current_page;
        }
        if ( ! empty( $offset ) ) {
            $default_args['offset'] = $offset;
        }
        if ( ! empty( $sort_by_cat ) && 'all' !== $sort_by ) {
            $default_args['tax_query'] = array(
                array(
                    'taxonomy' => 'stm_lms_course_taxonomy',
                    'field'    => 'id',
                    'terms'    => $sort_by,
                ),
            );
            $sort_by                   = $sort_by_default;
        }
        if ( 'all' === $sort_by ) {
            $sort_by = $sort_by_default;
        }

        $default_args = apply_filters( 'stm_lms_filter_courses', $default_args, array(), array(), $sort_by );

        if (!empty($default_args['tax_query'])) {
            $default_args['tax_query']['relation'] = 'AND';
        }

        if(!empty($terms)) {
            $tax_query_terms = array(
                'taxonomy' => 'stm_lms_course_taxonomy',
                'field' => 'term_id',
                'terms' => $terms,
                'operator' => 'IN',
            );
            $default_args['tax_query']['relation'] = 'AND';
            $default_args['tax_query'][] = $tax_query_terms;
        }

		//Chindevs code to also filter on the category name
        if(!empty($show_only_lite_courses)) {
            $tax_query = array(
                'taxonomy' => 'stm_lms_course_taxonomy',
                'field' => 'term_id',
                'terms' =>  get_terms('stm_lms_course_taxonomy', array(
                   'meta_query' => array(
                       'relation' => 'AND',
                       array(
                           'key' => 'is_lite_category',
                           'value' => '1',
                       ),
                       array(
                           'key' => 'lite_category_name',
                           'value' => $lite_category_name,
                       ),
                   ),
                   'fields' => 'ids',
               )),
                'operator' => 'IN',
            );
        } else {
            $tax_query = array(
                'taxonomy' => 'stm_lms_course_taxonomy',
                'field' => 'term_id',
                'terms' => get_terms('stm_lms_course_taxonomy', array(
                    'meta_key' => 'is_lite_category',
                    'meta_value' => '1',
                    'fields' => 'ids',
                )),
                'operator' => 'NOT IN',
            );
        }

        $default_args['tax_query'][] = $tax_query;

        if ( 0 !== $posts_per_page ) {
            $courses = \STM_LMS_Courses::get_all_courses( $default_args );
        }

        /* content send*/
        $response = array();
        if ( ! empty( $courses ) && is_array( $courses ) ) {
            $response['cards'] = STM_LMS_Templates::load_lms_template(
                "elementor-widgets/courses/card/{$card_style}/main",
                array(
                    'courses'             => ( isset( $courses['posts'] ) ) ? $courses['posts'] : array(),
                    'meta_slots'          => $meta_slots,
                    'card_data'           => $card_data,
                    'popup_data'          => $popup_data,
                    'course_card_presets' => $card_style,
                )
            );
            if ( ! empty( $pagination_style ) && $courses['total_pages'] > 1 ) {
                $response['pagination'] = STM_LMS_Templates::load_lms_template(
                    "elementor-widgets/courses/courses-grid/pagination/{$pagination_style}",
                    array(
                        'pagination_data' => array(
                            'current_page'   => $current_page,
                            'total_pages'    => $courses['total_pages'],
                            'total_posts'    => $courses['total_posts'],
                            'posts_per_page' => $posts_per_page,
                            'offset'         => $posts_per_page + $offset,
                        ),
                    )
                );
            }
        }
        wp_send_json( $response );
    }


    public function courses_grid_sorting() {
        check_ajax_referer( 'filtering', 'nonce' );

        /* check & sanitize all ajax data */
        $cards_to_show    = ( isset( $_POST['cards_to_show'] ) ) ? intval( $_POST['cards_to_show'] ) : 8;
        $posts_per_page   = ( ! isset( $_POST['cards_to_show_choice'] ) || 'all' === $_POST['cards_to_show_choice'] ) ? -1 : $cards_to_show;
        $current_page     = ( isset( $_POST['current_page'] ) ) ? intval( $_POST['current_page'] ) : false;
        $offset           = ( isset( $_POST['offset'] ) ) ? intval( $_POST['offset'] ) : false;
        $card_style       = ( isset( $_POST['card_template'] ) ) ? sanitize_text_field( wp_unslash( $_POST['card_template'] ) ) : 'card_style_1';
        $pagination_style = ( isset( $_POST['pagination_template'] ) ) ? sanitize_text_field( wp_unslash( $_POST['pagination_template'] ) ) : '';
        $meta_slots       = ( isset( $_POST['meta_slots'] ) ) ? STM_LMS_Helpers::array_sanitize( wp_unslash( $_POST['meta_slots'] ) ) : array();
        $card_data        = ( isset( $_POST['card_data'] ) ) ? STM_LMS_Helpers::array_sanitize( wp_unslash( $_POST['card_data'] ) ) : array();
        $popup_data       = ( isset( $_POST['popup_data'] ) ) ? STM_LMS_Helpers::array_sanitize( wp_unslash( $_POST['popup_data'] ) ) : array();
        $sort_by          = ( isset( $_POST['sort_by'] ) ) ? sanitize_text_field( wp_unslash( $_POST['sort_by'] ) ) : '';
        $sort_by_cat      = ( isset( $_POST['sort_by_cat'] ) ) ? sanitize_text_field( wp_unslash( $_POST['sort_by_cat'] ) ) : '';
        $sort_by_default  = ( isset( $_POST['sort_by_default'] ) ) ? sanitize_text_field( wp_unslash( $_POST['sort_by_default'] ) ) : '';

        $show_only_lite_courses  =  ( isset( $_POST['args']['show_only_lite'] ) ) ? sanitize_text_field($_POST['args']['show_only_lite']) : '';
		//Chindevs code to get the lite category name from ajax call
        $lite_category_name = ( isset($_POST['args']['lite_category_name'] ) ) ? sanitize_text_field($_POST['args']['lite_category_name']) : '';
		error_log("inside the slms lite category php 321");
		error_log($lite_category_name);

        /* query courses */
        $default_args = array(
            'posts_per_page' => $posts_per_page,
            'meta_query'     => array(
                'relation' => 'AND',
                'featured' => array(
                    'relation' => 'OR',
                    array(
                        'key'     => 'featured',
                        'value'   => 'on',
                        'compare' => '!=',
                    ),
                    array(
                        'key'     => 'featured',
                        'compare' => 'NOT EXISTS',
                    ),
                ),
            ),
        );
        if ( ! empty( $current_page ) ) {
            $default_args['paged'] = $current_page;
        }
        if ( ! empty( $offset ) ) {
            $default_args['offset'] = $offset;
        }
        if ( ! empty( $sort_by_cat ) && 'all' !== $sort_by ) {
            $default_args['tax_query'] = array(
                array(
                    'taxonomy' => 'stm_lms_course_taxonomy',
                    'field'    => 'id',
                    'terms'    => $sort_by,
                ),
            );
            $sort_by                   = $sort_by_default;
        }
        if ( 'all' === $sort_by ) {
            $sort_by = $sort_by_default;
        }
        $default_args = apply_filters( 'stm_lms_filter_courses', $default_args, array(), array(), $sort_by );

        if (!empty($default_args['tax_query'])) {
            $default_args['tax_query']['relation'] = 'AND';
        }

        if(!empty($show_only_lite_courses)) {
            $tax_query = array(
                'taxonomy' => 'stm_lms_course_taxonomy',
                'field' => 'term_id',
                'terms' => get_terms('stm_lms_course_taxonomy', array(
                   'meta_query' => array(
                       'relation' => 'AND',
                       array(
                           'key' => 'is_lite_category',
                           'value' => '1',
                       ),
                       array(
                           'key' => 'lite_category_name',
                           'value' => $lite_category_name,
                       ),
                   ),
                   'fields' => 'ids',
               )),
                'operator' => 'IN',
            );
        } else {
            $tax_query = array(
                'taxonomy' => 'stm_lms_course_taxonomy',
                'field' => 'term_id',
                'terms' => get_terms('stm_lms_course_taxonomy', array(
                    'meta_key' => 'is_lite_category',
                    'meta_value' => '1',
                    'fields' => 'ids',
                )),
                'operator' => 'NOT IN',
            );
        }

        $default_args['tax_query'][] = $tax_query;

        if ( 0 !== $posts_per_page ) {
            $courses = \STM_LMS_Courses::get_all_courses( $default_args );
        }

        /* content send*/
        $response = array();
        if ( ! empty( $courses ) && is_array( $courses ) ) {
            $response['cards'] = STM_LMS_Templates::load_lms_template(
                "elementor-widgets/courses/card/{$card_style}/main",
                array(
                    'courses'             => ( isset( $courses['posts'] ) ) ? $courses['posts'] : array(),
                    'meta_slots'          => $meta_slots,
                    'card_data'           => $card_data,
                    'popup_data'          => $popup_data,
                    'course_card_presets' => $card_style,
                )
            );
            if ( ! empty( $pagination_style ) && $courses['total_pages'] > 1 ) {
                $response['pagination'] = STM_LMS_Templates::load_lms_template(
                    "elementor-widgets/courses/courses-grid/pagination/{$pagination_style}",
                    array(
                        'pagination_data' => array(
                            'current_page'   => $current_page,
                            'total_pages'    => $courses['total_pages'],
                            'total_posts'    => $courses['total_posts'],
                            'posts_per_page' => $posts_per_page,
                            'offset'         => $posts_per_page + $offset,
                        ),
                    )
                );
            }
        }
        wp_send_json( $response );
    }


    public static function enrolled_lite_url() {
        return STM_LMS_User::login_page_url() . 'enrolled-lite';
    }

    public function page_routes($page_routes){
        $page_routes['user_url']['sub_pages']['enrolled_lite'] = array(
            'template'  => 'stm-lms-user-lite-courses',
            'protected' => true,
            'url'       => 'enrolled-lite',
        );

        return $page_routes;
    }


    public function user_menu_items($menus){

        $menus[] = array(
            'order'        => 110,
            'id'           => 'enrolled_lite',
            'slug'         => 'enrolled-lite',
            'lms_template' => 'stm-lms-user-lite-courses',
            'menu_title'   => esc_html__( 'Enrolled Lite', 'slms' ),
            'menu_icon'    => 'fa-book',
            'menu_url'     => self::enrolled_lite_url(),
            'menu_place'   => 'learning',
        );

        return $menus;
    }

    public function get_user_courses(){
        check_ajax_referer( 'stm_lms_get_user_courses', 'nonce' );

        $offset = ( ! empty( $_GET['offset'] ) ) ? intval( $_GET['offset'] ) : 0;

        $sort = ( ! empty( $_GET['sort'] ) ) ? sanitize_text_field( $_GET['sort'] ) : 0;

        $referer = basename($_SERVER['HTTP_REFERER']);

        $r = self::_get_user_courses( $offset, $sort, $referer );

        wp_send_json( apply_filters( 'stm_lms_get_user_courses_filter', $r ) );
    }

    public static function _get_user_courses( $offset, $sort = 'date_low', $referer = '' ) {
        $user = STM_LMS_User::get_current_user();
        if ( empty( $user['id'] ) ) {
            die;
        }

        $lite_terms = get_terms('stm_lms_course_taxonomy', array(
            'meta_key' => 'is_lite_category',
            'meta_value' => '1',
            'fields' => 'ids',
        ));

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

                if($referer == 'enrolled-lite') {
                    if (empty(array_intersect($lite_terms, $post_terms))) {
                        continue;
                    }
                } else {
                    if (!empty(array_intersect($lite_terms, $post_terms))) {
                        continue;
                    }
                }

//                $price      = get_post_meta( $id, 'price', true );
//                $sale_price = STM_LMS_Course::get_sale_price( $id );

                $price             = SLMS_Course_Price::get( $id );
                $sale_price        = SLMS_Course_Price::get_sale( $id );

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
                    'terms'               => stm_lms_get_terms_array( $id, 'stm_lms_course_taxonomy', false, true ),
                    'terms_list'          => stm_lms_get_terms_array( $id, 'stm_lms_course_taxonomy', 'name' ),
                    'views'               => STM_LMS_Course::get_course_views( $id ),
                    'price'               => SLMS_Course_Price::display_price( $price ),
                    'sale_price'          => SLMS_Course_Price::display_price( $sale_price ),
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

new SLMS_Lite_Category();