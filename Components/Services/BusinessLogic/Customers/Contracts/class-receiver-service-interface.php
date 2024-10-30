<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic\Customers\Contracts;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Contracts\ReceiverService as Base_Receiver_Service;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver;

/**
 * Class Receiver_Service_Interface
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic\Customers\Contracts
 */
interface Receiver_Service_Interface extends Base_Receiver_Service {


	/**
	 * Gets registered receiver by ID.
	 *
	 * @param integer $receiver_id ID of the receiver.
	 *
	 * @return Receiver|null
	 */
	public function get_registered_receiver_by_id( $receiver_id );
}
