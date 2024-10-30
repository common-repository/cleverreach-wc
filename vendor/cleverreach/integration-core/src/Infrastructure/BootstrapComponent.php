<?php

namespace CleverReach\WooCommerce\IntegrationCore\Infrastructure;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigurationManager;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\CurlHttpClient;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\HttpClient;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\AsyncProcessStarterService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Interfaces\AsyncProcessService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Interfaces\TaskRunnerManager;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Interfaces\TaskRunnerStatusStorage;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\RunnerStatusStorage;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\TaskRunner;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\TaskRunnerWakeupService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\Events\EventBus;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\GuidProvider;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\TimeProvider;

/**
 * Class BootstrapComponent.
 *
 * @package CleverReach\WooCommerce\IntegrationCore\Infrastructure
 */
class BootstrapComponent
{
    /**
     * Initializes infrastructure components.
     *
     * @return void
     */
    public static function init()
    {
        static::initServices();
        static::initRepositories();
        static::initEvents();
    }

    /**
     * Initializes services and utilities.
     *
     * @return void
     */
    protected static function initServices()
    {
        ServiceRegister::registerService(
            ConfigurationManager::CLASS_NAME,
            function () {
                return ConfigurationManager::getInstance();
            }
        );
        ServiceRegister::registerService(
            TimeProvider::CLASS_NAME,
            function () {
                return TimeProvider::getInstance();
            }
        );
        ServiceRegister::registerService(
            GuidProvider::CLASS_NAME,
            function () {
                return GuidProvider::getInstance();
            }
        );
        ServiceRegister::registerService(
            EventBus::CLASS_NAME,
            function () {
                return EventBus::getInstance();
            }
        );
        ServiceRegister::registerService(
            AsyncProcessService::CLASS_NAME,
            function () {
                return AsyncProcessStarterService::getInstance();
            }
        );
        ServiceRegister::registerService(
            QueueService::CLASS_NAME,
            function () {
                return new QueueService();
            }
        );
        ServiceRegister::registerService(
            TaskRunnerWakeup::CLASS_NAME,
            function () {
                return new TaskRunnerWakeupService();
            }
        );
        ServiceRegister::registerService(
            TaskRunner::CLASS_NAME,
            function () {
                return new TaskRunner();
            }
        );
        ServiceRegister::registerService(
            TaskRunnerStatusStorage::CLASS_NAME,
            function () {
                return new RunnerStatusStorage();
            }
        );
        ServiceRegister::registerService(
            TaskRunnerManager::CLASS_NAME,
            function () {
                return new TaskExecution\TaskRunnerManager();
            }
        );
        ServiceRegister::registerService(
            HttpClient::CLASS_NAME,
            function () {
                return new CurlHttpClient();
            }
        );
    }

    /**
     * Initializes repositories.
     *
     * @return void
     */
    protected static function initRepositories()
    {
    }

    /**
     * Initializes events.
     *
     * @return void
     */
    protected static function initEvents()
    {
    }
}
