<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Tasks\Composite;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Tasks\Composite\Components\CompleteAuthTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Tasks\Composite\Components\UpdateUserInfoTask;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\CompositeTask;

class ConnectTask extends CompositeTask
{
    /**
     * ConnectTask constructor.
     */
    public function __construct()
    {
        parent::__construct(
            array(
                UpdateUserInfoTask::CLASS_NAME => 70,
                CompleteAuthTask::CLASS_NAME => 30,
            )
        );
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
        /** @var \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task $task */
        $task = new $taskKey;

        return $task;
    }
}
