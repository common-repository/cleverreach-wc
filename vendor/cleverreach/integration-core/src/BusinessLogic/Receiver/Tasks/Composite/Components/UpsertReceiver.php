<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Components;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Http\Proxy;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Context\SubscribtionStateChangedExecutionContext;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

class UpsertReceiver extends ReceiverSyncSubTask
{
    const CLASS_NAME = __CLASS__;

    /**
     * Sends changed receiver data to CleverReach.
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function execute()
    {
        /** @var SubscribtionStateChangedExecutionContext $context */
        $context = $this->getExecutionContext();

        if ($context->receiver !== null && $this->getProxy()->findReceiverByEmail($context->groupId, $context->email)) {
            $this->getReceiverProxy()->upsertPlus($context->groupId, array($context->receiver));
        }

        $this->reportProgress(100);
    }

    /**
     * Retrieves receiver proxy.
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
