<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Webhooks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\EventsService as BaseService;

abstract class EventsService extends BaseService
{
    const CLASS_NAME = __CLASS__;

    /**
     * Provides event type. One of [form | receiver]
     *
     * @return string
     */
    public function getType()
    {
        return 'automation';
    }
}
