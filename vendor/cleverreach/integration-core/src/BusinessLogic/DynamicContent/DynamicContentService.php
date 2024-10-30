<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\Contracts\DynamicContentService as BaseService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigurationManager;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class DynamicContentService
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent
 */
abstract class DynamicContentService implements BaseService
{
    /**
     * Appends created content id to the list
     *
     * @param string $id
     *
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     */
    public function addCreatedDynamicContentId($id)
    {
        $existingIds = $this->getCreatedDynamicContentIds();
        if (!in_array($id, $existingIds, true)) {
            $existingIds[] = $id;
            $this->getConfigurationManager()->saveConfigValue('dynamicContentIds', json_encode($existingIds));
        }
    }

    /**
     * Returns list of created content ids
     *
     * @return string[]
     *
     * @throws QueryFilterInvalidParamException
     */
    public function getCreatedDynamicContentIds()
    {
        $encodedIds = $this->getConfigurationManager()->getConfigValue('dynamicContentIds');

        return $encodedIds ? json_decode($encodedIds, true) : array();
    }

    /**
     * @return string|null
     * @throws QueryFilterInvalidParamException
     */
    public function getDynamicContentPassword()
    {
        $password = $this->getConfigurationManager()->getConfigValue('dynamicContentPassword');

        return !empty($password) ? $password : $this->createDynamicContentPassword();
    }

    /**
     * @return string
     *
     * @throws QueryFilterInvalidParamException
     */
    public function createDynamicContentPassword()
    {
        $password = hash('md5', (string)time());
        $this->getConfigurationManager()->saveConfigValue('dynamicContentPassword', $password);

        return $password;
    }

    /**
     * Retrieves configuration manager.
     *
     * @return ConfigurationManager Configuration Manager instance.
     */
    protected function getConfigurationManager()
    {
        /** @var ConfigurationManager $configurationManager */
        $configurationManager = ServiceRegister::getService(ConfigurationManager::CLASS_NAME);

        return $configurationManager;
    }
}
