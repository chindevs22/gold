<?php
/**
 * @var $course
 */

$course_id = intval($course['id']);
$price = SLMS_Course_Price::get($course_id);
$sale_price = SLMS_Course_Price::get_sale($course_id);
?>

<div class="ms_lms_courses_card_item_info_price">
	<?php if ( ! isset( $course['not_single_sale'] ) || ! $course['not_single_sale'] ) { ?>
		<a href="<?php echo esc_url( $course['url'] ); ?>" class="ms_lms_courses_card_item_info_price_preview">
			<?php esc_html_e( 'Preview this course', 'masterstudy-lms-learning-management-system' ); ?>
		</a>
		<div class="ms_lms_courses_card_item_info_price_single <?php echo ( ! empty( $sale_price ) ) ? 'sale' : ''; ?>">
			<span><?php echo esc_html( ( 0 != $price ) ? SLMS_Course_Price::display_price( $price, $course_id ) : __( 'Free', 'masterstudy-lms-learning-management-system' ) ); ?></span>
		</div>
		<?php if ( ! empty( $sale_price ) ) { ?>
			<div class="ms_lms_courses_card_item_info_price_sale">
				<span><?php echo esc_html( SLMS_Course_Price::display_price( $sale_price, $course_id ) ); ?></span>
			</div>
		<?php } ?>
	<?php } else { ?>
		<a href="<?php echo esc_url( $course['url'] ); ?>" class="ms_lms_courses_card_item_info_price_preview">
			<?php esc_html_e( 'Preview this course', 'masterstudy-lms-learning-management-system' ); ?>
		</a>
		<div class="ms_lms_courses_card_item_info_price_single subscription">
			<i class="stmlms-subscription"></i>
			<span><?php esc_html_e( 'Members Only', 'masterstudy-lms-learning-management-system' ); ?></span>
		</div>
	<?php } ?>
</div>
