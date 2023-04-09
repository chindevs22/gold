<?php
namespace Tribe\Vimeo_WP\Blocks;

use Tribe\Vimeo_WP\Core;

class Blocks {

	/**
	 * Registers the block.
	 *
	 * @hook init
	 * @return void
	 */
	public function register_blocks() {
		register_block_type(
			Core::PLUGIN_NAME . '/block',
			[
				'style'          => Core::PLUGIN_NAME . '-public',
				'editor_style'   => Core::PLUGIN_NAME . '-editor',
				'editor_scripts' => Core::PLUGIN_NAME . '-scripts',
			]
		);
	}

}
