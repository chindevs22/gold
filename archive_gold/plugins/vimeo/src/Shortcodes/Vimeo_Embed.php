<?php
namespace Tribe\Vimeo_WP\Shortcodes;

use Tribe\Vimeo_WP\Settings\Embed;

class Vimeo_Embed {
	private $embed;

	const SHORTCODE_ALLOWED_KSES = [ 'div' => [ 'style' => [], 'id' => [], 'data-js' => [], 'data-atts' => [] ], 'script' => [] ];

	public function __construct() {
		$this->embed = new Embed();
	}

	/**
	 * Returns the video based on the Vimeo Embed SDK for the shortcode.
	 *
	 * @param array $params
	 * @return string
	 */
	public function vimeo_embed( $params = [] ) {
		$atts = shortcode_atts( $this->embed->get_defaults(), $params, 'vimeo_embed' );
		$id   = 'vimeo-wc-video-' . uniqid();

		return sprintf(
			'<div style="width: 100%%; min-height: 1px;" id="%s" data-js="vimeo-wc-video" data-atts="%s"></div>',
			esc_attr( $id ),
			esc_attr( wp_json_encode( $atts ) )
		);
	}
}
