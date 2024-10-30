<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Stats\Contracts;

/**
 * Interface StatsService
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Stats\Contracts
 */
interface StatsService
{
    const CLASS_NAME = __CLASS__;
    /**
     * Returns current number of subscribed recipients
     *
     * @return int
     */
    public function getSubscribed();

    /**
     * Returns current number of unsubscribed recipients
     *
     * @return int
     */
    public function getUnsubscribed();
}
