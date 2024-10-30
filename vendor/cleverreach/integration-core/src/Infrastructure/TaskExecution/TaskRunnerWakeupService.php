<?php

namespace CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution;

use Exception;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogContextData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\TaskRunnerStatusChangeException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\TaskRunnerStatusStorageUnavailableException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Interfaces\AsyncProcessService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Interfaces\TaskRunnerStatusStorage;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\GuidProvider;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\TimeProvider;

/**
 * Class TaskRunnerWakeupService.
 *
 * @package CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution
 */
class TaskRunnerWakeupService implements TaskRunnerWakeup
{
    /**
     * Service instance.
     *
     * @var AsyncProcessStarterService
     */
    private $asyncProcessStarter;
    /**
     * Service instance.
     *
     * @var RunnerStatusStorage
     */
    private $runnerStatusStorage;
    /**
     * Service instance.
     *
     * @var TimeProvider
     */
    private $timeProvider;
    /**
     * Service instance.
     *
     * @var GuidProvider
     */
    private $guidProvider;

    /**
     * Wakes up @see TaskRunner instance asynchronously if active instance is not already running.
     *
     * @return void
     */
    public function wakeup()
    {
        try {
            $this->doWakeup();
        } catch (TaskRunnerStatusChangeException $ex) {
            Logger::logDebug(
                'Fail to wakeup task runner. Runner status storage failed to set new active state.',
                'Core',
                array(
                    new LogContextData('ExceptionMessage', $ex->getMessage()),
                    new LogContextData('ExceptionTrace', $ex->getTraceAsString()),
                )
            );
        } catch (TaskRunnerStatusStorageUnavailableException $ex) {
            Logger::logDebug(
                'Fail to wakeup task runner. Runner status storage unavailable.',
                'Core',
                array(
                    new LogContextData('ExceptionMessage', $ex->getMessage()),
                    new LogContextData('ExceptionTrace', $ex->getTraceAsString()),
                )
            );
        } catch (Exception $ex) {
            Logger::logDebug(
                'Fail to wakeup task runner. Unexpected error occurred.',
                'Core',
                array(
                    new LogContextData('ExceptionMessage', $ex->getMessage()),
                    new LogContextData('ExceptionTrace', $ex->getTraceAsString()),
                )
            );
        }
    }

    /**
     * Executes wakeup of queued task.
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\ProcessStarterSaveException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\TaskRunnerStatusChangeException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\TaskRunnerStatusStorageUnavailableException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    private function doWakeup()
    {
        $runnerStatus = $this->getRunnerStorage()->getStatus();
        $currentGuid = $runnerStatus->getGuid();
        if (!empty($currentGuid) && !$runnerStatus->isExpired()) {
            return;
        }

        if ($runnerStatus->isExpired()) {
            $this->runnerStatusStorage->setStatus(TaskRunnerStatus::createNullStatus());
            Logger::logDebug('Expired task runner detected, wakeup component will start new instance.');
        }

        $guid = $this->getGuidProvider()->generateGuid();

        $this->runnerStatusStorage->setStatus(
            new TaskRunnerStatus(
                $guid,
                $this->getTimeProvider()->getCurrentLocalTime()->getTimestamp()
            )
        );

        $this->getAsyncProcessStarter()->start(new TaskRunnerStarter($guid));
    }

    /**
     * Gets instance of @return TaskRunnerStatusStorage Service instance.
     * @see TaskRunnerStatusStorageInterface.
     *
     * @return TaskRunnerStatusStorage
     */
    private function getRunnerStorage()
    {
        if ($this->runnerStatusStorage === null) {
            /** @var \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\RunnerStatusStorage $runnerStatusStorage */
            $runnerStatusStorage = ServiceRegister::getService(TaskRunnerStatusStorage::CLASS_NAME);
            $this->runnerStatusStorage = $runnerStatusStorage;
        }

        return $this->runnerStatusStorage;
    }

    /**
     * Gets instance of @return GuidProvider Service instance.
     * @see GuidProvider.
     *
     * @return GuidProvider
     */
    private function getGuidProvider()
    {
        if ($this->guidProvider === null) {
            /** @var GuidProvider $guidProvider */
            $guidProvider = ServiceRegister::getService(GuidProvider::CLASS_NAME);
            $this->guidProvider = $guidProvider;
        }

        return $this->guidProvider;
    }

    /**
     * Gets instance of @return TimeProvider Service instance.
     * @see TimeProvider.
     *
     * @return TimeProvider
     */
    private function getTimeProvider()
    {
        if ($this->timeProvider === null) {
            /** @var TimeProvider $timeProvider */
            $timeProvider = ServiceRegister::getService(TimeProvider::CLASS_NAME);
            $this->timeProvider = $timeProvider;
        }

        return $this->timeProvider;
    }

    /**
     * Gets instance of @return AsyncProcessStarterService Service instance.
     * @see AsyncProcessStarterService.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\AsyncProcessStarterService
     */
    private function getAsyncProcessStarter()
    {
        if ($this->asyncProcessStarter === null) {
            /** @var AsyncProcessStarterService $asyncProcessStarter */
            $asyncProcessStarter = ServiceRegister::getService(AsyncProcessService::CLASS_NAME);
            $this->asyncProcessStarter = $asyncProcessStarter;
        }

        return $this->asyncProcessStarter;
    }
}
