<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Http;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Http\Proxy as BaseProxy;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO\Event;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO\EventRegisterResult;

/**
 * Class Proxy
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Http
 */
class Proxy extends BaseProxy
{
    const CLASS_NAME = __CLASS__;

    /**
     * Registers webhook event.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO\Event $event
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO\EventRegisterResult
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function registerEvent(Event $event)
    {
        $response = $this->post('hooks/eventhook', $event->toArray());

        return EventRegisterResult::fromArray($response->decodeBodyToArray());
    }

    /**
     * Deletes webhook event in a specific group with specific type.
     *
     * @param string $condition
     * @param string $type One of [form | receiver | automation]
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function deleteEvent($condition, $type)
    {
        $params = http_build_query(array('condition' => $condition));

        $this->delete("hooks/eventhook/$type?$params");
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
}
