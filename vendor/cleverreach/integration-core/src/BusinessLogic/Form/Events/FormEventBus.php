<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Events;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\Events\EventBus;

/**
 * Class FormEventBus
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Events
 */
class FormEventBus extends EventBus
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
