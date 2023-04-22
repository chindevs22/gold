<?php

class SLMS_Lite_Category {

    public function __construct()
    {
        add_action('init', array($this, 'add_custom_field_to_term_edit_page'));
        add_action('edited_stm_lms_course_taxonomy', array($this, 'save_custom_field_value'), 10, 2);
        add_action('created_stm_lms_course_taxonomy', array($this, 'save_custom_field_value'), 10, 2);
        add_action('pre_get_posts', array($this, 'exclude_category_from_posts'), 15);

//        add_filter('stm_lms_filter_courses', array($this, 'filter_courses'), 10, 4);

        remove_action( 'wp_ajax_ms_lms_courses_archive_filter', 'ms_lms_courses_archive_filter' );
        remove_action( 'wp_ajax_nopriv_ms_lms_courses_archive_filter', 'ms_lms_courses_archive_filter' );

        remove_action( 'wp_ajax_ms_lms_courses_grid_sorting', 'ms_lms_courses_grid_sorting' );
        remove_action( 'wp_ajax_nopriv_ms_lms_courses_grid_sorting', 'ms_lms_courses_grid_sorting' );

        add_action( 'wp_ajax_ms_lms_courses_archive_filter', array($this, 'courses_archive_filter') );
        add_action( 'wp_ajax_nopriv_ms_lms_courses_archive_filter', array($this, 'courses_archive_filter') );

        add_action( 'wp_ajax_ms_lms_courses_grid_sorting', array($this, 'courses_grid_sorting') );
        add_action( 'wp_ajax_nopriv_ms_lms_courses_grid_sorting', array($this, 'courses_grid_sorting') );

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

//    public function filter_courses($default_args, $terms, $metas, $sort_by){
//        return $default_args;
//    }

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

//        $show_only_lite_courses  = ( isset( $_POST['show_only_lite'] ) ) ? sanitize_text_field( wp_unslash( $_POST['show_only_lite'] ) ) : '';

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

}

new SLMS_Lite_Category();