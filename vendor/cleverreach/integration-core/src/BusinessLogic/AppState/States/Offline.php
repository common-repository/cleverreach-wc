<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\States;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\Contracts\State;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\Exceptions\InvalidStateTransition;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\StateRegister;

class Offline extends State
{
    const STATE_CODE = 'offline';

    /**
     * @inheritDoc
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\Exceptions\InvalidStateTransition
     */
    public function validateTransition(State $state)
    {
        if (!in_array($state->getCode(), array(InitialSyncConfig::STATE_CODE, InitialSync::STATE_CODE, Dashboard::STATE_CODE), true)) {
            throw new InvalidStateTransition('Only initial sync config, initial sync and dashboard states allowed after offline');
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
