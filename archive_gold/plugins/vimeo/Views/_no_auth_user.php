<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<p class="vimeo-settings__step"> <?php esc_html_e( 'Step 1', 'vimeo-for-wordpress' ); ?> </p>
<h2 class="vimeo-settings__title"> <?php esc_html_e( 'Vimeo for WooCommerce and Wordpress Settings', 'vimeo-for-wordpress' ); ?> </h2>
<p class="vimeo-settings__paragraph vimeo-settings__text-box">
	<?php esc_html_e( 'Head over to Vimeo to get your access token. You’ll need this to connect your account to WordPress. Don’t have a Vimeo account?', 'vimeo-for-wordpress' ); ?>
	<a class="vimeo-settings__link" target="_blank" href="https://vimeo.com/join?vcid=40629">
		<span class="link-text"> <?php esc_html_e( 'Sign up', 'vimeo-for-wordpress' ); ?> </span>
	</a>
</p>
<?php
if ( isset( $activation_error ) ) {
	printf(
		'<p class="vimeo-settings__paragraph vimeo-settings__paragraph--error">%s</p>',
		esc_html_e( $activation_error, 'vimeo-for-wordpress' )
	);
}
?>
<p>
	<a class="vimeo-settings__link" href="<?php echo esc_url( $request->get_connect_url() ); ?>" target="_blank" >
		<span class="link-text"> <?php esc_html_e( 'Get an access token', 'vimeo-for-wordpress' ); ?> </span>
		<span class="dashicons dashicons-external"></span>
	</a>
</p>

<div class="vimeo-settings__separator"></div>

<p class="vimeo-settings__step"> <?php esc_html_e( 'Step 2', 'vimeo-for-wordpress' ); ?> </p>
<h2 class="vimeo-settings__title"> <?php esc_html_e( 'Vimeo App settings', 'vimeo-for-wordpress' ); ?> </h2>
<p class="vimeo-settings__paragraph vimeo-settings__text-box">
	<?php esc_html_e( 'Using the access token you recieved on Vimeo.com, copy and paste it below to connect your account.', 'vimeo-for-wordpress' ); ?>
</p>
<?php include( '_settings_form.php' ); ?>
