<?php

namespace CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\DTO;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject;

/**
 * Class Options. Represents HTTP options set for Request by HttpClient.
 *
 * @package CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\DTO
 */
class Options extends DataTransferObject
{
    /**
     * Name of the option.
     *
     * @var mixed
     */
    private $name;
    /**
     * Value of the option.
     *
     * @var mixed
     */
    private $value;

    /**
     * Options constructor.
     *
     * @param mixed $name Name of the option.
     * @param mixed $value Value of the option.
     */
    public function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * Gets name of the option.
     *
     * @return mixed Name of the option.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets value of the option.
     *
     * @return mixed Value of the option.
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array(
            'name' => $this->getName(),
            'value' => $this->getValue(),
        );
    }

    /**
     * @inheritDoc
     *
     * @return Options Transformed object.
     */
    public static function fromArray(array $raw)
    {
        return new static($raw['name'], $raw['value']);
    }
}