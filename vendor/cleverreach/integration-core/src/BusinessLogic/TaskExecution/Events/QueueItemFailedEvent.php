<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\TaskExecution\Events;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueItem;

/**
 * Class QueueItemFailedEvent
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\TaskExecution\Events
 */
class QueueItemFailedEvent extends BaseQueueItemEvent
{
    const CLASS_NAME = __CLASS__;

    /**
     * @var string
     */
    protected $failureDescription;

    /**
     * QueueItemFailedEvent constructor.
     *
     * @param QueueItem $queueItem
     * @param string $failureDescription
     */
    public function __construct(QueueItem $queueItem, $failureDescription)
    {
        parent::__construct($queueItem);
        $this->failureDescription = $failureDescription;
    }

    /**
     * @return string
     */
    public function getFailureDescription()
    {
        return $this->failureDescription;
    }
}
