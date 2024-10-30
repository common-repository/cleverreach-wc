<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Contracts;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCartSettings;

interface AbandonedCartSettingsService
{
    const CLASS_NAME = __CLASS__;

    /**
     * Persists settings.
     *
     * @param AbandonedCartSettings|null $settings
     *
     * @return void
     */
    public function set(AbandonedCartSettings $settings = null);

    /**
     * Retrieves persisted settings.
     *
     * @return AbandonedCartSettings|null
     */
    public function get();
}
