<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\WebHooks\Handlers;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Events\Event;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException;

/**
 * Class Receiver_Unsubscribed_Handler
 *
 * @package CleverReach\WooCommerce\Components\WebHooks\Handlers
 */
class Receiver_Unsubscribed_Handler extends Receiver_Handler {


	/**
	 * Runs task logic.
	 *
	 * @inheritDoc
	 *
	 * @return void
	 *
	 * @throws AbortTaskExecutionException Exception.
	 */
	public function execute() {
		$receiver   = $this->get_receiver( $this->get_group_service()->getId(), $this->receiver_id );
		$subscriber = $this->get_subscriber_service()->getReceiver( $receiver->getEmail() );

		if ( null === $subscriber ) {
			throw new AbortTaskExecutionException(
				sprintf(
					'Receiver [%s] cannot be unsubscribed.',
					esc_html( $receiver->getEmail() )
				)
			);
		}

		$this->reportProgress( 60 );
		$this->handle_subscriber_update_or_create_event( $receiver, $subscriber );
		$this->get_events_buffer_handler()->handle( Event::subscriberUnsubscribed( $receiver->getEmail() ) );
		$this->reportProgress( 100 );
	}
}
