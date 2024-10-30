<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Pipeline;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCartTrigger;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Exceptions\FailedToPassFilterException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Http\Proxy;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

class ReceiverFilter extends Filter
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
        if (($receiver = $this->getReceiver($trigger)) === null) {
            throw new FailedToPassFilterException(
                "Receiver not found [{$trigger->getGroupId()}:{$trigger->getPoolId()}]."
            );
        }

        if (!$this->isReceiverActive($receiver)) {
            throw new FailedToPassFilterException(
                "Receiver not active [{$trigger->getGroupId()}:{$trigger->getPoolId()}]."
            );
        }
    }

    /**
     * Retrieves receiver from proxy.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCartTrigger $trigger
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver|null
     */
    private function getReceiver(AbandonedCartTrigger $trigger)
    {
        try {
            $receiver = $this->getProxy()->getReceiver($trigger->getGroupId(), $trigger->getPoolId());
        } catch (\Exception $e) {
            $receiver = null;
        }

        return $receiver;
    }

    /**
     * Checks if receiver is active.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver $receiver
     *
     * @return bool
     */
    private function isReceiverActive(Receiver $receiver)
    {
        $deactivated = $receiver->getDeactivated();
        $activated = $receiver->getActivated();

        if (is_string($activated) || is_string($deactivated) || $activated === null) {
            return false;
        }

        return $deactivated === null || $activated->getTimestamp() > $deactivated->getTimestamp();
    }

    /**
     * Retrieves receiver proxy.
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
