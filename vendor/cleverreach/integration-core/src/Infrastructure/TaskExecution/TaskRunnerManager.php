<?php

namespace CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Interfaces\TaskRunnerManager as BaseService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;

class TaskRunnerManager implements BaseService
{
    /**
     * @var \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\Configuration
     */
    protected $configuration;
    /**
     * @var \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup
     */
    protected $tasRunnerWakeupService;

    /**
     * Halts task runner.
     *
     * @return void
     */
    public function halt()
    {
        $this->getConfiguration()->setTaskRunnerHalted(true);
    }

    /**
     * Resumes task execution.
     *
     * @return void
     */
    public function resume()
    {
        $this->getConfiguration()->setTaskRunnerHalted(false);
        $this->getTaskRunnerWakeupService()->wakeup();
    }

    /**
     * Retrieves configuration.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\Configuration Configuration instance.
     */
    protected function getConfiguration()
    {
        if ($this->configuration === null) {
            /** @var Configuration $configuration */
            $configuration = ServiceRegister::getService(Configuration::CLASS_NAME);
            $this->configuration = $configuration;
        }

        return $this->configuration;
    }

    /**
     * Retrieves task runner wakeup service.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup Task runner wakeup instance.
     */
    protected function getTaskRunnerWakeupService()
    {
        if ($this->tasRunnerWakeupService === null) {
            /** @var TaskRunnerWakeup $taskRunnerWakeup */
            $taskRunnerWakeup = ServiceRegister::getService(TaskRunnerWakeup::CLASS_NAME);
            $this->tasRunnerWakeupService = $taskRunnerWakeup;
        }

        return $this->tasRunnerWakeupService;
    }
}
