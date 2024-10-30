<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Scheduler;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Scheduler\Interfaces\Schedulable;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task;

abstract class ScheduledTask extends Task implements Schedulable
{
    /**
     * Defines whether schedulable task can be enqueued for execution if there is already instance with queued status.
     *
     * @return bool False indeicates that the schedulable task should not enqueued if there
     *      is already instance in queued status.
     */
    public function canHaveMultipleQueuedInstances()
    {
        // Overwrite this method only in exceptional situations.
        // Keep in mind that enqueueing already queued task
        // Might have NEGATIVE performance impact
        // Due to queue congestion.
        return false;
    }
}
