<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Repositories;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\ORM\Interfaces\ConditionallyDeletes;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Interfaces\RepositoryInterface;

/**
 * Interface EventsBufferEntityRepository
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Repositories
 */
interface EventsBufferEntityRepository extends RepositoryInterface, ConditionallyDeletes
{
}
