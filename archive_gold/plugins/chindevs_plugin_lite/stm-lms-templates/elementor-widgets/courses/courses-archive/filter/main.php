<div class="ms_lms_courses_archive__filter">
    <a href="#" class="ms_lms_courses_archive__filter_toggle">
        <?php esc_html_e( 'Filters', 'masterstudy-lms-learning-management-system' ); ?>
    </a>
    <form class="ms_lms_courses_archive__filter_form" method="post">
        <div class="ms_lms_courses_archive__filter_options">
            <input type="hidden" name="show_only_lite" value="<?php echo $show_lite_courses; ?>">
            <?php /* if(count($terms)): ?>
                <?php foreach ($terms as $term): ?>
<!--                    <input type="hidden" name="category[]" value="--><?php //echo $term; ?><!--">-->
                <?php endforeach; ?>
            <?php endif; */ ?>
            <?php
			error_log("inside courses-archive flter main");
			error_log($lite_category_name);
            foreach ( $filter_data['filter_options'] as $option ) {
                STM_LMS_Templates::show_lms_template(
                    "elementor-widgets/courses/courses-archive/filter/options/{$option['template']}",
                    array(
                        'option' => $option,
                        'terms'  => $filter_data['terms'],
//                        'terms'  => (!empty($terms)) ? array_map('intval', $terms) : $filter_data['terms'],
                        'metas'  => $filter_data['metas'],
                        'show_lite_courses'  => $show_lite_courses,
						'lite_category_name' => $lite_category_name
//                        'display_terms' => (!empty($terms)) ? array_map('intval', $terms) : []
                    )
                );
            }
            ?>
        </div>
        <div class="ms_lms_courses_archive__filter_actions">
            <input type="submit" value="<?php esc_attr_e( 'Show Results', 'masterstudy-lms-learning-management-system' ); ?>" class="ms_lms_courses_archive__filter_actions_button">
            <a href="#" class="ms_lms_courses_archive__filter_actions_reset">
                <i class="lnr lnr-undo"></i>
                <span><?php esc_html_e( 'Reset All', 'masterstudy-lms-learning-management-system' ); ?></span>
            </a>
        </div>
    </form>
</div>
