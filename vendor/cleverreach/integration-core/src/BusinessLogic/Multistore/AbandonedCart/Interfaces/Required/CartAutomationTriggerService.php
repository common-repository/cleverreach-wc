<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Interfaces\Required;

/**
 * Interface CartAutomationTriggerService
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Interfaces\Required
 */
interface CartAutomationTriggerService
{
    const CLASS_NAME = __CLASS__;

    /**
     * Provides trigger data for specified cart identified by cart id.
     *
     * @param mixed $cartId
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\DTO\Trigger | null
     */
    public function getTrigger($cartId);
}
