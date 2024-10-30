<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Report\Http;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Report\DTO\Report;

/**
 * Class Proxy
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Report\Http
 */
class Proxy extends \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Http\Proxy
{
    const CLASS_NAME = __CLASS__;

    /**
     * Returns certain report
     *
     * @param string $mailingId id of mailing
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Report\DTO\Report
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function getReport($mailingId)
    {
        $response = $this->get("reports.json/$mailingId");

        return Report::fromArray($response->decodeBodyToArray());
    }
}
