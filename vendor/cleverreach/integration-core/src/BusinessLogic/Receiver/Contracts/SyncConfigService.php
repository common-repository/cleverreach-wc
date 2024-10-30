<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Contracts;

/**
 * Interface SyncConfigService
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Contracts
 */
interface SyncConfigService
{
    const CLASS_NAME = __CLASS__;

    /**
     * Retrieves enabled services.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Config\SyncService[]
     */
    public function getEnabledServices();

    /**
     * Sets enabled services.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Config\SyncService[] $services
     *
     * @return void
     */
    public function setEnabledServices(array $services);
}
