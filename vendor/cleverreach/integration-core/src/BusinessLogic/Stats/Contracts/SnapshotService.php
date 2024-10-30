<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Stats\Contracts;

/**
 * Interface SnapshotService
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Stats\Contracts
 */
interface SnapshotService
{
    const CLASS_NAME = __CLASS__;
    /**
     * Creates a snapshot of the given subscribed and unsubscribed recipients
     *
     * @return void
     */
    public function createSnapshot();

    /**
     * Retrieves saved stats from the past n days
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Stats\Entity\Stats[]
     */
    public function getSnapshots();

    /**
     * Removes stats older than defined interval
     *
     * @return void
     */
    public function remove();

    /**
     * Returns interval in days
     *
     * @return int
     */
    public function getInterval();

    /**
     * Sets interval in days
     *
     * @param int $days
     *
     * @return void
     */
    public function setInterval($days);
}
