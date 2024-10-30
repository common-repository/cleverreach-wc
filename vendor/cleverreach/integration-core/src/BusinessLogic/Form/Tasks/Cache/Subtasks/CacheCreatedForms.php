<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Tasks\Cache\Subtasks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\DTO\Form;

class CacheCreatedForms extends FormCacheUpdater
{
    const CLASS_NAME = __CLASS__;

    /**
     * @inheritDoc
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Exceptions\FailedToRetrieveFormCacheException
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Exceptions\FailedToCreateFormCacheException
     */
    public function execute()
    {
        $cachedForms = $this->getFromCacheService()->getForms();
        $this->reportProgress(5);

        foreach ($this->getForms() as $form) {
            if ($this->isCached($form, $cachedForms)) {
                continue;
            }

            $this->cacheForm($form);
            $this->reportAlive();
        }

        $this->reportProgress(100);
    }

    /**
     * Checks if form is already cached.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\DTO\Form $form
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Entities\Form[] $cachedForms
     *
     * @return bool
     */
    private function isCached(Form $form, array $cachedForms)
    {
        foreach ($cachedForms as $cachedForm) {
            if ($form->getId() === $cachedForm->getApiId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Caches form.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\DTO\Form $form
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Exceptions\FailedToCreateFormCacheException
     */
    private function cacheForm(Form $form)
    {
        $entity = $this->translateDtoToEntity($form);
        $this->getFromCacheService()->createForm($entity);
    }
}
