<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\HookHandlers\Automation;

use CleverReach\WooCommerce\Components\HookHandlers\Base_Handler;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Contracts\RecoveryEmailStatus;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToDeleteAutomationRecordException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogContextData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use WP_User;

/**
 * Class User_Login_Handler
 *
 * @package CleverReach\WooCommerce\Components\HookHandlers\Automation
 */
class Before_User_Login_Handler extends Base_Handler {


	/**
	 * Update automation record before user logs in
	 *
	 * @param string $username Username.
	 * @param string $password Password.
	 *
	 * @return void
	 */
	public function handle( $username, $password ) {
		if ( ! $this->should_handle_event() || 0 !== get_current_user_id() ) {
			return;
		}

		$user = wp_authenticate_username_password( null, $username, $password );

		if ( ! $user instanceof WP_User ) {
			return;
		}

		$session_key = WC()->session->get_customer_id();

		// If registered user logins to existing guest session.
		if ( get_current_user_id() !== $session_key ) {
			$record = $this->get_automation_record_service()->findBy(
				array(
					'cartId' => $session_key,
					'status' => RecoveryEmailStatus::PENDING,
				)
			);

			if ( 1 === count( $record ) ) {
				$record = $record[0];

				try {
					$cart_items = $this->get_cart_service()->get_cart_items_by_session_key( $session_key );
					$this->create_or_update_automation_record(
						$cart_items,
						$record->getAmount(),
						$user->user_email,
						$user->ID
					);

					$this->get_automation_record_service()->delete( $record->getId() );
				} catch ( FailedToDeleteAutomationRecordException $e ) {
					Logger::logError(
						'Failed to delete automation record.',
						'Integration',
						array(
							new LogContextData( 'message', $e->getMessage() ),
							new LogContextData( 'trace', $e->getTraceAsString() ),
						)
					);
				}

				return;
			}
		}

		$record = $this->get_automation_record_service()->findBy(
			array(
				'cartId' => $user->ID,
				'status' => RecoveryEmailStatus::PENDING,
			)
		);

		if ( 1 === count( $record ) ) {
			return;
		}

		// Create or update automation record, if necessary.
		$cart_items = $this->get_cart_service()->get_cart_items_by_session_key( $user->ID );
		$total      = $this->get_cart_service()->get_cart_total_for_cart_items( $cart_items );
		$this->create_or_update_automation_record( $cart_items, $total, $user->user_email, $user->ID );
	}
}
