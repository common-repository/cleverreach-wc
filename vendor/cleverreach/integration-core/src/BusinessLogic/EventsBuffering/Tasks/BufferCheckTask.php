<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Tasks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Contracts\BufferConfigurationInterface;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Entities\EventsBufferEntity;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Events\SyncActions;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Repositories\EventsBufferEntityRepository;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Tag\Tag;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Configuration\SyncConfiguration;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\ReceiverSyncTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\SubscribeReceiverTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\UnsubscribeReceiverTask;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigurationManager;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\Operators;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\QueryFilter;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\RepositoryRegistry;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task;

/**
 * Class BufferCheckTask
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Tasks
 */
class BufferCheckTask extends Task
{
    const EVENTS_BATCH_SIZE = 10000;

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $this->processReceiverUpsertEvent();
        $this->reportProgress(50);
        $this->processReceiverDeleteEvents();
        $this->reportProgress(60);
        $this->processReceiverSubscribeEvents();
        $this->reportProgress(80);
        $this->processReceiverUnsubscribeEvents();
        $this->resetHasEventsFlag();

        $this->reportProgress(100);
    }

    /**
     * @return false
     */
    public function isArchivable()
    {
        return false;
    }

    /**
     * @return void
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    protected function processReceiverUpsertEvent()
    {
        /** @var QueueService $queueService */
        $queueService = ServiceRegister::getService(QueueService::CLASS_NAME);

        $offset = 0;
        while ($batchOfEvents = $this->getBatchOfEvents(SyncActions::UPSERT_RECEIVER, $offset)) {
            $task = $this->getReceiverUpsertTaskInstance($batchOfEvents);

            $queueService->enqueue(
                'UpsertBufferedChanges-' . $this->getConfigManager()->getContext(),
                $task,
                $this->getConfigManager()->getContext()
            );

            $offset += self::EVENTS_BATCH_SIZE;

            $this->reportAlive();
        }

        $this->removeEventsByAction(SyncActions::UPSERT_RECEIVER);
    }

    /**
     * @return void
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    protected function processReceiverDeleteEvents()
    {
        /** @var QueueService $queueService */
        $queueService = ServiceRegister::getService(QueueService::CLASS_NAME);

        $offset = 0;
        while ($batchOfEvents = $this->getBatchOfEvents(SyncActions::DELETE_RECEIVER, $offset)) {
            $task = $this->getReceiverDeleteTaskInstance($batchOfEvents);

            $queueService->enqueue(
                'DeleteReceivers-' . $this->getConfigManager()->getContext(),
                $task,
                $this->getConfigManager()->getContext()
            );

            $offset += self::EVENTS_BATCH_SIZE;

            $this->reportAlive();
        }

        $this->removeEventsByAction(SyncActions::DELETE_RECEIVER);
    }

    /**
     * @return void
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    protected function processReceiverSubscribeEvents()
    {
        /** @var QueueService $queueService */
        $queueService = ServiceRegister::getService(QueueService::CLASS_NAME);

        $offset = 0;
        while ($batchOfEvents = $this->getBatchOfEvents(SyncActions::SUBSCRIBE_RECEIVER, $offset)) {
            foreach ($batchOfEvents as $event) {
                $task = $this->getSubscribeReceiverTask($event->getEmail());
                $queueService->enqueue(
                    'ReceiversSubscription-' . $this->getConfigManager()->getContext(),
                    $task,
                    $this->getConfigManager()->getContext()
                );
            }

            $offset += self::EVENTS_BATCH_SIZE;

            $this->reportAlive();
        }

        $this->removeEventsByAction(SyncActions::SUBSCRIBE_RECEIVER);
    }

    /**
     * @return void
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    protected function processReceiverUnsubscribeEvents()
    {
        /** @var QueueService $queueService */
        $queueService = ServiceRegister::getService(QueueService::CLASS_NAME);

        $offset = 0;
        while ($batchOfEvents = $this->getBatchOfEvents(SyncActions::UNSUBSCRIBE_RECEIVER, $offset)) {
            foreach ($batchOfEvents as $event) {
                $task = $this->getUnsubscribeReceiverTask($event->getEmail());
                $queueService->enqueue(
                    'ReceiversSubscription-' . $this->getConfigManager()->getContext(),
                    $task,
                    $this->getConfigManager()->getContext()
                );
            }

            $offset += self::EVENTS_BATCH_SIZE;

            $this->reportAlive();
        }

        $this->removeEventsByAction(SyncActions::UNSUBSCRIBE_RECEIVER);
    }

    /**
     * @param EventsBufferEntity[] $batchOfEvents
     *
     * @return Task
     */
    protected function getReceiverUpsertTaskInstance(array $batchOfEvents)
    {
        return new ReceiverSyncTask(new SyncConfiguration(
            array_map(static function (EventsBufferEntity $entity) {
                return $entity->getEmail();
            }, $batchOfEvents),
            Tag::fromBatch(array_unique(array_map(
                function (Tag $tag) {
                    return (string)$tag;
                },
                array_reduce($batchOfEvents, static function (array $tagsToDelete, EventsBufferEntity $entity) {
                    return array_merge($tagsToDelete, $entity->getTagsToRemove());
                }, array())
            )))
        ));
    }

    /**
     * @param EventsBufferEntity[] $batchOfEvents
     *
     * @return Task
     */
    protected function getReceiverDeleteTaskInstance(array $batchOfEvents)
    {
        return new DeleteReceiversTask(array_map(function (EventsBufferEntity $entity) {
            return $entity->getEmail();
        }, $batchOfEvents));
    }

    /**
     * @param string $email
     *
     * @return Task
     */
    protected function getSubscribeReceiverTask($email)
    {
        return new SubscribeReceiverTask($email);
    }

    /**
     * @param string $email
     *
     * @return Task
     */
    protected function getUnsubscribeReceiverTask($email)
    {
        return new UnsubscribeReceiverTask($email);
    }

    /**
     * @return void
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    protected function resetHasEventsFlag()
    {
        $this->getBufferConfigurationService()->updateHasEvents(
            $this->getConfigManager()->getContext(),
            (bool)$this->getRepository()->selectOne()
        );
    }

    /**
     * @param string $syncActions
     * @param int $offset
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Entities\EventsBufferEntity[]
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    protected function getBatchOfEvents($syncActions, $offset = 0)
    {
        $query = new QueryFilter();
        $query->where('syncAction', Operators::EQUALS, $syncActions);
        $query->setLimit(self::EVENTS_BATCH_SIZE);
        $query->setOffset($offset);

        /** @var EventsBufferEntity[] $bufferEvents */
        $bufferEvents = $this->getRepository()->select($query);

        return $bufferEvents;
    }

    /**
     * @param string $syncAction
     *
     * @return void
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    protected function removeEventsByAction($syncAction)
    {
        $query = new QueryFilter();
        $query->where('syncAction', Operators::EQUALS, $syncAction);
        $query->setLimit(self::EVENTS_BATCH_SIZE);

        while ($this->getRepository()->selectOne($query)) {
            $this->getRepository()->deleteWhere($query);
            $this->reportAlive();
        }
    }

    /**
     * @return EventsBufferEntityRepository
     * @throws RepositoryNotRegisteredException
     */
    private function getRepository()
    {
        /** @var EventsBufferEntityRepository $repository */
        $repository =  RepositoryRegistry::getRepository(EventsBufferEntity::CLASS_NAME);

        return $repository;
    }

    /**
     * @return ConfigurationManager Service instance.
     */
    private function getConfigManager()
    {
        /** @var ConfigurationManager $configurationManager */
        $configurationManager = ServiceRegister::getService(ConfigurationManager::CLASS_NAME);

        return $configurationManager;
    }

    /**
     * @return BufferConfigurationInterface
     */
    private function getBufferConfigurationService()
    {
        /** @var  BufferConfigurationInterface $service */
        $service = ServiceRegister::getService(BufferConfigurationInterface::CLASS_NAME);

        return $service;
    }
}
