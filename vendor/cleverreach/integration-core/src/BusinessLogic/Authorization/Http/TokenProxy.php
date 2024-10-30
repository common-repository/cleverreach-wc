<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Http;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Http\Proxy;

/**
 * Class TokenProxy
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Http
 */
class TokenProxy extends Proxy
{
    /**
     * Class name.
     */
    const CLASS_NAME = __CLASS__;

    /**
     * Revokes access token by deleting it.
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function revoke()
    {
        $this->delete('oauth/token.json');
    }
}
