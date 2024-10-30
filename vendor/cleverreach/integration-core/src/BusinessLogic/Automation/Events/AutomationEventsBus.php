<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\Events;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\Events\EventBus;

class AutomationEventsBus extends EventBus
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
