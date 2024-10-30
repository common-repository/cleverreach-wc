<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\WebHooks\Handlers;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Events\Event;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver;
use Exception;

/**
 * Class Receiver_Subscribed_Handler
 *
 * @package CleverReach\WooCommerce\Components\WebHooks\Handlers
 */
class Receiver_Subscribed_Handler extends Receiver_Handler {


	/**
	 * Runs task logic.
	 *
	 * @inheritDoc
	 *
	 * @return void
	 *
	 * @throws Exception Exception.
	 */
	public function execute() {
		$receiver = $this->get_receiver( $this->get_group_service()->getId(), $this->receiver_id );
		/**
		 * Receiver object.
		 *
		 * @var Receiver $subscriber
		 */
		$subscriber = $this->get_subscriber_service()->get_newsletter_by_email( $receiver->getEmail() );

		if ( $subscriber ) {
			$this->update_subscriber( $receiver, $subscriber );
			$this->get_events_buffer_handler()->handle( Event::subscriberSubscribed( $receiver->getEmail() ) );
		}

		$this->reportProgress( 100 );
	}
}
