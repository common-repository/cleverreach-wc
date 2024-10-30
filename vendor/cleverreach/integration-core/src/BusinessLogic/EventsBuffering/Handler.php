<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Contracts\BufferConfigurationInterface;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Contracts\BufferingEventsHandler;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Entities\EventsBufferEntity;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Events\Event;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Events\EventsCollection;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Repositories\EventsBufferEntityRepository;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigurationManager;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\Operators;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\QueryFilter;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\RepositoryRegistry;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class Handler
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering
 */
class Handler implements BufferingEventsHandler
{
    /**
     * @inheritDoc
     */
    public function handle($event)
    {
        $newEvents = is_array($event) ? $event : array($event);
        if (empty($newEvents)) {
            return;
        }

        $receiverEmailsMap = $this->getReceiverEmailsMap($newEvents);
        foreach ($receiverEmailsMap as $receiverEmail => $receiverEvents) {
            $this->handleSingleReceiverEvents($receiverEmail, $receiverEvents);
        }

        $this->getBufferConfigurationService()->updateHasEvents(
            $this->getConfigManager()->getContext(),
            true
        );
    }

    /**
     * @param string $receiverEmail
     * @param Event[] $newEvents
     *
     * @return void
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function handleSingleReceiverEvents($receiverEmail, array $newEvents)
    {
        $bufferedEvents = $this->getBufferedEvents($receiverEmail);
        foreach ($newEvents as $newEvent) {
            $bufferedEvents->applyNew($newEvent);
        }

        $this->persistChanges($receiverEmail, $bufferedEvents);
    }

    /**
     * @param string $email
     *
     * @return EventsCollection
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    private function getBufferedEvents($email)
    {
        $query = new QueryFilter();
        $query->where('email', Operators::EQUALS, $email);

        /** @var EventsBufferEntity[] $buffered */
        $buffered = $this->getRepository()->select($query);

        return new EventsCollection(array_map(function (EventsBufferEntity $entity) {
            return Event::fromEntity($entity);
        }, $buffered));
    }

    /**
     * @param string $email
     * @param EventsCollection $bufferedEvents
     *
     * @return void
     */
    private function persistChanges($email, EventsCollection $bufferedEvents)
    {
        $this->removeFromBuffer($email, $bufferedEvents->getEventsToRemove());
        $this->addToBuffer($bufferedEvents->getEventsToAdd());
    }

    /**
     * @param string $email
     * @param Event[] $events
     *
     * @return void
     */
    private function removeFromBuffer($email, array $events)
    {
        if (empty($events)) {
            return;
        }

        $query = new QueryFilter();
        $query->where('email', Operators::EQUALS, $email);
        $query->where('syncAction', Operators::IN, array_map(function (Event $event) {
            return $event->getAction();
        }, $events));

        $this->getRepository()->deleteWhere($query);
    }

    /**
     * @param Event[] $events
     *
     * @return void
     */
    private function addToBuffer(array $events)
    {
        foreach ($events as $event) {
            $this->getRepository()->save($event->toEntity());
        }
    }

    /**
     * @param Event[] $events
     *
     * @return array<string, Event[]>
     */
    private function getReceiverEmailsMap(array $events)
    {
        $receiverEmailMap = array();
        foreach ($events as $event) {
            $receiverEmailMap[$event->getEmail()][] = $event;
        }

        return $receiverEmailMap;
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
     * @return BufferConfigurationInterface
     */
    private function getBufferConfigurationService()
    {
        /** @var  BufferConfigurationInterface $service */
        $service = ServiceRegister::getService(BufferConfigurationInterface::CLASS_NAME);

        return $service;
    }

    /**
     * @return ConfigurationManager
     */
    private function getConfigManager()
    {
        /** @var ConfigurationManager $configurationManager */
        $configurationManager = ServiceRegister::getService(ConfigurationManager::CLASS_NAME);

        return $configurationManager;
    }
}
