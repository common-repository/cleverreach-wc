<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\Contracts\AppStateService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\StateRegister;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\States\InitialSyncConfig;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Contracts\AuthorizationService as BaseService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Contracts\RegistrationService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\DTO\AuthInfo;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\DTO\UserInfo;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Events\AuthorizationEventBus;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Events\ConnectionLostEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveUserInfoException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Http\OauthStatusProxy;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Http\RefreshProxy;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Http\Proxy;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigurationManager;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

abstract class AuthorizationService implements BaseService
{
    /**
     * @param string $lang
     *
     * @param bool $isRefresh
     *
     * @return string
     */
    public function getAuthIframeUrl($lang = 'en', $isRefresh = false)
    {
        $registerData = $this->getRegistrationService()->getData();
        $authUrl = Proxy::BASE_API_URL . 'oauth/authorize.php';
        $parameters = array(
            'response_type' => 'code',
            'grant' => 'basic',
            'client_id' => $this->getConfigService()->getClientId(),
            'redirect_uri' => $this->getRedirectURL($isRefresh),
            'bg' => $this->getAuthIframeColor(),
            'lang' => $lang,
        );

        if (!empty($registerData)) {
            $parameters['registerdata'] = $registerData;
        }

        $authUrl .= '?' . http_build_query($parameters);

        if ($isRefresh) {
            $authUrl .= '#login';
        }

        return $authUrl;
    }

    /**
     * Retrieves color code of authentication iframe background.
     *
     * @return string
     *     Color code.
     */
    public function getAuthIframeColor()
    {
        return 'ffffff';
    }

    /**
     * Retrieves valid auth info.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\DTO\AuthInfo
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    public function getAuthInfo()
    {
        $savedInfo = $this->getConfigurationManager()->getConfigValue('authInfo', array());
        if (empty($savedInfo)) {
            throw new FailedToRetrieveAuthInfoException('Failed to retrieve auth info.');
        }

        $authInfo = AuthInfo::fromArray($savedInfo);

        if (time() >= $authInfo->getAccessTokenDuration()) {
            $authInfo = $this->refreshAuthInfo($authInfo);
        }

        return $authInfo;
    }

    /**
     * Sets auth info.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\DTO\AuthInfo $authInfo
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    public function setAuthInfo($authInfo = null)
    {
        $this->getConfigurationManager()->saveConfigValue('authInfo', $authInfo ? $authInfo->toArray() : null);
    }

    /**
     * Retrieves user info.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\DTO\UserInfo
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveUserInfoException
     */
    public function getUserInfo()
    {
        $savedAuthInfo = $this->getConfigurationManager()->getConfigValue('userInfo', array());

        if (empty($savedAuthInfo)) {
            throw new FailedToRetrieveUserInfoException('Failed to retrieve user info.');
        }

        return UserInfo::fromArray($savedAuthInfo);
    }

    /**
     * Sets user info.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\DTO\UserInfo $userInfo
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    public function setUserInfo($userInfo = null)
    {
        $this->getConfigurationManager()->saveConfigValue('userInfo', $userInfo ? $userInfo->toArray() : null);
        if ($userInfo) {
            /** @var AppStateService $appStateService */
            $appStateService = ServiceRegister::getService(AppStateService::CLASS_NAME);
            $context = $appStateService->getStateContext();
            $context->changeState();

            $appStateService->setStateContext($context);
        }
    }

    /**
     * Saves user offline status.
     *
     * @param bool $isOffline
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    public function setIsOffline($isOffline)
    {
        if (!$this->isOffline() && $isOffline) {
            AuthorizationEventBus::getInstance()->fire(new ConnectionLostEvent());
        }

        $this->getConfigurationManager()->saveConfigValue('isOffline', $isOffline);
        /** @var AppStateService $appStateService */
        $appStateService = ServiceRegister::getService(AppStateService::CLASS_NAME);

        $isOffline ? $appStateService->setOffline() : $appStateService->setOnline();
    }

    /**
     * Provides cashed value for the offline mode status.
     *
     * @NOTE This value can be outdated. For fresh value please @see getFreshOfflineStatus
     *
     * @return bool Flag that indicates whether the user is offline or not.
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    public function isOffline()
    {
        return (bool)$this->getConfigurationManager()->getConfigValue('isOffline', false);
    }

    /**
     * Attempts to refresh offline status for the user. Provides refreshed offline mode status.
     *
     * @NOTE Refresh implies TWO API calls and ONE database write.
     *       This operation can have HIGH performance impact.
     *       For more performant option @see isOffline.
     *
     * The offline status will be refreshed only if the CleverReach API is available.
     *
     * @return boolean
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    public function getFreshOfflineStatus()
    {
        if ($this->getApiStatusProxy()->isAPIActive()) {
            $status = $this->getOauthStatusProxy()->isOauthCredentialsValid();
            $this->setIsOffline(!$status);
        }

        return $this->isOffline();
    }

    /**
     * Refreshes auth info.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\DTO\AuthInfo $authInfo
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\DTO\AuthInfo
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    protected function refreshAuthInfo(AuthInfo $authInfo)
    {
        try {
            $authInfo = $this->getRefreshProxy()->refreshAuthInfo($authInfo->getRefreshToken());
        } catch (\Exception $e) {
            $this->setIsOffline(true);
            throw new FailedToRefreshAccessToken($e->getMessage());
        }

        $this->setIsOffline(false);
        $this->setAuthInfo($authInfo);

        return $authInfo;
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
     * Retrieves Refresh proxy.
     *
     * @return RefreshProxy
     */
    protected function getRefreshProxy()
    {
        /** @var RefreshProxy $proxy */
        $proxy = ServiceRegister::getService(RefreshProxy::CLASS_NAME);

        return $proxy;
    }

    /**
     * Retrieves RegistrationService
     *
     * @return RegistrationService
     */
    protected function getRegistrationService()
    {
        /** @var RegistrationService $registrationService */
        $registrationService = ServiceRegister::getService(RegistrationService::CLASS_NAME);

        return $registrationService;
    }

    /**
     * Retrieves Configuration
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
     * Provides oauth status proxy.
     *
     * @return OauthStatusProxy
     */
    protected function getOauthStatusProxy()
    {
        /** @var OauthStatusProxy $oAuthStatusProxy */
        $oAuthStatusProxy = ServiceRegister::getService(OauthStatusProxy::CLASS_NAME);

        return $oAuthStatusProxy;
    }

    /**
     * Provides api status proxy.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\API\Http\Proxy
     */
    protected function getApiStatusProxy()
    {
        /** @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\API\Http\Proxy $apiStatusProxy */
        $apiStatusProxy = ServiceRegister::getService(\CleverReach\WooCommerce\IntegrationCore\BusinessLogic\API\Http\Proxy::CLASS_NAME);

        return $apiStatusProxy;
    }
}
