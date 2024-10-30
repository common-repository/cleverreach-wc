<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Stats;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Contracts\GroupService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Tag\Special\Subscriber;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Stats\Http\Proxy;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class StatsService
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Stats
 */
class StatsService implements Contracts\StatsService
{
    /**
     * @var Proxy
     */
    protected $proxy;
    /**
     * @var GroupService
     */
    protected $groupService;

    /**
     * @return int
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function getSubscribed()
    {
        /** @var Configuration $configService */
        $configService = ServiceRegister::getService(Configuration::CLASS_NAME);
        $tag = new Subscriber($configService->getIntegrationName());

        return (int)$this->getProxy()->countReceivers($tag, $this->getGroupService()->getId());
    }

    /**
     * @return int
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function getUnsubscribed()
    {
        $stats = $this->getProxy()->getStats($this->getGroupService()->getId());

        return (int)$stats->getInactiveReceiverCount();
    }

    /**
     * @return Proxy
     */
    protected function getProxy()
    {
        if (!$this->proxy) {
            /** @var Proxy $proxy */
            $proxy = ServiceRegister::getService(Proxy::CLASS_NAME);
            $this->proxy = $proxy;
        }

        return $this->proxy;
    }

    /**
     * @return GroupService
     */
    protected function getGroupService()
    {
        if (!$this->groupService) {
            /** @var GroupService $groupService */
            $groupService = ServiceRegister::getService(GroupService::CLASS_NAME);
            $this->groupService = $groupService;
        }

        return $this->groupService;
    }
}
