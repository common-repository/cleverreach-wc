<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Tasks\Trigger\Filter;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\DTO\Trigger;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Entities\AutomationRecord;

/**
 * Class FilterChain
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Tasks\Trigger\Filter
 */
class FilterChain
{
    /**
     * List of trigger filters.
     *
     * @var Filter[]
     */
    protected static $filters = array();

    /**
     * Passes record and trigger through registered filters.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Entities\AutomationRecord $record
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\DTO\Trigger $trigger
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToPassFilterException
     */
    public static function execute(AutomationRecord $record, Trigger $trigger)
    {
        foreach (static::getRegisteredFilters() as $filter) {
            $filter->pass($record, $trigger);
        }
    }

    /**
     * Appends the filter to the list of filters.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Tasks\Trigger\Filter\Filter $filter
     *
     * @return void
     */
    public static function append(Filter $filter)
    {
        static::$filters[] = $filter;
    }

    /**
     * Prepends the filter to the list of filters.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Tasks\Trigger\Filter\Filter $filter
     *
     * @return void
     */
    public static function prepend(Filter $filter)
    {
        array_unshift(static::$filters, $filter);
    }

    /**
     * Retrieves currently registered filters.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Tasks\Trigger\Filter\Filter[]
     */
    public static function peek()
    {
        return static::$filters;
    }

    /**
     * Removes registered filters.
     *
     * @return void
     */
    public static function reset()
    {
        static::$filters = array();
    }

    /**
     * Retrieves filters.
     *
     * @return Filter[]
     */
    protected static function getRegisteredFilters()
    {
        return static::$filters;
    }
}
