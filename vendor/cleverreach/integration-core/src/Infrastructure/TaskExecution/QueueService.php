<?php

namespace CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution;

use BadMethodCallException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\TaskExecution\TasksToBeDeleted;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Interfaces\ArchivedQueueItemRepository;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Interfaces\QueueItemRepository;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\Operators;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\QueryFilter;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\RepositoryRegistry;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Events\BeforeQueueStatusChangeEvent;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Events\QueueStatusChangedEvent;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\ExecutionRequirementsNotMetException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueItemSaveException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Interfaces\Priority;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\Events\EventBus;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\TimeProvider;

/**
 * Class Queue.
 *
 * @package CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution
 */
class QueueService
{
    /**
     * Fully qualified name of this class.
     */
    const CLASS_NAME = __CLASS__;
    /**
     * Maximum failure retries count
     */
    const MAX_RETRIES = 5;
    /**
     * A storage for task queue.
     *
     * @var QueueItemRepository
     */
    private $storage;
    /**
     * @var ArchivedQueueItemRepository
     */
    private $archivedStorage;
    /**
     * Time provider instance.
     *
     * @var TimeProvider
     */
    private $timeProvider;
    /**
     * Task runner wakeup instance.
     *
     * @var TaskRunnerWakeup
     */
    private $taskRunnerWakeup;
    /**
     * Configuration service instance.
     *
     * @var Configuration
     */
    private $configService;

    /**
     * Enqueues queue item to a given queue and stores changes.
     *
     * @param string $queueName Name of a queue where queue item should be queued.
     * @param Task $task Task to enqueue.
     * @param string $context Task execution context. If integration supports multiple accounts (middleware
     *     integration) context based on account id should be provided. Failing to do this will result in global task
     *     context and unpredictable task execution.
     *
     * @param int $priority
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueItem Created queue item.
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException When queue storage
     *     fails to save the item.
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException When queue storage
     *     fails to save the item.
     */
    public function enqueue($queueName, Task $task, $context = '', $priority = Priority::NORMAL)
    {
        $equalityHash = QueueItem::calculateEqualityHash(
            $context,
            $queueName,
            $priority,
            $task->getType(),
            $task->getEqualityComponents()
        );
        $existingQueueItem = $this->findLatestByHash($equalityHash, $queueName, $context);

        if ($existingQueueItem && $existingQueueItem->getStatus() === QueueItem::QUEUED) {
            $queueItem = $existingQueueItem;
        } else {
            $queueItem = new QueueItem($task);
            $queueItem->setStatus(QueueItem::QUEUED);
            $queueItem->setQueueName($queueName);
            $queueItem->setContext($context);
            $queueItem->setQueueTimestamp($this->getTimeProvider()->getCurrentLocalTime()->getTimestamp());
            $queueItem->setPriority($priority);
            $queueItem->setEqualityHash($equalityHash);

            $this->save($queueItem, array(), true, QueueItem::CREATED);
        }

        $this->getTaskRunnerWakeup()->wakeup();

        return $queueItem;
    }

    /**
     * Validates that the execution requirements are met for the particular
     * Execution job.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueItem $queueItem
     *
     * @return void
     *
     * @throws ExecutionRequirementsNotMetException
     */
    public function validateExecutionRequirements(QueueItem $queueItem)
    {
    }

    /**
     * Starts task execution, puts queue item in "in_progress" state and stores queue item changes.
     *
     * @param QueueItem $queueItem Queue item to start.
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException
     */
    public function start(QueueItem $queueItem)
    {
        if ($queueItem->getStatus() !== QueueItem::QUEUED) {
            $this->throwIllegalTransitionException($queueItem->getStatus(), QueueItem::IN_PROGRESS);
        }

        $lastUpdateTimestamp = $queueItem->getLastUpdateTimestamp();

        $queueItem->setStatus(QueueItem::IN_PROGRESS);
        $queueItem->setStartTimestamp($this->getTimeProvider()->getCurrentLocalTime()->getTimestamp());
        $queueItem->setLastUpdateTimestamp($queueItem->getStartTimestamp());

        $this->save(
            $queueItem,
            array('status' => QueueItem::QUEUED, 'lastUpdateTimestamp' => $lastUpdateTimestamp),
            true,
            QueueItem::QUEUED
        );

        $queueItem->getTask()->execute();
    }

