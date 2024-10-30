<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\Listeners;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\Events\EnabledServicesSetEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\Tasks\UpdateSyncSettingsTask;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigurationManager;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueService;

class SyncSettingsUpdater
{
    const CLASS_NAME = __CLASS__;

    /**
     * Updates sync settings when list of enabled sync services is changed.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\Events\EnabledServicesSetEvent $event
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     *
     * @noinspection PhpUnusedParameterInspection
     */
    public static function handle(EnabledServicesSetEvent $event)
    {
        $task = new UpdateSyncSettingsTask();
        $config = static::getConfigService();
        $manager = self::getConfigurationManager();

        static::getQueue()->enqueue($config->getDefaultQueueName(), $task, $manager->getContext());
    }

    /**
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Configuration\Configuration
     */
    protected static function getConfigService()
    {
        /** @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Configuration\Configuration $configService */
        $configService = ServiceRegister::getService(Configuration::CLASS_NAME);

        return $configService;
    }

    /**
     * Retrieves queue service.
     *
     * @return QueueService
     */
    protected static function getQueue()
    {
        /** @var QueueService $queueService */
        $queueService = ServiceRegister::getService(QueueService::CLASS_NAME);

        return $queueService;
    }

    /**
     * Retrieves configuration manager.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigurationManager
     */
    private static function getConfigurationManager()
    {
        /** @var ConfigurationManager $manager */
        $manager = ServiceRegister::getService(ConfigurationManager::CLASS_NAME);

        return $manager;
    }
}
