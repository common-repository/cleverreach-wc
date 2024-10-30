<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Tasks\Cache\Subtasks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Entities\Form;

class DeleteRemovedForms extends FormCacheUpdater
{
    const CLASS_NAME = __CLASS__;

    /**
     * @inheritDoc
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Exceptions\FailedToRetrieveFormCacheException
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Exceptions\FailedToDeleteFormCacheException
     */
    public function execute()
    {
        $forms = $this->getForms();

        $this->reportProgress(10);

        foreach ($this->getFromCacheService()->getForms() as $form) {
            if (!$this->isRemoved($form, $forms)) {
                continue;
            }

            $this->getFromCacheService()->deleteForm($form);
            $this->reportAlive();
        }

        $this->reportProgress(100);
    }

    /**
     * Checks if form has been removed on API.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Entities\Form $form
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\DTO\Form[] $forms
     *
     * @return bool
     */
    private function isRemoved(Form $form, array $forms)
    {
        foreach ($forms as $formFromApi) {
            if ($formFromApi->getId() === $form->getApiId()) {
                return false;
            }
        }

        return true;
    }
}
