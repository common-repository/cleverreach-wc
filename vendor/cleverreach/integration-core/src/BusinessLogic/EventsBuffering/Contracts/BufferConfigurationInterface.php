<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Contracts;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Entities\BufferConfiguration;

interface BufferConfigurationInterface
{
    const CLASS_NAME = __CLASS__;

    /**
     * Calculates next run, based on the current time and interval
     *
     * @param string $context
     *
     * @return void
     */
    public function calculateNextRun($context);

    /**
     * Updates interval for the given context
     *
     * @param string $context user identifier
     * @param string $intervalType enum value
     * @param int $customInterval in seconds
     * @param int $startTime timestamp
     *
     * @return void
     */
    public function saveInterval($context, $intervalType, $customInterval = 0, $startTime = 0);

    /**
     * Updates has events flag for the given context
     *
     * @param string $context
     * @param bool $hasEvents
     *
     * @return void
     */
    public function updateHasEvents($context, $hasEvents);

    /**
     * Returns configuration for the provided context
     *
     * @param string $context
     *
     * @return BufferConfiguration|null
     */
    public function getConfiguration($context);

    /**
     * Returns all configurations that are scheduled for execution
     *
     * @return BufferConfiguration[]
     */
    public function getScheduledForExecution();

    /**
     * Returns list of available intervals as key => label
     *
     * @return array<string,string>
     */
    public function getAvailableIntervals();
}