    /**
     * Puts queue item in finished status and stores changes.
     *
     * @param QueueItem $queueItem Queue item to finish.
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueItemSaveException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    public function finish(QueueItem $queueItem)
    {
        if ($queueItem->getStatus() !== QueueItem::IN_PROGRESS) {
            $this->throwIllegalTransitionException($queueItem->getStatus(), QueueItem::COMPLETED);
        }

        $queueItem->setStatus(QueueItem::COMPLETED);
        $queueItem->setFinishTimestamp($this->getTimeProvider()->getCurrentLocalTime()->getTimestamp());
        $queueItem->setProgressBasePoints(10000);

        if (in_array($queueItem->getTaskType(), TasksToBeDeleted::getTaskForDeletion())) {
            $this->getStorage()->removeQueueItem($queueItem);

            return;
        }

        if ($queueItem->getTask()->isArchivable()) {
            $this->archiveQueueItem($queueItem);
        } else {
            $this->save(
                $queueItem,
                array(
                    'status' => QueueItem::IN_PROGRESS,
                    'lastUpdateTimestamp' => $queueItem->getLastUpdateTimestamp(),
                ),
                true,
                QueueItem::IN_PROGRESS
            );
        }
    }

    /**
     * Returns queue item back to queue and sets updates last execution progress to current progress value.
     *
     * @param QueueItem $queueItem Queue item to requeue.
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    public function requeue(QueueItem $queueItem)
    {
        if ($queueItem->getStatus() !== QueueItem::IN_PROGRESS) {
            $this->throwIllegalTransitionException($queueItem->getStatus(), QueueItem::QUEUED);
        }

        $lastExecutionProgress = $queueItem->getLastExecutionProgressBasePoints();

        $queueItem->setStatus(QueueItem::QUEUED);
        $queueItem->setStartTimestamp(0);
        $queueItem->setLastExecutionProgressBasePoints($queueItem->getProgressBasePoints());

        $this->save(
            $queueItem,
            array(
                'status' => QueueItem::IN_PROGRESS,
                'lastExecutionProgress' => $lastExecutionProgress,
                'lastUpdateTimestamp' => $queueItem->getLastUpdateTimestamp(),
            ),
            true,
            QueueItem::IN_PROGRESS
        );
    }

    /**
     * Returns queue item back to queue and increments retries count.
     * When max retries count is reached puts item in failed status.
     *
     * @param QueueItem $queueItem Queue item to fail.
     * @param string $failureDescription Verbal description of failure.
     *
     * @return void
     *
     * @throws \BadMethodCallException Queue item must be in "in_progress" status for fail method.
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueItemSaveException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     */
    public function fail(QueueItem $queueItem, $failureDescription)
    {
        if ($queueItem->getStatus() !== QueueItem::IN_PROGRESS) {
            $this->throwIllegalTransitionException($queueItem->getStatus(), QueueItem::FAILED);
        }

        $queueItem->setRetries($queueItem->getRetries() + 1);
        $queueItem->setFailureDescription(
            ($queueItem->getFailureDescription() ? ($queueItem->getFailureDescription() . "\n") : '')
            . 'Attempt ' . $queueItem->getRetries() . ': ' . $failureDescription
        );

        if ($queueItem->getRetries() > $this->getMaxRetries()) {
            $queueItem->setStatus(QueueItem::FAILED);
            $queueItem->setFailTimestamp($this->getTimeProvider()->getCurrentLocalTime()->getTimestamp());

            if ($queueItem->getTask()->isArchivable()) {
                $this->archiveQueueItem($queueItem);

                return;
            }
        } else {
            $queueItem->setStatus(QueueItem::QUEUED);
            $queueItem->setStartTimestamp(0);
        }

        $this->save(
            $queueItem,
            array(
                'status' => QueueItem::IN_PROGRESS,
                'lastExecutionProgress' => $queueItem->getLastExecutionProgressBasePoints(),
                'lastUpdateTimestamp' => $queueItem->getLastUpdateTimestamp(),
            ),
            true,
            QueueItem::IN_PROGRESS
        );
    }

