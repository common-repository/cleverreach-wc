<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Scheduler\Models;

/**
 * Class WeeklySchedule
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Scheduler\Models
 */
class WeeklySchedule extends Schedule
{
    /**
     * Fully qualified name of this class.
     */
    const CLASS_NAME = __CLASS__;
    /**
     * @var string[]
     */
    protected static $daysMap = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
    /**
     * @var string[]
     */
    protected static $monthMap = array(
        'january',
        'february',
        'march',
        'april',
        'may',
        'june',
        'july',
        'august',
        'september',
        'october',
        'november',
        'december',
    );
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
        'lastWeek',
        'weeks',
        'lastUpdateTimestamp',
        'recurring',
        'isEnabled',
    );
    /**
     * Last week flag.
     *
     * @var bool
     */
    protected $lastWeek;
    /**
     * Array of week numbers when task should be queued.
     *
     * @var int[]
     */
    protected $weeks;

    /**
     * Calculates next schedule time.
     *
     * @return \DateTime Next schedule date.
     * @throws \Exception Emits Exception in case of an error while creating DateTime instance.
     */
    protected function calculateNextSchedule()
    {
        $now = $this->now();
        $day = ($this->getDay() ?: 1) % 7;
        $day = $day ?: 7;

        if ($this->isLastWeek()) {
            $year = (int)date('Y', $now->getTimestamp());
            $day = static::$daysMap[$day - 1];
            $monthNumber = (int)date('m', $now->getTimestamp());
            $month = static::$monthMap[$monthNumber - 1];
            $shouldStartAt = strtotime("last {$day} of {$month} {$year}");
            if ($now->getTimestamp() > $shouldStartAt) {
                $monthNumberNext = 1 + $monthNumber % 12;
                $month = static::$monthMap[$monthNumber % 12];
                $str = "last {$day} of ";
                if ($monthNumberNext < $monthNumber) {
                    $str .= 'next';
                    $year++;
                }

                $shouldStartAt = strtotime($str . " $month $year");
            }

            $now->setTimestamp($shouldStartAt ?: 0);
            $now->setTime($this->getHour(), $this->getMinute());

            return $now;
        }

        $currentDay = (int)date('N', $now->getTimestamp());
        $daysToAdd = $day === $currentDay ? 7 : (7 + $day - $currentDay) % 7;

        $nextSchedule = $this->now();
        $nextSchedule->setTimestamp($now->getTimestamp());
        $nextSchedule->setTime($this->getHour(), $this->getMinute());

        // move to day in the week
        $nextSchedule->add(new \DateInterval("P{$daysToAdd}D"));
        $weekNumber = (int)date('W', $nextSchedule->getTimestamp());

        while (!empty($this->weeks) && !in_array($weekNumber, $this->weeks)) {
            // move to next week
            $nextSchedule->add(new \DateInterval('P7D'));
            $weekNumber = (int)date('W', $nextSchedule->getTimestamp());
        }

        return $nextSchedule;
    }

    /**
     * Returns array of week numbers.
     *
     * @return int[] Week numbers.
     */
    public function getWeeks()
    {
        return $this->weeks;
    }

    /**
     * Sets array of week numbers.
     *
     * @param int[] $weeks Week numbers.
     *
     * @return void
     */
    public function setWeeks($weeks)
    {
        $this->weeks = $weeks;
    }

    /**
     * Returns last week flag.
     *
     * @return bool Last week flag.
     */
    public function isLastWeek()
    {
        return $this->lastWeek;
    }

    /**
     * Sets last week flag.
     *
     * @param bool $lastWeek Last week flag.
     *
     * @return void
     */
    public function setLastWeek($lastWeek)
    {
        $this->lastWeek = $lastWeek;
    }
}
