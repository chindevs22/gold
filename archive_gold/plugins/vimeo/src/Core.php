<?php
namespace Tribe\Vimeo_WP;

use Tribe\Vimeo_WP\Assets\Assets;
use Tribe\Vimeo_WP\Assets\Media_Button;
use Tribe\Vimeo_WP\Vimeo\Vimeo_Auth;
use Tribe\Vimeo_WP\Blocks\Blocks;
use Tribe\Vimeo_WP\Settings\Settings;
use Tribe\Vimeo_WP\Settings_Page\Settings_Page;
use Tribe\Vimeo_WP\Shortcodes\Vimeo_Embed;
use Tribe\Vimeo_WP\WooCommerce\Vimeo_WC;
use Tribe\Vimeo_WP\WooCommerce\Vimeo_WC_Gallery;

final class Core {

	const VERSION        = '1.1.2';
	const PLUGIN_NAME    = 'vimeo-for-wordpress';
	const REDIRECT_QUERY = 'vimeo_response';

	private static $instance;

	/**
	 * Makes the hook calls for the plugin.
	 *
	 * @hook plugins_loaded
	 * @return void
	 */
	public function init() {
		$settings_page = new Settings_Page();
		add_action( 'admin_menu', [ $settings_page, 'add_settings_menu' ] );
		add_filter( 'plugin_action_links', [ $settings_page, 'add_links_to_plugin_actions' ], 10, 4 );

		add_action( 'admin_init', [ new Settings(), 'register_settings' ], 15 );
		add_action( 'admin_init', [ new Vimeo_Auth(), 'disconnect_vimeo_account' ], 1 );

		add_action( 'init', [ new Blocks(), 'register_blocks' ] );

		add_action( 'init', [ new Assets(), 'enqueue_scripts' ] );
		
		if ( get_option( Settings::VIMEO_SHORTCODE, '1' ) === '1' ) {
			add_action( 'media_buttons', [ new Media_Button(), 'add_vimeo_button' ] );
			add_shortcode( 'vimeo_embed', [ new Vimeo_Embed(), 'vimeo_embed'] );
		}

		if ( class_exists( 'WooCommerce' ) && get_option( Settings::VIMEO_WOOCOMMERCE, '1' ) === '1' ) {
			add_action( 'admin_init', [ new Vimeo_WC(), 'init' ] );
			add_action( 'wp', [ new Vimeo_WC_Gallery(), 'init' ] );
		}
	}

	/**
	 * Any code you want to run when deactivating the plugin.
	 *
	 * @return void
	 */
	public static function activate() {
		return;
	}

	/**
	 * Any code that you want to run when deactivating the plugin.
	 *
	 * @return void
	 */
	public static function deactivate() {
		delete_option( Settings::USER_TOKEN );
		delete_option( Settings::ACCESS_TOKEN );
		delete_option( Settings::USER_TRACKING );
		delete_option( Settings::VIMEO_GUTENBERG_BLOCK );
		delete_option( Settings::VIMEO_WOOCOMMERCE );
		delete_option( Settings::VIMEO_SHORTCODE );
	}

	/**
	 * Singleton constructor.
	 *
	 * @return self
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		define( 'VIMEO_PATH', trailingslashit( plugin_dir_path( dirname( __FILE__ ) ) ) );
		define( 'VIMEO_URL', plugin_dir_url( dirname( __FILE__ ) ) );
		define( 'VIMEO_VERSION', self::VERSION );
	}

	private function __clone() {
	}

}
