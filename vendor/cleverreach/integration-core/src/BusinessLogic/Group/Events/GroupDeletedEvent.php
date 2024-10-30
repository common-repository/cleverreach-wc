<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Events;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\Events\Event;

/**
 * Class GroupDeletedEvent
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Events
 */
class GroupDeletedEvent extends Event
{
    const CLASS_NAME = __CLASS__;
    /**
     * @var string
     */
    protected $groupId;

    /**
     * GroupDeletedEvent constructor.
     *
     * @param string $groupId
     */
    public function __construct($groupId)
    {
        $this->groupId = $groupId;
    }

    /**
     * @return string
     */
    public function getGroupId()
    {
        return $this->groupId;
    }
}
