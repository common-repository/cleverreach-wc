<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Pipeline;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCartTrigger;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Exceptions\FailedToPassFilterException;

class CartFilter extends Filter
{
    /**
     * Checks whether trigger can pass the filter.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCartTrigger $trigger
     *
     * @return void
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Exceptions\FailedToPassFilterException
     *
     */
    public function pass(AbandonedCartTrigger $trigger)
    {
        if ($trigger->getAbandonedCartData()->getTotal() <= 0) {
            throw new FailedToPassFilterException(
                "Cart {$trigger->getAbandonedCartData()->getTotal()} has insufficient value"
            );
        }
    }
}
