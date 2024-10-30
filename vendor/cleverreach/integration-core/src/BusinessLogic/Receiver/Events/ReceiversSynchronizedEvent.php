<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Events;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\Events\Event;

/**
 * Class ReceiversSynchronizedEvent
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Events
 */
class ReceiversSynchronizedEvent extends Event
{
    /**
     * Fully qualified name of this class.
     */
    const CLASS_NAME = __CLASS__;
    /**
     * List of synchronized receiver emails.
     *
     * @var string[]
     */
    protected $receiverEmails;

    /**
     * ReceiversSynchronizedEvent constructor.
     *
     * @param string[] $receiverEmails
     */
    public function __construct(array $receiverEmails)
    {
        $this->receiverEmails = $receiverEmails;
    }

    /**
     * @return string[]
     */
    public function getReceiverEmails()
    {
        return $this->receiverEmails;
    }
}
