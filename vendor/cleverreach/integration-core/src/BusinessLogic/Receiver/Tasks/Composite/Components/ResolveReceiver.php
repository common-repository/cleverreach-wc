<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Components;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\Contracts\BlacklistFilterService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Context\SubscribtionStateChangedExecutionContext;

class ResolveReceiver extends ReceiverSyncSubTask
{
    const CLASS_NAME = __CLASS__;

    /**
     * Prepares receiver for subscription state change (subscribe / unsubscribe receiver task).
     */
    public function execute()
    {
        /** @var SubscribtionStateChangedExecutionContext $context */
        $context = $this->getExecutionContext();
        $context->email = $this->getBlacklistEmailService()->filterEmail($context->email);

        if (empty($context->email)) {
            $this->reportProgress(100);

            return;
        }

        $receiver = new Receiver();
        $receiver->setEmail($context->email);
        $context->receiver = $receiver;

        $this->reportProgress(100);
    }

    /**
     * @return BlacklistFilterService
     */
    protected function getBlacklistEmailService()
    {
        /** @var BlacklistFilterService $blacklistFilterService */
        $blacklistFilterService =  ServiceRegister::getService(BlacklistFilterService::CLASS_NAME);

        return $blacklistFilterService;
    }
}
