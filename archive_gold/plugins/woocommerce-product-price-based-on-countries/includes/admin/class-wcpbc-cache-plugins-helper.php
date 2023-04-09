<?php
/**
 * Clear the cache of all plugins when the "Load product price in the background" option change.
 *
 * @since 2.3.0
 * @package WCPBC
 */

defined( 'ABSPATH' ) || exit;

/**
 * WCPBC_Cache_Plugins_Helper Class
 */
class WCPBC_Cache_Plugins_Helper {

	/**
	 * Init hooks.
	 */
	public static function init() {
		add_action( 'update_option_wc_price_based_country_caching_support', array( __CLASS__, 'flush_cache' ) );
	}

	/**
	 * Flush the caches of the common caching plugins.
	 *
	 * @see https://github.com/TablePress/TablePress/blob/main/models/model-table.php#L603
	 * @since 2.3.0
	 */
	public static function flush_cache() {

		/**
		 * Filters whether the caches of common caching plugins shall be flushed.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $flush Whether caches of caching plugins shall be flushed. Default true.
		 */
		if ( ! apply_filters( 'wc_price_based_country_flush_plugins_caches', true ) ) {
			return false;
		}

		// Common cache flush callback.
		$cache_flush_callbacks = array(
			array( 'Breeze_PurgeCache', 'breeze_cache_flush' ), // Breeze.
			array( 'comet_cache', 'clear' ), // Comet Cache.
			'pantheon_wp_clear_edge_all', // Pantheon.
			'sg_cachepress_purge_cache', // SG Optimizer.
			array( 'Swift_Performance_Cache', 'clear_all_cache' ), // Swift Performance.
			'w3tc_pgcache_flush', // W3 Total Cache.
			array( 'WpeCommon', 'purge_memcached' ), // WP Engine.
			array( 'WpeCommon', 'clear_maxcdn_cache' ), // WP Engine.
			array( 'WpeCommon', 'purge_varnish_cache' ), // WP Engine.
			'wpfc_clear_all_cache', // WP Fastest Cache.
			'rocket_clean_domain', // WP Rocket.
			'wp_cache_clear_cache', // WP Super Cache.
			array( 'zencache', 'clear' ), // Zen Cache.
		);
		foreach ( $cache_flush_callbacks as $cache_flush_callback ) {
			if ( is_callable( $cache_flush_callback ) ) {
				call_user_func( $cache_flush_callback );
			}
		}

		// Common cache flush hooks.
		$cache_flush_hooks = array(
			'ce_clear_cache', // Cache Enabler.
			'cachify_flush_cache', // Cachify.
			'autoptimize_action_cachepurged', // Hyper Cache.
		);
		foreach ( $cache_flush_hooks as $cache_flush_hook ) {
			do_action( $cache_flush_hook ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
		}

		// Kinsta.
		if ( isset( $GLOBALS['kinsta_cache'] ) && ! empty( $GLOBALS['kinsta_cache']->kinsta_cache_purge ) && is_callable( array( $GLOBALS['kinsta_cache']->kinsta_cache_purge, 'purge_complete_caches' ) ) ) {
			$GLOBALS['kinsta_cache']->kinsta_cache_purge->purge_complete_caches();
		}

		// LiteSpeed Cache.
		if ( is_callable( array( 'LiteSpeed_Cache_Tags', 'add_purge_tag' ) ) ) {
			LiteSpeed_Cache_Tags::add_purge_tag( '*' );
		}

		// Pagely.
		if ( class_exists( 'PagelyCachePurge' ) ) {
			$_pagely = new PagelyCachePurge();
			if ( is_callable( array( $_pagely, 'purgeAll' ) ) ) {
				$_pagely->purgeAll();
			}
		}

		// Pressidum.
		if ( is_callable( array( 'Ninukis_Plugin', 'get_instance' ) ) ) {
			$_pressidum = Ninukis_Plugin::get_instance();
			if ( is_callable( array( $_pressidum, 'purgeAllCaches' ) ) ) {
				$_pressidum->purgeAllCaches();
			}
		}

		// Savvii.
		if ( defined( '\Savvii\CacheFlusherPlugin::NAME_DOMAINFLUSH_NOW' ) ) {
			$_savvii = new \Savvii\CacheFlusherPlugin();
			if ( is_callable( array( $_savvii, 'domainflush' ) ) ) {
				$_savvii->domainflush();
			}
		}

		// WP Fastest Cache.
		if ( isset( $GLOBALS['wp_fastest_cache'] ) && is_callable( $GLOBALS['wp_fastest_cache'], 'deleteCache' ) ) {
			$GLOBALS['wp_fastest_cache']->deleteCache();
		}

		// WP-Optimize.
		if ( function_exists( 'WP_Optimize' ) && is_callable( array( WP_Optimize(), 'get_page_cache' ) ) ) {
			$page_cache = WP_Optimize()->get_page_cache();
			if ( is_callable( array( $page_cache, 'purge' ) ) ) {
				WP_Optimize()->get_page_cache()->purge();
			}
		}
	}

	/**
	 * Returns true if there is a static caching in use. False otherwise.
	 */
	public static function has_cache_plugin() {

		if ( in_array( 'advanced-cache.php', array_keys( get_dropins() ), true ) ) {
			return true;
		}

		$cache_headers = array( 'cf-cache-status', 'sg-optimizer-worker-status', 'x-proxy-cache' );
		$response      = wp_remote_get( get_home_url() );

		if ( ! is_wp_error( $response ) && isset( $response['headers'] ) ) {

			foreach ( $response['headers'] as $key => $value ) {
				if ( in_array( $key, $cache_headers, true ) ) {
					return true;
				}
			}
		}

		return false;
	}
}
