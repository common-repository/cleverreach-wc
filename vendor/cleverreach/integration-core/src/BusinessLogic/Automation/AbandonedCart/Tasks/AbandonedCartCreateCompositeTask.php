<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Tasks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Webhooks\Tasks\RegisterEventTask;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\CompositeTask;

class AbandonedCartCreateCompositeTask extends CompositeTask
{
    public function __construct()
    {
        parent::__construct($this->getTasks());
    }

    /**
     * @inheritDoc
     */
    protected static function createTask(array $tasks, $initialProgress)
    {
        return new static();
    }

    /**
     * Instantiates task.
     *
     * @param string $taskKey
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task
     */
    protected function createSubTask($taskKey)
    {
        /** @var \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task $task */
        $task = new $taskKey;

        return $task;
    }

    /**
     * Retrieves sub tasks.
     *
     * @return array<string,int>
     */
    protected function getTasks()
    {
        return array(
            AbandonedCartCreateTask::CLASS_NAME => 30,
            RegisterEventTask::CLASS_NAME => 70,
        );
    }
}
