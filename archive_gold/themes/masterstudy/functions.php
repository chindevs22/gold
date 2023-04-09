<?php
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
	
$theme_info = wp_get_theme();
define( 'STM_THEME_VERSION', ( WP_DEBUG ) ? time() : $theme_info->get( 'Version' ) );
define( 'STM_MS_SHORTCODES', '1' );

define( 'STM_THEME_NAME', 'Masterstudy' );
define( 'STM_THEME_CATEGORY', 'Education WordPress Theme' );
define( 'STM_ENVATO_ID', '12170274' );
define( 'STM_TOKEN_OPTION', 'stm_masterstudy_token' );
define( 'STM_TOKEN_CHECKED_OPTION', 'stm_masterstudy_token_checked' );
define( 'STM_THEME_SETTINGS_URL', 'stm_option_options' );
define( 'STM_GENERATE_TOKEN', 'https://docs.stylemixthemes.com/masterstudy-theme-documentation/installation-and-activation/theme-activation' );
define( 'STM_SUBMIT_A_TICKET', 'https://support.stylemixthemes.com/tickets/new/support?item_id=12' );
define( 'STM_DEMO_SITE_URL', 'https://stylemixthemes.com/masterstudy/' );
define( 'STM_DOCUMENTATION_URL', 'https://docs.stylemixthemes.com/masterstudy-theme-documentation/' );
define( 'STM_CHANGELOG_URL', 'https://docs.stylemixthemes.com/masterstudy-theme-documentation/extra-materials/changelog' );
define( 'STM_INSTRUCTIONS_URL', 'https://docs.stylemixthemes.com/masterstudy-theme-documentation/installation-and-activation/theme-activation' );
define( 'STM_INSTALL_VIDEO_URL', 'https://www.youtube.com/watch?v=a8zb5KTAw48&list=PL3Pyh_1kFGGDikfKuVbGb_dqKmXZY86Ve&index=6&ab_channel=StylemixThemes' );
define( 'STM_VOTE_URL', 'https://stylemixthemes.cnflx.io/boards/masterstudy-lms' );
define( 'STM_BUY_ANOTHER_LICENSE', 'https://themeforest.net/item/masterstudy-education-center-wordpress-theme/12170274' );
define( 'STM_VIDEO_TUTORIALS', 'https://www.youtube.com/playlist?list=PL3Pyh_1kFGGDikfKuVbGb_dqKmXZY86Ve' );
define( 'STM_FACEBOOK_COMMUNITY', 'https://www.facebook.com/groups/masterstudylms' );
define( 'STM_TEMPLATE_URI', get_template_directory_uri() );
define( 'STM_TEMPLATE_DIR', get_template_directory() );
define( 'STM_THEME_SLUG', 'stm' );
define( 'STM_INC_PATH', get_template_directory() . '/inc' );

$inc_path     = get_template_directory() . '/inc';
$widgets_path = get_template_directory() . '/inc/widgets';
// Theme setups


add_filter( 'stm_theme_default_layout', 'get_stm_theme_default_layout' );
function get_stm_theme_default_layout() {
	return 'default';
}

add_filter( 'stm_theme_default_layout_name', 'get_stm_theme_default_layout_name' );
function get_stm_theme_default_layout_name() {
	return 'classic_lms';
}

add_filter( 'stm_theme_demos', 'masterstudy_get_demos' );
add_filter( 'stm_theme_demo_layout', 'stm_get_layout' );
add_filter( 'stm_theme_plugins', 'get_stm_theme_plugins' );
add_filter( 'stm_theme_layout_plugins', 'stm_layout_plugins', 10, 1 );

function get_stm_theme_plugins() {
	return stm_require_plugins( true );
}

add_filter( 'stm_theme_enable_elementor', 'get_stm_theme_enable_elementor' );

function get_stm_theme_enable_elementor() {
	return true;
}

