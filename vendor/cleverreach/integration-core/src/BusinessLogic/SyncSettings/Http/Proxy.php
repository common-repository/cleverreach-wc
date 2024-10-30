<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\Http;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\DTO\SyncSettings;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Http\Proxy as BaseProxy;

class Proxy extends BaseProxy
{
    const CLASS_NAME = __CLASS__;

    /**
     * Updates sync settings.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\DTO\SyncSettings $settings
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function updateSyncSettings(SyncSettings $settings)
    {
        $this->post('oauth/settings.json', $settings->toArray());
    }
}
