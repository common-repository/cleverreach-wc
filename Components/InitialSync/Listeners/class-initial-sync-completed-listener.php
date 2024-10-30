<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\InitialSync\Listeners;

use CleverReach\WooCommerce\Components\WebHooks\Handlers\Group_Deleted_Handler;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\RepositoryRegistry;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueService;

/**
 * Class Initial_Sync_Completed_Listener
 *
 * @package CleverReach\WooCommerce\Components\InitialSync\Listeners
 */
class Initial_Sync_Completed_Listener {

	const CLASS_NAME = __CLASS__;

	/**
	 * Handles initial sync completed event.
	 *
	 * @return void
	 *
	 * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryClassException Repository class exception.
	 * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException Exception when Repository is not registered.
	 */
	public static function handle() {
		$queue_item = self::get_queue_service()->findLatestByType( Group_Deleted_Handler::getClassName() );
		if ( ! $queue_item ) {

			return;
		}

		self::get_queue_repository()->delete( $queue_item );
	}

	/**
	 * Retrieves queue service.
	 *
	 * @return QueueService
	 */
	private static function get_queue_service() {
		// @phpstan-ignore-next-line
		return ServiceRegister::getService( QueueService::CLASS_NAME );
	}

	/**
	 * Retrieves queue repository.
	 *
	 * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Interfaces\QueueItemRepository Queue item repository.
	 * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryClassException Repository class exception.
	 * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException Repository not registered exception.
	 */
	private static function get_queue_repository() {
		return RepositoryRegistry::getQueueItemRepository();
	}
}
