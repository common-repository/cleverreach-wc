<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\FilterStrategy;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\Exceptions\WildcardFilterNotValidException;

class WildcardFilterStrategy extends AbstractFilterStrategy
{
    /**
     * @inheritDoc
     */
    public function canPass($email)
    {
        // Escape any special characters in the pattern
        $pattern = preg_quote($this->rule, '/');
        // Replace wildcard (*) with the appropriate regular expression pattern
        // Replace wildcard (?) with the appropriate regular expression pattern
        $pattern = str_replace(array('\*', '\?'), array('.*', '.'), $pattern);
        // Add start and end of string anchors to the regular expression pattern
        $pattern = '/^' . $pattern . '$/';
        // Check if the email matches the regular expression pattern
        return (bool)preg_match($pattern, $email);
    }

    /**
     * Wildcard must be a valid pattern
     *
     * @inheritDoc
     */
    public function validateRule()
    {
        // Use strpos to check for the presence of "*" or "?"
        if (strpos($this->rule, '*') === false && strpos($this->rule, '?') === false) {
            throw new WildcardFilterNotValidException();
        }
    }
}
