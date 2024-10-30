<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Http;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Http\Proxy as BaseProxy;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Blacklist\Blacklist;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver\Transformers\SubmitTransformer;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Blacklist\Transformers\SubmitTransformer as BlackListTransformer;

class Proxy extends BaseProxy
{
    /**
     * Class name.
     */
    const CLASS_NAME = __CLASS__;

    /**
     * Deletes receiver by email.
     *
     * @param string $email Receiver's email as an identifier.
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function deleteReceiver($email)
    {
        $this->delete("receivers.json/$email");
    }

    /**
     * Deletes multiple receivers by the email
     *
     * @param string[] $emails
     *
     * @return void
     */
    public function deleteReceivers(array $emails)
    {
        $this->post('receivers/delete.json', array(
            'receivers' => $emails
        ));
    }

    /**
     * Removes receiver from a blacklist.
     *
     * @param string $email Receiver identifier.
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function whitelist($email)
    {
        $email = urlencode($email);
        $this->delete("blacklist.json/$email");
    }

    /**
     * Adds receiver to blacklist.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Blacklist\Blacklist $blacklist object, with email and content
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function blacklist(Blacklist $blacklist)
    {
        $this->post('blacklist.json/', BlackListTransformer::transform($blacklist));
    }

    /**
     * Retrieves list of blacklisted emails for a given group.
     *
     * @return string[] List of retrieved blacklisted emails.
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function getBlacklisted()
    {
        $response = $this->get('blacklist.json');

        return array_map(function ($item) {
            return $item['email'];
        }, $response->decodeBodyToArray());
    }

    /**
     * Performs upsertplus action on the receivers endpoint.
     * For more details please
     * @see https://rest.cleverreach.com/explorer/v3#!/groups-v3/upsertplus_post
     *
     * @param mixed $groupId
     * @param Receiver[] $receivers
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function upsertPlus($groupId, array $receivers)
    {
        $this->post("groups.json/{$groupId}/receivers/upsertplus", SubmitTransformer::batchTransform($receivers));
    }

    /**
     * Retrieves receiver by given group id and email
     *
     * @param $groupId
     * @param $email
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver|null
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function findReceiverByEmail($groupId, $email)
    {
        $response = $this->post("receivers/filter.json", array(
                "groups" => array($groupId),
                "rules" => array(
                    array("field" => "email", "logic" => "eq", "condition" => $email),
                ),
            )
        )->decodeBodyToArray();

        return isset($response[0]) ? Receiver::fromArray($response[0]) : null;
    }

    /**
     * Retrieves receiver;
     *
     * @param string $groupId
     * @param string $receiverId
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function getReceiver($groupId, $receiverId)
    {
        $response = $this->get("groups.json/{$groupId}/receivers/{$receiverId}")->decodeBodyToArray();

        return Receiver::fromArray($response);
    }
}
