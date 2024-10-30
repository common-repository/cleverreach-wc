<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\WebHooks\Listeners;

use CleverReach\WooCommerce\Components\WebHooks\Handlers\Receiver_Subscribed_Handler;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Events\ReceiverSubscribedEvent;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;

/**
 * Class Receiver_Subscribed_Listener
 *
 * @package CleverReach\WooCommerce\Components\WebHooks\Listeners
 */
class Receiver_Subscribed_Listener extends Listener {


	const CLASS_NAME = __CLASS__;

	/**
	 * Handles event
	 *
	 * @param ReceiverSubscribedEvent $event Receiver subscribed event.
	 *
	 * @return void
	 *
	 * @throws QueueStorageUnavailableException Exception if queue storage unavailable.
	 */
	public static function handle( ReceiverSubscribedEvent $event ) {
		static::enqueue( new Receiver_Subscribed_Handler( $event->getReceiverId() ) );
	}
}
