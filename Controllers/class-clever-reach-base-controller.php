<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Controllers;

use CleverReach\WooCommerce\Components\Util\HTTP_Helper;

/**
 * Class Clever_Reach_Base_Controller
 *
 * @package CleverReach\WooCommerce\Controllers
 */
class Clever_Reach_Base_Controller {

	/**
	 * Is request call internal.
	 *
	 * @var bool
	 */
	protected $is_internal = true;

	/**
	 * Processes request. Reads 'action' parameter and calls action method if provided.
	 *
	 * @param string $action Request action.
	 *
	 * @return void
	 */
	public function process( $action = '' ) {
		if ( $this->is_internal ) {
			$this->validate_internal_call();
		}

		if ( empty( $action ) ) {
			$action = HTTP_Helper::get_param( 'action' );
		}

		if ( $action ) {
			if ( method_exists( $this, $action ) ) {
				$this->$action();
			} else {
				$this->return_json( array( 'error' => "Method $action does not exist!" ), 404 );
			}
		}
	}

	/**
	 * Validates if call made from plugin code is secure by checking session token.
	 * If call is not secure, returns 401 status and terminates request.
	 *
	 * @return void
	 */
	protected function validate_internal_call() {
		if ( ! $this->is_user_admin() ) {
			status_header( 401 );
			nocache_headers();

			exit();
		}
	}

	/**
	 * Check if user is administrator
	 *
	 * @return bool
	 */
	public function is_user_admin() {
		return in_array( 'administrator', wp_get_current_user()->roles );
	}

	/**
	 * Sets response header content type to json, echos supplied $data as a json string and terminates request.
	 *
	 * @param mixed[] $data Array to be returned as a json response.
	 * @param int     $status_code Response status code.
	 *
	 * @return void
	 */
	protected function return_json( array $data, $status_code = 200 ) {
		wp_send_json( $data, $status_code );
	}

	/**
	 * Sets response header content type to plain/text, echos supplied $data as a json string and terminates request.
	 *
	 * @param string $data Array to be returned as a json response.
	 * @param int    $status_code Response status code.
	 *
	 * @return void
	 */
	protected function return_plain_text( $data, $status_code = 200 ) {
		status_header( $status_code );
		echo esc_html( sanitize_text_field( $data ) );
		die();
	}

	/**
	 * Returns 404 response and terminates request.
	 *
	 * @return void
	 */
	protected function redirect404() {
		status_header( 404 );
		nocache_headers();

		require get_404_template();

		exit();
	}

	/**
	 * Dies with defined status code.
	 *
	 * @param int $status response status code.
	 *
	 * @return void
	 */
	protected function die_with_status( $status ) {
		status_header( $status );
		die();
	}
}
