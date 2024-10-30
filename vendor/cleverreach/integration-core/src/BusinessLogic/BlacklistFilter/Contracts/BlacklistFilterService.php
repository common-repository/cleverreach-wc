<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\Contracts;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\DTO\BlacklistFilterConfig;

interface BlacklistFilterService
{
    const CLASS_NAME = __CLASS__;

    /**
     * Filter emails
     *
     * @param array<string, string> $emails
     *
     * @return array<string, string>
     */
    public function filterEmails(array $emails);

    /**
     * Filter email
     *
     * @param string $email
     *
     * @return string|null
     */
    public function filterEmail($email);

    /**
     * Saves blacklist filter configuration
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\DTO\BlacklistFilterConfig $blacklistFilterConfig
     *
     * @return void
     */
    public function saveBlacklistFilterConfig(BlacklistFilterConfig $blacklistFilterConfig);

    /**
     * Retrieves blacklist filter configuration
     *
     * @return BlacklistFilterConfig|null
     */
    public function getBlacklistFilterConfig();

    /**
     * Get filter strategy types
     *
     * @return array<string>
     */
    public function getFilterTypes();
}
