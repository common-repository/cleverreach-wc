<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Tasks\Contracts;

/**
 * Interface ExecutionContextAware
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Tasks\Contracts
 */
interface ExecutionContextAware
{
    /**
     * @param callable $provider
     *
     * @return mixed
     */
    public function setExecutionContextProvider(callable $provider);
}
