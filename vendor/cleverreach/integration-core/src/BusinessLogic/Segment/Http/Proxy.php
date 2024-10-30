<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Segment\Http;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Http\Proxy as BaseProxy;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Segment\DTO\Segment;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Segment\DTO\Segment\Transofrmers\SubmitTransformer;

/**
 * Class Proxy
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Segment\Http
 */
class Proxy extends BaseProxy
{
    const CLASS_NAME = __CLASS__;

    /**
     * Retrieves all segments in a group.
     *
     * @param string $groupId Group identifier that will be used to retrieve segments.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Segment\DTO\Segment[]
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function getSegments($groupId)
    {
        $response = $this->get("groups.json/$groupId/filters");

        return Segment::fromBatch($response->decodeBodyToArray());
    }

    /**
     * Creates segment in a receiver group.
     *
     * @param string $groupId Group identifier that will be used when creating segments
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Segment\DTO\Segment $segment Segment data.
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function createSegment($groupId, Segment $segment)
    {
        $this->post("groups.json/$groupId/filters", SubmitTransformer::transform($segment));
    }

    /**
     * Updates segment.
     *
     * @param string $groupId
     * @param string $segmentId
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Segment\DTO\Segment $segment
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function updateSegment($groupId, $segmentId, Segment $segment)
    {
        $this->put("groups.json/$groupId/filters/$segmentId", SubmitTransformer::transform($segment));
    }

    /**
     * Deletes segment in a group identified by segment id.
     *
     * @param string $groupId Group identifier.
     * @param string $segmentId Segment identifier.
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function deleteSegment($groupId, $segmentId)
    {
        $this->delete("groups.json/$groupId/filters/$segmentId");
    }
}
