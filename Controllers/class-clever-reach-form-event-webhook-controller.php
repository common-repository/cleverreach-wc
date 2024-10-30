<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Controllers;

use CleverReach\WooCommerce\Components\Handlers\Events_Handler;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Events\Form_Events_Service;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\FormEventsService as Base_Form_Events_Service;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\WebHooks\Handler as Forms_Handler;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use WP_HTTP_Response;

/**
 * Class Clever_Reach_Form_Event_Webhook_Controller
 *
 * @package CleverReach\WooCommerce\Controllers
 */
class Clever_Reach_Form_Event_Webhook_Controller extends Clever_Reach_Base_Controller {


	/**
	 * Forms handler.
	 *
	 * @var Forms_Handler
	 */
	private $handler;

	/**
	 * Form events service.
	 *
	 * @var Form_Events_Service
	 */
	private $events_service;

	/**
	 * Is request call internal.
	 *
	 * @var bool
	 */
	protected $is_internal = false;

	/**
	 * Handles form event
	 *
	 * @return void
	 */
	public function handle_form() {
		$events_handler = new Events_Handler( $this->get_events_service(), $this->get_handler() );

		$response = new WP_HTTP_Response();
		$response = $events_handler->handle_request( $response );

		$this->return_plain_text( $response->get_data(), $response->get_status() );
	}

	/**
	 * Form events service.
	 *
	 * @return Form_Events_Service
	 */
	private function get_events_service() {
		if ( null === $this->events_service ) {
			/**
			 * Form events service.
			 *
			 * @var Form_Events_Service $events_service
			 */
			$events_service       = ServiceRegister::getService( Base_Form_Events_Service::CLASS_NAME );
			$this->events_service = $events_service;
		}

		return $this->events_service;
	}

	/**
	 * Forms handler.
	 *
	 * @return Forms_Handler
	 */
	private function get_handler() {
		if ( null === $this->handler ) {
			$this->handler = new Forms_Handler();
		}

		return $this->handler;
	}
}
