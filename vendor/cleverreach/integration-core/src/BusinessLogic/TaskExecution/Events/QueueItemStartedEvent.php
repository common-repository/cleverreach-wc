<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\TaskExecution\Events;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueItem;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\Events\Event;

/**
 * Class QueueItemStartedEvent
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\TaskExecution\Events
 */
class QueueItemStartedEvent extends Event
{
    const CLASS_NAME = __CLASS__;

    /**
     * @var QueueItem
     */
    protected $queueItem;

    /**
     * QueueItemStartedEvent constructor.
     *
     * @param QueueItem $queueItem
     */
    public function __construct(QueueItem $queueItem)
    {
        $this->queueItem = $queueItem;
    }

    /**
     * @return QueueItem
     */
    public function getQueueItem()
    {
        return $this->queueItem;
    }
}
