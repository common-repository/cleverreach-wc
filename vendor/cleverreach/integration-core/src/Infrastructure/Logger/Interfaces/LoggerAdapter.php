<?php

namespace CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Interfaces;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogData;

/**
 * Interface LoggerAdapter.
 *
 * @package CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Interfaces
 */
interface LoggerAdapter
{
    /**
     * Log message in system
     *
     * @param LogData $data
     *
     * @return void
     */
    public function logMessage(LogData $data);
}
