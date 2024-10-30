<?php

namespace CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data;

/**
 * Class Transformer
 *
 * @package CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data
 */
class Transformer
{
    /**
     * Transforms data transfer object to different format.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject $transformable Object to be transformed.
     *
     * @return mixed[] Transformed result.
     *
     */
    public static function transform(DataTransferObject $transformable)
    {
        return $transformable->toArray();
    }

    /**
     * Transforms a batch of transformable object.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject[] $batch Batch of transformable objects.
     *
     * @return mixed[] Batch of transformed objects.
     */
    public static function batchTransform($batch)
    {
        $result = array();

        if (!is_array($batch)) {
            return $result;
        }

        foreach ($batch as $index => $transformable) {
            $result[$index] = static::transform($transformable);
        }

        return $result;
    }

    /**
     * Trims empty arrays or null values.
     *
     * @param mixed[] $data
     *
     * @return void
     */
    protected static function trim(array &$data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                static::trim($data[$key]);
            }

            if ($value === null || (is_array($value) && empty($value))) {
                unset($data[$key]);
            }
        }
    }
}
