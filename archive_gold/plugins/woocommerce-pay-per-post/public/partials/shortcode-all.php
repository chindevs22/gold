<?php
	/**
	 * Do not edit this file directly.  You can copy this file to your theme directory
	 * in woocommerce-pay-per-post/shortcode-all.php
	 * The $ppp_posts variable is a WP Posts Object of posts that are protected.
	 */

?>
<?php if ( count( $ppp_posts ) > 0 ) : ?>
	<div class="wc-ppp-posts-container">
		<ul>
			<?php foreach ( $ppp_posts as $post ) : $status = 'locked';
				if ( Woocommerce_Pay_Per_Post_Helper::has_access( $post->ID, false ) ) {
					$status = 'unlocked';
				} ?>

				<li class="<?php echo $status; ?>"><a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>"><?php echo esc_html( $post->post_title ); ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
<?php endif; ?>
