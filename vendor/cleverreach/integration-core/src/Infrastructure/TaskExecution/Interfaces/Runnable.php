<?php

namespace CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Interfaces;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Interfaces\Serializable;

/**
 * Interface Runnable.
 *
 * @package CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Interfaces
 */
interface Runnable extends Serializable
{
    /**
     * Starts runnable run logic
     *
     * @return void
     */
    public function run();
}
