<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\WebHooks\Listeners;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Configuration\Config_Service;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigurationManager;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task;

/**
 * Class Listener
 *
 * @package CleverReach\WooCommerce\Components\WebHooks\Listeners
 */
abstract class Listener {


	/**
	 * Enqueues task.
	 *
	 * @param Task $task Task.
	 *
	 * @return void
	 *
	 * @throws QueueStorageUnavailableException Exception if queue storage unavailable.
	 * @throws QueryFilterInvalidParamException Exception if query filter is invalid.
	 */
	protected static function enqueue( Task $task ) {
		$context    = ConfigurationManager::getInstance()->getContext();
		$queue_name = static::get_config_service()->getDefaultQueueName();

		static::get_queue()->enqueue( $queue_name, $task, $context );
	}

	/**
	 * Retrieves config service.
	 *
	 * @return Config_Service
	 */
	protected static function get_config_service() {
		/**
		 * Config service.
		 *
		 * @var Config_Service $config_service
		 */
		$config_service = ServiceRegister::getService( Configuration::CLASS_NAME );

		return $config_service;
	}

	/**
	 * Retrieves queue service.
	 *
	 * @return QueueService
	 */
	protected static function get_queue() {
		/**
		 * Queue service.
		 *
		 * @var QueueService $queue_service
		 */
		$queue_service = ServiceRegister::getService( QueueService::CLASS_NAME );

		return $queue_service;
	}
}