add_filter( 'stm_theme_secondary_required_plugins', 'get_stm_theme_secondary_required_plugins' );
add_filter( 'stm_theme_elementor_addon', 'get_stm_theme_elementor_addon' );
add_action( 'stm_reset_theme_options', 'do_stm_reset_theme_options' );


if ( is_admin() && file_exists( get_template_directory() . '/admin/admin.php' ) ) {
	require_once get_template_directory() . '/admin/admin.php';
}

// Custom code and theme main setups
require_once $inc_path . '/setup.php';

// Header an Footer actions
require_once $inc_path . '/header.php';
require_once $inc_path . '/footer.php';

// Enqueue scripts and styles for theme
require_once $inc_path . '/scripts_styles.php';

/*Theme configs*/
require_once $inc_path . '/theme-config.php';

// Visual composer custom modules
if ( defined( 'WPB_VC_VERSION' ) ) {
	require_once $inc_path . '/visual_composer.php';
}

require_once $inc_path . '/elementor.php';

/////''' Custom code for any outputs modifying
//require_once($inc_path . '/payment.php');
require_once $inc_path . '/custom.php';

// Custom code for woocommerce modifying
if ( class_exists( 'WooCommerce' ) ) {
	require_once $inc_path . '/woocommerce_setups.php';
}

if ( defined( 'STM_LMS_URL' ) ) {
	require_once $inc_path . '/lms/main.php';
}
function stm_glob_pagenow() {
	global $pagenow;

	return $pagenow;
}

function stm_glob_wpdb() {
	global $wpdb;

	return $wpdb;
}

if ( class_exists( 'BuddyPress' ) ) {
	require_once $inc_path . '/buddypress.php';
}

//Announcement banner
if ( is_admin() ) {
	require_once $inc_path . '/admin/generate_styles.php';
	require_once $inc_path . '/admin/admin_helpers.php';
	require_once $inc_path . '/tgm/tgm-plugin-registration.php';
}

//
//
// --------------------------------------------------------------------------------------------
// CHIN DEV CODE ADDED BELOW
// --------------------------------------------------------------------------------------------
//
//

function test_pubs() {
	read_csv("publications.csv", "publications");
}

function create_course_data() {
    read_csv("questions.csv", "question");
    read_csv("lesson_test.csv", "lesson");
    read_csv("course_materials.csv", "course");
    read_csv("users.csv", "user");
    read_csv("user_self_assessment.csv", "userquiz");
    read_csv("user_self_assessment_details.csv", "useranswers");
    read_csv("enrol.csv", "enrol");
	read_csv("publications.csv", "publications");
}

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


// --------------------------------------------------------------------------------------------
// CREATE LESSONS SECTION
// --------------------------------------------------------------------------------------------

