<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\States;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\Contracts\State;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\Exceptions\InvalidStateTransition;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\StateRegister;

class InitialSyncConfig extends State
{
    const STATE_CODE = 'initial_sync_config';

    /**
     * @inheritDoc
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\Exceptions\InvalidStateTransition
     */
    public function validateTransition(State $state)
    {
        if (!in_array($state->getCode(), array(Offline::STATE_CODE, InitialSync::STATE_CODE), true)) {
            throw new InvalidStateTransition('Only offline or initial sync state allowed after initial config');
        }
    }

    /**
     * @inheritDoc
     */
    public function getNext()
    {
        return StateRegister::getState(InitialSync::STATE_CODE);
    }
}
