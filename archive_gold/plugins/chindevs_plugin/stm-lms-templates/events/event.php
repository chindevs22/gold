<?php
	error_log("in event.php");
	$user_events = new User_Events();
	$params = array(
		'limit' => 10,
		'user'  => get_current_user_id()
	);
	$event_ids = $user_events->generate( $params );
?>
<div class="stm_lms_user_info_top">
	<h3><?php esc_html_e( 'Enrolled Events', 'masterstudy-lms-learning-management-system' ); ?></h3>
</div>

<div id="enrolled-events">
	<div class="stm-lms-user-events">

		<div class="multiseparator"></div>

		<div class="stm_lms_user_events__grid">
			<?php foreach ( $event_ids as $id ) : ?>
			<div class="stm_lms_user_events__single" >
				
				<div class="stm_lms_user_events__single__inner">
					<div class="stm_lms_user_events__single--image">

<!-- 						<div class="stm_lms_post_status heading_font"
							 v-if="course.post_status"
							 v-bind:class="course.post_status.status">
							{{ course.post_status.label }}
						</div> -->

						<div class="image_wrapper">
							<?php echo do_shortcode("[tribe_event_inline id=$id]
																				 {url}{thumbnail}{/url}
																				 [/tribe_event_inline]") ?>
						</div>

					</div>

					<div class="stm_lms_user_events__single--inner">

<!-- 						<div class="stm_lms_user_events__single--terms" v-if="course.terms">
							<div class="stm_lms_user_events__single--term"
								 v-for="(term, key) in course.terms"
								 v-html="term" v-if="key === 0">
							</div>
						</div> -->

						<div class="stm_lms_user_events__single--title">
							<?php echo do_shortcode("[tribe_event_inline id=$id]
																{title:linked}
																[/tribe_event_inline]") ?>
						</div>

<!-- 						<div class="stm_lms_user_events__single--progress">
							<div class="stm_lms_user_events__single--progress_top">
								<div class="stm_lms_user_events__single--duration" v-if="course.duration">
									<i class="far fa-clock"></i>
									{{ course.duration }}
								</div>
								<div class="stm_lms_user_events__single--completed">
									{{ course.progress_label }}
								</div>
							</div>

							<div class="stm_lms_user_events__single--progress_bar">
								<div class="stm_lms_user_events__single--progress_filled"
									 v-bind:style="{'width' : course.progress + '%'}"></div>
							</div>

						</div> -->

<!-- 						<div class="stm_lms_user_events__single--enroll">
							<a v-if="course.expiration.length && course.is_expired || course.membership_expired || course.membership_inactive" class="btn btn-default"
							   :href="course.url" target="_blank">
								<span><?php esc_html_e( 'Preview Course', 'masterstudy-lms-learning-management-system' ); ?></span>
							</a>
							<a v-bind:href="course.current_lesson_id" class="btn btn-default"
							   v-bind:class="{'continue' : course.progress !== '0'}"
							   v-else>
								<span v-if="course.progress === '0'"><?php esc_html_e( 'Start Course', 'masterstudy-lms-learning-management-system' ); ?></span>
								<span v-else-if="course.progress === '100'"><?php esc_html_e( 'Completed', 'masterstudy-lms-learning-management-system' ); ?></span>
								<span v-else><?php esc_html_e( 'Continue', 'masterstudy-lms-learning-management-system' ); ?></span>
							</a>
						</div> -->

<!-- 						<div class="stm_lms_user_events__single--started">
							{{ course.start_time }}
						</div> -->

					</div>
				</div>
				
			</div>
<?php endforeach; ?>
		</div>

<!-- 		<h4 v-if="!courses.length && !loading"><?php esc_html_e( 'No courses.', 'masterstudy-lms-learning-management-system' ); ?></h4> -->

	</div>

</div>
