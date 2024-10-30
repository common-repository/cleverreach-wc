<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\DTO\AppState;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\States\AppStateContext;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\States\AutoConfig;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\States\Offline;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigurationManager;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

class AppStateService implements Contracts\AppStateService
{
    const DEFAULT_STATE_CODE = AutoConfig::STATE_CODE;

    /**
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\States\AppStateContext
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    public function getStateContext()
    {
        /** @var AppState|null $appState */
        $appState = $this->getAppState();
        if ($appState) {
            $currentState = $this->createStateInstance($appState);
            $previousState = $appState->getPrevious() ? $this->createStateInstance($appState->getPrevious()) : null;

            return new AppStateContext($currentState, $previousState);
        }

        return new AppStateContext($this->getDefaultState());
    }

    /**
     * @inheritDoc
     */
    public function setStateContext(AppStateContext $context)
    {
        $appState = new AppState($context->getCurrentState()->getCode(), $context->getCurrentState()->getSubState());
        if ($previous = $context->getPreviousState()) {
            $appState->setPrevious(new AppState($previous->getCode(), $previous->getSubState()));
        }

        $this->getConfigManager()->saveConfigValue('appState', $appState->toArray());
    }

    /**
     * @inheritDoc
     * @param string $newStateCode
     */
    public function changeState($newStateCode)
    {
        $context = $this->getStateContext();
        $context->changeState(StateRegister::getState($newStateCode));
        $this->setStateContext($context);
    }

    /**
     * @inheritDoc
     */
    public function setOffline()
    {
        $context = $this->getStateContext();
        $context->changeState(StateRegister::getState(Offline::STATE_CODE));
        $this->setStateContext($context);
    }

    /**
     * @inheritDoc
     */
    public function setOnline()
    {
        $context = $this->getStateContext();
        if ($context->getCurrentState()->getCode() !== Offline::STATE_CODE) {
            // skip if not in offline mode
            return;
        }

        $newState = $context->getPreviousState() ?: $this->getDefaultState();
        $previousState = StateRegister::getState(Offline::STATE_CODE);

        $this->setStateContext(new AppStateContext($newState, $previousState));
    }

    /**
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\DTO\AppState $appState
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\Contracts\State
     */
    protected function createStateInstance(AppState $appState)
    {
        $state = StateRegister::getState($appState->getStateCode());
        $state->setSubState($appState->getSubStateCode());

        return $state;
    }

    /**
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\DTO\AppState|\CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject|null
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    protected function getAppState()
    {
        $appState = $this->getConfigManager()->getConfigValue('appState');

        return $appState ? AppState::fromArray($appState) : null;
    }

    /**
     * @return ConfigurationManager
     */
    protected function getConfigManager()
    {
        /** @var ConfigurationManager $configurationManager */
        $configurationManager = ServiceRegister::getService(ConfigurationManager::CLASS_NAME);

        return $configurationManager;
    }

    /**
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\Contracts\State
     */
    protected function getDefaultState()
    {
        return StateRegister::getState(static::DEFAULT_STATE_CODE);
    }
}
