<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Controllers;

use CleverReach\WooCommerce\Components\Handlers\Events_Handler;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Events\Group_Events_Service;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\WebHooks\Handler as Groups_Handler;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\GroupEventsService as Base_Group_Events_Service;
use WP_HTTP_Response;

/**
 * Class Clever_Reach_Group_Event_Webhook_Controller
 *
 * @package CleverReach\WooCommerce\Controllers
 */
class Clever_Reach_Group_Event_Webhook_Controller extends Clever_Reach_Base_Controller {

	/**
	 * Groups handler.
	 *
	 * @var Groups_Handler
	 */
	private $handler;

	/**
	 * Group events service.
	 *
	 * @var Group_Events_Service
	 */
	private $events_service;

	/**
	 * Is request call internal.
	 *
	 * @var bool
	 */
	protected $is_internal = false;

	/**
	 * Handles group event
	 *
	 * @return void
	 */
	public function handle_group() {
		$event_handler = new Events_Handler( $this->get_events_service(), $this->get_handler() );

		$response = new WP_HTTP_Response();
		$response = $event_handler->handle_request( $response );

		$this->return_plain_text( $response->get_data(), $response->get_status() );
	}

	/**
	 * Gets the event service.
	 *
	 * @return Group_Events_Service
	 */
	private function get_events_service() {
		if ( null === $this->events_service ) {
			/**
			 *  Base group events service.
			 *
			 * @var Group_Events_Service $events_service
			 */
			$events_service       = ServiceRegister::getService( Base_Group_Events_Service::CLASS_NAME );
			$this->events_service = $events_service;
		}

		return $this->events_service;
	}

	/**
	 * Retrieves Groups handler.
	 *
	 * @return Groups_Handler
	 */
	private function get_handler() {
		if ( null === $this->handler ) {
			$this->handler = new Groups_Handler();
		}

		return $this->handler;
	}
}
