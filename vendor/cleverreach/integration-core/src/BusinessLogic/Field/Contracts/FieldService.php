<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Contracts;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO\Field;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO\FieldConfigStatistics;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO\FieldMap;

/**
 * Interface FieldService
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Contracts
 */
interface FieldService
{
    const CLASS_NAME = __CLASS__;

    /**
     * Retrieve list of enabled fields that an integration supports.
     *
     * @return Field[]
     */
    public function getEnabledFields();

    /**
     * Persists list of enabled fields.
     *
     * @param Field[] $fields
     *
     * @return void
     */
    public function setEnabledFields(array $fields);

    /**
     * Retrieve statistics regarding count of available attributes for import and creation.
     *
     * @param Field[] $inputFields List of enabled fields
     * @param FieldMap $fieldMap Map with source and destination fields
     *
     * @return FieldConfigStatistics
     */
    public function getConfigStatistics(array $inputFields, FieldMap $fieldMap = null);

    /**
     * Retrieves lit of all supported fields.
     *
     * @return Field[]
     */
    public function getSupportedFields();
}
