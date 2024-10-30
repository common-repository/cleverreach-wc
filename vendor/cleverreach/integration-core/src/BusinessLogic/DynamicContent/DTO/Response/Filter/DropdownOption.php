<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\DTO\Response\Filter;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject;

/**
 * Class DropdownOption
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\DTO
 */
class DropdownOption extends DataTransferObject
{
    /**
     * Dropdown text
     *
     * @var string
     */
    protected $text;
    /**
     * Dropdown value
     *
     * @var string
     */
    protected $value;

    /**
     * DropdownOption constructor.
     *
     * @param string $text
     * @param string $value
     */
    public function __construct($text, $value)
    {
        $this->text = $text;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     *
     * @return void
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return void
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array(
            'text' => $this->text,
            'value' => $this->value,
        );
    }
}
