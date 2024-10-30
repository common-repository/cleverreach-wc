<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Tasks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Contracts\AbandonedCartRecordService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCartTrigger;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCartTriggeredLog;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Exceptions\FailedToPassFilterException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Http\Proxy;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Pipeline\AbandonedCartTriggerPipeline;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Scheduler\Interfaces\Schedulable;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigurationManager;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogContextData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\RepositoryRegistry;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Serializer;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task;

class AbandonedCartTriggerTask extends Task implements Schedulable
{
    /**
     * @var int
     */
    protected $recordId;

    /**
     * AbandonedCartTriggerTask constructor.
     *
     * @param int $recordId
     */
    public function __construct($recordId)
    {
        $this->recordId = $recordId;
    }

    /**
     * @inheritdoc
     */
    public function getEqualityComponents()
    {
        return array('recordId' => $this->recordId);
    }

    public function serialize()
    {
        return Serializer::serialize(array($this->recordId));
    }

    public function unserialize($serialized)
    {
        list($this->recordId) = Serializer::unserialize($serialized);
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array('recordId' => $this->recordId);
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $array)
    {
        return new static($array['recordId']);
    }

    public function canHaveMultipleQueuedInstances()
    {
        return true;
    }

    /**
     * Triggers abandoned cart.
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException
     */
    public function execute()
    {
        $record = $this->getService()->getById($this->recordId);
        $trigger = $record->getTrigger();

        try {
            AbandonedCartTriggerPipeline::execute($trigger);
        } catch (FailedToPassFilterException $e) {
            Logger::logWarning($e->getMessage(), 'Core', array(new LogContextData('trace', $e->getTraceAsString())));
            $this->getService()->delete($trigger->getGroupId(), $trigger->getPoolId());
            throw new AbortTaskExecutionException($e->getMessage());
        }

        $this->reportProgress(5);
        $this->getProxy()->triggerAbandonedCart($trigger);
        $this->reportProgress(70);
        $this->getService()->deleteRecord($record);
        $this->reportProgress(90);
        $this->createLog($trigger);
        $this->reportProgress(100);
    }

    /**
     * Creates abandoned cart triggered log.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCartTrigger $trigger
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    private function createLog(AbandonedCartTrigger $trigger)
    {
        $log = new AbandonedCartTriggeredLog();
        $log->setContext($this->getConfigManager()->getContext());
        $log->setCartId($trigger->getCartId());
        $log->setTriggeredAt(new \DateTime());
        $this->getLogRepository()->save($log);
    }

    /**
     * Retrieves abandoned cart proxy.
     *
     * @return Proxy
     */
    private function getProxy()
    {
        /** @var Proxy $proxy */
        $proxy = ServiceRegister::getService(Proxy::CLASS_NAME);

        return $proxy;
    }

    /**
     * Retrieves abandoned cart record service.
     *
     * @return AbandonedCartRecordService
     */
    private function getService()
    {
        /** @var AbandonedCartRecordService $abandonedCartRecordService */
        $abandonedCartRecordService = ServiceRegister::getService(AbandonedCartRecordService::CLASS_NAME);

        return $abandonedCartRecordService;
    }

    /**
     * Retrieves log repository.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Interfaces\RepositoryInterface
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    private function getLogRepository()
    {
        return RepositoryRegistry::getRepository(AbandonedCartTriggeredLog::getClassName());
    }

    /**
     * Retrieves configuration manager.
     *
     * @return ConfigurationManager
     */
    private function getConfigManager()
    {
        /** @var ConfigurationManager $configurationManager */
        $configurationManager = ServiceRegister::getService(ConfigurationManager::CLASS_NAME);

        return $configurationManager;
    }
}
