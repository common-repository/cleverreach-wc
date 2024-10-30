<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Dashboard;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Dashboard\Contracts\DashboardService as BaseService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SecondarySynchronization\Tasks\Composite\SecondarySyncTask;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigurationManager;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueService;

class DashboardService implements BaseService
{
    /**
     * @inheritDoc
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    public function isSyncStatisticsDisplayed()
    {
        return $this->getConfigurationManager()->getConfigValue('isSyncStatisticsDisplayed', false);
    }

    /**
     * @inheritDoc
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    public function setSyncStatisticsDisplayed($status)
    {
        $this->getConfigurationManager()->saveConfigValue('isSyncStatisticsDisplayed', $status);
    }

    /**
     * @inheritDoc
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    public function getSyncedReceiversCount()
    {
        return $this->getConfigurationManager()->getConfigValue('numberOfSyncedReceivers', 0);
    }

    /**
     * @inheritDoc
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    public function setSyncedReceiversCount($count)
    {
        $this->getConfigurationManager()->saveConfigValue('numberOfSyncedReceivers', $count);
    }

    /**
     * Returns date of the last secondary sync task
     *
     * @return string|null
     */
    public function getLastSyncJobTime()
    {
        /** @var QueueService $queueService */
        $queueService = ServiceRegister::getService(QueueService::CLASS_NAME);
        $secondarySyncItem = $queueService->findLatestByType(
            SecondarySyncTask::getClassName(),
            $this->getConfigurationManager()->getContext()
        );

        if ($secondarySyncItem && $secondarySyncItem->getFinishTimestamp()) {
            $dateTime = new \DateTime("@{$secondarySyncItem->getFinishTimestamp()}");

            return $dateTime->format('d-m-Y H:i:s');
        }

        return null;
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
