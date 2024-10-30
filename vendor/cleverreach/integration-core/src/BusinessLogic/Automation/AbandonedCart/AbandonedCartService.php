<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Contracts\AbandonedCartService as BaseService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCart;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCartSubmit;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCartTrigger;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Exceptions\FailedToCreateAbandonedCartException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Exceptions\FailedToTriggerAbandonedCartException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Http\Proxy;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

abstract class AbandonedCartService implements BaseService
{
    /**
     * Creates abandoned cart.
     *
     * @param AbandonedCartSubmit $cartData
     *
     * @return AbandonedCart
     *
     */
    public function create(AbandonedCartSubmit $cartData)
    {
        try {
            $abandonedCart = $this->getProxy()->createAbandonedCartChain($cartData);
        } catch (\Exception $e) {
            throw new FailedToCreateAbandonedCartException($e->getMessage(), $e->getCode(), $e);
        }

        return $abandonedCart;
    }

    /**
     * Deletes abandoned cart.
     *
     * @NOTE NOT YET SUPPORTED BY THE API
     *
     * @param string $id
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Exceptions\FailedToDeleteAbandonedCartException
     */
    public function delete($id)
    {
        throw new \RuntimeException('Method not supported.');
    }

    /**
     * Enables abandoned cart.
     *
     * @NOTE NOT YET SUPPORTED BY THE API
     *
     * @param string $id
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Exceptions\FailedToEnableAbandonedCartException
     */
    public function enable($id)
    {
        throw new \RuntimeException('Method not supported.');
    }

    /**
     * Disables abandoned cart.
     *
     * @NOTE NOT YET SUPPORTED BY THE API
     *
     * @param string $id
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Exceptions\FailedToDisableAbandonedCartException
     */
    public function disable($id)
    {
        throw new \RuntimeException('Method not supported.');
    }

    /**
     * Triggers abandoned cart automation.
     *
     * @param AbandonedCartTrigger $trigger
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Exceptions\FailedToTriggerAbandonedCartException
     */
    public function trigger(AbandonedCartTrigger $trigger)
    {
        try {
            $this->getProxy()->triggerAbandonedCart($trigger);
        } catch (\Exception $e) {
            throw new FailedToTriggerAbandonedCartException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Retrieves automation proxy.
     *
     * @return Proxy
     */
    private function getProxy()
    {
        /** @var Proxy $proxy */
        $proxy = ServiceRegister::getService(Proxy::CLASS_NAME);

        return $proxy;
    }
}
