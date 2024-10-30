<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\States;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\Contracts\State;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\Exceptions\InvalidStateTransition;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\StateRegister;

class InitialSync extends State
{
    const STATE_CODE = 'initial_sync';

    /**
     * @inheritDoc
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\Exceptions\InvalidStateTransition
     */
    public function validateTransition(State $state)
    {
        if (!in_array($state->getCode(), array(Offline::STATE_CODE, Dashboard::STATE_CODE), true)) {
            throw new InvalidStateTransition('Only offline or dashboard state allowed after initial sync');
        }
    }

    /**
     * @inheritDoc
     */
    public function getNext()
    {
        return StateRegister::getState(Dashboard::STATE_CODE);
    }
}
