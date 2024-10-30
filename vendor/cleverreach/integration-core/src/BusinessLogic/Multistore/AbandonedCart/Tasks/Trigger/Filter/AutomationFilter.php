<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Tasks\Trigger\Filter;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\DTO\Trigger;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Entities\AutomationRecord;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToPassFilterException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Interfaces\CartAutomationService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class AutomationFilter
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Tasks\Trigger\Filter
 */
class AutomationFilter extends Filter
{
    /**
     * Checks if the record and cart data satisfy necessary requirements
     * before the mail can be sent.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Entities\AutomationRecord $record
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\DTO\Trigger $cartData
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToPassFilterException
     *
     * @return void
     */
    public function pass(AutomationRecord $record, Trigger $cartData)
    {
        $automation = $this->getAutomationService()->find($record->getAutomationId());
        if ($automation === null || !$automation->isActive()) {
            throw new FailedToPassFilterException('Automation does not exist or is not active.');
        }
    }

    /**
     * Provides cart automation service.
     *
     * @return CartAutomationService
     */
    private function getAutomationService()
    {
        /** @var CartAutomationService $cartService */
        $cartService = ServiceRegister::getService(CartAutomationService::CLASS_NAME);

        return $cartService;
    }
}
