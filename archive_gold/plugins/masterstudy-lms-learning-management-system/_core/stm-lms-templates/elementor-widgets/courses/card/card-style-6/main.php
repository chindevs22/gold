<?php
foreach ( $courses as $course ) {
	$course = STM_LMS_Courses::get_course_submetas( $course );
	?>
	<div class="ms_lms_courses_card_item">
		<div class="ms_lms_courses_card_item_wrapper">
			<?php if ( ! empty( $course['featured'] ) ) { ?>
				<div class="ms_lms_courses_card_item_featured <?php echo esc_attr( ( ! empty( $card_data['featured_position'] ) ) ? $card_data['featured_position'] : '' ); ?>">
					<span><?php echo esc_html__( 'Featured', 'masterstudy-lms-learning-management-system' ); ?></span>
				</div>
				<?php
			}
			if ( ! empty( $course['current_status'] ) ) {
				?>
				<div class="ms_lms_courses_card_item_status <?php echo esc_attr( ( ! empty( $card_data['status_presets'] ) ) ? $card_data['status_presets'] : '' ); ?> <?php echo esc_attr( ( ! empty( $card_data['status_position'] ) ) ? $card_data['status_position'] : '' ); ?> <?php echo esc_attr( ( ! empty( $course['current_status']['status'] ) ) ? $course['current_status']['status'] : '' ); ?>">
					<span><?php echo esc_html( $course['current_status']['label'] ); ?></span>
				</div>
			<?php } ?>
			<a href="<?php echo esc_url( $course['url'] ); ?>" class="ms_lms_courses_card_item_image_link">
				<img src="<?php echo esc_url( $course['image'] ); ?>" class="ms_lms_courses_card_item_image">
			</a>
			<div class="ms_lms_courses_card_item_info">
				<?php if ( ! empty( $card_data['show_category'] ) ) { ?>
					<span class="ms_lms_courses_card_item_info_category"><?php echo wp_kses_post( $course['terms'] ); ?></span>
				<?php } ?>
					<a href="<?php echo esc_url( $course['url'] ); ?>" class="ms_lms_courses_card_item_info_title">
						<h3><?php echo esc_html( $course['post_title'] ); ?></h3>
					</a>
				<?php
				if ( ! empty( $card_data['show_progress'] && $course['progress'] > 0 ) ) {
					STM_LMS_Templates::show_lms_template(
						'elementor-widgets/courses/card/global/progress-bar',
						array(
							'course'    => $course,
							'card_data' => $card_data,
						)
					);
				}
				if ( ! empty( $card_data['show_wishlist'] ) ) {
					?>
					<div class="ms_lms_courses_card_item_info_wishlist">
						<?php STM_LMS_Templates::show_lms_template( 'global/wish-list', array( 'course_id' => $course['id'] ) ); ?>
					</div>
					<?php
				}
				if ( ! empty( $card_data['show_slots'] ) && ! ( 'empty' === $meta_slots['card_slot_1'] && 'empty' === $meta_slots['card_slot_2'] ) ) {
					?>
					<div class="ms_lms_courses_card_item_info_meta">
						<?php
						if ( 'empty' !== $meta_slots['card_slot_1'] ) {
							STM_LMS_Templates::show_lms_template(
								'elementor-widgets/courses/card/global/meta-slot/main',
								array(
									'meta_slot' => $meta_slots['card_slot_1'],
									'course'    => $course,
								)
							);
						}
						if ( 'empty' !== $meta_slots['card_slot_2'] ) {
							STM_LMS_Templates::show_lms_template(
								'elementor-widgets/courses/card/global/meta-slot/main',
								array(
									'meta_slot' => $meta_slots['card_slot_2'],
									'course'    => $course,
								)
							);
						}
						?>
					</div>
					<?php
				}
				if ( ! empty( $card_data['show_divider'] ) ) {
					?>
					<span class="ms_lms_courses_card_item_info_divider"></span>
					<?php
				}
				if ( ! ( empty( $card_data['show_rating'] ) && empty( $card_data['show_price'] ) ) ) {
					?>
					<div class="ms_lms_courses_card_item_info_bottom_wrapper">
						<?php
						if ( ! empty( $card_data['show_rating'] ) ) {
							STM_LMS_Templates::show_lms_template(
								'elementor-widgets/courses/card/global/rating',
								array(
									'card_data' => $card_data,
									'course'    => $course,
								)
							);
						}
						if ( ! empty( $card_data['show_price'] ) ) {
							STM_LMS_Templates::show_lms_template(
								'elementor-widgets/courses/card/global/price',
								array(
									'card_data' => $card_data,
									'course'    => $course,
								)
							);
						}
						?>
					</div>
				<?php } ?>
				<div class="ms_lms_courses_card_item_info_price_preview_wrapper">
					<a href="<?php echo esc_url( $course['url'] ); ?>" class="ms_lms_courses_card_item_info_price_preview">
						<?php esc_html_e( 'Preview this course', 'masterstudy-lms-learning-management-system' ); ?>
					</a>
				</div>
			</div>
		</div>
	</div>
	<?php
}
