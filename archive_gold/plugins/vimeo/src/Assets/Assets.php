<?php
namespace Tribe\Vimeo_WP\Assets;

use Tribe\Vimeo_WP\Core;
use Tribe\Vimeo_WP\Settings\Settings;
use Tribe\Vimeo_WP\Vimeo\Vimeo_Auth;

class Assets {

	const SETTINGS_PAGE = 'settings_page_';

	/**
	 * Enques the scripts
	 *
	 * @hook init
	 * @return void
	 */
	public function enqueue_scripts() {
		add_action( 'enqueue_block_editor_assets', [ $this, 'register_block_editor_assets' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'register_admin_scripts' ], 99, 1 );
		add_action( 'wp_enqueue_scripts', [ $this, 'register_public_scripts' ], 9 );
		add_action( 'wp_enqueue_media', [ $this, 'include_media_button_js' ] );
	}

	/**
	 * Registers the block editor assets
	 *
	 * @return void
	 */
	public function register_block_editor_assets() {
		$deps = $this->get_asset_php( VIMEO_PATH . 'build/editor.asset.php' );
		$deps['dependencies'][] = Core::PLUGIN_NAME . '-analytics';
		wp_enqueue_script( Core::PLUGIN_NAME . '-admin', VIMEO_URL . 'build/editor.js', $deps['dependencies'], $deps['version'], true );
	}

	/**
	 * Registers the admin scripts
	 *
	 * @param mixed $hook Hook suffix for the current admin page.
	 *
	 * @return void
	 */
	public function register_admin_scripts( $hook ) {
		wp_register_style( Core::PLUGIN_NAME . '-admin', VIMEO_URL . 'build/editor.css', [], VIMEO_VERSION );

		if ( self::SETTINGS_PAGE . Settings::VIMEO_GROUP === $hook ) {
			wp_enqueue_style( Core::PLUGIN_NAME . '-admin' );
			$this->include_settings_js();
		}

		$args = [
			'token'            => get_option( Settings::ACCESS_TOKEN, '' ),
			'url'              => VIMEO_URL,
			'appId'            => Vimeo_Auth::APP_ID,
			'currentUrl'       => esc_url( get_home_url() ),
			'upgradeUrl'       => esc_url( 'https://vimeo.com/upgrade' ),
			'settingsUrl'      => esc_url( get_admin_url() . 'options-general.php?page=vimeo_settings' ),
			'autoCreate'       => false,
			'vertical'         => 'website',
			'additionalParams' => [
				'app'    => 'wordpress',
				'apiUrl' => esc_url( get_rest_url() ),
			],
			'userTracking'      => get_option( Settings::USER_TRACKING ) === '1',
			'enableBlocks'      => get_option( Settings::VIMEO_GUTENBERG_BLOCK, '1' ) === '1',
			'enableShortcode'   => get_option( Settings::VIMEO_SHORTCODE, '1' ) === '1',
			'enableWooCommerce' => get_option( Settings::VIMEO_WOOCOMMERCE, '1' ) === '1'
		];

		$params = wp_json_encode( apply_filters( 'vimeo_create_params', $args ) );
		wp_add_inline_script( 'utils', 'window.vimeoScript = ' . $params, 'before' );

		// Adds the ability to use the vimeo create via iFrame.
		wp_enqueue_script( Core::PLUGIN_NAME . '-create', 'https://f.vimeocdn.com/vimeo-widgets/create/0.3/widget.js', [], [], true );

		// Adds vimeo analytics support
		// TODO: fix CORS errror and use full url https://f.vimeocdn.com/js/tracking/bigpicture-client-1.1.js
		wp_enqueue_script( Core::PLUGIN_NAME . '-analytics', VIMEO_URL . 'bigpicture-client-1.1.js', [], [], true );

	}

	/**
	 * Registers the public scripts
	 *
	 * @return void
	 */
	public function register_public_scripts() {
		$deps = $this->get_asset_php( VIMEO_PATH . 'build/index.asset.php' );

		/** Added jquery here manually to ensure 'window.jquery' is available to /build/index.js */
		$deps['dependencies'][] = 'jquery';

		wp_enqueue_script( Core::PLUGIN_NAME . '-public', VIMEO_URL . 'build/index.js', $deps['dependencies'], $deps['version'], true );
		wp_enqueue_style( Core::PLUGIN_NAME . '-public', VIMEO_URL . 'build/style-index.css', [], VIMEO_VERSION );
		wp_enqueue_script( Core::PLUGIN_NAME . '-player', 'https://player.vimeo.com/api/player.js', [], VIMEO_VERSION, false );
	}

	/**
	 * Adds js for media button on classic editor.
	 *
	 * @return void
	 */
	public function include_media_button_js() {
		$deps = $this->get_asset_php( VIMEO_PATH . 'build/media.asset.php' );
		$deps['dependencies'][] = Core::PLUGIN_NAME . '-analytics';
		wp_enqueue_script( 'media_button', VIMEO_URL . 'build/media.js', $deps['dependencies'], $deps['version'], true );
	}

	/**
	 * Adds js for settings page.
	 *
	 * @return void
	 */
	public function include_settings_js() {
		$dependencies = [Core::PLUGIN_NAME . '-analytics'];
		wp_enqueue_script( Core::PLUGIN_NAME . '-settings', VIMEO_URL . 'build/settings.js', $dependencies, VIMEO_VERSION, true );
	}

	/**
	 * Grabs the file created from webpack build and outputs it for version and
	 * dependencies.
	 *
	 * @see https://developer.wordpress.org/block-editor/how-to-guides/javascript/js-build-setup/#dependency-management
	 *
	 * @param string $path
	 * @return array
	 */
	private function get_asset_php( $path = '' ) {
		$script_dependencies = [
			'dependencies' => null,
			'version'      => null,
		];
		if ( file_exists( $path ) ) {
			$script_dependencies = require $path;
		}

		return (array) $script_dependencies;
	}
}
