<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\Contracts;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\States\AppStateContext;

interface AppStateService
{
    const CLASS_NAME = __CLASS__;

    /**
     * Retrieves current application state and creates application context for current state.
     * If there is no saved state in the DB, the default - autoconfig state will be used.
     *
     * @return AppStateContext
     */
    public function getStateContext();

    /**
     * Persists current application state context.
     *
     * @param AppStateContext $context
     *
     * @return void
     */
    public function setStateContext(AppStateContext $context);

    /**
     * Helper method that changes the application state.
     *
     * @param string $newStateCode code identifier of new state
     *
     * @return void
     */
    public function changeState($newStateCode);

    /**
     * Helper method that puts the application in the offline mode.
     *
     * @return void
     */
    public function setOffline();

    /**
     * Helper method that get the application out of the offline mode.
     * Application state will be one that preceded the offline mode, if previous state is unknown the
     * application will be initialized to default - autoconfigure state
     *
     * @return void
     */
    public function setOnline();
}
