<?php

namespace CleverReach\WooCommerce\IntegrationCore\Infrastructure\AutoTest;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\RepositoryRegistry;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Singleton;

/**
 * Class AutoTestLogger.
 *
 * @package CleverReach\WooCommerce\IntegrationCore\Infrastructure\AutoConfiguration
 */
class AutoTestLogger extends Singleton implements ShopLoggerAdapter
{
    /**
     * Singleton instance of this class.
     *
     * @var static
     */
    protected static $instance;
    /**
     * Logs a message in system.
     *
     * @param LogData $data Data to log.
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function logMessage(LogData $data)
    {
        $repo = RepositoryRegistry::getRepository(LogData::CLASS_NAME);
        $repo->save($data);
    }

    /**
     * Gets all log entities.
     *
     * @return LogData[] An array of the LogData entities, if any.
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function getLogs()
    {
        /** @var LogData[] $logData */
        $logData = RepositoryRegistry::getRepository(LogData::CLASS_NAME)->select();

        return $logData;
    }

    /**
     * Transforms logs to the plain array.
     *
     * @return mixed[] An array of logs.
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function getLogsArray()
    {
        $result = array();
        foreach ($this->getLogs() as $log) {
            $result[] = $log->toArray();
        }

        return $result;
    }
}
