<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject;

class AbandonedCartSettings extends DataTransferObject
{
    /**
     * @var int
     */
    protected $delay;

    /**
     * AbandonedCartSettings constructor.
     *
     * @param int $delay
     */
    public function __construct($delay)
    {
        $this->delay = $delay;
    }

    /**
     * @return int
     */
    public function getDelay()
    {
        return $this->delay;
    }

    /**
     * @param int $delay
     *
     * @return void
     */
    public function setDelay($delay)
    {
        $this->delay = $delay;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array('delay' => $this->getDelay());
    }

    /**
     * Instantiates entity from an array.
     *
     * @inheritDoc
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCartSettings
     */
    public static function fromArray(array $data)
    {
        return new static(self::getDataValue($data, 'delay', 0));
    }
}
