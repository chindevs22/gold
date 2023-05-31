<?php
$terms = wp_get_post_terms( get_the_ID(), 'stm_lms_course_taxonomy' );
if ( ! empty( $terms ) ) {
	?>
	<div class="pull-left xs-product-cats-left">
		<div class="meta-unit categories clearfix">
			<div class="pull-left">
				<i class="fa-icon-stm_icon_category secondary_color"></i>
			</div>
			<div class="meta_values">
				<div class="label h6"><?php esc_html_e( 'Category:', 'masterstudy-lms-learning-management-system' ); ?></div>
				<div class="value h6">
					<a href="<?php echo esc_url( STM_LMS_Course::courses_page_url() . '?terms[]=' . $terms[0]->term_id . '&category[]=' . $terms[0]->term_id ); ?>"><?php echo esc_html( $terms[0]->name ); ?></a>
				</div>
			</div>
		</div>
	</div>
<?php } ?>
