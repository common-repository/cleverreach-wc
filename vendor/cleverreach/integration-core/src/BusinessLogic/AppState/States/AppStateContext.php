<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\States;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\Contracts\State;

class AppStateContext
{
    /**
     * @var State
     */
    protected $currentState;
    /**
     * @var State
     */
    protected $previousState;

    /**
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\Contracts\State $currentState
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\Contracts\State $previousState
     */
    public function __construct(State $currentState, State $previousState = null)
    {
        $this->currentState = $currentState;
        $this->previousState = $previousState;
    }

    /**
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\Contracts\State
     */
    public function getCurrentState()
    {
        return $this->currentState;
    }

    /**
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\Contracts\State
     */
    public function getPreviousState()
    {
        return $this->previousState;
    }

    /**
     * @param State|null $state
     *
     * @return void
     */
    public function changeState($state = null)
    {
        $newState = $state ?: $this->currentState->getNext();
        if ($newState->getCode() === $this->currentState->getCode()) {
            return;
        }

        $this->currentState->validateTransition($newState);

        $this->previousState = $this->currentState;
        $this->currentState = $newState;
    }
}
