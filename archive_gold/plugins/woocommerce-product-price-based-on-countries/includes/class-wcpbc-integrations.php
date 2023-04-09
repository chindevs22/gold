<?php
/**
 * Integrations
 *
 * Handle integrations between PBC and 3rd-Party plugins
 *
 * @version 1.6.14
 * @package WCPBC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCPBC_Integrations
 */
class WCPBC_Integrations {

	/**
	 * Add 3rd-Party plugins integrations
	 */
	public static function add_third_party_plugin_integrations() {

		$third_party_integrations = array(
			'woogle_get_container'         => dirname( __FILE__ ) . '/integrations/class-wcpbc-google-listing-and-ads.php',
			'wcpay_init'                   => dirname( __FILE__ ) . '/integrations/class-wcpbc-wc-payments.php',
			'Sitepress'                    => dirname( __FILE__ ) . '/integrations/class-wcpbc-admin-translation-management.php',
			'AngellEYE_Gateway_Paypal'     => dirname( __FILE__ ) . '/integrations/class-wcpbc-paypal-express-angelleye.php',
			'WC_Gateway_Twocheckout'       => dirname( __FILE__ ) . '/integrations/class-wcpbc-gateway-2checkout.php',
			'WC_Product_Addons'            => dirname( __FILE__ ) . '/integrations/class-wcpbc-product-addons-basic.php',
			'WC_Dynamic_Pricing'           => dirname( __FILE__ ) . '/integrations/class-wcpbc-dynamic-pricing-basic.php',
			'WoocommerceGpfCommon'         => dirname( __FILE__ ) . '/integrations/class-wcpbc-gpf.php',
			'WCS_ATT_Abstract_Module'      => dirname( __FILE__ ) . '/integrations/class-wcpbc-wcs-att.php',
			'WC_Gateway_PPEC_Plugin'       => dirname( __FILE__ ) . '/integrations/class-wcpbc-gateway-paypal-express-checkout.php',
			'RP_WCDPD'                     => dirname( __FILE__ ) . '/integrations/class-wcpbc-rightpress-product-price-shop.php',
			'woocommerce_payu_add_gateway' => dirname( __FILE__ ) . '/integrations/class-wcpbc-payu-payment-gateway.php',
			'\Elementor\Plugin'            => dirname( __FILE__ ) . '/integrations/class-wcpbc-elementor.php',
			'WC_Shipping_UPS_Init'         => dirname( __FILE__ ) . '/integrations/class-wcpbc-shipping-ups-fedex.php',
			'WC_Shipping_Fedex_Init'       => dirname( __FILE__ ) . '/integrations/class-wcpbc-shipping-ups-fedex.php',
			'Aelia\WC\EU_VAT_Assistant\WC_Aelia_EU_VAT_Assistant' => dirname( __FILE__ ) . '/integrations/class-wcpbc-eu-vat-assistant.php',
		);

		foreach ( $third_party_integrations as $class => $integration_file ) {

			if ( class_exists( $class ) || function_exists( $class ) ) {
				include_once $integration_file;
			}
		}

		/**
		 * Woo Discount Rules by Flycart.
		 *
		 * @since 2.0.11
		 * @version 2.2.1 Moved to "wp" hook
		 * @see https://wordpress.org/plugins/woo-discount-rules/
		 */
		if ( defined( 'WDR_SLUG' ) && 'woo_discount_rules' === WDR_SLUG && WCPBC_Ajax_Geolocation::is_enabled() ) {
			add_action( 'wp', array( __CLASS__, 'woo_discount_rules_fixes' ) );
		}
	}

	/**
	 * Is a plugin is active?
	 *
	 * @param string $plugin Plugin to check.
	 * @return bool
	 */
	private static function is_plugin_active( $plugin ) {
		return in_array( $plugin, (array) get_option( 'active_plugins', array() ), true ) || self::is_plugin_active_for_network( $plugin );
	}

	/**
	 * Is a plugin active for network?
	 *
	 * @param string $plugin Plugin to check.
	 * @return bool
	 */
	private static function is_plugin_active_for_network( $plugin ) {
		if ( ! is_multisite() ) {
			return false;
		}

		$plugins = get_site_option( 'active_sitewide_plugins' );
		if ( isset( $plugins[ $plugin ] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Include the PayPal Payments plugin integration.
	 */
	public static function add_paypal_payments_integraton() {
		if ( self::is_plugin_active( 'woocommerce-paypal-payments/woocommerce-paypal-payments.php' ) ) {
			include dirname( __FILE__ ) . '/integrations/class-wcpbc-paypal-payments.php';
		}
	}

	/**
	 * Woo Discount Rules by Flycart is a very aggresive plugin. It removes the price filter of the others hooks. We need to add the filters again.
	 */
	public static function woo_discount_rules_fixes() {
		// For AJAX Geolocation increment the filter priority.
		remove_filter( 'woocommerce_get_price_html', array( 'WCPBC_Ajax_Geolocation', 'price_html_wrapper' ), 0, 2 );
		add_filter( 'woocommerce_get_price_html', array( 'WCPBC_Ajax_Geolocation', 'price_html_wrapper' ), 9999, 2 );
	}

}
add_action( 'plugins_loaded', array( 'WCPBC_Integrations', 'add_third_party_plugin_integrations' ) );
add_action( 'plugins_loaded', array( 'WCPBC_Integrations', 'add_paypal_payments_integraton' ), 0 );
