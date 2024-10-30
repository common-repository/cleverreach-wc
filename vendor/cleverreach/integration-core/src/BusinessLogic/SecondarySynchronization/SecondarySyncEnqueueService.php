<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SecondarySynchronization;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\InitialSynchronization\Tasks\Composite\InitialSyncTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SecondarySynchronization\Tasks\Composite\SecondarySyncTask;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigurationManager;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueItem;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueService;

class SecondarySyncEnqueueService implements Contracts\SecondarySyncEnqueueService
{
    /**
     * @inheritDoc
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    public function enqueueSecondarySync()
    {
        if ($this->isInitialSyncFinished() && !$this->isSecondarySyncTaskInProgress()) {
            $this->getQueueService()->enqueue(
                $this->getConfigService()->getDefaultQueueName(),
                $this->getSecondarySyncInstance(),
                $this->getConfigManager()->getContext()
            );
        }
    }

    /**
     * @return bool
     */
    protected function isSecondarySyncTaskInProgress()
    {
        $secondarySyncTask = $this->getQueueService()->findLatestByType(
            SecondarySyncTask::getClassName(),
            $this->getConfigManager()->getContext()
        );

        return $secondarySyncTask &&
            in_array($secondarySyncTask->getStatus(), array(QueueItem::QUEUED, QueueItem::IN_PROGRESS), true);
    }

    /**
     * Returns instance of the secondary sync task
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SecondarySynchronization\Tasks\Composite\SecondarySyncTask
     */
    protected function getSecondarySyncInstance()
    {
        return new SecondarySyncTask();
    }

    /**
     * @return bool
     */
    protected function isInitialSyncFinished()
    {
        $initialSyncTask = $this->getQueueService()->findLatestByType(
            InitialSyncTask::getClassName(),
            $this->getConfigManager()->getContext()
        );

        return $initialSyncTask && $initialSyncTask->getStatus() === QueueItem::COMPLETED;
    }

    /**
     * @return ConfigurationManager
     */
    protected function getConfigManager()
    {
        /** @var ConfigurationManager $configurationManager */
        $configurationManager = ServiceRegister::getService(ConfigurationManager::CLASS_NAME);

        return $configurationManager;
    }

    /**
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Configuration\Configuration
     */
    protected function getConfigService()
    {
        /** @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Configuration\Configuration $configurationService */
        $configurationService = ServiceRegister::getService(Configuration::CLASS_NAME);

        return $configurationService;
    }

    /**
     * @return QueueService
     */
    protected function getQueueService()
    {
        /** @var QueueService $queueService */
        $queueService = ServiceRegister::getService(QueueService::CLASS_NAME);

        return $queueService;
    }
}
