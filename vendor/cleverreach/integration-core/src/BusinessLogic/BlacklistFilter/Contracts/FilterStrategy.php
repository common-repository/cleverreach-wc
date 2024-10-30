<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\Contracts;

interface FilterStrategy
{
    /**
     * Check if email can pass the filtering strategy.
     *
     * @param string $email
     *
     * @return bool
     */
    public function canPass($email);

    /**
     * Validate filter configuration
     * Static filter should be up to 500 coma-separated emails and Wildcard must be a valid pattern
     *
     * @return mixed
     */
    public function validateRule();
}
