<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Migrations\V300\Tasks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\InitialSynchronization\Tasks\Composite\Components\GroupSynchronization;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\InitialSynchronization\Tasks\Composite\InitialSyncTask;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task;

/**
 * Class Migration_Initial_Sync_Task.
 *
 * @package CleverReach\WooCommerce\Migrations\V300\Tasks
 */
class Migration_Initial_Sync_Task extends InitialSyncTask {


	/**
	 * Create subtask.
	 *
	 * @param string $task_key Task key.
	 *
	 * @return Task|Migration_Group_Sync_Task
	 */
	protected function createSubTask( $task_key ) {
		if ( GroupSynchronization::CLASS_NAME === $task_key ) {
			return new Migration_Group_Sync_Task();
		}

		return parent::createSubTask( $task_key );
	}
}
