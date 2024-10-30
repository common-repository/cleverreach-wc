<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\States;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\Contracts\State;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\Exceptions\InvalidStateTransition;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\StateRegister;

class Dashboard extends State
{
    const STATE_CODE = 'dashboard';

    /**
     * @inheritDoc
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\Exceptions\InvalidStateTransition
     */
    public function validateTransition(State $state)
    {
        if ($state->getCode() !== Offline::STATE_CODE) {
            throw new InvalidStateTransition('Only offline state allowed after dashboard');
        }
    }

    /**
     * @inheritDoc
     */
    public function getNext()
    {
        return StateRegister::getState(static::STATE_CODE);
    }
}
