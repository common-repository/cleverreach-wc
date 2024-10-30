<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\WebHooks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Contracts\GroupService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Events\ReceiverCreatedEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Events\ReceiverDeletedEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Events\ReceiverEventBus;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Events\ReceiverSubscribedEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Events\ReceiverUnsubscribedEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Events\ReceiverUpdatedEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Http\Proxy;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO\WebHook;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Exceptions\UnableToHandleWebHookException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

class Handler
{
    /**
     * @var array<string,string>
     */
    protected static $supportedEvents = array(
        'receiver.created' => ReceiverCreatedEvent::CLASS_NAME,
        'receiver.updated' => ReceiverUpdatedEvent::CLASS_NAME,
        'receiver.subscribed' => ReceiverSubscribedEvent::CLASS_NAME,
        'receiver.unsubscribed' => ReceiverUnsubscribedEvent::CLASS_NAME,
    );

    /**
     * @var string
     */
    protected static $receiverDeletedEvent = 'receiver.deleted';

    /**
     * Handles receiver web hook event.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO\WebHook $hook
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Exceptions\UnableToHandleWebHookException
     */
    public function handle(WebHook $hook)
    {
        if ($hook->getEvent() === static::$receiverDeletedEvent) {
            return;
        }

        if (!array_key_exists($hook->getEvent(), static::$supportedEvents)) {
            throw new UnableToHandleWebHookException('Event [' . $hook->getEvent() . '] not supported.');
        }

        if ($hook->getCondition() !== $this->getGroupService()->getId()) {
            throw new UnableToHandleWebHookException('Invalid group id.');
        }

        $payload = $hook->getPayload();
        if (empty($payload['pool_id'])) {
            throw new UnableToHandleWebHookException('Invalid payload.');
        }

        try {
            $this->getProxy()->getReceiver($hook->getCondition(), $payload['pool_id']);
        } catch (\Exception $e) {
            throw new UnableToHandleWebHookException($e->getMessage(), $e->getCode(), $e->getPrevious());
        }

        $eventType = static::$supportedEvents[$hook->getEvent()];

        /** @var \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\Events\Event $event */
        $event = new $eventType($payload['pool_id']);
        ReceiverEventBus::getInstance()->fire($event);
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

    /**
     * @return Proxy
     */
    protected function getProxy()
    {
        /** @var Proxy $proxy */
        $proxy = ServiceRegister::getService(Proxy::CLASS_NAME);

        return $proxy;
    }
}
