<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\States;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\Contracts\State;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\Exceptions\InvalidStateTransition;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\StateRegister;

class Welcome extends State
{
    const STATE_CODE = 'welcome';

    /**
     * @inheritDoc
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\Exceptions\InvalidStateTransition
     */
    public function validateTransition(State $state)
    {
        if ($state->getCode() !== InitialSyncConfig::STATE_CODE) {
            throw new InvalidStateTransition('Only initial sync config state allowed after welcome');
        }
    }

    /**
     * @inheritDoc
     */
    public function getNext()
    {
        return StateRegister::getState(InitialSyncConfig::STATE_CODE);
    }
}
