<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Contracts;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO\WebHook;

/**
 * Interface WebHookHandler
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Contracts
 */
interface WebHookHandler
{
    /**
     * Handles the web hook.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO\WebHook $hook
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Exceptions\UnableToHandleWebHookException
     */
    public function handle(WebHook $hook);
}
