<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Tasks\Create\Subtasks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Entities\CartAutomation;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Interfaces\CartAutomationService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\DTO\AutomationSubmit;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\Http\Proxy;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Serializer;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task;

class CreateAutomation extends Task
{
    const CLASS_NAME = __CLASS__;

    /**
     * @var mixed
     */
    protected $automationId;

    /**
     * CreateAutomation constructor.
     *
     * @param mixed $automationId
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
     * Creates automation on the API.
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
        if ($automation === null) {
            throw new AbortTaskExecutionException("Invalid automation.");
        }

        $this->reportProgress(5);

        $automation->setStatus('creating');
        $this->getCartService()->update($automation);

        $this->reportProgress(20);
        $details = $this->createAutomation($automation);

        $this->reportProgress(70);

        $automation->setIsActive($details->isActive());
        $automation->setCondition($details->getId());

        $this->getCartService()->update($automation);

        $this->reportProgress(100);
    }

    /**
     * Creates automation on the API.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Entities\CartAutomation $automation
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\DTO\AutomationDetails
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    protected function createAutomation(CartAutomation $automation)
    {
        $submitData = new AutomationSubmit($automation->getName(), $automation->getStoreId());
        $submitData->setSource($automation->getSource());
        $submitData->setDescription($automation->getDescription());

        return $this->getAutomationProxy()->create($submitData);
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
     * Provides automation proxy.
     *
     * @return Proxy
     */
    protected function getAutomationProxy()
    {
        /** @var Proxy $proxy */
        $proxy = ServiceRegister::getService(Proxy::CLASS_NAME);

        return $proxy;
    }
}
