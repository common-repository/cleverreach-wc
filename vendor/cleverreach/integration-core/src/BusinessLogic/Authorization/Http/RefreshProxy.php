<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Http;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\DTO\AuthInfo;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Http\Proxy;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\HttpClient;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

class RefreshProxy extends Proxy
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
     * @param string $refreshToken
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\DTO\AuthInfo
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function refreshAuthInfo($refreshToken)
    {
        $response = $this->post('oauth/token.php', $this->getRefreshParameters($refreshToken));
        $authInfo = $response->decodeBodyToArray();
        $authInfo['expires_in'] = $this->getConfigService()->getTokenLifeTime($authInfo['access_token']);

        return AuthInfo::fromArray($authInfo);
    }

    /**
     * Retrieves auth headers.
     *
     * @return array<string>
     */
    protected function getAuthHeaders()
    {
        $identity = base64_encode($this->getConfigService()->getClientId() . ':'
            . $this->getConfigService()->getClientSecret());

        return array('Authorization: Basic '. $identity);
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
     * Retrieves refresh auth info parameters.
     *
     * @param string $refreshToken
     *
     * @return array<string,string>
     */
    private function getRefreshParameters($refreshToken)
    {
        return array(
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken
        );
    }
}
