<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Controllers;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Clever_Reach_Refresh_Controller
 *
 * @package CleverReach\WooCommerce\Controllers
 */
class Clever_Reach_Refresh_Controller extends Clever_Reach_Base_Controller {


	/**
	 * Check status
	 *
	 * @return void
	 */
	public function check_status() {
		$this->return_json( array( 'status' => 'finished' ) );
	}
}
