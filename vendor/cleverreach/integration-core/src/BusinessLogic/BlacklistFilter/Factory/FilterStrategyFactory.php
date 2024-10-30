<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\Factory;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\Contracts\FilterStrategyType;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\DTO\BlacklistFilterConfig;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\FilterStrategy\StaticFilterStrategy;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\FilterStrategy\WildcardFilterStrategy;
use RuntimeException;

class FilterStrategyFactory
{
    /**
     * Create instance of filter strategy based on the type
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\DTO\BlacklistFilterConfig $config
     *
     * @return StaticFilterStrategy|WildcardFilterStrategy
     */
    public static function create(BlacklistFilterConfig $config)
    {
        switch ($config->getType()) {
            case FilterStrategyType::STATIC_TYPE:
                $instance = new StaticFilterStrategy($config->getRule());
                break;
            case FilterStrategyType::WILDCARD_TYPE:
                $instance = new WildcardFilterStrategy($config->getRule());
                break;
            default:
                throw new RuntimeException('Unknown filter strategy type.');
        }

        return $instance;
    }
}
