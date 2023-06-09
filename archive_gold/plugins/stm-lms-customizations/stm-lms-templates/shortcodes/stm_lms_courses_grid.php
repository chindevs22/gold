<?php
$style = 'style_1';

$base_color = stm_option( 'secondary_color', '#48a7d4' );
stm_lms_module_styles( 'courses_grid', $style );
stm_lms_module_scripts( 'courses_grid' );

$args = array(
    'per_row'      => $per_row,
    'include_link' => true,
);

if ( ! empty( $posts_per_page ) ) {
    $args['posts_per_page'] = $posts_per_page;
}

$posts = get_posts(array(
    'post_type' => 'stm-courses',
    'numberposts' => -1,
    'post_status' => 'publish',
    'fields' => 'ids'
));

//$total_posts = wp_count_posts( 'stm-courses' )->publish;
$total_posts = count($posts);

if ( ! empty( $taxonomy_default ) ) {
    $args['tax_query'] = array(
        array(
            'taxonomy' => 'stm_lms_course_taxonomy',
            'field'    => 'term_id',
            'terms'    => explode( ', ', $taxonomy_default ),
        ),
    );
}

if ( ! empty( $image_size ) ) {
    $args['image_size'] = $image_size;
}

$nav_color = 'secondary_color';

?>

<div class="stm_lms_courses_grid stm_lms_courses">

    <div class="stm_lms_courses_grid__top <?php echo esc_attr( $hide_top_bar ); ?>">
        <div class="stm_lms_courses_grid__counter">
            <?php if ( ! empty( $title ) ) : ?>
                <h2><?php echo wp_kses_post( $title ); ?></h2>
            <?php else : ?>
                <h3>
                    <?php
                    /* translators: %s Posts Total */
                    echo wp_kses_post( sprintf( __( 'Total: <strong>%s Courses</strong>', 'masterstudy-lms-learning-management-system' ), $total_posts ) );
                    ?>
                    <a class="stm_lms_courses_grid__all" href="<?php echo esc_url( STM_LMS_Course::courses_page_url() ); ?>">
                        <?php esc_html_e( 'View all', 'masterstudy-lms-learning-management-system' ); ?>
                    </a>
                </h3>
            <?php endif; ?>
        </div>
        <div class="stm_lms_courses_grid__sort heading_font stm_lms_grid_sort_module <?php echo esc_attr( $hide_sort ); ?>"
             data-text="<?php esc_attr_e( 'Sort by:', 'masterstudy-lms-learning-management-system' ); ?>">
            <select class="no-search">
                <option value="date_high"><?php esc_html_e( 'Release date (newest first)', 'masterstudy-lms-learning-management-system' ); ?></option>
                <option value="date_low"><?php esc_html_e( 'Release date (oldest first)', 'masterstudy-lms-learning-management-system' ); ?></option>
                <option value="rating"><?php esc_html_e( 'Overall Rating', 'masterstudy-lms-learning-management-system' ); ?></option>
                <option value="popular"><?php esc_html_e( 'Popular (most viewed)', 'masterstudy-lms-learning-management-system' ); ?></option>
            </select>
        </div>
    </div>

    <?php STM_LMS_Templates::show_lms_template( 'courses/grid', array( 'args' => $args ) ); ?>

    <div class="<?php echo esc_attr( $hide_load_more ); ?>">
        <?php STM_LMS_Templates::show_lms_template( 'courses/load_more', array( 'args' => $args ) ); ?>
    </div>

</div>
