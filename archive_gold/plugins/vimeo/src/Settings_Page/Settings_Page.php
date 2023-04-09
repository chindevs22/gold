<?php
namespace Tribe\Vimeo_WP\Settings_Page;

use Tribe\Vimeo_WP\Settings\Settings;

class Settings_Page {

	/**
	 * Adds settings link to the plugin line itom on the all plugins page.
	 *
	 * @hook plugin_action_links
	 * @param array $actions
	 * @param string $plugin_file
	 * @return array
	 */
	public function add_links_to_plugin_actions( $actions, $plugin_file ) {
		static $plugin;

		if ( ! isset( $plugin ) ) {
			$plugin = plugin_basename( VIMEO_PATH ) . '/Core.php';
		}

		if ( $plugin === $plugin_file ) {
			$link = sprintf(
				'<a href="%s">%s</a>',
				esc_url( admin_url( 'options-general.php?page=' . Settings::VIMEO_GROUP ) ),
				esc_html__( 'Settings', 'vimeo-for-wordpress' )
			);
			$settings = [
				'settings' => $link
			];
			$actions = array_merge( $settings, $actions );
		}

		return $actions;
	}


	/**
	 * Adds the Vimeo Settings menu item.
	 *
	 * @hook admin_menu
	 * @return void
	 */
	public function add_settings_menu() {
		add_options_page(
			__( 'Vimeo Settings', 'vimeo-for-wordpress' ),
			__( 'Vimeo Settings', 'vimeo-for-wordpress' ),
			'manage_options',
			Settings::VIMEO_GROUP,
			[ $this, 'create_admin_page' ]
		);
	}

	/**
	 * Outputs the markup for the Vimeo Settings page.
	 *
	 * @return void
	 */
	public function create_admin_page() {
		require VIMEO_PATH . 'Views/Settings.php';
	}
}
