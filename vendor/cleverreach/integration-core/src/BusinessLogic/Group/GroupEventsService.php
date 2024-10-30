<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\EventsService;

/**
 * Class GroupEventsService
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group
 */
abstract class GroupEventsService extends EventsService
{
    const CLASS_NAME = __CLASS__;

    /**
     * @inheritDoc
     */
    public function getType()
    {
        return 'group';
    }
}
