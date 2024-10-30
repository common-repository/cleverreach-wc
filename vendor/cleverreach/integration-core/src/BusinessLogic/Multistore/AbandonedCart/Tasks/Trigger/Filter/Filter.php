<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Tasks\Trigger\Filter;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\DTO\Trigger;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Entities\AutomationRecord;

abstract class Filter
{
    /**
     * Checks if the record and cart data satisfy necessary requirements
     * before the mail can be sent.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Entities\AutomationRecord $record
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\DTO\Trigger $cartData
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToPassFilterException
     *
     * @return void
     */
    abstract public function pass(AutomationRecord $record, Trigger $cartData);
}
