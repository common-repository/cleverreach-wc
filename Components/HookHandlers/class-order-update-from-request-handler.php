<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\HookHandlers;

use CleverReach\WooCommerce\Components\Repositories\Customers\Subscriber_Repository;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogContextData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use Exception;
use WC_Order;
use WP_REST_Request;

/**
 * Class Order_Update_From_Request_Handler
 *
 * @package CleverReach\WooCommerce\Components\HookHandlers
 */
class Order_Update_From_Request_Handler extends Base_Handler {

	/**
	 * Handle order update from request.
	 *
	 * @param WC_Order        $order - WC order.
	 * @param WP_REST_Request $request - Request.
	 *
	 * @return void
	 */
	public function handle( WC_Order $order, WP_REST_Request $request ) {
		if ( ! $this->should_handle_event() ) {
			return;
		}
		$order_id = $order->get_id();
		try {
			$cr_data   = $request['extensions']['cleverreach-wc'];
			$cr_status = $cr_data['is_subscribed'];
			if ( ! $cr_status ) {
				return;
			}

			$order = wc_get_order( $order_id );
			$order->update_meta_data( Subscriber_Repository::get_newsletter_column(), $cr_status );
			$order->save();

			$email = $order->get_user() ? $order->get_user()->user_email : $order->get_billing_email();

			$subscriber = $this->get_subscriber_service()->getReceiver( $email );
			if ( null === $subscriber ) {
				$subscriber = $this->get_subscriber_service()->get_newsletter_by_email( $email );
			}

			if ( ! $subscriber ) {
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
