<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Events;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\Events\EventBus;

/**
 * Class AuthorizationEventBus
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Events
 */
class AuthorizationEventBus extends EventBus
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
