<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\InitialSynchronization\Tasks\Composite\Components;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\ReceiverSyncTask;

class ReceiverSynchronization extends InitialSyncSubTask
{
    const CLASS_NAME = __CLASS__;

    /**
     * ReceiverSynchronization constructor.
     */
    public function __construct()
    {
        parent::__construct($this->getSubTasks());
    }

    /**
     * Retrieves sub tasks.
     *
     * @return array<string,int>
     */
    protected function getSubTasks()
    {
        return array(
            ReceiverSyncTask::CLASS_NAME => 100,
        );
    }
}
