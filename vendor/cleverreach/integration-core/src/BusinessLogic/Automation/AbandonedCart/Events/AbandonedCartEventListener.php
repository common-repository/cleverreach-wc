<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Events;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Contracts\AbandonedCartRecordService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Pipeline\AbandonedCartCreatePipeline;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogContextData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

class AbandonedCartEventListener
{
    /**
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Events\AbandonedCartEvent $event
     *
     * @return void
     */
    public function handle(AbandonedCartEvent $event)
    {
        try {
            AbandonedCartCreatePipeline::execute($event->getTrigger());
            $this->getService()->create($event->getTrigger());
        } catch (\Exception $e) {
            Logger::logWarning($e->getMessage(), 'Core', array(new LogContextData('trace', $e->getTraceAsString())));
        }
    }

    /**
     * Retrieves abandoned cart record service.
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
