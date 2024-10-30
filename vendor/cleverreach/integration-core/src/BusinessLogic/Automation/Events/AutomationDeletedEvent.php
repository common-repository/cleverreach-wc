<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\Events;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO\WebHook;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\Events\Event;

class AutomationDeletedEvent extends Event
{
    /**
     * Fully qualified name of this class.
     */
    const CLASS_NAME = __CLASS__;
    /**
     * Webhook that resulted in firing of this event.
     *
     * @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO\WebHook $hook
     */
    protected $hook;

    /**
     * Automation deleted constructor.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO\WebHook $hook
     */
    public function __construct(WebHook $hook)
    {
        $this->hook = $hook;
    }

    /**
     * Retrieves webhook.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO\WebHook
     */
    public function getHook()
    {
        return $this->hook;
    }
}
