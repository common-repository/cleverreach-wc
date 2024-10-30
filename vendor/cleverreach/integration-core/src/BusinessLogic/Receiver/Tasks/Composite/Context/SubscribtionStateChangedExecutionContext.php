<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Context;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Config\SyncService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\Transformer;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Interfaces\Serializable;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Serializer;

/**
 * Class SubscribtionStateChangedExecutionContext
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Context
 */
class SubscribtionStateChangedExecutionContext implements Serializable
{
    /**
     * @var string
     */
    public $groupId;
    /**
     * @var string
     */
    public $email;
    /**
     * @var Receiver|null
     */
    public $receiver;
    /**
     * @var mixed[]
     */
    public $services;

    /**
     * SubscribtionStateChangedExecutionContext constructor.
     *
     * @param string $email
     */
    public function __construct($email)
    {
        $this->email = $email;

        $this->groupId = '';
        $this->receiver = null;
        $this->services = array();
    }

    /**
     * @inheritDoc
     */
    public function serialize()
    {
        return Serializer::serialize(
            array(
                $this->groupId,
                $this->email,
                $this->receiver,
                $this->services,
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized)
    {
        list(
            $this->groupId,
            $this->email,
            $this->receiver,
            $this->services
        ) = Serializer::unserialize($serialized);
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $serializedData)
    {
        $entity = new static($serializedData['email']);

        $entity->groupId = $serializedData['groupId'];
        $entity->receiver = !empty($serializedData['receiver']) ? Receiver::fromArray($serializedData['receiver']) : null;
        $entity->services = SyncService::fromBatch($serializedData['services']);

        return $entity;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array(
            'email' => $this->email,
            'groupId' => $this->groupId,
            'receiver' => !empty($this->receiver) ? $this->receiver->toArray() : null,
            'services' => Transformer::batchTransform($this->services),
        );
    }
}
