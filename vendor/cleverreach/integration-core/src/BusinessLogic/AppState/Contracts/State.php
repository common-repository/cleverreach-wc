<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\Contracts;

abstract class State
{
    const STATE_CODE = null;
    /**
     * @var string
     */
    protected $code;
    /**
     * @var string
     */
    protected $subState;

    /**
     * State constructor
     */
    public function __construct()
    {
        $this->code = static::STATE_CODE;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getSubState()
    {
        return $this->subState;
    }

    /**
     * @param string $subState
     *
     * @return void
     */
    public function setSubState($subState)
    {
        $this->subState = $subState;
    }

    /**
     * Validates states transition
     *
     * @param State $state
     *
     * @return void
     */
    abstract public function validateTransition(State $state);

    /**
     * Returns the next state
     *
     * @return State
     */
    abstract public function getNext();
}
