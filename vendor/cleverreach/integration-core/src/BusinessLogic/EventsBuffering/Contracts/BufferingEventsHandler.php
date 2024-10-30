<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Contracts;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Events\Event;

/**
 * Interface BufferingEventsHandler
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Contracts
 */
interface BufferingEventsHandler
{
    const CLASS_NAME = __CLASS__;

    /**
     * Handlers the incoming event or array of events. All events must belong to a receiver with a same email.
     *
     * @param Event|Event[] $event
     *
     * @return void
     */
    public function handle($event);
}
