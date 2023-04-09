<?php
use Tribe\Vimeo_WP\Settings\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<form method="post" action="options.php">
	<?php
		settings_fields( Settings::VIMEO_GROUP );
		do_settings_sections( Settings::VIMEO_GROUP );
		submit_button();
	?>
</form>
