<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SecondarySynchronization\Tasks\Composite;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Tasks\CreateFieldsTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\ReceiverSyncTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\Tasks\UpdateSyncSettingsTask;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\CompositeTask;

class SecondarySyncTask extends CompositeTask
{
    /**
     * SecondarySyncTask constructor.
     */
    public function __construct()
    {
        parent::__construct($this->getSubTasks());
    }

    /**
     * @inheritdoc
     */
    public function isArchivable()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    protected function createSubTask($taskKey)
    {
        if ($taskKey === CreateFieldsTask::CLASS_NAME) {
            return new CreateFieldsTask(false);
        }

        /** @var \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task $task */
        $task = new $taskKey;

        return $task;
    }

    /**
     * Returns list of subtasks
     *
     * @return array<string,int>
     */
    protected function getSubTasks()
    {
        return array(
            UpdateSyncSettingsTask::CLASS_NAME => 5,
            CreateFieldsTask::CLASS_NAME => 20,
            ReceiverSyncTask::CLASS_NAME => 75,
        );
    }
}
