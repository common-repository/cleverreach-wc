<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Controllers;

use CleverReach\WooCommerce\Components\Util\HTTP_Helper;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Entities\CartAutomation;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Interfaces\CartAutomationService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Interfaces\CartAutomationService as CartAutomationServiceInterface;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Webhooks\Handler;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO\WebHook;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Exceptions\BaseException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogContextData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use WP_HTTP_Response;

/**
 * Class Clever_Reach_Form_Event_Webhook_Controller
 *
 * @package CleverReach\WooCommerce\Controllers
 */
class Clever_Reach_Automation_Event_Webhook_Controller extends Clever_Reach_Base_Controller {


	/**
	 *  Is request call internal.
	 *
	 * @var bool
	 */
	protected $is_internal = false;

	/**
	 * Execute webhook.
	 *
	 * @return WP_HTTP_Response
	 */
	public function execute() {
		$id       = HTTP_Helper::get_param( 'crAutomationId' );
		$response = new WP_HTTP_Response();

		if ( empty( $id ) ) {
			$response->set_status( 400 );

			return $response;
		}

		$cart = $this->get_cart_automation_service()->find( (int) $id );

		if ( null === $cart ) {
			$response->set_status( 400 );

			return $response;
		}

		if ( HTTP_Helper::is_get() ) {
			$this->register( $cart, $response );
		} else {
			$this->handle( $cart, $response );
		}

		return $response;
	}

	/**
	 * Returns Cart automation service.
	 *
	 * @return CartAutomationService
	 */
	private function get_cart_automation_service() {
		/**
		 * Cart automation service.
		 *
		 * @var CartAutomationService $cart_automation_service
		 */
		$cart_automation_service = ServiceRegister::getService( CartAutomationServiceInterface::CLASS_NAME );

		return $cart_automation_service;
	}

	/**
	 * Register webhook for automation
	 *
	 * @param CartAutomation   $cart Cart Automation.
	 * @param WP_HTTP_Response $response WP Http Response.
	 *
	 * @return void
	 */
	private function register( CartAutomation $cart, WP_HTTP_Response $response ) {
		$secret = HTTP_Helper::get_param( 'secret' );

		if ( null === $secret ) {
			$response->set_status( 400 );

			return;
		}

		$token = $cart->getWebhookVerificationToken() . ' ' . $secret;

		$this->return_plain_text( $token );
	}

	/**
	 * Handles automation webhook
	 *
	 * @param CartAutomation   $cart Cart Automation.
	 * @param WP_HTTP_Response $response WP Http Response.
	 *
	 * @return void
	 */
	private function handle( CartAutomation $cart, WP_HTTP_Response $response ) {
		$token = HTTP_Helper::get_request_calltoken();
		if ( empty( $token ) || $cart->getWebhookCallToken() !== $token ) {
			$response->set_status( 400 );

			return;
		}

		$body = HTTP_Helper::get_body();

		if ( ! $this->validate( $body ) ) {
			$response->set_status( 400 );

			return;
		}

		$webhook = new WebHook( $body['condition'], $body['event'], $body['payload'] );
		$handler = new Handler();

		try {
			$handler->handle( $webhook );
		} catch ( BaseException $e ) {
			Logger::logError(
				'Unable to handle webhook.',
				'Integration',
				array(
					new LogContextData( 'trace', $e->getTraceAsString() ),
					new LogContextData( 'message', $e->getMessage() ),
					new LogContextData( 'payload', $body ),
				)
			);

			$response->set_status( 400 );

			return;
		}

		$response->set_status( 200 );
	}

	/**
	 * Validate webhook params
	 *
	 * @param array<string,mixed> $body Request body.
	 *
	 * @return bool
	 */
	private function validate( array $body ) {
		return ! empty( $body['payload'] ) && ! empty( $body['event'] ) && ! empty( $body['condition'] );
	}
}
