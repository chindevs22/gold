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

       	<!-- 	ChinDevs code to add an assignment points field	 -->
		<h5 class="editor_comment__points-earned"><?php esc_html_e( 'Points Earned', 'masterstudy-lms-learning-management-system-pro' ); ?></h5>
        <input style="display:inline-block" type="number" name="points_earned" id="points_earned" min="0" max="1000" step="1">
        <span style="display:inline-block"><?php
			$orig_assignment = get_post_meta($assignment_id, 'assignment_id', true);
			$total_points = get_post_meta($orig_assignment, 'total_points', true);
			if (!empty($total_points)) {
				echo "out of " . $total_points;
			} ?>
		</span>

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
