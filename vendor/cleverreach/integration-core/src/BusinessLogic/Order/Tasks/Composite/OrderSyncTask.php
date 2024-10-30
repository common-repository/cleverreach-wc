<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Order\Tasks\Composite;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Order\Tasks\OrderItemsSyncTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Configuration\SyncConfiguration;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\ReceiverSyncTask;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Serializer;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\CompositeTask;

/**
 * Class OrderSyncTask
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Order\Tasks\Composite
 */
class OrderSyncTask extends CompositeTask
{
    /**
     * Integrated system order id.
     *
     * @var string | int
     */
    protected $orderId;
    /**
     * Buyer's email.
     *
     * @var string
     */
    protected $receiverEmail;
    /**
     * Order tracking mailing id.
     *
     * @var string
     */
    protected $mailingId;

    /**
     * OrderSyncTask constructor.
     *
     * @param int|string $orderId
     * @param string $receiverEmail
     * @param string $mailingId
     */
    public function __construct($orderId, $receiverEmail, $mailingId = '')
    {
        parent::__construct(
            array(
                OrderItemsSyncTask::getClassName() => 0.5,
                ReceiverSyncTask::getClassName() => 0.5,
            )
        );

        $this->orderId = $orderId;
        $this->receiverEmail = $receiverEmail;
        $this->mailingId = $mailingId;
    }

    /**
     * @inheritdoc
     */
    public function getEqualityComponents()
    {
        return array('orderId' => $this->orderId, 'receiverEmail' => $this->receiverEmail, 'mailingId' => $this->mailingId);
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $result = parent::toArray();
        $result['orderId'] = $this->orderId;
        $result['receiverEmail'] = $this->receiverEmail;
        $result['mailingId'] = $this->mailingId;

        return $result;
    }

    /**
     * Serializes order sync task.
     *
     * @return string
     */
    public function serialize()
    {
        return Serializer::serialize(array(
            'parent' => parent::serialize(),
            'self' => array(
                $this->orderId,
                $this->receiverEmail,
                $this->mailingId,
            )
        ));
    }

    /**
     * Unserializes OrderSyncTask.
     *
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $data = Serializer::unserialize($serialized);
        parent::unserialize($data['parent']);

        list($this->orderId, $this->receiverEmail, $this->mailingId) = $data['self'];
    }

    /**
     * @inheritDoc
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Order\Tasks\Composite\OrderSyncTask
     */
    public static function fromArray(array $data)
    {
        /** @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Order\Tasks\Composite\OrderSyncTask $entity */
        $entity = parent::fromArray($data);
        $entity->orderId = $data['orderId'];
        $entity->receiverEmail = $data['receiverEmail'];
        $entity->mailingId = $data['mailingId'];

        return $entity;
    }

    /**
     * Instantiates sub-task.
     *
     * @param string $taskKey
     *
     * @return OrderItemsSyncTask | ReceiverSyncTask
     */
    protected function createSubTask($taskKey)
    {
        if ($taskKey === OrderItemsSyncTask::getClassName()) {
            return new OrderItemsSyncTask($this->orderId, $this->receiverEmail, $this->mailingId);
        }

        return new ReceiverSyncTask(new SyncConfiguration(array($this->receiverEmail)));
    }
}
