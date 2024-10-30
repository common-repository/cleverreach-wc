<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Components;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Contracts\GroupService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Contracts\ExecutionContextAware;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Contracts\SyncConfigService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Http\Proxy;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Merger\MergerRegistry;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Context\ExecutionContext;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Context\SubscribtionStateChangedExecutionContext;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task;

abstract class ReceiverSyncSubTask extends Task implements ExecutionContextAware
{
    /**
     * @var callable
     */
    private $executionContextProvider;

    /**
     * @inheritDoc
     */
    public function setExecutionContextProvider($provider)
    {
        $this->executionContextProvider = $provider;
    }

    /**
     * Retrieves current execution context.
     *
     * @return ExecutionContext | SubscribtionStateChangedExecutionContext
     */
    protected function getExecutionContext()
    {
        return call_user_func($this->executionContextProvider);
    }

    /**
     * Retrieves sync config service.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Contracts\SyncConfigService
     */
    protected function getSyncConfigService()
    {
        /** @var SyncConfigService $syncConfigService */
        $syncConfigService = ServiceRegister::getService(SyncConfigService::CLASS_NAME);

        return $syncConfigService;
    }

    /**
     * Retrieves merger.
     *
     * @param string $merger
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Merger\Merger
     */
    protected function getMerger($merger)
    {
        return MergerRegistry::get($merger);
    }

    /**
     * Retrieves group service instance.
     *
     * @return GroupService
     */
    protected function getGroupService()
    {
        /** @var GroupService $groupService */
        $groupService = ServiceRegister::getService(GroupService::CLASS_NAME);

        return $groupService;
    }

    /**
     * Returns receiver proxy.
     *
     * @return Proxy
     */
    protected function getReceiverProxy()
    {
        /** @var Proxy $receiverProxy */
        $receiverProxy = ServiceRegister::getService(Proxy::CLASS_NAME);

        return $receiverProxy;
    }

    /**
     * Retrieves specific receiver service.
     *
     * @param string $service
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Contracts\ReceiverService
     */
    protected function getReceiverService($service)
    {
        /** @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\ReceiverService $receiverService */
        $receiverService = ServiceRegister::getService($service);

        return $receiverService;
    }
}
