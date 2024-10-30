<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\InitialSynchronization\Tasks\Composite;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Dashboard\Contracts\DashboardService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\InitialSynchronization\Events\InitialSyncCompletedEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\InitialSynchronization\Tasks\Composite\Components\FieldsSynchronization;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\InitialSynchronization\Tasks\Composite\Components\GroupSynchronization;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\InitialSynchronization\Tasks\Composite\Components\ReceiverSynchronization;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Events\ReceiverEventBus;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Events\ReceiverExportCompleteEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\TaskExecution\Events\TaskCompletedEventBus;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\CompositeTask;

class InitialSyncTask extends CompositeTask
{
    /**
     * InitialSyncTask constructor.
     */
    public function __construct()
    {
        parent::__construct($this->getSubTasks());
    }

    /**
     * @inheritdoc
     */
    public function isArchivable()
    {
        return false;
    }

    public function execute()
    {
        ReceiverEventBus::getInstance()->when(
            ReceiverExportCompleteEvent::CLASS_NAME,
            array($this, 'setReceiverSynchronizationStatics')
        );

        parent::execute();

        TaskCompletedEventBus::getInstance()->fire(new InitialSyncCompletedEvent());
    }

    /**
     * Records receiver synchronization statistics.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Events\ReceiverExportCompleteEvent $event
     *
     * @return void
     */
    public function setReceiverSynchronizationStatics(ReceiverExportCompleteEvent $event)
    {
        $dashboard = $this->getDashboardService();
        $dashboard->setSyncStatisticsDisplayed(false);
        $dashboard->setSyncedReceiversCount($event->getSynchronizedReceiversCount());
    }

    /**
     * @inheritDoc
     */
    protected function createSubTask($taskKey)
    {
        /** @var \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task $task */
        $task = new $taskKey;

        return $task;
    }

    /**
     * Retrieves dashboard service.
     *
     * @return DashboardService
     */
    protected function getDashboardService()
    {
        /** @var DashboardService $dashboardService */
        $dashboardService = ServiceRegister::getService(DashboardService::CLASS_NAME);

        return $dashboardService;
    }

    /**
     * @return array<string,int>
     */
    private function getSubTasks()
    {
        return array(
            GroupSynchronization::CLASS_NAME => 10,
            FieldsSynchronization::CLASS_NAME => 5,
            ReceiverSynchronization::CLASS_NAME => 85,
        );
    }
}
