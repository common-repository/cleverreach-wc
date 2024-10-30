<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Modifier\Value;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Modifier\Modifier;

class UnsetModifier extends Modifier
{
    /**
     * @inheritDoc
     * @param string $field
     */
    public function __construct($field)
    {
        parent::__construct($field, null);
    }
}
