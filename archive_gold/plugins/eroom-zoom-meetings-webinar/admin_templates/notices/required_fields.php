<?php
function stm_eroom_general_admin_notice() {
	$settings = get_option( 'stm_zoom_settings', array() );
	if ( get_admin_page_parent() == 'stm_zoom' ) {
		if ( empty( $settings['sdk_key'] ) || empty( $settings['sdk_secret'] ) ) {
			echo '<div class="notice notice-warning is-dismissible eroom-notice">';
			echo '<p>';
			echo sprintf( '%1s <strong>%2s</strong> %3s <strong>%4s</strong> %5s', esc_html__( 'Please add', 'eroom-zoom-meetings-webinar' ), esc_html__( 'Meeting SDK', 'eroom-zoom-meetings-webinar' ), esc_html__( 'to integrate Zoom Client functionalities and make', 'eroom-zoom-meetings-webinar' ), esc_html__( 'Join In Browser', 'eroom-zoom-meetings-webinar' ), esc_html__( 'work', 'eroom-zoom-meetings-webinar' ) );
			echo '</p>';
			echo '</div>';
		}
		if ( empty( $settings['auth_account_id'] ) && empty( $settings['auth_client_id'] ) && empty( $settings['auth_client_secret'] ) ) {
			echo '<div class="notice notice-error is-dismissible">';
			echo '<p>';
			echo sprintf( '%1s <a href="https://marketplace.zoom.us/docs/guides/build/jwt-app/jwt-faq/#jwt-app-type-deprecation-faq--omit-in-toc-" target="_blank">%2s</a> %3s <a href="#" class="eroom-migration-wizard" >%4s</a> %5s', esc_html__( 'Zoom is deprecating their JWT app from June of 2023 and until the deadline all your current APIs will work. Please see', 'eroom-zoom-meetings-webinar' ), esc_html__( 'JWT App Type Depreciation FAQ', 'eroom-zoom-meetings-webinar' ), esc_html__( 'for more details, It is recommended to run the', 'eroom-zoom-meetings-webinar' ), esc_html__( 'migration wizard', 'eroom-zoom-meetings-webinar' ), esc_html__( 'in easy steps to smooth the transition to the new Server to Server OAuth system.', 'eroom-zoom-meetings-webinar' ) );
			echo '</p>';
			echo '</div>';
		} else if ( empty( $settings['auth_account_id'] ) || empty( $settings['auth_client_id'] ) || empty( $settings['auth_client_secret'] ) ) {
			echo '<div class="update-message notice inline notice-alt notice-error">
			<p>' . esc_html__( 'Please complete all OAuth fields', 'eroom-zoom-meetings-webinar' ) . '</p>
		</div>';
		}
	}

	if ( get_admin_page_parent() == 'stm_zoom_pro' ) {
		if ( empty( $settings['auth_account_id'] ) && empty( $settings['auth_client_id'] ) && empty( $settings['auth_client_secret'] ) ) {
			echo '<div class="notice notice-error is-dismissible">';
			echo '<p>';
			echo sprintf( '%1s <a href="https://marketplace.zoom.us/docs/guides/build/jwt-app/jwt-faq/#jwt-app-type-deprecation-faq--omit-in-toc-" target="_blank">%2s</a> %3s <a href="#" class="eroom-migration-wizard" >%4s</a> %5s', esc_html__( 'Zoom is deprecating their JWT app from June of 2023 and until the deadline all your current APIs will work. Please see', 'eroom-zoom-meetings-webinar' ), esc_html__( 'JWT App Type Depreciation FAQ', 'eroom-zoom-meetings-webinar' ), esc_html__( 'for more details, It is recommended to run the', 'eroom-zoom-meetings-webinar' ), esc_html__( 'migration wizard', 'eroom-zoom-meetings-webinar' ), esc_html__( 'in easy steps to smooth the transition to the new Server to Server OAuth system.', 'eroom-zoom-meetings-webinar' ) );
			echo '</p>';
			echo '</div>';
		}
	}
}

add_action( 'admin_notices', 'stm_eroom_general_admin_notice', 100 );
$settings = get_option( 'stm_zoom_settings', array() );

if ( ! empty( $settings ) && ( empty( $settings['auth_account_id'] ) && empty( $settings['auth_client_id'] ) && empty( $settings['auth_client_secret'] ) ) ) {
	Migration::get_instance();
}
