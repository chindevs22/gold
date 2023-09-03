<?php
/**
 * Plugin Name:       Chindevs Plugin
 * Description:       Code for all changes made by ChinDevs team
 * Version:           1.0.1
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
$feedbackLessonID = 321807;
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
    'billing_postcode' => 'pincode',
    'billing_phone' => 'phone_no',
    'ijl5c9zv6lp' => 'state',
    'cigbecl89n' => 'country',
    '0hrgnga1qhp5' => 'city',
    'l3pqlc3elr' => 'address',
    'rgcxegzsmy' => 'pincode'
);


$orderMetaMapping = array (
    '_billing_email' => 'billing_email',
    '_billing_phone' => 'billing_contact_no',
    '_billing_city' =>'billing_city',
    '_billing_state' => 'billing_state',
    '_billing_country' => 'billing_country',
    '_billing_postcode' => 'billing_zipcode',
    '_billing_address_1' => 'billing_address',
    '_shipping_email' => 'shipping_email',
    '_shipping_phone' => 'shipping_contact_no',
    '_shipping_city' =>'shipping_city',
    '_shipping_state' => 'shipping_state',
    '_shipping_country' => 'shipping_country',
    '_shipping_postcode' => 'shipping_zipcode',
    '_shipping_address_1' => 'shipping_address',
    '_order_currency' => 'currency'
);

// Other meta keys might be needed (hear/hear_source/cm_center/)
$newMetaMapping = array (
     'date_of_birth' => 'dob',
     'gender' => 'gender',
	 'profession' => 'profession',
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
require_once  plugin_dir_path(__FILE__) . 'emails.php';
require_once  plugin_dir_path(__FILE__) . 'certificate_gpa.php';

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


function event_registration_scripts() {
	wp_enqueue_script( 'event-registration-scripts', plugins_url( '/assets/js/event-registration.js', __FILE__ ), array(), false, true );
    wp_enqueue_style( 'event-registration', GIFT_COURSE_URL . '/assets/css/event-registration.css', array(), 'false', false);
}
add_action( 'wp_enqueue_scripts', 'event_registration_scripts' );

function payment_faq_style() {
    wp_enqueue_style( 'payment-faq', GIFT_COURSE_URL . '/assets/css/profile-fields.css', array(), 'false', false);
}
add_action('wp_enqueue_scripts', 'payment_faq_style');

/// --------------------------------------------------------- COURSE MIGRATION ---------------------------------------------------------------

// All Course Data migration functions
function create_course_data() {
// 	echo " <br> <br> STARTING QUESTIONS <br> <br> ";
//     read_csv("cd-courses-docs/question.csv", "question");
// 	echo "<br> <br>  DONE WITH QUESTIONS <br> <br> ";

//	echo " <br> <br> STARTING LESSONS <br> <br> ";
//    read_csv("cd-courses-docs/obj_lessons.csv", "lesson");
//	echo "<br> <br>  DONE WITH LESSONS <br> <br> ";

// 	echo " <br> <br> STARTING ASSIGNMENT <br> <br> ";
// 	read_csv("cd-courses-docs/subj_lessons.csv", "assignment");
// 	echo "<br> <br>  DONE WITH ASSIGNMENT <br> <br> ";

//  echo " <br> <br> STARTING POSTAL ASSIGNMENT<br> <br> ";
// 	read_csv("cd-courses-docs/pos_lessons.csv", "postal");
// 	echo "<br> <br>  DONE WITH POSTAL ASSIGNMENT<br> <br> ";

// 	echo " <br> <br> STARTING COURSES <br> <br> ";
//  read_csv("cd-courses-docs/dm_courses.csv", "course");
// 	echo "<br> <br>  DONE WITH COURSES <br> <br> ";

 	echo " <br> <br> STARTING USERS <br> <br> "; //two user files
  read_csv("cd_docs/DMN_USERS7.csv", "user");
 	echo " <br> <br> ENDING USERS <br> <br> ";

// 	echo " <br> <br> STARTING USER ASSESSMENT <br> <br> ";
//     read_csv("cd-courses-docs/small/usa_obj.csv", "userquiz");
// 	echo " <br> <br> ENDING USER ASSESSMENT <br> <br> ";

// 	echo " <br> <br> STARTING USER ASSESSMENT FOR ASSIGNMENTS <br> <br> ";
//     read_csv("cd-courses-docs/usa_split.csv", "userassignment");
// 	echo " <br> <br> ENDING USER  ASSESSMENT FOR ASSIGNMENTS <br> <br> ";

// 	echo " <br> <br> STARTING USER ASSESSMENT FOR POSTAL <br> <br> ";
//    read_csv("cd-courses-docs/usa_pos.csv", "userassignmentpostal");
// 	echo " <br> <br> ENDING USER  ASSESSMENT FOR POSTAL <br> <br> ";

// 	echo " <br> <br> STARTING USER ASSESSMENT DETAILS FOR ASSIGNMENTS <br> <br> ";
//     read_csv("postal_usad.csv", "userassignmentanswers");
// 	echo " <br> <br> ENDING USER ASSESSMENT DETAILS  FOR ASSIGNMENTS <br> <br> ";

// 	echo " <br> <br> STARTING USER ASSESSMENT DETAILS <br> <br> ";
//     read_csv("cd-courses-docs/small/usad_10.csv", "useranswers");
// 	echo " <br> <br> ENDING USER ASSESSMENT DETAILS <br> <br> ";

// 	echo " <br> <br> STARTING ENROLL <br> <br> ";
//    read_csv("cd-courses-docs/small/enrol_courses.csv", "enrol");
//    echo " <br> <br> ENDING ENROLL <br> <br> ";

// 	echo " <br> <br> STARTING PUBLICATIONS <br> <br> ";
// 	read_csv("products.csv", "publications");
// 	echo " <br> <br> ENDING PUBLICATIONS <br> <br> ";
	
// 	echo " <br> <br> STARTING ORDERS <br> <br> ";
// 	read_csv("cd-products/orders_contrived.csv", "orders");
// 	echo " <br> <br> ENDING ORDERS <br> <br> ";
}

// Course data migration
function read_csv($file_name, $type) {
    //file mapping from our File Manager
    $fileName = "/home/freewaydns-dev108/{$file_name}"; //Ensure this is the right file location
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
        } else if ($type == "assignment") {
            create_assignment_from_csv($tempArray);
        } else if ($type == "postal") {
            create_assignment_from_csv($tempArray);
        } else if ($type == "question") {
            create_question_from_csv($tempArray);
        } else if ($type == "user") {
            create_user_from_csv($tempArray);
        } else if ($type == "userquiz") {
            progress_users_quiz_from_csv($tempArray);
        } else if ($type == "useranswers") {
            progress_users_answers_from_csv($tempArray);
        } else if ($type == "userassignment") {
            progress_users_assignment_from_csv($tempArray, false);
        } else if ($type == "userassignmentpostal") {
            progress_users_assignment_from_csv($tempArray, true);
        } else if ($type == "userassignmentanswers") {
            progress_user_assignment_answers_from_csv($tempArray);
        } else if ($type == "enrol") {
            enrol_users_from_csv($tempArray);
        } else if ($type == "publications") {
            create_publications_from_csv($tempArray);
        } else if ($type == "orders") {
            create_orders_from_csv($tempArray);
        }
    }
    fclose($file);
}
add_shortcode( 'test-functions', 'create_course_data' );


/// --------------------------------------------------------- LITE DATA MIGRATION ---------------------------------------------------------------
function create_lite_data() {
// 	EVENT FILES
// 	echo " <br> <br> STARTING EVENT LESSON<br> <br> ";
// 	read_lite_csv("cd-event-docs/webinar_lessons.csv", "event_lesson");
// 	echo " <br> <br> ENDING EVENT LESSON<br> <br> ";

// 	echo " <br> <br> STARTING EVENT<br> <br> ";
// 	read_lite_csv("cd-event-docs/webinar_courses.csv", "webinar");
// 	read_lite_csv("cd-event-docs/event_courses.csv", "event");
// 	echo " <br> <br> ENDING EVENT <br> <br> ";

	echo " <br> <br> STARTING ENROLL EVENT<br> <br> ";
// 	read_lite_csv("cd-event-docs/enrol_webinars.csv", "user_webinar");
	read_lite_csv("cd-event-docs/enrol_event.csv", "user_event");
	echo " <br> <br> ENDING ENROLL EVENT<br> <br> ";

// 	// SM FILES
// 	echo " <br> <br> STARTING SM LESSONS <br> <br> ";
// 	read_lite_csv("cd-sm-docs/sm_lessons.csv", "sm_lesson");
// 	echo " <br> <br> ENDING SM LESSONS <br> <br> ";

// 	echo " <br> <br> STARTING SM <br> <br> ";
// 	read_lite_csv("cd-sm-docs/sm_courses.csv", "shravana_mangalam");
// 	echo " <br> <br> ENDING SM <br> <br> ";

// 	echo " <br> <br> STARTING ENROLL SM USERS <br> <br> ";
// 	read_lite_csv("cd-sm-docs/sm_enrol.csv", "user_sm");
// 	echo " <br> <br> ENDING ENROLL SM USERS <br> <br> ";


// 	read_event_sm_csv("cd-sm-docs/sm_courses_small", "shravana_mangalam");
}

// "Lite" Types Data migration
function read_lite_csv($file_name, $type) {
    //file mapping from our File Manager
    $fileName = "/home/freewaydns-dev108/{$file_name}";
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
// 		error_log("mapping line");
// 		error_log(print_r($mappingLine, true));
        // create mapping based on header
        foreach($line as $value) {
           $sanitized_value = preg_replace("/\\\\u([0-9abcdef]{4})/", "&#x$1;", $value);
           $tempArray[$mappingLine[$count++]] = $sanitized_value;
        }
        if ($type == "event_lesson") {
            create_event_lesson_from_csv($tempArray);
        } else if ($type == "webinar") {
            create_event_from_csv($tempArray, true);
		} else if ($type == "event") {
		    create_event_from_csv($tempArray, false);
        } else if ($type == "user_webinar") {
            enrol_sm_users_from_csv($tempArray, "webinar");
	    } else if ($type == "user_event") {
	        enrol_sm_users_from_csv($tempArray, "event");
        } else if ($type == "sm_lesson") { //utilizes the defailt create lesson
            create_sm_lesson_from_csv($tempArray);
        } else if ($type == "shravana_mangalam") { //utilizes the defailt create lesson
            create_sm_from_csv($tempArray);
        } else if ($type == "user_sm") { //utilizes the defailt create lesson
            enrol_sm_users_from_csv($tempArray, "sm");
        }
    }
    fclose($file);
}

add_shortcode( 'test-functions-lite', 'create_lite_data' );



///-------------------------------------------------------- END DATA MIGRATION CODE ---------------------------------------------------------------------------------------

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
	$fields['stm_student_assignment']['section_group']['fields']['status'] = array(
        'type'  => 'select',
        'label' => esc_html__( 'Status', 'masterstudy-lms-learning-management-system-pro' ),
        'options' => [
            '' => __('Not selected', 'masterstudy-lms-learning-management-system-pro'),
            'not_passed' => __('Not Passed', 'masterstudy-lms-learning-management-system-pro'),
            'passed' => __('Passed', 'masterstudy-lms-learning-management-system-pro'),
        ],
        'default' => ''
    );
	$fields['stm_student_assignment']['section_group']['fields']['points_earned'] = array(
        'type'  => 'number',
        'label' => esc_html__( 'Points Earned', 'masterstudy-lms-learning-management-system-pro' ),
    );
	$fields['assignment_settings']['tab_1']['fields']['total_points'] = array(
        'type'        => 'number',
        'label'       => esc_html__( 'Total Points on Assignment', 'masterstudy-lms-learning-management-system-pro' ),
    );
    return $fields;
}


//Add Event fields to backend Admin View
add_filter('stm_wpcfto_fields', 'stm_lms_event_fields', 99, 1);


function stm_lms_event_fields($fields) {

	$fields['stm_courses_settings']['event_settings']= array(
		'name'   => esc_html__( 'Event', 'masterstudy-lms-learning-management-system' ),
		'label'  => esc_html__( 'General Events', 'masterstudy-lms-learning-management-system' ),
		'icon'   => 'fa fa-cog',
		'fields' => array(
//			'event_dates'	=> array(
//				'group'		 => 'started',
//				'type'       	=> 'dates',
//        		'label'      	=> esc_html__( 'Event Dates', 'masterstudy-lms-learning-management-system' ),
//        		'sanitize'   	=> 'save_event_dates',
//			),
            'start_event_date' => array(
                'group'		 => 'started',
                'type'       	=> 'text',
                'label'      	=> esc_html__( 'Event Start Date', 'masterstudy-lms-learning-management-system' ),
                'placeholder'    => esc_html__( 'Format as YYYY-MM-DD (IST)', 'masterstudy-lms-learning-management-system' ),
            ),
            'end_event_date' => array(
                'type'       	=> 'text',
                'label'      	=> esc_html__( 'Event End Date', 'masterstudy-lms-learning-management-system' ),
                'placeholder'    => esc_html__( 'Format as YYYY-MM-DD (IST)', 'masterstudy-lms-learning-management-system' ),
            ),
			'start_event_time' => array(
                'type'       	=> 'text',
                'label'      	=> esc_html__( 'Event Start Time', 'masterstudy-lms-learning-management-system' ),
                'placeholder'    => esc_html__( 'Format as 24 hours: HH:MM', 'masterstudy-lms-learning-management-system' ),
            ),
            'end_event_time' => array(
                'type'       	=> 'text',
                'label'      	=> esc_html__( 'Event End Time', 'masterstudy-lms-learning-management-system' ),
                'placeholder'    => esc_html__( 'Format as 24 hours: HH:MM', 'masterstudy-lms-learning-management-system' ),
            ),
			'event_repetition_days' => array(
                'type'       	=> 'text',
                'label'      	=> esc_html__( 'Repetition Days', 'masterstudy-lms-learning-management-system' ),
                'placeholder'    => esc_html__( 'Format as list ex: MO, TU, WE, TH, FR, SA, SU', 'masterstudy-lms-learning-management-system' ),
            ),
			'registration_close_date'	=> array(
				'group'		 => 'ended',
				'type'       	=> 'date',
        		'label'      	=> esc_html__( 'Registration Close Date', 'masterstudy-lms-learning-management-system' ),
        		'sanitize'   	=> 'wpcfto_save_dates',
			),
			'price_nonac'	=> array(
				'group'		 => 'started',
				'type'       => 'number',
				'label'       => sprintf(esc_html__( 'Price Non AC (INR)', 'masterstudy-lms-learning-management-system' ),'INR'),
				'placeholder' => sprintf( esc_html__( 'Leave empty if no Non AC Price', 'masterstudy-lms-learning-management-system' ), 'INR' ),
				'sanitize'    => 'wpcfto_save_number',
			),
			'price_ac'	=> array(
				'type'       => 'number',
				'label'       => sprintf(esc_html__( 'Price AC (INR)', 'masterstudy-lms-learning-management-system' ),'INR'),
				'placeholder' => sprintf( esc_html__( 'Leave empty if no AC Price', 'masterstudy-lms-learning-management-system' ), 'INR' ),
				'sanitize'    => 'wpcfto_save_number',
			 ),
			'price_online'	=> array(
				'type'       => 'number',
				'label'       => sprintf(esc_html__( 'Price Online (INR)', 'masterstudy-lms-learning-management-system' ),'INR'),
				'placeholder' => sprintf( esc_html__( 'Leave empty if no Online Price', 'masterstudy-lms-learning-management-system' ), 'INR' ),
				'sanitize'    => 'wpcfto_save_number',
			 ),
			'price_residential'	=> array(
				'group'      => 'ended',
				'type'       => 'number',
				'label'       => sprintf(esc_html__( 'Price Residential (INR)', 'masterstudy-lms-learning-management-system' ),'INR'),
				'placeholder' => sprintf( esc_html__( 'Leave empty if no Residential Price', 'masterstudy-lms-learning-management-system' ), 'INR' ),
				'sanitize'    => 'wpcfto_save_number',
			 ),
			'price_nonac_usd'	=> array(
				'group'      => 'started',
				'type'       => 'number',
				'label'       => sprintf(esc_html__( 'Price Non AC (USD)', 'masterstudy-lms-learning-management-system' ),'USD'),
				'placeholder' => sprintf( esc_html__( 'Leave empty if no Non AC Price', 'masterstudy-lms-learning-management-system' ), 'USD' ),
				'sanitize'    => 'wpcfto_save_number',
			 ),
			'price_ac_usd'	=> array(
				'type'       => 'number',
				'label'       => sprintf(esc_html__( 'Price AC (USD)', 'masterstudy-lms-learning-management-system' ),'USD'),
				'placeholder' => sprintf( esc_html__( 'Leave empty if no AC Price', 'masterstudy-lms-learning-management-system' ), 'USD' ),
				'sanitize'    => 'wpcfto_save_number',
			 ),
			'price_online_usd'	=> array(
				'type'       => 'number',
				'label'       => sprintf(esc_html__( 'Price Online (USD)', 'masterstudy-lms-learning-management-system' ),'USD'),
				'placeholder' => sprintf( esc_html__( 'Leave empty if no Online Price', 'masterstudy-lms-learning-management-system' ), 'USD' ),
				'sanitize'    => 'wpcfto_save_number',
			 ),
			'price_residential_usd'	=> array(
				'group'      => 'ended',
				'type'       => 'number',
				'label'       => sprintf(esc_html__( 'Price Residential (USD)', 'masterstudy-lms-learning-management-system' ),'USD'),
				'placeholder' => sprintf( esc_html__( 'Leave empty if no Residential Price', 'masterstudy-lms-learning-management-system' ), 'USD' ),
				'sanitize'    => 'wpcfto_save_number',
			 ),
		)
	);
	    return $fields;
}

//Add Includes fields to backend Admin View
add_filter('stm_wpcfto_fields', 'stm_lms_includes_fields', 99, 1);

function stm_lms_includes_fields($fields) {
	$fields['stm_courses_settings']['section_certificate']['fields']['free_lesson']= array(
		'type'       	=> 'text',
		'label'      	=> esc_html__( 'Free Lesson URL', 'masterstudy-lms-learning-management-system' ),
		'placeholder'    => esc_html__( 'lessonID ex: 123456', 'masterstudy-lms-learning-management-system' ),
	);
	$fields['stm_courses_settings']['section_certificate']['fields']['discussion_forum']= array(
		'type'       	=> 'text',
		'label'      	=> esc_html__( 'Discussion Forum Name', 'masterstudy-lms-learning-management-system' ),
		'placeholder'    => esc_html__( 'Forum Name (from url) ex: aparokshanubhuti', 'masterstudy-lms-learning-management-system' ),
	);
	return $fields;
}


// This creates the our version of an includes column (TODO: for any category)
add_filter( 'stm_lms_template_name', 'includes_file', 100, 2 );
function includes_file( $template_name, $vars ) {
	if ( $template_name === '/stm-lms-templates/course/udemy/parts/includes.php') {
		$template_name = '/stm-lms-templates/course/udemy/parts/includes1.php';
	}
	return $template_name;
}


// Add the Payment FAQ field to the backend Admin View
add_filter( 'stm_wpcfto_fields', 'stm_lms_faq_tab', 99, 1);

function stm_lms_faq_tab($fields) {
	$fields['stm_courses_settings']['section_payment_faq'] = array(
        'name'   => esc_html__( 'Payment FAQ', 'masterstudy-lms-learning-management-system' ),
        'icon'   => 'fas fa-question',
        'fields' => array(
            'payment_faq' => array(
                'type'  => 'faq',
                'label' => esc_html__( 'FAQ', 'masterstudy-lms-learning-management-system' ),
            ),
        ),
    );
	return $fields;
}

// Add ChinDevs tabs to Instructor view
add_filter( 'stm_lms_template_name', 'new_faq', 100, 2 );
function new_faq( $template_name, $vars ) {
	if ( $template_name === '/stm-lms-templates/course/udemy/parts/tabs/faq.php') {
		$template_name = '/stm-lms-templates/course/udemy/parts/all_faq.php';
	}
	return $template_name;
}

// Add Fill Profile Fields banner
function user_missing_profile_fields($user) {
    $displayName = $user->display_name;  // User's display name
    $firstName = $user->first_name;      // User's first name
    $lastName = $user->last_name;        // User's last name
    $user_profile_meta_values = array($displayName, $firstName, $lastName);

    $profile_address_meta_keys = array('ijl5c9zv6lp', 'cigbecl89n', '0hrgnga1qhp5', 'l3pqlc3elr', 'rgcxegzsmy');

    foreach ($profile_address_meta_keys as $meta_key) {
        if (metadata_exists('user', $user->ID, $meta_key)) {
            $meta_value = get_user_meta($user->ID, $meta_key, true);
           // error_log("the meta val " . $meta_value);
        } else {
          //  error_log("the meta key that doesn't exist " . $meta_key);
            return true;
        }
    }
}

// add_action( 'stm_lms_template_main', 'my_custom_banner', 10 );
function my_custom_banner() {
    $user = wp_get_current_user();
    if (user_missing_profile_fields($user)) {
    ?>
        <div class="profile-fields-banner">
          <p>Looks like you have fields to fill out</p>
          <button id="fill-now-btn">Fill Now</button>
        </div>
    <?php
    }

}
