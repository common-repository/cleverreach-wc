<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Interfaces\Required;

/**
 * Interface AutomationWebhooksService
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Interfaces
 */
interface AutomationWebhooksService
{
    const CLASS_NAME = __CLASS__;

    /**
     * Provides automation webhook url.
     *
     * @param mixed $automationId
     *
     * @return string
     */
    public function getWebhookUrl($automationId);
}
