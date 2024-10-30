<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Pipeline;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Contracts\AbandonedCartRecordService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCartTrigger;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Exceptions\FailedToPassFilterException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

class RecordFilter extends Filter
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
        $record = $this->getService()->get($trigger->getGroupId(), $trigger->getPoolId());

        if ($record !== null) {
            throw new FailedToPassFilterException(
                "Record already created for [{$trigger->getGroupId()}:{$trigger->getPoolId()}]."
            );
        }
    }

    /**
     * @return AbandonedCartRecordService
     */
    private function getService()
    {
        /** @var AbandonedCartRecordService $abandonedCartRecordService */
        $abandonedCartRecordService = ServiceRegister::getService(AbandonedCartRecordService::CLASS_NAME);

        return $abandonedCartRecordService;
    }
}
