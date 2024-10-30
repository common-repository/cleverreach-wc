<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Http;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\DTO\UserInfo;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Http\Proxy;

class UserProxy extends Proxy
{
    const CLASS_NAME = __CLASS__;

    /**
     * Retrieves current User Info.
     *
     * @return UserInfo
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function getUserInfo()
    {
        $response = $this->get('debug/whoami.json');

        return UserInfo::fromArray($response->decodeBodyToArray());
    }

    /**
     * Retrieves user's language.
     *
     * @param string $userId
     *
     * @return string
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function getUserLanguage($userId)
    {
        $response = $this->get("clients.json/$userId/users");
        $response = $response->decodeBodyToArray();

        return $response[0]['lang'];
    }
}
