<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\HookHandlers;

use CleverReach\WooCommerce\Components\Repositories\Customers\Subscriber_Repository;
use CleverReach\WooCommerce\Components\Util\HTTP_Helper;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogContextData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use Exception;

/**
 * Class Add_Newsletter_To_Order_Handler
 *
 * @package CleverReach\WooCommerce\Components\HookHandlers
 */
class Add_Newsletter_To_Order_Handler extends Base_Handler {


	/**
	 * Handles order created event to add custom meta to the order.
	 *
	 * @param int $order_id id of created order.
	 *
	 * @return void
	 */
	public function handle( $order_id ) {
		if ( ! $this->should_handle_event() ) {
			return;
		}

		Logger::logInfo( 'Order created for custom fields event detected. Order id: ' . $order_id, 'Integration' );

		try {
			$cr_status = HTTP_Helper::get_param( Subscriber_Repository::NEWSLETTER_STATUS_FIELD ) === '1';
			if ( ! $cr_status ) {
				return;
			}

			$order = wc_get_order( $order_id );
			$email = $order->get_user() ? $order->get_user()->user_email : $order->get_billing_email();

			$subscriber = $this->get_subscriber_service()->getReceiver( $email );
			if ( null === $subscriber ) {
				$subscriber = $this->get_subscriber_service()->get_newsletter_by_email( $email );
			}

			if ( ! $subscriber || $subscriber->isActive() ) {
				return;
			}

			if ( wc()->session ) {
				wc()->session->set( 'cr_wc_should_send_doi', $this->get_doi_service()->is_doi_enabled() );
				wc()->session->set( 'cr_wc_should_subscribe', true );
			}
		} catch ( Exception $e ) {
			Logger::logError(
				'Failed to get subscriber by email or update subscriber.',
				'Integration',
				array(
					new LogContextData( 'message', $e->getMessage() ),
					new LogContextData( 'trace', $e->getTraceAsString() ),
				)
			);
		}
	}
}
