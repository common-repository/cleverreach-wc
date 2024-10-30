<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Tasks\Create;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Tasks\Create\Subtasks\CreateAutomation;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Tasks\Create\Subtasks\FinalizeAutomationCreation;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Webhooks\Tasks\RegisterWebhooksTask;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Serializer;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\CompositeTask;

class CreateCartAutomationTask extends CompositeTask
{
    /**
     * Id of the automation.
     *
     * @var int
     */
    public $automationId;

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return Serializer::serialize(
            array(
                'parent' => parent::serialize(),
                'automationId' => $this->automationId,
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function unserialize($serialized)
    {
        $unserialized = Serializer::unserialize($serialized);
        parent::unserialize($unserialized['parent']);
        $this->automationId = $unserialized['automationId'];
    }

    /**
     * @inheritdoc
     */
    public static function fromArray(array $serializedData)
    {
        /** @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Tasks\Create\CreateCartAutomationTask $entity */
        $entity = parent::fromArray($serializedData);
        $entity->automationId = $serializedData['automationId'];

        return $entity;
    }

    /**
     * @inheritdoc
     */
    public function toArray()
    {
        $result = parent::toArray();
        $result['automationId'] = $this->automationId;

        return $result;
    }

    /**
     * CreateCartAutomationTask constructor.
     *
     * @param int $id
     */
    public function __construct($id = null)
    {
        parent::__construct(array(
            CreateAutomation::CLASS_NAME => 30,
            RegisterWebhooksTask::CLASS_NAME => 60,
            FinalizeAutomationCreation::CLASS_NAME => 10,
        ));

        $this->automationId = $id;
    }

    /**
     * Instantiates subtask.
     *
     * @param string $taskKey
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task
     */
    protected function createSubTask($taskKey)
    {
        /** @var \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task $task */
        $task = new $taskKey($this->automationId);

        return $task;
    }
}
