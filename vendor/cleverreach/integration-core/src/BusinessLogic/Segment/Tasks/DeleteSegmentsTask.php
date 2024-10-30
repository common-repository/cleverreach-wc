<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Segment\Tasks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Segment\DTO\Segment;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Serializer;

/**
 * Class DeleteSegmentsTask
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Segment\Tasks
 */
class DeleteSegmentsTask extends SegmentTask
{
    /**
     * List of segment names that identify segments that must be deleted.
     *
     * @var string[]
     */
    protected $segmentNames;

    /**
     * DeleteSegmentsTask constructor.
     *
     * @param string[] $segmentNames
     */
    public function __construct(array $segmentNames)
    {
        $this->segmentNames = $segmentNames;
    }

    /**
     * @inheritdoc
     */
    public function getEqualityComponents()
    {
        return array('segmentNames' => $this->segmentNames);
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $serializedData)
    {
        return new self($serializedData['segmentNames']);
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array('segmentNames' => $this->segmentNames);
    }

    /**
     * @inheritDoc
     */
    public function serialize()
    {
        return Serializer::serialize(array('segmentNames' => $this->segmentNames));
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized)
    {
        $unserialized = Serializer::unserialize($serialized);
        $this->segmentNames = $unserialized['segmentNames'];
    }

    /**
     * Deletes segments identified by the segment name.
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function execute()
    {
        $groupId = $this->getGroupService()->getId();
        $segments = $this->getSegmentsByGroupId($groupId);
        $this->reportProgress(30);
        foreach ($segments as $name => $segmentGroup) {
            if (in_array($name, $this->segmentNames, true)) {
                $this->deleteSegments($groupId, $segmentGroup);
            }

            $this->reportAlive();
        }

        $this->reportProgress(100);
    }

    /**
     * Deletes list of segments.
     *
     * @param string $groupId
     * @param Segment[] $segments
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    protected function deleteSegments($groupId, array $segments)
    {
        $proxy = $this->getProxy();

        foreach ($segments as $segment) {
            $proxy->deleteSegment($groupId, $segment->getId());
        }
    }
}
