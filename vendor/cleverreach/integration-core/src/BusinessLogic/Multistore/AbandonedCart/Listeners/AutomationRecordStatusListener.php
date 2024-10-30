<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Listeners;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Tasks\Trigger\TriggerCartAutomationTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Contracts\RecoveryEmailStatus;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Interfaces\AutomationRecordService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Interfaces\AutomationRecordTrigger;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\TaskExecution\Events\QueueItemAbortedEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\TaskExecution\Events\QueueItemEnqueuedEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\TaskExecution\Events\QueueItemFailedEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\TaskExecution\Events\QueueItemFinishedEvent;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueItem;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task;

/**
 * Class AutomationRecordStatusListener
 *
 * @package CleverReach\WooCommerce\IntegrationCore\Tests\BusinessLogic\Multistore\AbandonedCart\Listeners
 */
class AutomationRecordStatusListener
{
    /**
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\TaskExecution\Events\QueueItemEnqueuedEvent $event
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToUpdateAutomationRecordException
     */
    public function onEnqueue(QueueItemEnqueuedEvent $event)
    {
        /** @var TriggerCartAutomationTask $task */
        $task = $event->getTask();
        if ($this->isEventTaskValid($task)) {
            $this->updateRecord($task->getRecordId(), RecoveryEmailStatus::SENDING);
        }
    }

    /**
     * @param QueueItemFailedEvent $event
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToUpdateAutomationRecordException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     */
    public function onFail(QueueItemFailedEvent $event)
    {
        /** @var TriggerCartAutomationTask|null $task */
        $task = $event->getQueueItem()->getTask();
        if ($task && $this->isEventTaskValid($task) && $event->getQueueItem()->getStatus() === QueueItem::FAILED) {
            $this->updateRecord($task->getRecordId(), RecoveryEmailStatus::NOT_SENT, $event->getFailureDescription());
        }
    }

    /**
     * @param QueueItemAbortedEvent $event
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToUpdateAutomationRecordException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     */
    public function onAbort(QueueItemAbortedEvent $event)
    {
        /** @var TriggerCartAutomationTask|null $task */
        $task = $event->getQueueItem()->getTask();
        if ($task && $this->isEventTaskValid($task)) {
            $this->updateRecord($task->getRecordId(), RecoveryEmailStatus::NOT_SENT, $event->getAbortDescription());
        }
    }



    /**
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\TaskExecution\Events\QueueItemFinishedEvent $event
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToUpdateAutomationRecordException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     */
    public function onComplete(QueueItemFinishedEvent $event)
    {
        /** @var TriggerCartAutomationTask|null $task */
        $task = $event->getQueueItem()->getTask();
        if ($task && $this->isEventTaskValid($task)) {
            $this->updateRecord($task->getRecordId(), RecoveryEmailStatus::SENT);
        }
    }

    /**
     * @param int $recordId
     * @param string $status
     * @param string $errorMessage
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToUpdateAutomationRecordException
     */
    private function updateRecord($recordId, $status, $errorMessage = '')
    {
        /** @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Interfaces\AutomationRecordService $recordService */
        $recordService = ServiceRegister::getService(AutomationRecordService::CLASS_NAME);
        $record = $recordService->find($recordId);
        if ($record) {
            $record->setStatus($status);
            $record->setErrorMessage($errorMessage);
            if ($status === RecoveryEmailStatus::SENT) {
                $record->setSentTime(new \DateTime());
            }

            $recordService->update($record);
        }
    }

    /**
     * @param \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task $task
     *
     * @return bool
     */
    private function isEventTaskValid(Task $task)
    {
        return $task instanceof AutomationRecordTrigger;
    }
}
