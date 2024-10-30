<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Utility;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Contracts\BufferIntervals;

class IntervalConverter
{
    /**
     * @var array<string,int>
     */
    public static $intervalMap = array(
        BufferIntervals::IMMEDIATE => 0,
        BufferIntervals::ONE_MINUTE => 60,
        BufferIntervals::FIVE_MINUTES => 300,
        BufferIntervals::FIFTEEN_MINUTES => 900,
        BufferIntervals::ONE_HOUR => 3600,
        BufferIntervals::FOUR_HOURS => 14400,
        BufferIntervals::TWELVE_HOURS => 43200,
        BufferIntervals::DAILY => 86400,
        BufferIntervals::CUSTOM => -1,
    );

    /**
     * Returns interval in seconds mapped from the key
     *
     * @param string $intervalKey
     * @param int $customMinutesInterval
     *
     * @return int
     */
    public static function getMappedToSeconds($intervalKey, $customMinutesInterval)
    {
        $intervalInSeconds = array_key_exists($intervalKey, static::$intervalMap) ?
            static::$intervalMap[$intervalKey] :
            -1;

        if ($intervalInSeconds === -1) {
            $intervalInSeconds = 60 * $customMinutesInterval;
        }

        return $intervalInSeconds;
    }

    /**
     * Returns available interval types
     *
     * @return string[]
     */
    public static function getAllowedIntervalTypes()
    {
        return array_keys(static::$intervalMap);
    }
}
