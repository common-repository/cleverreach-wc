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
 * Class Multisite_Customer_Role_Added_Handler
 *
 * @package CleverReach\WooCommerce\Components\HookHandlers
 */
class Multisite_Customer_Role_Added_Handler extends Base_Handler {

	/**
	 * Handles user role added event.
	 *
	 * @param int     $user_id User's id.
	 * @param string  $user_role Role to be added.
	 * @param mixed[] $old_roles Array of user's previous roles.
	 *
	 * @return void
	 */
	public function handle( $user_id, $user_role, $old_roles ) {
		$sites = get_sites();
		foreach ( $sites as $site ) {
			switch_to_blog( $site->blog_id );
			if ( $this->should_handle_event() && is_user_member_of_blog( $user_id ) ) {
				try {
					$user = $this->get_subscriber_service()->get_registered_receiver_by_id( $user_id );

					if ( $user ) {
						Logger::logInfo(
							'Multisite customer role changed event detected. Customer email: ' . $user->getEmail(),
							'Integration'
						);

						$tags_for_delete = $this->get_tag_service()->get_origin_tags_by_roles( $old_roles );

						$this->get_events_buffer_handler()->handle(
							Event::buyerUpdated( $user->getEmail(), $tags_for_delete )
						);
					}
				} catch ( Exception $e ) {
					Logger::logError(
						'Failed to handle multisite customer role changed event.',
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
