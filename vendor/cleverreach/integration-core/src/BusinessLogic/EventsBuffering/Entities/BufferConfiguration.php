<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Entities;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Configuration\EntityConfiguration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Configuration\IndexMap;

class BufferConfiguration extends DataTransferObject
{
    const CLASS_NAME = __CLASS__;

    /**
     * @var string
     */
    protected $context;
    /**
     * @var string
     */
    protected $intervalType;
    /**
     * @var int
     */
    protected $interval;

    /**
     * @var int
     */
    protected $nextRun;
    /**
     * @var bool
     */
    protected $hasEvents;

    /**
     * @param string $context
     * @param string $intervalType
     * @param int $interval
     * @param int $nextRun
     * @param bool $hasEvents
     */
    public function __construct($context, $intervalType, $interval, $nextRun, $hasEvents)
    {
        $this->context = $context;
        $this->intervalType = $intervalType;
        $this->interval = $interval;
        $this->nextRun = $nextRun;
        $this->hasEvents = $hasEvents;
    }

    /**
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param string $context
     *
     * @return void
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * @return string
     */
    public function getIntervalType()
    {
        return $this->intervalType;
    }

    /**
     * @param string $intervalType
     *
     * @return void
     */
    public function setIntervalType($intervalType)
    {
        $this->intervalType = $intervalType;
    }

    /**
     * @return int
     */
    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * @param int $interval
     *
     * @return void
     */
    public function setInterval($interval)
    {
        $this->interval = $interval;
    }

    /**
     * @return int
     */
    public function getNextRun()
    {
        return $this->nextRun;
    }

    /**
     * @param int $nextRun
     *
     * @return void
     */
    public function setNextRun($nextRun)
    {
        $this->nextRun = $nextRun;
    }

    /**
     * @return bool
     */
    public function isHasEvents()
    {
        return $this->hasEvents;
    }

    /**
     * @param bool $hasEvents
     *
     * @return void
     */
    public function setHasEvents($hasEvents)
    {
        $this->hasEvents = $hasEvents;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray()
    {
        return array(
            'context' => $this->context,
            'intervalType' => $this->intervalType,
            'interval' => $this->interval,
            'nextRun' => $this->nextRun,
            'hasEvents' => $this->hasEvents,
        );
    }

    /**
     * @param array<string,mixed> $data
     *
     * @return BufferConfiguration
     */
    public static function fromArray(array $data)
    {
        return new static(
            static::getDataValue($data, 'context'),
            static::getDataValue($data, 'intervalType'),
            static::getDataValue($data, 'interval'),
            static::getDataValue($data, 'nextRun'),
            static::getDataValue($data, 'hasEvents')
        );
    }
}
