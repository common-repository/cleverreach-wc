<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Events;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\Events\Event;

class ReceiverUnsubscribedEvent extends Event
{
    const CLASS_NAME = __CLASS__;
    /**
     * @var string
     */
    protected $receiverId;

    /**
     * ReceiverUnsubscribedEvent constructor.
     *
     * @param string $receiverId
     */
    public function __construct($receiverId)
    {
        $this->receiverId = $receiverId;
    }

    /**
     * @return string
     */
    public function getReceiverId()
    {
        return $this->receiverId;
    }
}
