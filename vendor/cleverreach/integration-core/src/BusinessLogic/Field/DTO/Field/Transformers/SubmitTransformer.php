<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO\Field\Transformers;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Data\ReduceTransformer;

/**
 * Class SubmitTransformer
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO\Field\Transformers
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
            'type',
            'group_id',
            'description',
            'preview_value',
            'default_value',
        );
    }
}
