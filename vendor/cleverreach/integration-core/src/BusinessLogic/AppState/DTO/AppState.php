<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\DTO;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject;

class AppState extends DataTransferObject
{
    /**
     * @var string
     */
    protected $stateCode;
    /**
     * @var string
     */
    protected $subStateCode;
    /**
     * @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\DTO\AppState|null
     */
    protected $previous;

    /**
     * @param string $stateCode
     * @param string $subStateCode
     */
    public function __construct($stateCode, $subStateCode)
    {
        $this->stateCode = $stateCode;
        $this->subStateCode = $subStateCode;
    }

    /**
     * @return string
     */
    public function getStateCode()
    {
        return $this->stateCode;
    }

    /**
     * @return string
     */
    public function getSubStateCode()
    {
        return $this->subStateCode;
    }

    /**
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\DTO\AppState|null
     */
    public function getPrevious()
    {
        return $this->previous;
    }

    /**
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\AppState\DTO\AppState|null $previous
     *
     * @return void
     */
    public function setPrevious($previous)
    {
        $this->previous = $previous;
    }

    /**
     * @inheritDoc
     *
     * @param array<string,mixed> $data
     *
     * @return static
     */
    public static function fromArray(array $data)
    {
        $appState = new static(
            static::getDataValue($data, 'stateCode'),
            static::getDataValue($data, 'subStateCode', null)
        );

        if (!empty($data['previous'])) {
            $appState->previous = static::fromArray($data['previous']);
        }

        return $appState;
    }

    /**
     * @inheritDoc
     *
     * @return array<string,mixed>
     */
    public function toArray()
    {
        return array(
            'stateCode' => $this->stateCode,
            'subStateCode' => $this->subStateCode,
            'previous' => $this->previous ? $this->previous->toArray() : null,
        );
    }
}
