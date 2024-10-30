<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Contracts;

/**
 * Interface ExecutionContextAware
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Contracts
 */
interface ExecutionContextAware
{
    /**
     * Sets provider that resolves current execution context.
     *
     * @param callable $provider Provider that resolves ExecutionContext
     * @see \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Context\ExecutionContext
     *      For the details of available context parameters.
     *
     * @return void
     */
    public function setExecutionContextProvider($provider);
}
