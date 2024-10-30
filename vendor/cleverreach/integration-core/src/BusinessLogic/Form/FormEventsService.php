<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\EventsService;

/**
 * Class FormEventsService
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form
 */
abstract class FormEventsService extends EventsService
{
    const CLASS_NAME = __CLASS__;

    /**
     * @inheritDoc
     */
    public function getType()
    {
        return 'form';
    }
}
