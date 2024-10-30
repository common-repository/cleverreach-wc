<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Survey\Contracts;

/**
 * Interface SurveyType
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Survey\Contracts
 */
interface SurveyType
{
    const PLUGIN_INSTALLED = 'plugin_installed';
    const INITIAL_SYNC_FINISHED = 'initial_sync_finished';
    const FIRST_FORM_USED = 'first_form_used';
    const PERIODIC = 'periodic';
}
