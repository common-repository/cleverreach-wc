<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\ViewModel\Dashboard;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Dashboard\Contracts\DashboardService as Base_Dashboard_Service;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Dashboard\DashboardService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueItem;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueService;

/**
 * Class Initial_Sync_State
 *
 * @package CleverReach\WooCommerce\ViewModel\Dashboard
 */
class Initial_Sync_State {

	/**
	 * Initial sync task
	 *
	 * @var QueueItem $initial_sync_task
	 */
	private $initial_sync_task;

	/**
	 * Dashboard Service
	 *
	 * @var DashboardService
	 */
	private $dashboard_service;

	/**
	 * InitialSync constructor.
	 */
	public function __construct() {
		$this->initial_sync_task = $this->get_queue_service()->findLatestByType( 'InitialSyncTask' );
	}

	/**
	 * Checks if initial sync is in progress
	 *
	 * @return bool
	 */
	public function is_initial_sync_in_progress() {
		if ( ! $this->initial_sync_task ) {
			return false;
		}

		return in_array(
			$this->initial_sync_task->getStatus(),
			array( QueueItem::CREATED, QueueItem::QUEUED, QueueItem::IN_PROGRESS ),
			true
		);
	}

	/**
	 * Checks if initial sync has failed
	 *
	 * @return bool
	 */
	public function is_initial_sync_failed() {
		if ( ! $this->initial_sync_task ) {
			return false;
		}

		return $this->initial_sync_task->getStatus() === QueueItem::FAILED;
	}

	/**
	 * Returns description of failure
	 *
	 * @return string
	 */
	public function get_failure_description() {
		if ( ! $this->is_initial_sync_failed() ) {
			return '';
		}

		return $this->initial_sync_task->getFailureDescription();
	}

	/**
	 * Checks if import statistics should be displayed
	 *
	 * @return bool
	 */
	public function should_display_import_stats() {
		try {
			if ( $this->should_display_statistics() && ! $this->get_dashboard_service()->isSyncStatisticsDisplayed() ) {
				$this->get_dashboard_service()->setSyncStatisticsDisplayed( true );

				return true;
			}

			return false;
		} catch ( QueryFilterInvalidParamException $e ) {
			Logger::logError( $e->getMessage(), 'Integration' );

			return false;
		}
	}

	/**
	 * Checks if statistics should be displayed
	 *
	 * @return bool
	 */
	public function should_display_statistics() {
		if ( ! $this->initial_sync_task ) {
			return false;
		}

		return $this->initial_sync_task->getStatus() === QueueItem::COMPLETED;
	}

	/**
	 * Returns queue service
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
	 * Returns dashboard service
	 *
	 * @return DashboardService
	 */
	private function get_dashboard_service() {
		if ( null === $this->dashboard_service ) {
			/**
			 * Dashboard service.
			 *
			 * @var DashboardService $dashboard_service
			 */
			$dashboard_service       = ServiceRegister::getService( Base_Dashboard_Service::CLASS_NAME );
			$this->dashboard_service = $dashboard_service;
		}

		return $this->dashboard_service;
	}
}
