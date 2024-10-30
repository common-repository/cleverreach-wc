<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Migrations\V300\Tasks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\Tasks\RegisterDynamicContentTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Tasks\CacheFormsTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Tasks\CreateDefaultFormTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Tasks\RegisterFormEventsTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\InitialSynchronization\Tasks\Composite\Components\InitialSyncSubTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Mailing\Tasks\CreateDefaultMailing;

/**
 * Class Migration_Group_Sync_Task.
 *
 * @package CleverReach\WooCommerce\Migrations\V300\Tasks
 */
class Migration_Group_Sync_Task extends InitialSyncSubTask {



	/**
	 * Migration_Group_Sync_Task constructor.
	 */
	public function __construct() {
		parent::__construct( $this->get_sub_tasks() );
	}

	/**
	 * Get sub tasks.
	 *
	 * @return int[] Subtasks
	 */
	protected function get_sub_tasks() {
		return array(
			CreateDefaultFormTask::CLASS_NAME      => 15,
			CacheFormsTask::CLASS_NAME             => 50,
			CreateDefaultMailing::CLASS_NAME       => 15,
			RegisterFormEventsTask::CLASS_NAME     => 5,
			RegisterDynamicContentTask::CLASS_NAME => 15,
		);
	}
}
