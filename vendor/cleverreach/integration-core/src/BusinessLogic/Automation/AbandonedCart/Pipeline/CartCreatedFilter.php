<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Pipeline;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Contracts\AbandonedCartEntityService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCartTrigger;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Exceptions\FailedToPassFilterException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

class CartCreatedFilter extends Filter
{
    /**
     * Checks whether the cart is created or not.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCartTrigger $trigger
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Exceptions\FailedToPassFilterException
     */
    public function pass(AbandonedCartTrigger $trigger)
    {
        if ($this->getService()->get() === null) {
            throw new FailedToPassFilterException('Cart is not created.');
        }
    }

    /**
     * Retrieves abandoned cart service.
     *
     * @return AbandonedCartEntityService
     */
    private function getService()
    {
        /** @var AbandonedCartEntityService $abandonedCartEntityService */
        $abandonedCartEntityService = ServiceRegister::getService(AbandonedCartEntityService::CLASS_NAME);

        return $abandonedCartEntityService;
    }
}
