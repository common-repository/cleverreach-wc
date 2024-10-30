<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Segment\Tasks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Contracts\GroupService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Segment\Contracts\SegmentService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Segment\Http\Proxy;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task;

/**
 * Class SegmentTask
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Segment\Tasks
 */
abstract class SegmentTask extends Task
{
    /**
     * Segment service.
     *
     * @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Segment\Contracts\SegmentService
     */
    protected $segmentService;
    /**
     * Group service.
     *
     * @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Contracts\GroupService
     */
    protected $groupService;
    /**
     * Segment proxy.
     *
     * @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Segment\Http\Proxy
     */
    protected $proxy;

    /**
     * Retrieves segments by group id.
     *
     * @param string $groupId
     *
     * @return array<string, \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Segment\DTO\Segment[]>
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    protected function getSegmentsByGroupId($groupId)
    {
        $result = array();
        $segments = $this->getProxy()->getSegments($groupId);

        foreach ($segments as $segment) {
            $result[$segment->getName()][] = $segment;
        }

        return $result;
    }

    /**
     * Retrieve segment service.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Segment\Contracts\SegmentService
     */
    protected function getSegmentService()
    {
        if ($this->segmentService === null) {
            /** @var SegmentService $segmentService */
            $segmentService = ServiceRegister::getService(SegmentService::CLASS_NAME);
            $this->segmentService = $segmentService;
        }

        return $this->segmentService;
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
