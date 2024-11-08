<?php

namespace CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Serializer;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\TaskEvents\AliveAnnouncedTaskEvent;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\TaskEvents\TaskProgressEvent;

/**
 * Class CompositeTask
 *
 * This type of task should be used when there is a need for synchronous execution of several tasks.
 *
 * @package CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution
 */
abstract class CompositeTask extends Task
{
    /**
     * A map of progress per task. Array key is task FQN and current progress is value.
     *
     * @var mixed[]
     */
    protected $taskProgressMap = array();
    /**
     * A map of progress share per task. Array key is task FQN and value is percentage of progress share (0 - 100).
     *
     * @var mixed[]
     */
    protected $tasksProgressShare = array();
    /**
     * An array of all tasks that compose this task.
     *
     * @var Task[]
     */
    protected $tasks = array();
    /**
     * Percentage of initial progress.
     *
     * @var int
     */
    private $initialProgress;

    /**
     * CompositeTask constructor.
     *
     * @param mixed[] $subTasks List of all tasks for this composite task. Key is task FQN and value is percentage share.
     * @param int $initialProgress Initial progress in percents.
     */
    public function __construct(array $subTasks, $initialProgress = 0)
    {
        $this->initialProgress = $initialProgress;

        $this->taskProgressMap = array(
            'overallTaskProgress' => 0,
        );

        $this->tasksProgressShare = array();

        foreach ($subTasks as $subTaskKey => $subTaskProgressShare) {
            $this->taskProgressMap[$subTaskKey] = 0;
            $this->tasksProgressShare[$subTaskKey] = $subTaskProgressShare;
        }
    }

    /**
     * @inheritDoc
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Interfaces\Serializable
     *      Instance of serialized object.
     */
    public static function fromArray(array $serializedData)
    {
        $tasks = array();

        foreach ($serializedData['tasks'] as $index => $task) {
            $tasks[$index] = Serializer::unserialize($task);
        }

        $entity = static::createTask($tasks, $serializedData['initial_progress']);
        $entity->tasks = $tasks;
        $entity->initialProgress = $serializedData['initial_progress'];
        $entity->taskProgressMap = $serializedData['task_progress_map'];
        $entity->tasksProgressShare = $serializedData['tasks_progress_share'];

        $entity->onUnserialized();

        return $entity;
    }

