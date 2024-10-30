<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Mailing\Http;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Http\Proxy as BaseProxy;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Mailing\DTO\Mailing;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Mailing\DTO\MailingDetails;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Mailing\DTO\MailingPreview;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Mailing\DTO\MailingRelease;

/**
 * Class Proxy
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Mailing\Http
 */
class Proxy extends BaseProxy
{
    const CLASS_NAME = __CLASS__;

    /**
     * Sends preview mailing
     *
     * @param string $mailingId
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Mailing\DTO\MailingPreview $preview
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function sendPreview($mailingId, MailingPreview $preview)
    {
        $this->post("mailings.json/$mailingId/sendpreview", $preview->toArray());
    }

    /**
     * Stops previously released mailing
     *
     * @param string $mailingId
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function stopMailing($mailingId)
    {
        $this->post("mailings.json/$mailingId/stop");
    }

    /**
     * Returns mailing by its id
     *
     * @param string $mailingId
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Mailing\DTO\MailingDetails|null
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function getMailing($mailingId)
    {
        $response = $this->get("mailings.json/$mailingId");
        $decodedResponse = $response->decodeBodyToArray();
        if (!empty($decodedResponse['id'])) {
            return MailingDetails::fromArray($decodedResponse);
        }

        return null;
    }

    /**
     * @param mixed $mailingId
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Mailing\DTO\MailingRelease $mailingRelease
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function releaseMailing($mailingId, MailingRelease $mailingRelease)
    {
        $this->post("mailings.json/$mailingId/release", $mailingRelease->toArray());
    }

    /**
     * Creates mailing.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Mailing\DTO\Mailing $mailing
     *
     * @return string|bool ID of created mailing if request, false if not created
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function createMailing(Mailing $mailing)
    {
        $response = $this->post('mailings.json', $mailing->toArray());
        $decodedResponse = $response->decodeBodyToArray();

        if ($decodedResponse['success'] === true) {
            return $decodedResponse['id'];
        }

        return false;
    }

    /**
     * Updates mailing with the given id
     *
     * @param string $mailingId
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Mailing\DTO\Mailing $mailing
     *
     * @return bool|mixed
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function updateMailing($mailingId, Mailing $mailing)
    {
        $response = $this->put("mailings.json/$mailingId", $mailing->toArray());
        $decodedResponse = $response->decodeBodyToArray();

        if ($decodedResponse['success'] === true) {
            return $decodedResponse['id'];
        }

        return false;
    }

    /**
     * Checks if any mailing is present on api.
     *
     * @return bool
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function hasMailing()
    {
        $response = $this->get('mailings.json')->decodeBodyToArray();

        return !empty($response);
    }
}
