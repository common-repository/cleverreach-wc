<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Tasks\SubTasks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Contracts\GroupService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Http\Proxy;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Tasks\Contracts\ExecutionContextAware;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task;

abstract class SubTask extends Task implements ExecutionContextAware
{
    /**
     * @var callable
     */
    private $contextProvider;

    /**
     * @param callable $provider
     *
     * @return void
     */
    public function setExecutionContextProvider(callable $provider)
    {
        $this->contextProvider = $provider;
    }

    /**
     * Retrieves execution context.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Tasks\Context\ExecutionContext
     */
    protected function getExecutionContext()
    {
        return call_user_func($this->contextProvider);
    }

    /**
     * Retrieves events service.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Contracts\EventsService
     */
    protected function getEventsService()
    {
        /** @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Contracts\EventsService $eventsService */
        $eventsService = ServiceRegister::getService($this->getExecutionContext()->getEventServiceClass());

        return $eventsService;
    }

    /**
     * Retrieves events proxy.
     *
     * @return Proxy
     */
    protected function getEventsProxy()
    {
        /** @var Proxy $proxy */
        $proxy = ServiceRegister::getService(Proxy::CLASS_NAME);

        return $proxy;
    }

    /**
     * Provides group service.
     *
     * @return GroupService
     */
    protected function getGroupService()
    {
        /** @var GroupService $groupService */
        $groupService = ServiceRegister::getService(GroupService::CLASS_NAME);

        return $groupService;
    }
}
