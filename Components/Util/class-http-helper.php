<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Util;

/**
 * Class HTTP_Helper
 *
 * @package CleverReach\WooCommerce\Components\Util
 */
class HTTP_Helper {


	/**
	 * Checks whether current request is GET.
	 *
	 * @return bool
	 */
	public static function is_get() {
		if ( isset( $_SERVER['REQUEST_METHOD'] ) ) {
			return 'GET' === $_SERVER['REQUEST_METHOD'];
		}

		return false;
	}

	/**
	 * Gets request parameter if exists. Otherwise, returns null.
	 *
	 * @param string $key Request parameter key.
	 *
	 * @return string|null
	 */
	public static function get_param( $key ) {
		if ( isset( $_REQUEST[ $key ] ) ) {
			return sanitize_text_field( wp_unslash( $_REQUEST[ $key ] ) );
		}

		return null;
	}

	/**
	 * Gets request body.
	 *
	 * @return mixed[]
	 */
	public static function get_body() {
		$file_contents = file_get_contents( 'php://input' );
		if ( ! $file_contents ) {
			$file_contents = '';
		}

		return json_decode( $file_contents, true );
	}

	/**
	 * Retrieves calltoken from request
	 *
	 * @return string|null
	 */
	public static function get_request_calltoken() {
		$request_call_token = isset( $_SERVER['HTTP_X_CR_CALLTOKEN'] ) ?
			sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_CR_CALLTOKEN'] ) ) : null;

		return isset( $request_call_token ) ? $request_call_token : null;
	}
}
