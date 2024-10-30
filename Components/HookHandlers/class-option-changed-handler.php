<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\HookHandlers;

use CleverReach\WooCommerce\Components\Repositories\Base_Repository;
use CleverReach\WooCommerce\Components\Repositories\Role_Repository;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Tag\Tag_Service;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Configuration\SyncConfiguration;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\ReceiverSyncTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Segment\Tasks\CreateSegmentsTask;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;

/**
 * Class Option_Changed_Handler
 *
 * @package CleverReach\WooCommerce\Components\HookHandlers
 */
class Option_Changed_Handler extends Base_Handler {


	/**
	 * Tag service
	 *
	 * @var Tag_Service
	 */
	private $tag_service;

	/**
	 * Handles option changed event.
	 *
	 * @param string $option changed option.
	 *
	 * @param mixed  $old_value old value.
	 *
	 * @param mixed  $new_value new value.
	 *
	 * @return void
	 */
	public function handle( $option, $old_value, $new_value ) {
		if ( ! is_string( $option ) || ! $this->should_handle_event() ) {
			return;
		}

		$site_option_name = 'blogname';
		$role_option_name = Base_Repository::get_table_name( Role_Repository::ROLES_TABLE );

		if ( $site_option_name === $option ) {
			$this->handle_store_name_change( $old_value, $new_value );
		} elseif ( $role_option_name === $option ) {
			$this->handle_roles_change( $old_value, $new_value );
		}
	}

	/**
	 * Handles store name changed event.
	 *
	 * @param string $old_name old site name.
	 *
	 * @param string $new_name new site name.
	 *
	 * @return void
	 */
	private function handle_store_name_change( $old_name, $new_name ) {
		if ( $old_name === $new_name ) {
			return;
		}
		Logger::logInfo( "Site name change event detected. Name changed from $old_name to $new_name." );

		$tags_for_delete = $this->get_tag_service()->get_origin_tags_by_website_name( $old_name );

		$this->enqueue_task(
			new ReceiverSyncTask(
				new SyncConfiguration(
					array(),
					$tags_for_delete
				)
			)
		);
		$this->enqueue_task( new CreateSegmentsTask() );
	}

	/**
	 * Handles role changed event.
	 *
	 * @param mixed $old_roles roles before change.
	 *
	 * @param mixed $new_roles roles after change.
	 *
	 * @return void
	 */
	private function handle_roles_change( $old_roles, $new_roles ) {
		if ( $old_roles !== $new_roles ) {
			Logger::logInfo( "Roles changed event detected. Roles changed from $old_roles to $new_roles." );

			$tags_for_delete = $this->get_tag_service()->get_origin_tags_by_roles( $old_roles );

			$this->enqueue_task(
				new ReceiverSyncTask(
					new SyncConfiguration(
						array(),
						$tags_for_delete
					)
				)
			);
			$this->enqueue_task( new CreateSegmentsTask() );
		}
	}

	/**
	 * Retrieve Tag service
	 *
	 * @return Tag_Service
	 */
	protected function get_tag_service() {
		if ( ! $this->tag_service ) {
			$this->tag_service = new Tag_Service();
		}

		return $this->tag_service;
	}
}
