<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Migrations\V300;

use CleverReach\WooCommerce\Components\Util\Database;
use CleverReach\WooCommerce\Components\Util\Step;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\ReceiverEventsService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\Migrations\Exceptions\Failed_To_Execute_Migration_Step_Exception;
use Exception;

/**
 * Class Migrate_Clever_Reach_Webhooks_Data.
 *
 * @package CleverReach\WooCommerce\Migrations\V300
 */
class Migrate_Clever_Reach_Webhooks_Data extends Step {


	const CLASS_NAME = __CLASS__;

	/**
	 * Execute migration step.
	 *
	 * @throws Failed_To_Execute_Migration_Step_Exception Failed to execute migration step exception.
	 */
	public function execute() {
		try {
			$database           = new Database( $this->db );
			$call_token         = $database->get_old_config_value( 'CLEVERREACH_EVENT_CALL_TOKEN' );
			$verification_token = $database->get_old_config_value( 'CLEVERREACH_EVENT_VERIFICATION_TOKEN' );

			$this->get_receiver_events_service()->setCallToken( $call_token );
			$this->get_receiver_events_service()->setVerificationToken( $verification_token );
		} catch ( Exception $e ) {
			throw new Failed_To_Execute_Migration_Step_Exception(
				'Failed to execute migration step because: ' . esc_html( $e->getMessage() )
			);
		}
	}

	/**
	 * Retrieve event service.
	 *
	 * @return ReceiverEventsService
	 */
	private function get_receiver_events_service() {
		/**
		 * Receiver events service.
		 *
		 * @var ReceiverEventsService $receiver_events_service
		 */
		$receiver_events_service = ServiceRegister::getService( ReceiverEventsService::CLASS_NAME );

		return $receiver_events_service;
	}
}