    /**
     * Fails task that cannot be deserialized.
     *
     * @param QueueItem $queueItem
     * @param string $failureDescription
     *
     * @return void
     */
    public function forceFail(QueueItem $queueItem, $failureDescription)
    {
        $queueItem->setRetries($this->getMaxRetries());
        $queueItem->setFailureDescription(
            ($queueItem->getFailureDescription() ? ($queueItem->getFailureDescription() . "\n") : '')
            . 'Attempt ' . $queueItem->getRetries() . ': ' . $failureDescription
        );
        $queueItem->setStatus(QueueItem::FAILED);
        $queueItem->setFailTimestamp($this->getTimeProvider()->getCurrentLocalTime()->getTimestamp());

        $this->getStorage()->forceFail($queueItem);
    }

    /**
     * Aborts the queue item. Aborted queue item will not be started again.
     *
     * @param QueueItem $queueItem Queue item to abort.
     * @param string $abortDescription Verbal description of the reason for abortion.
     *
     * @return void
     *
     * @throws \BadMethodCallException Queue item must be in "in_progress" status for abort method.
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueItemSaveException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     */
    public function abort(QueueItem $queueItem, $abortDescription)
    {
        if ($queueItem->getStatus() !== QueueItem::IN_PROGRESS) {
            $this->throwIllegalTransitionException($queueItem->getStatus(), QueueItem::ABORTED);
        }

        $queueItem->setStatus(QueueItem::ABORTED);
        $queueItem->setFailTimestamp($this->getTimeProvider()->getCurrentLocalTime()->getTimestamp());
        $queueItem->setFailureDescription(
            ($queueItem->getFailureDescription() ? ($queueItem->getFailureDescription() . "\n") : '')
            . 'Attempt ' . ($queueItem->getRetries() + 1) . ': ' . $abortDescription
        );

        if ($queueItem->getTask()->isArchivable()) {
            $this->archiveQueueItem($queueItem);
        } else {
            $this->save(
                $queueItem,
                array(
                    'status' => QueueItem::IN_PROGRESS,
                    'lastExecutionProgress' => $queueItem->getLastExecutionProgressBasePoints(),
                    'lastUpdateTimestamp' => $queueItem->getLastUpdateTimestamp(),
                ),
                true,
                QueueItem::IN_PROGRESS
            );
        }
    }

