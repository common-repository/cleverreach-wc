<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Migrations\V300;

use CleverReach\WooCommerce\Components\Util\Step;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\AuthorizationService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Contracts\AuthorizationService as AuthorizationServiceInterface;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Contracts\GroupService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\InitialSynchronization\Tasks\Composite\InitialSyncTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\TaskExecution\QueueService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigurationManager;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\Migrations\Exceptions\Failed_To_Execute_Migration_Step_Exception;
use CleverReach\WooCommerce\Migrations\V300\Tasks\Migration_Initial_Sync_Task;
use Exception;

/**
 * Class Enqueue_Migration_Initial_Sync_Task
 *
 * @package CleverReach\WooCommerce\Migrations\V300
 */
class Enqueue_Migration_Initial_Sync_Task extends Step {


	const CLASS_NAME = __CLASS__;

	/**
	 * Configuration manager.
	 *
	 * @var ConfigurationManager
	 */
	private $config_manager;

	/**
	 * Execute migration step.
	 *
	 * @throws Failed_To_Execute_Migration_Step_Exception Failed to execute migration step exception|Query filter invalid param exception.
	 */
	public function execute() {
		try {
			if ( $this->get_auth_service()->isOffline() ) {
				$this->get_config_manager()->saveConfigValue( 'userMigrated', true );

				return;
			}

			if ( $this->get_group_service()->getId() !== '' ) {
				$this->get_queue_service()->enqueue(
					$this->get_config_service()->getDefaultQueueName(),
					new Migration_Initial_Sync_Task()
				);
			} else {
				$this->get_queue_service()->enqueue(
					$this->get_config_service()->getDefaultQueueName(),
					new InitialSyncTask()
				);
			}
		} catch ( Exception $e ) {
			throw new Failed_To_Execute_Migration_Step_Exception(
				'Failed to execute migration step because: ' . esc_html( $e->getMessage() )
			);
		}
	}

	/**
	 * Retrieve Authorization service.
	 *
	 * @return AuthorizationService
	 */
	private function get_auth_service() {
		/**
		 * Authorization service.
		 *
		 * @var AuthorizationService $auth_service
		 */
		$auth_service = ServiceRegister::getService( AuthorizationServiceInterface::CLASS_NAME );

		return $auth_service;
	}

	/**
	 * Retrieve Configuration manager.
	 *
	 * @return ConfigurationManager
	 */
	private function get_config_manager() {
		if ( null === $this->config_manager ) {
			$this->config_manager = ConfigurationManager::getInstance();
		}

		return $this->config_manager;
	}

	/**
	 * Retrieve Group service.
	 *
	 * @return GroupService
	 */
	private function get_group_service() {
		/**
		 * Group service.
		 *
		 * @var GroupService $group_service
		 */
		$group_service = ServiceRegister::getService( GroupService::CLASS_NAME );

		return $group_service;
	}

	/**
	 * Retrieve Queue service.
	 *
	 * @return QueueService
	 */
	private function get_queue_service() {
		/**
		 * Queue service.
		 *
		 * @var QueueService $queue_service
		 */
		$queue_service = ServiceRegister::getService( QueueService::CLASS_NAME );

		return $queue_service;
	}

	/**
	 * Retrieve Configuration.
	 *
	 * @return Configuration
	 */
	private function get_config_service() {
		/**
		 * Config service.
		 *
		 * @var Configuration $config_service
		 */
		$config_service = ServiceRegister::getService( Configuration::CLASS_NAME );

		return $config_service;
	}
}
