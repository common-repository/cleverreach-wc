<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Util;

/**
 * A utility class that utilises data related to user's shop.
 *
 * Class Shop_Helper
 *
 * @package CleverReach\WooCommerce\Util
 */
class Shop_Helper {

	/**
	 * Database date format
	 */
	const DATE_FORMAT = 'Y-m-d H:i:s';

	/**
	 * Shop URL.
	 *
	 * @var string
	 */
	private static $shop_url;

	/**
	 * Returns whether CleverReach plugin is enabled.
	 *
	 * @return bool
	 */
	public static function is_plugin_enabled() {
		if ( self::is_plugin_active_for_network() ) {
			return true;
		}

		return self::is_plugin_active_for_current_site();
	}

	/**
	 * Returns if CleverReach plugin is active through network
	 *
	 * @return bool
	 */
	public static function is_plugin_active_for_network() {
		if ( ! is_multisite() ) {
			return false;
		}

		$plugins = get_site_option( 'active_sitewide_plugins' );

		return isset( $plugins[ self::get_plugin_name() ] );
	}

	/**
	 * Returns if CleverReach plugin is active for current site
	 *
	 * @return bool
	 */
	public static function is_plugin_active_for_current_site() {
		return in_array(
			self::get_plugin_name(),
			apply_filters( 'active_plugins', get_option( 'active_plugins' ) ),
			true
		);
	}

	/**
	 * Returns the name of the plugin
	 *
	 * @return string
	 */
	public static function get_plugin_name() {
		return plugin_basename( dirname( dirname( __DIR__ ) ) . '/cleverreach-wc.php' );
	}

	/**
	 * Checks if WooCommerce is active in the shop.
	 *
	 * @return bool
	 */
	public static function is_woocommerce_active() {
		return self::is_plugin_active( 'woocommerce.php' );
	}

	/**
	 * Checks if cURL library is installed and enabled on the system.
	 *
	 * @return bool
	 */
	public static function is_curl_enabled() {
		return function_exists( 'curl_version' );
	}

	/**
	 * Gets the name of the shop
	 *
	 * @return string
	 */
	public static function get_shop_name() {
		$name = get_bloginfo( 'name' );
		if ( ! $name ) {
			$name = '';
		}

		return $name;
	}

	/**
	 * Gets URL for CleverReach controller.
	 *
	 * @param string  $name Name of the controller without "CleverReach" and "Controller".
	 * @param string  $action Name of the action.
	 * @param mixed[] $params Associative array of parameters.
	 *
	 * @return string
	 */
	public static function get_controller_url( $name, $action = '', array $params = array() ) {
		$query = array( 'cleverreach_wc_controller' => $name );
		if ( ! empty( $action ) ) {
			$query['action'] = $action;
		}

		$query = array_merge( $query, $params );

		return get_site_url() . '/?' . http_build_query( $query );
	}

	/**
	 * Gets base URL of default shop's frontend.
	 *
	 * @return string
	 */
	public static function get_shop_url() {
		if ( empty( self::$shop_url ) ) {
			self::$shop_url = get_site_url();
			/**
			 * Trimmed string.
			 *
			 * @var string $trimmed
			 */
			$trimmed        = str_replace( 'https://', '', self::$shop_url );
			self::$shop_url = $trimmed;
			/**
			 * Trimmed string.
			 *
			 * @var string $trimmed
			 */
			$trimmed        = str_replace( 'http://', '', self::$shop_url );
			self::$shop_url = $trimmed;
		}

		return self::$shop_url;
	}

	/**
	 *  Gets URL to requested file. Plugin root is returned if no parameter is passed.
	 *
	 * @param string $path Path to requested file.
	 *
	 * @return string
	 */
	public static function get_clever_reach_base_url( $path = '' ) {
		return plugins_url( $path, dirname( __DIR__ ) );
	}

	/**
	 * Returns plugin current version.
	 *
	 * @return string
	 */
	public static function get_plugin_version() {
		$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . self::get_plugin_name() );

		return $plugin_data['Version'];
	}

	/**
	 * Get WooCommerce version
	 *
	 * @return string
	 */
	public static function get_woocommerce_version() {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugin_data = array();

		if ( self::is_woocommerce_active() ) {
			$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' );
		}

		return $plugin_data['Version'];
	}

	/**
	 * Checks if CleverReach plugin for WooCommerce is already installed.
	 *
	 * @return bool
	 */
	public static function is_cleverreach_plugin_for_wordpress_installed() {
		return self::is_plugin_active( 'cleverreach-wp.php' );
	}

	/**
	 * Returns whether the current page is a CleverReach plugin page.
	 *
	 * @return bool
	 */
	public static function is_current_page_cleverreach() {
		return array_key_exists( 'page', $_REQUEST ) && ( 'cleverreach-wc' === $_REQUEST['page'] );
	}

	/**
	 * Checks if plugin is active.
	 *
	 * @param string $plugin_name The name of the plugin main entry point file. For example "cleverreach-wp.php".
	 *
	 * @return bool
	 */
	private static function is_plugin_active( $plugin_name ) {
		$all_plugins = get_option( 'active_plugins' );

		if ( is_multisite() ) {
			$all_plugins = array_merge( $all_plugins, array_keys( get_site_option( 'active_sitewide_plugins' ) ) );
		}

		foreach ( $all_plugins as $plugin ) {
			if ( false !== strpos( $plugin, '/' . $plugin_name ) ) {
				return true;
			}
		}

		return false;
	}
}
