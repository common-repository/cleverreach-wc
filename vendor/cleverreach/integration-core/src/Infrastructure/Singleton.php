<?php

namespace CleverReach\WooCommerce\IntegrationCore\Infrastructure;

use RuntimeException;

/**
 * Base class for all singleton implementations.
 * Every class that extends this class MUST have its own protected static field $instance!
 *
 * @package CleverReach\WooCommerce\IntegrationCore\Infrastructure
 */
abstract class Singleton
{
    /**
     * @var static|null
     */
    protected static $instance;

    /**
     * Hidden constructor.
     */
    protected function __construct()
    {
    }

    /**
     * Returns singleton instance of callee class.
     *
     * @return static Instance of callee class.
     */
    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        if (!(static::$instance instanceof static)) {
            throw new RuntimeException('Invalid singleton instance.');
        }

        return static::$instance;
    }

    /**
     * Resets singleton instance. Required for proper tests.
     *
     * @return void
     */
    public static function resetInstance()
    {
        static::$instance = null;
    }
}
