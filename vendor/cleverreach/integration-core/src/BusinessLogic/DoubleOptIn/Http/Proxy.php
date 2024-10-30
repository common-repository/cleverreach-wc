<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DoubleOptIn\Http;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DoubleOptIn\DTO\DoubleOptInEmail;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DoubleOptIn\DTO\Transformers\SubmitTransformer;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Http\Proxy as BaseProxy;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Class Proxy
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DoubleOptIn\Http
 */
class Proxy extends BaseProxy
{
    const CLASS_NAME = __CLASS__;

    /**
     * @param DoubleOptInEmail $email
     *
     * @return void
     *
     * @throws FailedToRefreshAccessToken
     * @throws FailedToRetrieveAuthInfoException
     * @throws HttpCommunicationException
     * @throws HttpRequestException
     */
    public function sendDoubleOptInEmail(DoubleOptInEmail $email)
    {
        $endpoint = "forms.json/{$email->getFormId()}/send/{$email->getType()}";

        $this->post($endpoint, SubmitTransformer::transform($email));
    }
}
