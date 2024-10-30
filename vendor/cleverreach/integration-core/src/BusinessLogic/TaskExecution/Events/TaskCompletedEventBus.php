<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\TaskExecution\Events;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\Events\EventBus;

/**
 * Class TaskCompletedEventBus
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\TaskExecution\Events
 */
class TaskCompletedEventBus extends EventBus
{
    /**
     * Class name.
     */
    const CLASS_NAME = __CLASS__;

    /**
     * Instance.
     *
     * @var static
     */
    protected static $instance;
}
