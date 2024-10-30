<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Segment\DTO\Segment\Transofrmers;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Data\ReduceTransformer;

/**
 * Class SubmitTransformer
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Segment\DTO\Segment\Transofrmers
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
            'rules',
        );
    }
}
