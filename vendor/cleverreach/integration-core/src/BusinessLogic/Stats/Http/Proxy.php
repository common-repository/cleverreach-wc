<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Stats\Http;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Http\Proxy as BaseProxy;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Tag\Tag;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Stats\DTO\Stats;

/**
 * Class Proxy
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Stats\Http
 */
class Proxy extends BaseProxy
{
    const CLASS_NAME = __CLASS__;

    /**
     * @param string $groupId
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Stats\DTO\Stats
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function getStats($groupId)
    {
        $response = $this->get("groups.json/$groupId/stats");

        return Stats::fromArray($response->decodeBodyToArray());
    }

    /**
     * Get the count of receivers with a certain tag
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Tag\Tag $tag
     * @param string|null $groupId
     *
     * @return string
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function countReceivers(Tag $tag, $groupId = null)
    {
        $endpoint = 'tags/count.json?' . $this->buildQuery($tag, $groupId);
        $response = $this->get($endpoint);

        return $response->getBody();
    }

    /**
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Tag\Tag $tag
     * @param string|null $groupId
     *
     * @return string
     */
    private function buildQuery(Tag $tag, $groupId)
    {
        $queryParams = array(
            'tag' => (string)$tag,
        );

        if ($groupId) {
            $queryParams['group_id'] = $groupId;
        }

        return http_build_query($queryParams);
    }
}
