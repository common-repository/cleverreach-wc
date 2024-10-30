<?php

namespace CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution;

use Exception;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\HttpClient;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogContextData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Interfaces\RepositoryInterface;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\QueryFilter;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\RepositoryRegistry;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Singleton;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\ProcessStarterSaveException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Interfaces\AsyncProcessService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Interfaces\Runnable;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\GuidProvider;

/**
 * Class AsyncProcessStarter.
 *
 * @package CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution
 */
class AsyncProcessStarterService extends Singleton implements AsyncProcessService
{
    /**
     * Singleton instance of this class.
     *
     * @var static
     */
    protected static $instance;
    /**
     * Configuration instance.
     *
     * @var Configuration
     */
    private $configuration;
    /**
     * Process entity repository.
     *
     * @var RepositoryInterface
     */
    private $processRepository;
    /**
     * GUID provider instance.
     *
     * @var GuidProvider
     */
    private $guidProvider;
    /**
     * HTTP client.
     *
     * @var HttpClient
     */
    private $httpClient;

    /**
     * AsyncProcessStarterService constructor.
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    protected function __construct()
    {
        parent::__construct();
        /** @var HttpClient $httpClient */
        $httpClient = ServiceRegister::getService(HttpClient::CLASS_NAME);
        /** @var GuidProvider $guidProvider */
        $guidProvider = ServiceRegister::getService(GuidProvider::CLASS_NAME);
        /** @var Configuration $configuration */
        $configuration = ServiceRegister::getService(Configuration::CLASS_NAME);

        $this->httpClient = $httpClient;
        $this->guidProvider = $guidProvider;
        $this->configuration = $configuration;
        $this->processRepository = RepositoryRegistry::getRepository(Process::CLASS_NAME);
    }

    /**
     * Starts given runner asynchronously (in new process/web request or similar).
     *
     * @param Runnable $runner Runner that should be started async.
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\ProcessStarterSaveException
     */
    public function start(Runnable $runner)
    {
        $guid = trim($this->guidProvider->generateGuid());

        $this->saveGuidAndRunner($guid, $runner);
        $this->startRunnerAsynchronously($guid);
    }

    /**
     * Runs a process with provided identifier.
     *
     * @param string $guid Identifier of process.
     *
     * @return void
     */
    public function runProcess($guid)
    {
        try {
            $filter = new QueryFilter();
            $filter->where('guid', '=', $guid);

            /** @var Process $process */
            $process = $this->processRepository->selectOne($filter);
            if ($process !== null) {
                $process->getRunner()->run();
                $this->processRepository->delete($process);
            }
        } catch (Exception $e) {
            Logger::logError($e->getMessage(), 'Core', array(new LogContextData('guid', $guid)));
        }
    }

    /**
     * Saves runner and guid to storage.
     *
     * @param string $guid Unique process identifier.
     * @param Runnable $runner Runner instance.
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\ProcessStarterSaveException
     */
    private function saveGuidAndRunner($guid, Runnable $runner)
    {
        try {
            $process = new Process();
            $process->setGuid($guid);
            $process->setRunner($runner);

            $this->processRepository->save($process);
        } catch (Exception $e) {
            Logger::logError($e->getMessage());
            throw new ProcessStarterSaveException($e->getMessage(), 0, $e);
        }
    }

    /**
     * Starts runnable asynchronously.
     *
     * @param string $guid Unique process identifier.
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    private function startRunnerAsynchronously($guid)
    {
        try {
            $this->httpClient->requestAsync(
                $this->configuration->getAsyncProcessCallHttpMethod(),
                $this->configuration->getAsyncProcessUrl($guid)
            );
        } catch (Exception $e) {
            Logger::logError($e->getMessage(), 'Integration');
            throw new HttpRequestException($e->getMessage(), 0, $e);
        }
    }
}
