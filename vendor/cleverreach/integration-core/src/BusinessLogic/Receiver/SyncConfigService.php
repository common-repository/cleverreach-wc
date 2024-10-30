<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Config\SyncService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigurationManager;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Singleton;

/**
 * Class SyncConfigService
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver
 */
class SyncConfigService extends Singleton implements Contracts\SyncConfigService
{
    /**
     * @var static
     */
    protected static $instance;
    /**
     * Retrieves enabled services.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Config\SyncService[]
     *
     * @noinspection PhpDocMissingThrowsInspection
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function getEnabledServices()
    {
        return SyncService::fromBatch($this->getConfigurationManager()->getConfigValue('enabledSyncServices', array()));
    }

    /**
     * Sets enabled services.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Config\SyncService[] $services
     *
     * @return void
     *
     * @noinspection PhpDocMissingThrowsInspection
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function setEnabledServices(array $services)
    {
        $persistFormat = array();

        foreach ($services as $service) {
            $persistFormat[] = $service->toArray();
        }

        $this->getConfigurationManager()->saveConfigValue('enabledSyncServices', $persistFormat);
    }

    /**
     * Retrieves configuration manager.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigurationManager Configuration Manager instance.
     */
    protected function getConfigurationManager()
    {
        /** @var ConfigurationManager $configurationManager */
        $configurationManager = ServiceRegister::getService(ConfigurationManager::CLASS_NAME);

        return $configurationManager;
    }
}
