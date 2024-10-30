<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\Contracts;

interface BlacklistFilter
{
    const CLASS_NAME = __CLASS__;

    /**
     * Returns NEW array of emails that can pass the filter from the provided list of emails.
     *
     * @param array<string, string> $emails
     *
     * @return array<string, string>
     */
    public function filterEmails(array $emails);

    /**
     * Filter one email
     *
     * @param string $email
     *
     * @return string|null
     */
    public function filterEmail($email);
}
