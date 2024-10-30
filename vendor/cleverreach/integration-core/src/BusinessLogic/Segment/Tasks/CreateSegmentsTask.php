<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Segment\Tasks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Segment\DTO\Segment;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Segment\Http\Proxy;

class CreateSegmentsTask extends SegmentTask
{
    const CLASS_NAME = __CLASS__;

    /**
     * Creates new segments or updates existing.
     *
     * Segments are identified by segment name, thus to use this one must assure that segment name is unique.
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function execute()
    {
        $groupId = $this->getGroupService()->getId();
        $proxy = $this->getProxy();

        $this->reportProgress(5);

        $segments = $this->getSegmentsByGroupId($groupId);
        $integrationSegments = $this->getSegmentService()->getSegments();

        $this->reportProgress(30);

        foreach ($integrationSegments as $integrationSegment) {
            $this->createSegment($integrationSegment, $segments, $proxy, $groupId);
            $this->reportAlive();
        }

        $this->reportProgress(100);
    }

    /**
     * Updates or creates segment.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Segment\DTO\Segment $integrationSegment
     * @param array<string,mixed> $segments
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Segment\Http\Proxy $proxy
     * @param string $groupId
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    protected function createSegment(
        Segment $integrationSegment,
        array &$segments,
        Proxy $proxy,
        $groupId
    ) {
        if (!array_key_exists($integrationSegment->getName(), $segments)) {
            $proxy->createSegment($groupId, $integrationSegment);
        }
    }
}
