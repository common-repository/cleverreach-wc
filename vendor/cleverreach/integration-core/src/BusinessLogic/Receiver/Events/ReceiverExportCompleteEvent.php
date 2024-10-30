<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Events;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\Events\Event;

class ReceiverExportCompleteEvent extends Event
{
    const CLASS_NAME = __CLASS__;

    /** @var int */
    protected $synchronizedReceiversCount;

    /**
     * ReceiverExportCompleteEvent constructor.
     *
     * @param int $synchronizedReceiversCount
     */
    public function __construct($synchronizedReceiversCount)
    {
        $this->synchronizedReceiversCount = $synchronizedReceiversCount;
    }

    /**
     * @return int
     */
    public function getSynchronizedReceiversCount()
    {
        return $this->synchronizedReceiversCount;
    }
}
