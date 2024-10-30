<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject;

class FieldMapItem extends DataTransferObject
{
    /**
     * @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO\Field | null
     */
    protected $source;
    /**
     * @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO\Field | null
     */
    protected $destination;

    /**
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO\Field $source
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO\Field $destination
     */
    public function __construct(Field $source, Field $destination)
    {
        $this->source = $source;
        $this->destination = $destination;
    }

    /**
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO\Field
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO\Field
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array(
            'source' => $this->source ? $this->source->toArray() : null,
            'destination' => $this->destination ? $this->destination->toArray() : null,
        );
    }

    /**
     * @inheritDoc
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject|static
     */
    public static function fromArray(array $data)
    {
        return new static(
            Field::fromArray(static::getDataValue($data, 'source')),
            Field::fromArray(static::getDataValue($data, 'destination'))
        );
    }
}
