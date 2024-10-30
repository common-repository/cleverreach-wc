<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SupportConsole\Contracts;

/**
 * Interface SupportService
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SupportConsole
 */
interface SupportService
{
    const CLASS_NAME = __CLASS__;
    /**
     * Return system configuration parameters
     *
     * @return array<string, mixed>
     */
    public function get();

    /**
     * Updates system configuration parameters
     *
     * @param array<string, mixed> $payload
     *
     * @return mixed
     */
    public function update(array $payload);
}