// Create Lesson Data
function create_lesson_from_csv($lessonData) {
	global $lessonToQuestionsMap, $sectionToLessonMap, $lessonMGMLtoWP;

    $wpdata['post_title'] = $lessonData['title'];
    $wpdata['post_status'] ='publish';
    if ($lessonData['lesson_type'] == 'quiz') {
        $wpdata['post_type'] = 'stm-quizzes';
        $wpdata['post_content'] = $lessonData['summary'];
    } else {
        //study material post meta
        $link = 'https://dev108.freewaydns.net/wp-content/uploads/course_materials/'.$lessonData['course_id'].'/'.$lessonData['attachment'];
        $file_content = '<a href="https:////dev108.freewaydns.net/wp-content/plugins/pdfjs-viewer-shortcode/pdfjs/web/viewer.php?file='.$link.'&amp;dButton=false&amp;pButton=true&amp;oButton=false&amp;sButton=true#zoom=auto&amp;pagemode=none" target="_blank" rel="noopener"><img src="https://dev108.freewaydns.net/wp-content/uploads/2023/02/button_open-pdf.png" alt="PDF icon" /></a>';
        // audio post meta
        $embedded_audio = '[embed]'.$lessonData['audio_url'].'[/embed]';
        $wpdata['post_type'] = 'stm-lessons';
        $wpdata['post_content'] = $file_content . $embedded_audio;
    }
	
    $lesson_post_id = wp_insert_post( $wpdata );

    $lessonMGMLtoWP[$lessonData['id']] = $lesson_post_id; //save MGML ID
    $sectionID = $lessonData['section_id']; //map section ID for course
    if (!array_key_exists($sectionID, $sectionToLessonMap)) {
		// TODO: replaced section_name with title here
        $sectionToLessonMap[$sectionID] = array("{$lessonData['title']}", "{$lesson_post_id}");
    } else {
        array_push($sectionToLessonMap[$sectionID], "{$lesson_post_id}");
    }

    if ($lessonData['lesson_type'] == 'quiz') {
          update_post_meta($lesson_post_id, 'correct_answer', 'on');
          update_post_meta($lesson_post_id, 'passing_grade', '0');
          update_post_meta($lesson_post_id, 're_take_cut', '0');
          update_post_meta($lesson_post_id, 'quiz_style', 'global');
          $questionArray = $lessonToQuestionsMap[$lessonData['id']];
		  if (!empty($questionArray)) {
			  $questionString = implode(",", $questionArray);
			  update_post_meta($lesson_post_id, 'questions', $questionString);
		  } else {
			  echo "No questions available for Quiz <br>";
		  }
    } else {
        //video post meta
        update_post_meta($lesson_post_id, 'duration', $lessonData['duration']);
        update_post_meta($lesson_post_id, 'type', $lessonData['lesson_type']);
        $video_type = strtolower($lessonData['video_type']);
        update_post_meta($lesson_post_id, 'video_type', $video_type);
        update_post_meta($lesson_post_id, "lesson_{$video_type}_url", $lessonData['video_url']);
    }
}

// --------------------------------------------------------------------------------------------
// CREATE COURSE SECTION
// --------------------------------------------------------------------------------------------

// create the course
function create_course_from_csv($courseData) {
    global $courseMGMLtoWP, $sectionToLessonMap;

    // Create array of Course info from CSV data
	$wpdata['post_title'] = $courseData['title'];
    $wpdata['post_content'] = html_entity_decode($courseData['description']);
	$wpdata['post_excerpt'] = $courseData['short_description'];
	$wpdata['post_status'] ='publish';
	$wpdata['post_type'] = 'stm-courses';
	$course_post_id = wp_insert_post( $wpdata );

    $curriculum_string = "";
	$combinedArray = array();
	$sectionString = $courseData['section'];
	$sectionArray = create_array_from_string($sectionString, ",");

	foreach ($sectionArray as $sectionID) {
		if ($sectionToLessonMap[$sectionID]) {
			$combinedArray = array_merge($combinedArray, $sectionToLessonMap[$sectionID]);
		}
    }

	$curriculum_string = implode(",", $combinedArray);
	$courseMGMLtoWP[$courseData['id']] = $course_post_id;
	update_post_meta($course_post_id, 'price', $courseData['price_usd']);
	update_post_meta($course_post_id, 'curriculum', $curriculum_string);
	update_post_meta($course_post_id, 'level', $courseData['level']);
	update_post_meta($course_post_id, 'current_students', 0);
	add_course_image($course_post_id, $courseData['id']); // adds the image to the course

	// this appends the category as a term with the taxonomy relationship to the course ID

	$category = $courseData['parent_category'];

	if ($category == 'Satsang Webinars' || $category == 'Text-based Webinars') {
        $category_arr = array("Study Format", $category);
    } else {
        $category_arr = array("Subject Matter", $category);
    }
    wp_set_object_terms($course_post_id, $category_arr, 'stm_lms_course_taxonomy', $append = true );
}

