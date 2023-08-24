<?php
/**
 * @var $course_id
 */

$video       = get_post_meta( $course_id, 'udemy_content_length_video', true );
$assets      = get_post_meta( $course_id, 'udemy_num_additional_assets', true );
$articles    = get_post_meta( $course_id, 'udemy_num_article_assets', true );
$certificate = get_post_meta( $course_id, 'udemy_has_certificate', true );

$freeLesson	 = get_post_meta( $course_id, 'free_lesson', true );
$freeLessonURL = get_permalink( $course_id ) . $freeLesson;
$courseFiles = trim(get_post_meta( $course_id, 'course_files', true ),'[]');
$discussionForum = get_post_meta( $course_id, 'discussion_forum', true );
$discussionForumURL = get_site_url() . "/forums/forum/" . $discussionForum;
?>

<div class="stm_lms_udemy_includes">

	<h4><?php esc_html_e( 'Includes', 'masterstudy-lms-learning-management-system-pro' ); ?></h4>


	<?php if ( ! empty( $video ) ) : ?>
		<div class="stm_lms_udemy_include heading_font">
			<i class="lnricons-play primary_color"></i>
			<?php
			printf(
				/* translators: %s Hours */
				esc_html__( '%s hours on-demand video', 'masterstudy-lms-learning-management-system-pro' ),
				round( $video / 3600, 0 ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			);
			?>
		</div>
		<?php
	else :
		$video = get_post_meta( $course_id, 'video_duration', true );
		if ( ! empty( $video ) ) :
			?>
		<div class="stm_lms_udemy_include heading_font">
			<i class="lnricons-play primary_color"></i>
			<?php echo esc_html( $video ); ?>
		</div>
			<?php
		endif;
	endif;
	?>


	<?php if ( ! empty( $articles ) ) : ?>
		<div class="stm_lms_udemy_include heading_font">
			<i class="lnricons-text-format primary_color"></i>
			<?php
			printf(
				/* translators: %s Articles */
				_n( '%s article', '%s articles', $articles, 'masterstudy-lms-learning-management-system-pro' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				$articles // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			);
			?>
		</div>
	<?php endif; ?>


	<?php if ( $freeLesson ) : ?>
		<div class="stm_lms_udemy_include heading_font">
			<i class="lnricons-laptop-phone primary_color"></i>
			<a href="<?php echo  $freeLessonURL ?>"  target="_blank">
				<?php esc_html_e( 'View first lesson free!', 'masterstudy-lms-learning-management-system-pro' ); ?>
			</a>
		</div>
	<?php endif; ?>

	<?php if ( $courseFiles ) : ?>
		<?php foreach(explode(",", $courseFiles) as $eachCourseFile): ?>
		<div class="stm_lms_udemy_include heading_font">
			<i class="lnricons-laptop-phone primary_color"></i>
			<a href="<?php echo get_the_guid( $eachCourseFile ) ?>"  target="_blank">
				<?php echo get_the_title( $eachCourseFile ) ?>
			</a>
		</div>
		<?php endforeach; ?>
	<?php endif; ?>

	<?php if ( $discussionForum ) : ?>
		<div class="stm_lms_udemy_include heading_font">
			<i class="lnricons-laptop-phone primary_color"></i>
			<a href="<?php echo  $discussionForumURL ?>"  target="_blank">
				<?php esc_html_e( 'Discussion Forum', 'masterstudy-lms-learning-management-system-pro' ); ?>
			</a>
		</div>
	<?php endif; ?>

	<!--div class="stm_lms_udemy_include heading_font">
		<i class="lnricons-clock3 primary_color"></i>
		<a href="https://drive.google.com/file/d/1L5iqfLK2kg2y8kdlFF-vEBZIsH2ed2-v/view?usp=sharing"-->
			<!--?php esc_html_e( 'Sample Questionnaire', 'masterstudy-lms-learning-management-system-pro' ); ?-->
		<!--/a>
	</div-->

	<?php if ( $certificate ) : ?>
		<div class="stm_lms_udemy_include heading_font">
			<i class="lnricons-license2 primary_color"></i>
				<?php esc_html_e( 'Certificate of Completion', 'masterstudy-lms-learning-management-system-pro' ); ?>
		</div>
	<?php endif; ?>

</div>


