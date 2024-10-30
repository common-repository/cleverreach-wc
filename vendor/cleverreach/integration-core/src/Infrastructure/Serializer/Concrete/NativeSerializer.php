<?php

namespace CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Concrete;

use Exception;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Serializer;

/**
 * Class NativeSerializer
 *
 * @package CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Concrete
 */
class NativeSerializer extends Serializer
{
    /**
     * Serializes data.
     *
     * @param mixed $data Data to be serialized.
     *
     * @return string String representation of the serialized data.
     */
    protected function doSerialize($data)
    {
        return serialize($data);
    }

    /**
     * Unserializes data.
     *
     * @param string $serialized Serialized data.
     *
     * @return mixed Unserialized data.
     */
    protected function doUnserialize($serialized)
    {
        try {
            $unserialized = unserialize($serialized);
        } catch (Exception $e) {
            return null;
        }

        return $unserialized;
    }
}
