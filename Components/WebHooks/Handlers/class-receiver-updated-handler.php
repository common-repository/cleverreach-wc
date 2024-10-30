<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\WebHooks\Handlers;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver;
use Exception;

/**
 * Class Receiver_Updated_Handler
 *
 * @package CleverReach\WooCommerce\Components\WebHooks\Handlers
 */
class Receiver_Updated_Handler extends Receiver_Handler {


	/**
	 *  Runs task logic.
	 *
	 * @inheritDoc
	 *
	 * @return void
	 *
	 * @throws Exception Exception.
	 */
	public function execute() {
		/**
		 * Receiver object.
		 *
		 * @var Receiver $receiver
		 */
		$receiver   = $this->get_receiver( $this->get_group_service()->getId(), $this->receiver_id );
		$subscriber = $this->get_subscriber_service()->get_newsletter_by_email( $receiver->getEmail() );

		$this->reportProgress( 30 );
		$this->handle_subscriber_update_or_create_event( $receiver, $subscriber );
		$this->reportProgress( 100 );
	}
}
