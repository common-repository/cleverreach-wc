<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Events;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Contracts\AbandonedCartRecordService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

class AbandonedCartUpdatedEventListener
{
    /**
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Events\AbandonedCartUpdatedEvent $event
     *
     * @return void
     */
    public function handle(AbandonedCartUpdatedEvent $event)
    {
        $record = $this->getService()->getByCartId($event->getTrigger()->getCartId());

        if ($record) {
            $record->setCartId($event->getTrigger()->getCartId());
            $record->setGroupId($event->getTrigger()->getGroupId());
            $record->setPoolId($event->getTrigger()->getPoolId());
            $record->setEmail($event->getTrigger()->getPoolId());
            $record->setTrigger($event->getTrigger());
            $record->setCustomerId($event->getTrigger()->getCustomerId());
            $this->getService()->update($record);
        }
    }

    /**
     * Retrieves record service.
     *
     * @return AbandonedCartRecordService
     */
    private function getService()
    {
        /** @var AbandonedCartRecordService $abandonedCartRecordService */
        $abandonedCartRecordService = ServiceRegister::getService(AbandonedCartRecordService::CLASS_NAME);

        return $abandonedCartRecordService;
    }
}
