<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Listeners;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Events\EnabledFieldsSetEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SecondarySynchronization\Contracts\SecondarySyncEnqueueService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

class EnabledFieldsSetEventListener
{
    /**
     * @param EnabledFieldsSetEvent $event
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    public function handle(EnabledFieldsSetEvent $event)
    {
        if ($this->areFieldsAdded($event)) {
            $this->getSecondarySyncEnqueueService()->enqueueSecondarySync();
        }
    }

    /**
     * @param EnabledFieldsSetEvent $event
     *
     * @return bool
     */
    protected function areFieldsAdded(EnabledFieldsSetEvent $event)
    {
        $oldFieldNames = $event->getPreviousEnabledFieldNames();
        $newFieldNames = $event->getNewEnabledFieldNames();

        $addedFields = array_diff($newFieldNames, $oldFieldNames);

        return !empty($addedFields);
    }

    /**
     * @return SecondarySyncEnqueueService
     */
    protected function getSecondarySyncEnqueueService()
    {
        /** @var SecondarySyncEnqueueService $secondarySyncEnqueueService */
        $secondarySyncEnqueueService = ServiceRegister::getService(SecondarySyncEnqueueService::CLASS_NAME);

        return $secondarySyncEnqueueService;
    }
}
