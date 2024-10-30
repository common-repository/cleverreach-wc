<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\Events;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\Events\EventBus;

/**
 * Class SyncSettingsEventBus
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\Events
 */
class SyncSettingsEventBus extends EventBus
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
