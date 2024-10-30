<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Tasks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Tasks\Context\ExecutionContext;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Tasks\Contracts\ExecutionContextAware;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Tasks\SubTasks\EventProvider;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Tasks\SubTasks\EventRegistrationResultRecorder;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Tasks\SubTasks\EventRegistrator;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Tasks\SubTasks\ObsoleteEventDeleter;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Serializer;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\CompositeTask;

abstract class RegisterEventTask extends CompositeTask
{
    /**
     * @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Tasks\Context\ExecutionContext
     */
    private $executionContext;

    /**
     * RegisterEventTask constructor.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Tasks\Context\ExecutionContext $executionContext
     */
    public function __construct($executionContext)
    {
        parent::__construct($this->getSubTasks());

        $this->executionContext = $executionContext;
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
     * Provides execution context.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Tasks\Context\ExecutionContext
     */
    public function getExecutionContext()
    {
        return $this->executionContext;
    }

    /**
     * Registers execution context for subtasks when deserialization is complete.
     */
    public function onUnserialized()
    {
        parent::onUnserialized();

        /** @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Contracts\ExecutionContextAware $task */
        foreach ($this->tasks as $task) {
            $task->setExecutionContextProvider(array($this, 'getExecutionContext'));
        }
    }

    /**
     * @inheritDoc
     */
    protected function createSubTask($taskKey)
    {
        /** @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Tasks\SubTasks\SubTask $task */
        $task = new $taskKey();
        $task->setExecutionContextProvider(array($this, 'getExecutionContext'));

        return $task;
    }

    /**
     * @return array<string, int>
     */
    protected function getSubTasks()
    {
        return array(
            EventProvider::CLASS_NAME => 10,
            ObsoleteEventDeleter::CLASS_NAME => 10,
            EventRegistrator::CLASS_NAME => 60,
            EventRegistrationResultRecorder::CLASS_NAME => 20,
        );
    }
}