    /**
     * Creates composite task instance.
     *
     * @param mixed[] $tasks
     * @param int $initialProgress
     *
     * @return static
     */
    protected static function createTask(array $tasks, $initialProgress)
    {
        return new static($tasks, $initialProgress);
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $tasks = array();

        foreach ($this->tasks as $index => $task) {
            $tasks[$index] = Serializer::serialize($task);
        }

        return array(
            'initial_progress' => $this->initialProgress,
            'task_progress_map' => $this->taskProgressMap,
            'tasks_progress_share' => $this->tasksProgressShare,
            'tasks' => $tasks,
        );
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return Serializer::serialize(
            array(
                'initialProgress' => $this->initialProgress,
                'taskProgress' => $this->taskProgressMap,
                'subTasksProgressShare' => $this->tasksProgressShare,
                'tasks' => $this->tasks,
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function unserialize($serialized)
    {
        $unserializedStateData = Serializer::unserialize($serialized);

        $this->initialProgress = $unserializedStateData['initialProgress'];
        $this->taskProgressMap = $unserializedStateData['taskProgress'];
        $this->tasksProgressShare = $unserializedStateData['subTasksProgressShare'];
        $this->tasks = $unserializedStateData['tasks'];

        $this->onUnserialized();
    }

    /**
     * Called upon composite task deserialization.
     * Allows bootstrapping operations to be completed when the deserialization is complete.
     *
     * @return void
     */
    public function onUnserialized()
    {
        $this->registerSubTasksEvents();
    }

    /**
     * Runs task logic. Executes each task sequentially.
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException
     */
    public function execute()
    {
        while ($activeTask = $this->getActiveTask()) {
            $activeTask->execute();
        }
    }

    /**
     * Determines whether task can be reconfigured.
     *
     * @return bool TRUE if active task can be reconfigures; otherwise, FALSE.
     */
    public function canBeReconfigured()
    {
        $activeTask = $this->getActiveTask();

        return $activeTask !== null ? $activeTask->canBeReconfigured() : false;
    }

    /**
     * Reconfigures the task.
     *
     * @return void
     */
    public function reconfigure()
    {
        $activeTask = $this->getActiveTask();

        if ($activeTask !== null) {
            $activeTask->reconfigure();
        }
    }

    /**
     * Gets progress by each task.
     *
     * @return mixed[] A map of progress per task. Array key is task FQN and current progress is value.
     */
    public function getProgressByTask()
    {
        return $this->taskProgressMap;
    }

    /**
     * Creates a sub task for specified task FQN.
     *
     * @param string $taskKey Fully qualified name of the task.
     *
     * @return Task Created task.
     */
    abstract protected function createSubTask($taskKey);

    /**
     * Returns active task.
     *
     * @return Task|null Active task if any; otherwise, NULL.
     */
    protected function getActiveTask()
    {
        $task = null;
        foreach ($this->taskProgressMap as $taskKey => $taskProgress) {
            if ($taskKey === 'overallTaskProgress') {
                continue;
            }

            if ($taskProgress < 100) {
                $task = $this->getSubTask($taskKey);

                break;
            }
        }

        return $task;
    }

    /**
     * Gets sub task by the task FQN. If sub task does not exist, creates it.
     *
     * @param string $taskKey Task FQN.
     *
     * @return Task An instance of task for given FQN.
     */
    protected function getSubTask($taskKey)
    {
        if (empty($this->tasks[$taskKey])) {
            $this->tasks[$taskKey] = $this->createSubTask($taskKey);
            $this->registerSubTaskEvents($taskKey);
        }

        return $this->tasks[$taskKey];
    }

    /**
     * Registers "report progress" and "report alive" events to all sub tasks.
     *
     * @return void
     */
    protected function registerSubTasksEvents()
    {
        foreach ($this->tasks as $key => $task) {
            $this->registerSubTaskEvents($key);
        }
    }

    /**
     * Registers "report progress" and "report alive" events to a sub task.
     *
     * @param string $taskKey KeyA Task for which to register listener.
     *
     * @return void
     */
    protected function registerSubTaskEvents($taskKey)
    {
        $task = $this->tasks[$taskKey];
        $task->setExecutionId($this->getExecutionId());
        $this->registerReportAliveEvent($task);
        $this->registerReportProgressEvent($taskKey);
    }

    /**
     * Calculates overall progress based on current progress for all tasks.
     *
     * @param float $subTaskProgress Progress for current sub task.
     * @param string $subTaskKey FQN of current task.
     *
     * @return void
     */
    protected function calculateProgress($subTaskProgress, $subTaskKey)
    {
        // set current task progress to overall map
        $this->taskProgressMap[$subTaskKey] = $subTaskProgress;

        if (!$this->isProcessCompleted()) {
            $overallProgress = $this->initialProgress;
            foreach ($this->tasksProgressShare as $key => $share) {
                $overallProgress += $this->taskProgressMap[$key] * $share / 100;
            }

            $this->taskProgressMap['overallTaskProgress'] = $overallProgress;
        } else {
            $this->taskProgressMap['overallTaskProgress'] = 100;
        }
    }

    /**
     * Checks if all sub tasks are completed.
     *
     * @return bool TRUE if all tasks are completed; otherwise, FALSE.
     */
    protected function isProcessCompleted()
    {
        foreach (array_keys($this->tasksProgressShare) as $subTaskKey) {
            if ($this->taskProgressMap[$subTaskKey] < 100) {
                return false;
            }
        }

        return true;
    }

    /**
     * Registers "report alive" event listener so that this composite task can broadcast event.
     *
     * @param Task $task A Task for which to register listener.
     *
     * @return void
     */
    private function registerReportAliveEvent(Task $task)
    {
        $self = $this;

        $task->when(
            AliveAnnouncedTaskEvent::CLASS_NAME,
            function () use ($self) {
                $self->reportAlive();
            }
        );
    }

    /**
     * Registers "report progress" event listener so that this composite task can calculate and report overall progress.
     *
     * @param string $taskKey A Task for which to register listener.
     *
     * @return void
     */
    private function registerReportProgressEvent($taskKey)
    {
        $self = $this;
        $task = $this->tasks[$taskKey];

        $task->when(
            TaskProgressEvent::CLASS_NAME,
            function (TaskProgressEvent $event) use ($self, $taskKey) {
                $self->calculateProgress($event->getProgressFormatted(), $taskKey);
                $self->reportProgress($self->taskProgressMap['overallTaskProgress']);
            }
        );
    }
}
