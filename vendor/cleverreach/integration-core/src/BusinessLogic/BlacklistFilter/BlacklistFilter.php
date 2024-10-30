<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\Contracts\BlacklistFilter as BlacklistFilterInterface;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\Contracts\FilterStrategy;

class BlacklistFilter implements BlacklistFilterInterface
{
    /**
     * @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\Contracts\FilterStrategy
     */
    private $filterStrategy;

    /**
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\Contracts\FilterStrategy $filterStrategy
     */
    public function __construct(FilterStrategy $filterStrategy)
    {
        $this->filterStrategy = $filterStrategy;
    }

    /**
     * @inheritDoc
     */
    public function filterEmails(array $emails)
    {
        $filteredEmails = array();
        foreach ($emails as $email => $services) {
            if (!$this->filterStrategy->canPass($email)) {
                $filteredEmails[$email] = $services;
            }
        }

        return $filteredEmails;
    }

    /**
     * @inheritDoc
     */
    public function filterEmail($email)
    {
        if (!$this->filterStrategy->canPass($email)) {
            return $email;
        }

        return null;
    }
}
