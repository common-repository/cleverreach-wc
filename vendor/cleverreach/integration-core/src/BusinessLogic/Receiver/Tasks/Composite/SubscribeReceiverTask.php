<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Contracts\ExecutionContextAware;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Components\ReceiverGroupResolver;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Components\RemoveReceiverFromBlacklist;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Components\ResolveReceiver;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Components\SubscribeReceiver;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Components\SyncServicesResolver;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Components\UpsertReceiver;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Context\SubscribtionStateChangedExecutionContext;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Serializer;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\CompositeTask;

class SubscribeReceiverTask extends CompositeTask
{
    const CLASS_NAME = __CLASS__;

    /**
     * @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Context\SubscribtionStateChangedExecutionContext
     */
    public $executionContext;

    /**
     * @param string $email
     */
    public function __construct($email = '')
    {
        parent::__construct($this->getSubTasks());

        $this->executionContext = new SubscribtionStateChangedExecutionContext($email);
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
                'executionContext' => Serializer::serialize($this->executionContext),
            )
        );
    }

    public function unserialize($serialized)
    {
        $unserialized = Serializer::unserialize($serialized);
        parent::unserialize($unserialized['parent']);
        $this->executionContext = Serializer::unserialize($unserialized['executionContext']);
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $result = parent::toArray();
        $result['executionContext'] = $this->executionContext->toArray();

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

        /** @var SubscribtionStateChangedExecutionContext $executionContext */
        $executionContext = SubscribtionStateChangedExecutionContext::fromArray(
            $serializedData['executionContext']
        );

        $entity->executionContext = $executionContext;

        return $entity;
    }

    protected static function createTask(array $tasks, $initialProgress)
    {
        return new static();
    }

    /**
     * Registers execution context for subtasks when deserialization is complete.
     *
     * @return void
     */
    public function onUnserialized()
    {
        parent::onUnserialized();

        /** @var ExecutionContextAware $task */
        foreach ($this->tasks as $task) {
            $task->setExecutionContextProvider(array($this, 'getExecutionContext'));
        }
    }

    /**
     * Retrieves execution context.
     *
     * @return SubscribtionStateChangedExecutionContext
     */
    public function getExecutionContext()
    {
        return $this->executionContext;
    }

    /**
     * @inheritDoc
     */
    protected function createSubTask($taskKey)
    {
        $this->reportAlive();

        /** @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Tasks\SubTasks\SubTask $task */
        $task = new $taskKey;
        $task->setExecutionContextProvider(array($this, 'getExecutionContext'));

        return $task;
    }

    /**
     * Retrieves list of sub tasks.
     *
     * @return array<string,int>
     */
    protected function getSubTasks()
    {
        return array(
            ReceiverGroupResolver::CLASS_NAME => 5,
            SyncServicesResolver::CLASS_NAME => 10,
            ResolveReceiver::CLASS_NAME => 5,
            SubscribeReceiver::CLASS_NAME => 45,
            RemoveReceiverFromBlacklist::CLASS_NAME => 5,
            UpsertReceiver::CLASS_NAME => 30,
        );
    }
}
