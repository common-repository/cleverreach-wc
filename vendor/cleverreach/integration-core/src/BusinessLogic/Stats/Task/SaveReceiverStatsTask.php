<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Stats\Task;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Scheduler\ScheduledTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Stats\Contracts\SnapshotService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class SaveReceiverStatsTas
 *  * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Stats\Task
 */
class SaveReceiverStatsTask extends ScheduledTask
{
    /**
     * @inheritDoc
     */
    public function execute()
    {
        $this->reportProgress(5);

        /** @var SnapshotService $snapshotService */
        $snapshotService = ServiceRegister::getService(SnapshotService::CLASS_NAME);

        $snapshotService->createSnapshot();
        $this->reportProgress(50);

        $snapshotService->remove();
        $this->reportProgress(100);
    }
}
