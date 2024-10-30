<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Contracts;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO\FieldMap;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver;

interface FieldMapService
{
    const CLASS_NAME = __CLASS__;

    /**
     * Applies mapping specified in the field map to the list of receivers
     *
     * @param FieldMap $fieldMap
     * @param Receiver[] $receivers
     *
     * @return void
     */
    public function applyMapping(FieldMap $fieldMap, array &$receivers);
}
