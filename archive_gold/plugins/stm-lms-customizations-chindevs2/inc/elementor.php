<?php

add_action('elementor/widgets/register', function (){
    require_once SLMS_PATH . '/inc/classes/SLMS_Courses.php';
    require_once SLMS_PATH . '/inc/classes/SLMS_Courses_Searchbox.php';
    \Elementor\Plugin::instance()->widgets_manager->unregister( 'ms_lms_courses' );
    \Elementor\Plugin::instance()->widgets_manager->unregister( 'ms_lms_courses_searchbox' );
    \Elementor\Plugin::instance()->widgets_manager->register( new \StmLmsElementor\Widgets\MsLmsCoursesChild() );
    \Elementor\Plugin::instance()->widgets_manager->register( new \StmLmsElementor\Widgets\MsLmsCoursesSearchboxChild() );
});