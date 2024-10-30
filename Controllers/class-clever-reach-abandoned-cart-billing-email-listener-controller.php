<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Controllers;

use CleverReach\WooCommerce\Components\Util\HTTP_Helper;

/**
 * Class Clever_Reach_Abandoned_Cart_Billing_Email_Listener_Controller
 *
 * @package CleverReach\WooCommerce\Controllers
 */
class Clever_Reach_Abandoned_Cart_Billing_Email_Listener_Controller extends Clever_Reach_Base_Controller {


	/**
	 *  Is request call internal.
	 *
	 * @var bool
	 */
	protected $is_internal = false;

	/**
	 * Handle billing email changes.
	 *
	 * @return void
	 */
	public function execute() {
		$billing_email = HTTP_Helper::get_param( 'billing_email' );

		if ( ! is_email( $billing_email ) || is_user_logged_in() ) {
			$this->return_json(
				array(
					'success' => false,
					'message' => 'Email address is not valid',
				),
				400
			);
		}

		WC()->cart->get_customer()->set_email( $billing_email );
		do_action( 'woocommerce_update_cart_action_cart_updated' );
	}
}
