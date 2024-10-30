<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\DTO\Transformers;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Data\ReduceTransformer;

/**
 * Class SubmitTransformer
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\DTO\Transformers
 */
class SubmitTransformer extends ReduceTransformer
{
    /**
     * @inheritDoc
     */
    protected static function getAllowedKeys()
    {
        return array(
            'name',
            'url',
            'password',
            'type',
            'cors',
            'icon',
        );
    }
}
