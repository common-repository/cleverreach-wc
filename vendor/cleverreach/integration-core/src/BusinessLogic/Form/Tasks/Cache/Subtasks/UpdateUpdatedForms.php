<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Tasks\Cache\Subtasks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Entities\Form;

class UpdateUpdatedForms extends FormCacheUpdater
{
    const CLASS_NAME = __CLASS__;

    /**
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Exceptions\FailedToRetrieveFormCacheException
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Exceptions\FailedToUpdateFormCacheException
     */
    public function execute()
    {
        $forms = $this->getForms();

        $this->reportProgress(10);

        foreach ($this->getFromCacheService()->getForms() as $form) {
            if (($formDto = $this->getFormDto($form, $forms)) !== null) {
                $this->update($form, $formDto);
            }

            $this->reportAlive();
        }

        $this->reportProgress(100);
    }

    /**
     * Retrieves dto for specified entity.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Entities\Form $form
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\DTO\Form[] $forms
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\DTO\Form | null
     */
    private function getFormDto(Form $form, array $forms)
    {
        foreach ($forms as $formFromApi) {
            if ($formFromApi->getId() === $form->getApiId()) {
                return $formFromApi;
            }
        }

        return null;
    }

    /**
     * Updates form.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Entities\Form $form
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\DTO\Form $formDto
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Exceptions\FailedToUpdateFormCacheException
     */
    private function update(Form $form, \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\DTO\Form $formDto)
    {
        $entity = $this->translateDtoToEntity($formDto);
        $entity->setId($form->getId());

        $this->getFromCacheService()->updateForm($entity);
    }
}
