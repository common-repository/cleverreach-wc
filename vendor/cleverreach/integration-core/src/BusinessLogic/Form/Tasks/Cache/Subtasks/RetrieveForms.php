<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Tasks\Cache\Subtasks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Contracts\FormService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\DTO\Form;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Contracts\GroupService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\Transformer;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Serializer;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task;

class RetrieveForms extends Task
{
    const CLASS_NAME = __CLASS__;
    /**
     * @var Form[]
     */
    public $forms;

    /**
     * @inheritDoc
     */
    public function serialize()
    {
        return Serializer::serialize($this->forms);
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized)
    {
        $this->forms = Serializer::unserialize($serialized);
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array(
            'forms' => Transformer::batchTransform($this->forms),
        );
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $array)
    {
        $entity = new static();
        $entity->forms = Form::fromBatch($array['forms']);

        return $entity;
    }

    /**
     * Provides forms retrieved from api.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\DTO\Form[]
     */
    public function getForms()
    {
        return $this->forms;
    }

    /**
     * @inheritDoc
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Exceptions\FailedToRetriveFormException
     */
    public function execute()
    {
        $this->forms = $this->getFormService()->getForms($this->getGroupService()->getId(), true);
        $this->reportProgress(100);
    }

    /**
     * @return FormService
     */
    private function getFormService()
    {
        /** @var FormService $formService */
        $formService = ServiceRegister::getService(FormService::CLASS_NAME);

        return $formService;
    }

    /**
     * @return GroupService
     */
    private function getGroupService()
    {
        /** @var GroupService $groupService */
        $groupService = ServiceRegister::getService(GroupService::CLASS_NAME);

        return $groupService;
    }
}