// add course image
function add_course_image($course_post_id, $course_id) {
    $upload_dir = wp_upload_dir();
    $upload_path = "course_materials/{$course_id}/thumbnail.jpg";
    $filename = "thumbnail.jpg";
    $wp_filetype = wp_check_filetype(basename($filename), null );
    $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title' => sanitize_file_name($filename),
        'post_content' => '',
        'post_status' => 'inherit'
    );

    $attachment_id = wp_insert_attachment( $attachment, $upload_path, $course_post_id );
    if ( ! is_wp_error( $attachment_id ) ) {
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload_path );
        wp_update_attachment_metadata( $attachment_id, $attachment_data );
        set_post_thumbnail( $course_post_id, $attachment_id );
    }
}

//Helper Function to create an Array from a String
function create_array_from_string($sectionString, $delimiter) {
	$sectionString = trim($sectionString, '[');
    $sectionString = trim($sectionString, ']');
    $sectionString = trim($sectionString, ' ');
	$sectionString = trim($sectionString, '"');
    $sectionArray = explode($delimiter, $sectionString);
    return $sectionArray;
}

// --------------------------------------------------------------------------------------------
// CREATE QUESTIONS SECTION
// --------------------------------------------------------------------------------------------
// 
function create_question_from_csv($questionData) {
    global $lessonToQuestionsMap, $questionMGMLtoWP, $wpQuestionsToAnswers;

    $wpdata['post_title'] = $questionData['title'];
    $wpdata['post_status'] ='publish';
    $wpdata['post_type'] = 'stm-questions';
    $question_post_id = wp_insert_post( $wpdata );
    $quiz_id = $questionData['quiz_id'];

    $questionMGMLtoWP[$questionData['id']] = $question_post_id; //map MGML question ID

    // MAP question ID to quiz
    if (!array_key_exists($quiz_id, $lessonToQuestionsMap)) {
 		$lessonToQuestionsMap[$quiz_id] = array($question_post_id);
    } else {
        array_push($lessonToQuestionsMap[$quiz_id], $question_post_id);
    }

    // add metadata for question
    if ($questionData['type'] == 'multiple_choice') {
        update_post_meta($question_post_id, 'type', 'multi_choice');
    } elseif ($questionData['type'] == 'matching')  {
        update_post_meta($question_post_id, 'type', 'item_match');
    } else {
       update_post_meta($question_post_id, 'type', $questionData['type']);
    }

    $answers = array();
    if ($questionData['type'] != 'matching')  {
        // if not matching
        $count = 1;
        $options = create_array_from_string($questionData['options'], '","');
        //print_r($options);
        $isCorrect = $questionData['correct_answers'];
        foreach ($options as $option) {
            $option = trim($option, "\"");
            $optionArray["text"] = $option;
            $optionArray["isTrue"] = str_contains($isCorrect, $count++) ? "1" : "0";
            array_push($answers, $optionArray);
        }
    }
	else {
        $matching_data = $questionData['options'];
        $questionKey  = '"questions":';
        $optionKey = '"options":';
        $qPos = stripos($matching_data, $questionKey);
        $oPos = stripos($matching_data, $optionKey);
        $questionString = substr($matching_data, $qPos+12, $oPos-14);
        $optionString = substr($matching_data, $oPos+10, -1);

        $questions = create_array_from_string($questionString, '","');
        $options = create_array_from_string($optionString, '","');

		$arrLength = count($questions);
        $correctAnswers = create_array_from_string($questionData['correct_answers'], '","');

        for($x = 0; $x < $arrLength; $x++) {
 			$correctAnswer = $correctAnswers[$x];
            $optionArray["question"] = $questions[$x];
            $optionArray["text"] = $options[$correctAnswer - 1];
            $optionArray["isTrue"] = 0;
            array_push($answers, $optionArray);
        }
    }
    // map WP qusetion to WP Answers List
    $wpQuestionsToAnswers[$question_post_id] = $options;
    update_post_meta($question_post_id, 'answers', $answers);
}


