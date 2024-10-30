<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Http;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\DTO\ConnectionStatus;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Http\Proxy;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

class OauthStatusProxy extends Proxy
{
    const CLASS_NAME = __CLASS__;

    /**
     * Finishes oauth process.
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveUserInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function finishOauth()
    {
        $this->post('oauth/finish.json', $this->getFinishParameters());
    }

    /**
     * Checks if oauth parameters are still valid.
     *
     * @return bool
     */
    public function isOauthCredentialsValid()
    {
        return $this->getConnectionStatus()->isConnected();
    }

    /**
     * Validates connection to the CleverReach
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\DTO\ConnectionStatus
     */
    public function getConnectionStatus()
    {
        try {
            $this->get('debug/validate.json');

            return new ConnectionStatus(true);
        } catch (\Exception $e) {
            return new ConnectionStatus(false, $e->getMessage());
        }
    }

    /**
     * Retrieves finish parameters.
     *
     * @return array<string,mixed>
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveUserInfoException
     */
    private function getFinishParameters()
    {
        return array(
            'finished' => true,
            'name' => $this->authService->getUserInfo()->getFirstName() ?: 'User',
            'brand' => $this->getConfigService()->getIntegrationName(),
            'client_id' => $this->getConfigService()->getClientId(),
        );
    }

    /**
     * Retrieves configuration service.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Configuration\Configuration
     */
    private function getConfigService()
    {
        /** @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Configuration\Configuration $configService */
        $configService = ServiceRegister::getService(Configuration::CLASS_NAME);

        return $configService;
    }
}
