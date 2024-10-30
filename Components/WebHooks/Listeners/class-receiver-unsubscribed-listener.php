<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\WebHooks\Listeners;

use CleverReach\WooCommerce\Components\WebHooks\Handlers\Receiver_Unsubscribed_Handler;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Events\ReceiverUnsubscribedEvent;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;

/**
 * Class Receiver_Unsubscribed_Listener
 *
 * @package CleverReach\WooCommerce\Components\WebHooks\Listeners
 */
class Receiver_Unsubscribed_Listener extends Listener {


	const CLASS_NAME = __CLASS__;

	/**
	 * Handles event
	 *
	 * @param ReceiverUnsubscribedEvent $event Receiver unsubscribed event.
	 *
	 * @return void
	 *
	 * @throws QueueStorageUnavailableException Exception if queue storage unavailable.
	 */
	public static function handle( ReceiverUnsubscribedEvent $event ) {
		static::enqueue( new Receiver_Unsubscribed_Handler( $event->getReceiverId() ) );
	}
}
