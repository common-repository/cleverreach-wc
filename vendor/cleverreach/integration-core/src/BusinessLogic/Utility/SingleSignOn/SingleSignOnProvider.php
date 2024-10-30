<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Utility\SingleSignOn;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Contracts\AuthorizationService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class SingleSignOnProvider
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Utility
 */
class SingleSignOnProvider
{
    const CLASS_NAME = __CLASS__;

    const FALLBACK_URL = 'https://cleverreach.com/login';

    /**
     * Creates SSO link in format:
     * https://$client_id.$expanded_zone.cleverreach.com/admin/login.php?otp=$otp&oid=$oid&exp=$exp&ref=urlencode($deepLink).
     *
     * @param string $deepLink Address on which will system redirect after successful login.
     *
     * @return string
     *   SSO link.
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveUserInfoException
     */
    public static function getUrl($deepLink)
    {
        /** @var AuthorizationService $authService */
        $authService = ServiceRegister::getService(AuthorizationService::CLASS_NAME);
        $userInfo = $authService->getUserInfo();
        $loginDomain = $userInfo->getLoginDomain();
        if (empty($loginDomain)) {
            return static::FALLBACK_URL;
        }

        $cleverreachToken = $authService->getAuthInfo()->getAccessToken();

        /** @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Configuration\Configuration $configService */
        $configService = ServiceRegister::getService(Configuration::CLASS_NAME);

        $params = array(
            'otp' => OtpProvider::generateOtp($cleverreachToken),
            'oid' => $configService->getClientId(),
            'exp' => self::getExpiryTimestamp($cleverreachToken),
            'ref' => $deepLink,
        );

        return 'https://' . $userInfo->getLoginDomain() . '/admin/login.php?' . http_build_query($params);
    }

    /**
     * Returns token expiry timestamp.
     *
     * @param string $cleverreachToken Token provided by CleverReach.
     *
     * @return int
     *   Token expiry timestamp.
     */
    private static function getExpiryTimestamp($cleverreachToken)
    {
        // cleverreachToken = header64encoded.body64encoded.cripto64encoded
        $tokenArray = explode('.', $cleverreachToken);
        $body64encoded = $tokenArray[1];
        $body = json_decode(base64_decode($body64encoded), true);

        return $body['exp'];
    }
}
