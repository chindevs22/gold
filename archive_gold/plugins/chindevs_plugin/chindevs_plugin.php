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

$productCategoryMap = array(
    "Publications" => 95,
    "Pendrives" => 96,
    "Combo Offers" => 303
);

use \Elementor\Plugin;

require_once  plugin_dir_path(__FILE__) . 'gift_courses.php';

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

// register Gift Course style

add_action( 'wp_enqueue_scripts', 'gift_course_scripts' );
function gift_course_scripts() {

	$assets = GIFT_COURSE_URL;
	error_log("trying to enqueue scripts");
	error_log($assets);
	wp_enqueue_script( 'gift-course-scripts', plugins_url( '/js/gift-course.js', __FILE__ ), array(), false, true );
    wp_enqueue_style( 'gift-course', $assets . '/css/gift-course.css', array(), 'false', false);
}

// data migration
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


// FEEDBACK FORM
// based on form submission
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

// ??
function hide_complete_button() {
	?>
		<style>.stm-lms-lesson_navigation_complete {display: none;}</style>
	<?php
}
add_action('wp_head', 'submit_form_js');
add_shortcode('shortcodefeedback', 'hide_complete_button'); // required on lesson page

function ms_change_single_course_button_text( $text ) {
    return 'New Button Text';
}
add_filter( 'ms_single_course_button_text', 'ms_change_single_course_button_text' );
add_shortcode( 'test-functions', 'create_course_data' );
