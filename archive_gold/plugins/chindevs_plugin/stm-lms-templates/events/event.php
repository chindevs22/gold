<?php
	error_log("in event.php");
	$user_events = new User_Events();
	$params = array(
		'limit' => 30,
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

						<div class="image-wrapper">
							<?php
								echo do_shortcode("[tribe_event_inline id=$id]
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
							<h5>
								<?php echo do_shortcode("[tribe_event_inline id=$id]
																{title:linked}
																[/tribe_event_inline]") ?>
							</h5>
						</div>

						<div class="stm_lms_user_events__single--progress">
							<div class="stm_lms_user_events__single--progress_top">
								<div class="stm_lms_user_events__single--started">
									<i class="far fa-clock"></i>
									<?php echo "<b>Start Date:</b> " . do_shortcode("[tribe_event_inline id=$id]
																{start_date}
																[/tribe_event_inline]") ?>
								</div>
							</div>
						</div>

					</div>
				</div>

			</div>
			<?php endforeach; ?>
		</div>

		<?php
				if (count($event_ids) === 0) {
					echo "<h4>You have not registered for any events yet! Head to our Events tab to checkout the latest events available!</h4>";
				}
		?>

	</div>


<!-- 	<div class="text-center load-my-events">
		<button id="update-events-button">Load More Events</button>
	</div> -->
</div>
