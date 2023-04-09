<?php
namespace Tribe\Vimeo_WP\WooCommerce;

class Vimeo_WC {

	const VIDEOS_META_KEY     = '_vimeo_woocommerce_videos';
	const DISPLAY_IN_META_KEY = '_vimeo_woocommerce_display_in';
	const ORDER_META_KEY      = '_vimeo_woocommerce_order';
	const WOO_META_NONCE      = '_vimeo_woo_nonce';

	const DISPLAY_IN_TAB_VALUE = 'video_tab';
	const ORDER_LAST_VALUE     = 'last';

	public function init() {
		add_action( 'add_meta_boxes_product', [ $this, 'add_meta_box' ], 10, 1 );
		add_action( 'save_post_product', [ $this, 'save_meta_box_details' ], 10, 1 );
		add_action( 'vimeo_create_params', [ $this, 'add_woocommerce_create_params' ], 10, 1 );
	}

	/**
	 * Adds the meta box for WooCommerce
	 *
	 * @param string $post_type
	 * @return void
	 */
	public function add_meta_box( $post_type ) {
		add_meta_box(
			'vimeo-plugin-meta-box',
			__( 'Vimeo video gallery', 'vimeo-for-wordpress' ),
			[ $this, 'render_meta_box'],
			'product',
			'side',
			'default'
		);
	}

	public function render_meta_box( $post ) {
		$videos_value     = get_post_meta( $post->ID, self::VIDEOS_META_KEY, true );
		$display_in_value = get_post_meta( $post->ID, self::DISPLAY_IN_META_KEY, true );
		$order_value      = get_post_meta( $post->ID, self::ORDER_META_KEY, true );
		?>
		<div data-js="app-vimeo-woocommerce"></div>
		<input data-js="app-vimeo-woocommerce-input" type="hidden" name="<?php echo esc_attr( self::VIDEOS_META_KEY ); ?>" value="<?php echo esc_attr( $videos_value ); ?>" />
		<?php wp_nonce_field( self::WOO_META_NONCE, self::WOO_META_NONCE ); ?>
		<div data-js="<?php echo esc_attr( self::DISPLAY_IN_META_KEY ); ?>" data-initial-value="<?php echo esc_attr( $display_in_value ); ?>"></div>
		<div data-js="<?php echo esc_attr( self::ORDER_META_KEY ); ?>" data-initial-value="<?php echo esc_attr( $order_value ); ?>"></div>
		<?php
	}

	public function save_meta_box_details( $post_id ) {
		if ( isset( $_REQUEST[ self::WOO_META_NONCE ] ) ) {
			if ( wp_verify_nonce( sanitize_text_field( $_REQUEST[ self::WOO_META_NONCE ] ), self::WOO_META_NONCE ) ) {
				$meta_keys = [
					self::VIDEOS_META_KEY,
					self::DISPLAY_IN_META_KEY,
					self::ORDER_META_KEY
				];
				foreach ( $meta_keys as $meta_key ) {
					if ( array_key_exists( $meta_key, $_POST ) ) {
						update_post_meta(
							$post_id,
							$meta_key,
							sanitize_text_field( wp_unslash( $_POST[ $meta_key ] ) )
						);
					}
				}
			}
		}
	}

	/**
	 * Adds the WooCommerce product Id and Auto Create settings for
	 * the create flow.
	 *
	 * @param array $args
	 * @return array
	 */
	public function add_woocommerce_create_params( $args ) {
		$args['additionalParams']['app'] = 'woocommerce';
		if ( 'product' === get_post_type() ) {
			$args['additionalParams']['productId'] = get_the_ID();
		}
		return $args;
	}
}
