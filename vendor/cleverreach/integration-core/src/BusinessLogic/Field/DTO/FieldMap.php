<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\Transformer;

class FieldMap extends DataTransferObject
{
    /**
     * @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO\FieldMapItem[]
     */
    protected $items;

    /**
     * @param FieldMapItem[] $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    /**
     * @return FieldMapItem[]
     */
    public function getItems()
    {
        return $this->items;
    }


    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return Transformer::batchTransform($this->items);
    }

    /**
     * @inheritDoc
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO\FieldMap
     */
    public static function fromArray(array $data)
    {
        return new static(FieldMapItem::fromBatch($data));
    }
}
