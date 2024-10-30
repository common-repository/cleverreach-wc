<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Contracts;

interface GroupService
{
    const CLASS_NAME = __CLASS__;

    /**
     * Returns group settings type
     *
     * @return string type of saved group settings (create new, select existing...)
     */
    public function getReceiverListSettingsType();

    /**
     * Save receiver list settings type
     *
     * @param string $settingsType type of saved receiver list settings (create new, select existing...)
     *
     * @return void
     */
    public function setReceiverListSettingsType($settingsType);

    /**
     * Retrieves integration specific group name.
     *
     * @return string Integration provided group name.
     */
    public function getDefaultName();

    /**
     * Retrieves persisted group name.
     *
     * @return string persisted group name.
     */
    public function getName();

    /**
     * Persists provided group name
     *
     * @param string $name merchant's provided group name
     *
     * @return void
     */
    public function setName($name);

    /**
     * Retrieves integration specific blacklisted emails suffix.
     *
     * @NOTICE SUFFIX MUST START WITH DASH (-)!
     *
     * @return string Blacklisted emails suffix.
     */
    public function getBlacklistedEmailsSuffix();

    /**
     * Retrieves group id.
     *
     * @return string Group id. Empty string if group id is not saved.
     */
    public function getId();

    /**
     * Saves group id.
     *
     * @param string $id Group id to be saved.
     *
     * @return void
     */
    public function setId($id);

    /**
     * Retrieves list of all groups for a current user.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\DTO\Group[] List of available groups.
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function getGroups();

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
    public function getGroupByName($name);

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
    public function createGroup($name);
}
