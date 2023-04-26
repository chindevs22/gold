<?php
/**
 * Plugin Name:       Chindevs Plugin
 * Description:       Code for all changes made by ChinDevs team
 * Version:           1.0.0
 * Author:            Chin Devs
 * Author URI:        https://www.alecrust.com/
 * Text Domain:       chindevs
 */

define( 'CD_TEMP', dirname( __FILE__ ) );
define( 'GIFT_COURSE_URL', plugin_dir_url( __FILE__ ) );

/**
 * Plugin version.
 */

/**
 * Load core plugin class defining all hooks and global variables.
 */

// --------------------------------------------------------------------------------------------
// CHIN DEVS GLOBAL VARIABLES
// --------------------------------------------------------------------------------------------

$sectionToLessonMap = array(); //mgml section ID -> array string "Section Name WP_LESSONID1 WP_LESSONID2"
$lessonToQuestionsMap = array(); // mgml quiz ID -> array of WP question IDS
$lessonMGMLtoWP = array();
$courseMGMLtoWP = array();
$questionMGMLtoWP = array();
$attemptNumberMap = array(); // userID + courseId + quizId -> attempt number for all questions
$wpQuestionsToAnswers = array(); // wp question ID to wp array of answers
$userMGMLtoWP = array();
$randomEmailCounter = 50;
$selfAssessmentToUser = array(); // self assessment id in mgml to user in mgml
$existingMetaMapping = array (
        'billing_address_1' => 'address',
        'billing_city' =>'city',
        'billing_state' => 'state',
        'billing_country' => 'country',
        'billing_postcode' => 'pin_code',
        'billing_phone' => 'phone_no'
    );

$newMetaMapping = array (
     'date_of_birth' => 'dob',
     'gender' => 'gender'
);

//The Hardcoded ID's of the Product Main categories (created from frontend)
$productCategoryMap = array(
    "Publications" => 95,
    "Pendrives" => 96,
    "Combo Offers" => 303
);

//The Hardcoded ID's of the Event Main categories (created from frontend)
$eventCategoryMap = array (
	"Vedanta Sadhaka Course" => 105,
	"Camps and Retreats" => 106,
	"Events" => 107,
	"Textual Workshops" => 108,
	"Seminars and Conferences" => 109,
	"Puja Vidhanam Course" => 110,
	"Upanayanam" => 111
);

//Hardcoded ID of a sample lesson for all events
$templateEventSection = "Event Details 5768";

use \Elementor\Plugin;

require_once  plugin_dir_path(__FILE__) . 'gift_courses.php';
require_once  plugin_dir_path(__FILE__) . 'user_events.php';
require_once  plugin_dir_path(__FILE__) . 'classes/user-event-class.php';

// load all files in functions.php
$cd_functions_dir = plugin_dir_path(__FILE__) . 'cd_functions/';
foreach (glob($cd_functions_dir . '*.php') as $filename) {
    require_once $filename;
}

// load the template files on top of the LMS template path
add_filter('stm_lms_template_file', function($path, $template_name){
    if(file_exists(CD_TEMP.$template_name)) {
        return CD_TEMP;
    }
    return $path;
}, 10, 2);

// register styles (gift_course and user_events
add_action('wp_enqueue_scripts', 'user_events_style');
function user_events_style() {
	    wp_enqueue_style( 'user-events', GIFT_COURSE_URL . '/assets/css/enrolled-events.css', array(), 'false', false);
}

function gift_course_scripts() {
	wp_enqueue_script( 'gift-course-scripts', plugins_url( '/assets/js/gift-course.js', __FILE__ ), array(), false, true );
    wp_enqueue_style( 'gift-course', GIFT_COURSE_URL . '/assets/css/gift-course.css', array(), 'false', false);
}
add_action( 'wp_enqueue_scripts', 'gift_course_scripts' );

/// ------------------- COURSE MIGRATION -------------------------

