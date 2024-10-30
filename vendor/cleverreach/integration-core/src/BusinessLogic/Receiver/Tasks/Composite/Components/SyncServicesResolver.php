<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Components;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Config\SyncService;

class SyncServicesResolver extends ReceiverSyncSubTask
{
    const CLASS_NAME = __CLASS__;

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $services = $this->getSyncConfigService()->getEnabledServices();

        $this->sortServices($services);

        $hashMap = array();
        foreach ($services as $service) {
            $hashMap[$service->getUuid()] = $service;
        }

        $this->getExecutionContext()->services = $hashMap;

        $this->reportProgress(100);
    }

    /**
     * Sorts array of SyncServices.
     *
     * @param SyncService[] $services
     *
     * @return void
     */
    private function sortServices(array &$services)
    {
        usort(
            $services,
            function (SyncService $a, SyncService $b) {
                if ($a->getPriority() === $b->getPriority()) {
                    return 0;
                }

                return $a->getPriority() > $b->getPriority() ? 1 : -1;
            }
        );
    }
}
