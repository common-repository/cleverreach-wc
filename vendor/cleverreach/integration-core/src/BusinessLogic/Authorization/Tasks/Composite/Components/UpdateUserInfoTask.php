<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Tasks\Composite\Components;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Contracts\AuthorizationService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Http\UserProxy;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Scheduler\ScheduledTask;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

class UpdateUserInfoTask extends ScheduledTask
{
    const CLASS_NAME = __CLASS__;

    /**
     * Updates user info.
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function execute()
    {
        $userInfo = $this->getProxy()->getUserInfo();

        $this->reportProgress(30);

        $userInfo->setLanguage($this->getProxy()->getUserLanguage($userInfo->getId()));

        $this->reportProgress(80);

        $this->getAuthService()->setUserInfo($userInfo);

        $this->reportProgress(100);
    }

    /**
     * Retrieves User Proxy.
     *
     * @return UserProxy
     */
    private function getProxy()
    {
        /** @var UserProxy $proxy */
        $proxy = ServiceRegister::getService(UserProxy::CLASS_NAME);

        return $proxy;
    }

    /**
     * Retrieves authorization service.
     *
     * @return AuthorizationService
     */
    private function getAuthService()
    {
        /** @var AuthorizationService $authService */
        $authService = ServiceRegister::getService(AuthorizationService::CLASS_NAME);

        return $authService;
    }
}
