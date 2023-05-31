<?php
/**
 * @var $course_id
 * @var $price
 */

$has_course = STM_LMS_User::has_course_access( $course_id, false );
$is_event = is_course_event($course_id);
$past_deadline = false;

if ($is_event) {
	$current_date = new DateTime( 'now', new DateTimeZone( 'UTC' ) );
    $close_date = get_event_deadline_date($course_id);
    $past_deadline = isset($close_date) && $close_date <=  $current_date->format('Y-m-d H:i:s');
}
$buttonText = $is_event ? "Gift Event" : "Gift Course";

if ( is_user_logged_in() && !$past_deadline ) :
	?>
	<div class="stm-lms-buy-buttons stm-lms-buy-buttons-enterprise"
			data-lms-params='<?php echo wp_json_encode( compact( 'course_id' ) ); ?>'
			data-lms-modal="gift-course"
			data-target=".stm-lms-modal-gift-course">
		<div class="btn btn-default btn_big heading_font text-center">
			<span><?php esc_html_e( $buttonText, 'masterstudy-lms-learning-management-system-pro' ); ?></span>
		</div>
	</div>
	<?php
endif;
