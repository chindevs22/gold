<?php

namespace Tribe\Vimeo_WP\WooCommerce;

use Tribe\Vimeo_WP\Shortcodes\Vimeo_Embed;

/**
 * Class Vimeo_WC_Gallery
 * Adds the Video to the WooCommerce Gallery
 */
class Vimeo_WC_Gallery {

	const VIMEO_ID = 'videoId';

	public function init() {
		if ( is_product() ) {
			$product      = wc_get_product( get_the_id() );
			$is_video_tab = Vimeo_WC::DISPLAY_IN_TAB_VALUE == $product->get_meta( Vimeo_WC::DISPLAY_IN_META_KEY, true );
			if ($is_video_tab) {
				new Vimeo_WC_Video_Tab();
			} else {
				// is video gallery
				if ( ! has_post_thumbnail( $product->get_id() ) ) {
					return null;
				}
				$is_last  = Vimeo_WC::ORDER_LAST_VALUE === $product->get_meta( Vimeo_WC::ORDER_META_KEY, true );
				$priority = $is_last ? 21 : 10;
				add_action( 'woocommerce_product_thumbnails', [$this, 'add_video_html_to_gallery'], $priority, 0 );
			}
		}
	}

	/**
	 * Adds the video html to the gallery
	 *
	 * @return void
	 */
	public function add_video_html_to_gallery() {
		$product        = wc_get_product( get_the_id() );
		$gallery_videos = json_decode( $product->get_meta( Vimeo_WC::VIDEOS_META_KEY, true ), true );
		if ( empty( $gallery_videos ) ) {
			return;
		}
		foreach ( $gallery_videos as $video ) {
			$video['autoplay'] = 0;
			$large_thumbnail   = $video['thumbnail'];
			$title             = $video['name'];
			$embed             = new Vimeo_Embed();

			$identifierToReplace = $large_thumbnail . '#vimeo-wc';
			echo ( '<div
					data-thumb="' . esc_url( $large_thumbnail ) . '"
					data-thumb-alt="' . esc_attr( $title ) . '"
					class="woocommerce-product-gallery__image vimeo-wc__gallery-video"
					data-js="' . esc_url( $identifierToReplace ) . '"
					>
						<img
							style="display: none;"
							class="wp-post-image"
							width="100%"
							alt="' . esc_attr( $title ) . '"
							data-large_image_width="0"
							data-large_image_height="0"
							src="' . esc_url( $large_thumbnail ) . '"
							data-src="' . esc_url( $identifierToReplace ) . '"
							data-large_image="' . esc_url( $identifierToReplace ) . '"
						/>
						' . wp_kses( $embed->vimeo_embed( $video ), Vimeo_Embed::SHORTCODE_ALLOWED_KSES ) . '
				</div>' );
		}
	}
}
