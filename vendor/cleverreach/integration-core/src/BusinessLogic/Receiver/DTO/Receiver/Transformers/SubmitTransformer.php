<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver\Transformers;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Modifier\Modifier;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\Transformer;

/**
 * Class SubmitTransformer
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver\Transformers
 */
class SubmitTransformer extends Transformer
{
    /**
     * @inheritDoc
     */
    public static function transform(DataTransferObject $transformable)
    {
        $result = $transformable->toArray();
        static::applyModifiers($result);
        $result = array_intersect_key($result, array_flip(static::getAllowedFields()));
        static::trim($result);

        return $result;
    }

    /**
     * Apply defined modifiers to receiver fields.
     *
     * @param array<string,mixed> $result
     *
     * @return void
     */
    protected static function applyModifiers(array &$result)
    {
        if (!empty($result['modifiers'])) {
            foreach ($result['modifiers'] as $modifier) {
                if (in_array($modifier['field'], static::getPrimaryFields(), true)) {
                    static::modifyData($result, Modifier::fromArray($modifier));
                } else {
                    static::modifyData($result['global_attributes'], Modifier::fromArray($modifier));
                }
            }
        }
    }

    /**
     * Retrieves list of primary (non-global) attributes.
     *
     * @return string[]
     */
    protected static function getPrimaryFields()
    {
        return array('tags');
    }

    /**
     * Creates modified value.
     *
     * @param array<string,mixed> $data Reference to a modifiable data.
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Modifier\Modifier $modifier Modifier details.
     *
     * @return void
     */
    protected static function modifyData(array &$data, Modifier $modifier)
    {
        $value = $modifier->getFormattedValue();

        $field = $modifier->getField();
        if (array_key_exists($field, $data) && is_array($data[$field])) {
            $data[$field][] = $value;
        } else {
            $data[$field] = $value;
        }
    }

    /**
     * Retrieves list of submittable keys.
     *
     * @return string[]
     */
    protected static function getAllowedFields()
    {
        return array(
            'email',
            'source',
            'activated',
            'deactivated',
            'registered',
            'global_attributes',
            'tags',
            'orders',
        );
    }
}
