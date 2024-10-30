<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Tasks\Trigger;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Http\Proxy;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Interfaces\AutomationRecordService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Interfaces\AutomationRecordTrigger;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Interfaces\Required\CartAutomationTriggerService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Tasks\Trigger\Filter\FilterChain;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Scheduler\Interfaces\Schedulable;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Serializer;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task;

class TriggerCartAutomationTask extends Task implements Schedulable, AutomationRecordTrigger
{
    /**
     * @var int
     */
    protected $recordId;

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array('recordId' => $this->recordId);
    }

    /**
     * @inheritDoc
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Interfaces\Serializable
     *      Instance of serialized object.
     */
    public static function fromArray(array $array)
    {
        return new static($array['recordId']);
    }

    /**
     * @return int
     */
    public function getRecordId()
    {
        return $this->recordId;
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
        return Serializer::serialize($this->recordId);
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
        $this->recordId = Serializer::unserialize($serialized);
    }

    /**
     * TriggerCartAutomationTask constructor.
     *
     * @param int $recordId
     */
    public function __construct($recordId)
    {
        $this->recordId = $recordId;
    }

    /**
     * Triggers abandoned cart automation.
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException
     */
    public function execute()
    {
        $record = $this->getRecordService()->find($this->recordId);
        if ($record === null) {
            throw new AbortTaskExecutionException('Abandoned cart record is not found.');
        }

        $this->reportProgress(30);

        $trigger = $this->getCartAutomationTriggerService()->getTrigger($record->getCartId());
        if ($trigger === null) {
            throw new AbortTaskExecutionException("Abandoned cart trigger is not found for the provided Cart ID: {$record->getCartId()}");
        }

        $this->reportProgress(60);

        try {
            FilterChain::execute($record, $trigger);
        } catch (\Exception $e) {
            throw new AbortTaskExecutionException("An abandoned cart email has not been sent. Reason: {$e->getMessage()}.", $e->getCode(), $e);
        }

        $this->reportProgress(90);

        $this->getProxy()->trigger($trigger);

        $this->reportProgress(100);
    }

    /**
     * Provides automation record service.
     *
     * @return AutomationRecordService
     */
    private function getRecordService()
    {
        /** @var AutomationRecordService $automationRecordService */
        $automationRecordService = ServiceRegister::getService(AutomationRecordService::CLASS_NAME);

        return $automationRecordService;
    }

    /**
     * Provides automation trigger service.
     *
     * @return CartAutomationTriggerService
     */
    private function getCartAutomationTriggerService()
    {
        /** @var CartAutomationTriggerService $cartAutomationTriggerService */
        $cartAutomationTriggerService = ServiceRegister::getService(CartAutomationTriggerService::CLASS_NAME);

        return $cartAutomationTriggerService;
    }

    /**
     * Provides proxy class.
     *
     * @return Proxy
     */
    private function getProxy()
    {
        /** @var Proxy $proxy */
        $proxy = ServiceRegister::getService(Proxy::CLASS_NAME);

        return $proxy;
    }

    /**
     * Defines whether schedulable task can be enqueued for execution if there is already instance with queued status.
     *
     * @return bool False indicates that the schedulable task should not enqueued if there
     *      is already instance in queued status.
     */
    public function canHaveMultipleQueuedInstances()
    {
        return true;
    }
}
