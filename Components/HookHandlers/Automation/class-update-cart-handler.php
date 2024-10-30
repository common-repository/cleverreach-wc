<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\HookHandlers\Automation;

use CleverReach\WooCommerce\Components\HookHandlers\Base_Handler;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;

/**
 * Class Update_Cart_Handler
 *
 * @package CleverReach\WooCommerce\Components\HookHandlers\Automation
 */
class Update_Cart_Handler extends Base_Handler {


	/**
	 * Handle add to cart hook
	 *
	 * @return void
	 */
	public function handle() {
		if ( ! $this->should_handle_event() ) {
			return;
		}

		Logger::logInfo(
			'Update abandoned cart record event detected. Session id: ' . WC()->session->get_customer_id(),
			'Integration'
		);

		$cart     = WC()->cart;
		$customer = $cart->get_customer();

		$email = $customer->get_email() ? $customer->get_email() : $customer->get_billing_email();

		if ( $cart->is_empty() ) {
			$this->delete_automation_record( WC()->session->get_customer_id() );
		} else {
			$this->create_or_update_automation_record(
				$cart->get_cart(),
				$cart->get_total( '' ),
				$email,
				WC()->session->get_customer_id(),
				true
			);
		}
	}
}
