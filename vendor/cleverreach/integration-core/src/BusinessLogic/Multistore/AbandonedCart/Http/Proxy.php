<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Http;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Http\Proxy as BaseProxy;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\DTO\Trigger;

class Proxy extends BaseProxy
{
    const CLASS_NAME = __CLASS__;

    /**
     * Triggers abandoned cart automation.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\DTO\Trigger $trigger
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function trigger(Trigger $trigger)
    {
        $this->post('trigger/abandonedcart.json', $trigger->toArray());
    }
}
