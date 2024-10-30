<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Merger;

use InvalidArgumentException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Singleton;

/**
 * Class MergerRegistry
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Merger
 */
class MergerRegistry extends Singleton
{
    /**
     * Singleton instance of this class.
     *
     * @var static
     */
    protected static $instance;
    /**
     * @var Merger[]
     */
    protected $mergers;

    /**
     * MergerRegistry constructor.
     */
    protected function __construct()
    {
        parent::__construct();

        $this->mergers = array();
    }

    /**
     * Register merger class.
     *
     * @param string $type Type of merger.
     * @param callable $delegate Delegate that will give instance of registered merger.
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     *  In case delegate is not a callable.
     */
    public static function register($type, $delegate)
    {
        static::getInstance()->doRegister($type, $delegate);
    }

    /**
     * Retrieves merger instance.
     *
     * If merger is not registered the instance of \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Merger\Merger will be
     * returned.
     *
     * @param string $type Type of merger. Identifier used during registration should be used.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Merger\Merger Instance of merger.
     */
    public static function get($type = '')
    {
        return static::getInstance()->doGet($type);
    }

    /**
     * Register merger class.
     *
     * @param string $type Type of merger.
     * @param callable $delegate Delegate that will give instance of registered merger.
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     *  In case delegate is not a callable.
     */
    protected function doRegister($type, $delegate)
    {
        if (!is_callable($delegate)) {
            throw new InvalidArgumentException("$type delegate is not callable.");
        }

        /** @var Merger $delegate */
        $this->mergers[$type] = $delegate;
    }

    /**
     * Retrieves merger instance.
     *
     * If merger is not registered the instance of \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Merger\Merger will be
     * returned.
     *
     * @param string $type Type of merger. Identifier used during registration should be used.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Merger\Merger Instance of merger.
     */
    protected function doGet($type = '')
    {
        if (empty($this->mergers[$type])) {
            return Merger::getInstance();
        }

        /** @var callable $merger */
        $merger = $this->mergers[$type];

        return call_user_func($merger);
    }
}
