<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\PaymentPlan\Http;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Http\Proxy as BaseProxy;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\PaymentPlan\DTO\PaymentPlan;

class Proxy extends BaseProxy
{
    const CLASS_NAME = __CLASS__;

    /**
     * Retrieves payment plan.
     *
     * @param string $clientId CleverReach client id. (Not integration client id).
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\PaymentPlan\DTO\PaymentPlan
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function getPaymentPlan($clientId)
    {
        $response = $this->get("clients.json/$clientId/plan");

        return PaymentPlan::fromArray($response->decodeBodyToArray());
    }

    /**
     * Returns the count of active receivers.
     *
     * @param string $clientId
     *
     * @return int
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function getActiveReceiversCount($clientId)
    {
        $response = $this->get("clients.json/$clientId/receivercount");

        return (int) $response->getBody();
    }
}
