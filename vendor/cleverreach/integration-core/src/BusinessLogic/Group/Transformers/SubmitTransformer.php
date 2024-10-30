<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Transformers;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Data\ReduceTransformer;

class SubmitTransformer extends ReduceTransformer
{
    /**
     * @inheritDoc
     */
    protected static function getAllowedKeys()
    {
        return array(
            'name',
            'locked',
            'backup',
            'receiver_info'
        );
    }
}
