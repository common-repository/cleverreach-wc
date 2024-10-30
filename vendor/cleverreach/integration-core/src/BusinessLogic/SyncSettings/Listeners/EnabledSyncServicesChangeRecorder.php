<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\Listeners;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\Entities\EnabledServicesChangeLog;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\Events\EnabledServicesSetEvent;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigurationManager;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\Transformer;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\RepositoryRegistry;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\TimeProvider;

class EnabledSyncServicesChangeRecorder
{
    const CLASS_NAME = __CLASS__;

    /**
     * Saves current services in change log.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\Events\EnabledServicesSetEvent $event
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public static function handle(EnabledServicesSetEvent $event)
    {
        $entity = new EnabledServicesChangeLog();
        $entity->createdAt = TimeProvider::getInstance()->getDateTime(time());
        $entity->services = Transformer::batchTransform($event->getNewServices());
        $entity->context = static::getConfigManager()->getContext();

        static::getRepository()->save($entity);
    }

    /**
     * Retrieves repository.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Interfaces\RepositoryInterface
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    protected static function getRepository()
    {
        return RepositoryRegistry::getRepository(EnabledServicesChangeLog::getClassName());
    }

    /**
     * Return configuration manager.
     *
     * @return ConfigurationManager
     */
    protected static function getConfigManager()
    {
        /** @var ConfigurationManager $configurationManager */
        $configurationManager = ServiceRegister::getService(ConfigurationManager::CLASS_NAME);

        return $configurationManager;
    }
}
