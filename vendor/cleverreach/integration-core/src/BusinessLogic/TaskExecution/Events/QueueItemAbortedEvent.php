<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\TaskExecution\Events;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueItem;

/**
 * Class QueueItemAbortedEvent
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\TaskExecution\Events
 */
class QueueItemAbortedEvent extends BaseQueueItemEvent
{
    const CLASS_NAME = __CLASS__;

    /**
     * @var string
     */
    protected $abortDescription;

    /**
     * QueueItemAbortedEvent constructor.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueItem $queueItem
     * @param string $abortDescription
     */
    public function __construct(QueueItem $queueItem, $abortDescription)
    {
        parent::__construct($queueItem);
        $this->abortDescription = $abortDescription;
    }

    /**
     * @return string
     */
    public function getAbortDescription()
    {
        return $this->abortDescription;
    }
}
