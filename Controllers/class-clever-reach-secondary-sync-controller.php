<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Controllers;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SecondarySynchronization\Tasks\Composite\SecondarySyncTask;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueItem;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueService;
use Exception;

/**
 * Class Clever_Reach_Secondary_Sync_Controller
 *
 * @package CleverReach\WooCommerce\Controllers
 */
class Clever_Reach_Secondary_Sync_Controller extends Clever_Reach_Base_Controller {


	/**
	 * Queue service.
	 *
	 * @var QueueService
	 */
	private $queue_service;

	/**
	 * Retries secondary sync task.
	 *
	 * @return void
	 */
	public function retry() {
		try {
			if ( $this->is_secondary_sync_task_running() ) {
				$this->return_json( array( 'success' => true ) );
			}

			$this->get_queue_service()->enqueue(
				$this->get_config_service()->getDefaultQueueName(),
				new SecondarySyncTask()
			);

			$this->return_json( array( 'success' => true ) );
		} catch ( Exception $e ) {
			Logger::logError( $e->getMessage(), 'Integration' );

			$this->return_json( array( 'success' => false ) );
		}
	}

	/**
	 * Checks status of secondary sync task.
	 *
	 * @return void
	 */
	public function check_status() {
		$status = $this->get_secondary_sync_task_status();
		$this->return_json( array( 'status' => $status ) );
	}

	/**
	 * Checks if Secondary sync task is running
	 *
	 * @return bool
	 */
	private function is_secondary_sync_task_running() {
		/**
		 * Secondary sync task
		 *
		 * @var QueueItem $secondary_sync_task
		 */
		$secondary_sync_task = $this->get_queue_service()->findLatestByType( 'SecondarySyncTask' );

		return $secondary_sync_task &&
			in_array(
				$secondary_sync_task->getStatus(),
				array( QueueItem::CREATED, QueueItem::QUEUED, QueueItem::IN_PROGRESS ),
				true
			);
	}

	/**
	 * Returns secondary sync task status
	 *
	 * @return string
	 */
	private function get_secondary_sync_task_status() {
		/**
		 * Secondary sync task
		 *
		 * @var QueueItem $secondary_sync_task
		 */
		$secondary_sync_task = $this->get_queue_service()->findLatestByType( 'SecondarySyncTask' );

		if ( $secondary_sync_task ) {
			return $secondary_sync_task->getStatus();
		}

		return QueueItem::COMPLETED;
	}

	/**
	 * Returns queue service.
	 *
	 * @return QueueService
	 */
	private function get_queue_service() {
		if ( null === $this->queue_service ) {
			/**
			 * Queue service.
			 *
			 * @var QueueService $queue_service
			 */
			$queue_service       = ServiceRegister::getService( QueueService::CLASS_NAME );
			$this->queue_service = $queue_service;
		}

		return $this->queue_service;
	}

	/**
	 * Retrieves Config service.
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