// --------------------------------------------------------------------------------------------
// CREATE USER SECTION
// --------------------------------------------------------------------------------------------

function create_user_from_csv($userData) {
    global $userMGMLtoWP;

    // Create array of User info from CSV data
	$wpdata['user_pass'] = "HariOm2022!";
	$wpdata['user_login'] = $userData['first_name'];
	$wpdata['first_name'] = $userData['first_name'];
	$wpdata['last_name'] = $userData['last_name'];
	$wpdata['display_name'] = $userData['first_name'];
	$wpdata['user_email'] = $userData['email'];

	if ( !username_exists($wpdata['user_login']) && !email_exists($wpdata['user_email']) ) {
		$user_id = wp_insert_user($wpdata);
		$wp_user = new WP_User($user_id);
		echo "USER: " . $userData['first_name'] . "<br>";
		echo "The MGML user id" . $userData['id'] . "<br>";
		echo "WordPRESS user ID" . $user_id . "<br>";
		$userMGMLtoWP[$userData['id']] = $user_id;
		$wp_user->set_role('subscriber');
		create_meta($userData, $user_id);
	}
	else {
		echo "NOT creating new user <br>";
		$user_id = wp_update_user($wpdata);
		$userMGMLtoWP[$userData['id']] = $user_id;
		create_meta($userData, $user_id);
	}
	echo "The user mgml to wp map: ";
}

function create_meta($userData, $user_id) {
    global $existingMetaMapping, $newMetaMapping;
    foreach ($existingMetaMapping as $key => $value) {
       update_user_meta( $user_id, $key, $userData[$value] );
    }
    foreach ($newMetaMapping as $key => $value) {
       add_user_meta( $user_id, $key, $userData[$value], true );
    }
}

// --------------------------------------------------------------------------------------------
// USER PROGRESS SECTION
// --------------------------------------------------------------------------------------------

function progress_users_quiz_from_csv($progressData) {
	global $wpdb, $userMGMLtoWP, $courseMGMLtoWP, $selfAssessmentToUser, $lessonMGMLtoWP, $attemptNumberMap;
	$mgml_user_id = $progressData['user_id'];
    $wp_user_id = $userMGMLtoWP[$mgml_user_id];

	$table_name = 'wp_stm_lms_user_quizzes';

//     $mgml_user_id = $progressData['user_id'];
//     $wp_user_id = $userMGMLtoWP[$mgml_user_id];
    $wp_quiz_id = $lessonMGMLtoWP[$progressData['quiz_id']];
    $wp_course_id =  $courseMGMLtoWP[$progressData['course_id']];
    $attempt_key = "" . $wp_user_id . $wp_course_id . $wp_quiz_id;
    $attempt_number = $progressData['attempt'];
    $attemptNumberMap[$attempt_key] = $attempt_number;


  $grade = $progressData['marks']/$progressData['quiz_marks'] * 100; //aka progress

  // map self assessment id to user id
  $selfAssessmentToUser[$progressData['id']] =  $mgml_user_id;

  $wpdb->insert($table_name, array(
      'user_quiz_id' => NULL,
      'user_id' => $wp_user_id,
      'course_id' => $wp_course_id,
      'quiz_id' => $wp_quiz_id,
      'progress' => $grade,
      'status' => 'passed',
      'sequency' => '[]',
  ));
  progress_user_lessons($wp_course_id, $wp_quiz_id, $wp_user_id);
}