// All Course Data migration functions
function create_course_data() {
    read_csv("questions.csv", "question");
    read_csv("lesson_combined.csv", "lesson");
    read_csv("course_materials.csv", "course");
    read_csv("users.csv", "user");
    read_csv("user_self_assessment.csv", "userquiz");
    read_csv("user_self_assessment_details.csv", "useranswers");
    read_csv("enrol.csv", "enrol");
	read_csv("publications.csv", "publications");
}
// Course data migration
function read_csv($file_name, $type) {
    //file mapping from our File Manager
    $fileName = "/home/freewaydns-dev108/cd-test-docs/{$file_name}";
    $file = fopen($fileName, 'r');
    $dataArray = array();
    $headerLine = true;
    while (($line = fgetcsv($file)) !== FALSE) {
        // check header line and if so store for the column names
        if($headerLine) {
            $headerLine = false;
            $mappingLine = $line;
            continue;
        }
        // loop through the column values in one row
        $count = 0;
        $tempArray = array();
        // create mapping based on header
        foreach($line as $value) {
            $sanitized_value = preg_replace("/\\\\u([0-9abcdef]{4})/", "&#x$1;", $value);
            $tempArray[$mappingLine[$count++]] = $sanitized_value;
        }
        if ($type == "lesson") {
            create_lesson_from_csv($tempArray);
        } else if ($type == "course") {
            create_course_from_csv($tempArray);
        } else if ($type == "question") {
            create_question_from_csv($tempArray);
        } else if ($type == "user") {
            create_user_from_csv($tempArray);
        } else if ($type == "userquiz") {
            progress_users_quiz_from_csv($tempArray);
        } else if ($type == "useranswers") {
            progress_users_answers_from_csv($tempArray);
        } else if ($type == "enrol") {
            enrol_users_from_csv($tempArray);
        } else if ($type == "publications") {
            create_publications_from_csv($tempArray);
        }
    }
    fclose($file);
}
add_shortcode( 'test-functions', 'create_course_data' );


/// ------------------- EVENT MIGRATION -------------------------
function create_event_data() {
//    read_event_csv("event_lesson.csv", "event_lesson"); //event details for live events the zoom link for lesson 1
    read_event_csv("currentevents_nolessons.csv", "event");
//    read_event_csv("user_event.csv", "user_event");
}
// Course data migration
function read_event_csv($file_name, $type) {
    //file mapping from our File Manager
    $fileName = "/home/freewaydns-dev108/cd-event-docs/{$file_name}";
    $file = fopen($fileName, 'r');
    $dataArray = array();
    $headerLine = true;
    while (($line = fgetcsv($file)) !== FALSE) {
        // check header line and if so store for the column names
        if($headerLine) {
            $headerLine = false;
            $mappingLine = $line;
            continue;
        }
        // loop through the column values in one row
        $count = 0;
        $tempArray = array();
        // create mapping based on header
        foreach($line as $value) {
           $sanitized_value = preg_replace("/\\\\u([0-9abcdef]{4})/", "&#x$1;", $value);
            $tempArray[$mappingLine[$count++]] = $value;
        }
        if ($type == "event_lesson") {
            create_event_lesson_from_csv($tempArray);
        } else if ($type == "event") {
            create_event_from_csv($tempArray);
        } else if ($type == "user_event") {
            create_user_event_from_csv($tempArray);
        }
    }
    fclose($file);
}
add_shortcode( 'test-functions-events', 'create_event_data' );


// FEEDBACK FORM ---- based on form submission
function submit_form_js() {
?>
    <script>
        document.addEventListener( 'wpcf7submit', function( event ) {
          button = document.getElementsByClassName("stm-lms-lesson_navigation_complete")[0];
          button.style.display = "inline";
        }, false );

    </script>
<?php
}

// Hides Complete button on Feedback Form
function hide_complete_button() {
	?>
		<style>.stm-lms-lesson_navigation_complete {display: none;}</style>
	<?php
}
add_action('wp_head', 'submit_form_js');
add_shortcode('shortcodefeedback', 'hide_complete_button'); // required on lesson page

// Add the Assignment field to the backend Admin View
add_filter( 'stm_wpcfto_fields', 'stm_lms_assignment_field', 99, 1);

function stm_lms_assignment_field($fields) {
	$fields['stm_student_assignment']['section_group']['fields']['assignment_grade'] = array(
		'type'  => 'number',
		'label' => esc_html__( 'Assignment Grade', 'masterstudy-lms-learning-management-system-pro' ),
	);
	return $fields;
}

//Add Event dates field to backend Admin View
add_filter('stm_wpcfto_fields', 'stm_lms_event_date_field', 99, 1);