    /**
     * Updates queue item progress.
     *
     * @param QueueItem $queueItem Queue item to be updated.
     * @param int $progress New progress.
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    public function updateProgress(QueueItem $queueItem, $progress)
    {
        if ($queueItem->getStatus() !== QueueItem::IN_PROGRESS) {
            throw new BadMethodCallException('Progress reported for not started queue item.');
        }

        $lastExecutionProgress = $queueItem->getLastExecutionProgressBasePoints();
        $lastUpdateTimestamp = $queueItem->getLastUpdateTimestamp();

        $queueItem->setProgressBasePoints($progress);
        $queueItem->setLastUpdateTimestamp($this->getTimeProvider()->getCurrentLocalTime()->getTimestamp());

        $this->save(
            $queueItem,
            array(
                'status' => QueueItem::IN_PROGRESS,
                'lastExecutionProgress' => $lastExecutionProgress,
                'lastUpdateTimestamp' => $lastUpdateTimestamp,
            )
        );
    }

    /**
     * Keeps passed queue item alive by setting last update timestamp.
     *
     * @param QueueItem $queueItem Queue item to keep alive.
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    public function keepAlive(QueueItem $queueItem)
    {
        $lastExecutionProgress = $queueItem->getLastExecutionProgressBasePoints();
        $lastUpdateTimestamp = $queueItem->getLastUpdateTimestamp();
        $queueItem->setLastUpdateTimestamp($this->getTimeProvider()->getCurrentLocalTime()->getTimestamp());

        $this->save(
            $queueItem,
            array(
                'status' => QueueItem::IN_PROGRESS,
                'lastExecutionProgress' => $lastExecutionProgress,
                'lastUpdateTimestamp' => $lastUpdateTimestamp,
            )
        );
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     *
     * Finds queue item by Id.
     *
     * @param int|string $id Id of a queue item to find.
     *
     * @return QueueItem|null Queue item if found; otherwise, NULL.
     */
    public function find($id)
    {
        $filter = new QueryFilter();
        /** @noinspection PhpUnhandledExceptionInspection */
        $filter->where('id', '=', $id);

        return $this->getStorage()->selectOne($filter);
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     *
     * Finds archived queue item by Id.
     *
     * @param int $id Id of a queue item to find.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\ArchivedQueueItem|null ArchivedQueue item if found; otherwise,
     *     NULL.
     */
    public function findArchived($id)
    {
        $filter = new QueryFilter();
        /** @noinspection PhpUnhandledExceptionInspection */
        $filter->where('id', '=', $id);

        return $this->getArchivedStorage()->selectOne($filter);
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     *
     * Finds latest queue item by type.
     *
     * @param string $type Type of a queue item to find.
     * @param string $context Task scope restriction, default is global scope.
     *
     * @return QueueItem|null Queue item if found; otherwise, NULL.
     */
    public function findLatestByType($type, $context = '')
    {
        $filter = new QueryFilter();
        /** @noinspection PhpUnhandledExceptionInspection */
        $filter->where('taskType', '=', $type);
        if (!empty($context)) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $filter->where('context', '=', $context);
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        $filter->orderBy('queueTime', 'DESC');

        return $this->getStorage()->selectOne($filter);
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     *
     * Finds queue items with status "in_progress".
     *
     * @return QueueItem[] Running queue items.
     */
    public function findRunningItems()
    {
        $filter = new QueryFilter();
        /** @noinspection PhpUnhandledExceptionInspection */
        $filter->where('status', '=', QueueItem::IN_PROGRESS);

        return $this->getStorage()->select($filter);
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     *
     * Finds queue items with status "in_progress", and where lastUpdateTimestamp is less than current time decreased
     * with default max inactivity period
     *
     * @return QueueItem[] Running queue items.
     */
    public function findExpiredRunningItems()
    {
        $filter = new QueryFilter();
        $lastUpdateTimeFilter = $this->getTimeProvider()->getCurrentLocalTime()->getTimestamp(
        ) - Task::MAX_INACTIVITY_PERIOD;

        /** @noinspection PhpUnhandledExceptionInspection */
        $filter->where('status', '=', QueueItem::IN_PROGRESS);
        $filter->where('lastUpdateTimestamp', Operators::LESS_THAN, $lastUpdateTimeFilter);

        return $this->getStorage()->select($filter);
    }

    /**
     * Returns count of items with provided status
     *
     * @param string $status
     *
     * @return int
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    public function countItems($status)
    {
        $filter = new QueryFilter();
        /** @noinspection PhpUnhandledExceptionInspection */
        $filter->where('status', '=', $status);

        return $this->getStorage()->count($filter);
    }

    /**
     * Finds list of earliest queued queue items per queue.
     * Only queues that doesn't have running tasks are taken in consideration.
     * Returned queue items are ordered in the descending priority.
     *
     * @param int $limit Result set limit. By default max 10 earliest queue items will be returned.
     *
     * @return QueueItem[] An array of found queue items.
     */
    public function findOldestQueuedItems($limit = 10)
    {
        $result = array();
        $currentLimit = $limit;

        foreach (QueueItem::getAvailablePriorities() as $priority) {
            $batch = $this->getStorage()->findOldestQueuedItems($priority, $currentLimit);
            $result[] = $batch;

            if (($currentLimit -= count($batch)) <= 0) {
                break;
            }
        }

        $result = !empty($result) ? call_user_func_array('array_merge', $result) : $result;

        return array_slice($result, 0, $limit);
    }

    /**
     * @param QueueItem $queueItem
     *
     * @return bool
     */
    public function deleteQueueItemFromStorage($queueItem)
    {
        return $this->getStorage()->removeQueueItem($queueItem);
    }

    /**
     * Return queue item at the top of the queue
     *
     * @param string $equalityHash task specific hash
     * @param string $queueName Queue name
     * @param string $context Task scope restriction, default is global scope.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueItem|null
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    public function findLatestByHash($equalityHash, $queueName, $context = '')
    {
        $filter = new QueryFilter();

        if (!empty($context)) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $filter->where('context', '=', $context);
        }

        if (!empty($queueName)) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $filter->where('queueName', '=', $queueName);
        }

        if (!empty($equalityHash)) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $filter->where('equalityHash', '=', $equalityHash);
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        $filter->orderBy('queueTime', 'DESC');

        return $this->getStorage()->selectOne($filter);
    }

    /**
     * Creates or updates given queue item using storage service. If queue item id is not set, new queue item will be
     * created; otherwise, update will be performed.
     *
     * @param QueueItem $queueItem Item to save.
     * @param array<string,mixed> $additionalWhere List of key/value pairs to set in where clause when saving queue item.
     * @param bool $reportStateChange Indicates whether to invoke a status change event.
     * @param string $previousState If event should be invoked, indicates the previous state.
     *
     * @return int Id of saved queue item.
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    private function save(
        QueueItem $queueItem,
        array $additionalWhere = array(),
        $reportStateChange = false,
        $previousState = ''
    ) {
        try {
            if ($reportStateChange) {
                $this->reportBeforeStatusChange($queueItem, $previousState);
            }

            $id = $this->getStorage()->saveWithCondition($queueItem, $additionalWhere);
            $queueItem->setId($id);

            if ($reportStateChange) {
                $this->reportStatusChange($queueItem, $previousState);
            }

            return $id;
        } catch (QueueItemSaveException $exception) {
            throw new QueueStorageUnavailableException('Unable to update the task.', $exception);
        }
    }

    /**
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueItemSaveException
     */
    private function archiveQueueItem(QueueItem $queueItem)
    {
        //removes queue item from entity table
        $this->getStorage()->removeQueueItem($queueItem);

        //archives queue item (saves queue item to archived table)
        $this->getArchivedStorage()->archiveQueueItem($queueItem);
    }

    /**
     * Fires event for before status change.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueItem $queueItem Queue item with is about to change status.
     * @param string $previousState Previous state. MUST be one of the states defined as constants in @see QueueItem.
     *
     * @return void
     */
    private function reportBeforeStatusChange(QueueItem $queueItem, $previousState)
    {
        /** @var EventBus $eventBus */
        $eventBus = ServiceRegister::getService(EventBus::CLASS_NAME);
        $eventBus->fire(new BeforeQueueStatusChangeEvent($queueItem, $previousState));
    }

    /**
     * Fires event for status change.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueItem $queueItem Queue item with changed status.
     * @param string $previousState Previous state. MUST be one of the states defined as constants in @see QueueItem.
     *
     * @return void
     */
    private function reportStatusChange(QueueItem $queueItem, $previousState)
    {
        /** @var EventBus $eventBus */
        $eventBus = ServiceRegister::getService(EventBus::CLASS_NAME);
        $eventBus->fire(new QueueStatusChangedEvent($queueItem, $previousState));
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     *
     * Gets task storage instance.
     *
     * @return QueueItemRepository Task storage instance.
     */
    private function getStorage()
    {
        if ($this->storage === null) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->storage = RepositoryRegistry::getQueueItemRepository();
        }

        return $this->storage;
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     *
     * Gets archived queue item repository instance.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Interfaces\ArchivedQueueItemRepository
     */
    private function getArchivedStorage()
    {
        if ($this->archivedStorage === null) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->archivedStorage = RepositoryRegistry::getArchivedQueueItemRepository();
        }

        return $this->archivedStorage;
    }

    /**
     * Gets time provider instance.
     *
     * @return TimeProvider Time provider instance.
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
     * Gets task runner wakeup instance.
     *
     * @return TaskRunnerWakeup Task runner wakeup instance.
     */
    private function getTaskRunnerWakeup()
    {
        if ($this->taskRunnerWakeup === null) {
            /** @var TaskRunnerWakeup $taskRunnerWakeup */
            $taskRunnerWakeup = ServiceRegister::getService(TaskRunnerWakeup::CLASS_NAME);
            $this->taskRunnerWakeup = $taskRunnerWakeup;
        }

        return $this->taskRunnerWakeup;
    }

    /**
     * Gets configuration service instance.
     *
     * @return Configuration Configuration service instance.
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

    /**
     * Prepares exception message and throws exception.
     *
     * @param string $fromStatus A status form which status change is attempts.
     * @param string $toStatus A status to which status change is attempts.
     *
     * @return void
     *
     * @throws \BadMethodCallException
     */
    private function throwIllegalTransitionException($fromStatus, $toStatus)
    {
        throw new BadMethodCallException(
            sprintf(
                'Illegal queue item state transition from "%s" to "%s"',
                $fromStatus,
                $toStatus
            )
        );
    }

    /**
     * Returns maximum number of retries.
     *
     * @return int Number of retries.
     */
    private function getMaxRetries()
    {
        $configurationValue = $this->getConfigService()->getMaxTaskExecutionRetries();

        return $configurationValue !== null ? $configurationValue : self::MAX_RETRIES;
    }
}
