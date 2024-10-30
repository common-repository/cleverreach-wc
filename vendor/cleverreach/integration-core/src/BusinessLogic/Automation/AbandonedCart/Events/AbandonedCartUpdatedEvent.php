<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Events;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCartTrigger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\Events\Event;

class AbandonedCartUpdatedEvent extends Event
{
    const CLASS_NAME = __CLASS__;
    /**
     * @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCartTrigger
     */
    private $trigger;

    /**
     * AbandonedCartUpdatedEvent constructor.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCartTrigger $trigger
     */
    public function __construct(AbandonedCartTrigger $trigger)
    {
        $this->trigger = $trigger;
    }

    /**
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCartTrigger
     */
    public function getTrigger()
    {
        return $this->trigger;
    }
}
