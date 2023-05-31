<?php
function additional_emails( $emails ) {
    $emails['stm_lms_assignment_submitted'] = array(
        'section' => 'assignment',
        'notice'  => esc_html__(
            'Assignment Submitted and Waiting Review',
            'masterstudy-lms-learning-management-system-pro'
        ),
        'subject' => esc_html__(
            'Assignment status change.',
            'masterstudy-lms-learning-management-system-pro'
        ),
        'message' => esc_html__(
            'Your assignment has been checked',
            'masterstudy-lms-learning-management-system-pro'
        ),
        'vars' => array(
            'user_login'       => esc_html__( 'User Login', 'masterstudy-lms-learning-management-system-pro' ),
            'course_title'     => esc_html__( 'Course title', 'masterstudy-lms-learning-management-system-pro' ),
            'assignment_title' => esc_html__( 'Assignment title', 'masterstudy-lms-learning-management-system-pro' ),

        ),
    );

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

?>