function progress_user_lessons($wp_course_id, $wp_quiz_id, $wp_user_id) {
	global $wpdb;

	$curriculum_string = get_post_meta($wp_course_id, 'curriculum', true);
    $ca = create_array_from_string($curriculum_string, ',');

    $arrLength = count($ca);
    $quizIndex = 0;
    for($x = 0; $x < $arrLength; $x++) {
        if ($wp_quiz_id == $ca[$x]) {
           $quizIndex = $x;
		   break;
        }
    }
    $isLeft = false;
    $isRight = false;
    $lessonsCompleted = array();
    $indexLeft = $quizIndex - 1;
    $indexRight = $quizIndex + 1;
    while (!$isLeft || !$isRight) {
       if ($indexRight == $arrLength || intval($ca[$indexRight]) == 0) {
            $isRight = true;
       } else {
            array_push($lessonsCompleted, $ca[$indexRight++]);
       }
       if(intval($ca[$indexLeft]) == 0) {
            $isLeft = true;
       } else {
          array_push($lessonsCompleted, $ca[$indexLeft--]);
       }
    }

	echo "LESSON COMPLETED <br>";
    // Insert Each Completed Lesson Based on Completed Quiz
    foreach($lessonsCompleted as $lesson_id) {
        $table_name = 'wp_stm_lms_user_lessons';
        $wpdb->insert($table_name, array(
            'user_lesson_id' => NULL,
            'user_id' => $wp_user_id,
            'course_id' => $wp_course_id,
            'lesson_id' => $lesson_id
        ));
    }
}

function progress_users_answers_from_csv($answerData) {
    global $wpdb, $userMGMLtoWP, $attemptNumberMap, $courseMGMLtoWP, $lessonMGMLtoWP, $selfAssessmentToUser, $questionMGMLtoWP, $wpQuestionsToAnswers;
    $table_name = 'wp_stm_lms_user_answers';

    // get IDS
    $sa_id = $answerData['self_assessment_id'];
    $mgml_user_id = $selfAssessmentToUser[$sa_id];
    $wp_user_id = $userMGMLtoWP[$mgml_user_id];
    $wp_course_id = $courseMGMLtoWP[$answerData['course_id']];
    $wp_quiz_id = $lessonMGMLtoWP[$answerData['quiz_id']];

    // TODO CHECK the WP question ID should be part of the $lessonToQuestionsMap of $answerData['quiz_id']

    $wp_question_id = $questionMGMLtoWP[$answerData['question_id']];
    $options = $wpQuestionsToAnswers[$wp_question_id];
    $userAnswers = create_array_from_string($answerData['answers'], '","'); // ex [ 2, 4 ]

    $arrLength = count($userAnswers);
    $chosenAnswers = array();
    for($x = 0; $x < $arrLength; $x++) {
        $correctAnswer = $userAnswers[$x]; //2
        array_push($chosenAnswers, $options[$correctAnswer - 1]);
    }

    $answerString = implode(",", $chosenAnswers); // comma seperated string of answers
    $isCorrect = ($answerData['question_marks'] == $answerData['marks_obtained']) ? "1" : "0";
    $attempt_key = "" . $wp_user_id . $wp_course_id . $wp_quiz_id;
    $wpdb->insert($table_name, array(
        'user_answer_id' => NULL,
        'user_id' => $wp_user_id,
        'course_id' => $wp_course_id,
        'quiz_id' => $lessonMGMLtoWP[$answerData['quiz_id']],
        'question_id' => $wp_question_id,
        'user_answer' => $answerString,
        'correct_answer' => $isCorrect,
        'attempt_number' => $attemptNumberMap[$attempt_key],
    ));
}

function enrol_users_from_csv($enrolData) {
    global $wpdb, $userMGMLtoWP, $courseMGMLtoWP, $lessonMGMLtoWP, $selfAssessmentToUser, $questionMGMLtoWP;
    $table_name = 'wp_stm_lms_user_courses';
	// TODO: Hardcoding since we didn't make that lesson - $enrolData['current_lesson_id']
	$wp_course_id = $courseMGMLtoWP[$enrolData['course_id']];
    $wpdb->insert($table_name, array(
        'user_course_id' => NULL,
        'user_id' => $userMGMLtoWP[$enrolData['user_id']],
        'course_id' => $wp_course_id,
        'current_lesson_id' => '0',
        'progress_percent' => $enrolData['progress_percent'],
        'status' => 'enrolled',
		'start_time' => time(),
	));
	
	$students = get_post_meta($wp_course_id, 'current_students', true);
	update_post_meta($wp_course_id, 'current_students', $students + 1);
	
}

