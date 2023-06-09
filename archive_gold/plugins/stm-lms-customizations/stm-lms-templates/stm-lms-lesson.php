<?php if ( ! defined( 'ABSPATH' ) ) {
	exit; //Exit if accessed directly
}

/**
 * @var $lesson_id
 * @var $lms_page_path
 */

global $wp;
$user = STM_LMS_User::get_current_user();

if ( empty( $user['id'] ) ) {
	setcookie( 'redirect_trial_lesson', home_url( $wp->request ), time() + ( 3600 ), '/' );
	wp_safe_redirect( STM_LMS_USER::user_page_url() );
}

$course = get_page_by_path( $lms_page_path, OBJECT, 'stm-courses' );

$post_id = intval( $course->ID );
$item_id = intval( $lesson_id );

add_filter(
	'pre_get_document_title',
	function ( $title ) use ( $item_id ) {
		return get_the_title( $item_id );
	}
);

do_action( 'stm_lms_before_item_template_start', $post_id, $item_id );

$is_previewed = ( ! empty( $is_previewed ) ) ? $is_previewed : false;

$content_type             = ( get_post_type( $item_id ) === 'stm-lessons' ) ? 'lesson' : get_post_type( $item_id );
$content_type             = ( get_post_type( $item_id ) === 'stm-quizzes' ) ? 'quiz' : $content_type;
$stm_lms_question_sidebar = apply_filters( 'stm_lms_show_question_sidebar', true );
$lesson_type              = '';
if ( 'lesson' === $content_type ) {
	$lesson_type = get_post_meta( $item_id, 'type', true );
	stm_lms_register_style( 'lesson_' . $lesson_type );
}

STM_LMS_Templates::show_lms_template(
	'lesson/header',
	compact(
		'post_id',
		'item_id',
		'is_previewed',
		'content_type',
		'lesson_type'
	)
);

$custom_css = get_post_meta( $item_id, '_wpb_shortcodes_custom_css', true );

stm_lms_register_style( 'lesson', array(), $custom_css );
do_action( 'stm_lms_template_main' );

