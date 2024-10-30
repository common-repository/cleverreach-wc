<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\WebHooks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Contracts\GroupService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Events\GroupDeletedEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Events\GroupEventBus;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Contracts\WebHookHandler;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO\WebHook;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Exceptions\UnableToHandleWebHookException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class Handler
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\WebHooks
 */
class Handler implements WebHookHandler
{
    const CLASS_NAME = __CLASS__;

    /**
     * @var string[]
     */
    protected static $supportedEvents = array(
        'group.deleted',
    );

    /**
     * @inheritDoc
     */
    public function handle(WebHook $hook)
    {
        if (!in_array($hook->getEvent(), static::$supportedEvents, true)) {
            throw new UnableToHandleWebHookException('Event [' . $hook->getEvent() . '] not supported.');
        }

        if ($hook->getCondition() !== $this->getGroupService()->getId()) {
            throw new UnableToHandleWebHookException('Invalid group id.');
        }

        $payload = $hook->getPayload();
        if (empty($payload['group_id'])) {
            throw new UnableToHandleWebHookException('Invalid payload.');
        }

        GroupEventBus::getInstance()->fire(new GroupDeletedEvent($payload['group_id']));
    }

    /**
     * @return GroupService
     */
    private function getGroupService()
    {
        /** @var GroupService $groupService */
        $groupService = ServiceRegister::getService(GroupService::CLASS_NAME);

        return $groupService;
    }
}
