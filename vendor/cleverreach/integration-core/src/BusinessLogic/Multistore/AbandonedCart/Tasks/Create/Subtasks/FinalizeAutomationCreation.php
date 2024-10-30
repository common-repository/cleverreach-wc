<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Tasks\Create\Subtasks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Interfaces\CartAutomationService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Serializer;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task;

/**
 * Class FinalizeAutomationCreation
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Tasks\Create\Subtasks
 */
class FinalizeAutomationCreation extends Task
{
    const CLASS_NAME = __CLASS__;
    /**
     * @var int
     */
    protected $automationId;

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
     * FinalizeAutomationCreation constructor.
     *
     * @param int $automationId
     */
    public function __construct($automationId)
    {
        $this->automationId = $automationId;
    }

    /**
     * Sets the status that denotes whether the automation is successfully created or not.
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToUpdateCartException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException
     */
    public function execute()
    {
        $automation = $this->getCartService()->find($this->automationId);
        if ($automation === null || !$automation->getCondition()) {
            throw new AbortTaskExecutionException("Invalid automation.");
        }

        $this->reportProgress(40);

        $webhookCallToken = $automation->getWebhookCallToken();

        $status = !empty($webhookCallToken) ? 'created' : 'incomplete';
        $automation->setStatus($status);
        $this->getCartService()->update($automation);

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
}
