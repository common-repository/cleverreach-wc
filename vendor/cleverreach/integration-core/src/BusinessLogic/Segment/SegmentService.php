<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Segment;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Contracts\GroupService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Segment\DTO\Segment;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Segment\Http\Proxy;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class SegmentService
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Segment
 */
abstract class SegmentService implements Contracts\SegmentService
{
    /**
     * @var GroupService
     */
    protected $groupService;
    /**
     * @var Proxy
     */
    protected $proxy;

    /**
     * @param string $filter
     *
     * @return Segment|null
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function getSegment($filter)
    {
        $segments = $this->getProxy()->getSegments($this->getGroupService()->getId());
        foreach ($segments as $segment) {
            if ($segment->isConditionMatch($filter)) {
                return $segment;
            }
        }

        return null;
    }

    /**
     * Retrieve group service.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Contracts\GroupService
     */
    protected function getGroupService()
    {
        if ($this->groupService === null) {
            /** @var GroupService $groupService */
            $groupService = ServiceRegister::getService(GroupService::CLASS_NAME);
            $this->groupService = $groupService;
        }

        return $this->groupService;
    }

    /**
     * Retrieve proxy.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Segment\Http\Proxy
     */
    protected function getProxy()
    {
        if ($this->proxy === null) {
            /** @var Proxy $proxy */
            $proxy = ServiceRegister::getService(Proxy::CLASS_NAME);
            $this->proxy = $proxy;
        }

        return $this->proxy;
    }
}
