<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Data;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\Transformer as BaseTransformer;

class ReduceTransformer extends BaseTransformer
{
    /**
     * @inheritDoc
     */
    public static function transform(DataTransferObject $transformable)
    {
        $result = $transformable->toArray();

        return array_intersect_key($result, array_flip(static::getAllowedKeys()));
    }

    /**
     * Retrieves a list of submittable keys for Field DTO.
     *
     * @return string[] List of submittable keys.
     */
    protected static function getAllowedKeys()
    {
        return array();
    }
}
