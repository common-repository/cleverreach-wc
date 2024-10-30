<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject;

class FieldConfigStatistics extends DataTransferObject
{
    /**
     * @var int
     */
    protected $fieldsLeftToImport;
    /**
     * @var int
     */
    protected $fieldsLeftToCreate;
    /**
     * @var int
     */
    protected $newFieldsCount;

    /**
     * @param int $fieldsLeftToImport
     * @param int $fieldsLeftToCreate
     * @param int $newFieldsCount
     */
    public function __construct($fieldsLeftToImport, $fieldsLeftToCreate, $newFieldsCount)
    {
        $this->fieldsLeftToImport = $fieldsLeftToImport;
        $this->fieldsLeftToCreate = $fieldsLeftToCreate;
        $this->newFieldsCount = $newFieldsCount;
    }

    /**
     * @return int
     */
    public function getFieldsLeftToImport()
    {
        return $this->fieldsLeftToImport;
    }

    /**
     * @return int
     */
    public function getFieldsLeftToCreate()
    {
        return $this->fieldsLeftToCreate;
    }

    /**
     * @return int
     */
    public function getNewFieldsCount()
    {
        return $this->newFieldsCount;
    }

    /**
     * @inheritDoc
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject|static
     */
    public static function fromArray(array $data)
    {
        return new static(
            static::getDataValue($data, 'fieldsLeftToImport', 0),
            static::getDataValue($data, 'fieldsLeftToCreate', 0),
            static::getDataValue($data, 'newFieldsCount', 0)
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array(
            'fieldsLeftToImport' => $this->fieldsLeftToImport,
            'fieldsLeftToCreate' => $this->fieldsLeftToCreate,
            'newFieldsCount' => $this->newFieldsCount,
        );
    }
}
