<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\DTO\Response\Filter;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject;

/**
 * Class FilterCollection
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\DTO
 */
class FilterCollection extends DataTransferObject
{
    /**
     * @var Filter[]
     */
    protected $filters = array();

    /**
     * @return Filter[]
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param Filter[] $filters
     *
     * @return void
     */
    public function setFilters($filters)
    {
        $this->filters = $filters;
    }

    /**
     * @param Filter $filter
     *
     * @return void
     */
    public function addFilter(Filter $filter)
    {
        $this->filters[] = $filter;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $data = array();
        foreach ($this->filters as $filter) {
            $data[] = $filter->toArray();
        }

        return $data;
    }
}