// --------------------------------------------------------------------------------------------
// PRODUCTS SECTION
// --------------------------------------------------------------------------------------------

// Helper Function for Building Attribute Array for Publications
function build_attr_array($attr, $count) {
	$key = trim($attr['key'], " \x3A");
	$value = trim($attr['value'], " \x3A");
    $data_arr = array();
    $data_arr['name'] = $key;
    $data_arr['value'] = $value;
    $data_arr['position'] = $count;
    $data_arr['is_visible'] = 1;
    $data_arr['is_variation'] = 0;
    $data_arr['is_taxonomy'] = 0;
    return $data_arr;
}

// product is a post
function create_publications_from_csv($productData) {
    $wpdata['post_title'] = $productData['title'];
    $wpdata['post_excerpt'] = html_entity_decode($productData['description']);
    $wpdata['post_content']  = html_entity_decode($productData['description']);
    $wpdata['post_status'] ='publish';
    $wpdata['post_type'] = 'product';
    $product_post_id = wp_insert_post( $wpdata );

    // add product metadata
    $prod_price =  $productData['usd_price']; //need to handle rupees

    update_post_meta($product_post_id, '_visibility', 'visible');
    update_post_meta($product_post_id, '_stock_status', 'instock');
    update_post_meta($product_post_id, '_stock',  $productData['quantity']);
    update_post_meta($product_post_id, '_regular_price', $prod_price);
    update_post_meta($product_post_id, '_featured', $productData['featured_product'] == 1 ? 'yes' : 'no');
    if ($productData['discount_flag'] == 1) {
        $percent_off = $prod_price * $productData['discounted_price']/100;
        $sale_price = round($prod_price - $percent_off);
        update_post_meta($product_post_id, '_sale_price', $sale_price);
    }
    update_post_meta($product_post_id, '_weight', $productData['weight']/1000);

    // set the product image - TBD once we have an image

    // set the product category -----------------------------------------------------------------

    global $productCategoryMap; // make sure global var exists
        $taxonomy = 'product_cat';
    $parent_cat = $productData['parent_category_name']; // ensure in CSV
    $sub_cat = $productData['sub_category_name']; // ensure in CSV
    $wp_category_int = 0;
    $parent_cat_id = $productCategoryMap[$parent_cat];
    $defaults = array('parent'=> $parent_cat_id);

    $term_response = term_exists($sub_cat, $taxonomy); // a record
    if ($term_response == null) {
        $new_term = wp_insert_term($sub_cat, $taxonomy , $defaults);
        $wp_category_int = intval($new_term['term_id']);
    } else {
        $current_term = term_exists($sub_cat, $taxonomy, $parent_cat_id);
        if ($current_term == null) {
            $new_term = wp_insert_term($sub_cat, $taxonomy, $defaults);
            $wp_category_int = intval($new_term['term_id']);
        } else {
            $wp_category_int = intval($current_term['term_id']);
        }
    }
    wp_set_object_terms($product_post_id, $wp_category_int, $taxonomy, $append = true );

    // Set the the product attributes for language and author -----------------------------------------
    
    $extra_attributes = json_decode($productData['variable_filed'], true);
    $count = 0;
    $outer_arr = array();
    $terms_array = array(); // add each term (Author: Swami Ji) to the array

	foreach($extra_attributes as $attr) {
		
		$key = trim($attr['key'], " \x3A"); // strip whitespace and colon from key
		$value = trim($attr['value'], " \x3A");
		$formattedAttr = "" . $key . ": " . $value;
		
        array_push($terms_array, $formattedAttr); // build tags
        
        $data_arr = build_attr_array($attr, $count);
        $count += 1;
        $outer_arr[strtolower($key)] = $data_arr;
    }
    // add language attribute from language col
    // ensure new_language exists in CSV
    if(!array_key_exists('language', $outer_arr)) {
        $attr = array("key" => "Language", "value" => $productData['new_language']);
        array_push($terms_array, "" . $attr['key'] . ": " . $attr['value']);
        $data_arr = build_attr_array($attr, $count);
        $outer_arr[strtolower($attr['key'])] = $data_arr;
    }
    update_post_meta($product_post_id, '_product_attributes', $outer_arr);

    wp_set_object_terms($product_post_id,  $terms_array, 'product_tag', $append = true );
}

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

