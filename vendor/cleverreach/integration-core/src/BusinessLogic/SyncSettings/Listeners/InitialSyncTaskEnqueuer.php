<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\Listeners;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\Contracts\AppStateService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\StateRegister;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\States\InitialSync;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\InitialSynchronization\Tasks\Composite\InitialSyncTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\Events\EnabledServicesSetEvent;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigurationManager;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueService;

class InitialSyncTaskEnqueuer
{
    /**
     * Enqueues initial sync after the sync services have been set.
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
        $context = self::getConfigurationManager()->getContext();
        $task = static::getQueue()->findLatestByType('InitialSyncTask', $context);

        if ($task !== null) {
            return;
        }

        $task = static::getInitialSyncTask();
        $configuration = static::getConfigService();
        static::getQueue()->enqueue(
            $configuration->getDefaultQueueName(),
            $task,
            $context
        );

        /** @var AppStateService $appStateService */
        $appStateService = ServiceRegister::getService(AppStateService::CLASS_NAME);

        $context = $appStateService->getStateContext();
        $context->changeState();

        $appStateService->setStateContext($context);
    }

    /**
     * Retrieves initial sync task instance.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\InitialSynchronization\Tasks\Composite\InitialSyncTask
     */
    protected static function getInitialSyncTask()
    {
        return new InitialSyncTask();
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
    protected static function getConfigurationManager()
    {
        /** @var ConfigurationManager $manager */
        $manager = ServiceRegister::getService(ConfigurationManager::CLASS_NAME);

        return $manager;
    }
}
