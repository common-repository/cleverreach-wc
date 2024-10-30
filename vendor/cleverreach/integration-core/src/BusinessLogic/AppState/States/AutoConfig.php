<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\States;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\Contracts\State;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\Exceptions\InvalidStateTransition;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\StateRegister;

class AutoConfig extends State
{
    const STATE_CODE = 'autoconfig';

    /**
     * @inheritDoc
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\Exceptions\InvalidStateTransition
     */
    public function validateTransition(State $state)
    {
        if ($state->getCode() !== Welcome::STATE_CODE) {
            throw new InvalidStateTransition('Only welcome state allowed after autoconfig');
        }
    }

    /**
     * @inheritDoc
     *
     * @return Welcome|State
     */
    public function getNext()
    {
        return StateRegister::getState(Welcome::STATE_CODE);
    }
}
