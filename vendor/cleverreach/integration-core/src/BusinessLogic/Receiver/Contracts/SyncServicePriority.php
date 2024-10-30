<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Contracts;

/**
 * Interface SyncServicePriority
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Contracts
 */
interface SyncServicePriority
{
    const LOWEST = 1;
    const LOW = 10;
    const MEDIUM = 100;
    const HIGH = 1000;
    const HIGHEST = 10000;
}
