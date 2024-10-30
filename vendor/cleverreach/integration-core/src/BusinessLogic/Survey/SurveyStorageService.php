<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Survey;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigurationManager;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class SurveyStorageService
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Survey
 */
abstract class SurveyStorageService implements Contracts\SurveyStorageService
{
    /**
     * @param string $type
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigEntity
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    public function setSurveyOpened($type)
    {
        return $this->getConfigurationManager()->saveConfigValue($type . 'FormOpened', true);
    }

    /**
     * @param string $type
     *
     * @return bool
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    public function isSurveyOpened($type)
    {
        return (bool)$this->getConfigurationManager()->getConfigValue($type . 'FormOpened', false);
    }

    /**
     * Returns last poll ID retrieved from CleverReach poll endpoint.
     *
     * @return string|null Poll ID
     * @throws QueryFilterInvalidParamException
     */
    public function getLastPollId()
    {
        return $this->getConfigurationManager()->getConfigValue('lastPollId');
    }

    /**
     * Sets last poll ID retrieved from CleverReach poll endpoint.
     *
     * @param string $pollId Poll ID
     *
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     */
    public function setLastPollId($pollId)
    {
        $this->getConfigurationManager()->saveConfigValue('lastPollId', $pollId);
    }

    /**
     * Retrieves configuration manager.
     *
     * @return ConfigurationManager Configuration Manager instance.
     */
    protected function getConfigurationManager()
    {
        /** @var ConfigurationManager $manager */
        $manager = ServiceRegister::getService(ConfigurationManager::CLASS_NAME);

        return $manager;
    }
}
