<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\DTO;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject;

class BlacklistFilterConfig extends DataTransferObject
{
    const CLASS_NAME = __CLASS__;

    /**
     * @var string
     */
    private $type;
    /**
     * @var string
     */
    private $rule;

    /**
     * @param string $type
     * @param string $rule
     */
    public function __construct($type, $rule)
    {
        $this->type = $type;
        $this->rule = $rule;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     * Transforms BlacklistFilterConfig instance to array.
     *
     * @inheritDoc
     */
    public function toArray()
    {
        return array(
            'type' => $this->type,
            'rule' => $this->rule
        );
    }

    /**
     * Creates instance of BlacklistFilterConfig from array.
     *
     * @param array<string, string> $data
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\DTO\BlacklistFilterConfig
     */
    public static function fromArray(array $data)
    {
        return new static(
            static::getDataValue($data, 'type'),
            static::getDataValue($data, 'rule')
        );
    }
}
