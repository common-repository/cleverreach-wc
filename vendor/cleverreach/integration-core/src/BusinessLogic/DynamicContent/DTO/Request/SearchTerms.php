<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\DTO\Request;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject;

/**
 * Class SearchTerms
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\DTO\Request
 */
class SearchTerms extends DataTransferObject
{
    /**
     * @var array<string,mixed>
     */
    protected $searchTerms = array();

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function add($key, $value)
    {
        $this->searchTerms[$key] = $value;
    }

    /**
     * Returns value by its key
     *
     * @param string $key
     *
     * @return mixed|string
     */
    public function getValue($key)
    {
        return static::getDataValue($this->searchTerms, $key, null);
    }

    /**
     * @return array<string,mixed>
     */
    public function getSearchTerms()
    {
        return $this->searchTerms;
    }

    /**
     * @param array<string,mixed> $searchTerms
     *
     * @return void
     */
    public function setSearchTerms($searchTerms)
    {
        $this->searchTerms = $searchTerms;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array(
            'searchTerms' => $this->searchTerms,
        );
    }
}
