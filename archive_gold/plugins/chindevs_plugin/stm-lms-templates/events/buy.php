<?php
/**
 * @var $course_id
 * @var $price
 */

$has_course = STM_LMS_User::has_course_access( $course_id, false );

if ( is_user_logged_in() ) :
	?>

	<div class="stm-lms-buy-buttons stm-lms-buy-buttons-enterprise"
			data-lms-params='<?php echo wp_json_encode( compact( 'course_id' ) ); ?>'
			data-lms-modal="event-registration"
			data-target=".stm-lms-modal-event-registration">
		<div class="btn btn-default btn_big heading_font text-center">
			<span><?php esc_html_e( 'Register for Event', 'masterstudy-lms-learning-management-system-pro' ); ?></span>
		</div>
	</div>
	<?php
endif;
