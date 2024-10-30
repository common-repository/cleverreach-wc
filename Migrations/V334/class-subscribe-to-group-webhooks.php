<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Migrations\V334;

use CleverReach\WooCommerce\Components\Util\Step;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\GroupService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Tasks\RegisterGroupEventsTask;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueService;
use Exception;

/**
 * Class Subscribe_To_Group_Webhooks
 *
 * @package CleverReach\WooCommerce\Migrations\V334
 */
class Subscribe_To_Group_Webhooks extends Step {

	const CLASS_NAME = __CLASS__;

	/**
	 *  Executes migration step.
	 *
	 * @return void
	 *
	 * @inheritDoc
	 */
	public function execute() {
		try {
			if ( $this->get_group_service()->getId() !== '' ) {
				$this->get_queue_service()->enqueue(
					$this->get_config_service()->getDefaultQueueName(),
					new RegisterGroupEventsTask()
				);
			}
		} catch ( Exception $e ) {
			Logger::logError( 'Unable to enqueue RegisterGroupEventsTask: ' . $e->getMessage() );
		}
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
