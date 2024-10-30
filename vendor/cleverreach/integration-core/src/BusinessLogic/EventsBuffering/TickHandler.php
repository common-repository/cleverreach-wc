<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Contracts\BufferConfigurationInterface;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Tasks\BufferCheckTask;
use Exception;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogContextData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Interfaces\Priority;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueService;

/**
 * Class TickHandler
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering
 */
class TickHandler
{
    const CLASS_NAME = __CLASS__;

    /**
     * Queues Buffer check task.
     *
     * @return void
     */
    public function handle()
    {
        try {
            /** @var QueueService $queueService */
            $queueService = ServiceRegister::getService(QueueService::CLASS_NAME);

            $dueBuffers = $this->getBufferConfigurationService()->getScheduledForExecution();
            foreach ($dueBuffers as $dueBuffer) {
                $queueService->enqueue(
                    'BufferCheck-'. $dueBuffer->getContext(),
                    $this->getBufferCheckTaskInstance(),
                    $dueBuffer->getContext(),
                    Priority::HIGH
                );

                $this->getBufferConfigurationService()->calculateNextRun($dueBuffer->getContext());
            }
        } catch (Exception $exception) {
            Logger::logError(
                'Failed to enqueue task BufferCheckTask',
                'Core',
                array(new LogContextData('trace', $exception->getTraceAsString()))
            );
        }
    }

    /**
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Tasks\BufferCheckTask
     */
    protected function getBufferCheckTaskInstance()
    {
        return new BufferCheckTask();
    }

    /**
     * @return BufferConfigurationInterface
     */
    private function getBufferConfigurationService()
    {
        /** @var  BufferConfigurationInterface $service */
        $service = ServiceRegister::getService(BufferConfigurationInterface::CLASS_NAME);

        return $service;
    }
}
