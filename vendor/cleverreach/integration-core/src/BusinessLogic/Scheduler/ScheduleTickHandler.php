<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Scheduler;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogContextData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueItem;

/**
 * Class ScheduleTickHandler
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Scheduler
 */
class ScheduleTickHandler
{
    const CLASS_NAME = __CLASS__;

    /**
     * Queues ScheduleCheckTask.
     *
     * @return void
     */
    public function handle()
    {
        /** @var QueueService $queueService */
        $queueService = ServiceRegister::getService(QueueService::CLASS_NAME);
        /** @var Configuration $configService */
        $configService = ServiceRegister::getService(Configuration::CLASS_NAME);
        $task = $queueService->findLatestByType('ScheduleCheckTask');
        $threshold = $configService->getSchedulerTimeThreshold();

        if ($task && in_array($task->getStatus(), array(QueueItem::QUEUED, QueueItem::IN_PROGRESS), true)) {
            return;
        }

        if ($task === null || $task->getQueueTimestamp() + $threshold < time()) {
            $task = new ScheduleCheckTask();
            try {
                $queueService->enqueue($configService->getSchedulerQueueName(), $task);
            } catch (QueueStorageUnavailableException $ex) {
                Logger::logError(
                    'Failed to enqueue task ' . $task->getType(),
                    'Core',
                    array(new LogContextData('trace', $ex->getTraceAsString()))
                );
            }
        }
    }
}
