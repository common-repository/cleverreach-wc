<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Blacklist\Transformers;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Data\ReduceTransformer;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject;

/**
 * Class SubmitTransformer
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Blacklist\Transformers
 */
class SubmitTransformer extends ReduceTransformer
{
    /**
     * @inheritDoc
     */
    public static function transform(DataTransferObject $transformable)
    {
        $data = parent::transform($transformable);
        static::trim($data);

        return $data;
    }

    /**
     * @inheritDoc
     */
    protected static function getAllowedKeys()
    {
        return array(
            'email',
            'comment',
        );
    }
}
