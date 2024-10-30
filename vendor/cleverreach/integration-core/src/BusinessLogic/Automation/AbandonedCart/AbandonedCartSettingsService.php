<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Contracts\AbandonedCartSettingsService as BaseService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCartSettings;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigurationManager;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

class AbandonedCartSettingsService implements BaseService
{
    /**
     * Persists settings.
     *
     * @param AbandonedCartSettings|null $settings
     *
     * @return void
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function set(AbandonedCartSettings $settings = null)
    {
        $data = $settings ? $settings->toArray() : null;

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->getConfigManager()->saveConfigValue('abandonedCartSettings', $data);
    }

    /**
     * Retrieves persisted settings.
     *
     * @return AbandonedCartSettings|null
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function get()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $settings = $this->getConfigManager()->getConfigValue('abandonedCartSettings');
        if ($settings !== null) {
            $settings = AbandonedCartSettings::fromArray($settings);
        }

        return $settings;
    }

    /**
     * @return ConfigurationManager
     */
    private function getConfigManager()
    {
        /** @var ConfigurationManager $configurationManager */
        $configurationManager = ServiceRegister::getService(ConfigurationManager::CLASS_NAME);

        return $configurationManager;
    }
}
