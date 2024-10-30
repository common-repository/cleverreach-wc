<?php

namespace CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogContextData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Serializer;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\TaskRunnerRunException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\TaskRunnerStatusStorageUnavailableException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Interfaces\Runnable;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Interfaces\TaskRunnerStatusStorage;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\TaskEvents\TickEvent;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\Events\EventBus;

/**
 * Class TaskRunnerStarter.
 *
 * @package CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution
 */
class TaskRunnerStarter implements Runnable
{
    /**
     * Unique runner guid.
     *
     * @var string
     */
    private $guid;
    /**
     * Instance of task runner status storage.
     *
     * @var TaskRunnerStatusStorage
     */
    private $runnerStatusStorage;
    /**
     * Instance of task runner.
     *
     * @var TaskRunner
     */
    private $taskRunner;
    /**
     * Instance of task runner wakeup service.
     *
     * @var TaskRunnerWakeup
     */
    private $taskWakeup;

    /**
     * TaskRunnerStarter constructor.
     *
     * @param string $guid Unique runner guid.
     */
    public function __construct($guid)
    {
        $this->guid = $guid;
    }

    /**
     * @inheritDoc
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Interfaces\Serializable
     *      Instance of serialized object.
     */
    public static function fromArray(array $array)
    {
        return new static($array['guid']);
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array('guid' => $this->guid);
    }

    /**
     * String representation of object.
     *
     * @inheritdoc
     */
    public function serialize()
    {
        return Serializer::serialize(array($this->guid));
    }

    /**
     * Constructs the object.
     *
     * @inheritdoc
     */
    public function unserialize($serialized)
    {
        list($this->guid) = Serializer::unserialize($serialized);
    }

    /**
     * Get unique runner guid.
     *
     * @return string Unique runner string.
     */
    public function getGuid()
    {
        return $this->guid;
    }

    /**
     * Starts synchronously currently active task runner instance.
     */
    public function run()
    {
        try {
            $this->doRun();
        } catch (TaskRunnerStatusStorageUnavailableException $ex) {
            Logger::logError(
                'Failed to run task runner. Runner status storage unavailable.',
                'Core',
                array(new LogContextData('ExceptionMessage', $ex->getMessage()))
            );
            Logger::logDebug(
                'Failed to run task runner. Runner status storage unavailable.',
                'Core',
                array(
                    new LogContextData('ExceptionMessage', $ex->getMessage()),
                    new LogContextData('ExceptionTrace', $ex->getTraceAsString()),
                )
            );
        } catch (TaskRunnerRunException $ex) {
            Logger::logInfo($ex->getMessage());
            Logger::logDebug(
                $ex->getMessage(),
                'Core',
                array(new LogContextData('ExceptionTrace', $ex->getTraceAsString()))
            );
            // @phpstan-ignore-next-line
        } catch (\Exception $ex) {
            Logger::logError(
                'Failed to run task runner. Unexpected error occurred.',
                'Core',
                array('ExceptionMessage' => $ex->getMessage())
            );
            Logger::logDebug(
                'Failed to run task runner. Unexpected error occurred.',
                'Core',
                array(
                    new LogContextData('ExceptionMessage', $ex->getMessage()),
                    new LogContextData('ExceptionTrace', $ex->getTraceAsString()),
                )
            );
        }
    }

    /**
     * Runs task execution.
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\TaskRunnerRunException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\TaskRunnerStatusStorageUnavailableException
     */
    private function doRun()
    {
        $runnerStatus = $this->getRunnerStorage()->getStatus();
        if ($this->guid !== $runnerStatus->getGuid()) {
            throw new TaskRunnerRunException('Failed to run task runner. Runner guid is not set as active.');
        }

        if ($runnerStatus->isExpired()) {
            $this->getTaskWakeup()->wakeup();
            throw new TaskRunnerRunException('Failed to run task runner. Runner is expired.');
        }

        $this->getTaskRunner()->setGuid($this->guid);
        $this->getTaskRunner()->run();

        /** @var EventBus $eventBus */
        $eventBus = ServiceRegister::getService(EventBus::CLASS_NAME);
        $eventBus->fire(new TickEvent());

        // Send wakeup signal when runner is completed.
        $this->getTaskWakeup()->wakeup();
    }

    /**
     * Gets task runner status storage instance.
     *
     * @return TaskRunnerStatusStorage Instance of runner status storage service.
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
     * Gets task runner instance.
     *
     * @return TaskRunner Instance of runner service.
     */
    private function getTaskRunner()
    {
        if ($this->taskRunner === null) {
            /** @var \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\TaskRunner $taskRunner */
            $taskRunner = ServiceRegister::getService(TaskRunner::CLASS_NAME);
            $this->taskRunner = $taskRunner;
        }

        return $this->taskRunner;
    }

    /**
     * Gets task runner wakeup instance.
     *
     * @return TaskRunnerWakeup Instance of runner wakeup service.
     */
    private function getTaskWakeup()
    {
        if ($this->taskWakeup === null) {
            /** @var TaskRunnerWakeup $taskWakeup */
            $taskWakeup = ServiceRegister::getService(TaskRunnerWakeup::CLASS_NAME);
            $this->taskWakeup = $taskWakeup;
        }

        return $this->taskWakeup;
    }
}
