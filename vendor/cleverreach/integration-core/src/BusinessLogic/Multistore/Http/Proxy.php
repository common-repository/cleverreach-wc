<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\Http;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Http\Proxy as BaseProxy;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\DTO\AutomationDetails;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\DTO\AutomationSubmit;

/**
 * Class Proxy
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\Http
 */
class Proxy extends BaseProxy
{
    const CLASS_NAME = __CLASS__;

    /**
     * Creates automation details.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\DTO\AutomationSubmit $automation
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\DTO\AutomationDetails
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function create(AutomationSubmit $automation)
    {
        $response = $this->post('automation/createfromtemplate/abandonedcart.json', $automation->toArray());

        return AutomationDetails::fromArray($response->decodeBodyToArray());
    }
}
