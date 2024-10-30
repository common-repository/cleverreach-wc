<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\WebHooks\Listeners;

use CleverReach\WooCommerce\Components\WebHooks\Handlers\Receiver_Updated_Handler;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Events\ReceiverUpdatedEvent;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;

/**
 * Class Receiver_Updated_Listener
 *
 * @package CleverReach\WooCommerce\Components\WebHooks\Listeners
 */
class Receiver_Updated_Listener extends Listener {


	const CLASS_NAME = __CLASS__;

	/**
	 * Handles event
	 *
	 * @param ReceiverUpdatedEvent $event Receiver updated event.
	 *
	 * @return void
	 *
	 * @throws QueueStorageUnavailableException Exception if queue storage unavailable.
	 */
	public static function handle( ReceiverUpdatedEvent $event ) {
		static::enqueue( new Receiver_Updated_Handler( $event->getReceiverId() ) );
	}
}
