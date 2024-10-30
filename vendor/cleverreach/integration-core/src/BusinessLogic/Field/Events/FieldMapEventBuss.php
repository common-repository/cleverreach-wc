<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Events;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\Events\EventBus;

class FieldMapEventBuss extends EventBus
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
