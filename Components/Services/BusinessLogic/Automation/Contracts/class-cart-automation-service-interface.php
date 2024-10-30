<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation\Contracts;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation\Entities\Recovery_Record;

/**
 * Interface Cart_Automation_Service_Interface
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation\Contracts
 */
interface Cart_Automation_Service_Interface {


	/**
	 * Merge carts before checkout.
	 *
	 * @param Recovery_Record $recovery_record Recovery record.
	 *
	 * @return void
	 */
	public function merge_carts_before_checkout( Recovery_Record $recovery_record );
}
