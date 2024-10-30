<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\TaskExecution\Events;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Interfaces\Priority;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\Events\Event;

/**
 * Class QueueItemEnqueuedEvent
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\TaskExecution\Events
 */
class QueueItemEnqueuedEvent extends Event
{
    const CLASS_NAME = __CLASS__;
    /**
     * @var string
     */
    protected $queueName;
    /**
     * @var \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task
     */
    protected $task;
    /**
     * @var string
     */
    protected $context;
    /**
     * @var int
     */
    protected $priority;

    /**
     * QueueItemEnqueuedEvent constructor.
     *
     * @param string $queueName
     * @param \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task $task
     * @param string $context
     * @param int $priority
     */
    public function __construct($queueName, Task $task, $context = '', $priority = Priority::NORMAL)
    {
        $this->queueName = $queueName;
        $this->task = $task;
        $this->context = $context;
        $this->priority = $priority;
    }

    /**
     * @return string
     */
    public function getQueueName()
    {
        return $this->queueName;
    }

    /**
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }
}
