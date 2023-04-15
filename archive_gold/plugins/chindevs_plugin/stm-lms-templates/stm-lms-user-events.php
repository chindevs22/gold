<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} // Exit if accessed directly ?>

<?php

error_log("inside user-events template");
get_header();
stm_lms_register_style( 'enterprise_groups' );
stm_lms_register_script( 'enterprise-groups', array( 'vue.js', 'vue-resource.js' ) );
wp_localize_script(
	'stm-lms-enterprise-groups',
	'stm_lms_groups',
	array(
		'limit'        => STM_LMS_Enterprise_Courses::get_group_common_limit(),
		'translations' => array(
			'group_limit' => esc_html__( 'Group Limit:', 'masterstudy-lms-learning-management-system-pro' ),
		),
	)
);
do_action( 'stm_lms_template_main' );
?>

<?php STM_LMS_Templates::show_lms_template( 'modals/preloader' ); ?>

	<div class="stm-lms-wrapper stm-lms-wrapper--gradebook user-account-page">

		<div id="stm_lms_user_events" v-bind:class="{'loading': loading}">

			<div class="container">

				<?php do_action( 'stm_lms_admin_after_wrapper_start', STM_LMS_User::get_current_user() ); ?>
				<?php
					$user_events = new User_Events();
					$params = array(
						'limit' => 10,
						'user'  => get_current_user_id()
					);
					$event_ids = $user_events->generate( $params );
				?>
				<?php if ( is_array( $event_ids ) ) : ?>
					<div class="event-ids">
						<?php foreach ( $event_ids as $id ) : ?>
							<div><?php echo do_shortcode(
								"[tribe_event_inline id=$id]
									{url}{thumbnail}{/url}
									<h4>{title:linked}</h4>
									<p>Time: {start_date} @ {start_time} – {end_date} @ {end_time}</p>
									<p>{excerpt}</p>
									[/tribe_event_inline]"
						)?></div>
						<?php endforeach; ?>
					</div>
            	<?php endif; ?>


			</div>

		</div>

		<?php do_action( 'stm_lms_after_groups_end' ); ?>

	</div>

<?php get_footer(); ?>
<?php
					$user_events = new User_Events();
					$params = array(
						'limit' => 10,
						'user'  => get_current_user_id()
					);
					$event_ids = $user_events->generate( $params );
?>
<div id="user_events">
	<div class="stm-lms-user-events">

		<div class="multiseparator"></div>

		<div class="stm_lms_instructor_courses__grid">
			<div class="stm_lms_instructor_courses__single">
			    <?php foreach ( $event_ids as $id ) : ?>

				<div class="stm_lms_instructor_courses__single__inner">
					<div class="stm_lms_instructor_courses__single--image">

						<div class="image_wrapper">
						    <?php echo do_shortcode("[tribe_event_inline id=$id]
                                                     {url}{thumbnail}{/url}
                                                     [/tribe_event_inline]") ?>
						</div>

					</div>

// 					<div class="stm_lms_instructor_courses__single--inner">
//
// 						<div class="stm_lms_instructor_courses__single--terms" v-if="course.terms">
// 							<div class="stm_lms_instructor_courses__single--term"
// 								 v-for="(term, key) in course.terms"
// 								 v-html="term" v-if="key === 0">
// 							</div>
// 						</div>
//
// 						<div class="stm_lms_instructor_courses__single--title">
// 							<a v-bind:href="course.link">
// 								<h5 v-html="course.title"></h5>
// 							</a>
// 						</div>
//
// 						<div class="stm_lms_instructor_courses__single--progress">
// 							<div class="stm_lms_instructor_courses__single--progress_top">
// 								<div class="stm_lms_instructor_courses__single--duration" v-if="course.duration">
// 									<i class="far fa-clock"></i>
// 									{{ course.duration }}
// 								</div>
// 								<div class="stm_lms_instructor_courses__single--completed">
// 									{{ course.progress_label }}
// 								</div>
// 							</div>
//
// 							<div class="stm_lms_instructor_courses__single--progress_bar">
// 								<div class="stm_lms_instructor_courses__single--progress_filled"
// 									 v-bind:style="{'width' : course.progress + '%'}"></div>
// 							</div>
//
// 						</div>
//
// 						<div class="stm_lms_instructor_courses__single--enroll">
// 							<a v-if="course.expiration.length && course.is_expired || course.membership_expired || course.membership_inactive" class="btn btn-default"
// 							   :href="course.url" target="_blank">
// 								<span><?php esc_html_e( 'Preview Course', 'masterstudy-lms-learning-management-system' ); ?></span>
// 							</a>
// 							<a v-bind:href="course.current_lesson_id" class="btn btn-default"
// 							   v-bind:class="{'continue' : course.progress !== '0'}"
// 							   v-else>
// 								<span v-if="course.progress === '0'"><?php esc_html_e( 'Start Course', 'masterstudy-lms-learning-management-system' ); ?></span>
// 								<span v-else-if="course.progress === '100'"><?php esc_html_e( 'Completed', 'masterstudy-lms-learning-management-system' ); ?></span>
// 								<span v-else><?php esc_html_e( 'Continue', 'masterstudy-lms-learning-management-system' ); ?></span>
// 							</a>
// 						</div>
//
// 						<div class="stm_lms_instructor_courses__single--started">
// 							{{ course.start_time }}
// 						</div>
//
// 					</div>
				</div>
            <?php endforeach; ?>
			</div>

		</div>

		<h4 v-if="!courses.length && !loading"><?php esc_html_e( 'No courses.', 'masterstudy-lms-learning-management-system' ); ?></h4>
		<h4 v-if="loading"><?php esc_html_e( 'Loading courses.', 'masterstudy-lms-learning-management-system' ); ?></h4>

	</div>

	<div class="text-center load-my-courses">
		<a @click="getCourses()" v-if="!total" class="btn btn-default" v-bind:class="{'loading' : loading}">
			<span><?php esc_html_e( 'Show more', 'masterstudy-lms-learning-management-system' ); ?></span>
		</a>
	</div>

</div>
