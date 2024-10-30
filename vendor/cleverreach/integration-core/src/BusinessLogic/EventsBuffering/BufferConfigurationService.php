<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Contracts\BufferIntervals;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Entities\BufferConfiguration;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Repositories\BufferConfigurationRepositoryInterface;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Utility\IntervalConverter;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Language\Translator;

class BufferConfigurationService implements Contracts\BufferConfigurationInterface
{
    /**
     * @var BufferConfigurationRepositoryInterface
     */
    protected $bufferConfigurationRepository;

    /**
     * @param BufferConfigurationRepositoryInterface $bufferConfigurationRepository
     */
    public function __construct(BufferConfigurationRepositoryInterface $bufferConfigurationRepository)
    {
        $this->bufferConfigurationRepository = $bufferConfigurationRepository;
    }

    /**
     * @inheritDoc
     */
    public function calculateNextRun($context)
    {
        $savedConfiguration = $this->bufferConfigurationRepository->getConfiguration($context);
        $interval = $savedConfiguration ? $savedConfiguration->getInterval() : 0;

        $this->bufferConfigurationRepository->updateNextRun($context, time() + $interval);
    }

    /**
     * @param string $context
     * @param string $intervalType
     * @param int $customInterval
     * @param int $startTime
     *
     * @return void
     * @throws \InvalidArgumentException If the start time format is not valid for daily intervals.
     */
    public function saveInterval($context, $intervalType, $customInterval = 0, $startTime = 0)
    {
        $this->validateIntervals($intervalType, $customInterval, $startTime);

        $intervalInSeconds = IntervalConverter::getMappedToSeconds($intervalType, $customInterval);

        $nextRun = $this->getNextRun($intervalInSeconds, $intervalType, $startTime)->getTimestamp();

        $configuration = $this->getConfiguration($context);
        if ($configuration === null) {
            $configuration = new BufferConfiguration($context, $intervalType, $intervalInSeconds, $nextRun, false);
            $this->bufferConfigurationRepository->createConfiguration($configuration);

            return;
        }

        $this->bufferConfigurationRepository->saveInterval($context, $intervalType, $intervalInSeconds, $nextRun);
    }

    /**
     * @inheritDoc
     */
    public function updateHasEvents($context, $hasEvents)
    {
        $configuration = $this->getConfiguration($context);
        if ($configuration === null) {
            // stores BufferConfiguration with default (immediate) values
            $configuration = new BufferConfiguration($context, BufferIntervals::IMMEDIATE, 0, time(), $hasEvents);
            $this->bufferConfigurationRepository->createConfiguration($configuration);
        }

        $this->bufferConfigurationRepository->updateHasEvents($context, $hasEvents);
    }

    /**
     * @inheritDoc
     */
    public function getConfiguration($context)
    {
        return $this->bufferConfigurationRepository->getConfiguration($context);
    }

    /**
     * @inheritDoc
     */
    public function getScheduledForExecution()
    {
        $currentTime = new \DateTime();

        return $this->bufferConfigurationRepository->getFilteredConfigurations($currentTime->getTimestamp(), true);
    }

    /**
     * @inheritDoc
     */
    public function getAvailableIntervals()
    {
        $map = array();
        foreach (IntervalConverter::getAllowedIntervalTypes() as $intervalType) {
            $map[$intervalType] = Translator::translate($intervalType);
        }

        return $map;
    }

    /**
     * Calculates the next run time based on the provided interval and start time.
     *
     * @param int $intervalInSeconds The interval in seconds.
     * @param string $intervalType The type of interval (e.g., daily).
     * @param int $startTime The start time for daily intervals, timestamp.
     *
     * @return \DateTime The calculated next run time.
     * @throws \InvalidArgumentException If the start time format is not valid for daily intervals.
     */
    protected function getNextRun($intervalInSeconds, $intervalType, $startTime)
    {
        // For daily intervals, calculate next run based on the start time.
        if ($intervalType === BufferIntervals::DAILY) {
            $nextRun = (new \DateTime());
            $nextRun->setTimestamp($startTime);

            $now = new \DateTime();
            // If the current time is past the next run time, schedule for the next day.
            if ($now > $nextRun) {
                $nextRun->modify('+1 day');
            }

            return $nextRun;
        }

        // For non-daily intervals, calculate next run based on the current time and the interval in seconds.
        $nextRun = new \DateTime();
        $nextRun->modify("+$intervalInSeconds seconds");

        return $nextRun;
    }

    /**
     * Validates input intervals
     *
     * @param string $intervalType
     * @param int $customInterval
     * @param string $startTime
     *
     * @return void
     */
    protected function validateIntervals($intervalType, $customInterval, $startTime)
    {
        $allowedIntervals = IntervalConverter::getAllowedIntervalTypes();
        if (!in_array($intervalType, $allowedIntervals, true)) {
            throw new \InvalidArgumentException(
                "Interval is not supported. Supported intervals:" . implode(', ', $allowedIntervals)
            );
        }

        if ($intervalType === BufferIntervals::CUSTOM && ($customInterval < 0 || $customInterval > 1440)) {
            throw new \InvalidArgumentException("Custom interval should be between 0 and 1440 minutes.");
        }

        if ($intervalType === BufferIntervals::DAILY && !$startTime) {
            throw new \InvalidArgumentException('Start time should not be empty!');
        }
    }
}
