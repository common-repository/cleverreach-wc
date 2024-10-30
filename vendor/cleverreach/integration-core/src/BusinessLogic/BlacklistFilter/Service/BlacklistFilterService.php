<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\Service;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\BlacklistFilter;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\Contracts\BlacklistFilterService as BlacklistServiceInterface;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\Contracts\FilterStrategyType;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\DTO\BlacklistFilterConfig;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\Factory\FilterStrategyFactory;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigurationManager;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

class BlacklistFilterService implements BlacklistServiceInterface
{
    /**
     * @var array<string, BlacklistFilterConfig|null>
     */
    public static $CACHED_CONFIGURATION = array();

    /**
     * @inheritDoc
     */
    public function filterEmails(array $emails)
    {
        $filter = $this->getFilter();
        if ($filter) {
            return $filter->filterEmails($emails);
        }

        return $emails;
    }

    /**
     * @inheritDoc
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    public function filterEmail($email)
    {
        $filter = $this->getFilter();
        if ($filter) {
            return $filter->filterEmail($email);
        }

        return $email;
    }

    /**
     * @inheritDoc
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\DTO\BlacklistFilterConfig $blacklistFilterConfig
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\Exceptions\StaticFilterNotValidException
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\Exceptions\WildcardFilterNotValidException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    public function saveBlacklistFilterConfig(BlacklistFilterConfig $blacklistFilterConfig)
    {
        $filterStrategy = FilterStrategyFactory::create($blacklistFilterConfig);
        $filterStrategy->validateRule();

        $this->getConfigurationManager()->saveConfigValue('blacklistFilter', $blacklistFilterConfig->toArray());
        self::$CACHED_CONFIGURATION[$this->getConfigurationManager()->getContext()] = $blacklistFilterConfig;
    }

    /**
     * @inheritDoc
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\DTO\BlacklistFilterConfig|null
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    public function getBlacklistFilterConfig()
    {
        $context = $this->getConfigurationManager()->getContext();

        if (array_key_exists($context, self::$CACHED_CONFIGURATION)) {
            return self::$CACHED_CONFIGURATION[$context];
        }

        $blacklistFilterConfig = $this->loadBlacklistFilterConfig();

        self::$CACHED_CONFIGURATION[$context] = $blacklistFilterConfig;

        return $blacklistFilterConfig;
    }

    /**
     * @inheritDoc
     */
    public function getFilterTypes()
    {
        return array(
            FilterStrategyType::STATIC_TYPE,
            FilterStrategyType::WILDCARD_TYPE,
        );
    }

    /**
     * @return BlacklistFilter|null
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    protected function getFilter()
    {
        $filterConfig = $this->getBlacklistFilterConfig();
        if ($filterConfig) {
            $filterStrategy = FilterStrategyFactory::create($filterConfig);

            return new BlacklistFilter($filterStrategy);
        }

        return null;
    }

    /**
     * Retrieve blacklist filter configuration from database
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\DTO\BlacklistFilterConfig|null
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    protected function loadBlacklistFilterConfig()
    {
        $configValue = $this->getConfigurationManager()->getConfigValue('blacklistFilter');

        return $configValue ? BlacklistFilterConfig::fromArray($configValue) : null;
    }

    /**
     * Retrieves configuration manager.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigurationManager Configuration Manager instance.
     */
    protected function getConfigurationManager()
    {
        /** @var ConfigurationManager $configurationManager */
        $configurationManager = ServiceRegister::getService(ConfigurationManager::CLASS_NAME);

        return $configurationManager;
    }
}
