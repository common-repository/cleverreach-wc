<?php

namespace CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\TaskRunnerStatusChangeException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Interfaces\TaskRunnerStatusStorage;

/**
 * Class RunnerStatusStorage.
 *
 * @package CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution
 */
class RunnerStatusStorage implements TaskRunnerStatusStorage
{
    /**
     * Configuration service instance.
     *
     * @var Configuration
     */
    private $configService;

    /**
     * Returns task runner status.
     *
     * @return TaskRunnerStatus Task runner status instance.
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\TaskRunnerStatusStorageUnavailableException
     */
    public function getStatus()
    {
        $result = $this->getConfigService()->getTaskRunnerStatus();
        if (empty($result)) {
            $this->getConfigService()->setTaskRunnerStatus('', null);

            return TaskRunnerStatus::createNullStatus();
        }

        return new TaskRunnerStatus($result['guid'], $result['timestamp']);
    }

    /**
     * Sets task runner status.
     *
     * @param TaskRunnerStatus $status Status instance.
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\TaskRunnerStatusChangeException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\TaskRunnerStatusStorageUnavailableException
     */
    public function setStatus(TaskRunnerStatus $status)
    {
        $this->checkTaskRunnerStatusChangeAvailability($status);
        $this->getConfigService()->setTaskRunnerStatus($status->getGuid(), $status->getAliveSinceTimestamp());
    }

    /**
     * Checks if task runner can change availability status.
     *
     * @param TaskRunnerStatus $status Status instance.
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\TaskRunnerStatusChangeException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\TaskRunnerStatusStorageUnavailableException
     */
    private function checkTaskRunnerStatusChangeAvailability(TaskRunnerStatus $status)
    {
        $currentGuid = $this->getStatus()->getGuid();
        $guidForUpdate = $status->getGuid();

        if (!empty($currentGuid) && !empty($guidForUpdate) && $currentGuid !== $guidForUpdate) {
            throw new TaskRunnerStatusChangeException(
                'Task runner with guid: ' . $guidForUpdate . ' can not change the status.'
            );
        }
    }

    /**
     * Gets instance of @return Configuration Service instance.
     * @see Configuration service.
     *
     * @return Configuration
     */
    private function getConfigService()
    {
        if ($this->configService === null) {
            /** @var Configuration $configService */
            $configService = ServiceRegister::getService(Configuration::CLASS_NAME);
            $this->configService = $configService;
        }

        return $this->configService;
    }
}
