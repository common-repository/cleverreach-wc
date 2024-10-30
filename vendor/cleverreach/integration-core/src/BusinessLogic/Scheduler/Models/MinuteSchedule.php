<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Scheduler\Models;

/**
 * Class MinuteSchedule
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Scheduler\Models
 */
class MinuteSchedule extends Schedule
{
    /**
     * Fully qualified name of this class.
     */
    const CLASS_NAME = __CLASS__;

    /**
     * Array of field names.
     *
     * @var string[]
     */
    protected $fields = array(
        'id',
        'queueName',
        'context',
        'minute',
        'hour',
        'day',
        'month',
        'recurring',
        'isEnabled',
        'lastUpdateTimestamp',
        'interval',
    );

    /**
     * Schedule interval in minutes.
     *
     * @var int
     */
    protected $interval = 1;

    /**
     * Returns schedule interval.
     *
     * @return int Interval in minuets.
     */
    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * Sets schedule interval.
     *
     * @param int $interval Interval in minutes.
     *
     * @return void
     */
    public function setInterval($interval)
    {
        $this->interval = $interval;
    }

    /**
     * Calculates next schedule time.
     *
     * @return \DateTime Next schedule date.
     * @throws \Exception Emits Exception in case of an error while creating DateTime instance.
     */
    protected function calculateNextSchedule()
    {
        $interval = new \DateInterval("PT{$this->getInterval()}M");

        return $this->now()->add($interval);
    }
}
