<?php
/*
Plugin Name: STM LMS Customizations Chindevs
Plugin URI: https://stylemix.net/
Description: Chindevs Lite plugin
Author: Stylemix
Author URI: https://stylemix.net/
Text Domain: slms
Version: 1.0.0
*/

define( 'SLMS_VERSION', '1.0.0' );
define( 'SLMS_PATH', dirname( __FILE__ ) );
define( 'SLMS_URL', plugin_dir_url( __FILE__ ) );
$plugin_path = dirname( __FILE__ );

require_once $plugin_path . '/inc/functions.php';


if ( ! is_textdomain_loaded( 'slms' ) ) {
    load_plugin_textdomain(
        'slms',
        false,
        'slms/languages'
    );
}


function pre_var($var){
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
}

function pre_die($var){
    pre_var($var);
    die();
}

//ChinDevs code: Add Event dates field to backend Admin View
// add_filter('stm_wpcfto_fields', 'stm_lms_event_date_field', 99, 1);

// function stm_lms_event_date_field($fields) {
//     $fields['stm_courses_settings']['section_settings']['fields']['event_dates'] = array(
//         'type'       => 'dates',
//         'label'      => esc_html__( 'Event Dates', 'masterstudy-lms-learning-management-system' ),
//         'sanitize'   => 'wpcfto_save_dates',
//      );
//     return $fields;
// }
