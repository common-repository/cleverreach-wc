<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Pipeline;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCartTrigger;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCartTriggeredLog;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Exceptions\FailedToPassFilterException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigurationManager;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\Operators;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\QueryFilter;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\RepositoryRegistry;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

class AlreadyProcessedFilter extends Filter
{
    /**
     * Checks whether trigger can pass the filter.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCartTrigger $trigger
     *
     * @return void
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Exceptions\FailedToPassFilterException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function pass(AbandonedCartTrigger $trigger)
    {
        $query = new QueryFilter();
        $query->where('cartId', Operators::EQUALS, $trigger->getCartId());
        $query->where('context', Operators::EQUALS, $this->getConfigManager()->getContext());
        $log = $this->getLogRepository()->selectOne($query);
        if ($log !== null) {
            throw new FailedToPassFilterException("Cart [{$trigger->getCartId()}] has already been triggered.");
        }
    }

    /**
     * Retrieves log repository.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Interfaces\RepositoryInterface
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    private function getLogRepository()
    {
        return RepositoryRegistry::getRepository(AbandonedCartTriggeredLog::getClassName());
    }

    /**
     * Retrieves configuration manager.
     *
     * @return ConfigurationManager
     */
    private function getConfigManager()
    {
        /** @var ConfigurationManager $configurationManager */
        $configurationManager = ServiceRegister::getService(ConfigurationManager::CLASS_NAME);

        return $configurationManager;
    }
}
