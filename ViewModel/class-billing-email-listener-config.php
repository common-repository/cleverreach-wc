<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\ViewModel;

use CleverReach\WooCommerce\Components\Util\Shop_Helper;

/**
 * Class Billing_Email_Listener_Config
 *
 * @package CleverReach\WooCommerce\ViewModel
 */
class Billing_Email_Listener_Config {


	/**
	 * Retrieves configuration array.
	 *
	 * @return array<string,string>
	 */
	public static function get_config() {
		return array(
			'listenerUrl' => Shop_Helper::get_controller_url( 'Abandoned_Cart_Billing_Email_Listener', 'execute' ),
		);
	}
}
