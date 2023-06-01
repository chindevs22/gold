<?php

namespace MasterStudy\Lms\Pro\addons\assignments;

use MasterStudy\Lms\Plugin\Addon;
use MasterStudy\Lms\Plugin\Addons;

final class Assignments implements Addon {

	/**
	 * @return string
	 */
	public function get_name(): string {
		return Addons::ASSIGNMENTS;
	}

	/**
	 *
	 * @param \MasterStudy\Lms\Plugin $plugin
	 */
	public function register( \MasterStudy\Lms\Plugin $plugin ): void {
		$plugin->get_router()->load_routes( __DIR__ . '/routes.php' );
	}
}
