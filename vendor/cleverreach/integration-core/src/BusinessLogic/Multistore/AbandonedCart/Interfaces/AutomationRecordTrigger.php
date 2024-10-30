<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Interfaces;

interface AutomationRecordTrigger
{
    /**
     * Returns automation record id
     *
     * @return int
     */
    public function getRecordId();
}
