<?php
/**
 * Plugin Name:       Vimeo
 * Plugin URI:        https://vimeo.com/create?vcid=40636
 * Description:       Bring the power of video to your WordPress site and WooCommerce product pages by easily creating, uploading, and embedding videos to boost engagement and drive conversion.
 * Version:           1.2.1
 * Requires at least: 5.4
 * Requires PHP:      5.6
 * Author:            Vimeo
 * Author URI:        https://vimeo.com/?vcid=40637
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       vimeo-for-wordpress
 * Domain Path:       /languages
 */

use Tribe\Vimeo_WP\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once( 'vendor/autoload.php' );

register_activation_hook( __FILE__, [ Core::class, 'activate' ] );
register_deactivation_hook( __FILE__, [ Core::class, 'deactivate' ] );

add_action( 'plugins_loaded', static function () {
	vimeo_core()->init( __FILE__ );
}, 1, 0 );

function vimeo_core() {
	return Core::instance();
}
