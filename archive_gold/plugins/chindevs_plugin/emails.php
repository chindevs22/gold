<?php
function additional_emails( $emails ) {

    // ChinDevs Created
	$emails['stm_lms_gifted_course_for_receiver'] = array(
		'section' => 'course',
        'notice'  => esc_html__(
            'Gifted Course (To Receiver)',
            'masterstudy-lms-learning-management-system-pro'
        ),
        'subject' => esc_html__(
            'You were gifted a course!',
            'masterstudy-lms-learning-management-system-pro'
        ),
        'message' => esc_html__( 'You were gifted course {{course_title}} by a donor! Get started studying by logging in!', 'masterstudy-lms-learning-management-system-pro' ),
        'vars' => array(
            'course_title'     => esc_html__( 'Course title', 'masterstudy-lms-learning-management-system-pro' ),
        ),
	);

    // ChinDevs Created
	$emails['stm_lms_gifted_course_for_donor'] = array(
		'section' => 'course',
        'notice'  => esc_html__(
            'Gifted Course (To Donor)',
            'masterstudy-lms-learning-management-system-pro'
        ),
        'subject' => esc_html__(
            'Your Gift was successfully sent',
            'masterstudy-lms-learning-management-system-pro'
        ),
        'message' => esc_html__( 'Your gift of course {{course_title}} to {{user_email}} was successful', 'masterstudy-lms-learning-management-system-pro' ),
        'vars' => array(
            'course_title'     => esc_html__( 'Course title', 'masterstudy-lms-learning-management-system-pro' ),
            'user_email'       => esc_html__( 'User Email',  'masterstudy-lms-learning-management-system-pro'),
        ),
	);

	// ChinDevs Created - Course completed for User
	$emails['stm_lms_course_completed_for_user'] = array(
		'section' => 'course',
        'notice'  => esc_html__(
            'Course Completed (for User)',
            'masterstudy-lms-learning-management-system-pro'
        ),
        'subject' => esc_html__(
            'You have completed the course!',
            'masterstudy-lms-learning-management-system-pro'
        ),
        'message' => esc_html__( 'Congrats, you completed the course: {{course_title}}', 'masterstudy-lms-learning-management-system-pro' ),
        'vars' => array(
            'course_title'     => esc_html__( 'Course title', 'masterstudy-lms-learning-management-system-pro' ),
        ),
	);
    // ChinDevs Created - assignment submitted for User
    $emails['stm_lms_assignment_submitted'] = array(
        'section' => 'assignment',
        'notice'  => esc_html__(
            'Assignment Submitted and Pending Review (for User)',
            'masterstudy-lms-learning-management-system-pro'
        ),
        'subject' => esc_html__(
            'Assignment Submitted and Pending Review',
            'masterstudy-lms-learning-management-system-pro'
        ),
        'message' => esc_html__( 'Your Assignment {{assignment_title}} for course ({{course_title}}) has been submitted.', 'masterstudy-lms-learning-management-system-pro' ),
        'vars' => array(
            'course_title'     => esc_html__( 'Course title', 'masterstudy-lms-learning-management-system-pro' ),
            'assignment_title' => esc_html__( 'Assignment title', 'masterstudy-lms-learning-management-system-pro' ),
        ),
    );

    // Just Changed the Name -- Checked (no change)
    $emails['stm_lms_course_added'] = array(
        'section' => 'instructors',
        'notice'  => esc_html__( 'Course Created From FE (To SuperAdmin)', 'masterstudy-lms-learning-management-system-pro' ),
        'subject' => esc_html__( 'Course added', 'masterstudy-lms-learning-management-system-pro' ),
        'message' => esc_html__( 'Course {{course_title}} created by instructor, your ({{user_login}}). Please review this information from the admin Dashboard', 'masterstudy-lms-learning-management-system-pro' ),
        'vars'    => array(
// 					'action'       => esc_html__( 'Added or updated action made by instructor', 'masterstudy-lms-learning-management-system-pro' ),
            'user_login'   => esc_html__( 'Instructor login', 'masterstudy-lms-learning-management-system-pro' ),
            'course_title' => esc_html__( 'Course name', 'masterstudy-lms-learning-management-system-pro' ),
        ),
    );

    // Just Changed the Name -- Checked (no change)
    $emails['stm_lms_course_published'] = array(
        'section' => 'instructors',
        'notice'  => esc_html__(
            'Course Pending -> Publish (To Instructor)',
            'masterstudy-lms-learning-management-system-pro'
        ),
        'subject' => esc_html__(
            'Course published',
            'masterstudy-lms-learning-management-system-pro'
        ),
        'message' => esc_html__(
            'Your course - {{course_title}} was approved, and is now live on the website',
            'masterstudy-lms-learning-management-system-pro'
        ),
        'vars'    => array(
            'course_title' => esc_html__(
                'Course Title',
                'masterstudy-lms-learning-management-system-pro'
            ),
        ),
    );

    // ChinDevs Created ?? (apparently just a name change?) -- Checked
    $emails['stm_lms_course_created_for_instructor'] = array(
        'section' => 'instructors',
        'notice'  => esc_html__(
            'Course Created from FE (To Instructor)',
            'masterstudy-lms-learning-management-system-pro'
        ),
        'subject' => esc_html__(
            'You have created a course!',
            'masterstudy-lms-learning-management-system-pro'
        ),
        'message' => 'You have created {{course_title}} and it is now available to students.',
        'vars'    => array(
            'course_title' => esc_html__(
                'Course title',
                'masterstudy-lms-learning-management-system-pro'
            ),
        ),
    );

    // ChinDevs Created -- Checked (class-manage-course)
    $emails['stm_lms_course_updated_for_instructor'] = array(
        'section' => 'instructors',
        'notice'  => esc_html__(
            'Course Updated from FE (To Instructor)',
            'masterstudy-lms-learning-management-system-pro'
        ),
        'subject' => esc_html__(
            'You have updated a course!',
            'masterstudy-lms-learning-management-system-pro'
        ),
        'message' => 'You have updated {{course_title}} and it is now available to students.',
        'vars'    => array(
            'course_title' => esc_html__(
                'Course title',
                'masterstudy-lms-learning-management-system-pro'
            ),
        ),
    );

    // ChinDevs Created -- ?? have no clue whats going on here
    $emails['stm_lms_user_enrolled_in_course']  = array(
        'section' => 'instructors',
        'notice'  => esc_html__(
            'User Enrolled in Course (To Instructor)',
            'masterstudy-lms-learning-management-system-pro'
        ),
        'subject' => esc_html__(
            'A new user has enrolled in your course!',
            'masterstudy-lms-learning-management-system-pro'
        ),
        'message' => 'You have a new user {{login}} enrolled in your course {{course_title}}',
        'vars'    => array(
            'login' => esc_html__(
                'User login',
                'masterstudy-lms-learning-management-system-pro'
            ),
            'course_title' => esc_html__(
                'Course title',
                'masterstudy-lms-learning-management-system-pro'
            ),
        ),
    );

    // Just Changed the Name -- Checked (no change)
    $emails['stm_lms_course_added_to_user'] = array(
        'section' => 'course',
        'notice'  => esc_html__(
            'User enrolled in course (To SuperAdmin)',
            'masterstudy-lms-learning-management-system-pro'
        ),
        'subject' => esc_html__(
            'Course added to User',
            'masterstudy-lms-learning-management-system-pro'
        ),
        'message' => 'Course {{course_title}} was added to {{login}}.',
        'vars'    => array(
            'course_title' => esc_html__(
                'Course title',
                'masterstudy-lms-learning-management-system-pro'
            ),
            'login'        => esc_html__(
                'Login',
                'masterstudy-lms-learning-management-system-pro'
            ),
        ),
    );

    // ChinDevs Created -- Checked (class manage course)
    $emails['stm_lms_course_updated_for_user'] = array(
        'section' => 'course',
        'notice'  => esc_html__(
            'Course Updated from FE (To User)',
            'masterstudy-lms-learning-management-system-pro'
        ),
        'subject' => esc_html__(
            'Course Updated.',
            'masterstudy-lms-learning-management-system-pro'
        ),
        'message' => 'Course {{course_title}} has been updated with some new material. Check it out!',
        'vars'    => array(
            'course_title' => esc_html__(
                'Course title',
                'masterstudy-lms-learning-management-system-pro'
            ),
        ),
    );

    // Renamed and Edited Code -- Checked (no change)
    $emails['stm_lms_course_available_for_user'] = array(
        'section' => 'course',
        'notice'  => esc_html__(
            'User enrolled in course (To User)',
            'masterstudy-lms-learning-management-system-pro'
        ),
        'subject' => esc_html__(
            'Course added.',
            'masterstudy-lms-learning-management-system-pro'
        ),
        'message' => 'Course {{course_title}} is now available to learn.',
        'vars'    => array(
            'course_title' => esc_html__(
                'Course title',
                'masterstudy-lms-learning-management-system-pro'
            ),
        ),
    );

    // Renamed and Edited Code -- Checked (class-announcements.php)
    $emails['stm_lms_announcement_from_instructor_to_user'] = array(
        'section' => 'course',
        'notice'  => esc_html__(
            'Announcement Created from FE (To User)',
            'masterstudy-lms-learning-management-system-pro'
        ),
        'subject' => esc_html__(
            'Announcement from the Instructor',
            'masterstudy-lms-learning-management-system-pro'
        ),
        'message' => '{{mail}}',
        'vars'    => array(
            'mail' => esc_html__(
                'Instructor message',
                'masterstudy-lms-learning-management-system-pro'
            ),
        ),
    );

    // Renamed and Edited Code  -- Checked (class-announcements.php)
    $emails['stm_lms_announcement_from_instructor'] = array(
        'section' => 'instructors',
        'notice'  => esc_html__(
            'Announcement Created from FE (To Instructor)',
            'masterstudy-lms-learning-management-system-pro'
        ),
        'subject' => esc_html__(
            'Announcement from the Instructor',
            'masterstudy-lms-learning-management-system-pro'
        ),
        'message' => '{{mail}}',
        'vars'    => array(
            'mail' => esc_html__(
                'Instructor message',
                'masterstudy-lms-learning-management-system-pro'
            ),
        ),
    );

	return $emails;
}
add_filter( 'stm_lms_email_manager_emails', 'additional_emails' );


