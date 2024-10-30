<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Tasks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Contracts\FieldMapConfigService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Contracts\FieldService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO\Field;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Http\Proxy;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Serializer;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task;

class CreateFieldsTask extends Task
{
    const CLASS_NAME = __CLASS__;
    /**
     * Field proxy.
     *
     * @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Http\Proxy
     */
    protected $proxy;
    /**
     * Field service.
     *
     * @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Contracts\FieldService
     */
    protected $fieldService;
    /**
     * @var bool
     */
    private $doFieldUpdate;

    /**
     * @param bool $doFieldUpdate
     */
    public function __construct($doFieldUpdate = true)
    {
        $this->doFieldUpdate = $doFieldUpdate;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array('doFieldUpdate' => $this->doFieldUpdate);
    }

    /**
     * Transforms array into an serializable object,
     *
     * @inheritDoc
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Interfaces\Serializable
     *      Instance of serialized object.
     */
    public static function fromArray(array $array)
    {
        return new static($array['doFieldUpdate']);
    }

    /**
     * String representation of object
     *
     * @link https://php.net/manual/en/serializable.serialize.php
     *
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return Serializer::serialize($this->doFieldUpdate);
    }

    /**
     * Constructs the object
     *
     * @param string $serialized
     *
     * @return void
     */
    public function unserialize($serialized)
    {
        $this->doFieldUpdate = Serializer::unserialize($serialized);
    }

    /**
     * Creates or updates receiver fields.
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function execute()
    {
        $proxy = $this->getProxy();
        $existingFields = $proxy->getGlobalFields();
        $this->reportProgress(20);

        $existingFieldsMap = $this->createHashMap($existingFields);
        $this->reportProgress(30);

        $enabledFields = $this->getFieldService()->getEnabledFields();
        $mappedFieldNames = $this->getMappedSourceFieldNames();
        foreach ($enabledFields as $enabledField) {
            // If field is enabled but mapped to already existing global field, there is nothing we should do
            if (in_array($enabledField->getName(), $mappedFieldNames, true)) {
                continue;
            }

            if (!array_key_exists($enabledField->getName(), $existingFieldsMap)) {
                $proxy->createField($enabledField);
                continue;
            }

            if ($this->doFieldUpdate) {
                $existingField = $existingFieldsMap[$enabledField->getName()];
                $proxy->updateField($existingField->getId(), $enabledField);
            }
        }

        $this->reportProgress(100);
    }

    /**
     * Transforms list of fields to hash map where elements are identified by field name.
     *
     * @param Field[] $fields
     *
     * @return Field[]
     */
    protected function createHashMap(array $fields)
    {
        $result = array();

        foreach ($fields as $field) {
            $result[$field->getName()] = $field;
        }

        return $result;
    }

    /**
     * Retrieves Field proxy.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Http\Proxy
     */
    protected function getProxy()
    {
        if ($this->proxy === null) {
            /** @var Proxy $proxy */
            $proxy = ServiceRegister::getService(Proxy::CLASS_NAME);
            $this->proxy = $proxy;
        }

        return $this->proxy;
    }

    /**
     * @return string[]
     */
    protected function getMappedSourceFieldNames()
    {
        /** @var FieldMapConfigService $fieldMapConfigService */
        $fieldMapConfigService = ServiceRegister::getService(FieldMapConfigService::CLASS_NAME);
        $map = $fieldMapConfigService->get();

        return array_map(
            function ($item) {
                return $item->getSource()->getName();
            },
            $map->getItems()
        );
    }

    /**
     * Retrieves FieldService;
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Contracts\FieldService
     */
    protected function getFieldService()
    {
        if ($this->fieldService === null) {
            /** @var FieldService $fieldService */
            $fieldService = ServiceRegister::getService(FieldService::CLASS_NAME);
            $this->fieldService = $fieldService;
        }

        return $this->fieldService;
    }
}
