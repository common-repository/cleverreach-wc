<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\ORM\Interfaces;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\QueryFilter;

/**
 * Interface ConditionallyDeletes
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\ORM\Interfaces
 */
interface ConditionallyDeletes
{
    /**
     * @param \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\QueryFilter|null $queryFilter
     *
     * @return void
     */
    public function deleteWhere(QueryFilter $queryFilter = null);
}
