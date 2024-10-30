<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\EventsService;

abstract class ReceiverEventsService extends EventsService
{
    const CLASS_NAME = __CLASS__;

    public function getType()
    {
        return 'receiver';
    }
}
