<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\ViewModel\Dashboard;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Contracts\GroupService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\InitialSynchronization\Tasks\Composite\InitialSyncTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\ReceiverSyncTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\SubscribeReceiverTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\UnsubscribeReceiverTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Stats\Contracts\SnapshotService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Stats\Entity\Stats;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Stats\Http\Proxy;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Exceptions\BaseException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogContextData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryClassException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Interfaces\QueueItemRepository;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\Operators;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\QueryFilter;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\RepositoryRegistry;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class Receiver_Statistics
 *
 * @package CleverReach\WooCommerce\ViewModel\Dashboard
 */
class Receiver_Statistics {


	/**
	 * Snapshots
	 *
	 * @var Stats[]
	 */
	private $snapshots;

	/**
	 * ReceiverStatistics constructor.
	 */
	public function __construct() {
		$this->snapshots = $this->get_snapshot_service()->getSnapshots();
	}

	/**
	 * Gets last sync time.
	 *
	 * @return false|string
	 */
	public function get_last_sync_time() {
		$filter = new QueryFilter();

		try {
			$filter->where(
				'taskType',
				Operators::IN,
				array(
					SubscribeReceiverTask::getClassName(),
					UnsubscribeReceiverTask::getClassName(),
					ReceiverSyncTask::getClassName(),
					InitialSyncTask::getClassName(),
				)
			);

			$filter->where( 'status', Operators::EQUALS, 'completed' );

			$filter->orderBy( 'queueTime', 'DESC' );

			$task = $this->get_storage()->selectOne( $filter );

			return $task ? gmdate( 'd-m-Y H:i', $task->getFinishTimestamp() ) : '';
		} catch ( QueryFilterInvalidParamException  $e ) {
			Logger::logError(
				"Unable to return last sync time: {$e->getMessage()}",
				'Integration',
				array( new LogContextData( 'trace', $e->getTraceAsString() ) )
			);
		} catch ( RepositoryClassException  $e ) {
			Logger::logError(
				"Unable to return last sync time: {$e->getMessage()}",
				'Integration',
				array( new LogContextData( 'trace', $e->getTraceAsString() ) )
			);
		} catch ( RepositoryNotRegisteredException  $e ) {
			Logger::logError(
				"Unable to return last sync time: {$e->getMessage()}",
				'Integration',
				array( new LogContextData( 'trace', $e->getTraceAsString() ) )
			);
		}

		return '';
	}

	/**
	 * Retrieves number of customers
	 *
	 * @return int
	 */
	public function get_customers() {
		try {
			$stats     = $this->get_stats_proxy()->getStats( $this->get_group_service()->getId() );
			$customers = $stats->getTotalReceiverCount();
		} catch ( BaseException $e ) {
			$customers = 0;
		}

		return $customers;
	}

	/**
	 * Retrieves number of subscribed users
	 *
	 * @return int
	 */
	public function get_subscribed() {
		$latest_snapshot = end( $this->snapshots );

		return $latest_snapshot ? $latest_snapshot->getSubscribed() : 0;
	}


	/**
	 * Retrieves number of unsubscribed users
	 *
	 * @return int
	 */
	public function get_unsubscribed() {
		$latest_snapshot = end( $this->snapshots );

		return $latest_snapshot ? $latest_snapshot->getUnsubscribed() : 0;
	}

	/**
	 * Retrieves snapshot service
	 *
	 * @return SnapshotService
	 */
	private function get_snapshot_service() {
		/**
		 * Snapshot service.
		 *
		 * @var SnapshotService $snapshot_service
		 */
		$snapshot_service = ServiceRegister::getService( SnapshotService::CLASS_NAME );

		return $snapshot_service;
	}


	/**
	 * Retrieves group service
	 *
	 * @return GroupService
	 */
	protected function get_group_service() {
		/**
		 * Group service.
		 *
		 * @var GroupService $group_service
		 */
		$group_service = ServiceRegister::getService( GroupService::CLASS_NAME );

		return $group_service;
	}

	/**
	 * Retrieves stats proxy
	 *
	 * @return Proxy
	 */
	private function get_stats_proxy() {
		/**
		 * Proxy
		 *
		 * @var Proxy $proxy
		 */
		$proxy = ServiceRegister::getService( Proxy::CLASS_NAME );

		return $proxy;
	}

	/**
	 * Gets task storage.
	 *
	 * @return QueueItemRepository
	 *
	 * @throws RepositoryClassException When repository instance is not QueueItemRepository.
	 * @throws RepositoryNotRegisteredException When repository is not registered.
	 */
	private function get_storage() {
		return RepositoryRegistry::getQueueItemRepository();
	}
}