function stm_lms_event_date_field($fields) {
    $fields['stm_courses_settings']['section_settings']['fields']['event_dates'] = array(
        'type'       => 'dates',
        'label'      => esc_html__( 'Event Dates', 'masterstudy-lms-learning-management-system' ),
        'sanitize'   => 'wpcfto_save_dates',
     );
	$fields['stm_courses_settings']['section_accessibility']['fields']['price_nonac'] = array(
		'group'		 => 'started',
        'type'       => 'number',
        'label'       => sprintf(esc_html__( 'Price Non AC (INR)', 'masterstudy-lms-learning-management-system' ),'INR'),
        'placeholder' => sprintf( esc_html__( 'Leave empty if no Non AC Price', 'masterstudy-lms-learning-management-system' ), 'INR' ),
		'sanitize'    => 'wpcfto_save_number',
     );
	$fields['stm_courses_settings']['section_accessibility']['fields']['price_ac'] = array(
        'type'       => 'number',
        'label'       => sprintf(esc_html__( 'Price AC (INR)', 'masterstudy-lms-learning-management-system' ),'INR'),
        'placeholder' => sprintf( esc_html__( 'Leave empty if no AC Price', 'masterstudy-lms-learning-management-system' ), 'INR' ),
		'sanitize'    => 'wpcfto_save_number',
     );
	$fields['stm_courses_settings']['section_accessibility']['fields']['price_online'] = array(
        'type'       => 'number',
        'label'       => sprintf(esc_html__( 'Price Online (INR)', 'masterstudy-lms-learning-management-system' ),'INR'),
        'placeholder' => sprintf( esc_html__( 'Leave empty if no Online Price', 'masterstudy-lms-learning-management-system' ), 'INR' ),
		'sanitize'    => 'wpcfto_save_number',
     );
	$fields['stm_courses_settings']['section_accessibility']['fields']['price_residential'] = array(
		'group'      => 'ended',
        'type'       => 'number',
        'label'       => sprintf(esc_html__( 'Price Residential (INR)', 'masterstudy-lms-learning-management-system' ),'INR'),
        'placeholder' => sprintf( esc_html__( 'Leave empty if no Residential Price', 'masterstudy-lms-learning-management-system' ), 'INR' ),
		'sanitize'    => 'wpcfto_save_number',
     );
	$fields['stm_courses_settings']['section_accessibility']['fields']['price_nonac_usd'] = array(
		'group'      => 'started',
        'type'       => 'number',
        'label'       => sprintf(esc_html__( 'Price Non AC (USD)', 'masterstudy-lms-learning-management-system' ),'USD'),
        'placeholder' => sprintf( esc_html__( 'Leave empty if no Non AC Price', 'masterstudy-lms-learning-management-system' ), 'USD' ),
		'sanitize'    => 'wpcfto_save_number',
     );
	$fields['stm_courses_settings']['section_accessibility']['fields']['price_ac_usd'] = array(
        'type'       => 'number',
        'label'       => sprintf(esc_html__( 'Price AC (USD)', 'masterstudy-lms-learning-management-system' ),'USD'),
        'placeholder' => sprintf( esc_html__( 'Leave empty if no AC Price', 'masterstudy-lms-learning-management-system' ), 'USD' ),
		'sanitize'    => 'wpcfto_save_number',
     );
	$fields['stm_courses_settings']['section_accessibility']['fields']['price_online_usd'] = array(
        'type'       => 'number',
        'label'       => sprintf(esc_html__( 'Price Online (USD)', 'masterstudy-lms-learning-management-system' ),'USD'),
        'placeholder' => sprintf( esc_html__( 'Leave empty if no Online Price', 'masterstudy-lms-learning-management-system' ), 'USD' ),
		'sanitize'    => 'wpcfto_save_number',
     );
	$fields['stm_courses_settings']['section_accessibility']['fields']['price_residential_usd'] = array(
		'group'      => 'ended',
        'type'       => 'number',
        'label'       => sprintf(esc_html__( 'Price Residential (USD)', 'masterstudy-lms-learning-management-system' ),'USD'),
        'placeholder' => sprintf( esc_html__( 'Leave empty if no Residential Price', 'masterstudy-lms-learning-management-system' ), 'USD' ),
		'sanitize'    => 'wpcfto_save_number',
     );
    return $fields;
}