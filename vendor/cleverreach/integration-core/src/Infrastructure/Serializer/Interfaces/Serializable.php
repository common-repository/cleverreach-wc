<?php

namespace CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Interfaces;

/**
 * Interface Serializable
 *
 * @package CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Interfaces
 */
interface Serializable extends \Serializable
{
    /**
     * Transforms array into an serializable object,
     *
     * @param mixed[] $array Data that is used to instantiate serializable object.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Interfaces\Serializable
     *      Instance of serialized object.
     */
    public static function fromArray(array $array);

    /**
     * Transforms serializable object into an array.
     *
     * @return mixed[] Array representation of a serializable object.
     */
    public function toArray();
}
