<?php

namespace CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Interfaces;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\ProcessStarterSaveException;

/**
 * Interface AsyncProcessStarter.
 *
 * @package CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Interfaces
 */
interface AsyncProcessService
{
    /**
     * Fully qualified name of this interface.
     */
    const CLASS_NAME = __CLASS__;

    /**
     * Starts given runner asynchronously (in new process/web request or similar)
     *
     * @param Runnable $runner Runner that should be started async
     *
     * @return void
     *
     * @throws ProcessStarterSaveException
     */
    public function start(Runnable $runner);

    /**
     * Runs a process with provided identifier.
     *
     * @param string $guid Identifier of process.
     * r
     * @return void
     */
    public function runProcess($guid);
}
