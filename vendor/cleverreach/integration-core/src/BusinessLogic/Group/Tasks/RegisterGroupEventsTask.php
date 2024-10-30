<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Tasks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\GroupEventsService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Tasks\Context\ExecutionContext;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Tasks\RegisterEventTask;

/**
 * Class RegisterGroupEventsTask
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Tasks
 */
class RegisterGroupEventsTask extends RegisterEventTask
{
    const CLASS_NAME = __CLASS__;

    /**
     * RegisterGroupEventsTask constructor.
     */
    public function __construct()
    {
        parent::__construct(new ExecutionContext(GroupEventsService::CLASS_NAME));
    }

    protected static function createTask(array $tasks, $initialProgress)
    {
        return new static();
    }
}
