<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Events;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO\FieldMap;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\Events\Event;

class FieldMapConfigSetEvent extends Event
{
    const CLASS_NAME = __CLASS__;

    /**
     * @var FieldMap
     */
    protected $previousFieldMapConfig;
    /**
     * @var FieldMap
     */
    protected $newFieldMapConfig;

    /**
     * @param FieldMap $previousFieldMapConfig
     * @param FieldMap $newFieldMapConfig
     */
    public function __construct(FieldMap $previousFieldMapConfig, FieldMap $newFieldMapConfig)
    {
        $this->previousFieldMapConfig = $previousFieldMapConfig;
        $this->newFieldMapConfig = $newFieldMapConfig;
    }

    /**
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO\FieldMap
     */
    public function getPreviousFieldMapConfig()
    {
        return $this->previousFieldMapConfig;
    }

    /**
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO\FieldMap
     */
    public function getNewFieldMapConfig()
    {
        return $this->newFieldMapConfig;
    }
}
