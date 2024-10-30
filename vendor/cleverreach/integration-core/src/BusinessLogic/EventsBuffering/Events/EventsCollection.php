<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Events;

/**
 * Class EventsCollection
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Events
 */
class EventsCollection
{
    /**
     * @var Event[]
     */
    private $bufferedEvents;
    /**
     * @var Event[]
     */
    private $eventsToAdd = array();
    /**
     * @var Event[]
     */
    private $eventsToRemove = array();

    /**
     * EventsCollection constructor.
     *
     * @param Event[] $bufferedEvents
     */
    public function __construct(array $bufferedEvents)
    {
        $this->bufferedEvents = $bufferedEvents;
    }

    /**
     * @return Event[]
     */
    public function getEventsToAdd()
    {
        return $this->eventsToAdd;
    }

    /**
     * @return Event[]
     */
    public function getEventsToRemove()
    {
        return $this->eventsToRemove;
    }

    /**
     * @param Event $event
     *
     * @return void
     */
    public function applyNew(Event $event)
    {
        if ($this->has($event)) {
            return;
        }

        $this->eventsToAdd[] = $event;
        $existingEvent = $this->getMatchingEventByAction($event);
        if (!$existingEvent && $event->getAction() === SyncActions::UPSERT_RECEIVER) {
            return;
        }

        if ($event->getAction() === SyncActions::UPSERT_RECEIVER) {
            $this->eventsToRemove[] = $existingEvent;
            $event->mergeTagsToRemove($existingEvent->getTagsToRemove());
        }

        if (
            $event->equals(Event::subscriberSubscribed($event->getEmail())) &&
            $this->has(Event::subscriberUnsubscribed($event->getEmail()))
        ) {
            $this->eventsToRemove[] = Event::subscriberUnsubscribed($event->getEmail());
        }

        if (
            $event->equals(Event::subscriberUnsubscribed($event->getEmail())) &&
            $this->has(Event::subscriberSubscribed($event->getEmail()))
        ) {
            $this->eventsToRemove[] = Event::subscriberSubscribed($event->getEmail());
        }
    }

    /**
     * @param Event $event
     *
     * @return bool
     */
    public function has(Event $event)
    {
        $matchingEvents = array_filter($this->bufferedEvents, function (Event $bufferedEvent) use ($event) {
            return $event->equals($bufferedEvent);
        });

        return !empty($matchingEvents);
    }

    /**
     * Returns equal event from buffered event or null if there is no equal event in the collection
     *
     * @param Event $event
     *
     * @return Event|null
     */
    private function getMatchingEventByAction(Event $event)
    {
        foreach ($this->bufferedEvents as $bufferedEvent) {
            if ($event->getEmail() === $bufferedEvent->getEmail() && $event->getAction() === $bufferedEvent->getAction()) {
                return $bufferedEvent;
            }
        }

        return null;
    }
}
