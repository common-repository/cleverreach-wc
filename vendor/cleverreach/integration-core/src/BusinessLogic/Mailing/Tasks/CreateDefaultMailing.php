<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Mailing\Tasks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Contracts\AuthorizationService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Contracts\GroupService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Mailing\Contracts\DefaultMailingService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Mailing\DTO\Mailing;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Mailing\Http\Proxy;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task;

/**
 * Class CreateDefaultMailing
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Mailing\Tasks
 */
class CreateDefaultMailing extends Task
{
    const CLASS_NAME = __CLASS__;

    /**
     * Creates default mailing.
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveUserInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function execute()
    {
        $hasMailing = $this->getProxy()->hasMailing();

        $this->reportProgress(20);

        if (!$hasMailing) {
            $this->getProxy()->createMailing($this->getDefaultMailing());
        }

        $this->reportProgress(100);
    }

    /**
     * Provides default mailing.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Mailing\DTO\Mailing
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveUserInfoException
     */
    protected function getDefaultMailing()
    {
        $service = $this->getDefaultMailingService();

        $mailing = new Mailing();
        $mailing->setName($service->getName());
        $mailing->setSubject($service->getSubject());

        // This option is disabled until the client specifies
        // Template suitable for usage in integrations.

        // $mailing->setContent($service->getContent());
        $userInfo = $this->getAuthService()->getUserInfo();
        $mailing->setSenderName($userInfo->getCompany() ?: $userInfo->getFirstName() ?: 'User');
        $mailing->setSenderEmail($userInfo->getEmail());

        return $mailing;
    }

    /**
     * Provides mailing proxy.
     *
     * @return Proxy
     */
    protected function getProxy()
    {
        /** @var Proxy $proxy */
        $proxy = ServiceRegister::getService(Proxy::CLASS_NAME);

        return $proxy;
    }

    /**
     * Provides default mailing service.
     *
     * @return DefaultMailingService
     */
    protected function getDefaultMailingService()
    {
        /** @var DefaultMailingService $defaultMailingService */
        $defaultMailingService = ServiceRegister::getService(DefaultMailingService::CLASS_NAME);

        return $defaultMailingService;
    }

    /**
     * Provides authorization service.
     *
     * @return AuthorizationService
     */
    protected function getAuthService()
    {
        /** @var AuthorizationService $authorizationService */
        $authorizationService = ServiceRegister::getService(AuthorizationService::CLASS_NAME);

        return $authorizationService;
    }

    /**
     * Provides group service.
     *
     * @return GroupService
     */
    protected function getGroupService()
    {
        /** @var GroupService $groupService */
        $groupService = ServiceRegister::getService(GroupService::CLASS_NAME);

        return $groupService;
    }
}
