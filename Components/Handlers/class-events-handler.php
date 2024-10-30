<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Handlers;

use CleverReach\WooCommerce\Components\Util\HTTP_Helper;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\WebHooks\Handler as Forms_Handler;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\WebHooks\Handler as Receiver_Handler;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\WebHooks\Handler as Groups_Handler;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Contracts\EventsService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO\WebHook;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Exceptions\BaseException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use WP_HTTP_Response;

/**
 * Class Events_Handler
 *
 * @package CleverReach\WooCommerce\Components\Handlers
 */
class Events_Handler {

	/**
	 * Event service
	 *
	 * @var EventsService
	 */
	private $events_service;

	/**
	 * Receiver or Form handler
	 *
	 * @var Receiver_Handler|Forms_Handler|Groups_Handler
	 */
	private $handler;

	/**
	 * EventsHandler constructor.
	 *
	 * @param EventsService                                 $events_service Event service.
	 * @param Receiver_Handler|Forms_Handler|Groups_Handler $handler Handler.
	 */
	public function __construct( EventsService $events_service, $handler ) {
		$this->events_service = $events_service;
		$this->handler        = $handler;
	}

	/**
	 * Handles request from CleverReach.
	 *
	 * @param WP_HTTP_Response $response Response.
	 *
	 * @return WP_HTTP_Response
	 */
	public function handle_request( $response ) {
		if ( HTTP_Helper::is_get() ) {
			$response = $this->register( $response );
		} else {
			$response = $this->handle( $response );
		}

		return $response;
	}

	/**
	 * Registers webhook route
	 *
	 * @param WP_HTTP_Response $response Response.
	 *
	 * @return WP_HTTP_Response
	 */
	private function register( $response ) {
		$secret = HTTP_Helper::get_param( 'secret' );

		if ( null === $secret ) {
			$response->set_status( 400 );
		} else {
			$token = $this->events_service->getVerificationToken() . ' ' . $secret;
			$response->set_headers( array( 'Content-Type' => 'text/plain' ) );
			$response->set_data( $token );
			$response->set_status( 200 );
		}

		return $response;
	}

	/**
	 * Handles webhook.
	 *
	 * @param WP_HTTP_Response $response Response.
	 *
	 * @return WP_HTTP_Response
	 */
	private function handle( $response ) {
		$token = HTTP_Helper::get_request_calltoken();
		if ( null === ( $token )
			|| $this->events_service->getCallToken() !== $token ) {
			$response->set_status( 401 );

			return $response;
		}

		$params = HTTP_Helper::get_body();

		if ( ! $this->validate( $params ) ) {
			$response->set_status( 400 );

			return $response;
		}

		$web_hook = new WebHook( $params['condition'], $params['event'], $params['payload'] );

		try {
			$this->handler->handle( $web_hook );
		} catch ( BaseException $e ) {
			Logger::logError( $e->getMessage(), 'Integration' );
		}

		$response->set_status( 200 );

		return $response;
	}

	/**
	 * Validates webhook params
	 *
	 * @param mixed[] $params Array of params.
	 *
	 * @return bool
	 */
	private function validate( $params ) {
		return ! empty( $params['payload'] )
				&& ! empty( $params['event'] )
				&& ! empty( $params['condition'] );
	}
}
