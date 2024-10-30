<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation\Trigger\Trigger_Newsletter_Subscription_Task;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Configuration\Config_Service;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\ORM\Interfaces\ConditionallyDeletes;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Scheduler\Models\MinuteSchedule;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Scheduler\Models\Schedule;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigurationManager;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogContextData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Interfaces\RepositoryInterface;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\Operators;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\QueryFilter;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\RepositoryRegistry;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\TimeProvider;
use Exception;

/**
 * Class Checkbox_Service
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation
 */
class Newsletter_Subscription_Service {


	/**
	 * Config Service
	 *
	 * @var Config_Service
	 */
	private $config_service;

	/**
	 * Schedule task for user subscription.
	 *
	 * @param string $billing_email Billing email.
	 *
	 * @return int
	 */
	public function subscribe( $billing_email ) {
		try {
			$queue_name = $this->get_config_service()->getDefaultQueueName();
			$context    = $this->get_config_manager()->getContext();

			$schedule = new MinuteSchedule( new Trigger_Newsletter_Subscription_Task( $billing_email ), $queue_name, $context );

			$current_time = $this->get_time_provider()->getCurrentLocalTime();
			$delay        = $this->get_config_service()->get_checkbox_display_time();
			$target_time  = $current_time->modify( "+$delay seconds" );
			$schedule->setHour( (int) $target_time->format( 'G' ) );
			$schedule->setMinute( (int) $target_time->format( 'i' ) );
			$schedule->setRecurring( false );
			$schedule->setNextSchedule();

			return $this->get_schedule_repository()->save( $schedule );
		} catch ( Exception $e ) {
			Logger::logError(
				'Failed to handle CleverReach newsletter subscription checked event.',
				'Integration',
				array(
					new LogContextData( 'message', $e->getMessage() ),
					new LogContextData( 'trace', $e->getTraceAsString() ),
				)
			);

			return 0;
		}
	}

	/**
	 * Delete scheduled task for user subscription.
	 *
	 * @param string | int $task_id Scheduled task id.
	 *
	 * @return void
	 */
	public function undo( $task_id ) {
		try {
			$query = new QueryFilter();
			$query->where( 'id', Operators::EQUALS, (int) $task_id );

			$schedule = $this->get_schedule_repository()->selectOne( $query );

			isset( $schedule ) && $this->get_schedule_repository()->delete( $schedule );
		} catch ( QueryFilterInvalidParamException $e ) {
			Logger::logError(
				'Failed to undo.',
				'Integration',
				array(
					new LogContextData( 'message', $e->getMessage() ),
					new LogContextData( 'trace', $e->getTraceAsString() ),
				)
			);
		}
	}

	/**
	 * Retrieves config service
	 *
	 * @return Config_Service
	 */
	private function get_config_service() {
		if ( null === $this->config_service ) {
			/**
			 * Config service.
			 *
			 * @var Config_Service $config_service
			 */
			$config_service       = ServiceRegister::getService( Configuration::CLASS_NAME );
			$this->config_service = $config_service;
		}

		return $this->config_service;
	}

	/**
	 * Retrieves configuration manager.
	 *
	 * @return ConfigurationManager
	 */
	private function get_config_manager() {
		/**
		 * Configuration manager.
		 *
		 * @var ConfigurationManager $config_manager
		 */
		$config_manager = ServiceRegister::getService( ConfigurationManager::CLASS_NAME );

		return $config_manager;
	}

	/**
	 * Retrieves time provider.
	 *
	 * @return TimeProvider
	 */
	private function get_time_provider() {
		/**
		 * Time provider.
		 *
		 * @var TimeProvider $time_provider
		 */
		$time_provider = ServiceRegister::getService( TimeProvider::CLASS_NAME );

		return $time_provider;
	}

	/**
	 * Retrieves schedule repository.
	 *
	 * @return RepositoryInterface
	 */
	private function get_schedule_repository() {
		/**
		 * Repository interface.
		 *
		 * @var RepositoryInterface $repository
		 */
		$repository = RepositoryRegistry::getRepository( Schedule::getClassName() );

		return $repository;
	}
}
