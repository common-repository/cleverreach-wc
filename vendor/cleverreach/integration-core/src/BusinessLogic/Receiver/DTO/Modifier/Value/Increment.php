<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Modifier\Value;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Modifier\Modifier;

/**
 * Class Increment
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Modifier\Value
 */
class Increment extends Modifier
{
    /**
     * @inheritDoc
     */
    public function getType()
    {
        return '+';
    }
}
