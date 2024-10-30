<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Contracts;

interface BufferIntervals
{
    const IMMEDIATE = 'immediate';
    const ONE_MINUTE = 'one_minute';
    const FIVE_MINUTES = 'five_minutes';
    const FIFTEEN_MINUTES = 'fifteen_minutes';
    const ONE_HOUR = 'one_hour';
    const FOUR_HOURS = 'four_hours';
    const TWELVE_HOURS = 'twelve_hours';
    const DAILY = 'daily';
    const CUSTOM = 'custom';

    const DAILY_START_TIME_FORMAT = 'H:i';
}
