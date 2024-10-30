<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Controllers;

use CleverReach\WooCommerce\Components\Handlers\Product_Search_Handler;
use WP_HTTP_Response;

/**
 * Class Clever_Reach_Product_Search_Controller
 *
 * @package CleverReach\WooCommerce\Controllers
 */
class Clever_Reach_Product_Search_Controller extends Clever_Reach_Base_Controller {


	/**
	 * Is request call internal.
	 *
	 * @var bool
	 */
	protected $is_internal = false;

	/**
	 * Handles product search request.
	 *
	 * @return void
	 */
	public function handle_request() {
		$handler = new Product_Search_Handler();

		$response = new WP_HTTP_Response();
		$response = $handler->handle_request( $response );

		$this->return_json( $response->get_data() ? $response->get_data() : array(), $response->get_status() );
	}
}
