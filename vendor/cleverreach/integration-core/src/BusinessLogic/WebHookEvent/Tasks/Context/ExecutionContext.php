<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Tasks\Context;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO\Event;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO\EventRegisterResult;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Interfaces\Serializable;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Serializer;

class ExecutionContext implements Serializable
{
    /**
     * @var string
     */
    private $eventServiceClass;
    /**
     * @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO\Event
     */
    private $event;
    /**
     * @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO\EventRegisterResult
     */
    private $eventResult;

    /**
     * ExecutionContext constructor.
     *
     * @param string $eventServiceClass
     */
    public function __construct($eventServiceClass)
    {
        $this->eventServiceClass = $eventServiceClass;
    }

    /**
     * @return string
     */
    public function getEventServiceClass()
    {
        return $this->eventServiceClass;
    }

    /**
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO\Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO\Event $event
     *
     * @return void
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }

    /**
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO\EventRegisterResult
     */
    public function getEventResult()
    {
        return $this->eventResult;
    }

    /**
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO\EventRegisterResult $eventResult
     *
     * @return void
     */
    public function setEventResult($eventResult)
    {
        $this->eventResult = $eventResult;
    }

    /**
     * @inheritDoc
     */
    public function serialize()
    {
        return Serializer::serialize(array(
            $this->getEventServiceClass(),
            $this->getEvent(),
            $this->getEventResult(),
        ));
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized)
    {
        list($this->eventServiceClass, $this->event, $this->eventResult) = Serializer::unserialize($serialized);
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $array)
    {
        $entity = new static($array['eventServiceClass']);

        if (!empty($array['event'])) {
            $entity->setEvent(Event::fromArray($array['event']));
        }

        if (!empty($array['eventResult'])) {
            $entity->setEventResult(EventRegisterResult::fromArray($array['eventResult']));
        }

        return $entity;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $result = array(
            'eventServiceClass' => $this->getEventServiceClass(),
            'event' => null,
            'eventResult' => null,
        );

        if (($event = $this->getEvent()) !== null) {
            $result['event'] = $event->toArray();
        }

        if (($eventResult = $this->getEventResult()) !== null) {
            $result['eventResult'] = $eventResult->toArray();
        }

        return $result;
    }
}
