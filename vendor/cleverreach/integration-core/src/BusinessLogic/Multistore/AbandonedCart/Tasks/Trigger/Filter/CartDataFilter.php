<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Tasks\Trigger\Filter;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\DTO\Trigger;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Entities\AutomationRecord;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToPassFilterException;

/**
 * Class CartDataFilter
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Tasks\Trigger\Filter
 */
class CartDataFilter extends Filter
{
    public function pass(AutomationRecord $record, Trigger $cartData)
    {
        if ($cartData->getCart()->getTotal() <= 0.0) {
            throw new FailedToPassFilterException('Total value of a cart must be greater than zero.');
        }
    }
}
