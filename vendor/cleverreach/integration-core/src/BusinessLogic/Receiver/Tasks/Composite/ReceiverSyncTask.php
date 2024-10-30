<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Contracts\ExecutionContextAware;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Events\ReceiverEventBus;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Events\ReceiverSyncTaskCompletedEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Events\ReceiverSyncTaskStartedEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Components\BlacklistedEmailsResolver;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Components\ReceiverEmailsResolver;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Components\ReceiverGroupResolver;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Components\ReceiversExporter;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Components\SyncServicesResolver;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Configuration\SyncConfiguration;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Context\ExecutionContext;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Serializer;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\CompositeTask;

/**
 * Class ReceiverSyncTask
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite
 */
class ReceiverSyncTask extends CompositeTask
{
    const CLASS_NAME = __CLASS__;
    /**
     * @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Context\ExecutionContext
     */
    private $executionContext;

    /**
     * ReceiverSyncTask constructor.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Configuration\SyncConfiguration | null $configuration
     */
    public function __construct(SyncConfiguration $configuration = null)
    {
        parent::__construct($this->getSubTasks());

        $this->executionContext = new ExecutionContext();

        if ($configuration !== null) {
            $this->executionContext->syncConfiguration = $configuration;
        }
    }

    /**
     * @inheritDoc
     */
    public function getEqualityComponents()
    {
        return $this->toArray();
    }

    public function execute()
    {
        ReceiverEventBus::getInstance()->fire(new ReceiverSyncTaskStartedEvent());

        parent::execute();

        ReceiverEventBus::getInstance()->fire(new ReceiverSyncTaskCompletedEvent());
    }

    /**
     * @inheritDoc
     */
    public function serialize()
    {
        return Serializer::serialize(
            array(
                'parent' => parent::serialize(),
                'executionContext' => Serializer::serialize($this->executionContext),
            )
        );
    }

    /**
     * @inheritDoc
     */
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
     */
    public static function fromArray(array $data)
    {
        /** @var self $entity */
        $entity = parent::fromArray($data);

        /** @var ExecutionContext $executionContext */
        $executionContext = ExecutionContext::fromArray($data['executionContext']);
        $entity->executionContext = $executionContext;

        return $entity;
    }

    /**
     * @inheritDoc
     */
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
     * @return ExecutionContext
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

        /** @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Components\ReceiverSyncSubTask $task */
        $task = new $taskKey;
        $task->setExecutionContextProvider(array($this, 'getExecutionContext'));

        return $task;
    }

    /**
     * Retrieves list of sub-tasks with progress percentage share.
     *
     * @return array<string,int>
     */
    protected function getSubTasks()
    {
        return array(
            ReceiverGroupResolver::CLASS_NAME => 2,
            BlacklistedEmailsResolver::CLASS_NAME => 5,
            SyncServicesResolver::CLASS_NAME => 3,
            ReceiverEmailsResolver::CLASS_NAME => 10,
            ReceiversExporter::CLASS_NAME => 80,
        );
    }
}
