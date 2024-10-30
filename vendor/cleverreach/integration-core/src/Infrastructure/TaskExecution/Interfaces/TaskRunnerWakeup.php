<?php

namespace CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Interfaces;

/**
 * Interface TaskRunnerWakeup.
 *
 * @package CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Interfaces
 */
interface TaskRunnerWakeup
{
    /**
     * Fully qualified name of this interface.
     */
    const CLASS_NAME = __CLASS__;

    /**
     * Wakes up TaskRunner instance asynchronously if active instance is not already running.
     *
     * @return void
     */
    public function wakeup();
}
