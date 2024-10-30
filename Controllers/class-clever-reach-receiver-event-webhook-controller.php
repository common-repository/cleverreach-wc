<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Controllers;

use CleverReach\WooCommerce\Components\Handlers\Events_Handler;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Events\Receiver_Events_Service;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\ReceiverEventsService as Base_Receiver_Events_Service;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\WebHooks\Handler as Receivers_Handler;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use WP_HTTP_Response;

/**
 * Class Clever_Reach_Receiver_Event_Webhook_Controller
 *
 * @package CleverReach\WooCommerce\Controllers
 */
class Clever_Reach_Receiver_Event_Webhook_Controller extends Clever_Reach_Base_Controller {

	/**
	 * Receiver handler.
	 *
	 * @var Receivers_Handler
	 */
	private $handler;

	/**
	 * Receiver events service.
	 *
	 * @var Receiver_Events_Service
	 */
	private $events_service;

	/**
	 * Is request call internal.
	 *
	 * @var bool
	 */
	protected $is_internal = false;

	/**
	 * Handles receiver event.
	 *
	 * @return void
	 */
	public function handle_receiver() {
		$event_handler = new Events_Handler( $this->get_events_service(), $this->get_handler() );

		$response = new WP_HTTP_Response();
		$response = $event_handler->handle_request( $response );

		$this->return_plain_text( $response->get_data(), $response->get_status() );
	}

	/**
	 * Retrieves Receiver events service.
	 *
	 * @return Receiver_Events_Service
	 */
	private function get_events_service() {
		if ( null === $this->events_service ) {
			/**
			 * Receiver events service.
			 *
			 * @var Receiver_Events_Service $events_service
			 */
			$events_service       = ServiceRegister::getService( Base_Receiver_Events_Service::CLASS_NAME );
			$this->events_service = $events_service;
		}

		return $this->events_service;
	}

	/**
	 * Retrieves Receivers handler.
	 *
	 * @return Receivers_Handler
	 */
	private function get_handler() {
		if ( null === $this->handler ) {
			$this->handler = new Receivers_Handler();
		}

		return $this->handler;
	}
}
