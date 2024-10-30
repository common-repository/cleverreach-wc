<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Webhooks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Contracts\AbandonedCartEntityService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Contracts\AbandonedCartRecordService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCart;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\Events\AutomationActivatedEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\Events\AutomationDeactivatedEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\Events\AutomationDeletedEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\Events\AutomationEventsBus;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO\WebHook;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Exceptions\UnableToHandleWebHookException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

class Handler
{
    /**
     * List of supported events.
     *
     * @var string[]
     */
    protected static $supportedEvents = array(
        'automation.activated',
        'automation.deactivated',
        'automation.deleted',
    );

    /**
     * Handles automation related events.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO\WebHook $hook
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Exceptions\UnableToHandleWebHookException
     */
    public function handle(WebHook $hook)
    {
        $cart = $this->getEntityService()->get();

        if ($cart === null) {
            throw new UnableToHandleWebHookException('No automation chain is found in the database.');
        }

        $this->validateWebhook($hook, $cart);
        $this->handleWebhook($hook, $cart);
    }

    /**
     * Validates received webhook against persisted cart automation.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO\WebHook $hook
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCart $cart
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Exceptions\UnableToHandleWebHookException
     */
    protected function validateWebhook(WebHook $hook, AbandonedCart $cart)
    {
        if (!in_array($hook->getEvent(), static::$supportedEvents, true)) {
            throw new UnableToHandleWebHookException('Event [' . $hook->getEvent() . '] not supported.');
        }

        if ($cart->getId() !== $hook->getCondition()) {
            throw new UnableToHandleWebHookException(
                "Event not registered for automation chain [{$hook->getCondition()}]"
            );
        }
    }

    /**
     * Handles received webhook.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCart $cart
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO\WebHook $hook
     *
     * @return void
     */
    protected function handleWebhook(WebHook $hook, AbandonedCart $cart)
    {
        if ($hook->getEvent() === 'automation.deleted') {
            $this->deleteCart();
        } else {
            $this->updateCartStatus($cart, $hook->getEvent());
        }

        $this->fireEvents($hook);
    }

    /**
     * Updates cart status.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCart $cart
     * @param string $event
     *
     * @return void
     */
    protected function updateCartStatus(AbandonedCart $cart, $event)
    {
        $cart->setActive($event === 'automation.activated');
        $this->getEntityService()->set($cart);
    }

    /**
     * Deletes cart.
     *
     * @return void
     */
    protected function deleteCart()
    {
        $this->deleteCartData();
        $this->deleteRecords();
        $this->deleteAutomationEventData();
    }

    /**
     * Deletes cart data from the database.
     *
     * @return void
     */
    protected function deleteCartData()
    {
        $this->getEntityService()->set(null);
    }

    /**
     * Deletes all records with associated schedules.
     *
     * @return void
     */
    protected function deleteRecords()
    {
        $this->getRecordsService()->deleteAllRecords();
    }

    /**
     * Deletes automation event data.
     *
     * @return void
     */
    protected function deleteAutomationEventData()
    {
        $this->getEventsService()->setCallToken('');
        $this->getEventsService()->setSecret('');
        $this->getEventsService()->setVerificationToken('');
    }

    /**
     * Fires events that notify other systems that automation webhook has occurred.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO\WebHook $hook
     *
     * @return void
     */
    protected function fireEvents(WebHook $hook)
    {
        switch ($hook->getEvent()) {
            case 'automation.activated':
                AutomationEventsBus::getInstance()->fire(new AutomationActivatedEvent($hook));
                break;
            case 'automation.deactivated':
                AutomationEventsBus::getInstance()->fire(new AutomationDeactivatedEvent($hook));
                break;
            case 'automation.deleted':
                AutomationEventsBus::getInstance()->fire(new AutomationDeletedEvent($hook));
                break;
        }
    }

    /**
     * @return AbandonedCartEntityService
     */
    private function getEntityService()
    {
        /** @var AbandonedCartEntityService $abandonedCartEntityService */
        $abandonedCartEntityService = ServiceRegister::getService(AbandonedCartEntityService::CLASS_NAME);

        return $abandonedCartEntityService;
    }

    /**
     * Retrieves abandoned cart record service.
     *
     * @return AbandonedCartRecordService
     */
    private function getRecordsService()
    {
        /** @var AbandonedCartRecordService $abandonedCartRecordService */
        $abandonedCartRecordService = ServiceRegister::getService(AbandonedCartRecordService::CLASS_NAME);

        return $abandonedCartRecordService;
    }

    /**
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Webhooks\EventsService
     */
    private function getEventsService()
    {
        /** @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Webhooks\EventsService $eventsService */
        $eventsService = ServiceRegister::getService(EventsService::CLASS_NAME);

        return $eventsService;
    }
}
