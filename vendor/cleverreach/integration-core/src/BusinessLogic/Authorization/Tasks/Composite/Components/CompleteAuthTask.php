<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Tasks\Composite\Components;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Http\OauthStatusProxy;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task;

class CompleteAuthTask extends Task
{
    const CLASS_NAME = __CLASS__;

    /**
     * Completes oauth process.
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveUserInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function execute()
    {
        $this->getProxy()->finishOauth();
        $this->reportProgress(100);
    }

    /**
     * Retrieves oauth status proxy.
     *
     * @return OauthStatusProxy
     */
    private function getProxy()
    {
        /** @var OauthStatusProxy $proxy */
        $proxy = ServiceRegister::getService(OauthStatusProxy::CLASS_NAME);

        return $proxy;
    }
}
