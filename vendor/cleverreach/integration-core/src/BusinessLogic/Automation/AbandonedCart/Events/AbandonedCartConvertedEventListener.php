<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Events;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Contracts\AbandonedCartRecordService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

class AbandonedCartConvertedEventListener
{
    /**
     * Deletes record for converted cart.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Events\AbandonedCartConvertedEvent $event
     *
     * @return void
     */
    public function handle(AbandonedCartConvertedEvent $event)
    {
        $record = $this->getService()->getByCartId($event->getTrigger()->getCartId());

        if ($record) {
            $this->getService()->delete($record->getGroupId(), $record->getPoolId());
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
