<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\FilterStrategy;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\Contracts\FilterStrategy;

abstract class AbstractFilterStrategy implements FilterStrategy
{
    /**
     * @var string
     */
    protected $rule;

    /**
     * @param string $rule
     */
    public function __construct($rule)
    {
        $this->rule = $rule;
    }
}
