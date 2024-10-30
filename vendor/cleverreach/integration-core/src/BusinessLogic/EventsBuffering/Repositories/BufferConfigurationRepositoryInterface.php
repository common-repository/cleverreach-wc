<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Repositories;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Entities\BufferConfiguration;

interface BufferConfigurationRepositoryInterface
{
    const CLASS_NAME = __CLASS__;

    /**
     * Stores buffer configuration in database
     *
     * @param BufferConfiguration $bufferConfiguration
     *
     * @return void
     */
    public function createConfiguration(BufferConfiguration $bufferConfiguration);

    /**
     * Returns buffer configuration for given context
     *
     * @param string $context
     *
     * @return BufferConfiguration|null
     */
    public function getConfiguration($context);

    /**
     * Returns buffer configurations that satisfy given parameters
     *
     * @param integer $fromTimestamp
     * @param bool $hasEvents
     *
     * @return BufferConfiguration[]
     */
    public function getFilteredConfigurations($fromTimestamp, $hasEvents);

    /**
     * Update flag hasEvents for the provided context
     *
     * @param string $context
     * @param bool $hasEvents
     *
     * @return void
     */
    public function updateHasEvents($context, $hasEvents);

    /**
     * Updates next run field for the given context
     *
     * @param string $context
     * @param int $nextRun
     *
     * @return void
     */
    public function updateNextRun($context, $nextRun);

    /**
     * Updates provided fields for the given context
     *
     * @param string $context
     * @param string $intervalType
     * @param int $interval
     * @param int $nextRun
     *
     * @return void
     */
    public function saveInterval($context, $intervalType, $interval, $nextRun);
}
