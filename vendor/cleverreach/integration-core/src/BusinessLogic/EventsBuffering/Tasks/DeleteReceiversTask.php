<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Tasks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Http\Proxy;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Serializer;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task;

/**
 * Class DeleteReceiversTask
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Tasks
 */
class DeleteReceiversTask extends Task
{
    const BATCH_SIZE = 250;
    /**
     * @var string[]
     */
    private $emailsTodDelete;

    /**
     * DeleteReceiversTask constructor.
     *
     * @param string[] $emailsTodDelete
     */
    public function __construct(array $emailsTodDelete)
    {
        $this->emailsTodDelete = $emailsTodDelete;
    }

    /**
     * @inheritdoc
     */
    public function getEqualityComponents()
    {
        return $this->toArray();
    }

    public function serialize()
    {
        return Serializer::serialize(
            array(
                'parent' => parent::serialize(),
                'emailsTodDelete' => Serializer::serialize($this->emailsTodDelete),
            )
        );
    }

    public function unserialize($serialized)
    {
        $unserialized = Serializer::unserialize($serialized);
        parent::unserialize($unserialized['parent']);
        $this->emailsTodDelete = Serializer::unserialize($unserialized['emailsTodDelete']);
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $result = parent::toArray();
        $result['emailsTodDelete'] = $this->emailsTodDelete;

        return $result;
    }

    /**
     * @inheritDoc
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Interfaces\Serializable
     */
    public static function fromArray(array $serializedData)
    {
        /** @var self $entity */
        $entity = parent::fromArray($serializedData);

        $entity->emailsTodDelete = $serializedData['emailsTodDelete'];

        return $entity;
    }

    public function execute()
    {
        while ($batchOfEmails = $this->getBatchOfEmails()) {
            $this->getProxy()->deleteReceivers($batchOfEmails);
            $this->reportAlive();
            $this->unsetBatchOfEmails();
        }

        $this->reportProgress(100);
    }

    /**
     * Retrieves batch of receivers for synchronization.
     *
     * @return string[]
     */
    protected function getBatchOfEmails()
    {
        return array_slice($this->emailsTodDelete, 0, self::BATCH_SIZE, true);
    }

    /**
     * @return void
     */
    private function unsetBatchOfEmails()
    {
        $this->emailsTodDelete = array_slice($this->emailsTodDelete, self::BATCH_SIZE, null, true);
    }

    /**
     * @return Proxy
     */
    protected function getProxy()
    {
        /** @var Proxy $proxy */
        $proxy = ServiceRegister::getService(Proxy::CLASS_NAME);

        return $proxy;
    }
}
