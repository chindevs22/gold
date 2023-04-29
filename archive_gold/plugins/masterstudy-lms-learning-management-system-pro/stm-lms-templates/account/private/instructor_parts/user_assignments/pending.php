<?php
/**
 * @var $assignment_id
 * @var $assignment
 */

stm_lms_register_script( 'accept_assignment' );

?>

<div class="user_assingment_pending">
	<div class="editor_comment">

		<h4 class="editor_comment__title"><?php esc_html_e( 'Your comment', 'masterstudy-lms-learning-management-system-pro' ); ?></h4>
		<?php wp_editor( '', 'assignment_' . $assignment_id, array( 'quicktags' => false ) ); ?>

		<!-- 	ChinDevs code to add an assignment grade field	 -->
		<h5 class="editor_comment__grade"><?php esc_html_e( 'Grade', 'masterstudy-lms-learning-management-system-pro' ); ?></h5>
        <input type="number" name="assignment_grade" id="assignment_grade" min="0" max="100" step="1">


		<div class="user_assingment_actions">
			<a href="#" class="btn btn-default approve">
				<i class="fa fa-check"></i>
				<?php esc_html_e( 'Approve', 'masterstudy-lms-learning-management-system-pro' ); ?>
			</a>
			<a href="#" class="btn btn-default reject">
				<i class="fa fa-times"></i>
				<?php esc_html_e( 'Reject', 'masterstudy-lms-learning-management-system-pro' ); ?>
			</a>
		</div>

	</div>
</div>
