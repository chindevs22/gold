<?php

require_once SLMS_PATH . '/inc/elementor.php';
require_once SLMS_PATH . '/inc/countries.php';
require_once SLMS_PATH . '/inc/classes/SLMS_Lite_Category.php';
require_once SLMS_PATH . '/inc/classes/SLMS_IP_Info.php';
require_once SLMS_PATH . '/inc/classes/SLMS_Course_Price.php';
require_once SLMS_PATH . '/inc/classes/SLMS_Cart.php';
require_once SLMS_PATH . '/inc/classes/SLMS_Woocommerce.php';
require_once SLMS_PATH . '/inc/classes/SLMS_Quiz.php';
require_once SLMS_PATH . '/inc/classes/SLMS_Quiz_Admin.php';
require_once SLMS_PATH . '/inc/classes/SLMS_Instructor.php';
require_once SLMS_PATH . '/inc/classes/SLMS_Popular_Courses.php';
require_once SLMS_PATH . '/inc/classes/SLMS_Enterprise_Price.php';
require_once SLMS_PATH . '/inc/classes/SLMS_User_Quizzes.php';
//require_once SLMS_PATH . '/inc/classes/SLMS_User.php';

add_action('plugins_loaded', function(){
    require_once SLMS_PATH . '/inc/classes/SLMS_Manage_Course.php';
});

add_action('wp_enqueue_scripts', function(){

    wp_deregister_script('ms_lms_courses');

    wp_enqueue_style( 'slms_styles', SLMS_URL . 'assets/css/style.css', array( 'stm_theme_styles' ), SLMS_VERSION, 'all' );

    wp_enqueue_script( 'quiz_extra', SLMS_URL . 'assets/js/quiz_extra.js', array( 'jquery' ), SLMS_VERSION, true );
    wp_register_script( 'ms_lms_courses', SLMS_URL . 'assets/js/elementor-widgets/courses/courses.js', array( 'elementor-frontend' ), SLMS_VERSION, true );

}, 100);


add_filter('stm_lms_template_file', function($path, $template_name){
    if(file_exists(SLMS_PATH.$template_name)) {
        return SLMS_PATH;
    }
    return $path;
}, 100, 2);

add_filter('wpcfto_field_questions_v2', function ($path) {
    if(file_exists(SLMS_PATH . '/settings/questions_v2/field.php' )) {
        return SLMS_PATH . '/settings/questions_v2/field.php';
    }
    return $path;
}, 15);


function slms_questions_v2_load_template($tpl) {
    if(file_exists(SLMS_PATH . "/settings/questions_v2/tpls/{$tpl}.php" )) {
        require SLMS_PATH . "/settings/questions_v2/tpls/{$tpl}.php";
        return;
    }
    require STM_LMS_PATH . "/settings/questions_v2/tpls/{$tpl}.php";
}


function slms_locate_template($templates, $vars = [])
{
    extract($vars);
    $located = false;

    foreach ((array)$templates as $template) {
        if (substr($template, -4) !== '.php') {
            $template .= '.php';
        }

        if (!($located = locate_template('slms/' . $template))) {
            $located = SLMS_PATH . '/templates/' . $template;
        }

        if (file_exists($located)) {
            break;
        }
    }

    return apply_filters('slms_locate_template', $located, $templates);
}


function slms_include_template($template, $vars = []){
    extract($vars);

    $locate_template = slms_locate_template($template, $vars);

    if(file_exists($locate_template)) {
        include($locate_template);
    }
}