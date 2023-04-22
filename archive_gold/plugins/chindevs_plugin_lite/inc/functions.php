<?php

require_once SLMS_PATH . '/inc/elementor.php';
require_once SLMS_PATH . '/inc/classes/SLMS_Lite_Category.php';

add_action('wp_enqueue_scripts', function(){

    wp_deregister_script('ms_lms_courses');
//    wp_dequeue_script('ms_lms_courses');
//    wp_enqueue_script( 'ms_lms_courses', SLMS_URL. 'assets/js/elementor-widgets/courses/courses.js', ['jquery'], SLMS_VERSION, 'all' );
    wp_register_script( 'ms_lms_courses', SLMS_URL . 'assets/js/elementor-widgets/courses/courses.js', array( 'elementor-frontend' ), SLMS_VERSION, true );

}, 100);


add_filter('stm_lms_template_file', function($path, $template_name){
    if(file_exists(SLMS_PATH.$template_name)) {
        return SLMS_PATH;
    }
    return $path;
}, 10, 2);


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