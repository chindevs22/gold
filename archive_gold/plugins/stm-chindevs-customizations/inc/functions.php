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
require_once SLMS_PATH . '/inc/classes/SLMS_User.php';
require_once SLMS_PATH . '/inc/classes/SLMS_Points.php';
//require_once SLMS_PATH . '/inc/classes/SLMS_Dashboard_Calendar.php';
require_once SLMS_PATH . '/inc/classes/SLMS_Whatsapp.php';
require_once SLMS_PATH . '/inc/classes/SLMS_Events.php';
require_once SLMS_PATH . '/inc/classes/SLMS_SAARC.php';
require_once SLMS_PATH . '/inc/classes/SLMS_Course_Builder.php';
require_once SLMS_PATH . '/inc/classes/SLMS_Quiz_Builder.php'; //Added by Anjana

add_action('plugins_loaded', function(){
    require_once SLMS_PATH . '/inc/classes/SLMS_Manage_Course.php';
    require_once SLMS_PATH . '/inc/classes/SLMS_Announcements.php';
});

add_action('init', function(){
    require_once SLMS_PATH . '/inc/classes/SLMS_User_Assigment.php';
    require_once SLMS_PATH . '/inc/classes/SLMS_Single_Assignment.php';
    require_once SLMS_PATH . '/inc/classes/SLMS_Assignments.php';
    require_once SLMS_PATH . '/inc/classes/SLMS_Form_Builder.php';
    require_once SLMS_PATH . '/inc/classes/SLMS_Certificate_Builder.php';
});

add_action('wp_enqueue_scripts', function(){

    wp_deregister_script('stm-lms-lms');
    wp_deregister_script('stm-lms-accept_assignment');
    wp_deregister_script('stm-lms-edit_account');
    wp_deregister_script('stm-lms-register');

    wp_register_script( 'stm-lms-lms', SLMS_URL . 'assets/js/lms.js', array(), SLMS_VERSION, true );
    wp_register_script( 'stm-lms-accept_assignment', SLMS_URL . 'assets/js/accept_assignment.js', array(), SLMS_VERSION, true );
    wp_register_script( 'stm-lms-edit_account', SLMS_URL . 'assets/js/edit_account.js', array('vue.js', 'vue-resource.js'), SLMS_VERSION, true );
    wp_register_script( 'stm-lms-register', SLMS_URL . 'assets/js/register.js', array(), SLMS_VERSION, true );

}, 5);

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

add_filter( 'stm_lms_completed_label', function ($completed_label, $item_id, $post_id){
    return $completed_label;

    $show = true;
    $is_lite = false;

    $args = [
        'meta_key' => 'is_lite_category',
        'meta_value' => '1',
        'fields' => 'ids',
    ];
    $lite_terms = wp_get_post_terms( $post_id, 'stm_lms_course_taxonomy', $args );
    if ( ! empty( $lite_terms )) {
        $show = false;
        $is_lite = true;
    }

    if($is_lite) {
        $settings = get_option( 'stm_lms_sequential_drip_content_settings', array() );
        if ( ! empty( $settings['locked'] )) {
            $show = true;
        }

        $course_meta = STM_LMS_Helpers::parse_meta_field( $post_id );

        if(isset($course_meta['drip_content']) && !empty($course_meta['drip_content'])) {
            $drip_content = json_decode($course_meta['drip_content'], true);
            if(count($drip_content)) {
                foreach ($drip_content as $item) {
                    if($item['parent']['id'] == $item_id) {
                        $show = true;
                    }
                }
            }
        }
    }

    return ($show) ? $completed_label : '';
}, 15, 3);


function stmTimeToSeconds($time) {
    $timeInSeconds = strtotime($time);
    $formattedTime = date('H:i:s', $timeInSeconds);
    $timeParts = explode(':', $formattedTime);

    $hours = intval($timeParts[0]) * 3600;
    $minutes = intval($timeParts[1]) * 60;
    $seconds = intval($timeParts[2]);

    return $hours + $minutes + $seconds;
}

function stmSecondsToTime($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);

    return gmdate('H:i', $hours * 3600 + $minutes * 60);
}

/* Changes for search title first and then description
** changes for generating synonyms for search keyword
** Author: Anjana
*/
function custom_search_filter($query) {

    if ($query->is_search) {
        // Modify the search query to give priority to the title.
        $query->set('orderby', 'relevance');
        $query->set('order', 'DESC');

        $searchTerm     = $query->query_vars['s'];
        if(!empty($searchTerm)){
            $probableTerms  = buildProbableSearchTerms($searchTerm);
        }
        // Check if the search term is in the synonyms array
        if (!empty($probableTerms)) {
            // Modify the search query to the synonym
            $query->set('s', implode(' ',$probableTerms));
            wp_enqueue_script('custom-search-notice', SLMS_URL . 'assets/js/custom-search-notice.js', array('jquery'), null, true);
            wp_localize_script('custom-search-notice', 'searchData', array(
                'noticeMessage' => 'Your search results for ' . esc_html(implode(', ',$probableTerms)),'search' => $searchTerm
            ));
        }

    }

    return $query;
}

add_filter('pre_get_posts', 'custom_search_filter', 1);

/**
 * Generating synonyms from search keyword using PHP soundex function
 * Author: Anjana
 */
function buildProbableSearchTerms($searchTerm){

    global $wpdb;

    $custom_post_type = 'stm-courses'; // define your custom post type slug here
    // A sql query to return all post titles
    $results = $wpdb->get_results( $wpdb->prepare( "SELECT post_title FROM {$wpdb->posts} WHERE post_type = %s and post_status = 'publish'", $custom_post_type ), ARRAY_A );

    $wordsArr = [];
    foreach($results as $vals){
        $wordsArr[] = $vals['post_title'];
    }
    $sound_arr = [];
    foreach($wordsArr as $val) {
        $words =  explode(' ',$val);
        foreach($words as $word){
            $soundex_code = soundex($word);
            if (soundex($searchTerm) == $soundex_code) {
                $sound_arr[] = $word;
            }
        }
    }
    return array_unique($sound_arr);
}