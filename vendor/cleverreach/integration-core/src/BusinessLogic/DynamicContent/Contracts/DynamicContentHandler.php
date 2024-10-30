<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\Contracts;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\DTO\Request\DynamicContentRequest;

/**
 * Interface DynamicContentHandler
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\Contracts
 */
interface DynamicContentHandler
{
    /**
     * Handles request to the dynamic content endpoint
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\DTO\Request\DynamicContentRequest $request
     *
     * @return mixed[]
     */
    public function handle(DynamicContentRequest $request);
}
