<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\TaskExecution;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\API\Http\Proxy;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Contracts\AuthorizationService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Tasks\Composite\ConnectTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Scheduler\ScheduleCheckTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\TaskExecution\Events\QueueItemAbortedEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\TaskExecution\Events\QueueItemEnqueuedEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\TaskExecution\Events\QueueItemFailedEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\TaskExecution\Events\QueueItemFinishedEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\TaskExecution\Events\QueueItemRequeuedEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\TaskExecution\Events\QueueItemStartedEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\TaskExecution\Events\QueueItemStateTransitionEventBus;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Utility\Tasks\TaskCleanupTask;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\ExecutionRequirementsNotMetException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Interfaces\Priority;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueItem;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueService as BaseService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\Events\Event;

/**
 * Class QueueService
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\TaskExecution
 */
class QueueService extends BaseService
{
    const CLASS_NAME = __CLASS__;

    /**
     * @inheritDoc
     *
     * @param string $queueName
     * @param \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task $task
     * @param string $context
     * @param int $priority
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueItem
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    public function enqueue($queueName, Task $task, $context = '', $priority = Priority::NORMAL)
    {
        $item = parent::enqueue($queueName, $task, $context, $priority);
        $this->fireStateTransitionEvent(new QueueItemEnqueuedEvent($queueName, $task, $context, $priority));

        return $item;
    }

    /**
     * @inheritDoc
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueItem $queueItem
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    public function start(QueueItem $queueItem)
    {
        parent::start($queueItem);
        $this->fireStateTransitionEvent(new QueueItemStartedEvent($queueItem));
    }

    public function finish(QueueItem $queueItem)
    {
        parent::finish($queueItem);
        $this->fireStateTransitionEvent(new QueueItemFinishedEvent($queueItem));
    }

    /**
     * @inheritDoc
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueItem $queueItem
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    public function requeue(QueueItem $queueItem)
    {
        parent::requeue($queueItem);
        $this->fireStateTransitionEvent(new QueueItemRequeuedEvent($queueItem));
    }

    /**
     * @inheritDoc
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueItem $queueItem
     * @param string $abortDescription
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    public function abort(QueueItem $queueItem, $abortDescription)
    {
        parent::abort($queueItem, $abortDescription);
        $this->fireStateTransitionEvent(new QueueItemAbortedEvent($queueItem, $abortDescription));
    }

    /**
     * @inheritDoc
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueItem $queueItem
     * @param string $failureDescription
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    public function fail(QueueItem $queueItem, $failureDescription)
    {
        parent::fail($queueItem, $failureDescription);
        $this->fireStateTransitionEvent(new QueueItemFailedEvent($queueItem, $failureDescription));
    }

    /**
     * Performs execution requirements validation validation.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueItem $queueItem
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\ExecutionRequirementsNotMetException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     */
    public function validateExecutionRequirements(QueueItem $queueItem)
    {
        if (!$this->getApiStatusProxy()->isAPIActive()) {
            throw new ExecutionRequirementsNotMetException('API not operational.');
        }

        if (!$this->requiresAuthorization($queueItem)) {
            return;
        }

        if (!$this->getAuthService()->getFreshOfflineStatus()) {
            return;
        }

        throw new ExecutionRequirementsNotMetException('User is offline.');
    }

    /**
     * Checks if task requires authorization for execution.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueItem $queueItem
     *
     * @return bool
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     */
    protected function requiresAuthorization(QueueItem $queueItem)
    {
        $authorizationFreeTasks = array(
            ConnectTask::getClassName(),
            ScheduleCheckTask::getClassName(),
            TaskCleanupTask::getClassName(),
        );

        return !in_array($queueItem->getTaskType(), $authorizationFreeTasks, true);
    }

    /**
     * @param Event $event
     *
     * @return void
     */
    public function fireStateTransitionEvent(Event $event)
    {
        QueueItemStateTransitionEventBus::getInstance()->fire($event);
    }

    /**
     * Retrieves api status proxy.
     *
     * @return Proxy
     */
    private function getApiStatusProxy()
    {
        /** @var Proxy $proxy */
        $proxy = ServiceRegister::getService(Proxy::CLASS_NAME);

        return $proxy;
    }

    /**
     * Retrieves authorization service.
     *
     * @return AuthorizationService
     */
    private function getAuthService()
    {
        /** @var AuthorizationService $authService */
        $authService = ServiceRegister::getService(AuthorizationService::CLASS_NAME);

        return $authService;
    }
}
