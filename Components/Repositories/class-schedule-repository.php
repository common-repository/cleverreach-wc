<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Repositories;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Scheduler\Exceptions\ScheduleSaveException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Scheduler\Interfaces\ScheduleRepositoryInterface;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Scheduler\Models\Schedule;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Entity;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueItemSaveException;
use Exception;

/**
 * Class ScheduleRepository
 *
 * @package CleverReach\CleverReachIntegration\Repositories
 */
class Schedule_Repository extends Base_Repository implements ScheduleRepositoryInterface {

	/**
	 * Returns full class name.
	 *
	 * @noinspection SenselessMethodDuplicationInspection
	 *
	 * @return string Full class name.
	 */
	public static function getClassName() {
		return __CLASS__;
	}

	/**
	 * Creates or updates given schedule. If schedule id is not set, new schedule will be created otherwise
	 * update will be performed.
	 *
	 * @param Schedule            $schedule Schedule to save.
	 * @param array<string,mixed> $additional_where List of key/value pairs that must be satisfied upon saving schedule.
	 *
	 * @return int Id of saved queue item.
	 *
	 * @throws QueueItemSaveException Exception if queue item fails to save.
	 */
	public function saveWithCondition( Schedule $schedule, array $additional_where = array() ) {
		$item_id = null;
		try {
			$queue_item_id = $schedule->getId();
			if ( null === $queue_item_id || $queue_item_id <= 0 ) {
				$item_id = $this->save( $schedule );
			} else {
				$this->update_schedule( $schedule, $additional_where );
				$item_id = $queue_item_id;
			}
		} catch ( Exception $exception ) {
			throw new QueueItemSaveException(
				'Failed to save queue item with id: ' . esc_html( $item_id ),
				0,
				$exception // phpcs:ignore
			);
		}

		return $item_id;
	}

	/**
	 * Updates schedule
	 *
	 * @param Schedule            $schedule Schedule.
	 * @param array<string,mixed> $conditions Conditions.
	 *
	 * @return void
	 *
	 * @throws QueryFilterInvalidParamException|ScheduleSaveException Exception if saving schedule fails or query filter params are not valid.
	 */
	public function update_schedule( Schedule $schedule, array $conditions ) {
		$conditions = array_merge( $conditions, array( 'id' => $schedule->getId() ) );

		$item = $this->select_for_update( $conditions );
		$this->check_if_record_exists( $item );

		if ( null !== $item ) {
			$this->update_with_condition( $schedule, $conditions );
		}
	}

	/**
	 * Validates if item exists.
	 *
	 * @param Entity|null $item Schedule.
	 *
	 * @return void
	 *
	 * @throws ScheduleSaveException Exception if saving schedule fails.
	 */
	private function check_if_record_exists( Entity $item = null ) {
		if ( null === $item ) {
			$message = 'Failed to save schedule, update condition(s) not met.';
			Logger::logDebug( 'Failed to save schedule, update condition(s) not met.', 'Integration' );

			throw new ScheduleSaveException( esc_html( $message ) );
		}
	}
}
