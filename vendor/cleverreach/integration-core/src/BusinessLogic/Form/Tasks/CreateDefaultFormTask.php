<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Tasks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Contracts\FormService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\DTO\Form;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Contracts\GroupService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task;

/**
 * Class CreateDefaultFormTask
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Tasks
 */
class CreateDefaultFormTask extends Task
{
    const CLASS_NAME = __CLASS__;

    /**
     * Creates default form for the integration if the form does not exist.
     *
     * Form name is used to identify if the default form is already created or not.
     *
     * Saves default form id locally.
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Exceptions\FailedToRetriveFormException
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Exceptions\FailedToCreateFormException
     */
    public function execute()
    {
        $groupId = $this->getGroupService()->getId();
        $formService = $this->getFormService();
        $formName = $formService->getDefaultFormName();
        $forms = $formService->getForms($groupId);

        $this->reportProgress(40);

        $id = $this->getFormId($forms, $groupId);

        if ($id === null) {
            $id = $formService->createForm($groupId, 'default', array('name' => $formName, 'title' => $formName));
        }

        $this->getFormService()->setId($id);

        $this->reportProgress(100);
    }

    /**
     * Retrieves group service.
     *
     * @return GroupService
     */
    protected function getGroupService()
    {
        /** @var GroupService $groupService */
        $groupService = ServiceRegister::getService(GroupService::CLASS_NAME);

        return $groupService;
    }

    /**
     * Retrieves form service.
     *
     * @return FormService
     */
    protected function getFormService()
    {
        /** @var FormService $formService */
        $formService = ServiceRegister::getService(FormService::CLASS_NAME);

        return $formService;
    }

    /**
     * @param Form[] $forms
     * @param string $groupId
     *
     * @return string|null
     */
    protected function getFormId(array $forms, $groupId)
    {
        foreach ($forms as $form) {
            if ($form->getCustomerTableId() === $groupId) {
                return $form->getId();
            }
        }

        return null;
    }
}
