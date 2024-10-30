<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\HookHandlers;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Events\Event;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogContextData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use Exception;

/**
 * Class Order_Saved_Handler
 *
 * @package CleverReach\WooCommerce\Components\HookHandlers
 */
class Order_Saved_Handler extends Base_Handler {


	/**
	 * Handles order created event.
	 *
	 * @param int $order_id id of created order.
	 *
	 * @return void
	 */
	public function handle( $order_id ) {
		if ( ! $this->should_handle_event() ) {
			return;
		}

		Logger::logInfo( 'Order created event detected. Order id: ' . $order_id, 'Integration' );

		// We have to delete automation record once the order is made.
		if ( WC()->session ) {
			$this->delete_automation_record( WC()->session->get_customer_id() );
		}

		try {
			$order = wc_get_order( $order_id );
			$email = $order->get_user() ? $order->get_user()->user_email : $order->get_billing_email();
			if ( wc()->session ) {
				$mailing_id = wc()->session->get( 'cr_mailing_id' );
				$order->update_meta_data( '_cr_mailing_id', $mailing_id );
				$order->save();
			}

			$this->get_events_buffer_handler()->handle(
				Event::buyerUpdated( $email )
			);

			// This needs to be done here because of the task enqueuing order.
			if ( wc()->session && wc()->session->get( 'cr_wc_should_subscribe' ) ) {
				if ( wc()->session->get( 'cr_wc_should_send_doi' ) ) {
					$this->send_confirmation_email( $email );
					wc()->session->set( 'cr_wc_should_send_doi', false );
				} else {
					$subscriber = $this->get_subscriber_service()->get_newsletter_by_email( $email );
					$this->activate_subscriber( $subscriber );
				}

				wc()->session->set( 'cr_wc_should_subscribe', false );
			}
		} catch ( Exception $e ) {
			Logger::logError(
				'Failed to handle customer created event.',
				'Integration',
				array(
					new LogContextData( 'message', $e->getMessage() ),
					new LogContextData( 'trace', $e->getTraceAsString() ),
				)
			);
		}
	}
}
