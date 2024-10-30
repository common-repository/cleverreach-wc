<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Http;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\DTO\Group;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Transformers\SubmitTransformer;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Http\Proxy as BaseProxy;

class Proxy extends BaseProxy
{
    const CLASS_NAME = __CLASS__;

    /**
     * Retrieves groups for current user.
     *
     * @return Group[] List of available groups.
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function getGroups()
    {
        $response = $this->get('groups.json');

        return Group::fromBatch($response->decodeBodyToArray());
    }

    /**
     * Creates group with a given name.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\DTO\Group $group
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\DTO\Group Created group instance.
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function createGroup(Group $group)
    {
        $response = $this->post('groups.json', SubmitTransformer::transform($group));

        return Group::fromArray($response->decodeBodyToArray());
    }
}
