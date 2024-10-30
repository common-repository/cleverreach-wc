<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\InitialSynchronization\Tasks\Composite\Components;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\CompositeTask;

abstract class InitialSyncSubTask extends CompositeTask
{
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