add_filter ('chindevs-course-completed-email', 'student_course_completion_email' );
function student_course_completion_email( $data ) {
	$full_progress = $data['course']['progress_percent'] == 100;
	$course_completed = $data['course_completed'] == 1;
	$user = get_user_by( 'ID', $data['course']['user_id'] );
	$message = sprintf(
		/* translators: %1$s Course Title */
		esc_html__( 'Congrats! You completed the course: %1$s', 'masterstudy-lms-learning-management-system' ),
		 $data['title'],
	);

	if ($full_progress && $course_completed) {
		//send email to student
		STM_LMS_Mails::send_email(
			'You completed a course!',
			$message,
			$user->user_email,
			array(),
			'stm_lms_course_completed_for_user',
			array('course_title' => $data['title'])
		);
	}
	return $data;
}

// Gift Course - Course Added Email
function gift_course_emails($user, $course_title, $donor) {

    $donorMessage = sprintf(
    /* translators: %1$s Course Title, %2$s User Email */
        esc_html__( 'Your course donation of %1$s for %2$s was successfully sent!', 'masterstudy-lms-learning-management-system' ),
        $course_title,
        $user->user_email,
    );

    STM_LMS_Mails::send_email(
        'You donated a course!',
        $donorMessage,
        $donor->user_email,
        array(),
        'stm_lms_gifted_course_for_donor',
        array( 'user_email' => $user->user_email, 'course_title' =>  $course_title)
    );

    $receiverMessage = sprintf(
        /* translators: %1$s Course Title, %2$s User Login */
        esc_html__( 'Your were donated the course: %1$s, from a donor!', 'masterstudy-lms-learning-management-system' ),
        $course_title,
    );

    STM_LMS_Mails::send_email(
        'You were gifted this course by a donor!',
        $receiverMessage,
        $user->user_email,
        array(),
        'stm_lms_gifted_course_for_receiver',
        array( 'course_title' =>  $course_title)
    );
}
?>