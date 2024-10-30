<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic\Uninstall\Contracts;

/**
 * Interface Uninstall_Service_Interface
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic\Uninstall\Contracts
 */
interface Uninstall_Service_Interface {

	const CLASS_NAME = __CLASS__;

	/**
	 * Removes all plugin data.
	 *
	 * @return void
	 */
	public function remove_data();
}
