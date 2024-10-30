<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Modifier;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject;

/**
 * Class Modifier
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Modifier
 */
class Modifier extends DataTransferObject
{
    /**
     * Modified field identifier.
     *
     * @var string
     */
    protected $field;
    /**
     * Modification magnitude.
     *
     * @var mixed
     */
    protected $value;
    /**
     * @var string
     */
    protected $type;

    /**
     * Modifier constructor.
     *
     * @param string $field
     * @param mixed $value
     */
    public function __construct($field, $value)
    {
        $this->field = $field;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param string $field
     *
     * @return void
     */
    public function setField($field)
    {
        $this->field = $field;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     *
     * @return void
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Modifier\Modifier
     */
    public static function fromArray(array $data)
    {
        $modifier = new static($data['field'], $data['value']);
        $modifier->type = $data['type'];

        return $modifier;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array(
            'type' => $this->getType(),
            'field' => $this->getField(),
            'value' => $this->getValue(),
        );
    }

    /**
     * Return concatenated type and value fields if both type and value are not null, otherwise null.
     *
     * @return string|null
     */
    public function getFormattedValue()
    {
        return $this->value ? $this->type . $this->value : null;
    }
}
