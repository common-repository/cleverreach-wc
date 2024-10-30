<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Contracts;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO\Field;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO\FieldMap;

interface FieldMapConfigService
{
    const CLASS_NAME = __CLASS__;

    /**
     * Retrieves persisted field map configuration.
     *
     * @return FieldMap
     */
    public function get();

    /**
     * Persists field map configuration.
     *
     * @param FieldMap $fieldMap
     *
     * @return void
     */
    public function set(FieldMap $fieldMap);

    /**
     * Retrieves the source (integration) fields available for filed mapping. By default, all supported fields
     *
     * @return Field[]
     */
    public function getSourceFields();

    /**
     * Retrieves the destination (CleverReach) fields available for filed mapping.
     *
     * @return Field[]
     */
    public function getDestinationFields();
}
