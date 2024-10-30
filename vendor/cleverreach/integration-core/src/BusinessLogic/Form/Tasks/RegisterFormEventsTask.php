<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Tasks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\FormEventsService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Tasks\Context\ExecutionContext;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Tasks\RegisterEventTask;

class RegisterFormEventsTask extends RegisterEventTask
{
    const CLASS_NAME = __CLASS__;

    /**
     * RegisterFormEventsTask constructor.
     */
    public function __construct()
    {
        parent::__construct(new ExecutionContext(FormEventsService::CLASS_NAME));
    }

    protected static function createTask(array $tasks, $initialProgress)
    {
        return new static();
    }
}
