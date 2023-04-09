<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$total_videos      = isset( $user['metadata']['connections']['videos']['total'] ) ? $user['metadata']['connections']['videos']['total'] : 0;
$user_image_link   = isset( $user_image['link'] ) ? $user_image['link'] : '#no-image-link';
$user_image_height = isset( $user_image['height'] ) ? $user_image['height'] : 50;
$user_image_width  = isset( $user_image['width'] ) ? $user_image['width'] : 50;
$user_name         = isset( $user['name'] ) ? $user['name'] : 'Vimeo User';
$user_account      = isset( $user['account'] ) ? $user['account'] : '';
$user_uri          = isset( $user['uri'] ) ? $user['uri'] : '';
$is_update         = isset( $_GET['settings-updated'] ) ? true : false;
?>
<div class="vimeo-settings__profile" data-js="vimeo-settings-analytics"
	data-user-uri="<?php echo esc_attr( $user_uri ); ?>"
	data-is-update="<?php echo esc_attr( $is_update ); ?>">
	<a class="vimeo-settings__profile__image" href="<?php echo esc_url( $user_image_link ); ?>" target="_blank">
		<img src="<?php echo esc_url( $user_image_link ); ?>" height="<?php echo esc_attr( $user_image_height ); ?>" width="<?php echo esc_attr( $user_image_width ); ?>" />
	</a>
	<div>
		<div class="vimeo-settings__title--name"><?php echo esc_html( $user_name ); ?></div>
		<div class="vimeo-settings__profile__account">
			<span class="vimeo-settings__profile__label-premium">
				<?php esc_html_e( $user_account, 'vimeo-for-wordpress' ); ?>
			</span>
			<span class="vimeo-settings__profile__videos">
				<?php
				printf(
					esc_html(
						/* translators: %1$s is replaced with the total number of videos */
						_n( '%1$s video', '%1$s videos', $total_videos, 'vimeo-for-wordpress' )
					),
					esc_html( number_format_i18n( $total_videos ) )
				);
				?>
			</span>
		</div>
		<div>
			<form method="POST" id="vimeo_disconnect" >
				<?php wp_nonce_field( \Tribe\Vimeo_WP\Vimeo\Vimeo_Auth::VIMEO_NONCE ); ?>
				<input type="hidden" name="<?php echo esc_attr( \Tribe\Vimeo_WP\Vimeo\Vimeo_Auth::DISCONNECT_QUERY ); ?>" value="true">
				<a href="#vimeo_disconnect" onclick="this.closest('form').submit();return false;">
					<?php esc_html_e( 'Disconnect Vimeo Account', 'vimeo-for-wordpress' ); ?>
				</a>
			</form>
		</div>
	</div>
</div>
