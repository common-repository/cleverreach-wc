<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\HookHandlers;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\DeactivateReceiverTask;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogContextData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use Exception;

/**
 * Class Customer_Deleted_Handler
 *
 * @package CleverReach\WooCommerce\Components\HookHandlers
 */
class Customer_Deleted_Handler extends Base_Handler {


	/**
	 * Handles users deleted event.
	 *
	 * @param int $user_id id of deleted user.
	 *
	 * @return void
	 */
	public function handle( $user_id ) {
		if ( ! $this->should_handle_event() ) {
			return;
		}

		try {
			$user = $this->get_subscriber_service()->get_registered_receiver_by_id( $user_id );

			if ( $user ) {
				Logger::logInfo(
					'Customer deleted event detected. Customer email: ' . $user->getEmail(),
					'Integration'
				);
				$this->enqueue_task( new DeactivateReceiverTask( $user->getEmail() ) );
			}
		} catch ( Exception $e ) {
			Logger::logError(
				'Failed to handle customer deleted event.',
				'Integration',
				array(
					new LogContextData( 'message', $e->getMessage() ),
					new LogContextData( 'trace', $e->getTraceAsString() ),
				)
			);
		}
	}
}
