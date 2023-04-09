<?php
namespace Tribe\Vimeo_WP\WooCommerce;

use Tribe\Vimeo_WP\Shortcodes\Vimeo_Embed;

/**
 * Class Vimeo_WC_Video_Tab
 * Adds the Video to the WooCommerce Gallery
 */
class Vimeo_WC_Video_Tab {

	public function __construct() {
		add_filter( 'woocommerce_product_tabs', [ $this, 'tab_filter' ] );
	}

	/**
	 * A filter to add the Video tab to the WooCommerce Gallery
	 *
	 * @param $tabs
	 *
	 * @return array
	 */
	public function tab_filter( $tabs ) {
		$product = wc_get_product( get_the_ID() );
		if ( ! $product instanceof \WC_Product ) {
			return;
		}
		$gallery_videos = json_decode( $product->get_meta( Vimeo_WC::VIDEOS_META_KEY, true ), true);
		if ( $gallery_videos && !empty( $gallery_videos ) ) {
			$is_last =  Vimeo_WC::ORDER_LAST_VALUE === $product->get_meta( Vimeo_WC::ORDER_META_KEY, true );
			$tabs[ 'video_tab' ] = array(
				'title'     => __( 'Videos', 'vimeo-for-wordpress' ),
				'priority'  => $is_last ? 50 : 1,
				'callback'  => [ $this, 'render_tab' ],
			);
		}
		return $tabs;
	}

	/**
	 * Adds the video html to the video tab
	 *
	 * @return void
	 */
	public function render_tab() {
		$product = wc_get_product( get_the_ID() );
		if ( ! $product instanceof \WC_Product ) {
			return;
		}

		printf(
			'<h2>%s</h2>',
			esc_html__( 'Videos', 'vimeo-for-wordpress' )
		);
		$gallery_videos = json_decode( $product->get_meta( Vimeo_WC::VIDEOS_META_KEY, true ), true );
		foreach ( $gallery_videos as $video ) {
			$video['autoplay'] = 0;
			$embed             = new Vimeo_Embed();
			echo wp_kses( $embed->vimeo_embed( $video ), Vimeo_Embed::SHORTCODE_ALLOWED_KSES );
		}
	}
}
