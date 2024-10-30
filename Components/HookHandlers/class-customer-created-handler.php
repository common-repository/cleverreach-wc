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
use WP_User;

/**
 * Class Customer_Created_Handler
 *
 * @package CleverReach\WooCommerce\Components\HookHandlers
 */
class Customer_Created_Handler extends Base_Handler {


	/**
	 * Handles user created event.
	 *
	 * @param int $user_id id of created user.
	 *
	 * @return void
	 */
	public function handle( $user_id ) {
		if ( ! $this->should_handle_event() ) {
			return;
		}

		Logger::logInfo( 'User created event detected. User id: ' . $user_id, 'Integration' );

		try {
			/**
			 * WP user
			 *
			 * @var WP_User
			 */
			$user = get_user_by( 'id', $user_id );

			// Should never happen.
			if ( 0 === $user->ID ) {
				return;
			}

			$user_email = $user->user_email;

			$cr_status = HTTP_Helper::get_param( Subscriber_Repository::NEWSLETTER_STATUS_FIELD ) === '1';

			$subscriber = $this->get_subscriber_service()->getReceiver( $user_email );

			if ( null === $subscriber ) {
				$subscriber = $this->get_subscriber_service()->get_newsletter_by_email( $user_email );
			}

			// should never happen.
			if ( null === $subscriber ) {
				return;
			}

			$cr_status = $cr_status || $subscriber->isActive();

			if ( ! $cr_status ) {
				return;
			}

			if ( $this->get_doi_service()->is_doi_enabled() ) {
				$this->send_confirmation_email( $user_email );
			} else {
				$this->activate_subscriber( $subscriber );
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
