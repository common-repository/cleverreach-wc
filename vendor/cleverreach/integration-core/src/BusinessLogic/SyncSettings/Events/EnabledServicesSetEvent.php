<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\Events;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Config\SyncService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\Events\Event;

/**
 * Class EnabledServicesSet
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\Events
 */
class EnabledServicesSetEvent extends Event
{
    const CLASS_NAME = __CLASS__;

    /**
     * @var SyncService[]
     */
    private $previousServices;
    /**
     * @var SyncService[]
     */
    private $newServices;

    /**
     * EnabledServicesSet constructor.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Config\SyncService[] $previousServices
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Config\SyncService[] $newServices
     */
    public function __construct(array $previousServices, array $newServices)
    {
        $this->previousServices = $previousServices;
        $this->newServices = $newServices;
    }

    /**
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Config\SyncService[]
     */
    public function getPreviousServices()
    {
        return $this->previousServices;
    }

    /**
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Config\SyncService[]
     */
    public function getNewServices()
    {
        return $this->newServices;
    }
}
