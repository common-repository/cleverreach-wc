<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Contracts\GroupService as BaseService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\DTO\Group;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Http\Proxy;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigurationManager;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\TimeProvider;

abstract class GroupService implements BaseService
{
    /**
     * Group proxy.
     *
     * @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Http\Proxy
     */
    protected $proxy;

    /**
     * Returns group settings type
     *
     * @return string|null
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    public function getReceiverListSettingsType()
    {
        return $this->getConfigurationManager()->getConfigValue('listSettingsType');
    }

    /**
     * Save receiver list settings type
     *
     * @param string $settingsType
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    public function setReceiverListSettingsType($settingsType)
    {
        $this->getConfigurationManager()->saveConfigValue('listSettingsType', $settingsType);
    }

    /**
     * Retrieves persisted group name.
     *
     * @return string persisted group name.
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    public function getName()
    {
        return $this->getConfigurationManager()->getConfigValue('groupName') ?: $this->getDefaultName();
    }

    /**
     * Persists provided group name
     *
     * @param string $name merchant's provided group name
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    public function setName($name)
    {
        $this->getConfigurationManager()->saveConfigValue('groupName', $name);
    }

    /**
     * Retrieves group id.
     *
     * @return string Group id. Empty string if group id is not saved.
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function getId()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->getConfigurationManager()->getConfigValue('groupId', '');
    }

    /**
     * Saves group id.
     *
     * @param string $id Group id to be saved.
     *
     * @return void
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function setId($id)
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->getConfigurationManager()->saveConfigValue('groupId', $id);
    }

    /**
     * Retrieves list of available groups for the current user.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\DTO\Group[] List of available groups.
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function getGroups()
    {
        return $this->getProxy()->getGroups();
    }

    /**
     * Retrieves group by name.
     *
     * @param string $name
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\DTO\Group | null
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function getGroupByName($name)
    {
        $result = null;

        if (empty($name)) {
            return null;
        }

        $groups = $this->getGroups();
        foreach ($groups as $group) {
            if ($group->getName() === $name) {
                $result = $group;

                break;
            }
        }

        return $result;
    }

    /**
     * Creates group with provided name.
     *
     * @param string $name Group name.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\DTO\Group Created group.
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function createGroup($name)
    {
        $group = new Group();
        $group->setName($name);
        $group->setBackup(true);
        $group->setLocked(true);
        $time = $this->getTimeProvider()->getDateTime(time())->format(DATE_ATOM);
        $integrationName = $this->getConfigurationService()->getIntegrationName();
        $clientId = $this->getConfigurationService()->getClientId();
        $url = $this->getConfigurationService()->getSystemUrl();
        $group->setReceiverInfo("[$time] Automatically created by $integrationName $clientId ($url).");

        return $this->getProxy()->createGroup($group);
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

    /**
     * Retrieves group proxy.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Http\Proxy Group proxy instance.
     */
    protected function getProxy()
    {
        if ($this->proxy === null) {
            /** @var Proxy $proxy */
            $proxy = ServiceRegister::getService(Proxy::CLASS_NAME);
            $this->proxy = $proxy;
        }

        return $this->proxy;
    }

    /**
     * Retrieves time provider.
     *
     * @return TimeProvider Time Provider instance.
     */
    protected function getTimeProvider()
    {
        /** @var TimeProvider $timeProvider */
        $timeProvider = ServiceRegister::getService(TimeProvider::CLASS_NAME);

        return $timeProvider;
    }

    /**
     * Retrieves configuration service.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Configuration\Configuration Config service instance.
     */
    protected function getConfigurationService()
    {
        /** @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Configuration\Configuration $configurationService */
        $configurationService = ServiceRegister::getService(Configuration::CLASS_NAME);

        return $configurationService;
    }
}
