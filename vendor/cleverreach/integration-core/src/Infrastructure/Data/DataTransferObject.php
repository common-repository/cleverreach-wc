<?php

namespace CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data;

use RuntimeException;

/**
 * Class DataTransferObject
 *
 * @package CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data
 */
abstract class DataTransferObject
{
    /**
     * Creates instance of the data transfer object from an array.
     *
     * @param mixed[] $data Raw data used for the object instantiation.
     *
     * @return static An instance of the data transfer object.
     *
     * @noinspection PhpDocSignatureInspection
     */
    public static function fromArray(array $data)
    {
        throw new RuntimeException('Method from array not implemented');
    }

    /**
     * Creates list of DTOs from a batch of raw data.
     *
     * @param mixed[] $batch Batch of raw data.
     *
     * @return static[] List of DTO instances.
     */
    public static function fromBatch(array $batch)
    {
        $result = array();

        foreach ($batch as $index => $item) {
            $result[$index] = static::fromArray($item);
        }

        return $result;
    }

    /**
     * Transforms data transfer object to array.
     *
     * @return mixed[] Array representation of data transfer object.
     */
    abstract public function toArray();

    /**
     * Retrieves value from raw data.
     *
     * @param mixed[] $rawData Raw DTO data.
     * @param string $key Data key.
     * @param mixed $default Default value.
     *
     * @return mixed
     */
    protected static function getDataValue(array $rawData, $key, $default = '')
    {
        return isset($rawData[$key]) ? $rawData[$key]: $default;
    }
}
