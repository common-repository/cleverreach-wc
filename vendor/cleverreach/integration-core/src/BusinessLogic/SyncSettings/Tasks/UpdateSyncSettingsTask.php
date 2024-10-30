<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\Tasks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\Contracts\SyncSettingsService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\DTO\SyncSettings;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\Http\Proxy;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task;

/**
 * Class UpdateSyncSettingsTask
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\Tasks
 */
class UpdateSyncSettingsTask extends Task
{
    const CLASS_NAME = __CLASS__;

    /**
     * Updates sync settings.
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function execute()
    {
        $synchronized = array();
        foreach ($this->getSyncSettingsService()->getEnabledServices() as $service) {
            $synchronized[] = $service->getUuid();
        }

        $this->reportProgress(30);

        $notSynchronized = array();
        foreach ($this->getSyncSettingsService()->getAvailableServices() as $service) {
            if (!in_array($service->getUuid(), $synchronized, true)) {
                $notSynchronized[] = $service->getUuid();
            }
        }

        $this->reportProgress(70);

        $postData = new SyncSettings($synchronized, $notSynchronized);
        $this->getProxy()->updateSyncSettings($postData);

        $this->reportProgress(100);
    }

    /**
     * Retrieves sync settings service.
     *
     * @return SyncSettingsService
     */
    private function getSyncSettingsService()
    {
        /** @var SyncSettingsService $syncSettingsService */
        $syncSettingsService = ServiceRegister::getService(SyncSettingsService::CLASS_NAME);

        return $syncSettingsService;
    }

    /**
     * Retrieves sync settings proxy.
     *
     * @return Proxy
     */
    private function getProxy()
    {
        /** @var Proxy $proxy */
        $proxy = ServiceRegister::getService(Proxy::CLASS_NAME);

        return $proxy;
    }
}
