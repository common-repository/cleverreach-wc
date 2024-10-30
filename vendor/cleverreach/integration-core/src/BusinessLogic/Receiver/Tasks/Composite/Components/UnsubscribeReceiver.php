<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Components;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Context\SubscribtionStateChangedExecutionContext;

class UnsubscribeReceiver extends ReceiverSyncSubTask
{
    const CLASS_NAME = __CLASS__;

    /**
     * Unsubscribes receiver.
     */
    public function execute()
    {
        /** @var SubscribtionStateChangedExecutionContext $context */
        $context = $this->getExecutionContext();

        if ($context->receiver === null) {
            $this->reportProgress(100);

            return;
        }

        $this->reportProgress(5);

        foreach ($context->services as $service) {
            $receiverService = $this->getReceiverService($service->getService());
            $receiverFromService = $receiverService->getReceiver($context->email);
            if ($receiverFromService) {
                $this->getMerger($service->getMerger())->merge($receiverFromService, $context->receiver);
            }

            $receiverService->unsubscribe($context->receiver);
            /** @noinspection DisconnectedForeachInstructionInspection */
            $this->reportAlive();
        }

        $this->reportProgress(80);

        $context->receiver->setActivated('0');

        $this->reportProgress(100);
    }
}
