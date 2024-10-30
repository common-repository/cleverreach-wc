<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\Listeners;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SecondarySynchronization\Tasks\Composite\SecondarySyncTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\Events\EnabledServicesSetEvent;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigurationManager;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueItem;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueService;

class SecondarySyncTaskEnqueuer
{
    const CLASS_NAME = __CLASS__;

    /**
     * Enqueues Secondary sync task.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\Events\EnabledServicesSetEvent $event
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    public static function handle(EnabledServicesSetEvent $event)
    {
        $oldServices = static::getServicesIds($event->getPreviousServices());
        $newServices = static::getServicesIds($event->getNewServices());

        $addedServices = array_diff($newServices, $oldServices);
        if (empty($addedServices)) {
            return;
        }

        if (!static::isInitialSyncFinished() || static::isSecondarySyncTaskInProgress()) {
            return;
        }

        $task = static::getSecondarySyncTask();
        static::enqueue($task);
    }

    /**
     * @return bool
     */
    protected static function isInitialSyncFinished()
    {
        $initialSync = static::getQueue()->findLatestByType(
            'InitialSyncTask',
            self::getConfigurationManager()->getContext()
        );

        return $initialSync && $initialSync->getStatus() === QueueItem::COMPLETED;
    }

    /**
     * @return bool
     */
    protected static function isSecondarySyncTaskInProgress()
    {
        $secondarySyncTask = static::getQueue()->findLatestByType(
            SecondarySyncTask::getClassName(),
            self::getConfigurationManager()->getContext()
        );

        return $secondarySyncTask &&
            in_array($secondarySyncTask->getStatus(), array(QueueItem::QUEUED, QueueItem::IN_PROGRESS), true);
    }

    /**
     * Retrieves secondary sync.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SecondarySynchronization\Tasks\Composite\SecondarySyncTask
     */
    protected static function getSecondarySyncTask()
    {
        return new SecondarySyncTask();
    }

    /**
     * Enqueues secondary sync task.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task $task
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    protected static function enqueue($task)
    {
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
     * Retrieves services ids.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Config\SyncService[] $services
     *
     * @return string[]
     */
    protected static function getServicesIds(array $services)
    {
        $result = array();

        /** @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Config\SyncService $service */
        foreach ($services as $service) {
            $result[] = $service->getUuid();
        }

        return $result;
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
