<?php

namespace CleverReach\WooCommerce\IntegrationCore\Infrastructure\AutoTest;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogData;

/**
 * Class AutoTestStatus.
 *
 * @package CleverReach\WooCommerce\IntegrationCore\Infrastructure\AutoTest
 */
class AutoTestStatus extends DataTransferObject
{
    /**
     * The current status of the auto-test task.
     *
     * @var string
     */
    public $taskStatus;
    /**
     * Indicates whether the task finished.
     *
     * @var bool
     */
    public $finished;
    /**
     * Error message, if any.
     *
     * @var string
     */
    public $error;
    /**
     * An array of logs.
     *
     * @var LogData[]
     */
    public $logs;

    /**
     * AutoTestStatus constructor.
     *
     * @param string $taskStatus The current status of the auto-test task.
     * @param bool $finished Indicates whether the task finished.
     * @param string $error Error message, if any.
     * @param LogData[] $logs An array of logs.
     */
    public function __construct($taskStatus, $finished, $error, $logs)
    {
        $this->taskStatus = $taskStatus;
        $this->finished = $finished;
        $this->error = $error;
        $this->logs = $logs;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array(
            'taskStatus' => $this->taskStatus,
            'finished' => $this->finished,
            'error' => $this->error,
            'logs' => $this->logs,
        );
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $data)
    {
        return new static($data['taskStatus'], $data['finished'], $data['error'], $data['logs']);
    }
}
