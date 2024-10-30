<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Http;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\DTO\AuthInfo;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Http\Proxy;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\HttpClient;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

class AuthProxy extends Proxy
{
    const CLASS_NAME = __CLASS__;

    /**
     * AuthProxy constructor.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\HttpClient $client
     */
    public function __construct(HttpClient $client)
    {
        parent::__construct($client, null);
    }

    /**
     * Retrieves users auth info.
     *
     * @param string $code
     * @param string $redirectUrl
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\DTO\AuthInfo
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function getAuthInfo($code, $redirectUrl)
    {
        $response = $this->post('oauth/token.php', $this->getAuthParameters($code, $redirectUrl));
        $authInfo = $response->decodeBodyToArray();
        $authInfo['expires_in'] = $this->getConfigService()->getTokenLifeTime($authInfo['access_token']);

        return AuthInfo::fromArray($authInfo);
    }

    /**
     * @inheritDoc
     */
    protected function getAuthHeaders()
    {
        return array();
    }

    /**
     * Retrieves auth info parameters.
     *
     * @param string $code
     * @param string $redirectUrl
     *
     * @return array<string,string>
     */
    protected function getAuthParameters($code, $redirectUrl)
    {
        return array(
            'grant_type' => 'authorization_code',
            'client_id' => $this->getConfigService()->getClientId(),
            'client_secret' => $this->getConfigService()->getClientSecret(),
            'code' => $code,
            'redirect_uri' => urlencode($redirectUrl),
        );
    }

    /**
     * Retrieves full request url.
     *
     * @param string $endpoint Endpoint identifier.
     *
     * @return string Full request url.
     */
    protected function getUrl($endpoint)
    {
        return self::BASE_API_URL . ltrim(trim($endpoint), '/');
    }

    /**
     * Retrieves configuration service.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Configuration\Configuration
     */
    protected function getConfigService()
    {
        /** @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Configuration\Configuration $configService */
        $configService = ServiceRegister::getService(Configuration::CLASS_NAME);

        return $configService;
    }
}
