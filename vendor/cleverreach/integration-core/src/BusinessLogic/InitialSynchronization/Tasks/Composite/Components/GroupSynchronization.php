<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\InitialSynchronization\Tasks\Composite\Components;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\Tasks\RegisterDynamicContentTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Tasks\CacheFormsTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Tasks\CreateDefaultFormTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Tasks\RegisterFormEventsTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Tasks\CreateGroupTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Tasks\RegisterGroupEventsTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Mailing\Tasks\CreateDefaultMailing;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\RegisterReceiverEventsTask;

/**
 * Class GroupSynchronization
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\InitialSynchronization\Tasks\Composite\Components
 */
class GroupSynchronization extends InitialSyncSubTask
{
    const CLASS_NAME = __CLASS__;

    /**
     * GroupSynchronization constructor.
     */
    public function __construct()
    {
        parent::__construct($this->getSubTasks());
    }

    /**
     * Retrieves sub tasks.
     *
     * @return array<string,int>
     */
    protected function getSubTasks()
    {
        return array(
            CreateGroupTask::CLASS_NAME => 10,
            CreateDefaultFormTask::CLASS_NAME => 15,
            CacheFormsTask::CLASS_NAME => 40,
            CreateDefaultMailing::CLASS_NAME => 10,
            RegisterReceiverEventsTask::CLASS_NAME => 5,
            RegisterFormEventsTask::CLASS_NAME => 5,
            RegisterGroupEventsTask::CLASS_NAME => 5,
            RegisterDynamicContentTask::CLASS_NAME => 10,
        );
    }
}
