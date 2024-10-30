<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic\Schedule;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Configuration\Config_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Schedule\Contracts\Schedule_Service_Interface;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Tasks\Composite\Components\UpdateUserInfoTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Scheduler\Models\DailySchedule;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Scheduler\Models\HourlySchedule;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Scheduler\Models\MinuteSchedule;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Scheduler\Models\MonthlySchedule;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Scheduler\Models\Schedule;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Stats\Task\SaveReceiverStatsTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Utility\Tasks\TaskCleanupTask;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Interfaces\RepositoryInterface;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\RepositoryRegistry;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueItem;

/**
 * Class Schedule_Service
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic\Schedule
 */
class Schedule_Service implements Schedule_Service_Interface {

	/**
	 * Register Schedules during auth process
	 *
	 * @return void
	 *
	 * @throws RepositoryNotRegisteredException Exception if repository not registered.
	 */
	public function register_schedules() {
		$this->register_schedules_v300();
		$this->register_schedules_v310();
	}

	/**
	 * Registers schedules for v3.1.0
	 *
	 * @return void
	 *
	 * @throws RepositoryNotRegisteredException Exception if repository not registered.
	 */
	public function register_schedules_v310() {
		$this->register_task_cleanup( 'CreateCartAutomationTask', self::SCHEDULE_TYPE_HOURLY );
		$this->register_task_cleanup( 'TriggerCartAutomationTask', self::SCHEDULE_TYPE_HOURLY );
	}

	/**
	 * Registers schedules for v3.0.0
	 *
	 * @return void
	 *
	 * @throws RepositoryNotRegisteredException Exception if repository not registered.
	 */
	public function register_schedules_v300() {
		$this->register_update_user_schedule();
		$this->register_save_receiver_stats_schedule();

		$this->register_task_cleanup( 'ScheduleCheckTask', self::SCHEDULE_TYPE_MINUTE );
		$this->register_task_cleanup( 'ReceiverSyncTask', self::SCHEDULE_TYPE_DAILY );
		$this->register_task_cleanup( 'DeactivateReceiverTask', self::SCHEDULE_TYPE_DAILY );
		$this->register_task_cleanup( 'SubscribeReceiverTask', self::SCHEDULE_TYPE_DAILY );
		$this->register_task_cleanup( 'UnsubscribeReceiverTask', self::SCHEDULE_TYPE_DAILY );
		$this->register_task_cleanup( 'OrderSyncTask', self::SCHEDULE_TYPE_HOURLY );
		$this->register_task_cleanup( 'ConnectTask', self::SCHEDULE_TYPE_DAILY );
		$this->register_task_cleanup( 'UpdateSyncSettingsTask', self::SCHEDULE_TYPE_MONTHLY );
		$this->register_task_cleanup( 'ReceiverCreatedHandler', self::SCHEDULE_TYPE_DAILY );
		$this->register_task_cleanup( 'ReceiverUpdatedHandler', self::SCHEDULE_TYPE_DAILY );
		$this->register_task_cleanup( 'ReceiverSubscribedHandler', self::SCHEDULE_TYPE_DAILY );
		$this->register_task_cleanup( 'ReceiverUnsubscribedHandler', self::SCHEDULE_TYPE_DAILY );
		$this->register_task_cleanup( 'TaskCleanupTask', self::SCHEDULE_TYPE_DAILY );
		$this->register_task_cleanup( 'UpdateUserInfoTask', self::SCHEDULE_TYPE_DAILY );
		$this->register_task_cleanup( 'CreateSegmentsTask', self::SCHEDULE_TYPE_DAILY );
		$this->register_task_cleanup( 'SendDoubleOptInEmailsTask', self::SCHEDULE_TYPE_DAILY );
		$this->register_task_cleanup( 'SaveReceiverStatsTask', self::SCHEDULE_TYPE_DAILY );
		$this->register_task_cleanup( 'CacheFormsTask', self::SCHEDULE_TYPE_DAILY );
	}

	/**
	 * Registers task cleanup.
	 *
	 * @param string $task_type Type of the task.
	 * @param string $schedule_type Type of the schedule.
	 *
	 * @return void
	 *
	 * @throws RepositoryNotRegisteredException Exception if repository not registered.
	 */
	protected function register_task_cleanup( $task_type, $schedule_type ) {
		$task       = new TaskCleanupTask( $task_type, array( QueueItem::COMPLETED ) );
		$queue_name = $this->get_config_service()->getDefaultQueueName();

		$schedule = null;

		switch ( $schedule_type ) {
			case self::SCHEDULE_TYPE_MINUTE:
				$schedule = new MinuteSchedule( $task, $queue_name );
				$schedule->setInterval( 5 );
				$schedule->setRecurring( true );
				$schedule->setNextSchedule();
				break;
			case self::SCHEDULE_TYPE_HOURLY:
				$schedule = new HourlySchedule( $task, $queue_name );
				$schedule->setRecurring( true );
				$schedule->setNextSchedule();
				break;
			case self::SCHEDULE_TYPE_DAILY:
				$schedule = new DailySchedule( $task, $queue_name );
				$schedule->setRecurring( true );
				$schedule->setNextSchedule();
				break;
			case self::SCHEDULE_TYPE_MONTHLY:
				$schedule = new MonthlySchedule( $task, $queue_name );
				$schedule->setRecurring( true );
				$schedule->setNextSchedule();
				break;
		}

		if ( ! $schedule ) {
			return;
		}

		$this->get_schedule_repo()->save( $schedule );
	}

	/**
	 * Registers update user schedule.
	 *
	 * @return void
	 *
	 * @throws RepositoryNotRegisteredException Exception if repository not registered.
	 */
	private function register_update_user_schedule() {
		$task       = new UpdateUserInfoTask();
		$queue_name = $this->get_config_service()->getDefaultQueueName();
		$schedule   = new DailySchedule( $task, $queue_name );
		$schedule->setHour( 2 );
		$schedule->setMinute( 15 );
		$schedule->setRecurring( true );
		$schedule->setNextSchedule();

		$this->get_schedule_repo()->save( $schedule );
	}

	/**
	 * Retrieves schedule repository.
	 *
	 * @return RepositoryInterface
	 * @throws RepositoryNotRegisteredException Exception if repository not registered.
	 */
	private function get_schedule_repo() {
		return RepositoryRegistry::getRepository( Schedule::getClassName() );
	}

	/**
	 * Gets Configuration service.
	 *
	 * @return Config_Service Service instance.
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

	/**
	 * Registers receiver stats schedule.
	 *
	 * @return void
	 *
	 * @throws RepositoryNotRegisteredException Exception if repository not registered.
	 */
	private function register_save_receiver_stats_schedule() {
		$task       = new SaveReceiverStatsTask();
		$queue_name = $this->get_config_service()->getDefaultQueueName();
		$schedule   = new DailySchedule( $task, $queue_name );
		$schedule->setNextSchedule();
		$this->get_schedule_repo()->save( $schedule );
	}
}
