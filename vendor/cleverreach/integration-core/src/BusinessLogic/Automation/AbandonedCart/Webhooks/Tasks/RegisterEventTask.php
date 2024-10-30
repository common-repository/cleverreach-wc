<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Webhooks\Tasks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Webhooks\EventsService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Tasks\Context\ExecutionContext;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Tasks\RegisterEventTask as BaseTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Tasks\SubTasks\EventRegistrationResultRecorder;

class RegisterEventTask extends BaseTask
{
    const CLASS_NAME = __CLASS__;

    /**
     * RegisterReceiverEventsTask constructor.
     */
    public function __construct()
    {
        parent::__construct(new ExecutionContext(EventsService::CLASS_NAME));
    }

    /**
     * @inheritDoc
     */
    protected static function createTask(array $tasks, $initialProgress)
    {
        return new static();
    }

    /**
     * @inheritDoc
     */
    protected function getSubTasks()
    {
        return array(
            EventProvider::CLASS_NAME => 10,
            EventDeleter::CLASS_NAME => 30,
            EventRegistrator::CLASS_NAME => 40,
            EventRegistrationResultRecorder::CLASS_NAME => 20,
        );
    }
}
