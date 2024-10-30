<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Webhooks\Tasks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Entities\CartAutomation;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Interfaces\CartAutomationService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Interfaces\Required\AutomationWebhooksService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Utility\Random\RandomString;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO\Event;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO\EventRegisterResult;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Http\Proxy;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Serializer;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task;

class RegisterWebhooksTask extends Task
{
    const CLASS_NAME = __CLASS__;
    const AUTOMATION_EVENT_TYPE = 'automation';
    /**
     * Automation id.
     *
     * @var int
     */
    protected $automationId;

    /**
     * RegisterWebhooksTask constructor.
     *
     * @param int $automationId
     */
    public function __construct($automationId)
    {
        $this->automationId = $automationId;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array('automationId' => $this->automationId);
    }

    /**
     * @inheritDoc
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Interfaces\Serializable
     *      Instance of serialized object.
     */
    public static function fromArray(array $array)
    {
        return new static($array['automationId']);
    }

    /**
     * String representation of object
     *
     * @link https://php.net/manual/en/serializable.serialize.php
     *
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return Serializer::serialize($this->automationId);
    }

    /**
     * Constructs the object
     *
     * @param string $serialized
     *
     * @return void
     */
    public function unserialize($serialized)
    {
        $this->automationId = Serializer::unserialize($serialized);
    }

    /**
     * Registers webhook.
     *
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToUpdateCartException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException
     */
    public function execute()
    {
        $automation = $this->getCartService()->find($this->automationId);
        if ($automation === null || !$automation->getCondition()) {
            throw new AbortTaskExecutionException("Invalid automation.");
        }

        $this->reportProgress(5);

        // Try to delete already registered event.
        try {
            $this->getProxy()->deleteEvent($automation->getCondition(), self::AUTOMATION_EVENT_TYPE);
        } catch (\Exception $e) {
            // Nothing to do here as event deletion is not necessary.
        }

        $this->reportProgress(70);

        $this->generateVerificationToken($automation);
        $event = $this->getEvent($automation);
        $registrationDetails = $this->getProxy()->registerEvent($event);
        $this->setCallToken($automation, $registrationDetails);

        $this->reportProgress(100);
    }

    /**
     * Provides cart automation service.
     *
     * @return CartAutomationService
     */
    protected function getCartService()
    {
        /** @var CartAutomationService $cartService */
        $cartService = ServiceRegister::getService(CartAutomationService::CLASS_NAME);

        return $cartService;
    }

    /**
     * Provides event proxy.
     *
     * @return Proxy
     */
    protected function getProxy()
    {
        /** @var Proxy $proxy */
        $proxy = ServiceRegister::getService(Proxy::CLASS_NAME);

        return $proxy;
    }

    /**
     * Generates verification token for cart automation.
     *
     * @param CartAutomation $automation
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToUpdateCartException
     */
    private function generateVerificationToken(CartAutomation $automation)
    {
        $automation->setWebhookVerificationToken(RandomString::generate());
        $this->getCartService()->update($automation);
    }

    /**
     * Provides cart automation event.
     *
     * @param CartAutomation $automation
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO\Event
     */
    private function getEvent(CartAutomation $automation)
    {
        $event = new Event();
        // Group id in this context is automation condition.
        $event->setGroupId($automation->getCondition());
        $event->setEvent(static::AUTOMATION_EVENT_TYPE);
        $event->setVerificationToken($automation->getWebhookVerificationToken());
        $event->setUrl($this->getWebhookService()->getWebhookUrl($automation->getId()));

        return $event;
    }

    /**
     * Provides automation webhooks service.
     *
     * @return AutomationWebhooksService
     */
    private function getWebhookService()
    {
        /** @var AutomationWebhooksService $automationWebhooksService */
        $automationWebhooksService = ServiceRegister::getService(AutomationWebhooksService::CLASS_NAME);

        return $automationWebhooksService;
    }

    /**
     * Sets call token.
     *
     * @param CartAutomation $automation
     * @param EventRegisterResult $registrationDetails
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToUpdateCartException
     */
    private function setCallToken(CartAutomation $automation, EventRegisterResult $registrationDetails)
    {
        $automation->setWebhookCallToken($registrationDetails->getCallToken());
        $this->getCartService()->update($automation);
    }
}
