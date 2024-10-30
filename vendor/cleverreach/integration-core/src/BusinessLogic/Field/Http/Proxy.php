<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Http;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO\Field;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO\Field\Transformers\SubmitTransformer;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Http\Proxy as BaseProxy;

class Proxy extends BaseProxy
{
    /**
     * Class name.
     */
    const CLASS_NAME = __CLASS__;

    /**
     * Retrieves list of global fields.
     *
     * @return Field[]
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function getGlobalFields()
    {
        $response = $this->get('attributes.json');

        return Field::fromBatch($response->decodeBodyToArray());
    }

    /**
     * Updates global field.
     *
     * @param string $id Field id.
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO\Field $field Field updated data.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO\Field
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function updateField($id, Field $field)
    {
        $response = $this->put("attributes.json/{$id}", SubmitTransformer::transform($field));

        return Field::fromArray($response->decodeBodyToArray());
    }

    /**
     * Creates global field.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO\Field $field Global field data.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\DTO\Field
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function createField(Field $field)
    {
        $response = $this->post('attributes.json', SubmitTransformer::transform($field));

        return Field::fromArray($response->decodeBodyToArray());
    }
}
