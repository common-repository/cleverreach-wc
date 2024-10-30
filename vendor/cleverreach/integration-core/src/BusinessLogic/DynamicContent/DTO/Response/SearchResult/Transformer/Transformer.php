<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\DTO\Response\SearchResult\Transformer;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\Transformer as BaseTransformer;

/**
 * Class Transformer
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\DTO\Response\Filter\SearchResult\Transformer
 */
class Transformer extends BaseTransformer
{
    /**
     * @inheritDoc
     */
    public static function transform(DataTransferObject $transformable)
    {
        $transformed = parent::transform($transformable);
        static::trim($transformed);

        return $transformed;
    }
}
