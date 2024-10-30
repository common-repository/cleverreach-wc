<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\FilterStrategy;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\Exceptions\StaticFilterNotValidException;

class StaticFilterStrategy extends AbstractFilterStrategy
{
    /**
     * @inheritDoc
     */
    public function canPass($email)
    {
        $blacklistedEmails = array_map('trim', explode(',', $this->rule));

        return in_array($email, $blacklistedEmails, true);
    }

    /**
     * Static filter should be up to 500 coma-separated emails
     *
     * @inheritDoc
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\Exceptions\StaticFilterNotValidException
     */
    public function validateRule()
    {
        $blacklistedEmails = array_map('trim', explode(',', $this->rule));
        if (count($blacklistedEmails) > 500) {
            throw new StaticFilterNotValidException();
        }
    }
}
