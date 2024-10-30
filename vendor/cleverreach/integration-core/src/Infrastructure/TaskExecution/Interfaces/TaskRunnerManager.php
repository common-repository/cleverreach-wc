<?php

namespace CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Interfaces;

/**
 * Interface TaskRunnerManager
 *
 * @package CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Interfaces
 */
interface TaskRunnerManager
{
    const CLASS_NAME = __CLASS__;

    /**
     * Halts task runner.
     *
     * @return void
     */
    public function halt();

    /**
     * Resumes task execution.
     *
     * @return void
     */
    public function resume();
}
