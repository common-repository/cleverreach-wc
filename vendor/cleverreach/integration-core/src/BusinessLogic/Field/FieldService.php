<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Contracts\FieldService as BaseService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Contracts\FieldType;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO\Field;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO\FieldConfigStatistics;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO\FieldMap;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Events\EnabledFieldsSetEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Events\FieldEventBus;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Exceptions\MaxNumberOfFieldsException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Http\Proxy;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Language\Translator;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigurationManager;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

abstract class FieldService implements BaseService
{
    const MAX_NUMBER_OF_FIELDS = 45;
    /**
     * List of supported fields map in format [name => type]
     *
     * @var array<string,string>
     */
    protected static $supportedFieldsMap = array(
        'salutation' => FieldType::TEXT,
        'title' => FieldType::TEXT,
        'firstname' => FieldType::TEXT,
        'lastname' => FieldType::TEXT,
        'street' => FieldType::TEXT,
        'zip' => FieldType::TEXT,
        'city' => FieldType::TEXT,
        'company' => FieldType::TEXT,
        'state' => FieldType::TEXT,
        'country' => FieldType::TEXT,
        'birthday' => FieldType::DATE,
        'phone' => FieldType::TEXT,
        'language' => FieldType::TEXT,
    );

    /**
     * Retrieve list of fields that an integration supports.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO\Field[]
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    public function getEnabledFields()
    {
        $enabledFieldsNames = $this->getSavedFieldNames();
        if (empty($enabledFieldsNames)) {
            return array();
        }

        $supportedFieldsMap = array();
        foreach ($this->getSupportedFields() as $field) {
            $supportedFieldsMap[$field->getName()] = $field;
        }

        $enabledFieldsMap = array_intersect_key($supportedFieldsMap, array_flip($enabledFieldsNames));

        return $this->createTranslatedFieldsList($enabledFieldsMap);
    }

    /**
     * @inheritDoc
     *
     * @param Field[] $fields
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    public function setEnabledFields(array $fields)
    {
        $this->validateInputFields($fields);

        $newFieldNames = array();
        foreach ($fields as $field) {
            $newFieldNames[] = $field->getName();
        }

        $previousSavedFieldNames = $this->getSavedFieldNames();
        $this->getConfigManager()->saveConfigValue('enabledFields', $newFieldNames);

        $event = new EnabledFieldsSetEvent($previousSavedFieldNames, $newFieldNames);
        FieldEventBus::getInstance()->fire($event);
    }

    /**
     * @param Field[] $inputFields
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO\FieldConfigStatistics
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function getConfigStatistics(array $inputFields, FieldMap $fieldMap = null)
    {
        $countFields = count($inputFields);
        $globalAttributes = $this->getGlobalAttributes();
        $diff = $this->getNewFields($inputFields, $globalAttributes, $fieldMap);

        return new FieldConfigStatistics(
            static::MAX_NUMBER_OF_FIELDS - $countFields,
            static::MAX_NUMBER_OF_FIELDS - count($globalAttributes),
            count($diff)
        );
    }

    /**
     * Retrieves lit of supported fields.
     *
     * @return Field[]
     */
    public function getSupportedFields()
    {
        $supportedFields = array();
        foreach (static::$supportedFieldsMap as $name => $type) {
            $field = new Field($name, $type);
            $field->setDescription($this->getFieldLabel($name));
            $supportedFields[] = $field;
        }

        return $supportedFields;
    }

    /**
     * Returns fields that will be created
     *
     * @param Field[] $inputFields
     * @param Field[] $globalAttributes
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO\FieldMap|null $fieldMap
     *
     * @return Field[]
     */
    protected function getNewFields(array $inputFields, array $globalAttributes, FieldMap $fieldMap = null)
    {
        $mappedFields = $fieldMap ?
            array_map(
                function ($fieldMapItem) {
                    return $fieldMapItem->getSource();
                },
                $fieldMap->getItems()
            ) :
            array();

        return array_udiff(
            $inputFields,
            $globalAttributes,
            $mappedFields,
            /**
             * @var Field $inputField
             * @var Field $globalAttribute
             */
            function ($inputField, $globalAttribute) {
                if ($inputField->getName() === $globalAttribute->getName()) {
                    return 0;
                }

                return ($inputField->getName() > $globalAttribute->getName()) ? 1 : -1;
            }
        );
    }

    /**
     * Creates list of fields DTOs
     *
     * @param array<string, Field> $fieldsMap map of fields in format [name => type]
     *
     * @return Field[]
     */
    protected function createTranslatedFieldsList(array $fieldsMap)
    {
        $fields = array();

        /**
         * @var string $name
         * @var Field $field
         */
        foreach ($fieldsMap as $name => $field) {
            if (array_key_exists($name, static::$supportedFieldsMap)) {
                $field->setDescription($this->getFieldLabel($name));
            }

            $fields[] = $field;
        }

        return $fields;
    }

    /**
     * @param Field[] $inputFields
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    protected function validateInputFields(array $inputFields)
    {
        $statistics = $this->getConfigStatistics($inputFields);
        if ($statistics->getFieldsLeftToCreate() < 0) {
            throw new MaxNumberOfFieldsException('Already created max number of fields', 404);
        }

        if ($statistics->getFieldsLeftToImport() < 0) {
            throw new MaxNumberOfFieldsException('Already selected max number of fields', 404);
        }

        if ($statistics->getNewFieldsCount() < 0) {
            throw new MaxNumberOfFieldsException('Number of new fields must be 0 or greater', 404);
        }
    }

    /**
     * Retrieves field label.
     *
     * @param string $fieldName Field identifier.
     *
     * @return string
     */
    protected function getFieldLabel($fieldName)
    {
        return Translator::translate($fieldName);
    }

    /**
     * @return string[]
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    protected function getSavedFieldNames()
    {
        return (array)($this->getConfigManager()->getConfigValue('enabledFields'));
    }

    /**
     * @return ConfigurationManager
     */
    protected function getConfigManager()
    {
        /** @var ConfigurationManager $configurationManager */
        $configurationManager = ServiceRegister::getService(ConfigurationManager::CLASS_NAME);

        return $configurationManager;
    }

    /**
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO\Field[]
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    protected function getGlobalAttributes()
    {
        /** @var Proxy $proxy */
        $proxy = ServiceRegister::getService(Proxy::CLASS_NAME);

        return $proxy->getGlobalFields();
    }
}
