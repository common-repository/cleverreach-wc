<?php

namespace CleverReach\WooCommerce\IntegrationCore\Infrastructure\AutoTest;

use Exception;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Exceptions\StorageNotAccessibleException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogContextData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\Operators;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\QueryFilter;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\RepositoryRegistry;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueItem;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueService;

/**
 * Class AutoTestService.
 *
 * @package CleverReach\WooCommerce\IntegrationCore\Infrastructure\AutoTest
 */
class AutoTestService
{
    /**
     * Configuration service instance.
     *
     * @var Configuration
     */
    private $configService;

    /**
     * Starts the auto-test.
     *
     * @return int The queue item ID.
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Exceptions\StorageNotAccessibleException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    public function startAutoTest()
    {
        try {
            $this->setAutoTestMode(true);
            $this->deletePreviousLogs();
            Logger::logInfo('Start auto-test');
        } catch (Exception $e) {
            throw new StorageNotAccessibleException('Cannot start the auto-test because storage is not accessible.');
        }

        $this->logHttpOptions();

        /** @var QueueService $queueService */
        $queueService = ServiceRegister::getService(QueueService::CLASS_NAME);
        $queueItem = $queueService->enqueue('Auto-test', new AutoTestTask('DUMMY TEST DATA'));

        return $queueItem->getId();
    }

    /**
     * Activates the auto-test mode and registers the necessary components.
     *
     * @param bool $persist Indicates whether to store the mode change in configuration.
     *
     * @return void
     */
    public function setAutoTestMode($persist = false)
    {
        Logger::resetInstance();
        ServiceRegister::registerService(
            ShopLoggerAdapter::CLASS_NAME,
            function () {
                return AutoTestLogger::getInstance();
            }
        );

        if ($persist) {
            $this->getConfigService()->setAutoTestMode(true);
        }
    }

    /**
     * Gets the status of the auto-test task.
     *
     * @param int $queueItemId The ID of the queue item that started the task.
     *
     * @return AutoTestStatus The status of the auto-test task.
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryClassException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function getAutoTestTaskStatus($queueItemId = 0)
    {
        $this->setAutoTestMode();

        $filter = new QueryFilter();
        if ($queueItemId) {
            $filter->where('id', Operators::EQUALS, $queueItemId);
        } else {
            $filter->where('taskType', Operators::EQUALS, 'AutoTestTask');
            $filter->orderBy('queueTime', 'DESC');
        }

        $status = '';
        $item = RepositoryRegistry::getQueueItemRepository()->selectOne($filter);
        if ($item) {
            if ($item->getStatus() === QueueItem::QUEUED && $item->getQueueTimestamp() < time() - 30) {
                // if item is queued and task runner did not start it within 30 seconds, task expired
                Logger::logError('Auto-test task did not finish within expected time frame.');

                $status = 'timeout';
            } else {
                $status = $item->getStatus();
            }
        }

        return new AutoTestStatus(
            $status,
            in_array($status, array('timeout', QueueItem::COMPLETED, QueueItem::FAILED), true),
            $status === 'timeout' ? 'Task could not be started.' : '',
            AutoTestLogger::getInstance()->getLogs()
        );
    }

    /**
     * Resets the auto-test mode.
     * When auto-test finishes, this is needed to reset the flag in configuration service and
     * re-initialize shop logger. Otherwise, logs and async calls will still use auto-test mode.
     *
     * @param callable $loggerInitializerDelegate Delegate that will give instance of the shop logger service.
     *
     * @return void
     */
    public function stopAutoTestMode($loggerInitializerDelegate)
    {
        $this->getConfigService()->setAutoTestMode(false);
        ServiceRegister::registerService(ShopLoggerAdapter::CLASS_NAME, $loggerInitializerDelegate);
        Logger::resetInstance();
    }

    /**
     * Deletes previous auto-test logs.
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    protected function deletePreviousLogs()
    {
        $repo = RepositoryRegistry::getRepository(LogData::getClassName());
        $logs = $repo->select();
        foreach ($logs as $log) {
            $repo->delete($log);
        }
    }

    /**
     * Logs current HTTP configuration options.
     *
     * @return void
     */
    protected function logHttpOptions()
    {
        $testDomain = parse_url($this->getConfigService()->getAsyncProcessUrl(''), PHP_URL_HOST) ?: '';
        $options = array();
        foreach ($this->getConfigService()->getHttpConfigurationOptions($testDomain) as $option) {
            $options[$option->getName()] = $option->getValue();
        }

        Logger::logInfo(
            'HTTP configuration options',
            'Core',
            array(new LogContextData($testDomain, array('HTTPOptions' => $options)))
        );
    }

    /**
     * Gets the configuration service instance.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\Configuration Configuration service instance.
     */
    private function getConfigService()
    {
        if ($this->configService === null) {
            /** @var Configuration $configService */
            $configService = ServiceRegister::getService(Configuration::CLASS_NAME);
            $this->configService = $configService;
        }

        return $this->configService;
    }
}
