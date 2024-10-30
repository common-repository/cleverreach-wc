<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SecondarySynchronization\Contracts;

interface SecondarySyncEnqueueService
{
    const CLASS_NAME = __CLASS__;

    /**
     * Enqueues SecondarySyncTask if conditions are met
     *
     * @return void
     */
    public function enqueueSecondarySync();
}
