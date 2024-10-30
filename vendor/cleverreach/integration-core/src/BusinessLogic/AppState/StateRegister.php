<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\Contracts\State;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\Exceptions\StateNotRegisteredException;
use InvalidArgumentException;

/**
 * Class StateNotRegisteredException
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\Exceptions
 */
class StateRegister
{
    /**
     * State register instance.
     *
     * @var StateRegister
     */
    private static $instance;
    /**
     * Array of registered states.
     *
     * @var array<string,mixed>
     */
    protected $states;

    /**
     * StateRegister constructor.
     *
     * @param array<string,mixed> $states
     *
     * @throws \InvalidArgumentException
     *  In case delegate of a registered state is not a callable.
     */
    protected function __construct($states = array())
    {
        if (!empty($states)) {
            foreach ($states as $stateCode => $state) {
                $this->register($stateCode, $state);
            }
        }

        self::$instance = $this;
    }

    /**
     * Getting state register instance
     *
     * @return StateRegister
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * Gets state for specified state code.
     *
     * @param string $stateCode state code. Should be a constant defined within the concrete state class.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\Contracts\State Instance of state.
     * @noinspection PhpDocMissingThrowsInspection
     */
    public static function getState($stateCode)
    {
        // Unhandled exception warning suppressed on purpose so that all classes using state
        // would not need @throws tag.
        /** @noinspection PhpUnhandledExceptionInspection */
        return self::getInstance()->get($stateCode);
    }

    /**
     * Registers states with delegate as second parameter which represents function for creating new state instance.
     *
     * @param string $stateCode state code. Should be a constant defined within the concrete state class.
     * @param callable $delegate Delegate that will give instance of registered state.
     *
     * @return void
     * @throws \InvalidArgumentException
     *  In case delegate is not a callable.
     *
     */
    public static function registerState($stateCode, $delegate)
    {
        self::getInstance()->register($stateCode, $delegate);
    }

    /**
     * Register state class.
     *
     * @param string $stateCode state code. Should be a constant defined within the concrete state class.
     * @param callable $delegate Delegate that will give instance of registered state.
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     *  In case delegate is not a callable.
     */
    protected function register($stateCode, $delegate)
    {
        if (!is_callable($delegate)) {
            throw new InvalidArgumentException("$stateCode delegate is not callable.");
        }

        $this->states[$stateCode] = $delegate;
    }

    /**
     * Getting state instance.
     *
     * @param string $stateCode state code. Should be a constant defined within the concrete state class.
     *
     * @return State Instance of state
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\Exceptions\StateNotRegisteredException
     */
    protected function get($stateCode)
    {
        if (empty($this->states[$stateCode])) {
            throw new StateNotRegisteredException($stateCode);
        }

        return call_user_func($this->states[$stateCode]);
    }
}