function hide_complete_button() {
	?>
		<style>.stm-lms-lesson_navigation_complete {display: none;}</style>
	<?php
}
add_action('wp_head', 'submit_form_js');
add_shortcode('shortcodefeedback', 'hide_complete_button'); // required on lesson page

// create country_options on the form 
function create_country_options() {
    $form_options = get_option('stm_lms_form_builder_forms');
    
	$prof_form = $form_options[2];
	$fields = $prof_form['fields'];
	for ($x = 0; $x < count($fields); $x++) {
		$field = $fields[$x];
		if ($field['label'] == 'Country') {
			$field['choices'] = get_countries_new();
		}
		$fields[$x] = $field;
	}
	$form_options[2]['fields'] = $fields;		 
	update_option('stm_lms_form_builder_forms', $form_options);
}

// call the javascript 
function my_enqueue_script() {
    if (is_page('user-account')) {
        wp_enqueue_script('chindevs', get_template_directory_uri() . '/assets/js/chindevs.js', array('jquery'), '1.0', true);
    }
}
add_action('wp_enqueue_scripts', 'my_enqueue_script');

function get_auth_token() {
  $response = wp_remote_get("https://www.universal-tutorial.com/api/getaccesstoken", array(
	  'headers' => array(
            "Accept" => "application/json",
		    "api-token" => "VY2ojFwRsuDagMzCTEfGciexCBZfAr6EmrBkMvjTvGN0cn9W0bForp5Cf69WcnRIk-c",
		   "user-email" => "anushagopal1234@gmail.com")
  		)
	);
  $token = json_decode( wp_remote_retrieve_body( $response ) );
  return $token->auth_token;
}

function get_countries_new() {
	$token = get_auth_token();
	$response = wp_remote_get("https://www.universal-tutorial.com/api/countries/", array(
	  'headers' => array(
            "Authorization" => "Bearer {$token}",
		  "Accept" => "application/json")
  		)
	);
	
	$countries = json_decode( wp_remote_retrieve_body( $response ) );
    // Get countries
    $country_names = array();
    foreach ( $countries as $country ) {
		array_push($country_names, $country->country_name);
	}
	return $country_names;
}


//populate states dropdown
function get_states() {
  $country = $_POST['country'];
  $states = get_states_by_country_new($country);
  $options = '';
  foreach ($states as $state) {
	  $options .= '<option value="' . $state . '">' . $state . '</option>';
  }
  echo $options;
  wp_die();
}
add_action('wp_ajax_get_states', 'get_states');
add_action('wp_ajax_nopriv_get_states', 'get_states');

function get_states_by_country_new($country_name) {
	$token = get_auth_token();
	$response = wp_remote_get("https://www.universal-tutorial.com/api/states/{$country_name}", array(
	  'headers' => array(
            "Authorization" => "Bearer {$token}",
		  "Accept" => "application/json")
  		)
	);
	$states = json_decode( wp_remote_retrieve_body( $response ) );
	$state_names = array();
	foreach ( $states as $state ) {
		array_push($state_names, $state->state_name);
	}
	return $state_names;
}


// add_shortcode( 'test-functions', 'create_country_options' );
// add_action('wp_head', 'update_registration_form');
add_shortcode( 'test-functions', 'create_course_data' );
