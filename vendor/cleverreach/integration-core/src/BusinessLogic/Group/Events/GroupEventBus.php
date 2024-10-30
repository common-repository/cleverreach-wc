<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Events;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\Events\EventBus;

/**
 * Class GroupEventBus
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Events
 */
class GroupEventBus extends EventBus
{
    /**
     * Class name.
     */
    const CLASS_NAME = __CLASS__;

    /**
     * Singleton instance of this class.
     *
     * @var static
     */
    protected static $instance;
}
