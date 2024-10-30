<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Scheduler;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Scheduler\Exceptions\ScheduleSaveException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Scheduler\Interfaces\Schedulable;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Scheduler\Interfaces\ScheduleRepositoryInterface;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Scheduler\Models\Schedule;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogContextData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\Operators;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\QueryFilter;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\RepositoryRegistry;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueItem;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\TimeProvider;

/**
 * Class ScheduleCheckTask
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Scheduler
 */
class ScheduleCheckTask extends Task
{
    /**
     * @var ScheduleRepositoryInterface
     */
    private $repository;

    /**
     * @inheritdoc
     */
    public function isArchivable()
    {
        return false;
    }

    /**
     * Runs task logic.
     *
     * @throws RepositoryNotRegisteredException
     * @throws QueryFilterInvalidParamException
     * @throws ScheduleSaveException
     */
    public function execute()
    {
        /** @var QueueService $queueService */
        $queueService = ServiceRegister::getService(QueueService::CLASS_NAME);

        foreach ($this->getSchedules() as $schedule) {
            try {
                if ($schedule->isRecurring()) {
                    $lastUpdateTimestamp = $schedule->getLastUpdateTimestamp();
                    $schedule->setNextSchedule();
                    $schedule->setLastUpdateTimestamp($this->now()->getTimestamp());
                    $this->getScheduleRepository()->saveWithCondition(
                        $schedule,
                        array('lastUpdateTimestamp' => $lastUpdateTimestamp)
                    );
                } else {
                    $this->getScheduleRepository()->delete($schedule);
                }

                $task = $schedule->getTask();

                if (!($task instanceof Schedulable)) {
                    Logger::logError("Cannot schedule task that is not schedulable: [{$task->getClassName()}].");

                    continue;
                }

                if (
                    !$task->canHaveMultipleQueuedInstances() &&
                    $this->isAlreadyEnqueued($schedule->getTaskType(), $schedule->getContext())
                ) {
                    Logger::logInfo("Scheduled task [{$task->getClassName()}] already enqueued.");

                    continue;
                }

                $queueService->enqueue($schedule->getQueueName(), $task, $schedule->getContext());
            } catch (QueueStorageUnavailableException $ex) {
                Logger::logError(
                    'Failed to enqueue task for schedule:' . $schedule->getId(),
                    'Core',
                    array(new LogContextData('trace', $ex->getTraceAsString()))
                );
            }
        }

        $this->reportProgress(100);
    }

    /**
     * Returns an array of Schedules that are due for execution
     *
     * @return Schedule[]
     *
     * @throws QueryFilterInvalidParamException
     * @throws RepositoryNotRegisteredException
     */
    private function getSchedules()
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('nextSchedule', Operators::LESS_OR_EQUAL_THAN, $this->now());
        $queryFilter->where('isEnabled', Operators::EQUALS, true);
        $queryFilter->orderBy('nextSchedule', QueryFilter::ORDER_ASC);
        $queryFilter->setLimit(1000);

        return $this->getScheduleRepository()->select($queryFilter);
    }

    /**
     * Returns current date and time
     *
     * @return \DateTime
     */
    protected function now()
    {
        /** @var TimeProvider $timeProvider */
        $timeProvider = ServiceRegister::getService(TimeProvider::CLASS_NAME);

        return $timeProvider->getCurrentLocalTime();
    }

    /**
     * Returns repository instance
     *
     * @return ScheduleRepositoryInterface
     * @throws RepositoryNotRegisteredException
     */
    private function getScheduleRepository()
    {
        if ($this->repository === null) {
            /** @var ScheduleRepositoryInterface $repository */
            $repository = RepositoryRegistry::getRepository(Schedule::getClassName());
            $this->repository = $repository;
        }

        return $this->repository;
    }

    /**
     * @param string $taskType
     * @param string $context
     *
     * @return bool
     */
    private function isAlreadyEnqueued($taskType, $context)
    {
        $result = false;

        $lastTask = $this->getQueueService()->findLatestByType($taskType, $context);
        if ($lastTask && in_array($lastTask->getStatus(), array(QueueItem::QUEUED, QueueItem::IN_PROGRESS), true)) {
            $result = true;
        }

        return $result;
    }

    /**
     * Retrieves queue service instance.
     *
     * @return QueueService Queue Service instance.
     */
    private function getQueueService()
    {
        /** @var QueueService $queueService */
        $queueService = ServiceRegister::getService(QueueService::CLASS_NAME);

        return $queueService;
    }
}
