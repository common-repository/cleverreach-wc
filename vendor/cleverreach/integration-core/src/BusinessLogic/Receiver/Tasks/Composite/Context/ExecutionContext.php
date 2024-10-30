<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Context;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Config\SyncService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Configuration\SyncConfiguration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\Transformer;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Interfaces\Serializable;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Serializer;

class ExecutionContext implements Serializable
{
    /**
     * @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Configuration\SyncConfiguration
     */
    public $syncConfiguration;
    /**
     * @var string
     */
    public $groupId;
    /**
     * @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Config\SyncService[]
     */
    public $services;
    /**
     * @var string[]
     */
    public $blacklistedEmails;
    /**
     * @var mixed[]
     */
    public $receiverEmails;

    /**
     * ExecutionContext constructor.
     */
    public function __construct()
    {
        $this->syncConfiguration = new SyncConfiguration();
        $this->groupId = '';
        $this->services = array();
        $this->blacklistedEmails = array();
        $this->receiverEmails = array();
    }

    /**
     * @inheritDoc
     */
    public function serialize()
    {
        return Serializer::serialize(
            array(
                $this->syncConfiguration,
                $this->groupId,
                $this->services,
                $this->blacklistedEmails,
                $this->receiverEmails,
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized)
    {
        list(
            $this->syncConfiguration,
            $this->groupId,
            $this->services,
            $this->blacklistedEmails,
            $this->receiverEmails
        ) = Serializer::unserialize($serialized);
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array(
            'syncConfiguration' => $this->syncConfiguration->toArray(),
            'groupId' => $this->groupId,
            'services' => Transformer::batchTransform($this->services),
            'blacklistedEmails' => $this->blacklistedEmails,
            'receiverEmails' => $this->receiverEmails,
        );
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $data)
    {
        $self = new static();

        /** @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Configuration\SyncConfiguration $syncConfig */
        $syncConfig = SyncConfiguration::fromArray($data['syncConfiguration']);

        $self->syncConfiguration = $syncConfig;
        $self->groupId = $data['groupId'];
        $self->services = SyncService::fromBatch($data['services']);
        $self->blacklistedEmails = $data['blacklistedEmails'];
        $self->receiverEmails = $data['receiverEmails'];

        return $self;
    }
}
