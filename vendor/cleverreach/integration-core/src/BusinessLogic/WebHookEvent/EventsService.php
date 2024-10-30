<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Contracts\EventsService as BaseService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigurationManager;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class EventsService
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent
 */
abstract class EventsService implements BaseService
{
    /**
     * @inheritDoc
     */
    public function getCallToken()
    {
        return $this->getValue('callToken', '');
    }

    /**
     * @inheritDoc
     */
    public function setCallToken($token)
    {
        $this->setValue('callToken', $token);
    }

    /**
     * @inheritDoc
     */
    public function getSecret()
    {
        return $this->getValue('secret', '');
    }

    /**
     * @inheritDoc
     */
    public function setSecret($secret)
    {
        $this->setValue('secret', $secret);
    }

    /**
     * @inheritDoc
     */
    public function getVerificationToken()
    {
        return $this->getValue('verificationToken', '');
    }

    /**
     * @inheritDoc
     */
    public function setVerificationToken($token)
    {
        $this->setValue('verificationToken', $token);
    }

    /**
     * Retrieves config value.
     *
     * @param string $key
     * @param mixed $default
     * @param bool $isContextSpecific
     *
     * @return mixed
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    protected function getValue($key, $default = null, $isContextSpecific = true)
    {
        $key = $this->getConfigKey($key);

        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->getConfigManager()->getConfigValue($key, $default, $isContextSpecific);
    }

    /**
     * Saves config value.
     *
     * @param string $key
     * @param mixed $value
     * @param bool $isContextSpecific
     *
     * @return void
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    protected function setValue($key, $value, $isContextSpecific = true)
    {
        $key = $this->getConfigKey($key);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->getConfigManager()->saveConfigValue($key, $value, $isContextSpecific);
    }

    /**
     * Retrieves configuration manager.
     *
     * @return ConfigurationManager
     */
    private function getConfigManager()
    {
        /** @var ConfigurationManager $configurationManager */
        $configurationManager = ServiceRegister::getService(ConfigurationManager::CLASS_NAME);

        return $configurationManager;
    }

    /**
     * Generates config key.
     *
     * @param string $key
     *
     * @return string
     */
    private function getConfigKey($key)
    {
        $key = $this->getType() . '-' . $key;

        return $key;
    }
}
