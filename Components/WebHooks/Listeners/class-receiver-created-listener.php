<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\WebHooks\Listeners;

use CleverReach\WooCommerce\Components\WebHooks\Handlers\Receiver_Created_Handler;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Events\ReceiverCreatedEvent;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;

/**
 * Class Receiver_Created_Listener
 *
 * @package CleverReach\WooCommerce\Components\WebHooks\Listeners
 */
class Receiver_Created_Listener extends Listener {


	const CLASS_NAME = __CLASS__;

	/**
	 * Handles event
	 *
	 * @param ReceiverCreatedEvent $event Receiver created event.
	 *
	 * @return void
	 *
	 * @throws QueueStorageUnavailableException Exception if queue storage unavailable.
	 */
	public static function handle( ReceiverCreatedEvent $event ) {
		static::enqueue( new Receiver_Created_Handler( $event->getReceiverId() ) );
	}
}
