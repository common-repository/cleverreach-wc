<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO\FieldMap;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Contracts\FieldService as FieldServiceInterface;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Events\FieldMapConfigSetEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Events\FieldMapEventBuss;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Exceptions\DuplicatedDestinationFieldException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Exceptions\MaxNumberOfFieldsException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Http\Proxy;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigurationManager;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

class FieldMapConfigService implements Contracts\FieldMapConfigService
{
    /**
     * @inheritDoc
     */
    public function get()
    {
        $savedMap = $this->getConfigManager()->getConfigValue('fieldMap');

        return $savedMap ? FieldMap::fromArray($savedMap) : new FieldMap(array());
    }

    /**
     * @inheritDoc
     */
    public function set(FieldMap $fieldMap)
    {
        $this->validateMap($fieldMap);
        $previousMap = $this->get();
        $this->getConfigManager()->saveConfigValue('fieldMap', $fieldMap->toArray());

        $event = new FieldMapConfigSetEvent($previousMap, $fieldMap);
        FieldMapEventBuss::getInstance()->fire($event);
    }

    /**
     * @inheritDoc
     */
    public function getSourceFields()
    {
        return $this->getFieldService()->getSupportedFields();
    }

    /**
     * @inheritDoc
     */
    public function getDestinationFields()
    {
        return $this->getProxy()->getGlobalFields();
    }

    /**
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO\FieldMap $fieldMap
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Exceptions\MaxNumberOfFieldsException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Exceptions\DuplicatedDestinationFieldException
     */
    protected function validateMap(FieldMap $fieldMap)
    {
        $this->checkDuplicatedDestinationFields($fieldMap);
        $this->checkStatisticsParameters($fieldMap);
    }

    /**
     * @param FieldMap $fieldMap
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Exceptions\DuplicatedDestinationFieldException
     */
    protected function checkDuplicatedDestinationFields(FieldMap $fieldMap)
    {
        $destinationFieldNames = array_map(function ($mapItem) {
            return $mapItem->getDestination()->getName();
        }, $fieldMap->getItems());

        if (count(array_unique($destinationFieldNames)) < count($destinationFieldNames)) {
            throw new DuplicatedDestinationFieldException('Duplicates in the destination field names among all field items detected');
        }
    }

    /**
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO\FieldMap $fieldMap
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Exceptions\MaxNumberOfFieldsException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    protected function checkStatisticsParameters(FieldMap $fieldMap)
    {
        $enabledFields = $this->getFieldService()->getEnabledFields();
        $statistics = $this->getFieldService()->getConfigStatistics($enabledFields, $fieldMap);
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
     * @return ConfigurationManager
     */
    protected function getConfigManager()
    {
        /** @var ConfigurationManager $configurationManager */
        $configurationManager = ServiceRegister::getService(ConfigurationManager::CLASS_NAME);

        return $configurationManager;
    }

    /**
     * @return FieldService
     */
    protected function getFieldService()
    {
        /** @var FieldService $fieldService */
        $fieldService = ServiceRegister::getService(FieldServiceInterface::CLASS_NAME);

        return $fieldService;
    }

    /**
     * @return Proxy
     */
    protected function getProxy()
    {
        /** @var Proxy $proxy */
        $proxy = ServiceRegister::getService(Proxy::CLASS_NAME);

        return $proxy;
    }
}
