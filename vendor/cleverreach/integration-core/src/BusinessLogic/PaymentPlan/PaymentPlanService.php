<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\PaymentPlan;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\PaymentPlan\Contracts\PaymentPlanService as BaseService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\PaymentPlan\Http\Proxy;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

class PaymentPlanService implements BaseService
{
    /**
     * Retrieves payment plan.
     *
     * @param string $clientId
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\PaymentPlan\DTO\PaymentPlan
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function getPlanInfo($clientId)
    {
        return $this->getProxy()->getPaymentPlan($clientId);
    }

    /**
     * Retrieves the count of active receivers.
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
        return $this->getProxy()->getActiveReceiversCount($clientId);
    }

    /**
     * Retrieves payment plan proxy.
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
