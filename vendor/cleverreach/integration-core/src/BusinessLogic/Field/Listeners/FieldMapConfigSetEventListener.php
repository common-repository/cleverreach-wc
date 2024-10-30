<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Listeners;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO\FieldMap;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Events\FieldMapConfigSetEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SecondarySynchronization\Contracts\SecondarySyncEnqueueService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

class FieldMapConfigSetEventListener
{
    /**
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Events\FieldMapConfigSetEvent $event
     *
     * @return void
     */
    public function handle(FieldMapConfigSetEvent $event)
    {
        if ($this->areMapItemsAdded($event)) {
            $this->getSecondarySyncEnqueueService()->enqueueSecondarySync();
        }
    }

    /**
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Events\FieldMapConfigSetEvent $event
     *
     * @return bool
     */
    protected function areMapItemsAdded(FieldMapConfigSetEvent $event)
    {
        $previousMap = $this->createSourceDestinationMap($event->getPreviousFieldMapConfig());
        $newMap = $this->createSourceDestinationMap($event->getNewFieldMapConfig());

        $addedDstItems = array_diff($newMap, $previousMap);
        $addedSrcItems = array_diff_key($newMap, $previousMap);

        return !empty($addedDstItems) || !empty($addedSrcItems);
    }

    /**
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO\FieldMap $fieldMap
     *
     * @return array<string,string>
     */
    protected function createSourceDestinationMap(FieldMap $fieldMap)
    {
        $map = array();
        foreach ($fieldMap->getItems() as $fieldMapItem) {
            $map[$fieldMapItem->getSource()->getName()] = $fieldMapItem->getDestination()->getName();
        }

        return $map;
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
