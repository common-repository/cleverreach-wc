<?php

namespace CleverReach\WooCommerce\IntegrationCore\Infrastructure\AutoTest;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogContextData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Serializer;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task;

/**
 * Class AutoTestTask.
 *
 * @package CleverReach\WooCommerce\IntegrationCore\Infrastructure\AutoTest
 */
class AutoTestTask extends Task
{
    /**
     * Dummy data for the task.
     *
     * @var string
     */
    protected $data;

    /**
     * AutoTestTask constructor.
     *
     * @param string $data Dummy data.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @inheritDoc
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Interfaces\Serializable
     *      Instance of serialized object.
     */
    public static function fromArray(array $array)
    {
        return new static($array['data']);
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array('data' => $this->data);
    }

    /**
     * String representation of object.
     *
     * @return string The string representation of the object or null.
     */
    public function serialize()
    {
        return Serializer::serialize(array($this->data));
    }

    /**
     * Constructs the object.
     *
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     */
    public function unserialize($serialized)
    {
        list($this->data) = Serializer::unserialize($serialized);
    }

    /**
     * Runs task logic.
     */
    public function execute()
    {
        $this->reportProgress(5);
        Logger::logInfo('Auto-test task started');

        $this->reportProgress(50);
        Logger::logInfo('Auto-test task parameters', 'Core', array(new LogContextData('context', $this->data)));

        $this->reportProgress(100);
        Logger::logInfo('Auto-test task ended');
    }
}