$has_access   = STM_LMS_User::has_course_access( $post_id, $item_id );
$has_preview  = STM_LMS_Lesson::lesson_has_preview( $item_id );
$is_previewed = STM_LMS_Lesson::is_previewed( $post_id, $item_id );
$lesson_style = STM_LMS_Options::get_option( 'lesson_style', 'default' );
if ( $has_access || $has_preview ) :

	if ( apply_filters( 'stm_lms_stop_item_output', false, $post_id ) ) {
		do_action( 'stm_lms_before_item_lesson_start', $post_id, $item_id );
	} else {
		if ( 'classic' === $lesson_style && 'stream' !== $lesson_type && 'zoom_conference' !== $lesson_type ) {
			stm_lms_register_style( 'lesson/style_classic', array() );
		}
		if ( ! $is_previewed ) {
			do_action( 'stm_lms_lesson_started', $post_id, $item_id, '' );
		}
		stm_lms_update_user_current_lesson( $post_id, $item_id );
		?>
		<div class="stm-lms-course__overlay"></div>

		<?php STM_LMS_Templates::show_lms_template( 'modals/preloader' ); ?>

		<div class="stm-lms-wrapper <?php echo esc_attr( get_post_type( $item_id ) . ' lesson_style_' . $lesson_style ); ?>">

			<div class="stm-lms-course__curriculum">
				<?php
				STM_LMS_Templates::show_lms_template(
					'lesson/curriculum',
					array(
						'post_id'     => $post_id,
						'item_id'     => $item_id,
						'lesson_type' => $lesson_type,
					)
				);
				?>
			</div>

			<?php
			if ( ! $is_previewed ) {
				?>
				<?php if ( $stm_lms_question_sidebar ) : ?>
					<h3>
                        <?php if($content_type == 'quiz'): ?>
                            <a href="<?php echo add_query_arg( 're-take', '1', STM_LMS_Helpers::get_current_url() ); ?>" class="stm-lms-course__sidebar_refresh"
                                 title="<?php _e('Re-take Quiz','slms'); ?>" data-reload="<?php echo add_query_arg( 're-take', '1', STM_LMS_Helpers::get_current_url() ); ?>">
                                <i class="fa fa-refresh"></i>
                            </a>
                            <div class="stm-lms-course__sidebar_save slms_save_progeress" title="<?php _e('Save Progress','slms'); ?>">
                                <i class="fa fa-save"></i>
                            </div>
                        <?php endif; ?>
						<div class="stm-lms-course__sidebar_toggle">
							<i class="fa fa-question"></i>
						</div>
					</h3>
				<?php endif ?>
				<?php
					STM_LMS_Templates::show_lms_template(
						'lesson/finish_score',
						array(
							'post_id' => $post_id,
							'item_id' => $item_id,
						)
					);
			}
			?>

			<div class="stm-lms-course__sidebar">
				<div class="stm-lesson_sidebar__close">
					<i class="lnr lnr-cross"></i>
				</div>
				<?php
				if ( ! $is_previewed ) {
					STM_LMS_Templates::show_lms_template(
						'lesson/sidebar',
						compact(
							'post_id',
							'item_id',
							'is_previewed'
						)
					);
				}
				?>
			</div>
			<?php

			$item_content = apply_filters( 'stm_lms_show_item_content', true, $post_id, $item_id );

			if ( $item_content ) {
				?>
				<div id="stm-lms-lessons">
					<div class="stm-lms-course__content">

						<?php
						STM_LMS_Templates::show_lms_template( 'lesson/content_top_wrapper_start', compact( 'lesson_type' ) );
						STM_LMS_Templates::show_lms_template( 'lesson/content_top', compact( 'post_id', 'item_id' ) );
						STM_LMS_Templates::show_lms_template( 'lesson/content_top_wrapper_end', compact( 'lesson_type' ) );
						?>

						<div class="stm-lms-course__content_wrapper">
							<?php
							STM_LMS_Templates::show_lms_template( 'lesson/content_wrapper_start', compact( 'lesson_type' ) );
							echo apply_filters( 'stm_lms_lesson_content', STM_LMS_Templates::load_lms_template( 'course/parts/' . $content_type, compact( 'post_id', 'item_id', 'is_previewed' ) ), $post_id, $item_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							STM_LMS_Templates::show_lms_template( 'lesson/content_wrapper_end', compact( 'lesson_type', 'item_id' ) );
							?>
						</div>
					</div>
				</div>
				<?php
			}

			echo apply_filters( 'stm_lms_course_item_content', $content = '', $post_id, $item_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
		</div>
		<?php
	}

else :
	stm_lms_register_style( 'lesson_locked' );
	stm_lms_register_script( 'lesson_locked', array(), false, "stm_lms_course_id = {$post_id};" );

	?>

	<div class="stm_lms_locked_lesson__overlay"></div>
	<div class="stm_lms_locked_lesson__popup">
		<div class="stm_lms_locked_lesson__popup_inner">
			<h3><?php esc_html_e( 'Hey there, great course, right? Do you like this course?', 'masterstudy-lms-learning-management-system' ); ?></h3>
			<p>
				<?php esc_html_e( 'All of the most interesting lessons further. In order to continue you just need to purchase it', 'masterstudy-lms-learning-management-system' ); ?>
			</p>
			<?php
			STM_LMS_Templates::show_lms_template(
				'global/buy-button',
				array(
					'course_id'  => $post_id,
					'item_id'    => $item_id,
					'has_access' => false,
				)
			);
			?>
			<a class="stm_lms_locked_lesson__popup_close" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
				<i class="lnricons-cross"></i>
			</a>
		</div>
	</div>


	<div class="stm-lms-course__overlay"></div>
	<?php STM_LMS_Templates::show_lms_template( 'modals/preloader' ); ?>
	<div class="stm-lms-wrapper <?php echo esc_attr( get_post_type( $item_id ) ); ?>">

		<div class="stm-lms-course__curriculum">
			<?php
			STM_LMS_Templates::show_lms_template(
				'lesson/curriculum',
				array(
					'post_id' => $post_id,
					'item_id' => $item_id,
				)
			);
			?>
		</div>

		<?php
		if ( ! $is_previewed && $stm_lms_question_sidebar ) {
			?>
			<h3>
                <?php if($content_type == 'quiz'): ?>
                    <a href="<?php echo add_query_arg( 're-take', '1', STM_LMS_Helpers::get_current_url() ); ?>" class="stm-lms-course__sidebar_refresh"
                         title="<?php _e('Re-take Quiz','slms'); ?>" data-reload="<?php echo add_query_arg( 're-take', '1', STM_LMS_Helpers::get_current_url() ); ?>">
                        <i class="fa fa-refresh"></i>
                    </a>
                    <div class="stm-lms-course__sidebar_save" title="<?php _e('Save Progress','slms'); ?>">
                        <i class="fa fa-save"></i>
                    </div>
                <?php endif; ?>
				<div class="stm-lms-course__sidebar_toggle">
					<i class="fa fa-question"></i>
				</div>
			</h3>
		<?php } ?>

		<div class="stm-lms-course__sidebar">
			<div class="stm-lesson_sidebar__close">
				<i class="lnr lnr-cross"></i>
			</div>
			<?php
			if ( ! $is_previewed ) {
				STM_LMS_Templates::show_lms_template(
					'lesson/sidebar',
					compact(
						'post_id',
						'item_id',
						'is_previewed'
					)
				);
			}
			?>
		</div>

		<div id="stm-lms-lessons">
			<div class="stm-lms-course__content">

				<?php
				STM_LMS_Templates::show_lms_template( 'lesson/content_top_wrapper_start', compact( 'lesson_type' ) );
				STM_LMS_Templates::show_lms_template( 'lesson/content_top', compact( 'post_id', 'item_id' ) );
				STM_LMS_Templates::show_lms_template( 'lesson/content_top_wrapper_end', compact( 'lesson_type' ) );
				?>

				<div class="stm-lms-course__content_wrapper">
					<?php STM_LMS_Templates::show_lms_template( 'lesson/content_wrapper_start', compact( 'lesson_type' ) ); ?>

					<h4 class="text-center">
						<?php esc_html_e( 'Lesson is locked. Please Buy course to proceed.', 'masterstudy-lms-learning-management-system' ); ?>
					</h4>

					<?php STM_LMS_Templates::show_lms_template( 'lesson/content_wrapper_end', compact( 'lesson_type' ) ); ?>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>

<?php
if ( ! $is_previewed ) {
	STM_LMS_Templates::show_lms_template(
		'lesson/navigation',
		compact(
			'post_id',
			'item_id',
			'lesson_type'
		)
	);
}

do_action( 'template_redirect' );

STM_LMS_Templates::show_lms_template(
	'lesson/footer',
	compact( 'post_id', 'item_id', 'is_previewed' )
);

do_action( 'stm_lms_template_main_after' );

?>
