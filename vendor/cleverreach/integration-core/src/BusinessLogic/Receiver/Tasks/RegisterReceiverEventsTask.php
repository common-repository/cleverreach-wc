<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\ReceiverEventsService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Tasks\Context\ExecutionContext;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Tasks\RegisterEventTask;

class RegisterReceiverEventsTask extends RegisterEventTask
{
    const CLASS_NAME = __CLASS__;

    /**
     * RegisterReceiverEventsTask constructor.
     */
    public function __construct()
    {
        parent::__construct(new ExecutionContext(ReceiverEventsService::CLASS_NAME));
    }

    /**
     * @inheritDoc
     */
    protected static function createTask(array $tasks, $initialProgress)
    {
        return new static();
    }
}
