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

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Configuration\Config_Service;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\InitialSynchronization\Tasks\Composite\Components\FieldsSynchronization;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\InitialSynchronization\Tasks\Composite\Components\GroupSynchronization;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\InitialSynchronization\Tasks\Composite\Components\ReceiverSynchronization;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\InitialSynchronization\Tasks\Composite\InitialSyncTask;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueItem;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueService;
use Exception;

/**
 * Class Clever_Reach_Initial_Sync_Controller
 *
 * @package CleverReach\WooCommerce\Controllers
 */
class Clever_Reach_Initial_Sync_Controller extends Clever_Reach_Base_Controller {


	/**
	 * Queue service.
	 *
	 * @var QueueService
	 */
	private $queue_service;


	/**
	 * Retry initial sync.
	 *
	 * @return void
	 */
	public function retry() {
		try {
			$this->get_queue_service()->enqueue(
				$this->get_config_service()->getDefaultQueueName(),
				new InitialSyncTask()
			);
			$this->return_json( array( 'success' => true ) );
		} catch ( Exception $e ) {
			Logger::logError( $e->getMessage(), 'Integration' );

			$this->return_json( array( 'success' => false ) );
		}
	}

	/**
	 * Check status execution.
	 *
	 * @return void
	 *
	 * @throws QueueItemDeserializationException Exception if deserialization fails.
	 */
	public function check_status() {
		$sync_task_queue_item = $this->get_queue_service()->findLatestByType( 'InitialSyncTask' );

		if ( null === $sync_task_queue_item || QueueItem::FAILED === $sync_task_queue_item->getStatus() ) {
			$this->return_json( array( 'status' => QueueItem::FAILED ) );
		}

		/**
		 * Initial sync task
		 *
		 * @var InitialSyncTask $initial_sync_task
		 */
		$initial_sync_task          = $sync_task_queue_item->getTask();
		$initial_sync_task_progress = $initial_sync_task->getProgressByTask();

		$this->return_json(
			array(
				'status'       => $sync_task_queue_item->getStatus(),
				'taskStatuses' => array(
					'subscriberlist' => array(
						'status'   => $this->get_status( $initial_sync_task_progress[ GroupSynchronization::CLASS_NAME ] ),
						'progress' => $initial_sync_task_progress[ GroupSynchronization::CLASS_NAME ],
					),
					'add_fields'     => array(
						'status'   => $this->get_status( $initial_sync_task_progress[ FieldsSynchronization::CLASS_NAME ] ),
						'progress' => $initial_sync_task_progress[ FieldsSynchronization::CLASS_NAME ],
					),
					'recipient_sync' => array(
						'status'   => $this->get_status( $initial_sync_task_progress[ ReceiverSynchronization::CLASS_NAME ] ),
						'progress' => $initial_sync_task_progress[ ReceiverSynchronization::CLASS_NAME ],
					),
				),
			)
		);
	}

	/**
	 * Sets show notification to false (triggers when user clicks on notification dismiss button)
	 *
	 * @return void
	 */
	public function dismiss_notification_button() {
		try {
			$this->get_config_service()->set_show_admin_notice( false );
			$this->return_json( array( 'success' => true ) );
		} catch ( Exception $e ) {
			Logger::logError( $e->getMessage(), 'Integration' );

			$this->return_json( array( 'success' => false ) );
		}
	}

	/**
	 * Returns queue item status.
	 *
	 * @param int $progress Progress for queue item.
	 *
	 * @return string
	 */
	private function get_status( $progress ) {
		$status = QueueItem::QUEUED;
		if ( 0 < $progress && $progress < 100 ) {
			$status = QueueItem::IN_PROGRESS;
		} elseif ( $progress >= 100 ) {
			$status = QueueItem::COMPLETED;
		}

		return $status;
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
	 * Returns Configuration service.
	 *
	 * @return Config_Service
	 */
	private function get_config_service() {
		/**
		 * Config service.
		 *
		 * @var Config_Service $config_service
		 */
		$config_service = ServiceRegister::getService( Configuration::CLASS_NAME );

		return $config_service;
	}
}
