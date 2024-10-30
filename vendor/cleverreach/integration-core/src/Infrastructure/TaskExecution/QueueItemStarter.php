<?php

namespace CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution;

use Exception;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigurationManager;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogContextData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\RepositoryRegistry;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Serializer;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\ExecutionRequirementsNotMetException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Interfaces\Runnable;

/**
 * Class QueueItemStarter
 * @package CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution
 */
class QueueItemStarter implements Runnable
{
    /**
     * Id of queue item to start.
     *
     * @var int
     */
    private $queueItemId;
    /**
     * Service instance.
     *
     * @var QueueService
     */
    private $queueService;
    /**
     * Service instance.
     *
     * @var ConfigurationManager
     */
    private $configurationManager;

    /**
     * QueueItemStarter constructor.
     *
     * @param int $queueItemId Id of queue item to start.
     */
    public function __construct($queueItemId)
    {
        $this->queueItemId = $queueItemId;
    }

    /**
     * @inheritDoc
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Interfaces\Serializable
     *      Instance of serialized object.
     */
    public static function fromArray(array $array)
    {
        return new static($array['queue_item_id']);
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array('queue_item_id' => $this->queueItemId);
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return Serializer::serialize(array($this->queueItemId));
    }

    /**
     * @inheritdoc
     */
    public function unserialize($serialized)
    {
        list($this->queueItemId) = Serializer::unserialize($serialized);
    }

    /**
     * Starts runnable run logic.
     */
    public function run()
    {
        /** @var \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueItem $queueItem */
        $queueItem = $this->fetchItem();

        if ($queueItem === null || ($queueItem->getStatus() !== QueueItem::QUEUED)) {
            Logger::logDebug(
                'Fail to start task execution because task no longer exists or it is not in queued state anymore.',
                'Core',
                array(
                    new LogContextData('TaskId', $this->getQueueItemId()),
                    new LogContextData('Status', $queueItem !== null ? $queueItem->getStatus() : 'unknown'),
                )
            );

            return;
        }

        $queueService = $this->getQueueService();
        try {
            $this->getConfigManager()->setContext($queueItem->getContext());
            $queueService->validateExecutionRequirements($queueItem);
            $queueService->start($queueItem);
            $queueService->finish($queueItem);
        } catch (QueueStorageUnavailableException $e) {
            Logger::logInfo($e->getMessage(), 'Core', array(new LogContextData('trace', $e->getTraceAsString())));
        } catch (ExecutionRequirementsNotMetException $e) {
            $id = $queueItem->getId();
            Logger::logWarning(
                "Execution requirements not met for queue item [$id] because:" .
                $e->getMessage(),
                'Core',
                array(new LogContextData('ExceptionTrace', $e->getTraceAsString()))
            );
        } catch (AbortTaskExecutionException $exception) {
            $queueService->abort($queueItem, $exception->getMessage());
        } catch (QueueItemDeserializationException $e) {
            $queueService->forceFail($queueItem, 'Failed to deserialize task');
        } catch (Exception $ex) {
            if (QueueItem::IN_PROGRESS === $queueItem->getStatus()) {
                $queueService->fail($queueItem, $ex->getMessage());
            }
            $context = array(
                new LogContextData('TaskId', $this->getQueueItemId()),
                new LogContextData('ExceptionMessage', $ex->getMessage()),
                new LogContextData('ExceptionTrace', $ex->getTraceAsString()),
            );

            Logger::logError("Fail to start task execution because: {$ex->getMessage()}.", 'Core', $context);
        }
    }

    /**
     * Gets id of a queue item that will be run.
     *
     * @return int Id of queue item to run.
     */
    public function getQueueItemId()
    {
        return $this->queueItemId;
    }

    /**
     * Gets Queue item.
     *
     * @return QueueItem|null Queue item if found; otherwise, null.
     */
    private function fetchItem()
    {
        $queueItem = null;

        try {
            $queueItem = $this->getQueueService()->find($this->queueItemId);
        } catch (Exception $ex) {
            return null;
        }

        return $queueItem;
    }

    /**
     * Gets Queue service instance.
     *
     * @return QueueService Service instance.
     */
    private function getQueueService()
    {
        if ($this->queueService === null) {
            /** @var QueueService $queueService */
            $queueService = ServiceRegister::getService(QueueService::CLASS_NAME);
            $this->queueService = $queueService;
        }

        return $this->queueService;
    }

    /**
     * Gets configuration service instance.
     *
     * @return ConfigurationManager Service instance.
     */
    private function getConfigManager()
    {
        if ($this->configurationManager === null) {
            /** @var ConfigurationManager $configurationManager */
            $configurationManager = ServiceRegister::getService(ConfigurationManager::CLASS_NAME);
            $this->configurationManager = $configurationManager;
        }

        return $this->configurationManager;
    }
}
