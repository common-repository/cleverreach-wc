<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Contracts\SyncConfigService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\Contracts\SyncSettingsService as BaseService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\Events\EnabledServicesSetEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\Events\SyncSettingsEventBus;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

abstract class SyncSettingsService implements BaseService
{
    /**
     * @inheritDoc
     */
    public function setEnabledServices(array $services)
    {
        $previousServices = $this->getSyncConfigService()->getEnabledServices();
        $this->getSyncConfigService()->setEnabledServices($services);

        SyncSettingsEventBus::getInstance()->fire(new EnabledServicesSetEvent($previousServices, $services));
    }

    /**
     * @inheritDoc
     */
    public function getEnabledServices()
    {
        return $this->getSyncConfigService()->getEnabledServices();
    }

    /**
     * Retrieves sync config service.
     *
     * @return SyncConfigService
     */
    private function getSyncConfigService()
    {
        /** @var SyncConfigService $syncConfigService */
        $syncConfigService = ServiceRegister::getService(SyncConfigService::CLASS_NAME);

        return $syncConfigService;
    }
}
