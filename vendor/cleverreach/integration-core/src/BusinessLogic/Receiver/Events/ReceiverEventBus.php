<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Events;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\Events\EventBus as BaseEventBus;

/**
 * Class ReceiverEventBus
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Events
 */
class ReceiverEventBus extends BaseEventBus
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
