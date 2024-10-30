<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Contracts\AbandonedCartEntityService as BaseService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCart;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigurationManager;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

class AbandonedCartEntityService implements BaseService
{
    /**
     * Persists abandoned cart automation information.
     *
     * @param AbandonedCart|null $data
     *
     * @return void
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function set(AbandonedCart $data = null)
    {
        $data = $data ? $data->toArray() : null;

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->getConfigManager()->saveConfigValue('abandonedCart', $data);
    }

    /**
     * Retrieves abandoned cart persisted automation information.
     *
     * @return AbandonedCart|null
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function get()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $cart = $this->getConfigManager()->getConfigValue('abandonedCart');
        if ($cart !== null) {
            $cart = AbandonedCart::fromArray($cart);
        }

        return $cart;
    }

    /**
     * Persists the store id used when creating the automation chain.
     *
     * @param string $id
     *
     * @return void
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function setStoreId($id)
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->getConfigManager()->saveConfigValue('abandonedCartStoreId', $id);
    }
    /**
     * Retrieves store id.
     *
     * @return string
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function getStoreId()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->getConfigManager()->getConfigValue('abandonedCartStoreId', '');
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
