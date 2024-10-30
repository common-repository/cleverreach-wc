<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Segment\DTO;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject;

class Rule extends DataTransferObject
{
    /**
     * Field that the rule is targeting.
     *
     * @var string
     */
    protected $field;
    /**
     * Rule logic function @link(https://rest.cleverreach.com/explorer/v3#!/receivers-v3/runtimeFilter_post)
     *
     * @var string
     */
    protected $logic;
    /**
     * @var string
     */
    protected $condition;
    /**
     * @var string
     */
    protected $operator;

    /**
     * Rule constructor.
     *
     * @param string $condition
     * @param string $field
     * @param string $logic
     * @param string $operator
     */
    public function __construct($condition, $field = 'tags', $logic = 'contains', $operator = 'AND')
    {
        $this->condition = $condition;
        $this->field = $field;
        $this->logic = $logic;
        $this->operator = $operator;
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
     * @return string
     */
    public function getLogic()
    {
        return $this->logic;
    }

    /**
     * @param string $logic
     *
     * @return void
     */
    public function setLogic($logic)
    {
        $this->logic = $logic;
    }

    /**
     * @return string
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * @param string $condition
     *
     * @return void
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;
    }

    /**
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @param string $operator
     *
     * @return void
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;
    }

    /**
     * @inheritDoc
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Segment\DTO\Rule
     */
    public static function fromArray(array $data)
    {
        return new self(
            static::getDataValue($data, 'condition', ''),
            static::getDataValue($data, 'field', 'tags'),
            static::getDataValue($data, 'logic', 'eq'),
            static::getDataValue($data, 'operator', 'AND')
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array(
            'condition' => $this->getCondition(),
            'field' => $this->getField(),
            'logic' => $this->getLogic(),
            'operator' => $this->getOperator(),
        );
    }
}
