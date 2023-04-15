<?php
wp_enqueue_script( 'vue.js' );
wp_enqueue_script( 'vue-resource.js' );
// stm_lms_register_script( 'account/v1/enrolled-courses' );
// stm_lms_register_style( 'user-courses' );
// stm_lms_register_style( 'events' );
// stm_lms_register_style( 'expiration/main' );
?>

<div class="stm_lms_user_info_top">

	<h3><?php esc_html_e( 'Enrolled Events', 'masterstudy-lms-learning-management-system' ); ?></h3>

	<div class="stm_lms_user_info_top__sort">

		<select class="no-search">
			<option value="date_low"><?php esc_html_e( 'Enrolled date (last one)', 'masterstudy-lms-learning-management-system' ); ?></option>
			<option value="date_high"><?php esc_html_e( 'Enrolled date (first one)', 'masterstudy-lms-learning-management-system' ); ?></option>
			<option value="progress_low"><?php esc_html_e( 'Progress (increasing)', 'masterstudy-lms-learning-management-system' ); ?></option>
			<option value="progress_high"><?php esc_html_e( 'Progress (decreasing)', 'masterstudy-lms-learning-management-system' ); ?></option>
		</select>

	</div>

</div>

<?php echo do_shortcode('[tribe-user-event-confirmations]') ?>

// <div id="enrolled-events">
// 	<div class="stm-lms-user-events">
//
// 		<div class="multiseparator"></div>
//
// 		<div class="stm_lms_events__grid">
// 			<div class="stm_lms_events__single" v-for="event in events"
// 				 v-bind:class="{'expired' : event.expiration.length && event.is_expired || event.membership_expired || event.membership_inactive}">
// 				<div class="stm_lms_events__single__inner">
// 					<div class="stm_lms_events__single--image">
//
// 						<div class="stm_lms_post_status heading_font"
// 							 v-if="event.post_status"
// 							 v-bind:class="event.post_status.status">
// 							{{ event.post_status.label }}
// 						</div>
//
// 						<div v-html="event.image" class="image_wrapper"></div>
//
// // 						<?php STM_LMS_Templates::show_lms_template( 'account/private/parts/expiration' ); ?>
//
// 					</div>
//
// 					<div class="stm_lms_events__single--inner">
//
// 						<div class="stm_lms_events__single--terms" v-if="event.terms">
// 							<div class="stm_lms_events__single--term"
// 								 v-for="(term, key) in event.terms"
// 								 v-html="term" v-if="key === 0">
// 							</div>
// 						</div>
//
// 						<div class="stm_lms_events__single--title">
// 							<a v-bind:href="event.link">
// 								<h5 v-html="event.title"></h5>
// 							</a>
// 						</div>
//
// 						<div class="stm_lms_events__single--progress">
// 							<div class="stm_lms_events__single--progress_top">
// 								<div class="stm_lms_events__single--duration" v-if="event.duration">
// 									<i class="far fa-clock"></i>
// 									{{ event.duration }}
// 								</div>
// 								<div class="stm_lms_events__single--completed">
// 									{{ event.progress_label }}
// 								</div>
// 							</div>
//
// 						</div>
//
// 						<div class="stm_lms_events__single--started">
// 							{{ event.start_time }}
// 						</div>
//
// 					</div>
// 				</div>
//
// 			</div>
//
// 		</div>
//
// 		<h4 v-if="!events.length && !loading"><?php esc_html_e( 'No events.', 'masterstudy-lms-learning-management-system' ); ?></h4>
// 		<h4 v-if="loading"><?php esc_html_e( 'Loading events.', 'masterstudy-lms-learning-management-system' ); ?></h4>
//
// 	</div>
//
// 	<div class="text-center load-my-events">
// 		<a @click="getEvents()" v-if="!total" class="btn btn-default" v-bind:class="{'loading' : loading}">
// 			<span><?php esc_html_e( 'Show more', 'masterstudy-lms-learning-management-system' ); ?></span>
// 		</a>
// 	</div>

</div>
