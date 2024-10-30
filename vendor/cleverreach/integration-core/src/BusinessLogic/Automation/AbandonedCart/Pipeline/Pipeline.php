<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Pipeline;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCartTrigger;

abstract class Pipeline
{
    /**
     * List of trigger filters.
     *
     * @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Pipeline\Filter[]
     */
    protected static $filters = array();

    /**
     * Passes trigger through the filter.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCartTrigger $trigger
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Exceptions\FailedToPassFilterException
     */
    public static function execute(AbandonedCartTrigger $trigger)
    {
        foreach (static::getRegisteredFilters() as $filter) {
            $filter->pass($trigger);
        }
    }

    /**
     * Appends the filter to the list of filters.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Pipeline\Filter $filter
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
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Pipeline\Filter $filter
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
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Pipeline\Filter[]
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
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Pipeline\Filter[]
     */
    protected static function getRegisteredFilters()
    {
        return static::$filters;
    }
}
