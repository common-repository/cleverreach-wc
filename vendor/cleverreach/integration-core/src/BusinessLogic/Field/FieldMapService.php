<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO\FieldMap;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Modifier\Modifier;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver;

class FieldMapService implements Contracts\FieldMapService
{
    /**
     * @inheritDoc
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO\FieldMap $fieldMap
     * @param Receiver[] $receivers
     */
    public function applyMapping(FieldMap $fieldMap, array &$receivers)
    {
        foreach ($receivers as $receiver) {
            $this->modifyReceiver($receiver, $fieldMap);
        }
    }

    /**
     * @param Receiver $receiver
     * @param FieldMap $fieldMap
     *
     * @return void
     */
    protected function modifyReceiver(Receiver $receiver, FieldMap $fieldMap)
    {
        foreach ($fieldMap->getItems() as $mapItem) {
            $receiver->addModifier(new Modifier(
                $mapItem->getDestination()->getName(),
                $receiver->getGlobalAttribute($mapItem->getSource()->getName())
            ));
        }
    }
}
