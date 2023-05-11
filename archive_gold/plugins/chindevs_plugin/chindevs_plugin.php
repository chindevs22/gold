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
        'billing_postcode' => 'pincode',
        'billing_phone' => 'phone_no'
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


function event_registration_scripts() {
	wp_enqueue_script( 'event-registration-scripts', plugins_url( '/assets/js/event-registration.js', __FILE__ ), array(), false, true );
    wp_enqueue_style( 'event-registration', GIFT_COURSE_URL . '/assets/css/event-registration.css', array(), 'false', false);
}
add_action( 'wp_enqueue_scripts', 'event_registration_scripts' );

/// --------------------------------------------------------- COURSE MIGRATION ---------------------------------------------------------------

// All Course Data migration functions
function create_course_data() {
// 	echo " <br> <br> STARTING QUESTIONS <br> <br> ";
//     read_csv("question.csv", "question");
// 	echo "<br> <br>  DONE WITH QUESTIONS <br> <br> ";
// 	echo " <br> <br> STARTING LESSONS <br> <br> ";
//     read_csv("lessons.csv", "lesson");
// 	echo "<br> <br>  DONE WITH LESSONS <br> <br> ";
// 	echo " <br> <br> STARTING COURSES <br> <br> ";
//     read_csv("courses.csv", "course");
// 	echo "<br> <br>  DONE WITH COURSES <br> <br> ";
// 	echo " <br> <br> STARTING USERS <br> <br> "; //two user files
//     read_csv("validusers2.csv", "user");
// 	echo " <br> <br> ENDING USERS <br> <br> ";
// 	echo " <br> <br> STARTING USER ASSESSMENT <br> <br> ";
//     read_csv("usa_c7.csv", "userquiz");
// 	echo " <br> <br> ENDING USER ASSESSMENT <br> <br> ";
// 	echo " <br> <br> STARTING USER ASSESSMENT DETAILS <br> <br> ";
//     read_csv("usad_c7_small2.csv", "useranswers");
// 	echo " <br> <br> ENDING USER ASSESSMENT DETAILS <br> <br> ";
		echo " <br> <br> STARTING ENROLL <br> <br> ";
    read_csv("enrol_c7.csv", "enrol");
    	echo " <br> <br> ENDING ENROLL <br> <br> ";
// 	read_csv("publications.csv", "publications");
}
// Course data migration
function read_csv($file_name, $type) {
    //file mapping from our File Manager
    $fileName = "/home/freewaydns-dev108/cd-courses-docs/{$file_name}"; //Ensure this is the right file location
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


/// --------------------------------------------------------- LITE DATA MIGRATION ---------------------------------------------------------------
function create_lite_data() {
// 	EVENT FILES
// 	echo " <br> <br> STARTING EVENT LESSON<br> <br> ";
// 	read_lite_csv("cd-event-docs/event_lesson.csv", "event_lesson");
// 	echo " <br> <br> ENDING EVENT LESSON<br> <br> ";

// 	echo " <br> <br> STARTING EVENT<br> <br> ";
// 	read_lite_csv("cd-event-docs/event_courses.csv", "event");
// 	echo " <br> <br> ENDING EVENT <br> <br> ";

// 	echo " <br> <br> STARTING ENROLL EVENT<br> <br> ";
// 	read_lite_csv("cd-event-docs/enrol_event.csv", "user_event");
// 	echo " <br> <br> ENDING ENROLL EVENT<br> <br> ";
//    read_lite_csv("cd-event-docs/user_event.csv", "user_event");

	// SM FILES
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
        } else if ($type == "event") {
            create_event_from_csv($tempArray);
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
			'event_dates'	=> array(
				'group'		 => 'started',
				'type'       	=> 'dates',
        		'label'      	=> esc_html__( 'Event Dates', 'masterstudy-lms-learning-management-system' ),
        		'sanitize'   	=> 'wpcfto_save_dates',
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


// This creates the our version of an includes column (TODO: for any category)
add_filter( 'stm_lms_template_name', 'includes_file', 100, 2 );
function includes_file( $template_name, $vars ) {
	if ( $template_name === '/stm-lms-templates/course/udemy/parts/includes.php') {
		$template_name = '/stm-lms-templates/course/udemy/parts/includes1.php';
	}
	return $template_name;
}

// create calendar

function get_events_by_category($start_date, $end_date) {
  global $wpdb;
  $sql = "
    SELECT p.*
    FROM $wpdb->posts p
    INNER JOIN $wpdb->term_relationships tr ON p.ID = tr.object_id
    INNER JOIN $wpdb->term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
    INNER JOIN $wpdb->termmeta tm ON tt.term_id = tm.term_id
    WHERE p.post_type = 'stm-courses'
      AND p.post_status = 'publish'
      AND tm.meta_key = 'lite_category_name'
      AND tm.meta_value = 'event'
      AND p.ID IN (
        SELECT post_id
        FROM $wpdb->postmeta
		WHERE meta_key = 'event_dates'
        AND meta_value != ''
		AND meta_value REGEXP '^\\d+'
		AND meta_value BETWEEN '$start_date' AND '$end_date'
	)
  ";
  $results = $wpdb->get_results($sql);
  return $results;
}


function get_events_for_month($month, $year) {
  // Get the start and end timestamps for the month
  $start_timestamp = strtotime("$year-$month-01 00:00:00");
  $end_timestamp = strtotime("$year-$month-" . date('t', strtotime("$year-$month-01")) . " 23:59:59");

  $posts = get_events_by_category($start_timestamp, $end_timestamp);
  echo print_r($posts, true);

//   Create an array of events for each day of the month
  $events = array();
  for ($day = 1; $day <= date('t', $start_timestamp); $day++) {
    $events[$day] = array();
    foreach ($posts as $post) {
      $event_dates = explode(',', get_post_meta($post->ID, 'event_dates', true));
      foreach ($event_dates as $event_date) {
        if (date('j', $event_date) == $day) {
          $events[$day][] = $post;
        }
      }
    }
  }
  return $events;
}

function display_calendar() {
  // Get the month and year from the query string, or default to the current month and year
  $month = isset($_GET['month']) ? $_GET['month'] : date('n');
  $year = isset($_GET['year']) ? $_GET['year'] : date('Y');

  // Get the events for the current month and year
  $events = get_events_for_month($month, $year);
  echo print_r($events, true);

  // Start building the calendar HTML
  $calendar_output = '<table>';
  $calendar_output .= '<thead><tr><th colspan="7">' . date('F Y', strtotime("$year-$month-01")) . '</th></tr>';
  $calendar_output .= '<tr><th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th></tr></thead>';
  $calendar_output .= '<tbody>';

  // Loop through each day of the month and create a table cell for it
  $day_count = 1;
  $current_day_timestamp = strtotime("$year-$month-$day_count");
  $weekday = date('w', $current_day_timestamp);
  $calendar_output .= '<tr>';
  while ($day_count <= date('t', $current_day_timestamp)) {
    $calendar_output .= '<td class="';
    if ($day_count == date('j') && $month == date('n') && $year == date('Y')) {
      $calendar_output .= 'today ';
    }
    $calendar_output .= 'day">';
    $calendar_output .= '<div class="day-number">' . $day_count . '</div>';
    if (!empty($events[$day_count])) {
      foreach ($events[$day_count] as $event) {
        $calendar_output .= '<div class="event">' . $event->post_title . '</div>';
      }
    }
    $calendar_output .= '</td>';
    if ($weekday == 6) {
      $calendar_output .= '</tr><tr>';
      $weekday = 0;
    } else {
      $weekday++;
    }
    $day_count++;
  }
  // Finish building the calendar HTML
  $calendar_output .= '</tr></tbody></table>';

  echo $calendar_output;
}

add_shortcode( 'test-calendar', 'display_calendar' );




