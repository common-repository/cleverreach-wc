<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\HookHandlers;

use CleverReach\WooCommerce\Components\Repositories\Customers\Subscriber_Repository;
use CleverReach\WooCommerce\Components\Util\HTTP_Helper;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Events\Event;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\DeactivateReceiverTask;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogContextData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use Exception;
use WP_User;

/**
 * Class Multisite_Customer_Updated_Handler
 *
 * @package CleverReach\WooCommerce\Components\HookHandlers
 */
class Multisite_Customer_Updated_Handler extends Base_Handler {

	/**
	 * Handles user updated event.
	 *
	 * @param int     $user_id id of updated user.
	 *
	 * @param WP_User $old_user_data old data.
	 *
	 * @return void
	 */
	public function handle( $user_id, $old_user_data ) {
		$sites      = get_sites();
		$email_sent = false;
		foreach ( $sites as $site ) {
			switch_to_blog( $site->blog_id );

			if ( $this->should_handle_event() && is_user_member_of_blog( $user_id ) ) {
				Logger::logInfo( 'Multisite user updated event detected. User id: ' . $user_id, 'Integration' );

				try {
					$from_profile  = true;
					$from_checkout = false;
					if ( wc()->session ) {
						$from_profile  = wc()->session->get( 'from_profile' );
						$from_checkout = wc()->session->get( 'cr_wc_should_subscribe' );
					}

					$cr_status = HTTP_Helper::get_param( Subscriber_Repository::NEWSLETTER_STATUS_FIELD ) === '1';

					$new_user_data = get_user_by( 'id', $user_id );

					if ( $old_user_data->user_email !== $new_user_data->user_email ) {
						$this->enqueue_task( new DeactivateReceiverTask( $old_user_data->user_email ) );
					}

					$subscriber = $this->get_subscriber_service()->getReceiver( $new_user_data->user_email );
					if ( null === $subscriber ) {
						$subscriber = $this->get_subscriber_service()->get_newsletter_by_email( $new_user_data->user_email );
					}

					if ( null === $subscriber ) {
						return;
					}

					$old_cr_status = $subscriber->isActive();

					$diff_roles      = array_diff( $old_user_data->roles, $new_user_data->roles );
					$tags_for_delete = $this->get_tag_service()->get_origin_tags_by_roles( $diff_roles );

					$this->get_events_buffer_handler()->handle(
						Event::subscriberUpdated( $subscriber->getEmail(), $tags_for_delete )
					);

					// Subscribe receiver if isn't already.
					if ( $cr_status && ! $old_cr_status && ! $from_checkout ) {
						if ( ( ! $email_sent ) && $this->get_doi_service()->is_doi_enabled() ) {
							$this->send_confirmation_email( $subscriber->getEmail(), $tags_for_delete );
							$email_sent = true;
						} else {
							$this->activate_subscriber( $subscriber, $tags_for_delete );
						}
						// Unsubscribe receiver.
					} elseif ( ! $cr_status && $old_cr_status && $from_profile ) {
						$this->deactivate_subscriber( $subscriber, $tags_for_delete );
					}
				} catch ( Exception $e ) {
					Logger::logError(
						'Failed to handle customer updated event.',
						'Integration',
						array(
							new LogContextData( 'message', $e->getMessage() ),
							new LogContextData( 'trace', $e->getTraceAsString() ),
						)
					);
				}
			}

			restore_current_blog();
		}
	}
}
