<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Events;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\Events\Event;

class EnabledFieldsSetEvent extends Event
{
    const CLASS_NAME = __CLASS__;

    /**
     * @var string[]
     */
    protected $previousEnabledFieldNames;
    /**
     * @var string[]
     */
    protected $newEnabledFieldNames;

    /**
     * @param string[] $previousEnabledFieldNames
     * @param string[] $newEnabledFieldNames
     */
    public function __construct(array $previousEnabledFieldNames, array $newEnabledFieldNames)
    {
        $this->previousEnabledFieldNames = $previousEnabledFieldNames;
        $this->newEnabledFieldNames = $newEnabledFieldNames;
    }

    /**
     * @return string[]
     */
    public function getPreviousEnabledFieldNames()
    {
        return $this->previousEnabledFieldNames;
    }

    /**
     * @return string[]
     */
    public function getNewEnabledFieldNames()
    {
        return $this->newEnabledFieldNames;
    }
}
